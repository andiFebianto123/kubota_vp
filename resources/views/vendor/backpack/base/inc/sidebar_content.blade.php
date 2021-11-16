<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
@foreach ((new App\Helpers\sidebar())->generate() as $key => $menu)
    @if(in_array(backpack_auth()->user()->role->name, $menu['roles']))
    <li class="nav-item"><a class="nav-link" href="{{  $menu['url'] }}"><i class="la {{$menu['icon']}} nav-icon"></i> {{$menu['name']}}</a></li>
    @endif
@endforeach