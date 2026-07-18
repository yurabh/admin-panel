<?php

namespace App\Services\AI\OpenAI;

use App\Services\AI\Contacts\GenerateResponseInterface;
use JsonException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use RuntimeException;
use Throwable;

abstract class AbstractOpenAIGenerator implements GenerateResponseInterface
{
    protected Chat $chat;

    public function __construct()
    {
        $this->chat = OpenAI::chat();
    }

    public function generate(): array
    {
        try {
            $result = $this->chat->create($this->requestSchema());

            $raw = $result->choices[0]->message->content ?? '';

            $raw = $this->extractJson($raw);

            $response = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($response)) {
                throw new RuntimeException('OpenAI response is not a JSON object.');
            }

            return $response;
        } catch (JsonException $e) {
            logger()->error('OpenAI JSON decode error', [
                'message' => $e->getMessage(),
                'class' => static::class,
            ]);

        } catch (Throwable $e) {
            logger()->error('OpenAI General Error', [
                'message' => $e->getMessage(),
                'class' => static::class,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return [];
    }

    private function extractJson(string $text): string
    {
        $text = preg_replace('/```(?:json)?\s*|\s*```/', '', $text);
        $text = trim($text);

        if (str_starts_with($text, '{') || str_starts_with($text, '[')) {
            return $text;
        }

        if (preg_match('/(\{.*\})/s', $text, $matches)) {
            return $matches[1];
        }
        return $text;
    }

    abstract public function requestSchema(): array;
}
