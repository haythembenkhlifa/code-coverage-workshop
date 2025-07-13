<?php

use App\Events\PostUpdated;
use App\Models\Post;
use Carbon\Carbon;

describe('PostUpdated Event', function () {

    it('can be instantiated with a post and original data', function () {

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

        // Action
        $event = new PostUpdated($post, $originalData);

        // Assert
        expect($event->post)->toBe($post);
        expect($event->originalData)->toBe($originalData);
        expect($event->originalData['title'])->toBe('Original Title');
        expect($event->originalData['content'])->toBe('Original Content');
    });

    it('can be dispatched', function () {

        // Arrange
        $post = new Post([
            'id' => 1,
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => 1,
        ]);
        $originalData = ['title' => 'Original Title'];

        // Action & Assert
        expect(fn() => PostUpdated::dispatch($post, $originalData))->not->toThrow(\Exception::class);
    });

    it('uses correct traits', function () {

        // Arrange
        $reflection = new \ReflectionClass(PostUpdated::class);

        // Assert
        $traits = array_keys($reflection->getTraits());
        expect($traits)->toContain('Illuminate\Foundation\Events\Dispatchable');
        expect($traits)->toContain('Illuminate\Broadcasting\InteractsWithSockets');
        expect($traits)->toContain('Illuminate\Queue\SerializesModels');
    });
});
