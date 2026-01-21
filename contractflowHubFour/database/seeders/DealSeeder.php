<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deal;
use App\Models\Client;

class DealSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        if (!$client) {
            $this->command->warn('Nenhum client encontrado. Deal não criado.');
            return;
        }

        Deal::create([
            'title' => 'Primeira Venda',
            'value' => 3000,
            'status' => 'open',
            'client_id' => $client->id,
            'company_id' => $client->company_id,
        ]);
    }
}
