<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Calendar\RegenerateCalendarSyncTokenAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class CalendarIcsFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_user_can_generate_token_and_fetch_valid_ics_feed(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('profile.calendar-sync.store'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Calendar sync URL berhasil dibuat.');

        $token = (string) DB::table('users')->where('id', $owner->id)->value('calendar_sync_token');

        $this->assertSame(64, strlen($token));

        $this->get(route('calendar.feed', ['token' => $token]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertSee('BEGIN:VCALENDAR', false)
            ->assertSee('BEGIN:VEVENT', false)
            ->assertSee('Rapat: Technical Meeting Seminar Karier', false);
    }

    public function test_invalid_token_returns_empty_calendar(): void
    {
        $this->get(route('calendar.feed', ['token' => 'invalid-token']))
            ->assertOk()
            ->assertSee('BEGIN:VCALENDAR', false)
            ->assertSee('END:VCALENDAR', false)
            ->assertDontSee('BEGIN:VEVENT', false);
    }

    public function test_regenerate_token_invalidates_old_feed_url(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $action = app(RegenerateCalendarSyncTokenAction::class);

        $firstUrl = $action->execute((int) $user->id);
        $firstToken = basename(parse_url($firstUrl, PHP_URL_PATH), '.ics');

        $secondUrl = $action->execute((int) $user->id);
        $secondToken = basename(parse_url($secondUrl, PHP_URL_PATH), '.ics');

        $this->assertNotSame($firstToken, $secondToken);

        $this->get(route('calendar.feed', ['token' => $firstToken]))
            ->assertOk()
            ->assertDontSee('BEGIN:VEVENT', false);

        $this->get(route('calendar.feed', ['token' => $secondToken]))
            ->assertOk()
            ->assertSee('BEGIN:VEVENT', false);
    }

    public function test_feed_is_scoped_to_user_organizations(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        DB::table('meetings')->insert([
            'organization_id' => $this->organizationId('hima-informatika'),
            'project_id' => null,
            'created_by_user_id' => null,
            'title' => 'Rapat Rahasia HMIF',
            'agenda' => 'Tidak boleh muncul di feed BEM.',
            'location' => 'Ruang HMIF',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHour(),
            'status' => 'planned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $feedUrl = app(RegenerateCalendarSyncTokenAction::class)->execute((int) $owner->id);
        $token = basename(parse_url($feedUrl, PHP_URL_PATH), '.ics');

        $this->get(route('calendar.feed', ['token' => $token]))
            ->assertOk()
            ->assertSee('Technical Meeting Seminar Karier', false)
            ->assertDontSee('Rapat Rahasia HMIF', false);
    }

    public function test_profile_page_receives_calendar_sync_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        app(RegenerateCalendarSyncTokenAction::class)->execute((int) $owner->id);

        $this->actingAs($owner)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Profile/Edit')
                ->where('calendarSync.enabled', true)
                ->whereType('calendarSync.feedUrl', 'string'));
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
