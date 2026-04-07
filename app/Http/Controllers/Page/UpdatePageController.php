<?php

namespace App\Http\Controllers\Page;

use App\Actions\Page\UpdatePageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Page\UpdatePageRequest;
use App\Http\Resources\Page\PageResource;
use App\Models\Page;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OAT;

class UpdatePageController extends Controller
{
    use AuthorizesRequests;

    #[OAT\Put(
        path: '/api/admin/pages/{id}',
        description: 'Updates page content and handles authorization via PagePolicy.',
        summary: 'Update an existing page',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/UpdatePageRequest')
        ),
        tags: ['Admin Pages'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the page to update',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Page updated successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/PageResource')
            ),
            new OAT\Response(response: 403, description: 'Forbidden - User not authorized to update this page'),
            new OAT\Response(response: 404, description: 'Page not found'),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function __invoke(Page $page, UpdatePageRequest $request, UpdatePageAction $action)
    {
        $this->authorize('update', $page);

        $page = DB::transaction(fn() => $action->handle($request, $page));

        return PageResource::make($page);
    }
}
