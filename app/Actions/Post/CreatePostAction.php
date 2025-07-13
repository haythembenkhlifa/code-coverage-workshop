<?php

namespace App\Actions\Post;

use App\Events\PostCreated;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatePostAction
{
    /**
     * Create a new post with validated data.
     */
    public function execute(array $validatedData): ?Post
    {
        try {
            DB::beginTransaction();

            // Create the post
            $post = Post::create($validatedData);

            // Dispatch the PostCreated event
            PostCreated::dispatch($post);

            // Commit the transaction
            DB::commit();

            return $post;
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Failed to create post', [
                'error' => $e->getMessage(),
                'data' => $validatedData,
            ]);
            return null;
        }
    }
}
