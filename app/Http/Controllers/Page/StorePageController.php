<?php

namespace App\Http\Controllers\Page;

use App\Actions\Page\StorePageAction;
use App\Exceptions\PageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Page\StorePageRequest;
use App\Http\Resources\Page\PageResource;
use Exception;
use Illuminate\Support\Facades\Log;

class StorePageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StorePageRequest $request, StorePageAction $action)
    {
        try {
            $page = $action->handle($request);

            Log::debug('Page were stored');

            return PageResource::make($page);

        } catch (Exception $e) {
            throw new PageException($e->getMessage(), 0, $e);
        }
    }
}
