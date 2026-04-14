<?php

namespace App\Http\Controllers\Setting;

use App\Actions\Setting\SettingCreateAction;
use App\Actions\Setting\SettingDeleteAction;
use App\Actions\Setting\SettingUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\SettingCreateRequest;
use App\Http\Requests\Setting\SettingUpdateRequest;
use App\Http\Resources\Setting\SettingResource;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OAT;

class SettingController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/settings',
        description: 'Returns a key-value pair of all system settings from the cache.',
        summary: 'Get all cached settings',
        tags: ['Admin Settings'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(
                    type: 'object',
                    example: [
                        'site_name' => 'My Admin Panel',
                        'site_logo' => '/images/logo.png',
                        'maintenance_mode' => 'false',
                    ],
                    additionalProperties: new OAT\AdditionalProperties(type: 'string')
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index()
    {
        $allCached = Setting::getAllCached();

        return response()->json(
            $allCached
        );
    }

    #[OAT\Post(
        path: '/api/admin/settings',
        description: 'Creates a new system setting and returns the created resource.',
        summary: 'Create a new setting',
        security: [['bearerAuth' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/SettingCreateRequest')
        ),
        tags: ['Admin Settings'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Setting created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/SettingResource')
            ),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    /**
     * Store a newly created resource in storage.
     *
     * @throws \Throwable
     */
    public function store(SettingCreateRequest $request, SettingCreateAction $action)
    {
        $setting = DB::transaction(fn () => $action->handle($request));

        Log::debug('Setting created', ['setting' => $setting->id]);

        return SettingResource::make($setting);
    }

    #[OAT\Get(
        path: '/api/admin/settings/{key}',
        description: 'Retrieves the value of a setting from the cache using its unique key.',
        summary: 'Get a specific setting by key',
        security: [['bearerAuth' => []]],
        tags: ['Admin Settings'],
        parameters: [
            new OAT\Parameter(
                name: 'key',
                description: 'The unique key of the setting (e.g., site_name)',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'string', example: 'site_name')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'key', type: 'string', example: 'site_name'),
                        new OAT\Property(property: 'value', type: 'string', example: 'My Admin Panel'),
                    ]
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Setting not found',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Not found'),
                    ]
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(string $key)
    {
        $value = Setting::getValue($key);

        return $value ? response()->json(['key' => $key, 'value' => $value])
            : response()->json(['message' => 'Not found'], 404);
    }

    /**
     * @throws \Throwable
     */
    #[OAT\Put(
        path: '/api/admin/settings/{id}',
        description: 'Updates a specific system setting by its ID and returns the updated resource.',
        summary: 'Update an existing setting',
        security: [['bearerAuth' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/SettingUpdateRequest')
        ),
        tags: ['Admin Settings'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the setting to update',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Setting updated successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/SettingResource')
            ),
            new OAT\Response(response: 404, description: 'Setting not found'),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function update(SettingUpdateRequest $request, Setting $setting, SettingUpdateAction $action)
    {
        $setting = $action->handle($request, $setting);

        return SettingResource::make($setting);
    }

    #[OAT\Delete(
        path: '/api/admin/settings/{key}',
        description: 'Deletes a specific system setting using its unique key via SettingDeleteAction.',
        summary: 'Delete a setting by key',
        security: [['bearerAuth' => []]],
        tags: ['Admin Settings'],
        parameters: [
            new OAT\Parameter(
                name: 'key',
                description: 'The unique key of the setting to delete (e.g., site_name)',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'string', example: 'site_name')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Setting deleted successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Setting deleted successfully'),
                    ]
                )
            ),
            new OAT\Response(response: 404, description: 'Setting not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(string $key, SettingDeleteAction $action)
    {
        return $action->handle($key);
    }
}
