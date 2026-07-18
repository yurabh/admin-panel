<?php

namespace App\Services\AI\OpenAI;

use App\Services\AI\Contacts\ContentTransformerInterface;

class PostContentTransformer extends AbstractOpenAIGenerator implements ContentTransformerInterface
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
            'model' => config('openai.models.transform'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => "Rewrite this content:\n\n<content>\n{$this->content}\n</content>",
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'optimized_content',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'optimized_text' => [
                                'type' => 'string',
                                'description' => 'Rewritten content in Markdown, same language as input.',
                            ],
                        ],
                        'required' => ['optimized_text'],
                    ],
                ],
            ],
            'temperature' => 0,
            'max_tokens' => config('openai.tasks.transform.max_tokens'),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
            You are a professional content editor for a blog platform. Rewrite blog post content to improve readability and structure using Markdown.

            Rules:
            - Keep the SAME language as the input (do not translate).
            - Preserve ALL facts, numbers, statistics, and specific details.
            - Do NOT add new information or made-up facts.
            - Structure with # main title, ## sections, ### subsections.
            - Use **bold** for key numbers and important terms.
            - Use bullet lists for enumerations.
            - Keep professional, informative tone.
            - Output length similar to input length.
            PROMPT;
    }
}
