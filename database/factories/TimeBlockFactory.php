<?php

namespace Database\Factories;

use App\Models\TimeBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TimeBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = now();
        return [
            'block_date' => $this->faker->date(),
            'block_length' => $this->faker->randomNumber(2),
            'description' => $this->faker->randomNumber(3),
            'category_id' => $this->faker->randomNumber(),
            'user_id' => $this->faker->randomNumber(),
            'created_at' => $now,
            'updated_at' => $now
        ];
    }
}
