<?php

namespace App\Http\Controllers;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\PublishPostAction;
use App\Actions\Post\UpdatePostAction;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\QueryBuilders\Post\PostQueryBuilder;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    /**
     * Display a listing of the posts.
     */
    public function index(PostQueryBuilder $queryBuilder): PostCollection
    {
        $posts = $queryBuilder
            ->withUser()
            ->latest()
            ->get();

        return new PostCollection($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request, CreatePostAction $action): JsonResponse
    {
        $post = $action->execute($request->validated());

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post'
            ], 500);
        }

        return (new PostResource($post))
            ->additional([
                'success' => true,
                'message' => 'Post created successfully'
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): PostResource
    {
        $post->load('user');

        return new PostResource($post);
    }

    /**
     * Update the specified post.
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): JsonResponse
    {
        $updatedPost = $action->execute($post, $request->validated());

        if (!$updatedPost) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post'
            ], 500);
        }

        return (new PostResource($updatedPost))
            ->additional([
                'success' => true,
                'message' => 'Post updated successfully'
            ])
            ->response();
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }

    /**
     * Publish the specified post.
     */
    public function publish(Post $post, PublishPostAction $action): JsonResponse
    {
        $published = $action->execute($post);

        if (!$published) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish post'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Post published successfully',
            'data' => new PostResource($published)
        ]);
    }
}
