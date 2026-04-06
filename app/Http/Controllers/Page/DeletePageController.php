<?php

namespace App\Http\Controllers\Page;

use App\Exceptions\PageException;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OAT;

class DeletePageController extends Controller
{

    #[OAT\Delete(
        path: '/api/admin/pages/{id}',
        description: 'Deletes the page record and its associated image from S3 storage.',
        summary: 'Delete a specific page',
        tags: ['Admin Pages'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the page to delete',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Page deleted successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Page deleted successfully')
                    ]
                )
            ),
            new OAT\Response(response: 404, description: 'Page not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 500, description: 'Server error / PageException')
        ]
    )]
    public function __invoke(Page $page)
    {
        try {
            DB::transaction(function () use ($page) {
                $imagePath = $page->image;
                $page->delete();
                if ($imagePath) {
                    Storage::disk('s3')->delete($imagePath);
                    Log::debug('Image was deleted from s3 bucket');
                }
                Log::debug('Page was deleted from database');
            });
        } catch (Exception $e) {
            throw new PageException($e->getMessage(), 0, $e);
        }
        return response()->json([
            'message' => 'Page deleted successfully'
        ]);
    }
}
