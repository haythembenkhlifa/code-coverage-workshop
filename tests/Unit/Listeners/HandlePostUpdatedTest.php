<?php

use App\Events\PostUpdated;
use App\Listeners\HandlePostUpdated;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;

describe('HandlePostUpdated Listener', function () {

    it('can handle PostUpdated event without errors', function () {

        // Arrange
        $post = new Post([
            'id' => 1,
            'title' => 'Updated Post',
            'content' => 'Updated content',
            'user_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $originalData = [
            'title' => 'Original Title',
            'content' => 'Original Content',
        ];

        $event = new PostUpdated($post, $originalData);
        $listener = new HandlePostUpdated();

        // Action & Assert
        expect(fn() => $listener->handle($event))->not->toThrow(\Exception::class);
    });

    it('can be instantiated', function () {

        // Action & Assert
        expect(new HandlePostUpdated())->toBeInstanceOf(HandlePostUpdated::class);
    });
});
