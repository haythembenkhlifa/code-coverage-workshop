<?php

namespace App\QueryBuilders\Post;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PostQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = Post::query();
    }

    /**
     * Include user relationship.
     */
    public function withUser(): self
    {
        $this->query->with('user');

        return $this;
    }

    /**
     * Order by latest posts.
     */
    public function latest(): self
    {
        $this->query->latest();

        return $this;
    }

    /**
     * Get all posts.
     */
    public function get(): Collection
    {
        return $this->query->get();
    }

    /**
     * Find a specific post by ID.
     */
    public function find(int $id): ?Post
    {
        return $this->query->find($id);
    }

    /**
     * Get the underlying query builder.
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Filter posts that are published (published_at is not null).
     */
    public function onlyPublished(): self
    {
        $this->query->whereNotNull('published_at');

        return $this;
    }

    /**
     * Filter posts that are not published (published_at is null).
     */
    public function onlyUnpublished(): self
    {
        $this->query->whereNull('published_at');

        return $this;
    }

    /**
     * Search posts by title or content.
     */
    public function search(string $term): self
    {
        $this->query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%$term%")
                ->orWhere('content', 'like', "%$term%");
        });

        return $this;
    }
}
