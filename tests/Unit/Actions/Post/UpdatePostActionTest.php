<?php

use App\Actions\Post\UpdatePostAction;
use App\Events\PostUpdated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('UpdatePostAction', function () {

    it('updates post with valid data', function () {

        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'user_id' => $user->id,
        ]);
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ];
        $action = new UpdatePostAction();

        // Action
        $result = $action->execute($post, $updateData);

        // Assert
        expect($result)->toBeInstanceOf(Post::class);
        expect($result->title)->toBe('Updated Title');
        expect($result->content)->toBe('Updated content');
        expect($result->user_id)->toBe($user->id); // Should remain unchanged
    });

    it('persists updates to database', function () {

        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $updateData = [
            'title' => 'Database Updated Title',
            'content' => 'Database updated content',
        ];
        $action = new UpdatePostAction();

        // Action
        $result = $action->execute($post, $updateData);

        // Assert
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Database Updated Title',
            'content' => 'Database updated content',
        ]);
    });

    it('updates only provided fields', function () {

        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'user_id' => $user->id,
        ]);
        $updateData = [
            'title' => 'Only Title Updated',
        ];
        $action = new UpdatePostAction();

        // Action
        $result = $action->execute($post, $updateData);

        // Assert
        expect($result->title)->toBe('Only Title Updated');
        expect($result->content)->toBe('Original content'); // Should remain unchanged
        expect($result->user_id)->toBe($user->id); // Should remain unchanged
    });

    it('returns the same post instance', function () {

        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $updateData = ['title' => 'New Title'];
        $action = new UpdatePostAction();

        // Action
        $result = $action->execute($post, $updateData);

        // Assert
        expect($result->is($post))->toBeTrue();
        expect($result->id)->toBe($post->id);
    });

    it('updates timestamps', function () {

        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $originalUpdatedAt = $post->updated_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        $updateData = ['title' => 'Timestamp Test'];
        $action = new UpdatePostAction();

        // Action
        $result = $action->execute($post, $updateData);

        // Assert
        expect($result->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
    });

    it('dispatches PostUpdated event when post is updated', function () {

        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'user_id' => $user->id,
        ]);
        $updateData = [
            'title' => 'Event Updated Title',
            'content' => 'Event updated content',
        ];
        $action = new UpdatePostAction();

        // Action
        $result = $action->execute($post, $updateData);

        // Assert
        Event::assertDispatched(PostUpdated::class, function ($event) use ($result) {
            return $event->post->id === $result->id
                && $event->originalData['title'] === 'Original Title'
                && $event->originalData['content'] === 'Original content';
        });
    });
});
