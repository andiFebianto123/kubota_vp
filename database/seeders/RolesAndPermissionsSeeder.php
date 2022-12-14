<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // $this->createRole();

        $this->createPermission();

        // $this->assignRolePermission();

        // $this->assignUserRole();

        // $this->assignUserRole();

        // $this->assignRolePermission();
    }

    function createRole(){
        $arr_seeders = [
            [
              [
                "id" => 1,
                "name" => "Admin PTKI",
                "guard_name" => "web",
              ],
              ["id" => 1],
            ],
            [
              [
                "id" => 2,
                "name" => "Marketing Vendor",
                "guard_name" => "web",
              ],
              ['id' => 2],
            ],
            [
              [
                "id" => 3,
                "name" => "Finance Vendor",
                "guard_name" => "web",
              ],
              ['id' => 3],
            ],
            [
                [
                    'id' => 4,
                    'name' => 'Warehouse Vendor',
                    'guard_name' => 'web'
                ],
                ['id' => 4]
            ]
          ];
  
        foreach($arr_seeders as $key => $seed) {
            Role::updateOrCreate($seed[0],$seed[1]);
        }
    }

    function createPermission(){
        $arrPermission = [
            [
                [
                  "name" => "Show Payment Status DS",
                  "guard_name" => "web",
                  "description" => "Mempunyai akses untuk melihat panel Payment Status pada Delivery Sheet"
                ],
                ['name' => 'Show Payment Status DS'],
            ],
            [
              [
                "name" => "Read dashboard",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk melihat halaman dashboard"
              ],
              ['name' => 'Read dashboard'],
            ],
            [
              [
                "name" => "Read Purchase Order",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk melihat data di halaman purchase order"
              ],
              ['name' => 'Read Purchase Order'],
            ],
            [
              [
                "name" => "Export Purchase Order",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk export data purchase order"
              ],
              ['name' => 'Export Purchase Order'],
            ],
            [
              [
                "name" => "Import Purchase Order",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk import data purchase order"
              ],
              ['name' => 'Import Purchase Order'],
            ],
            [
              [
                "name" => "Send Mail New PO",
                "guard_name" => "web",
                "description" => "Mempunyai akses kirim email di semua vendor jika ada new PO"
              ],
              ['name' => 'Send Mail New PO'],
            ],
            [
              [
                "name" => "Read PO Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses print order sheet, accept, reject, view detail PO Line dan view detail PO Change History"
              ],
              ['name' => 'Read PO Detail'],
            ],
            [
              [
                "name" => "Accept PO Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses accept data PO"
              ],
              ['name' => 'Accept PO Detail'],
            ],
            [
              [
                "name" => "Reject PO Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses reject data PO"
              ],
              ['name' => 'Reject PO Detail'],
            ],
            [
              [
                "name" => "Unread PO Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses unread pada PO detail"
              ],
              ['name' => 'Unread PO Detail'],
            ],
            [
              [
                "name" => "Read PO Line Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses melihat halaman detail Po Line & Delivery Sheet Detail"
              ],
              ['name' => 'Read PO Line Detail'],
            ],
            [
              [
                "name" => "Create Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses membuat data Delivery Sheet"
              ],
              ['name' => 'Create Delivery Sheet'],
            ],
            [
              [
                "name" => "Print Label Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses mencetak label data Delivery Sheet"
              ],
              ['name' => 'Print Label Delivery Sheet'],
            ],
            [
              [
                "name" => "Delete Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses menghapus data Delivery Sheet"
              ],
              ['name' => 'Delete Delivery Sheet'],
            ],
            [
              [
                "name" => "Read Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses melihat Delivery Sheet"
              ],
              ['name' => 'Read Delivery Sheet'],
            ],
            [
              [
                "name" => "Read Delivery Sheet in Table",
                "guard_name" => "web",
                "description" => "Mempunyai akses melihat detail delivery sheet"
              ],
              ['name' => 'Read Delivery Sheet in Table'],
            ],
            [
              [
                "name" => "Delete Delivery Sheet in Table",
                "guard_name" => "web",
                "description" => "Mempunyai akses menghapus tombol delete di delivery sheet"
              ],
              ['name' => 'Delete Delivery Sheet in Table'],
            ],
            [
              [
                "name" => "Print Label",
                "guard_name" => "web",
                "description" => "Mempunyai akses mencetak label data delivery sheet"
              ],
              ['name' => 'Print Label'],
            ],
            [
              [
                "name" => 'Read Delivery Status in Table',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuka delivery status'
              ],
              ['name' => 'Read Delivery Status in Table']
            ],
            [
              [
                "name" => 'Read Summary MO',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuka Summary MO'
              ],
              ['name' => 'Read Summary MO']
            ],
            [
              [
                "name" => 'Read List Payment',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuka List Payment'
              ],
              ['name' => 'Read List Payment']
            ],
            [
              [
                "name" => 'Download Button List Payment',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses download file faktur pajak, invoice dan surat jalan'
              ],
              ['name' => 'Download Button List Payment']
            ],
            [
              [
                "name" => 'Create Invoice and Tax',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuat faktur pajak'
              ],
              ['name' => 'Create Invoice and Tax']
            ],
            [
              [
                "name" => 'Read Forecast',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuka halaman Forecast'
              ],
              ['name' => 'Read Forecast']
            ],
            [
              [
                "name" => 'Read Vendor',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuka halaman Vendor'
              ],
              ['name' => 'Read Vendor']
            ],
            [
              [
                "name" => 'Update Vendor',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses edit data vendor'
              ],
              ['name' => 'Update Vendor']
            ],
            [
              [
                "name" => 'Create Vendor',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuat data vendor'
              ],
              ['name' => 'Create Vendor']
            ],
            [
              [
                "name" => 'Delete Vendor',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses menghapus data vendor'
              ],
              ['name' => 'Delete Vendor']
            ],
            [
              [
                "name" => 'Read User',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat halaman User'
              ],
              ['name' => 'Read User']
            ],
            [
              [
                "name" => 'Update User',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses mengubah data User'
              ],
              ['name' => 'Update User']
            ],
            [
              [
                "name" => 'Create User',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuat data User'
              ],
              ['name' => 'Create User']
            ],
            [
              [
                "name" => 'Delete User',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses menghapus data User'
              ],
              ['name' => 'Delete User']
            ],
            [
              [
                "name" => 'Read General Message',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat halaman General Message'
              ],
              ['name' => 'Read General Message']
            ],
            [
              [
                "name" => 'Update General Message',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses mengubah data General Message'
              ],
              ['name' => 'Update General Message']
            ],
            [
              [
                "name" => 'Create General Message',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuat data General Message'
              ],
              ['name' => 'Create General Message']
            ],
            [
              [
                "name" => 'Delete General Message',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses menghapus data General Message'
              ],
              ['name' => 'Delete General Message']
            ],
            //
            [
              [
                "name" => 'Read Configuration',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat halaman Configuration'
              ],
              ['name' => 'Read Configuration']
            ],
            [
              [
                "name" => 'Update Configuration',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses mengubah data Configuration'
              ],
              ['name' => 'Update Configuration']
            ],
            [
              [
                "name" => 'Create Configuration',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuat data Configuration'
              ],
              ['name' => 'Create Configuration']
            ],
            [
              [
                "name" => 'Delete Configuration',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses menghapus data Configuration'
              ],
              ['name' => 'Delete Configuration']
            ],
            // permission role
            [
              [
                "name" => 'Read Role',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat halaman Role'
              ],
              ['name' => 'Read Role']
            ],
            [
              [
                "name" => 'Update Role',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses mengubah data Role'
              ],
              ['name' => 'Update Role']
            ],
            [
              [
                "name" => 'Create Role',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses membuat data Role'
              ],
              ['name' => 'Create Role']
            ],
            [
              [
                "name" => 'Delete Role',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses menghapus data Role'
              ],
              ['name' => 'Delete Role']
            ],
            // permission for Permission
            [
              [
                "name" => 'Read Permission',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat data Permission'
              ],
              ['name' => 'Read Permission']
            ],
            [
              [
                "name" => 'Read History Summary MO',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat data Mo di menu history mo'
              ],
              ['name' => 'Read History Summary MO']
            ],
            [
              [
                "name" => 'Show Price In Delivery Sheet Menu',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat harga di menu delivery sheet'
              ],
              ['name' => 'Show Price In Delivery Sheet Menu']
            ],
            [
              [
                "name" => 'Show Price In Delivery Status Menu',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat harga di menu delivery status'
              ],
              ['name' => 'Show Price In Delivery Status Menu']
            ],
            [
              [
                "name" => 'Show Price In PO Menu',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat harga di menu po'
              ],
              ['name' => 'Show Price In PO Menu']
            ],
            [
              [
                "name" => 'Show Price In List Payment Menu',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses melihat harga di menu list payment'
              ],
              ['name' => 'Show Price In List Payment Menu']
            ],
            [
              [
                "name" => 'Access Reset Password',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses untuk melakukan reset password'
              ],
              ['name' => 'Access Reset Password']
            ],
            [
              [
                "name" => 'Access Reset Attempt Login',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses untuk melakukan reset attempt login'
              ],
              ['name' => 'Access Reset Attempt Login']
            ],
            [
              [
                "name" => 'Read Delivery Return',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses untuk mengakses menu delivery return'
              ],
              ['name' => 'Read Delivery Return']
            ],
            [
              [
                "name" => 'Close Delivery Return',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses untuk melakukan close pada delivery return'
              ],
              ['name' => 'Close Delivery Return']
            ],
            [
              [
                "name" => 'Create Delivery Return',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses untuk melakukan create pada delivery return'
              ],
              ['name' => 'Create Delivery Return']
            ],
            [
              [
                "name" => 'Delete Delivery Return',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses untuk melakukan delete pada delivery return'
              ],
              ['name' => 'Delete Delivery Return']
            ],
            [
              [
                "name" => 'Mark Urgent PO',
                "guard_name" => 'web',
                "description" => 'Mempunyai akses untuk menandai po urgent'
              ],
              ['name' => 'Mark Urgent PO']
            ],
            [
              [
                "name" => "Export Accept/Reject/Open PO",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk export purchase order dengan status accept/reject/open"
              ],
              [
                'name' => 'Export Accept/Reject/Open PO'
              ]
            ],
            [
              [
                "name" => "Show Price In PO A/R/Open Menu",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk melihat price purchase order line accept/reject/open xport"
              ],
              [
                'name' => 'Show Price In PO A/R/Open Menu'
              ]
            ]
        ];
        foreach($arrPermission as $key => $seed) {
          // Permission::updateOrCreate($seed[0],$seed[1]);
          $cari = Permission::where('name', $seed[1]['name']);
          if($cari->count() > 0){
            $cari->update($seed[0]);
          }else{
            $permission = new Permission;
            $permission->name = $seed[0]['name'];
            $permission->guard_name = $seed[0]['guard_name'];
            $permission->description = $seed[0]['description'];
            $permission->save();
          }
        }
    }

    function assignRolePermission(){
        // memberikan permission pada role
        $adminRole = Role::findByName('Admin PTKI');
        // $adminRole->givePermissionTo('Show Payment Status DS');
        $adminRole->givePermissionTo('Read Role');
        $adminRole->givePermissionTo('Update Role');
        $adminRole->givePermissionTo('Create Role');
        $adminRole->givePermissionTo('Delete Role');
        $adminRole->givePermissionTo('Read Permission');
        $adminRole->givePermissionTo('Read dashboard');
        // dd($adminRole->permissions);

    }

    function assignUserRole(){
        $userAdmin = User::find(1)->first();

        if($userAdmin){
            $userAdmin->assignRole(['Admin PTKI']);
            // bila ingin menambahkan permission langsung dari user
            // $userAdmin->givePermissionTo('update');
        }
    }
}