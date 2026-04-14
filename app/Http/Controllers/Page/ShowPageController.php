<?php

namespace App\Http\Controllers\Page;

use App\Actions\Page\ShowPageAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Page\PageResource;
use OpenApi\Attributes as OAT;

class ShowPageController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/pages/{id}',
        description: 'Returns a single page resource by its ID using PageAction.',
        summary: 'Display a specific page',
        tags: ['Admin Pages'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the page to retrieve',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(ref: '#/components/schemas/PageResource')
            ),
            new OAT\Response(response: 404, description: 'Page not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function __invoke($id, ShowPageAction $action)
    {
        return PageResource::make($action->handle($id));
    }
}
