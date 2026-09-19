<?php

namespace Database\Factories;

use App\Models\BlacklistRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlacklistRuleFactory extends Factory
{
    protected $model = BlacklistRule::class;

    public function definition(): array
    {
        return [
            'device_id' => null,
            'domain' => $this->faker->domainName(),
            'type' => BlacklistRule::TYPE_EXACT,
            'enabled' => true,
        ];
    }
}
