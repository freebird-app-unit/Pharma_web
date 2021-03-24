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
	if (!empty($order->invoice)) {
		if (file_exists(storage_path('app/public/uploads/invoice/'.$order->invoice))){
			$image_url = asset('storage/app/public/uploads/invoice/' . $order->invoice);
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
					<!-- <img src="{{ $image_url }}" style="width:150px;"> -->
					<!-- <div class="order_description order_note" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;">
						<strong>Order Note</strong><br>
						<?php echo $order->prescription_name; ?>
					</div> -->
				</div>
				<div class="col-sm-6">
					<div style="text-align:center;">
						<h3 align="center">Order status</h3>
						<a href="javascript:;" class="btn btn-info">New...</a>
					</div>
					<br><br>
				<div class="col-sm-6">
					<div>
						<strong>Order details</strong><br><br>
						Order Action:&nbsp;&nbsp;<a onclick="assign_order(<?php echo $order->id; ?>)" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#assign_modal">Assign</a><!-- <a onclick="reject_order(<?php echo $order->id; ?>)" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a> -->
						<br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Delivery Type:&nbsp;&nbsp;<?php echo $order_detail->delivery_type; ?><br><br>
						Order Amount:&nbsp;&nbsp;<?php echo $order_detail->order_amount; ?><br><br>
						Delivery Amount:&nbsp;&nbsp;<?php echo $order_detail->deliveryprice; ?><br><br>
						Net payable Amount:&nbsp;&nbsp;<?php echo ($order_detail->order_amount+$order_detail->deliveryprice); ?> 
					</div>
					<br><br>
					<div>
						<strong>Pharmacy details</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $customer->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $customer->mobile_number; ?><br><br>
						Location:&nbsp;&nbsp; <?php echo $order_detail->PickupAddress; ?>
					</div>
					<br><br>
					<div>
						<strong>Customer details</strong><br><br>
						Name:&nbsp;&nbsp;<?php echo $customer->name; ?><br><br>
						Contact Number:&nbsp;&nbsp;<?php echo $customer->mobile_number; ?><br><br>
						Location:&nbsp;&nbsp; <?php echo $order_detail->DestAddress; ?>
					</div>
					<br><br>
					<div>
						<strong>Order Process</strong><br><br>
						Order Recevied:&nbsp;&nbsp;<?php echo $order_detail->receive_date; ?><br><br>
						Order Accept at:&nbsp;&nbsp;<?php echo $order_detail->accept_date; ?><br><br>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="assign_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel">Assign to</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('logistic.acceptedorders.assign') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" id="assign_id" name="assign_id" value=""/>
				<label>Assign</label>
				<select id="delivery_boy" name="delivery_boy" class="form-control" required>
					<option value="">Select delivery boy</option>
					<?php 
					foreach($deliveryboy_list as $raw){
						echo '<option value="'.$raw->id.'">'.$raw->name.'</option>';
					}
					?>
				</select>
				<br>
				<a class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
				<input type="submit" name="submit" value="Send" class="btn btn-success"/>
				</form>
				
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="reject_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel">Reject reason</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('orders.reject') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" id="reject_id" name="reject_id" value=""/>
				<label>Select reject reason</label>
				<select id="reject_reason" name="reject_reason" class="form-control" required>
					<option value="">Select reason</option>
					<?php 
					foreach($reject_reason as $raw){
						echo '<option value="'.$raw->id.'">'.$raw->reason.'</option>';
					}
					?>
				</select>
				<br>
				<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
				<input type="submit" name="submit" value="Send" class="btn btn-success"/>
				</form>
				
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection
