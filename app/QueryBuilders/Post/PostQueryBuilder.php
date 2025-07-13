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
}
