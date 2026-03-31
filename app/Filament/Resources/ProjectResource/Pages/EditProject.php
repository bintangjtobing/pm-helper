<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Services\PdfExtractorService;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    public bool $isGeneratingGoals = false;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('kanban')
                ->label(
                    fn ()
                    => ($this->record->type === 'scrum' ? __('Scrum board') : __('Kanban board'))
                )
                ->icon('heroicon-o-view-boards')
                ->color('secondary')
                ->url(function () {
                    if ($this->record->type === 'scrum') {
                        return route('filament.pages.scrum/{project}', ['project' => $this->record->id]);
                    } else {
                        return route('filament.pages.kanban/{project}', ['project' => $this->record->id]);
                    }
                }),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function generateGoalsFromDocuments(): void
    {
        $project = $this->record;
        $documents = $project->getMedia('documents');

        if ($documents->isEmpty()) {
            Filament::notify('danger', __('No documents uploaded yet. Please save the form with documents first.'));
            return;
        }

        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            Filament::notify('danger', __('OpenAI API key not configured.'));
            return;
        }

        $this->isGeneratingGoals = true;

        try {
            $extractor = new PdfExtractorService();
            $allText = '';

            foreach ($documents as $doc) {
                $text = $extractor->extractFromMedia($doc, 6000);
                $allText .= "\n\n--- Document: {$doc->file_name} ---\n{$text}";
            }

            if (empty(trim($allText))) {
                Filament::notify('danger', __('Could not extract text from the uploaded documents.'));
                $this->isGeneratingGoals = false;
                return;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a project management analyst. Analyze the provided project documents and generate a comprehensive summary of project goals, requirements, and key information. Format your response in clean HTML suitable for a rich text editor (use <h3>, <p>, <ul>, <li>, <strong> tags). Do NOT use markdown. Do NOT use em dashes. Use hyphens (-), commas, or periods instead. Structure the output with these sections: 1) Project Overview, 2) Key Goals & Objectives, 3) Requirements & Scope, 4) Key Deliverables, 5) Important Notes & Constraints. Be thorough but concise.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Analyze these project documents and generate a structured summary of goals and requirements:\n\n{$allText}",
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 3000,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI goals generation failed', ['status' => $response->status(), 'body' => $response->body()]);
                Filament::notify('danger', __('Failed to generate goals. Please try again.'));
                $this->isGeneratingGoals = false;
                return;
            }

            $data = $response->json();
            $generatedGoals = $data['choices'][0]['message']['content'] ?? '';

            if (empty($generatedGoals)) {
                Filament::notify('danger', __('No content generated. Please try again.'));
                $this->isGeneratingGoals = false;
                return;
            }

            // Update the form state
            $this->data['goals'] = $generatedGoals;

            // Also save to database directly
            $project->update(['goals' => $generatedGoals]);

            Filament::notify('success', __('Goals & Requirements generated from documents successfully!'));
        } catch (\Exception $e) {
            Log::error('Goals generation error', ['error' => $e->getMessage()]);
            Filament::notify('danger', __('An error occurred: ') . $e->getMessage());
        }

        $this->isGeneratingGoals = false;
    }
}
