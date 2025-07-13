<?php

use App\Events\PostCreated;
use App\Listeners\HandlePostCreated;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;

describe('HandlePostCreated Listener', function () {

    it('can handle PostCreated event without errors', function () {

        // Arrange
        $post = new Post([
            'id' => 1,
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $event = new PostCreated($post);
        $listener = new HandlePostCreated();

        // Action & Assert
        expect(fn() => $listener->handle($event))->not->toThrow(\Exception::class);
    });

    it('can be instantiated', function () {

        // Action & Assert
        expect(new HandlePostCreated())->toBeInstanceOf(HandlePostCreated::class);
    });
});
