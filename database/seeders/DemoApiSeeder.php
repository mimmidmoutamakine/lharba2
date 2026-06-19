<?php

namespace Database\Seeders;

use App\Models\AccessRequest;
use App\Models\LesenTopic;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data for exercising the mobile API locally.
 *   Login: demo@lharba.tech / password123  (approved Telc B1 access)
 * Run:  php artisan db:seed --class=DemoApiSeeder
 */
class DemoApiSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@lharba.tech'],
            ['name' => 'Demo Student', 'password' => 'password123', 'is_admin' => false],
        );

        // Approved German B1 access (so the API gate lets them in).
        if (! $user->accessRequests()->where('status', AccessRequest::STATUS_APPROVED)->exists()) {
            AccessRequest::create([
                'user_id'    => $user->id,
                'language'   => 'de',
                'exam'       => 'Telc',
                'level'      => 'B1',
                'status'     => AccessRequest::STATUS_APPROVED,
                'decided_at' => now(),
            ]);
        }

        LesenTopic::updateOrCreate(
            ['slug' => 'wohnungssuche-demo'],
            [
                'title'        => 'Wohnungssuche & Alltag (Demo)',
                'title_ar'     => 'البحث عن سكن (تجريبي)',
                'level'        => 'B1',
                'category'     => 'Alltag',
                'is_published' => true,
                'teil3'        => [
                    'situations' => [
                        ['id' => 11, 'text' => 'Eine Familie mit zwei Kindern sucht eine große Wohnung mit Garten.'],
                        ['id' => 12, 'text' => 'Ein Student sucht ein günstiges WG-Zimmer in der Nähe der Uni.'],
                        ['id' => 13, 'text' => 'Ein Rentnerpaar möchte eine ruhige Wohnung im Erdgeschoss.'],
                        ['id' => 14, 'text' => 'Jemand sucht einen Stellplatz für sein Auto in der Innenstadt.'],
                    ],
                    'ads' => [
                        ['id' => 'a', 'title' => '4-Zimmer-Haus mit Garten', 'text' => 'Großes Reihenhaus, ideal für Familien, eigener Garten.', 'summary' => 'منزل واسع بحديقة مناسب للعائلات'],
                        ['id' => 'b', 'title' => 'WG-Zimmer nahe Campus', 'text' => 'Günstiges Zimmer in 3er-WG, 5 Min. zur Universität.', 'summary' => 'غرفة رخيصة قرب الجامعة'],
                        ['id' => 'c', 'title' => 'Erdgeschosswohnung, ruhig', 'text' => 'Barrierefrei, ruhige Lage, ideal für Senioren.', 'summary' => 'شقة هادئة في الطابق الأرضي'],
                        ['id' => 'd', 'title' => 'Fitnessstudio-Mitgliedschaft', 'text' => 'Neueröffnung, erste Woche gratis.', 'summary' => 'اشتراك في نادٍ رياضي'],
                        ['id' => 'x', 'title' => null, 'text' => '', 'summary' => null],
                    ],
                    'correctAnswers' => ['11' => 'a', '12' => 'b', '13' => 'c', '14' => 'x'],
                ],
            ],
        );

        $this->command?->info('Demo data ready — login demo@lharba.tech / password123');
    }
}
