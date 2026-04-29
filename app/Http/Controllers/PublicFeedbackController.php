<?php

namespace App\Http\Controllers;

use App\Models\CustomerFeedback;
use App\Models\CustomerFeedbackAttachment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicFeedbackController extends Controller
{
    public function show(string $token): View
    {
        $project = $this->resolveProject($token);

        return view('public.feedback-form', [
            'project' => $project,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $project = $this->resolveProject($token);

        if ($request->filled('website')) {
            return redirect()->route('public.feedback.thanks', $token);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:160',
            'title' => 'required|string|max:180',
            'description' => 'required|string|max:5000',
            'website' => 'nullable|max:0',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => [
                'file',
                'max:25600',
                'mimes:pdf,docx',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ], [
            'attachments.*.max' => 'Each file must be 25MB or smaller.',
            'attachments.*.mimes' => 'Only PDF or DOCX files are allowed.',
            'attachments.*.mimetypes' => 'Only PDF or DOCX files are allowed.',
        ]);

        $descriptionFooter = "\n\n---\nSubmitted publicly by: {$validated['name']} <{$validated['email']}>";

        $feedback = CustomerFeedback::create([
            'project_id' => $project->id,
            'user_id' => $project->owner_id,
            'title' => $validated['title'],
            'description' => $validated['description'] . $descriptionFooter,
            'status' => 'pending',
        ]);

        $files = $request->file('attachments', []);
        if (! empty($files)) {
            $storagePath = 'feedback-attachments/' . $feedback->id;

            foreach ($files as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
                if (! in_array($extension, ['pdf', 'docx'], true)) {
                    continue;
                }

                $storedName = uniqid('fb_') . '.' . $extension;
                $file->storeAs($storagePath, $storedName, 'public');

                CustomerFeedbackAttachment::create([
                    'feedback_id' => $feedback->id,
                    'filename_stored' => $storedName,
                    'filename_original' => Str::limit($file->getClientOriginalName(), 200, ''),
                    'mime_type' => $file->getMimeType() ?: ($extension === 'pdf'
                        ? 'application/pdf'
                        : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                    'size_bytes' => $file->getSize() ?: 0,
                ]);
            }
        }

        return redirect()->route('public.feedback.thanks', $token);
    }

    public function thanks(string $token): View
    {
        $project = $this->resolveProject($token);

        return view('public.feedback-thanks', [
            'project' => $project,
        ]);
    }

    protected function resolveProject(string $token): Project
    {
        $project = Project::where('public_feedback_token', $token)
            ->where('public_feedback_enabled', true)
            ->first();

        abort_if(! $project, 404, 'This feedback link is not active.');

        return $project;
    }
}
