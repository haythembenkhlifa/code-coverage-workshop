<?php

namespace App\Listeners;

use App\Events\PostPublished;
use Illuminate\Support\Facades\Log;

class HandlePostPublished
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostPublished $event): void
    {
        // Log the post publication
        Log::info('Post published', [
            'post_id' => $event->post->id,
            'title' => $event->post->title,
            'user_id' => $event->post->user_id,
            'published_at' => $event->post->published_at,
        ]);

        // Add any additional logic here:
        // - Send notifications to subscribers
        // - Update search index
        // - Clear cache
        // - Send social media updates
        // - Trigger email campaigns
        // - Update analytics
        // - etc.
    }
}
