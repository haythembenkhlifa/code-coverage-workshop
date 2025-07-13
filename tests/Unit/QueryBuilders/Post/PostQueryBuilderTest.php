<?php

use App\Models\Post;
use App\Models\User;
use App\QueryBuilders\Post\PostQueryBuilder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('PostQueryBuilder', function () {

    it('can be instantiated', function () {
        // Action
        $queryBuilder = new PostQueryBuilder();

        // Assert
        expect($queryBuilder)->toBeInstanceOf(PostQueryBuilder::class);
        expect($queryBuilder->getQuery())->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);
    });

    it('can include user relationship', function () {

        // Arrange
        $user = User::factory()->create();
        Post::factory()->create(['user_id' => $user->id]);
        $queryBuilder = new PostQueryBuilder();

        // Action
        $posts = $queryBuilder
            ->withUser()
            ->get();

        // Assert
        expect($posts->first()->relationLoaded('user'))->toBeTrue();
        expect($posts->first()->user)->toBeInstanceOf(User::class);
    });

    it('can order posts by latest', function () {

        // Arrange
        $user = User::factory()->create();
        $firstPost = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(2)
        ]);
        $secondPost = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDay()
        ]);
        $thirdPost = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()
        ]);
        $queryBuilder = new PostQueryBuilder();

        // Action
        $posts = $queryBuilder
            ->latest()
            ->get();

        // Assert
        expect($posts->first()->id)->toBe($thirdPost->id);
        expect($posts->last()->id)->toBe($firstPost->id);
    });

    it('can chain methods fluently', function () {

        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['user_id' => $user->id]);
        $queryBuilder = new PostQueryBuilder();

        // Action
        $posts = $queryBuilder
            ->withUser()
            ->latest()
            ->get();

        // Assert
        expect($posts)->toHaveCount(3);
        expect($posts->first()->relationLoaded('user'))->toBeTrue();
    });

    it('can find a specific post by id', function () {

        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $queryBuilder = new PostQueryBuilder();

        // Action
        $foundPost = $queryBuilder->find($post->id);

        // Assert
        expect($foundPost)->not->toBeNull();
        expect($foundPost->id)->toBe($post->id);
        expect($foundPost->title)->toBe($post->title);
    });

    it('returns null when post is not found', function () {

        // Arrange
        $queryBuilder = new PostQueryBuilder();

        // Action
        $foundPost = $queryBuilder->find(999);

        // Assert
        expect($foundPost)->toBeNull();
    });

    it('can find post with user relationship', function () {

        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $queryBuilder = new PostQueryBuilder();

        // Action
        $foundPost = $queryBuilder
            ->withUser()
            ->find($post->id);

        // Assert
        expect($foundPost)->not->toBeNull();
        expect($foundPost->relationLoaded('user'))->toBeTrue();
        expect($foundPost->user->id)->toBe($user->id);
    });

    it('returns empty collection when no posts exist', function () {
        // Arrange
        $queryBuilder = new PostQueryBuilder();

        // Action
        $posts = $queryBuilder->get();

        // Assert
        expect($posts)->toBeEmpty();
        expect($posts)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    });

    it('maintains query builder state across method calls', function () {

        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(2)->create(['user_id' => $user->id]);
        $queryBuilder = new PostQueryBuilder();

        // Action
        $queryBuilder->withUser();
        $queryBuilder->latest();
        $posts = $queryBuilder->get();

        // Assert
        expect($posts)->toHaveCount(2);
        expect($posts->first()->relationLoaded('user'))->toBeTrue();
    });

    it('can access underlying query builder', function () {
        // Arrange
        $queryBuilder = new PostQueryBuilder();

        // Action
        $query = $queryBuilder->getQuery();

        // Assert
        expect($query)->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);
        expect($query->getModel())->toBeInstanceOf(Post::class);
    });
});
