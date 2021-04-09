<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = now();
        return [
            'name' => $this->faker->sentence(3),
            'target_percentage' => $this->faker->randomNumber(2),
            'user_id' => $this->faker->randomNumber(),
            'created_at' => $now,
            'updated_at' => $now
        ];
    }
}
