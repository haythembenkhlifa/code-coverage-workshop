<?php

namespace App\Actions\Post;

use App\Events\PostUpdated;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdatePostAction
{
    /**
     * Update an existing post with validated data.
     */
    public function execute(Post $post, array $validatedData): ?Post
    {
        try {
            DB::beginTransaction();

            // Store original data before update
            $originalData = $post->getOriginal();

            // Update the post
            $post->update($validatedData);

            // Dispatch the PostUpdated event
            PostUpdated::dispatch($post, $originalData);

            // Commit the transaction
            DB::commit();

            return $post;
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Failed to update post', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
                'data' => $validatedData,
            ]);

            return null;
        }
    }
}
