<?php

namespace App\Http\Livewire;

use Filament\Forms;
use Illuminate\Support\HtmlString;
use JeffGreco13\FilamentBreezy\Pages\MyProfile as BaseProfile;
use App\Models\User;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class Profile extends BaseProfile
{
    use WithFileUploads;

    protected static ?string $slug = 'my-profile';

    // Add property for avatar upload
    public $avatar;


    public function mount(): void
    {
        parent::mount();
    }

    protected function getUpdateProfileFormSchema(): array
    {
        $fields = parent::getUpdateProfileFormSchema();

        // Add username field after name field (position 1)
        $usernameField = Forms\Components\TextInput::make('username')
            ->label(__('Username'))
            ->unique(User::class, 'username', ignorable: $this->user)
            ->helperText(__('Your unique username for mentions (@username). Only letters, numbers, and underscores allowed.'))
            ->required()
            ->rules(['regex:/^[a-zA-Z0-9_]+$/']);

        // Add profile picture field at the beginning
        $avatarField = Forms\Components\FileUpload::make('avatar')
            ->label(__('Profile Picture'))
            ->image()
            ->disk('public')
            ->directory('avatars')
            ->visibility('public')
            ->maxSize(5120) // 5MB max
            ->imageResizeMode('cover')
            ->imageCropAspectRatio('1:1')
            ->imageResizeTargetWidth('200')
            ->imageResizeTargetHeight('200')
            ->placeholder(function () {
                if ($this->user->avatar_url) {
                    return __('Upload new picture to replace current one');
                }
                return __('No profile picture set');
            })
            // Add events to auto-save when upload complete
            ->uploadProgressIndicatorPosition('left')
            ->uploadButtonPosition('left')
            ->loadingIndicatorPosition('left')
            ->removeUploadedFileButtonPosition('right')
            ->enableOpen()
            ->afterStateUpdated(function ($state) {
                if ($state) {
                    $this->uploadAvatar($state);
                }
            });

        // Custom avatar preview
        $avatarPreview = Forms\Components\View::make('components.profile.avatar-preview')
            ->visible(fn () => !empty($this->user->getAttributes()['avatar_url']))
            ->label('Current Avatar');

        // Secondary email (CC) field
        $secondaryEmailField = Forms\Components\TextInput::make('secondary_email')
            ->label(__('Secondary Email (CC)'))
            ->email()
            ->helperText(__('Optional — this email will be CC\'d on notifications sent to you.'))
            ->placeholder('e.g. personal@gmail.com');

        // Gender field
        $genderField = Forms\Components\Select::make('gender')
            ->label(__('Gender'))
            ->options([
                'male' => __('Male'),
                'female' => __('Female'),
                'other' => __('Other'),
            ])
            ->placeholder(__('Select gender'));

        // Birthday field
        $birthdayField = Forms\Components\DatePicker::make('birthday')
            ->label(__('Birthday'))
            ->maxDate(now()->subYears(16));

        // Department field
        $departmentField = Forms\Components\Select::make('department_id')
            ->label(__('Department'))
            ->options(\App\Models\Department::orderBy('sort_order')->pluck('name', 'id'))
            ->searchable()
            ->reactive()
            ->afterStateUpdated(fn (callable $set) => $set('position_id', null))
            ->placeholder(__('Select department'));

        // Position field (filtered by department)
        $positionField = Forms\Components\Select::make('position_id')
            ->label(__('Position'))
            ->options(function (callable $get) {
                $deptId = $get('department_id');
                if (!$deptId) return [];
                return \App\Models\Position::where('department_id', $deptId)->orderBy('sort_order')->pluck('name', 'id');
            })
            ->searchable()
            ->placeholder(__('Select position'));

        // Direct supervisor field
        $supervisorField = Forms\Components\Select::make('supervisor_id')
            ->label(__('Direct Supervisor'))
            ->options(function () {
                return User::where('id', '!=', $this->user->id)
                    ->orderBy('name')
                    ->get()
                    ->mapWithKeys(fn ($u) => [$u->id => $u->name . ($u->position ? ' — ' . $u->position->name : '')])
                    ->toArray();
            })
            ->searchable()
            ->placeholder(__('Select supervisor'));

        // Locale selection field
        $localeField = Forms\Components\Select::make('locale')
            ->label(__('Language'))
            ->placeholder(__('Use system default'))
            ->options(config('system.locales.list', []))
            ->searchable()
            ->helperText(__('Choose your preferred language. Leave empty to use the system default.'));

        // Default project selection field
        $defaultProjectField = Forms\Components\Select::make('default_project_id')
            ->label(__('Default Project'))
            ->placeholder(__('No default project'))
            ->options(function () {
                return \App\Models\Project::where('owner_id', $this->user->id)
                    ->orWhereHas('users', function ($q) {
                        $q->where('users.id', $this->user->id);
                    })
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->searchable()
            ->helperText(__('Your default project for quick access.'));

        // Insert fields into the form
        array_splice($fields, 0, 0, [$avatarPreview, $avatarField]); // Add avatar fields at the beginning
        array_splice($fields, 3, 0, [$usernameField]); // Add username after name (now at position 3)
        $fields[] = $secondaryEmailField;
        $fields[] = $genderField;
        $fields[] = $birthdayField;
        $fields[] = $departmentField;
        $fields[] = $positionField;
        $fields[] = $supervisorField;
        $fields[] = $localeField;
        $fields[] = $defaultProjectField;

        // Update email field helper text (now at position 4)
        $emailFieldIndex = 4;
        if (isset($fields[$emailFieldIndex])) {
            $fields[$emailFieldIndex]->helperText(function () {
                $pendingEmail = $this->user->getPendingEmail();
                if ($pendingEmail) {
                    return new HtmlString(
                        '<span>' .
                        __('You have a pending email verification for :email.', [
                            'email' => $pendingEmail
                        ])
                        . '</span> <a wire:click="resendPending"
                                    class="hover:cursor-pointer hover:text-primary-500 hover:underline">
                        ' . __('Click here to resend') . '
                    </a>'
                    );
                } else {
                    return '';
                }
            });
        }

        return $fields;
    }

    protected function getFormModel(): User
    {
        return $this->user;
    }

    // Override the getUpdateProfileFormValidationRules method to add the ignoreRecord rule for username
    protected function getUpdateProfileFormValidationRules(): array
    {
        $rules = parent::getUpdateProfileFormValidationRules();

        // Replace the username validation rule to ignore the current user
        if (isset($rules['username'])) {
            $rules['username'] = [
                'required',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($this->user->id)
            ];
        }

        return $rules;
    }

    public function uploadAvatar($avatar)
    {
        if (!$avatar) {
            return;
        }

        try {
            $path = is_string($avatar)
                ? Storage::disk('public')->path($avatar)
                : $avatar->getRealPath();

            $info = getimagesize($path);
            if (!$info) {
                $this->notify('error', __('File bukan gambar yang valid.'));
                return;
            }

            $mime = $info['mime'];
            $source = match ($mime) {
                'image/jpeg' => imagecreatefromjpeg($path),
                'image/png' => imagecreatefrompng($path),
                'image/webp' => imagecreatefromwebp($path),
                default => null,
            };

            if (!$source) {
                $this->notify('error', __('Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.'));
                return;
            }

            // Resize to 200x200
            $size = 200;
            $srcW = imagesx($source);
            $srcH = imagesy($source);
            $cropSize = min($srcW, $srcH);
            $srcX = (int)(($srcW - $cropSize) / 2);
            $srcY = (int)(($srcH - $cropSize) / 2);

            $thumb = imagecreatetruecolor($size, $size);
            imagecopyresampled($thumb, $source, 0, 0, $srcX, $srcY, $size, $size, $cropSize, $cropSize);
            imagedestroy($source);

            // Save as compressed JPEG
            $filename = 'avatars/' . $this->user->id . '_' . time() . '.jpg';
            $fullPath = Storage::disk('public')->path($filename);

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagejpeg($thumb, $fullPath, 80);
            imagedestroy($thumb);

            // Delete old avatar file if it's a local file
            $oldUrl = $this->user->avatar_url;
            if ($oldUrl && str_contains($oldUrl, '/storage/avatars/')) {
                $oldFile = str_replace('/storage/', '', parse_url($oldUrl, PHP_URL_PATH));
                Storage::disk('public')->delete($oldFile);
            }

            // Clean up the temp uploaded file
            if (is_string($avatar)) {
                Storage::disk('public')->delete($avatar);
            }

            $this->user->update(['avatar_url' => '/storage/' . $filename]);
            $this->user->refresh();
            $this->notify('success', __('Foto profil berhasil diupload'));
            return redirect(request()->header('Referer', route('filament.pages.my-profile')));

        } catch (\Exception $e) {
            \Log::error('Failed to upload avatar: ' . $e->getMessage());
            $this->notify('error', __('Upload foto profil gagal. Silakan coba lagi.'));
        }
    }

    public function handleAvatarUpload($upload)
    {
        if (isset($upload['avatar']) && $upload['avatar']) {
            $this->uploadAvatar($upload['avatar']);
        }
    }

    public function updateProfile()
    {
        // Validate form data
        $data = $this->updateProfileForm->getState();

        // Handle username update
        if (isset($data['username'])) {
            // Clean username
            $data['username'] = strtolower(trim($data['username']));
        }

        $loginColumnValue = $data[$this->loginColumn];
        unset($data[$this->loginColumn]);

        // Remove the avatar field as we handle it separately
        unset($data['avatar']);

        $this->user->update($data);
        $this->user->refresh();

        // Update form with latest data
        $this->updateProfileForm->fill($this->user->toArray());

        if ($loginColumnValue != $this->user->{$this->loginColumn}) {
            $this->user->newEmail($loginColumnValue);
        }

        $this->notify("success", __('Profil berhasil diperbarui'));
    }

    public function resendPending(): void
    {
        $this->user->resendPendingEmailVerificationMail();
        $this->notify('success', __('Email verifikasi telah dikirim'));
    }

    /**
     * Remove the user's avatar
     */
    public function removeAvatar()
    {
        $oldUrl = $this->user->avatar_url;
        if ($oldUrl && str_contains($oldUrl, '/storage/avatars/')) {
            $oldFile = str_replace('/storage/', '', parse_url($oldUrl, PHP_URL_PATH));
            Storage::disk('public')->delete($oldFile);
        }

        $this->user->update(['avatar_url' => null]);
        $this->user->refresh();
        $this->notify('success', __('Foto profil berhasil dihapus'));
        return redirect(request()->header('Referer', route('filament.pages.my-profile')));
    }
}
