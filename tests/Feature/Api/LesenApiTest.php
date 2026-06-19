<?php

namespace Tests\Feature\Api;

use App\Models\AccessRequest;
use App\Models\LesenAttempt;
use App\Models\LesenTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LesenApiTest extends TestCase
{
    use RefreshDatabase;

    /** A user with an approved German B1 access request (mirrors web gating). */
    private function userWithAccess(string $level = 'B1', string $language = 'de'): User
    {
        $user = User::factory()->create(['is_admin' => false]);

        AccessRequest::create([
            'user_id'    => $user->id,
            'language'   => $language,
            'exam'       => 'Telc',
            'level'      => $level,
            'status'     => AccessRequest::STATUS_APPROVED,
            'decided_at' => now(),
        ]);

        return $user->fresh();
    }

    private function makeTopic(array $overrides = []): LesenTopic
    {
        return LesenTopic::create(array_merge([
            'title'        => 'Wohnungssuche',
            'title_ar'     => 'البحث عن سكن',
            'level'        => 'B1',
            'category'     => 'Alltag',
            'is_published' => true,
            'teil3'        => [
                'situations' => [
                    ['id' => 11, 'text' => 'Familie sucht eine Wohnung.'],
                    ['id' => 12, 'text' => 'Student sucht ein WG-Zimmer.'],
                ],
                'ads' => [
                    ['id' => 'a', 'title' => '3-Zimmer-Wohnung', 'text' => '...', 'summary' => 'شقة'],
                    ['id' => 'b', 'title' => 'WG-Zimmer', 'text' => '...', 'summary' => 'غرفة'],
                    ['id' => 'x', 'title' => null, 'text' => '', 'summary' => null],
                ],
                'correctAnswers' => ['11' => 'a', '12' => 'b'],
            ],
        ], $overrides));
    }

    public function test_index_lists_published_topics_that_have_teil3(): void
    {
        $user = $this->userWithAccess();
        $this->makeTopic(['title' => 'Has Teil3', 'slug' => 'has-teil3']);
        $this->makeTopic(['title' => 'No Teil3', 'slug' => 'no-teil3', 'teil3' => null]);
        $this->makeTopic(['title' => 'Unpublished', 'slug' => 'unpub', 'is_published' => false]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/lesen/telc');

        $response->assertOk()->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.slug', 'has-teil3');
    }

    public function test_index_filters_by_user_level(): void
    {
        $user = $this->userWithAccess('B1');
        $this->makeTopic(['slug' => 'b1-topic', 'level' => 'B1']);
        $this->makeTopic(['slug' => 'b2-topic', 'level' => 'B2']);

        $this->actingAs($user, 'sanctum')->getJson('/api/lesen/telc')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'b1-topic');
    }

    public function test_show_returns_teil3_without_the_answer_key(): void
    {
        $user  = $this->userWithAccess();
        $topic = $this->makeTopic(['slug' => 'wohnung']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/lesen/telc/wohnung');

        $response->assertOk()
            ->assertJsonPath('data.teil3.situations.0.id', 11)
            ->assertJsonCount(3, 'data.teil3.ads');

        // The answer key must NOT be exposed on GET.
        $this->assertArrayNotHasKey('correctAnswers', $response->json('data.teil3'));
    }

    public function test_submit_scores_and_persists_attempt(): void
    {
        $user  = $this->userWithAccess();
        $topic = $this->makeTopic(['slug' => 'wohnung']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/lesen/telc/wohnung/submit', [
            'part'    => 'teil3',
            'answers' => ['11' => 'a', '12' => 'x'], // one right (11), one wrong (12)
        ]);

        $response->assertOk()
            ->assertJsonPath('score', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonPath('per_situation.11.isCorrect', true)
            ->assertJsonPath('per_situation.12.isCorrect', false)
            ->assertJsonPath('correctAnswers.12', 'b');

        $this->assertDatabaseHas('lesen_attempts', [
            'user_id'        => $user->id,
            'lesen_topic_id' => $topic->id,
            'part'           => 'teil3',
            'score'          => 1,
            'total'          => 2,
        ]);
    }

    public function test_user_without_access_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]); // no AccessRequest
        $this->makeTopic(['slug' => 'wohnung']);

        $this->actingAs($user, 'sanctum')->getJson('/api/lesen/telc')->assertForbidden();
        $this->actingAs($user, 'sanctum')->getJson('/api/lesen/telc/wohnung')->assertForbidden();
    }

    public function test_attempts_history_lists_user_attempts(): void
    {
        $user  = $this->userWithAccess();
        $topic = $this->makeTopic(['slug' => 'wohnung']);

        LesenAttempt::create([
            'user_id'        => $user->id,
            'lesen_topic_id' => $topic->id,
            'part'           => 'teil3',
            'answers'        => ['11' => 'a'],
            'score'          => 1,
            'total'          => 2,
        ]);

        $this->actingAs($user, 'sanctum')->getJson('/api/me/attempts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.topic.slug', 'wohnung');
    }
}
