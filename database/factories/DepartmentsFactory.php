<?php

namespace Database\Factories;

use App\Models\Departments;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DepartmentsFactory extends Factory
{
    protected $model = Departments::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'name' => $this->faker->word(),
            'department_head' => User::all()->random(1)->id,
        ];
    }
}
