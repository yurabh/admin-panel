<?php

namespace App\Services\AI\Contacts;

interface PostClassifierInterface extends GenerateResponseInterface
{
    public function setContent(string $content): self;
}
