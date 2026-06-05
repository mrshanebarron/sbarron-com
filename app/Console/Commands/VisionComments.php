<?php

namespace App\Console\Commands;

use App\Models\VisionComment;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Moderate /vision reader comments from the CLI — no web admin surface.
 *
 *   php artisan vision:comments              list pending (the queue)
 *   php artisan vision:comments --all        list everything
 *   php artisan vision:comments --approve=ID approve one (it goes public)
 *   php artisan vision:comments --reject=ID  delete one
 *
 * Run on the box: `cd /var/www/sbarron.com && php artisan vision:comments`.
 */
class VisionComments extends Command
{
    protected $signature = 'vision:comments
        {--all : show all comments, not just the pending queue}
        {--approve= : approve the comment with this id (makes it public)}
        {--reject= : delete the comment with this id}';

    protected $description = 'Moderate reader comments on the /vision docs';

    public function handle(): int
    {
        if ($id = $this->option('approve')) {
            $c = VisionComment::find($id);
            if (! $c) {
                $this->error("No comment #{$id}.");
                return self::FAILURE;
            }
            $c->update(['approved' => true, 'approved_at' => now()]);
            $this->info("Approved #{$id} — now live on /vision/{$c->doc_slug}.");
            return self::SUCCESS;
        }

        if ($id = $this->option('reject')) {
            $c = VisionComment::find($id);
            if (! $c) {
                $this->error("No comment #{$id}.");
                return self::FAILURE;
            }
            $c->delete();
            $this->info("Deleted #{$id}.");
            return self::SUCCESS;
        }

        $query = VisionComment::query()->orderBy('created_at', 'desc');
        if (! $this->option('all')) {
            $query->where('approved', false);
        }
        $comments = $query->get();

        if ($comments->isEmpty()) {
            $this->info($this->option('all') ? 'No comments yet.' : 'Queue is empty — nothing pending.');
            return self::SUCCESS;
        }

        $this->table(
            ['id', 'status', 'doc', 'author', 'when', 'comment'],
            $comments->map(fn ($c) => [
                $c->id,
                $c->approved ? 'live' : 'PENDING',
                Str::limit($c->doc_slug, 28),
                Str::limit($c->author_name, 18),
                $c->created_at->diffForHumans(),
                Str::limit(str_replace("\n", ' ', $c->body), 60),
            ])->all(),
        );

        if (! $this->option('all')) {
            $this->line('');
            $this->line('Approve: <info>php artisan vision:comments --approve=ID</info>   Reject: <info>--reject=ID</info>');
        }

        return self::SUCCESS;
    }
}
