<?php

use App\Actions\Post\CreatePostAction;
use App\Events\PostCreated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CreatePostAction', function () {

    it('creates a post with valid data', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Test Post Title',
            'content' => 'Test post content',
            'user_id' => $user->id,
        ];
        $action = new CreatePostAction();

        // Action
        $result = $action->execute($data);

        // Assert
        expect($result)->toBeInstanceOf(Post::class);
        expect($result->title)->toBe('Test Post Title');
        expect($result->content)->toBe('Test post content');
        expect($result->user_id)->toBe($user->id);
        expect($result->id)->not->toBeNull();
    });

    it('persists post to database', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Database Test Post',
            'content' => 'Content for database test',
            'user_id' => $user->id,
        ];
        $action = new CreatePostAction();

        // Action
        $result = $action->execute($data);

        // Assert
        $this->assertDatabaseHas('posts', [
            'id' => $result->id,
            'title' => 'Database Test Post',
            'content' => 'Content for database test',
            'user_id' => $user->id,
        ]);
    });

    it('returns post with timestamps', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Timestamp Test',
            'content' => 'Testing timestamps',
            'user_id' => $user->id,
        ];
        $action = new CreatePostAction();

        // Action
        $result = $action->execute($data);

        // Assert
        expect($result->created_at)->not->toBeNull();
        expect($result->updated_at)->not->toBeNull();
        expect($result->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($result->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('dispatches PostCreated event when post is created', function () {

        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $data = [
            'title' => 'Event Test Post',
            'content' => 'Testing event dispatch',
            'user_id' => $user->id,
        ];
        $action = new CreatePostAction();

        // Action
        $result = $action->execute($data);

        // Assert
        Event::assertDispatched(PostCreated::class, function ($event) use ($result) {
            return $event->post->id === $result->id;
        });
    });

    it('returns null and logs error if transaction fails', function () {

        // Arrange
        Event::fake();
        Log::spy();

        // Mock DB::transaction to throw an exception
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Transaction failed'));

        $action = new CreatePostAction();

        // Action
        $result = $action->execute([
            'title' => 'Test',
            'content' => 'Test content',
            'user_id' => 1,
        ]);

        // Assert
        expect($result)->toBeNull();
        Event::assertNotDispatched(PostCreated::class);
        Log::shouldHaveReceived('error')->once()->with('Failed to create post', [
            'error' => 'Transaction failed',
            'data' => [
                'title' => 'Test',
                'content' => 'Test content',
                'user_id' => 1,
            ],
        ]);
    });
});
