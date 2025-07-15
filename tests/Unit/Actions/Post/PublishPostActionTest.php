<?php

use App\Actions\Post\PublishPostAction;
use App\Events\PostPublished;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

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
        DB::shouldReceive('transaction')->andReturn(null);

        // Act
        $result = $action->execute($post);

        // Assert
        expect($result)->toBeNull();
    });

    it('dispatches PostPublished event when post is published', function () {
        // Arrange
        Event::fake();
        $post = Post::factory()->create(['published_at' => null]);
        $action = new PublishPostAction();

        // Act
        $result = $action->execute($post);

        // Assert
        expect($result)->not->toBeNull();
        Event::assertDispatched(PostPublished::class, function ($event) use ($result) {
            return $event->post->id === $result->id;
        });
    });

    it('logs an error if publish throws exception', function () {
        // Arrange
        $post = Post::factory()->create(['published_at' => null]);
        $action = new PublishPostAction();

        // Mock DB::transaction to throw exception
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Transaction failed'));

        Log::shouldReceive('error')
            ->once()
            ->with('Failed to publish post', [
                'post_id' => $post->id,
                'error' => 'Transaction failed',
            ]);

        // Act
        $result = $action->execute($post);

        // Assert
        expect($result)->toBeNull();
    });
});
