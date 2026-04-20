<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use App\Models\GoalPeriod;
use App\Models\KeyResult;
use App\Models\User;
use App\Notifications\WeeklyReportSubmitted;
use App\Services\PdfExtractorService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class EditWeeklyReport extends EditRecord
{
    protected static string $resource = WeeklyReportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),

            Actions\Action::make('updateOkrProgress')
                ->label(__('Update OKR Progress'))
                ->icon('heroicon-o-trending-up')
                ->color('success')
                ->visible(fn () => $this->activeKeyResults()->isNotEmpty() && $this->record->user_id === auth()->id())
                ->form(fn () => $this->buildOkrUpdateForm())
                ->action(function (array $data) {
                    $this->applyOkrUpdates($data);
                }),

            Actions\Action::make('generateFromAttachments')
                ->label(__('Generate Report from Attachments (AI)'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('Generate Report Content'))
                ->modalSubheading(__('AI will read your uploaded PDF attachments and combine with ticket activity data to generate comprehensive report notes.'))
                ->action(function () {
                    $this->generateReportFromAttachments();
                }),

            Actions\Action::make('submit')
                ->label(__('Submit Report'))
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading(__('Submit Weekly Report'))
                ->modalSubheading(__('Once submitted, you will not be able to edit this report. Are you sure?'))
                ->action(function () {
                    $this->save();

                    $this->record->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    $notifyUsers = User::role(['Super Admin', 'Project Manager', 'Stakeholder'])->get();
                    foreach ($notifyUsers as $user) {
                        $user->notify(new WeeklyReportSubmitted($this->record));
                    }

                    $this->redirect(WeeklyReportResource::getUrl('view', ['record' => $this->record]));
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    public function generateReportFromAttachments(): void
    {
        $report = $this->record;
        $attachments = $report->getMedia('attachments');

        if ($attachments->isEmpty()) {
            Filament::notify('danger', __('No attachments uploaded yet. Please save the form with attachments first.'));
            return;
        }

        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            Filament::notify('danger', __('OpenAI API key not configured.'));
            return;
        }

        try {
            // Extract PDF text
            $extractor = new PdfExtractorService();
            $pdfContent = '';
            foreach ($attachments as $att) {
                if (strtolower($att->mime_type) === 'application/pdf') {
                    $text = $extractor->extractFromMedia($att, 8000);
                    $pdfContent .= "\n\n--- Attachment: {$att->file_name} ---\n{$text}";
                }
            }

            if (empty(trim($pdfContent))) {
                Filament::notify('danger', __('Could not extract text from the uploaded PDFs.'));
                return;
            }

            // Get auto_summary data
            $summaryText = '';
            $summary = $report->auto_summary;
            if ($summary && is_array($summary)) {
                $progress = $summary['progress_summary'] ?? [];
                $summaryText .= "System Ticket Data for this week:";
                $summaryText .= "\n- Tickets Touched: " . ($progress['total_tickets_touched'] ?? 0);
                $summaryText .= "\n- Tickets Completed: " . ($progress['tickets_completed'] ?? 0);
                $summaryText .= "\n- Completion Rate: " . ($progress['completion_rate'] ?? 0) . '%';
                $summaryText .= "\n- Status Changes: " . ($progress['status_changes_count'] ?? 0);
                $summaryText .= "\n- Hours Logged: " . ($progress['total_hours'] ?? 0) . 'h';

                foreach ($summary['tickets_updated'] ?? [] as $t) {
                    $summaryText .= "\n- [{$t['code']}] {$t['name']} (Status: {$t['status']}, Priority: {$t['priority']})";
                }
            }

            // Project context
            $projectName = $report->project->name ?? 'General';
            $weekLabel = $report->week_start->format('M d') . ' - ' . $report->week_end->format('M d, Y');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a project management report writer. Generate comprehensive weekly report notes in clean HTML format (use <h3>, <p>, <ul>, <li>, <strong> tags). Do NOT use markdown. Do NOT use em dashes. Use hyphens (-), commas, or periods instead. Structure the report with these sections: 1) Executive Summary, 2) Key Accomplishments, 3) Challenges & Blockers, 4) Next Week Plan, 5) Notes & Observations. Combine information from both the uploaded documents AND the system ticket data. Be specific and reference ticket codes where applicable.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate a weekly report for project '{$projectName}' for the week of {$weekLabel}.\n\n{$summaryText}\n\nUploaded Documents:\n{$pdfContent}",
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 3000,
            ]);

            if ($response->failed()) {
                Log::error('Weekly report AI generation failed', ['body' => $response->body()]);
                Filament::notify('danger', __('Failed to generate report. Please try again.'));
                return;
            }

            $data = $response->json();
            $generatedContent = $data['choices'][0]['message']['content'] ?? '';

            if (empty($generatedContent)) {
                Filament::notify('danger', __('No content generated. Please try again.'));
                return;
            }

            // Extract metrics from PDF using AI
            $metricsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a data extractor. Analyze the documents and extract project progress metrics. Return ONLY valid JSON with this exact structure, no other text:
{"total_tickets_touched": number, "tickets_completed": number, "completion_rate": number (0-100), "status_changes_count": number, "total_hours": number, "projects_worked": number, "status_breakdown": {"StatusName": count}, "type_breakdown": {"TypeName": count}, "tickets_updated": [{"code": "XXX-00", "name": "title", "status": "Status", "priority": "Priority", "type": "Type", "status_color": "#666", "priority_color": "#666"}], "tickets_completed_list": [{"code": "XXX-00", "name": "title", "project_name": "Project"}]}
Extract real numbers from the documents. If a metric is not mentioned, use 0. For tickets, extract as many as you can find mentioned in the documents.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Extract metrics from these project documents:\n{$pdfContent}",
                    ],
                ],
                'temperature' => 0.1,
                'max_tokens' => 2000,
            ]);

            // Update auto_summary with PDF-extracted metrics
            if ($metricsResponse->successful()) {
                $metricsData = $metricsResponse->json();
                $metricsJson = $metricsData['choices'][0]['message']['content'] ?? '';
                // Strip markdown code fences if present
                $metricsJson = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($metricsJson));
                $extracted = json_decode($metricsJson, true);

                if ($extracted && is_array($extracted)) {
                    $currentSummary = $report->auto_summary ?? [];

                    // Merge: use PDF data where it has higher values, keep system data otherwise
                    $sysProgress = $currentSummary['progress_summary'] ?? [];
                    $currentSummary['progress_summary'] = [
                        'total_tickets_touched' => max($sysProgress['total_tickets_touched'] ?? 0, $extracted['total_tickets_touched'] ?? 0),
                        'tickets_completed' => max($sysProgress['tickets_completed'] ?? 0, $extracted['tickets_completed'] ?? 0),
                        'completion_rate' => max($sysProgress['completion_rate'] ?? 0, $extracted['completion_rate'] ?? 0),
                        'status_changes_count' => max($sysProgress['status_changes_count'] ?? 0, $extracted['status_changes_count'] ?? 0),
                        'total_hours' => max($sysProgress['total_hours'] ?? 0, $extracted['total_hours'] ?? 0),
                        'projects_worked' => max($sysProgress['projects_worked'] ?? 0, $extracted['projects_worked'] ?? 0),
                    ];

                    // Merge ticket lists
                    if (!empty($extracted['tickets_updated'])) {
                        $existingCodes = collect($currentSummary['tickets_updated'] ?? [])->pluck('code')->toArray();
                        foreach ($extracted['tickets_updated'] as $t) {
                            if (!in_array($t['code'] ?? '', $existingCodes)) {
                                $currentSummary['tickets_updated'][] = $t;
                            }
                        }
                    }

                    if (!empty($extracted['tickets_completed_list'])) {
                        $existingCodes = collect($currentSummary['tickets_completed'] ?? [])->pluck('code')->toArray();
                        foreach ($extracted['tickets_completed_list'] as $t) {
                            if (!in_array($t['code'] ?? '', $existingCodes)) {
                                $currentSummary['tickets_completed'][] = $t;
                            }
                        }
                    }

                    // Merge breakdowns
                    if (!empty($extracted['status_breakdown'])) {
                        $currentSummary['status_breakdown'] = array_merge(
                            $currentSummary['status_breakdown'] ?? [],
                            $extracted['status_breakdown']
                        );
                    }
                    if (!empty($extracted['type_breakdown'])) {
                        $currentSummary['type_breakdown'] = array_merge(
                            $currentSummary['type_breakdown'] ?? [],
                            $extracted['type_breakdown']
                        );
                    }

                    $report->update(['auto_summary' => $currentSummary]);
                }
            }

            // Update form and database
            $this->data['content'] = $generatedContent;
            $report->update(['content' => $generatedContent]);

            Filament::notify('success', __('Report generated from attachments! Progress summary and notes updated.'));
        } catch (\Exception $e) {
            Log::error('Weekly report generation error', ['error' => $e->getMessage()]);
            Filament::notify('danger', __('Error: ') . $e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    /**
     * Key Results the report owner owns in an active quarterly period.
     * Basis for the OKR Progress action — hidden entirely if none exist.
     */
    protected function activeKeyResults()
    {
        $activePeriod = GoalPeriod::query()
            ->where('type', 'quarterly')
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->first();

        if (! $activePeriod) {
            return collect();
        }

        return KeyResult::query()
            ->with('goal')
            ->whereHas('goal', fn ($q) => $q
                ->where('period_id', $activePeriod->id)
                ->where('owner_id', $this->record->user_id)
                ->where('type', 'objective')
                ->whereNotIn('status', ['cancelled']))
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Build the modal form schema dynamically, one field-group per active KR.
     */
    protected function buildOkrUpdateForm(): array
    {
        $krs = $this->activeKeyResults();
        $schema = [
            Forms\Components\Placeholder::make('okr_intro')
                ->label('')
                ->content(new HtmlString(
                    '<div style="font-size:12.5px; color:#6b7280; line-height:1.55;">'
                    . 'Update progress on your Key Results for <strong>week of '
                    . $this->record->week_start->format('d M Y')
                    . '</strong>. Leave the value unchanged if a KR had no movement this week.'
                    . '</div>'
                )),
        ];

        foreach ($krs as $kr) {
            $mode = $kr->progress_mode;
            $modeLabel = $mode === 'auto' ? 'Auto (read-only)' : ucfirst($mode);
            $valueDisabled = $mode === 'auto';
            $unit = $kr->unit ? ' ' . $kr->unit : '';
            $code = $kr->code ? "{$kr->code} — " : '';

            $schema[] = Forms\Components\Fieldset::make("{$code}{$kr->title}")
                ->schema([
                    Forms\Components\Placeholder::make("okr_meta_{$kr->id}")
                        ->label('')
                        ->content(new HtmlString(
                            '<div style="font-size:11.5px; color:#6b7280;">'
                            . "Target: <strong>" . ($kr->target_value !== null ? number_format((float) $kr->target_value, 2) : '—') . "{$unit}</strong>"
                            . " • Current: <strong>" . number_format((float) $kr->current_value, 2) . "{$unit}</strong>"
                            . " • Mode: <strong>{$modeLabel}</strong>"
                            . '</div>'
                        ))
                        ->columnSpan('full'),

                    Forms\Components\TextInput::make("kr_{$kr->id}_value")
                        ->label('New value')
                        ->numeric()
                        ->step(0.01)
                        ->default($kr->current_value)
                        ->disabled($valueDisabled)
                        ->dehydrated(! $valueDisabled),

                    Forms\Components\TextInput::make("kr_{$kr->id}_note")
                        ->label('Note (optional)')
                        ->maxLength(500)
                        ->disabled($valueDisabled)
                        ->dehydrated(! $valueDisabled),
                ])
                ->columns(2);
        }

        return $schema;
    }

    /**
     * Persist the form submissions as KeyResultUpdate rows with source=weekly_report.
     */
    protected function applyOkrUpdates(array $data): void
    {
        $krs = $this->activeKeyResults();
        $userId = auth()->id();
        $weekStart = $this->record->week_start?->format('Y-m-d');
        $count = 0;

        foreach ($krs as $kr) {
            $valueKey = "kr_{$kr->id}_value";
            if (! array_key_exists($valueKey, $data)) {
                continue; // auto KR or field skipped
            }

            $newValue = (float) $data[$valueKey];
            if ((float) $kr->current_value === $newValue) {
                continue;
            }

            $kr->recordUpdate(
                value: $newValue,
                source: 'weekly_report',
                userId: $userId,
                note: $data["kr_{$kr->id}_note"] ?? null,
                weekStart: $weekStart,
            );
            $count++;
        }

        Notification::make()
            ->title($count > 0
                ? "Updated {$count} Key Result" . ($count === 1 ? '' : 's')
                : 'No changes recorded.')
            ->success()
            ->send();
    }
}
