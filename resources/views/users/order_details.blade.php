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
					<div>
						<strong>Order detail</strong><br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Delivery Type:&nbsp;&nbsp;<?php if($order_detail->delivery_type==''){echo 'free';}else{ echo $order_detail->delivery_type;} ?><br><br>
						Leaved With Neighbour:&nbsp;&nbsp;<?php if($order->leaved_with_neighbor==1){echo 'true';} else {echo 'false';} ?><br><br>
						Order Type:&nbsp;&nbsp;<?php echo $order->order_type; ?><br><br>
						Pickup Images:&nbsp;&nbsp;
						<?php 
						if($pickup_images_file_array){
							foreach ($pickup_images_file_array as $pickup_images_file_array_key => $pickup_images_file_array_value) {
								echo '<a href="'.$pickup_images_file_array_value.'" target="_blank"><img src="'.$pickup_images_file_array_value.'" width="50"/></a>'.'<br>';
							}
						}else{
							echo 'No File Found';
						}
						?> <br><br> 
						Delivered Images:&nbsp;&nbsp;
						<?php 
						if($delivered_images_file_array){
							foreach ($delivered_images_file_array as $delivered_images_file_array_key => $delivered_images_file_array_value) {
								echo '<a href="'.$delivered_images_file_array_value.'" target="_blank"><img src="'.$delivered_images_file_array_value.'" width="50"/></a>'.'<br>';
							}
						}else{
							echo 'No File Found';
						}
						?> 
						<br><br>

						<?php 
						$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';
						$accept_datetime = ($order->accept_datetime!='')?date('d-M-Y  h:i a',strtotime($order->accept_datetime)):'';
						$assign_datetime = ($order->assign_datetime!='')?date('d-M-Y  h:i a',strtotime($order->assign_datetime)):'';
						$pickup_datetime = ($order->pickup_datetime!='')?date('d-M-Y  h:i a',strtotime($order->pickup_datetime)):'';
						$deliver_datetime = ($order->deliver_datetime!='')?date('d-M-Y  h:i a',strtotime($order->deliver_datetime)):'';
						?>
						Created Date Time:&nbsp;&nbsp;<?php echo $created_at; ?><br><br>
						Accept Date Time:&nbsp;&nbsp;<?php echo $accept_datetime; ?><br><br>
						Assign Date Time:&nbsp;&nbsp;<?php echo $assign_datetime; ?><br><br>
						Pickup Date Time:&nbsp;&nbsp;<?php echo $pickup_datetime; ?><br><br>
						Deliver Date Time:&nbsp;&nbsp;<?php echo $deliver_datetime; ?><br><br>
					</div>
					<br><br>
					<div>
						<strong>Customer detail</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $customer->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $customer->mobile_number; ?><br><br>
						Address:&nbsp;&nbsp; <?php echo $order_detail->address; ?>
					</div>
				</div>
				<div class="col-sm-6">
					<input type="hidden" name="sent_otp" id="sent_otp"/>
					<a href="javascript:;" class="btn btn-info request_otp" onclick="request_prescription(<?php echo $order->id; ?>)">Request Prescription</a>
					<p class="otp_message"></p>
					<div id="verify_otp_container" style="display:none;">
						<input type="text" name="otp" id="otp" class="form-control" placeholder="Enter otp"/><br>
						<a href="javascript:;" class="btn btn-success" onclick="verify_otp(<?php echo $order->id; ?>)">Verify</a>
						<a href="javascript:;" class="btn btn-info resend_otp" onclick="request_prescription(<?php echo $order->id; ?>)">Resend</a>
					</div>
					<div id="prescription_image_container">
					
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
