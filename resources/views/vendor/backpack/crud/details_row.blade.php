<!-- <div class="m-t-10 m-b-10 p-l-10 p-r-10 p-t-10 p-b-10">
	<div class="row">
		<div class="col-md-12">
			{{-- trans('backpack::crud.details_row') --}}
		</div>
	</div>
</div>
<div class="clearfix"></div> -->
<?php
	// dd($data_materials);
?>

<div class="m-t-10 m-b-10 p-l-10 p-r-10 p-t-10 p-b-10">
	<div class="row">
		<div class="col-md-12">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Material Item</th>
						<th>Description</th>
						<th>Total Qty</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data_materials as $data_material)
						<tr>
							<td>{{ $data_material->matl_item }}</td>
							<td>{{ $data_material->description }}</td>
							<td>{{ $data_material->remaining_qty }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="clearfix"></div>