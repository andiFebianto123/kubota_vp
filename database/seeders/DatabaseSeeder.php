<?php

namespace Database\Seeders;

use App\Models\Forecast;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(VendorSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(GeneralMessageSeeder::class);
        
        $this->call(ConfigurationSeeder::class);
        $this->call(PurchaseOrderSeeder::class);
        $this->call(PurchaseOrderLineSeeder::class);
        $this->call(DeliverySeeder::class);
        $this->call(DeliveryStatusSeeder::class);
        // $this->call(TempUploadDeliverySeeder::class);
        // $this->call(ForecastSeeder::class);
    }
}
