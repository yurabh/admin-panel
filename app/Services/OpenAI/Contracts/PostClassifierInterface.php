<?php

namespace App\Services\OpenAI\Contracts;

interface PostClassifierInterface extends GenerateResponseInterface
{
    public function setContent(string $content): self;
}
