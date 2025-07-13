<?php

use App\Models\Post;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created with factory', function () {
    // Arrange
    $user = User::factory()->create();

    // Action
    $post = Post::factory()->create(['user_id' => $user->id]);

    // Assert
    expect($post)->toBeInstanceOf(Post::class);
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => $post->title,
        'content' => $post->content,
        'user_id' => $user->id,
    ]);
});

it('can be created with attributes', function () {

    // Arrange
    $user = User::factory()->create();
    $postData = [
        'title' => 'Test Post Title',
        'content' => 'This is test post content.',
        'user_id' => $user->id,
    ];

    // Action
    $post = Post::create($postData);

    // Assert
    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->title)->toBe('Test Post Title')
        ->and($post->content)->toBe('This is test post content.')
        ->and($post->user_id)->toBe($user->id);

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post Title',
        'content' => 'This is test post content.',
        'user_id' => $user->id,
    ]);
});

it('has no guarded attributes', function () {

    // Arrange
    $post = new Post();

    // Action
    $guarded = $post->getGuarded();

    // Assert
    expect($guarded)->toBe([]);
});

it('allows mass assignment for all attributes', function () {

    // Arrange
    $user = User::factory()->create();
    $postData = [
        'title' => 'Mass Assignment Test',
        'content' => 'Testing mass assignment.',
        'user_id' => $user->id,
    ];

    // Action
    $post = Post::create($postData);

    // Assert
    expect($post->title)->toBe('Mass Assignment Test')
        ->and($post->content)->toBe('Testing mass assignment.')
        ->and($post->user_id)->toBe($user->id);
});

it('requires title field', function () {
    // Arrange
    $user = User::factory()->create();

    // Action & Assert
    Post::create([
        'content' => 'Test content',
        'user_id' => $user->id,
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('requires content field', function () {
    // Arrange
    $user = User::factory()->create();

    // Action & Assert
    Post::create([
        'title' => 'Test title',
        'user_id' => $user->id,
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('requires user_id field', function () {
    // Action & Assert
    Post::create([
        'title' => 'Test title',
        'content' => 'Test content',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('has timestamps', function () {
    // Arrange
    $user = User::factory()->create();

    // Action
    $post = Post::factory()->create(['user_id' => $user->id]);

    // Assert
    expect($post->created_at)->not->toBeNull()
        ->and($post->updated_at)->not->toBeNull()
        ->and($post->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($post->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('can be updated', function () {

    // Arrange
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);
    $newTitle = 'Updated Post Title';

    // Action
    $post->update(['title' => $newTitle]);

    // Assert
    expect($post->fresh()->title)->toBe($newTitle);
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => $newTitle,
    ]);
});

it('can be deleted', function () {

    // Arrange
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);
    $postId = $post->id;

    // Action
    $post->delete();

    // Assert
    $this->assertDatabaseMissing('posts', ['id' => $postId]);
});

it('factory creates posts with proper structure', function () {
    // Arrange
    $user = User::factory()->create();

    // Action
    $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

    // Assert
    expect($posts)->toHaveCount(3);
    $posts->each(function ($post) use ($user) {
        expect($post->title)->toBeString()
            ->and($post->content)->toBeString()
            ->and($post->user_id)->toBe($user->id);
    });
});

it('can store long content', function () {

    // Arrange
    $user = User::factory()->create();
    $longContent = str_repeat('This is a very long content. ', 100);

    // Action
    $post = Post::create([
        'title' => 'Long Content Test',
        'content' => $longContent,
        'user_id' => $user->id,
    ]);

    // Assert
    expect($post->content)->toBe($longContent);
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'content' => $longContent,
    ]);
});

it('can be found by title', function () {

    // Arrange
    $user = User::factory()->create();
    $uniqueTitle = 'Unique Test Title ' . time();
    $post = Post::factory()->create([
        'title' => $uniqueTitle,
        'user_id' => $user->id,
    ]);

    // Action
    $foundPost = Post::where('title', $uniqueTitle)->first();

    // Assert
    expect($foundPost)->not->toBeNull()
        ->and($foundPost->id)->toBe($post->id)
        ->and($foundPost->title)->toBe($uniqueTitle);
});

it('belongs to a user', function () {

    // Arrange
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    // Action & Assert
    expect($post->user_id)->toBe($user->id);
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'user_id' => $user->id,
    ]);
});
