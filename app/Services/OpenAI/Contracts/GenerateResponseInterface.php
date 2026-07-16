<?php

namespace App\Services\OpenAI\Contracts;

interface GenerateResponseInterface
{
    public function generate(): array;

    public function requestSchema(): array;
}
