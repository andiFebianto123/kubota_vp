<?php

namespace App\Imports;

use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Role;

use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
// use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
// 
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

// 
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
//
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;




// HeadingRowFormatter::default('none');

// OnEachRow
class UserMasterImport implements OnEachRow, WithHeadingRow //WithValidation //SkipsOnFailure
{

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use Importable; //SkipsFailures;


    public $errorsMessage = [];

    public $dataUsers = [];

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $dataRow  = $row->toArray();

        $validator = Validator::make($dataRow, $this->rules($dataRow));

        if ($validator->fails()) {
            $this->errorsMessage[] = ['row' => $rowIndex, 'message' => collect($validator->errors()->all())->join('<br />')];
            return;
        }else{
            $row = $this->convertRowData($dataRow, $rowIndex);
            $strPasswordRandom = Str::random(8);
            $password = bcrypt($strPasswordRandom);
            $this->dataUsers[] = [
                'name' => $row['name'],
                'username' => $row['user_name'],
                'email' => $row['email'],
                'vendor_id' => $row['vendor_number'],
                'password' => $password,
                'role' => $row['role'],
                // 'is_active' => 1,
                'send_email_by_password' => $strPasswordRandom,
                'title' => 'New User Account',
            ];
            $user = new User;
            $user->name = $row['name'];
            $user->username = $row['user_name'];
            $user->email = $row['email'];
            $user->vendor_id = $row['vendor_number'];
            $user->password = $password;
            if(strtolower($row['status']) == 'active'){
                $user->is_active = 1;
            }else if(strtolower($row['status']) == 'inactive'){
                $user->is_active = 0;
            }else{
                $user->is_active = 0;
            }
            $user->save();
            $user->assignRole([$row['role']]);
        }
    }

  

    private function convertRowData($data, $index)
    {
        if($data['vendor_number'] != null){
            // cek nomor vendor
            $cekVendor = Vendor::where('vend_num', trim($data['vendor_number']))->limit(1);
            if($cekVendor->exists()){
                $data['vendor_number'] = $cekVendor->get()[0]->id;
            }
        }
        return $data;
    }


    // function convertNumbertoDate($str){
    //     return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($str))
    //     ->format('Y-m-d');
    // }

    
    public function rules($data): array
    {
        return [
            'name' => 'required|max:255',
            'user_name' => [
                'required', 
                'max:255',
                function($attribute, $value, $onFailure){
                    if(strlen($value) > 0){
                        if(User::where('username', $value)->exists()){
                            $onFailure("{$attribute} not unique in user master");
                        }
                    }
                }
            ],
            'email' => 'required|max:255',
            'vendor_number' => [
                'required_unless:role,Admin PTKI',
                function($attribute, $value, $onFailure){
                    if(!Vendor::where('vend_num', $value)->exists() && $value != null){
                        $onFailure("{$attribute} is not exists in vendor master");
                    }
                }
            ],
            'role' => [
                'required',
                function($attribute, $value, $onFailure){
                    if(!Role::where('name', $value)->exists()){
                        $onFailure("{$attribute} is not exists in role master");
                    }
                }
            ],
            'status' => [
                'nullable'
            ]
        ];
    }
    
    public function headingRow(): int
    {
        return 1;
    }
}
