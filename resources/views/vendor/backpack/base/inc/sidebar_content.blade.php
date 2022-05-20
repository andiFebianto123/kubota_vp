@foreach ((new App\Helpers\Sidebar())->generate() as $key => $menu)
    @if($menu['access'])
    <li class="nav-item @if($menu['childrens']) nav-dropdown @endif">
        <?php
            $user = backpack_auth()->user()->last_update_password;
            $url = ($user == null) ? "javascript:void(0)" : $menu['url'];
        ?>
        <a class="nav-link @if(str_contains(URL::current(), $menu['key'])) active @endif  parent @if($menu['childrens']) nav-dropdown-toggle @endif" href="{{  $url }}">
            <i class="nav-icon la {{$menu['icon']}}"></i> {{$menu['name']}}
        </a>
        @if($menu['childrens'])
        <ul class="nav-dropdown-items">
            @foreach($menu['childrens'] as $key2 => $child)
            <?php
                $url_ = ($user == null) ? "javascript:void(0)" : $child['url'];
            ?>
            <li class="nav-item">
                <a class="nav-link childs" href="{{ $url_ }}">
                <span>• {{$child['name']}}</span>
                </a>
            </li>
            @endforeach
        </ul>
        @endif
    </li> 
    @endif
@endforeach
