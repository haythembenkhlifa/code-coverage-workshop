<?php

namespace App\Listeners;

use App\Events\PostCreated;
use Illuminate\Support\Facades\Log;

class HandlePostCreated
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
    public function handle(PostCreated $event): void
    {
        // Log the post creation
        Log::info('New post created', [
            'post_id' => $event->post->id,
            'title' => $event->post->title,
            'user_id' => $event->post->user_id,
            'created_at' => $event->post->created_at,
        ]);

        // Add any additional logic here:
        // - Send notifications
        // - Update cache
        // - Trigger other processes
        // - etc.
    }
}
