<?php

namespace App\Models;

use App\Notifications\UserCreatedNotification;
use Devaslanphp\FilamentAvatar\Core\HasAvatarUrl;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JeffGreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Traits\HasRoles;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Api\Upload\UploadApi;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable,
        HasRoles, HasAvatarUrl, SoftDeletes, MustVerifyNewEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'secondary_email',
        'gender',
        'birthday',
        'department_id',
        'position_id',
        'supervisor_id',
        'password',
        'creation_token',
        'type',
        'oidc_username',
        'email_verified_at',
        'avatar_url',
        'avatar_cloudinary_public_id',
        'locale',
        'timezone',
        'default_project_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
    ];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function supervisor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Assign a random default avatar based on gender.
     * Copies from public/images/default-avatars/{gender}/ to storage/avatars/
     */
    public function assignGenderAvatar(): void
    {
        if (!$this->gender || in_array($this->gender, ['other'])) return;

        // Don't override if user already uploaded a custom avatar (stored in /storage/avatars/ with user id)
        $currentAvatar = $this->getAttributes()['avatar_url'] ?? null;
        if ($currentAvatar && str_contains($currentAvatar, '/' . $this->id . '_')) return;

        $gender = $this->gender; // male or female
        $sourceDir = public_path("images/default-avatars/{$gender}");

        if (!is_dir($sourceDir)) return;

        $files = glob($sourceDir . '/*.png');
        if (empty($files)) return;

        $sourceFile = $files[array_rand($files)];
        $randomSuffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
        $baseName = pathinfo($sourceFile, PATHINFO_FILENAME);
        $filename = "avatars/{$baseName}-{$randomSuffix}.png";

        // Ensure directory exists
        $destDir = Storage::disk('public')->path('avatars');
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        copy($sourceFile, Storage::disk('public')->path($filename));
        $this->update(['avatar_url' => '/storage/' . $filename]);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (User $item) {
            if (empty($item->username)) {
                $item->username = self::generateUsernameFromEmail($item->email);
            }
            if ($item->type == 'db') {
                $item->password = bcrypt(uniqid());
                $item->creation_token = Uuid::uuid4()->toString();
            }
        });

        static::updating(function (User $item) {
            // Regenerate username jika email berubah dan username kosong
            if ($item->isDirty('email') && empty($item->username)) {
                $item->username = self::generateUsernameFromEmail($item->email);
            }
        });

        static::created(function (User $item) {
            if ($item->type == 'db') {
                $item->notify(new UserCreatedNotification($item));
            }
        });
    }

    public function projectsOwning(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id', 'id');
    }

    public function projectsAffected(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users', 'user_id', 'project_id')->withPivot(['role']);
    }

    // Alias untuk konsistensi dengan kode CustomerFeedback
    public function projects(): BelongsToMany
    {
        return $this->projectsAffected();
    }

    // Alias untuk konsistensi dengan kode CustomerFeedback
    public function ownedProjects(): HasMany
    {
        return $this->projectsOwning();
    }

    public function favoriteProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_favorites', 'user_id', 'project_id');
    }

    public function ticketsOwned(): HasMany
    {
        return $this->hasMany(Ticket::class, 'owner_id', 'id');
    }

    public function ticketsResponsible(): HasMany
    {
        return $this->hasMany(Ticket::class, 'responsible_id', 'id');
    }

    public function socials(): HasMany
    {
        return $this->hasMany(SocialiteUser::class, 'user_id', 'id');
    }

    public function hours(): HasMany
    {
        return $this->hasMany(TicketHour::class, 'user_id', 'id');
    }

    /**
     * Customer feedbacks yang dibuat user
     */
    public function customerFeedbacks(): HasMany
    {
        return $this->hasMany(CustomerFeedback::class, 'user_id');
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class, 'user_id');
    }

    /**
     * All messenger conversations where this user is a participant (either side).
     */
    public function messengerConversations()
    {
        return MessengerConversation::query()
            ->where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id);
    }

    /**
     * Messages sent by this user.
     */
    public function messengerMessagesSent(): HasMany
    {
        return $this->hasMany(MessengerMessage::class, 'sender_id');
    }

    /**
     * Semua projects yang bisa diakses user (owned + attached)
     */
    public function accessibleProjects()
    {
        $ownedProjectIds = $this->projectsOwning()->pluck('id');
        $attachedProjectIds = $this->projectsAffected()->pluck('projects.id');
        $allProjectIds = $ownedProjectIds->merge($attachedProjectIds)->unique();

        return Project::whereIn('id', $allProjectIds);
    }

    /**
     * Check if user has access to specific project
     */
    public function hasAccessToProject(Project $project): bool
    {
        return $this->id === $project->owner_id ||
               $this->projectsAffected()->where('projects.id', $project->id)->exists();
    }

    /**
     * Get projects available for feedback (only for clients)
     */
    public function getProjectsForFeedback()
    {
        if (!$this->hasRole('Client')) {
            return collect();
        }

        return $this->accessibleProjects()->get();
    }

    public function totalLoggedInHours(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->hours->sum('value');
            }
        );
    }

    public function canAccessFilament(): bool
    {
        return true;
    }

    private static function generateUsernameFromEmail($email)
    {
        // Ambil bagian sebelum @ dari email
        $baseUsername = strtolower(explode('@', $email)[0]);

        // Bersihkan karakter yang tidak diinginkan
        $baseUsername = preg_replace('/[^a-z0-9_]/', '', $baseUsername);

        // Cek apakah username sudah ada
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    public static function getValidationRules($ignoreId = null)
    {
        return [
            'username' => [
                'required',
                'unique:users,username' . ($ignoreId ? ",$ignoreId" : ''),
                'regex:/^[a-zA-Z0-9_]+$/',
                'min:3',
                'max:30'
            ]
        ];
    }

    public static function getValidationMessages()
    {
        return [
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'username.unique' => 'This username is already taken.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username cannot exceed 30 characters.'
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->attributes['avatar_url'] ?? null;
    }

    public function avatarUrl(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if (!empty($value)) {
                    return $value;
                }

                return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&color=ffffff';
            }
        );
    }

    // Method to get initials for avatar display
    public function getInitials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= mb_substr($word, 0, 1);
                if (strlen($initials) >= 2) break;
            }
        }

        return mb_strtoupper($initials);
    }
}
