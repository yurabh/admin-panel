<?php

namespace App\Providers;

use App\Services\OpenAI\Contracts\ContentTransformerInterface;
use App\Services\OpenAI\Contracts\PostClassifierInterface;
use App\Services\OpenAI\Contracts\SecurityCheckerInterface;
use App\Services\OpenAI\Contracts\SeoParserInterface;
use App\Services\OpenAI\PostClassifier as OpenAIPostClassifier;
use App\Services\OpenAI\PostContentTransformer as OpenAIPostContentTransformer;
use App\Services\OpenAI\PostMetadataParser as OpenAIPostMetadataParser;
use App\Services\OpenAI\PromptInjectionGateway as OpenAIPromptInjectionGateway;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SecurityCheckerInterface::class, OpenAIPromptInjectionGateway::class);
        $this->app->bind(SeoParserInterface::class, OpenAIPostMetadataParser::class);
        $this->app->bind(ContentTransformerInterface::class, OpenAIPostContentTransformer::class);
        $this->app->bind(PostClassifierInterface::class, OpenAIPostClassifier::class);
    }
}
