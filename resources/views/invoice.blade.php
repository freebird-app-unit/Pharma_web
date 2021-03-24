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
						<a href="javascript:;" class="btn btn-warning">Processing...</a>
					</div>
					<br><br>
					<div>
						<strong>Order detail</strong><br><br>
						Order Number:&nbsp;&nbsp;<?php echo $order->order_number; ?><br><br>
						Order Assigned by:&nbsp;&nbsp;<?php echo $assign_by.' - '.$assign_time; ?><br><br>
						Order Action:&nbsp;&nbsp; <a class="btn btn-success waves-effect waves-light" href="<?php echo url('/receivedorders/delivered/'.$order->id); ?>" title="Accept order">Delivered</a>
						
						<a onclick="reject_order(<?php echo $order->id; ?>)" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#assign_modal">Reject</a>
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

<div id="assign_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel">Not delivered reason</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('receivedorders.reject') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" id="reject_id" name="reject_id" value=""/>
				<select id="reason" name="reason" class="form-control" required>
					<option value="">Select reason</option>
					<?php 
					foreach($reject_reason as $reason){
						echo '<option value="'.$reason->id.'">'.$reason->reason.'</option>';
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
