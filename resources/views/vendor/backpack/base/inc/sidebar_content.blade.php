@foreach ((new App\Helpers\Sidebar())->generate() as $key => $menu)
    @if($menu['access'])
    <li class="nav-item @if($menu['childrens']) nav-dropdown @endif">
        <a class="nav-link parent @if($menu['childrens']) nav-dropdown-toggle @endif" href="{{  $menu['url'] }}">
            <i class="nav-icon la {{$menu['icon']}}"></i> {{$menu['name']}}
        </a>
        @if($menu['childrens'])
        <ul class="nav-dropdown-items">
            @foreach($menu['childrens'] as $key2 => $child)
            <li class="nav-item">
                <a class="nav-link childs" href="{{$child['url']}}">
                <span>• {{$child['name']}}</span>
                </a>
            </li>
            @endforeach
        </ul>
        @endif
    </li>
    @endif
@endforeach
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon la la-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>