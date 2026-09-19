<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'machine_id' => (string) Str::uuid(),
            'name' => 'PC-'.ucfirst($this->faker->word()),
            'os_version' => '10.0.19045',
            'api_token' => Device::generateToken(),
            'policy_version' => 1,
            'status' => Device::STATUS_NEVER,
            'last_seen_at' => null,
            'applied_version' => null,
            'last_heartbeat' => null,
        ];
    }
}
