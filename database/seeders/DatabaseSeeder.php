<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // PDL-050 — registries must be real DB rows before any RegistryGate
        // check (App\Modules\AI\Infrastructure\Registry\RegistryGate) can be
        // meaningful. Must run before any manual/demo data seeder that
        // exercises the AI pre-check flow.
        $this->call(RegistrySeeder::class);
    }
}
