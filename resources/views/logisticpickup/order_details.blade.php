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
	if($order_detail->preimage!=''){
		$destinationPath = base_path() . '/storage/app/public/uploads/prescription/'.$order_detail->preimage;
		if(file_exists($destinationPath)){
			$image_url = url('/').'/storage/app/public/uploads/prescription/'.$order_detail->preimage;
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
					<div class="gallery"> 
						<a href="{{ $image_url }}" class="big"><img src="{{ $image_url }}" style="width:150px;"></a>
						<br><br><br>
						<div class="prescription" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;">
						<strong style="color: white">Prescription Name</strong><br>
						<div style="color: white"><?php echo $order_detail->prename; ?></div>
						</div>
						<div class="clear"></div>
					</div>
					
					<!-- <img src="{{ $image_url }}" style="width:150px;">
					<div class="order_description order_note" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;">
						<strong>Order Note</strong><br>
						<?php echo $order->prescription_name; ?>
					</div> -->
				</div>
				<div class="col-sm-6">
					<div style="text-align:center;">
						<h3 align="center">Order status</h3>
						<a href="javascript:;" class="btn btn-info">Ready For Pickup...</a>
					</div>
					<br><br>
					<div>
						<strong>Order details</strong><br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Delivery Type:&nbsp;&nbsp;<?php echo $order_detail->delivery_type; ?><br><br>
						Order Amount:&nbsp;&nbsp;<?php echo $order_detail->order_amount; ?><br><br>
						Delivery Amount:&nbsp;&nbsp;<?php echo $order_detail->delivery_price; ?><br><br>
						Net payable Amount:&nbsp;&nbsp;<?php echo ($order_detail->order_amount+$order_detail->delivery_price); ?> 
					</div>
					<br><br>
					<div>
						<strong>Pharmacy details</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $order_detail->pharmacyname; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $order_detail->pharmacymobile_number; ?><br><br>
						Location:&nbsp;&nbsp; <?php echo $order_detail->pharmacyaddress; ?>
					</div>
					<br><br>
					<div>
						<strong>Customer details</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $order_detail->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $order_detail->mobile_number; ?><br><br>
						Location:&nbsp;&nbsp; <?php echo $order_detail->delivery_address; ?>
					</div>
					<br><br>
					<div>
						<strong>Order Process</strong><br><br>
						Order Recevied:&nbsp;&nbsp;<?php echo $order_detail->created_at; ?><br><br>
						Order Accept at:&nbsp;&nbsp;<?php echo $order_detail->accept_datetime; ?><br><br>
						Order Assign to:&nbsp;&nbsp; <?php echo $order_detail->assign_datetime; ?><br><br>
						Order Pickup time:&nbsp;&nbsp; <?php echo $order_detail->pickup_datetime; ?><br><br>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
