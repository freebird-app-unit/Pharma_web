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
	<?php 
	$image_url = '';
	if($order->prescription!=''){
		$destinationPath = base_path() . '/uploads/prescription/'.$order->prescription;
		if(file_exists($destinationPath)){
			$image_url = url('/').'/uploads/prescription/'.$order->prescription;
		}else{
			$image_url = url('/').'/uploads/placeholder.png';
		}
	}else{
		$image_url = url('/').'/uploads/placeholder.png';
	}
	?>
		<div class="card-box">
			<div class="row">
				<div class="col-sm-6">
					<img src="{{ $image_url }}">
					<div class="order_description order_note" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;">
						<strong>Order Note</strong><br>
						<?php echo $order->order_note; ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div style="text-align:center;">
						<h3 align="center">Order status</h3>
						<i style="font-size:50px;" class="glyphicon glyphicon-ok-circle text-info"></i><br>
						<span class="text-info">Delivered</span>
					</div>
					<br><br>
					<div>
						<strong>Order detail</strong><br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Order Received at:&nbsp;&nbsp;<?php echo date('h:i A',strtotime($order->created_at)); ?><br><br>
						Order Assigned by:&nbsp;&nbsp;<?php echo $assign_by.' - '.$assign_time; ?><br><br>
						Order Delivered:&nbsp;&nbsp; <?php echo $delivered_time; ?><br><br>
					</div>
					<br><br>
					<div>
						<strong>Customer detail</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $customer->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $customer->mobile_number; ?><br><br>
						Location:&nbsp;&nbsp; <?php echo $address; ?>
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
