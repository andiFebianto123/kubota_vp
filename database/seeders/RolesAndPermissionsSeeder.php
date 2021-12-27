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

        $this->createRole();

        $this->createPermission();

        $this->assignRolePermission();

        $this->assignUserRole();
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
                "name" => "Print DS with Price",
                "guard_name" => "web",
                "description" => "Mempunyai akses mencetak DS dengan harga"
              ],
              ['name' => 'Print DS with Price'],
            ],
            [
              [
                "name" => "Print DS without Price",
                "guard_name" => "web",
                "description" => "Mempunyai akses mencetak DS tanpa harga"
              ],
              ['name' => 'Print DS without Price'],
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
        $adminRole->givePermissionTo('Create Role');
        $adminRole->givePermissionTo('Update Role');
        $adminRole->givePermissionTo('Delete Role');
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