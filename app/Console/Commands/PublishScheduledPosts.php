<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $postsToPublish = $this->findPostsToPublish();

        $count = $postsToPublish->count();

        if ($count === 0) {
            $this->comment("[" . now() . "] don't publish anything");
            return;
        }
        $postsToPublish->update(['is_published' => true]);

        $this->info("[" . now() . "] Posts successfully published: {$count}");
    }

    private function findPostsToPublish()
    {
        return Post::where('is_published', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
