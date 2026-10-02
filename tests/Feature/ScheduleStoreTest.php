<?php

namespace Tests\Feature;

use App\Models\BlacklistProfile;
use App\Models\ScheduleWindow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleStoreTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    private function profile(): BlacklistProfile
    {
        return BlacklistProfile::create([
            'name' => 'Test',
            'slug' => 'test-'.uniqid(),
            'dns_mode' => BlacklistProfile::DNS_MODE_LOCAL_FILTER,
            'enabled' => true,
        ]);
    }

    public function test_it_creates_one_window_per_selected_day(): void
    {
        $this->admin();
        $profile = $this->profile();

        $response = $this->post(route('schedules.store'), [
            'profile_id' => $profile->id,
            'days' => [1, 2, 3, 4, 5],
            'start_time' => '19:00',
            'end_time' => '22:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertSame(5, ScheduleWindow::where('profile_id', $profile->id)->count());

        foreach ([1, 2, 3, 4, 5] as $day) {
            $this->assertDatabaseHas('schedule_windows', [
                'profile_id' => $profile->id,
                'day_of_week' => $day,
                'start_time' => '19:00',
                'end_time' => '22:00',
                'enabled' => true,
            ]);
        }
    }

    public function test_it_does_not_duplicate_existing_slots(): void
    {
        $this->admin();
        $profile = $this->profile();

        $payload = [
            'profile_id' => $profile->id,
            'days' => [1, 2, 3, 4, 5],
            'start_time' => '19:00',
            'end_time' => '22:00',
        ];

        $this->post(route('schedules.store'), $payload)->assertRedirect();
        $this->post(route('schedules.store'), $payload)->assertRedirect();

        $this->assertSame(5, ScheduleWindow::where('profile_id', $profile->id)->count());
    }

    public function test_it_rejects_invalid_input(): void
    {
        $this->admin();

        $this->post(route('schedules.store'), [
            'profile_id' => 999999,
            'days' => [],
            'start_time' => '19:00',
            'end_time' => '22:00',
        ])->assertSessionHasErrors(['profile_id', 'days']);

        $this->post(route('schedules.store'), [
            'profile_id' => $this->profile()->id,
            'days' => [9],
            'start_time' => '19:00',
            'end_time' => '22:00',
        ])->assertSessionHasErrors('days.0');

        $this->post(route('schedules.store'), [
            'profile_id' => $this->profile()->id,
            'days' => [1],
            'start_time' => '22:00',
            'end_time' => '19:00',
        ])->assertSessionHasErrors('end_time');
    }
}
