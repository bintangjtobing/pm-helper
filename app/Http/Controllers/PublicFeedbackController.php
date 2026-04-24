<?php

namespace App\Http\Controllers;

use App\Models\CustomerFeedback;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        $descriptionFooter = "\n\n---\nSubmitted publicly by: {$validated['name']} <{$validated['email']}>";

        CustomerFeedback::create([
            'project_id' => $project->id,
            'user_id' => $project->owner_id,
            'title' => $validated['title'],
            'description' => $validated['description'] . $descriptionFooter,
            'status' => 'pending',
        ]);

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
