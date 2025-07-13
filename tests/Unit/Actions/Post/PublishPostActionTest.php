<?php

use App\Actions\Post\PublishPostAction;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PublishPostAction', function () {
    it('publishes a post by setting published_at', function () {
        // Arrange
        $post = Post::factory()->create(['published_at' => null]);
        $action = new PublishPostAction();

        // Act
        $result = $action->execute($post);

        // Assert
        expect($result)->not->toBeNull();
        expect($result->published_at)->not->toBeNull();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'published_at' => $result->published_at,
        ]);
    });

    it('returns null if save fails', function () {
        // Arrange
        $post = Post::factory()->create(['published_at' => null]);
        $action = new PublishPostAction();

        // Simulate failure by mocking the save method
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andReturn(null);

        // Act
        $result = $action->execute($post);

        // Assert
        expect($result)->toBeNull();
    });

    it('logs an error if publish throws exception', function () {
        // Arrange
        $post = Post::factory()->create(['published_at' => null]);
        $action = new PublishPostAction();

        // Mock DB::transaction to throw exception
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andThrow(new Exception('DB error'));
        \Illuminate\Support\Facades\Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($post) {
                return $message === 'Failed to publish post' && $context['post_id'] === $post->id;
            });

        // Act
        $result = $action->execute($post);

        // Assert
        expect($result)->toBeNull();
    });
});
