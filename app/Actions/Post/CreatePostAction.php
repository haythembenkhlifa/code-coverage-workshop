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
            return DB::transaction(function () use ($validatedData) {
                $post = Post::create($validatedData);
                if ($post) {
                    event(new PostCreated($post));
                    return $post;
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to create post', [
                'error' => $e->getMessage(),
                'data' => $validatedData,
            ]);
            return null;
        }
    }
}
