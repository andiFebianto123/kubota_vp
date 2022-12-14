<?php
namespace App\Helpers;
use Auth;

class Sidebar
{
 
  public function generate()
  {
    return
    [
      [
        'name' => 'Dashboard',
        'url' => backpack_url('dashboard'),
        'icon' => 'la-home',
        'key' => 'dashboard',
        'access' => Constant::checkPermission('Read dashboard'),
        'childrens' => []
      ],
      [
        'name' => 'Purchase Order',
        'url' => backpack_url('purchase-order'),
        'icon' => 'la-newspaper',
        'key' => 'purchase-order',
        'access' => Constant::checkPermission('Read Purchase Order'),
        'childrens' => [
          // [
          //   'name' => 'List PO',
          //   'url' => backpack_url('purchase-order'),
          // ],
          // [
          //   'name' => 'Temporary DS',
          //   'url' => backpack_url('temp-upload-delivery'),
          // ]
        ]
      ],
      [
        'name' => 'Delivery Return',
        'url' => backpack_url('delivery-return'),
        'icon' => 'la-dice',
        'key' => 'delivery-return',
        'access' => Constant::checkPermission('Read Delivery Return'),
        'childrens' => []
      ],
      [
        'name' => 'Delivery Sheet',
        'url' => backpack_url('delivery'),
        'icon' => 'la-file',
        'key' => 'delivery-sheet',
        'access' => Constant::checkPermission('Read Delivery Sheet'),
        'childrens' => []
      ],
      [
        'name' => 'Delivery Status',
        'url' => backpack_url('delivery-status'),
        'icon' => 'la-stream',
        'key' => 'delivery-status',
        'access' => Constant::checkPermission('Read Delivery Status in Table'),
        'childrens' => []
      ],
      [
        'name' => 'Summary MO',
        'url' => '#',
        'icon' => 'la-cube',
        'key' => 'material-outhouse-summary',
        'access' => Constant::checkPermission('Read Summary MO'),
        'childrens' => [
          [
            'name' => 'Per Item',
            'url' => backpack_url('material-outhouse-summary-per-item'),
          ],
          [
            'name' => 'Per Po',
            'url' => backpack_url('material-outhouse-summary-per-po'),
          ],
        ]
      ],
      [
        'name' => 'History Summary MO',
        'url' => '#',
        'icon' => 'la-cube',
        'key' => 'history-material-outhouse-summary',
        'access' => Constant::checkPermission('Read History Summary MO'),
        'childrens' => [
          [
            'name' => 'History Per Item',
            'url' => backpack_url('history-mo-summary-per-item'),
          ],
          [
            'name' => 'History Per Po',
            'url' => backpack_url('history-mo-summary-per-po'),
          ]
        ]
      ],
      [
        'name' => 'List Payment',
        'url' => backpack_url('tax-invoice'),
        'icon' => 'la-file-invoice-dollar',
        'key' => 'tax-invoice',
        'access' => Constant::checkPermission('Read List Payment'),
        'childrens' => []
      ],
      
      [
        'name' => 'Forecast',
        'url' => backpack_url('forecast'),
        'icon' => 'la-chart-bar',
        'key' => 'forecast',
        'access' => Constant::checkPermission('Read Forecast'),
        'childrens' => []
      ],
      [
        'name' => 'Vendor',
        'url' => backpack_url('vendor'),
        'icon' => 'la-people-carry',
        'key' => 'vendors',
        'access' => Constant::checkPermission('Read Vendor'),
        'childrens' => []
      ],
      [
        'name' => 'User',
        'url' => backpack_url('user'),
        'icon' => 'la-user-tie',
        'key' => 'user',
        'access' => Constant::checkPermission('Read User'),
        'roles' => ['Admin PTKI'],
        'childrens' => []
      ],
      [
        'name' => 'General Message',
        'url' => backpack_url('general-message'),
        'icon' => 'la-envelope',
        'key' => 'general-message',
        'access' => Constant::checkPermission('Read General Message'),
        'childrens' => []
      ],
      [
        'name' => 'Configurations',
        'url' => backpack_url('configuration'),
        'icon' => 'la-tools',
        'key' => 'configuration',
        'access' => Constant::checkPermission('Read Configuration'),
        'childrens' => []
      ],
      [
        'name' => 'Role',
        'url' => backpack_url('role'),
        'icon' => 'la-users',
        'key' => 'role',
        'access' => Constant::checkPermission('Read Role'),
        'childrens' => []
      ],
      [
        'name' => 'Permission',
        'url' => backpack_url('permission'),
        'icon' => 'la-lock',
        'key' => 'permission',
        'access' => Constant::checkPermission('Read Permission'),
        'childrens' => []
      ],
    ];
  }
}