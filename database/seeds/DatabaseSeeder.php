<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateTables([
            'users',
            'document_types',
            'client_services',
            'locals',
            'clients',
            'order_subservices',
            'service_subservices',
            'services',
            'subservices',
            'orders',
            'order_details',
            'evaluation_details',
            'evaluations',
            'production_details',
            'productions',
            'transfer_details',
            'transfers',
            'diagnostic_details',
            'diagnostics',
            'sexage_details',
            'sexages',
            'delivery_details',
            'deliveries'
        ]);

        $this->call(UserSeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(SubserviceSeeder::class);
        $this->call(ServiceSubserviceSeeder::class);
        $this->call(ClientSeeder::class);
    }

    public function truncateTables(array $tables)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }
}
