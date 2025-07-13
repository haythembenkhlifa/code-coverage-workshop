<?php

use App\Events\PostCreated;
use App\Models\Post;
use Carbon\Carbon;

describe('PostCreated Event', function () {

    it('can be instantiated with a post', function () {

        // Arrange
        $post = new Post([
            'id' => 1,
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Action
        $event = new PostCreated($post);

        // Assert
        expect($event->post)->toBe($post);
        expect($event->post->id)->toBe(1);
        expect($event->post->title)->toBe('Test Post');
    });

    it('can be dispatched', function () {

        // Arrange
        $post = new Post([
            'id' => 1,
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => 1,
        ]);

        // Action & Assert
        expect(fn() => PostCreated::dispatch($post))->not->toThrow(\Exception::class);
    });

    it('uses correct traits', function () {

        // Arrange
        $reflection = new \ReflectionClass(PostCreated::class);

        // Assert
        $traits = array_keys($reflection->getTraits());
        expect($traits)->toContain('Illuminate\Foundation\Events\Dispatchable');
        expect($traits)->toContain('Illuminate\Broadcasting\InteractsWithSockets');
        expect($traits)->toContain('Illuminate\Queue\SerializesModels');
    });
});
