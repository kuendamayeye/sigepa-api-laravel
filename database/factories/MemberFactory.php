<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $active = fake()->boolean(85);

        return [
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(['Male', 'Female']),
            'birth_date' => fake()->date(),
            'bi' => strtoupper(fake()->bothify('??######??#')),
            'phone_number' => '9'.fake()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'residence_province_id' => 'prov-luanda',
            'residence_municipality_id' => 'mun-belas',
            'status' => $active ? 'active' : 'inactive',
            'is_active' => $active,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }
}
