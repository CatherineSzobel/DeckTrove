<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'commentable_type' => 'user',
            'commentable_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }

    public function by(User $author): static
    {
        return $this->state(['user_id' => $author->id]);
    }

    /** A comment on this user's profile. */
    public function on(User $profile): static
    {
        return $this->state(['commentable_type' => 'user', 'commentable_id' => $profile->id]);
    }
}
