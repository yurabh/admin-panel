<?php

namespace App\Http\Controllers\Page;

use App\Actions\Page\StorePageAction;
use App\Exceptions\PageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Page\StorePageRequest;
use App\Http\Resources\Page\PageResource;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OAT;

class StorePageController extends Controller
{
    #[OAT\Post(
        path: '/api/admin/pages',
        description: 'Creates a new page with an optional image upload to S3.',
        summary: 'Create a new page',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OAT\Schema(ref: '#/components/schemas/StorePageRequest')
            )
        ),
        tags: ['Admin Pages'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Page created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/PageResource')
            ),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 500, description: 'Server error / PageException'),
        ]
    )]
    public function __invoke(StorePageRequest $request, StorePageAction $action)
    {
        try {
            $page = DB::transaction(fn () => $action->handle($request));

            Log::debug('Page were stored');

            return PageResource::make($page);
        } catch (Exception $e) {
            throw new PageException($e->getMessage(), 0, $e);
        }
    }
}
