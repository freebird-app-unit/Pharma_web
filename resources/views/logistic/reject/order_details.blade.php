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
	$image_url = url('/').'/uploads/placeholder.png';
	if (!empty($order->prescription_image)) {
		if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
			$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
		}
	}
	?>
		<div class="card-box">
			<div class="row">
				<div class="col-sm-6">
					<div class="gallery"> 
						<a href="{{ $image_url }}" class="big"><img src="{{ $image_url }}" style="width:150px;"></a>
						<div class="clear"></div>
					</div>
					<!-- <img src="{{ $image_url }}" style="width:150px;">
					<div class="order_description order_note" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;">
						<strong>Order Note</strong><br>
						<?php echo $order->prescription_name; ?>
					</div> -->
				</div>
				<div class="col-sm-6">
					<div>
						<strong>Order details</strong><br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Delivery Type:&nbsp;&nbsp;<?php echo $order_detail->delivery_type; ?><br><br>
						Order Amount:&nbsp;&nbsp;<?php echo $order->order_amount; ?><br><br>
						Delivery Amount:&nbsp;&nbsp;<?php echo $order_detail->delivery_price; ?><br><br>
						Net payable Amount:&nbsp;&nbsp;<?php echo ($order->order_amount+$order_detail->delivery_price); ?> 
					</div>
					<br><br>
					<div>
						<strong>Pharmacy details</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $customer->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $customer->mobile_number; ?><br><br>
						Location:&nbsp;&nbsp; <?php echo $order_detail->pharmacyaddress; ?>
					</div>
					<br><br>
					<div>
						<strong>Customer details</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $customer->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $customer->mobile_number; ?><br><br>
						Location:&nbsp;&nbsp; <?php echo $order_detail->address; ?>
					</div>
					<br><br>
					<div>
						<strong>Order Process</strong><br><br>
						Order Recevied:&nbsp;&nbsp;<?php echo $order_detail->create_datetime; ?><br><br>
						Order Accept at:&nbsp;&nbsp;<?php echo $order_detail->accept_datetime; ?><br><br>
						Order Assign to:&nbsp;&nbsp; <?php echo $order_detail->assign_datetime; ?><br><br>
						Order Reject to:&nbsp;&nbsp; <?php echo $order_detail->reject_datetime; ?><br><br>
						Rejected By:&nbsp;&nbsp; <?php echo $order_detail->deliveryboyname; ?><br><br>
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
