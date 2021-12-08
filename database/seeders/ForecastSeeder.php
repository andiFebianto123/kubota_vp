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
             "id" => 1,
             "forecast_num" => "Forecast 1",
             "forecast_date" => "2021-12-17 14:33:33",
             "item" => "Item 1",
             "qty" => 12,
            ],
            ["id" => 1],
          ],
          [
            [
             "id" => 2,
             "forecast_num" => "Forecast 2",
             "forecast_date" => "2021-11-17 14:34:13",
             "item" => "Item 3",
             "qty" => 2,
            ],
            ["id" => 2],
          ],
          [
            [
             "id" => 3,
             "forecast_num" => "Forecast 3",
             "forecast_date" => "2021-12-10 14:34:13",
             "vend_num" => "V001303",
             "item" => "Item 2",
             "qty" => 23,
            ],
            ["id" => 3],
          ],
          [
            [
             "id" => 4,
             "forecast_num" => "Forecast 4",
             "forecast_date" => "2021-12-08 14:34:13",
             "item" => "Item 2",
             "qty" => 11,
            ],
            ["id" => 4],
          ],
          [
            [
             "id" => 5,
             "forecast_num" => "Forecast 5",
             "forecast_date" => "2022-01-30 14:34:13",
             "item" => "Item 1",
             "vend_num" => "V001303",
             "qty" => 10,
            ],
            ["id" => 5],
          ],
          [
            [
             "id" => 6,
             "forecast_num" => "Forecast 6",
             "forecast_date" => "2022-01-30 16:18:36",
             "item" => "Item 1",
             "qty" => 65,
            ],
            ["id" => 6],
          ],
          [
            [
             "id" => 7,
             "forecast_num" => "Forecast 7",
             "forecast_date" => "2021-12-07 15:11:35",
             "item" => "Item 4",
             "qty" => 98,
            ],
            ["id" => 7],
          ],
          [
            [
             "id" => 8,
             "forecast_num" => "Forecast 8",
             "forecast_date" => "2021-12-08 15:11:35",
             "item" => "Item 4",
             "qty" => 40,
            ],
            ["id" => 8],
          ],
          [
            [
             "id" => 9,
             "forecast_num" => "Forecast 9",
             "forecast_date" => "2021-12-13 14:34:13",
             "item" => "Item 2",
             "qty" => 9,
            ],
            ["id" => 9],
          ],
          [
            [
             "id" => 10,
             "forecast_num" => "Forecast 10",
             "forecast_date" => "2021-12-20 14:33:33",
             "item" => "Item 1",
             "qty" => 15,
            ],
            ["id" => 10],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          Forecast::updateOrCreate($seed[0],$seed[1]);
       }
    }
}