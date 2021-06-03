@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title">{{ $page_title }}</h4>
			<ol class="breadcrumb">
				<li><a href="{{ url('/') }}">Dashboard</a></li>
				<li class="active">{{ $page_title }}</li>
			</ol>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
			<div class="row">
				<div class="col-sm-6">
						<table id="admin_order_list" class="table  table-striped">
							<thead>
							<tr>
								<th>Category Name</th>
								<th>Product</th>
								<th>Quantity</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($category as $value) { ?>
								<td>{{ $value['category'] }}</td>
								<td>{{ $value['product'] }}</td>
								<td>{{ $value['qty'] }}</td>
						</tbody>
							<?php }?>
					</table>  
				</div>
				<div class="col-sm-6">
					<div>
						<br>
						<strong>Order detail</strong><br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Delivery Type:&nbsp;&nbsp;<?php if($order_detail->delivery_type==''){echo 'free';}else{ echo $order_detail->delivery_type;} ?><br><br>
						Leaved With Neighbour:&nbsp;&nbsp;<?php if($order->leaved_with_neighbor==1){echo 'true';} else {echo 'false';} ?><br><br>
						Order Type:&nbsp;&nbsp;<?php echo $order->order_type; ?><br><br>
					</div>
					<br><br>
					<div>
						<strong>Customer detail</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $customer->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $customer->mobile_number; ?><br><br>
						Address:&nbsp;&nbsp; <?php echo $order_detail->address; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
function reject_order(id){
	$('#reject_id').val(id);
}
</script>
@endsection
