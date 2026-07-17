<?php

namespace App\Services\AI\Bedrock;

use App\Services\AI\Contacts\SeoParserInterface;

class PostMetadataParser extends AbstractClaudeGenerator implements SeoParserInterface
{
    private string $content;

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function requestSchema(): array
    {
        $prompt = <<<PROMPT
            Analyze the following blog post content and extract SEO metadata.

            Rules:
            - focus_keyword: 2-4 words in English, lowercase, representing the main topic.
            - meta_description: 150-160 characters in English, engaging summary for search engines. Include focus_keyword naturally.
            - suggested_slug: URL-friendly slug in English, lowercase, words separated by hyphens. Maximum 60 characters. Only a-z, 0-9, and hyphens allowed.

            <content>
            {$this->content}
            </content>
            PROMPT;

        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [['text' => $prompt]]
                ]
            ],
            'tools' => [
                [
                    'toolSpec' => [
                        'name' => 'submit_seo_metadata',
                        'description' => 'Return calculated SEO fields.',
                        'inputSchema' => [
                            'json' => [
                                'type' => 'object',
                                'properties' => [
                                    'focus_keyword' => ['type' => 'string'],
                                    'meta_description' => ['type' => 'string'],
                                    'suggested_slug' => ['type' => 'string']
                                ],
                                'required' => ['focus_keyword', 'meta_description', 'suggested_slug']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
