<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use App\Models\User;
use App\Notifications\WeeklyReportSubmitted;
use App\Services\PdfExtractorService;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EditWeeklyReport extends EditRecord
{
    protected static string $resource = WeeklyReportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),

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
}
