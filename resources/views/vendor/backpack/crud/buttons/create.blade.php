{{-- 
@if ($crud->hasAccess('create'))
	<a href="{{ url($crud->route.'/create') }}" class="btn btn-sm btn-primary-vp" data-style="zoom-in"><span class="ladda-label"><i class="la la-plus"></i> {{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span></a>
@endif	
--}}

@if ($crud->hasAccess('create'))
	@if (isset($crud->button_create))
	<a href="{{ url($crud->route.'/create') }}" class="btn btn-sm btn-primary-vp" data-style="zoom-in"><span class="ladda-label"><i class="la la-plus"></i> {{ $crud->button_create }}</span></a>
	@else
	<a href="{{ url($crud->route.'/create') }}" class="btn btn-sm btn-primary-vp" data-style="zoom-in"><span class="ladda-label"><i class="la la-plus"></i> Create</span></a>
	@endif
@endif