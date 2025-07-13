<?php

namespace App\Listeners;

use App\Events\PostUpdated;
use Illuminate\Support\Facades\Log;

class HandlePostUpdated
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
    public function handle(PostUpdated $event): void
    {
        // Log the post update
        Log::info('Post updated', [
            'post_id' => $event->post->id,
            'title' => $event->post->title,
            'user_id' => $event->post->user_id,
            'updated_at' => $event->post->updated_at,
            'changes' => $event->post->getChanges(),
            'original_data' => $event->originalData,
        ]);

        // Add any additional logic here:
        // - Send notifications to subscribers
        // - Update search indexes
        // - Clear cache
        // - Track analytics
        // - etc.
    }
}
