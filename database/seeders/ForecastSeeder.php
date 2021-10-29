<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Forecast;

class ForecastSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
             "forecast_num" => "Forecast 1",
             "qty" => "12",
            ],
            ["forecast_num" => "Forecast 1"],
          ],
          [
            [
             "forecast_num" => "Forecast 2",
             "qty" => "2",
            ],
            ["forecast_num" => "Forecast 2"],
          ],
          [
            [
             "forecast_num" => "Forecast 3",
             "qty" => "23",
            ],
            ["forecast_num" => "Forecast 3"],
          ],
          [
            [
             "forecast_num" => "Forecast 4",
             "qty" => "11",
            ],
            ["forecast_num" => "Forecast 4"],
          ],
          [
            [
             "forecast_num" => "Forecast 5",
             "qty" => "10",
            ],
            ["forecast_num" => "Forecast 5"],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          Forecast::updateOrCreate($seed[0],$seed[1]);
       }
    }
}