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
            return DB::transaction(function () use ($post, $validatedData) {
                // Store original data before update
                $originalData = $post->getOriginal();

                // Update the post
                if ($post->update($validatedData)) {
                    PostUpdated::dispatch($post, $originalData);
                    return $post;
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to update post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'data' => $validatedData,
            ]);
            return null;
        }
    }
}
