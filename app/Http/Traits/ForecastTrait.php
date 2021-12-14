<?php
namespace App\Http\Traits;

trait ForecastTrait {

    private function searchPerTgl($date){
        $key = $this->querySearchRangeForecast->filter(function ($value) use($date){
            return false !== stristr($value->tanggal, $date);           
        });
        return [
            'count' => $key->values()->count(),
            'result' => $key->first()
        ];
    }

    private function searchPerRangeTgl($rangeDate){
        $key = $this->querySearchRangeForecast->filter(function($value) use($rangeDate){
            return in_array($value->tanggal, $rangeDate);
        });
        return [
            'count' => $key->values()->count(),
            'result' => $key->sum(function($data){
                return $data->qty;
            })
        ];
    }

    private function searchPerRangeMoon($moon){
        $key = $this->querySearchRangeForecast->filter(function($value) use($moon){
            return $value->bulan == $moon;
        });
        return [
            'count' => $key->values()->count(),
            'result' => $key->first()
        ];
    }

    /**
     * Method untuk memproses perhitungan qty per item jika type forecast adalah days
     * @param string $str --> nama item
     * @throws void
     * @return void
     */
    private function prosesDataPerItem2($str){
        # Mencari semua data forecast berdasarkan nama item untuk proses perhitungan

        $itemForecast = ["<span>{$str}</span>"]; // isi data forecast pertama dengan nama item
        $itemForecastOriginal = [$str];
        $qtyBefore = 0;
        $iterasi = 0;

        foreach ($this->dataTglPerDay as $date){
            # format $date : d-m-Y
            # pencarian qty item berdasarkan tanggal
            $get = $this->searchPerTgl($date);
            
            if($get['count'] > 0){
                # jika ada datanya berdasarkan tanggal yang dituju
                // tambahkah value qty ke data ori
                array_push($itemForecastOriginal, $get['result']->qty);
                // tambahkan value qty ke data asli
                array_push($itemForecast, "<span>".$get['result']->qty."</span>");
            }else{
                # jika tidak ada data
                // tambahkah value qty ke data ori
                array_push($itemForecastOriginal, 0);
                // tambahkan value qty ke data asli
                array_push($itemForecast, 0);
            }
            $iterasi++;
        }
        array_push($itemForecastOriginal, '<span>View</span>');
        array_push($itemForecast, '<span>View</span>');
        array_push($this->resultForecastForOriginal, $itemForecastOriginal);
        array_push($this->resultForecastForDays, $itemForecast);
    }

     /**
     * Method untuk memproses perhitungan qty per item jika type forecast adalah week
     * @param string $str --> nama item
     * @throws void
     * @return void
     */
    private function prosesDataPerItemForWeek2($str){
    
        $itemForecast = ["<span>{$str}</span>"];
        $itemForecastOriginal = [$str];

        foreach($this->dataDatePerWeek as $key => $weekDate){
            foreach($weekDate as $week){
                $get = $this->searchPerRangeTgl($week);
                if($get['count'] > 0){
                    array_push($itemForecastOriginal, $get['result']);
                    array_push($itemForecast, "<span>".$get['result']."</span>");
                }else{
                    array_push($itemForecastOriginal, 0);
                    array_push($itemForecast, 0);
                }
            }
        }
        array_push($itemForecastOriginal, '<span>View</span>');
        array_push($itemForecast, '<span>View</span>');
        array_push($this->resultForecastForOriginal, $itemForecastOriginal);
        array_push($this->resultForecastForWeeks, $itemForecast);
    }

    private function prosesDataPerItemForMoon($str){
        $itemForecast = ["<span>{$str}</span>"];
        $itemForecastOriginal = [$str];

        // 0 => "2021-12"
        // 1 => "2022-01"
        // 2 => "2022-02"
        foreach($this->dataTglPerDay as $moon){
            $get = $this->searchPerRangeMoon($moon);
            if($get['count'] > 0){
                array_push($itemForecastOriginal, $get['result']->qty);
                array_push($itemForecast, "<span>".$get['result']->qty."</span>");
            }else{
                array_push($itemForecastOriginal, 0);
                array_push($itemForecast, 0);
            }
        }
        array_push($itemForecastOriginal, '<span>View</span>');
        array_push($itemForecast, '<span>View</span>');
        array_push($this->resultForecastForOriginal, $itemForecastOriginal);
        array_push($this->resultForecastForMoons, $itemForecast);
    }
}