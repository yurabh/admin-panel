<?php

namespace App\Services\AI\Contacts;

interface ContentTransformerInterface
{
    public function setContent(string $content): self;
}
