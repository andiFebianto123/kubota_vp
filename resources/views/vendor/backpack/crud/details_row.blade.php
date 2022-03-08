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
					<?php // $total_qty += $data_material->jumlah_lot_qty; ?>
						<tr>
							<td>{{ $data_material->matl_item }}</td>
							<td>{{ $data_material->description }}</td>
							<td>{{ $data_material->issue_qty }}</td>
						</tr>
					@endforeach
					<?php 
						// $available_qty = $total_qty - $issued_qty;
					?>
					<!-- <tr>
						<td colspan="2">Total Qty</td>
						<td><strong>{{-- $total_qty --}}</strong></td>
					</tr>
					<tr>
						<td colspan="2">Issued Qty</td>
						<td><strong>{{-- $issued_qty --}}</strong></td>
					</tr>
					<tr>
						<td colspan="2"><strong>Available Material (Total Qty - Issued Qty)</strong></td>
						<td><strong>{{-- $available_qty --}}</strong></td>
					</tr> -->
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="clearfix"></div>