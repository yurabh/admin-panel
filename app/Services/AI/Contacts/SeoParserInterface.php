<?php

namespace App\Services\AI\Contacts;

interface SeoParserInterface extends GenerateResponseInterface
{
    public function setContent(string $content): self;
}
