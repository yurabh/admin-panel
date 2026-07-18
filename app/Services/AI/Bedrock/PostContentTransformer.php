<?php

namespace App\Services\AI\Bedrock;

use App\Services\AI\Contacts\ContentTransformerInterface;

class PostContentTransformer extends AbstractClaudeGenerator implements ContentTransformerInterface
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
            You are a professional content editor for a blog platform.
            Your task is to rewrite the following blog post content to improve its readability and structure using Markdown formatting.

            RULES:
            - Keep the SAME language as the input (do not translate).
            - Preserve ALL facts, numbers, statistics, and specific details from the original.
            - Do NOT add new information, opinions, or made-up facts.
            - Do NOT remove important information.
            - Structure the content with meaningful headers:
              - Use # for the main title (only one).
              - Use ## for major sections.
              - Use ### for subsections.
            - Use **bold** to highlight key numbers, statistics, and important terms.
            - Use bullet lists (- item) for enumerations, tips, and recommendations.
            - Keep a professional, informative, and neutral tone.
            - Do NOT change the meaning or intent of the original text.
            - The output length should be similar to the input length. Do not make it significantly shorter or longer.
            - Return ONLY the transformed content via the tool. Do not add explanations or comments.

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
            'inferenceConfig' => [
                'temperature' => 0,
                'maxTokens' => 4000,
            ],
            'tools' => [
                [
                    'toolSpec' => [
                        'name' => 'submit_optimized_content',
                        'description' => 'Return the rewritten and formatted article content in Markdown.',
                        'inputSchema' => [
                            'json' => [
                                'type' => 'object',
                                'properties' => [
                                    'optimized_text' => [
                                        'type' => 'string',
                                        'description' => 'The rewritten content in Markdown format with the same language as the input.'
                                    ]
                                ],
                                'required' => ['optimized_text']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
