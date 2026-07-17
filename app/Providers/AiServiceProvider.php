<?php

namespace App\Providers;

use App\Services\AI\Bedrock\PostClassifier;
use App\Services\AI\Bedrock\PostContentTransformer;
use App\Services\AI\Bedrock\PostMetadataParser;
use App\Services\AI\Bedrock\PromptInjectionGateway;
use App\Services\AI\Contacts\ContentTransformerInterface;
use App\Services\AI\Contacts\PostClassifierInterface;
use App\Services\AI\Contacts\SecurityCheckerInterface;
use App\Services\AI\Contacts\SeoParserInterface;
use App\Services\AI\OpenAI\PostClassifier as OpenAIPostClassifier;
use App\Services\AI\OpenAI\PostContentTransformer as OpenAIPostContentTransformer;
use App\Services\AI\OpenAI\PostMetadataParser as OpenAIPostMetadataParser;
use App\Services\AI\OpenAI\PromptInjectionGateway as OpenAIPromptInjectionGateway;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $provider = config('services.ai_provider', 'openai');

        if ($provider === 'openai') {
            $this->app->bind(SecurityCheckerInterface::class, OpenAIPromptInjectionGateway::class);
            $this->app->bind(SeoParserInterface::class, OpenAIPostMetadataParser::class);
            $this->app->bind(ContentTransformerInterface::class, OpenAIPostContentTransformer::class);
            $this->app->bind(PostClassifierInterface::class, OpenAIPostClassifier::class);
        } else {
            $this->app->bind(SecurityCheckerInterface::class, PromptInjectionGateway::class);
            $this->app->bind(SeoParserInterface::class, PostMetadataParser::class);
            $this->app->bind(ContentTransformerInterface::class, PostContentTransformer::class);
            $this->app->bind(PostClassifierInterface::class, PostClassifier::class);
        }
    }
}
