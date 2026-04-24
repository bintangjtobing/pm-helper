<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Notifications\WeeklyReportSubmitted;
use App\Services\PdfExtractorService;
use App\Services\WeeklyReportService;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateWeeklyReport extends CreateRecord
{
    protected static string $resource = WeeklyReportResource::class;

    public function mount(): void
    {
        parent::mount();

        $service = new WeeklyReportService();
        $bounds = $service->getWeekBounds();
        $summary = $service->generateAutoSummary(auth()->user(), $bounds['week_start'], $bounds['week_end'], null);
        $content = $service->formatSummaryAsMarkdown($summary);

        $this->form->fill([
            'user_id' => auth()->id(),
            'week_start' => $bounds['week_start']->format('Y-m-d'),
            'content' => $content,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = new WeeklyReportService();

        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $projectId = isset($data['project_id']) && $data['project_id'] !== '' ? (int) $data['project_id'] : null;

        $data['user_id'] = auth()->id();
        $data['week_start'] = $weekStart->format('Y-m-d');
        $data['week_end'] = $weekEnd->format('Y-m-d');
        $data['auto_summary'] = $service->generateAutoSummary(auth()->user(), $weekStart, $weekEnd, $projectId);
        $data['status'] = 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->enrichAutoSummaryFromPdf($this->record);
    }

    /**
     * After report is created, extract metrics from PDF attachments
     * and merge into auto_summary for richer Progress Summary.
     */
    private function enrichAutoSummaryFromPdf(WeeklyReport $report): void
    {
        $attachments = $report->getMedia('attachments');
        if ($attachments->isEmpty()) return;

        $apiKey = config('services.openai.key');
        if (empty($apiKey)) return;

        try {
            $extractor = new PdfExtractorService();
            $pdfContent = '';
            foreach ($attachments as $att) {
                if (strtolower($att->mime_type) === 'application/pdf') {
                    $text = $extractor->extractFromMedia($att, 8000);
                    $pdfContent .= "\n\n--- {$att->file_name} ---\n{$text}";
                }
            }

            if (empty(trim($pdfContent))) return;

            // Extract structured metrics from PDF
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a data extractor. Analyze project documents and extract progress metrics. Return ONLY valid JSON, no markdown fences, no other text. Structure:
{"total_tickets_touched": number, "tickets_completed": number, "completion_rate": number, "status_changes_count": number, "total_hours": number, "projects_worked": number, "status_breakdown": {"StatusName": count}, "type_breakdown": {"TypeName": count}, "tickets_updated": [{"code": "XXX-00", "name": "title", "status": "Status", "priority": "Priority", "type": "Type", "status_color": "#666", "priority_color": "#666"}], "tickets_completed_list": [{"code": "XXX-00", "name": "title", "project_name": "Project"}]}
Count ALL tickets mentioned in the document. If tickets have codes like QOS-xx, extract them. For completion_rate, calculate based on completed vs total if possible.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Extract all progress metrics from these documents:\n{$pdfContent}",
                    ],
                ],
                'temperature' => 0.1,
                'max_tokens' => 3000,
            ]);

            if ($response->failed()) return;

            $data = $response->json();
            $metricsJson = $data['choices'][0]['message']['content'] ?? '';
            $metricsJson = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($metricsJson));
            $extracted = json_decode($metricsJson, true);

            if (!$extracted || !is_array($extracted)) return;

            $currentSummary = $report->auto_summary ?? [];
            $sysProgress = $currentSummary['progress_summary'] ?? [];

            // Merge: keep higher values
            $currentSummary['progress_summary'] = [
                'total_tickets_touched' => max($sysProgress['total_tickets_touched'] ?? 0, $extracted['total_tickets_touched'] ?? 0),
                'tickets_completed' => max($sysProgress['tickets_completed'] ?? 0, $extracted['tickets_completed'] ?? 0),
                'completion_rate' => max($sysProgress['completion_rate'] ?? 0, $extracted['completion_rate'] ?? 0),
                'status_changes_count' => max($sysProgress['status_changes_count'] ?? 0, $extracted['status_changes_count'] ?? 0),
                'total_hours' => max($sysProgress['total_hours'] ?? 0, $extracted['total_hours'] ?? 0),
                'projects_worked' => max($sysProgress['projects_worked'] ?? 0, $extracted['projects_worked'] ?? 0),
            ];

            // Merge ticket lists (avoid duplicates by code)
            if (!empty($extracted['tickets_updated'])) {
                $existingCodes = collect($currentSummary['tickets_updated'] ?? [])->pluck('code')->toArray();
                foreach ($extracted['tickets_updated'] as $t) {
                    if (!empty($t['code']) && !in_array($t['code'], $existingCodes)) {
                        $currentSummary['tickets_updated'][] = $t;
                    }
                }
            }
            if (!empty($extracted['tickets_completed_list'])) {
                $existingCodes = collect($currentSummary['tickets_completed'] ?? [])->pluck('code')->toArray();
                foreach ($extracted['tickets_completed_list'] as $t) {
                    if (!empty($t['code']) && !in_array($t['code'], $existingCodes)) {
                        $currentSummary['tickets_completed'][] = $t;
                    }
                }
            }

            // Merge breakdowns
            if (!empty($extracted['status_breakdown'])) {
                $currentSummary['status_breakdown'] = array_merge($currentSummary['status_breakdown'] ?? [], $extracted['status_breakdown']);
            }
            if (!empty($extracted['type_breakdown'])) {
                $currentSummary['type_breakdown'] = array_merge($currentSummary['type_breakdown'] ?? [], $extracted['type_breakdown']);
            }

            // Update ticket count in progress_summary based on merged lists
            $currentSummary['progress_summary']['total_tickets_touched'] = max(
                $currentSummary['progress_summary']['total_tickets_touched'],
                count($currentSummary['tickets_updated'] ?? [])
            );
            $currentSummary['progress_summary']['tickets_completed'] = max(
                $currentSummary['progress_summary']['tickets_completed'],
                count($currentSummary['tickets_completed'] ?? [])
            );

            // Also update project_breakdown with merged tickets
            $projectBreakdown = [];
            foreach ($currentSummary['tickets_updated'] ?? [] as $t) {
                $pName = $t['project_name'] ?? $report->project->name ?? 'General';
                if (!isset($projectBreakdown[$pName])) {
                    $projectBreakdown[$pName] = ['total' => 0, 'tickets' => []];
                }
                $projectBreakdown[$pName]['total']++;
                $projectBreakdown[$pName]['tickets'][] = $t;
            }
            if (!empty($projectBreakdown)) {
                $currentSummary['project_breakdown'] = $projectBreakdown;
            }

            $report->update(['auto_summary' => $currentSummary]);

        } catch (\Exception $e) {
            Log::warning('PDF metrics extraction failed during create', ['error' => $e->getMessage()]);
        }
    }

    protected function getActions(): array
    {
        return [];
    }

    protected function getCreateFormAction(): \Filament\Pages\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label(__('Save as Draft'));
    }

    protected function getCreateAnotherFormAction(): \Filament\Pages\Actions\Action
    {
        return Actions\Action::make('submit')
            ->label(__('Submit Report'))
            ->color('success')
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->modalHeading(__('Submit Weekly Report'))
            ->modalSubheading(__('Once submitted, you will not be able to edit this report. Are you sure?'))
            ->action(function () {
                $this->create(false);

                $report = WeeklyReport::where('user_id', auth()->id())
                    ->latest()
                    ->first();

                if ($report) {
                    $report->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    $notifyUsers = User::role(['Super Admin', 'Project Manager', 'Stakeholder'])->get();
                    foreach ($notifyUsers as $user) {
                        $user->notify(new WeeklyReportSubmitted($report));
                    }
                }

                $this->redirect(WeeklyReportResource::getUrl('index'));
            });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
