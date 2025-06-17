<?php

namespace Database\Seeders;

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
        // First seed countries as they are independent
        $this->call(CountriesTableSeeder::class);
        
        // Then seed users which creates the first company
        $this->call(UsersTableSeeder::class);
        
        // Finally seed currencies for all companies
        $this->call(CurrenciesTableSeeder::class);
        
        // Run company seeder to ensure default data is set up
        $this->call(CompanySeeder::class);
    }
}
