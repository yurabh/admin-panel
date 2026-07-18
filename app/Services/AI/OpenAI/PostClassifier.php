<?php

namespace App\Services\AI\OpenAI;

use App\Models\Category;
use App\Models\Tag;
use App\Services\AI\Contacts\PostClassifierInterface;

class PostClassifier extends AbstractOpenAIGenerator implements PostClassifierInterface
{
    private string $content = '';

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function requestSchema(): array
    {
        $categoriesJson = Category::query()
            ->select('id', 'name')
            ->get()
            ->toJson();

        $tagsJson = Tag::query()
            ->where('is_active', true)
            ->select('id', 'name')
            ->get()
            ->toJson();

        return [
            'model' => config('openai.models.classification'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt($categoriesJson, $tagsJson),
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'post_classification',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'category_id' => [
                                'type' => ['integer', 'null'],
                                'description' => 'Best matching category ID from list, or null.',
                            ],
                            'tag_ids' => [
                                'type' => 'array',
                                'items' => ['type' => 'integer'],
                                'description' => 'Array of matching tag IDs from list.',
                            ],
                        ],
                        'required' => ['category_id', 'tag_ids'],
                    ],
                ],
            ],
            'temperature' => 0,
            'max_tokens' => config('openai.tasks.classification.max_tokens'),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
            You are a content classifier for a blog platform. Analyze article content and classify it using ONLY provided categories and tags from the database.

            Rules:
            - Select ONE category that best matches the main topic (use ID from list).
            - Select 1 to 5 relevant tags (use IDs from list).
            - Return null for category_id if nothing matches.
            - Return empty array [] for tag_ids if nothing matches.
            - Do NOT invent new IDs.
            - Base decisions only on article content.
            PROMPT;
    }

    private function userPrompt(string $categoriesJson, string $tagsJson): string
    {
        return <<<PROMPT
            AVAILABLE CATEGORIES:
            {$categoriesJson}

            AVAILABLE TAGS:
            {$tagsJson}

            <article>
            {$this->content}
            </article>
            PROMPT;
    }
}
