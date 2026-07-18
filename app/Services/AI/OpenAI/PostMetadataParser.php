<?php

namespace App\Services\AI\OpenAI;

use App\Services\AI\Contacts\SeoParserInterface;

class PostMetadataParser extends AbstractOpenAIGenerator implements SeoParserInterface
{
    private string $content = '';

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function requestSchema(): array
    {
        return [
            'model' => config('openai.models.parsing'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => "Analyze this blog post:\n\n<content>\n{$this->content}\n</content>",
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'seo_metadata',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'focus_keyword' => [
                                'type' => 'string',
                                'description' => 'Main keyword, 2-4 words in English, lowercase.',
                            ],
                            'meta_description' => [
                                'type' => 'string',
                                'description' => '150-160 character SEO description in English.',
                            ],
                            'suggested_slug' => [
                                'type' => 'string',
                                'description' => 'URL slug in English, lowercase, hyphens between words, max 60 chars.',
                            ],
                        ],
                        'required' => ['focus_keyword', 'meta_description', 'suggested_slug'],
                    ],
                ],
            ],
            'temperature' => 0,
            'max_tokens' => config('openai.tasks.parsing.max_tokens'),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
            You are a professional SEO editor. Analyze blog post content and extract SEO metadata.

            Rules:
            - focus_keyword: 2-4 words in English, lowercase, representing the main topic
            - meta_description: 150-160 characters in English, engaging, includes focus_keyword
            - suggested_slug: URL-friendly, English, lowercase, hyphens only, max 60 chars

            Return ONLY valid JSON matching the schema.
            PROMPT;
    }
}
