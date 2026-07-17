<?php

namespace App\Services\AI\Contacts;

interface SecurityCheckerInterface extends GenerateResponseInterface
{
    public function setContent(string $content): self;
}
