<?php

namespace App\Actions\Page;

use App\Http\Requests\Page\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdatePageAction
{
    public function handle(UpdatePageRequest $request, Page $page): Page
    {
        $data = $request->validated();

        Log::debug('Validation page passed');

        if ($request->hasFile('image')) {

            $this->updateImageInS3Bucket($page);

            $data['image'] = $request->file('image')
                ->store('pages/images', 's3');

            Log::debug('Image was updated in s3 bucket');
        }

        $page->update($data);

        return $page;
    }

    private function updateImageInS3Bucket(Page $page): void
    {
        if (! empty($page->image)) {

            Storage::disk('s3')->delete($page->image);

            Log::debug('Old Image removed from s3 bucket');
        }
    }
}
