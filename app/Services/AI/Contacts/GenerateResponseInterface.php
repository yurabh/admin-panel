<?php

namespace App\Services\AI\Contacts;

interface GenerateResponseInterface
{
    public function generate(): array;

    public function requestSchema(): array;
}
