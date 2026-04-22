<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketCommentAttachment;
use App\Models\TicketSharedResource;
use App\Support\SharedResourceExtractor;
use Illuminate\Console\Command;

class BackfillTicketSharedResources extends Command
{
    protected $signature = 'pmhelper:backfill-shared-resources {--fresh : Truncate the table before rebuild}';
    protected $description = 'Walk every ticket + comment + attachment and (re)populate ticket_shared_resources';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            TicketSharedResource::query()->delete();
            $this->warn('Table cleared.');
        }

        $this->info('Walking ticket descriptions…');
        Ticket::chunkById(200, function ($chunk) {
            foreach ($chunk as $t) {
                foreach (SharedResourceExtractor::extractFromHtml($t->content) as $l) {
                    TicketSharedResource::firstOrCreate(
                        [
                            'ticket_id' => $t->id,
                            'source' => 'description',
                            'url' => $l['url'],
                        ],
                        [
                            'user_id' => $t->owner_id,
                            'kind' => $l['kind'],
                            'title' => $l['title'],
                            'host' => $l['host'],
                        ]
                    );
                }
            }
        });

        $this->info('Walking comments…');
        TicketComment::chunkById(200, function ($chunk) {
            foreach ($chunk as $c) {
                foreach (SharedResourceExtractor::extractFromHtml($c->content) as $l) {
                    TicketSharedResource::firstOrCreate(
                        [
                            'comment_id' => $c->id,
                            'source' => 'comment',
                            'url' => $l['url'],
                        ],
                        [
                            'ticket_id' => $c->ticket_id,
                            'user_id' => $c->user_id,
                            'kind' => $l['kind'],
                            'title' => $l['title'],
                            'host' => $l['host'],
                        ]
                    );
                }
            }
        });

        $this->info('Walking attachments…');
        TicketCommentAttachment::with('comment')->chunkById(200, function ($chunk) {
            foreach ($chunk as $a) {
                if (! $a->comment) {
                    continue;
                }
                TicketSharedResource::firstOrCreate(
                    ['attachment_id' => $a->id],
                    [
                        'ticket_id' => $a->comment->ticket_id,
                        'comment_id' => $a->comment_id,
                        'user_id' => $a->comment->user_id,
                        'source' => 'attachment',
                        'kind' => SharedResourceExtractor::kindFromMime($a->mime_type),
                        'url' => '/storage/comment-videos/' . $a->comment->id . '/' . $a->filename_stored,
                        'title' => $a->filename_original,
                    ]
                );
            }
        });

        $total = TicketSharedResource::count();
        $this->info("Done. {$total} rows in ticket_shared_resources.");

        return Command::SUCCESS;
    }
}
