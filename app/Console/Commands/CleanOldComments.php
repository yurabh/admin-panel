<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;

class CleanOldComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-old-comments';

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
        Comment::where("is_approved", false)
            ->where("created_at", "<", now()->subDays(30))
            ->delete();

        $this->info("Old Comments have been cleaned");
    }
}
