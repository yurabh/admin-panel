<?php

namespace App\Http\Controllers\Page;

use App\Actions\Page\UpdatePageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Page\UpdatePageRequest;
use App\Http\Resources\Page\PageResource;
use App\Models\Page;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UpdatePageController extends Controller
{
    use AuthorizesRequests;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Page $page, UpdatePageRequest $request, UpdatePageAction $action)
    {
        $this->authorize('update', $page);

        return PageResource::make($action->handle($request, $page));
    }
}
