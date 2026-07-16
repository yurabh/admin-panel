<?php

namespace App\Services\OpenAI\Contracts;

interface ContentTransformerInterface
{
    public function setContent(string $content): self;
}
