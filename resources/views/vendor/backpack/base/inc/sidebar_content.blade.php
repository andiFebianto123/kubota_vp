<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
@foreach ((new App\Helpers\Sidebar())->generate() as $key => $menu)
    @if(in_array(backpack_auth()->user()->roles->pluck('name')->first(), $menu['roles']))
    <li class="nav-item"><a class="nav-link" href="{{  $menu['url'] }}"><i class="la {{$menu['icon']}} nav-icon"></i> {{$menu['name']}}</a></li>
    @endif
@endforeach
<!-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('role') }}'><i class='nav-icon la la-question'></i> Roles</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('permission') }}'><i class='nav-icon la la-question'></i> Permissions</a></li> -->