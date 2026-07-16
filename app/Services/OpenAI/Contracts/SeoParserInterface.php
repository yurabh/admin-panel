<?php

namespace App\Services\OpenAI\Contracts;

interface SeoParserInterface extends GenerateResponseInterface
{
    public function setContent(string $content): self;
}
