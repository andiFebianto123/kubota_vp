{{-- 
@if ($crud->hasAccess('create'))
	<a href="{{ url($crud->route.'/create') }}" class="btn btn-primary btn-primary-vp" data-style="zoom-in"><span class="ladda-label"><i class="la la-plus"></i> {{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span></a>
@endif	
--}}

@if ($crud->hasAccess('create'))
	<a href="{{ url($crud->route.'/create') }}" class="btn btn-primary btn-primary-vp" data-style="zoom-in"><span class="ladda-label"><i class="la la-plus"></i> Create</span></a>
@endif