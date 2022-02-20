<?php
namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;


class TemplateUserExport implements FromView, WithEvents
{

    private $count_data = 0;

    public function view(): View
    {
        $dataUser = User::join('vendor', 'vendor.id', 'users.vendor_id')
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->select('users.name', 'username', 'email', 'vendor.vend_num', 'roles.name as nama_role');

        $data['users'] = $dataUser->get();
        $this->count_data = $dataUser->count();

        return view('exports.excel.template-users', $data);
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $style_header = [
                    //Set font style
                    'font' => [
                        'bold'      =>  true,
                        'color' => ['argb' => 'ffffff'],
                    ],
        
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '66aba3',
                         ]           
                    ],
        
                ];

                $style_group_protected = [
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'ededed',
                         ]           
                    ],
        
                ];

                $arr_columns = range('A', 'F');
                foreach ($arr_columns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $many_data = $this->count_data +1;
                $event->sheet->getDelegate()->getStyle('A1:F1')->applyFromArray($style_header);
                $event->sheet->getDelegate()->getStyle('B2:F'.$many_data)->applyFromArray($style_group_protected);
                // $event->sheet->protectCells('B2:H10', 'PHP');

                // $event->sheet->getProtection()->setPassword('kubota');
                // $event->sheet->getProtection()->setSheet(true);
                // $event->sheet->getStyle('I2:L10')->getProtection()
                // ->setLocked(Protection::PROTECTION_UNPROTECTED);
            },
        ];
    }
}