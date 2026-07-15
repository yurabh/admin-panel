<?php

namespace App\Services\Bedrock;

use App\Models\Category;
use App\Models\Tag;

class PostClassifier extends AbstractClaudeGenerator
{
    private string $content;

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function requestSchema(): array
    {
        $categories = Category::all(['id', 'name'])->toArray();
        $tags = Tag::where('is_active', true)->get(['id', 'name'])->toArray();

        $categoriesJson = json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $tagsJson = json_encode($tags, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
            You are a content classifier for a blog platform.
            Analyze the article text below and classify it using ONLY the provided categories and tags.

            RULES:
            - Select ONE category that best matches the main topic. Use the ID from the AVAILABLE CATEGORIES list.
            - Select 1 to 5 relevant tags. Use IDs from the AVAILABLE TAGS list.
            - If no category matches, return null for category_id.
            - If no tags match, return an empty array [] for tag_ids.
            - Do NOT invent new IDs. Only use IDs that exist in the provided lists.
            - Base your decision only on the article content, not on assumptions.

            AVAILABLE CATEGORIES:
            {$categoriesJson}

            AVAILABLE TAGS:
            {$tagsJson}

            <article>
            {$this->content}
            </article>
            PROMPT;

        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [['text' => $prompt]]
                ]
            ],
            'inferenceConfig' => [
                'temperature' => 0,
                'maxTokens' => 1000,
            ],
            'tools' => [
                [
                    'toolSpec' => [
                        'name' => 'submit_classification',
                        'description' => 'Return the best matching category and tag IDs from the provided lists.',
                        'inputSchema' => [
                            'json' => [
                                'type' => 'object',
                                'properties' => [
                                    'category_id' => [
                                        'type' => ['integer', 'null'],
                                        'description' => 'Best matching category ID from the AVAILABLE CATEGORIES list, or null if no category fits.'
                                    ],
                                    'tag_ids' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'description' => 'Array of matching tag IDs from the AVAILABLE TAGS list. Can be empty.'
                                    ]
                                ],
                                'required' => ['category_id', 'tag_ids']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
