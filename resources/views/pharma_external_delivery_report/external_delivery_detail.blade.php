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
		<div class="panel panel-color panel-inverse" style="border:1px solid #4c5667;">
						<div class="panel-heading">
							<h3 class="panel-title">External Delivery Report</h3>
						</div>
					</div>

			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
					
					<table id="admin_report_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="20%" data-priority="1">Order Number</th>
								<th width="20%" data-priority="2">Order Status</th>
								<th width="20%" data-priority="3">Order Date</th>
								<th width="20%" data-priority="4">Delivery Date</th>
								<th width="20%" data-priority="5">Order Amount</th>
							</tr>
						</thead>
						<tbody>
							<?php echo $order_list; ?>
						</tbody>
					</table>
					<div class="col-sm-12"><br></div>
					
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">

	$(document.body).on('click','#save_btn',function(e) {
		e.preventDefault();
		if ($("#payment-form").valid()) { 
			var orderIds = '';
			var orderId = $("#admin_report_list input:checkbox:checked").map(function(){
				return $(this).val();
			}).get();

			console.log(orderId.length);
			console.log(orderId);

			if(orderId.length > 0){
				orderIds = orderId.toString();
				$('<input />').attr('type', 'hidden')
				.attr('name', 'orderIds')
				.attr('value', orderIds)
				.appendTo('#payment-form');
			}

			var token = document.getElementsByName("_token")[0].value;
			var voucher_type = $('input[name="voucher_type"]:checked').val();
			var voucher_info = $('#voucher_info').val();
			var transation_number = $('#transation_number').val();
			var order_type = $('#order_type').val();

			$.ajax({
				type: "post",
				url: base_url+'/payment_create',
				data: 'voucher_type='+voucher_type+'&orderIds='+orderIds+"&voucher_info="+voucher_info+"&_token="+token+"&transation_number="+transation_number+'&order_type='+order_type,
				success: function (responce) {	
					// location.reload();
				}
			});
		}
	});
</script>
@endsection

