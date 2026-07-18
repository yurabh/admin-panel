<?php

namespace App\Services\AI\Bedrock;

use App\Services\AI\Contacts\GenerateResponseInterface;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Throwable;

abstract class AbstractClaudeGenerator implements GenerateResponseInterface
{
    protected BedrockRuntimeClient $client;

    protected const MODEL_ID = 'global.anthropic.claude-sonnet-4-5-20250929-v1:0';

    public function __construct()
    {
        $this->client = new BedrockRuntimeClient([
            'region' => config('bedrock.region', 'us-east-1'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('bedrock.api_key'),
                'secret' => config('bedrock.secret_key'),
            ],
        ]);
    }

    public function generate(): array
    {
        try {
            $params = $this->requestSchema();
            $result = $this->client->converse([
                'modelId' => self::MODEL_ID,
                'messages' => $params['messages'],
                'toolConfig' => [
                    'tools' => $params['tools']
                ]
            ]);

            $stopReason = $result['stopReason'] ?? null;
            $outputMessage = $result['output']['message'] ?? [];

            if ($stopReason === 'tool_use' && !empty($outputMessage['content'])) {
                foreach ($outputMessage['content'] as $contentBlock) {
                    if (isset($contentBlock['toolUse'])) {
                        return $contentBlock['toolUse']['input'] ?? [];
                    }
                }
            }
            logger()->error('Claude Converse Error: Model did not invoke the requested tool. Stop reason: ' . $stopReason);
            return [];

        } catch (Throwable $e) {
            logger()->error('AWS Bedrock Converse API Error: ' . $e->getMessage());
            return [];
        }
    }

    abstract public function requestSchema(): array;
}
