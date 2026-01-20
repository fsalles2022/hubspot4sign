<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HubspotSnapshot;
use Carbon\Carbon;

class HubspotSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $portalId = 123456;

        for ($i = 6; $i >= 0; $i--) {
            HubspotSnapshot::updateOrCreate(
                [
                    'portal_id' => $portalId,
                    'snapshot_date' => Carbon::now()->subDays($i)->toDateString(),
                ],
                [
                    'company_name' => 'Minha Empresa',
                    'region' => 'na1',
                    'timezone' => 'America/Sao_Paulo',
                    'contacts' => rand(5, 50),
                    'companies' => rand(2, 20),
                    'deals' => rand(1, 10),
                ]
            );
        }

        $this->command->info('✅ Snapshots falsos gerados com sucesso!');
    }
}
