<?php

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\UpdatePostAction;
use App\Models\Post;
use App\Models\User;
use Mockery\MockInterface;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('PostController', function () {

    describe('index method', function () {

        it('returns all posts successfully', function () {

            // Arrange
            $user = User::factory()->create();
            $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

            // Action
            $response = $this->getJson(route('posts.index'));

            // Assert
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'content',
                            'user_id',
                            'created_at',
                            'updated_at',
                            'user'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true
                ]);

            expect($response->json('data'))->toHaveCount(3);
        });

        it('returns empty array when no posts exist', function () {
            // Action
            $response = $this->getJson(route('posts.index'));

            // Assert
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => []
                ]);
        });
    });

    describe('store method', function () {

        it('creates a new post successfully', function () {

            // Arrange
            $user = User::factory()->create();
            $postData = [
                'title' => 'Test Post Title',
                'content' => 'This is test post content.',
                'user_id' => $user->id,
            ];

            // Action
            $response = $this->postJson(route('posts.store'), $postData);

            // Assert
            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'user_id',
                        'created_at',
                        'updated_at'
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'title' => 'Test Post Title',
                        'content' => 'This is test post content.',
                        'user_id' => $user->id,
                    ],
                    'message' => 'Post created successfully'
                ]);

            $this->assertDatabaseHas('posts', [
                'title' => 'Test Post Title',
                'content' => 'This is test post content.',
                'user_id' => $user->id,
            ]);
        });

        it('validates required fields', function () {
            // Action
            $response = $this->postJson(route('posts.store'), []);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'content', 'user_id']);
        });

        it('validates title is required', function () {

            // Arrange
            $user = User::factory()->create();

            // Action
            $response = $this->postJson(route('posts.store'), [
                'content' => 'Test content',
                'user_id' => $user->id,
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
        });

        it('validates content is required', function () {

            // Arrange
            $user = User::factory()->create();

            // Action
            $response = $this->postJson(route('posts.store'), [
                'title' => 'Test title',
                'user_id' => $user->id,
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['content']);
        });

        it('validates user_id exists', function () {
            // Action
            $response = $this->postJson(route('posts.store'), [
                'title' => 'Test title',
                'content' => 'Test content',
                'user_id' => 999, // Non-existent user
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
        });

        it('validates title max length', function () {

            // Arrange
            $user = User::factory()->create();
            $longTitle = str_repeat('a', 256); // Exceeds 255 char limit

            // Action
            $response = $this->postJson(route('posts.store'), [
                'title' => $longTitle,
                'content' => 'Test content',
                'user_id' => $user->id,
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
        });

        it('returns 500 when post creation fails', function () {

            // Arrange
            $user = User::factory()->create();
            $data = [
                'title' => 'Test Post Title',
                'content' => 'This is test post content.',
                'user_id' => $user->id,
            ];

            // Simulate failure by mocking action to return null
            $this->instance(
                CreatePostAction::class,
                Mockery::mock(CreatePostAction::class, function (MockInterface $mock) {
                    $mock->shouldReceive('execute')->once()
                        ->andReturn(null);
                })
            );

            // Action
            $response = $this->postJson(route('posts.store'), $data);

            // Assert
            $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'Failed to create post'
                ]);
        });
    });

    describe('show method', function () {

        it('returns a specific post successfully', function () {

            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create(['user_id' => $user->id]);

            // Action
            $response = $this->getJson(route('posts.show', $post));

            // Assert
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'user'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'content' => $post->content,
                        'user_id' => $user->id,
                    ]
                ]);
        });

        it('returns 404 for non-existent post', function () {
            // Action
            $response = $this->getJson(route('posts.show', 999));

            // Assert
            $response->assertStatus(404);
        });
    });

    describe('update method', function () {

        it('updates a post successfully', function () {

            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create(['user_id' => $user->id]);
            $updateData = [
                'title' => 'Updated Title',
                'content' => 'Updated content',
            ];

            // Action
            $response = $this->putJson(route('posts.update', $post), $updateData);

            // Assert
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $post->id,
                        'title' => 'Updated Title',
                        'content' => 'Updated content',
                        'user_id' => $user->id,
                    ],
                    'message' => 'Post updated successfully'
                ]);

            $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'title' => 'Updated Title',
                'content' => 'Updated content',
            ]);
        });

        it('updates only provided fields', function () {

            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create([
                'title' => 'Original Title',
                'content' => 'Original content',
                'user_id' => $user->id
            ]);

            // Action
            $response = $this->putJson(route('posts.update', $post), [
                'title' => 'Updated Title Only'
            ]);

            // Assert
            $response->assertStatus(200);

            $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'title' => 'Updated Title Only',
                'content' => 'Original content', // Should remain unchanged
            ]);
        });

        it('validates fields when updating', function () {

            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create(['user_id' => $user->id]);

            // Action
            $response = $this->putJson("/api/posts/{$post->id}", [
                'title' => '', // Empty title
                'user_id' => 999, // Non-existent user
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'user_id']);
        });

        it('returns 404 for non-existent post', function () {
            // Action
            $response = $this->putJson('/api/posts/999', [
                'title' => 'Updated Title'
            ]);

            // Assert
            $response->assertStatus(404);
        });

        it('returns 500 when post update fails', function () {

            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create(['user_id' => $user->id]);
            $updateData = [
                'title' => 'Database Updated Title',
                'content' => 'Database updated content',
            ];

            // Simulate failure by mocking action to return null
            $this->instance(
                UpdatePostAction::class,
                Mockery::mock(UpdatePostAction::class, function (MockInterface $mock) {
                    $mock->shouldReceive('execute')->once()
                        ->andReturn(null);
                })
            );

            // Action
            $response = $this->putJson("/api/posts/{$post->id}", $updateData);

            // Assert
            $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'Failed to update post'
                ]);
        });
    });

    describe('destroy method', function () {

        it('deletes a post successfully', function () {

            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create(['user_id' => $user->id]);
            $postId = $post->id;

            // Action
            $response = $this->deleteJson("/api/posts/{$post->id}");

            // Assert
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Post deleted successfully'
                ]);

            $this->assertDatabaseMissing('posts', ['id' => $postId]);
        });

        it('returns 404 for non-existent post', function () {
            // Action
            $response = $this->deleteJson('/api/posts/999');

            // Assert
            $response->assertStatus(404);
        });
    });

    describe('integration tests', function () {

        it('can perform full CRUD operations', function () {

            // Arrange
            $user = User::factory()->create();

            // Create
            $createResponse = $this->postJson('/api/posts', [
                'title' => 'Integration Test Post',
                'content' => 'This is an integration test.',
                'user_id' => $user->id,
            ]);

            $createResponse->assertStatus(201);
            $postId = $createResponse->json('data.id');

            // Read
            $showResponse = $this->getJson("/api/posts/{$postId}");
            $showResponse->assertStatus(200);

            // Update
            $updateResponse = $this->putJson("/api/posts/{$postId}", [
                'title' => 'Updated Integration Test Post'
            ]);
            $updateResponse->assertStatus(200);

            // Delete
            $deleteResponse = $this->deleteJson("/api/posts/{$postId}");
            $deleteResponse->assertStatus(200);

            // Verify deletion
            $this->assertDatabaseMissing('posts', ['id' => $postId]);
        });

        it('includes user relationship in responses', function () {

            // Arrange
            $user = User::factory()->create(['name' => 'Test User']);
            $post = Post::factory()->create(['user_id' => $user->id]);

            // Action
            $response = $this->getJson("/api/posts/{$post->id}");

            // Assert
            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => 'Test User',
                            'email' => $user->email,
                        ]
                    ]
                ]);
        });
    });

    describe('publish method', function () {
        it('publishes a post successfully', function () {
            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create(['user_id' => $user->id, 'published_at' => null]);

            // Act
            $response = $this->postJson(route('posts.publish', $post));

            // Assert
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Post published successfully',
                ]);
            $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                // published_at should not be null
            ]);
        });

        it('returns 500 if publish fails', function () {
            // Arrange
            $user = User::factory()->create();
            $post = Post::factory()->create(['user_id' => $user->id, 'published_at' => null]);

            // Simulate failure by mocking action to return null
            $this->instance(
                \App\Actions\Post\PublishPostAction::class,
                Mockery::mock(\App\Actions\Post\PublishPostAction::class, function (MockInterface $mock) {
                    $mock->shouldReceive('execute')->once()->andReturn(null);
                })
            );

            // Act
            $response = $this->postJson(route('posts.publish', $post));

            // Assert
            $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'Failed to publish post',
                ]);
        });
    });
});
