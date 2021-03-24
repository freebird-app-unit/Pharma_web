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
					<br><br>
					<div class="order_description order_note" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;color: white">
						<strong>Prescription Name</strong><br>
						<?php echo $order->prescription_name; ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div style="text-align:center;">
						<h3 align="center">Order status</h3>
						<a href="javascript:;" class="btn btn-info" disabled><?php echo $order->order_status; ?>...</a>
					</div>
					<br><br>
					<div>
						<strong>Order detail</strong><br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Delivery Type:&nbsp;&nbsp;<?php echo $order_detail->delivery_type; ?><br><br>
						Leaved With Neighbour:&nbsp;&nbsp;<?php if($order->leaved_with_neighbor==1){echo 'true';} else {echo 'false';} ?><br><br>
						Order Type:&nbsp;&nbsp;<?php echo $order->order_type; ?><br><br>
						Order Received at:&nbsp;&nbsp;<?php echo $created_at = ($order->created_at!='')?date('d-M-Y h:i A',strtotime($order->created_at)):'';; ?><br><br>
						Order Action:&nbsp;&nbsp; <a class="btn btn-success waves-effect waves-light" href="<?php echo url('/orders/accept/'.$order->id); ?>" title="Accept order">Accept</a>
						
						<a onclick="reject_order(<?php echo $order->id; ?>)" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>
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
<script>
function reject_order(id){
	$('#reject_id').val(id);
}
</script>
@endsection
