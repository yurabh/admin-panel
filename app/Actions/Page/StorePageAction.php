<?php

namespace App\Actions\Page;

use App\Http\Requests\Page\StorePageRequest;
use App\Models\Page;
use Illuminate\Support\Facades\Log;

class StorePageAction
{
    public function handle(StorePageRequest $request): Page
    {
        $data = $request->validated();

        Log::debug('Validation passed successfully');

        if ($request->hasFile('image')) {

            $path = $request->file('image')
                ->store('pages/images', 's3');

            Log::debug('Image stored to disk successfully');

            $data['image'] = $path;
        }
        return Page::query()->create($data);
    }
}
