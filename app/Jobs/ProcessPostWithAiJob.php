<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\AI\AdminAiContentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPostWithAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        protected Post $post
    )
    {
    }

    public function handle(AdminAiContentService $aiService): void
    {
        if (!$this->post->exists) {
            return;
        }

        $this->post->update(['ai_status' => 'processing']);

        $aiService->processPostContent($this->post);
    }

    public function failed(Throwable $exception): void
    {
        logger()->error("AI Processing failed for Post ID {$this->post->id}: " . $exception->getMessage());

        $this->post->update(['ai_status' => 'failed']);
    }
}
