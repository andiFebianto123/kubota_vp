<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuration;
use App\Models\Forecast;

class ForecastSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
              "forecast_num" => "Forecast 1",
            ],
            ["forecast_num" => "Forecast 1"],
          ],
          [
            [
              "forecast_num" => "Forecast 2",
            ],
            ["forecast_num" => "Forecast 2"],
          ],
          [
            [
              "forecast_num" => "Forecast 3",
            ],
            ["forecast_num" => "Forecast 3"],
          ],
          [
            [
              "forecast_num" => "Forecast 4",
            ],
            ["forecast_num" => "Forecast 4"],
          ],
          [
            [
              "forecast_num" => "Forecast 5",
            ],
            ["forecast_num" => "Forecast 5"],
          ],
        
        ];

       foreach($arr_seeders as $key => $seed) {
            Forecast::updateOrCreate($seed[0],$seed[1]);
       }
    }
}