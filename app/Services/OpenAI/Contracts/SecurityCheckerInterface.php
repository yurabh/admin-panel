<?php

namespace App\Services\OpenAI\Contracts;

interface SecurityCheckerInterface extends GenerateResponseInterface
{
    public function setContent(string $content): self;
}
