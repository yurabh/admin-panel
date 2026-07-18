<?php

namespace App\Services\AI;

use App\Models\Post;
use App\Services\AI\Contacts\ContentTransformerInterface;
use App\Services\AI\Contacts\PostClassifierInterface;
use App\Services\AI\Contacts\SecurityCheckerInterface;
use App\Services\AI\Contacts\SeoParserInterface;

class AdminAiContentService
{
    public function __construct(
        protected SecurityCheckerInterface    $security,
        protected SeoParserInterface          $seoParser,
        protected ContentTransformerInterface $transformer,
        protected PostClassifierInterface     $classifier,
    )
    {
    }

    public function processPostContent(Post $post): array
    {
        $content = $post->content;

        $securityCheck = $this->security->setContent($content)->generate();

        if (isset($securityCheck['is_safe']) && $securityCheck['is_safe'] === false) {
            $post->update([
                'ai_status' => 'unsafe',
                'ai_metadata' => ['blocked_reason' => $securityCheck['reason'] ?? 'Security violation']
            ]);

            return ['post' => $post, 'status' => 'blocked'];
        }

        $seoData = $this->seoParser->setContent($content)->generate();

        $optimizedTextData = $this->transformer->setContent($content)->generate();

        $post->update([
            'ai_metadata' => [
                'focus_keyword' => $seoData['focus_keyword'] ?? null,
                'meta_description' => $seoData['meta_description'] ?? null,

            ],
            'ai_optimized_content' => $optimizedTextData['optimized_text'] ?? null,
            'ai_status' => 'safe'
        ]);

        $classification = $this->classifier->setContent($content)->generate();

        if (!empty($classification['category_id'])) {
            $post->update(['category_id' => $classification['category_id']]);
        }

        if (!empty($classification['tag_ids'])) {
            $post->tags()->sync($classification['tag_ids']);
        }

        return [
            'post' => $post->refresh(),
            'seo' => $seoData,
            'tags_applied' => $classification['tag_ids'] ?? []
        ];
    }
}
