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
					<?php 
						$image_url = url('/').'/uploads/placeholder.png';
						foreach ($pre_image as $pres) {
							if (!empty($pres->image)){

								$image_url = url('/').'/storage/app/public/uploads/prescription/'.$pres->image;

								?>
								<div class="gallery"> 
									<a href="{{ $image_url }}" class="big"><br><img src="{{ $image_url }}" style="width:150px;"></a>
									<div class="clear"></div>
								</div>
						<?php } }?>
						<?php
							if (!empty($order_detail->prescription_image)) {
									$image_url = url('/').'/storage/app/public/uploads/prescription/'.$order_detail->prescription_image;
									?>
						<div class="gallery"> 
							<a href="{{ $image_url }}" class="big"><img src="{{ $image_url }}" style="width:150px;"></a>
							<div class="clear"></div>
						</div>
						<?php }  
						?>
					
					<br><br>
					<div class="order_description order_note" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;color: white">
						<strong>Prescription Name</strong><br>
						<?php echo $order->prescription_name; ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div>
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
