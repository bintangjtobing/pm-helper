<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\User;

class AddDiscussionCommentTool implements Tool
{
    public function name(): string
    {
        return 'add_discussion_comment';
    }

    public function description(): string
    {
        return 'Add a reply (comment) to a discussion. Content may be markdown or HTML.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['discussion_id', 'content'],
            'properties' => [
                'discussion_id' => ['type' => 'integer'],
                'content' => ['type' => 'string'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $discussion = Discussion::with('project')->find((int) ($args['discussion_id'] ?? 0));
        if (! $discussion) {
            throw new \RuntimeException('Discussion not found.');
        }

        $project = $discussion->project;
        $hasAccess = $project && ($project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists());
        if (! $hasAccess) {
            throw new \RuntimeException('Access denied to this discussion.');
        }

        $content = trim((string) ($args['content'] ?? ''));
        if ($content === '') {
            throw new \RuntimeException('content is required.');
        }

        $reply = DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => $user->id,
            'content' => $content,
        ]);

        return [
            'id' => $reply->id,
            'discussion_id' => $discussion->id,
            'created_at' => optional($reply->created_at)->toIso8601String(),
            'message' => 'Reply added.',
        ];
    }
}
