<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\PostPublished;

class PublishPostAction
{
    /**
     * Publish the given post by setting published_at.
     * Returns the updated post or null on failure.
     */
    public function execute(Post $post): ?Post
    {
        try {
            return DB::transaction(function () use ($post) {
                $post->published_at = now();
                if ($post->save()) {
                    event(new PostPublished($post));
                    return $post;
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to publish post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
