<?php

namespace App\Actions\Page;

use App\Exceptions\PageException;
use App\Models\Page;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ShowPageAction
{
    public function __construct()
    {
    }

    public function handle($id): Page|JsonResponse
    {
        try {
            return Page::query()->findOrFail($id);
        } catch (Exception $e) {

            Log::debug('Page not found', ['exception' => $e->getMessage()]);

            throw new PageException($e->getMessage(), 0, $e);
        }
    }
}
