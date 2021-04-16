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
						<div class="panel-body">
							<div class="col-sm-3">
								<label>Search</label>
								<div class="form-group">
								<select class="form-control" name="record_display" id="record_display" >
									<option value='weekly'>Weekly</option>
									<option value='monthly'>Monthly</option>
									<option value='yearly'>Yearly</option>
									<option value='custom_date'>Custom date range</option>
								</select>
								</div>
							</div>
							<div class="col-sm-3 filter_block" id="block_monthly" style="display: none;">
								<label>Select Month</label>
								<div class="form-group">
								<select class="form-control" name="record_monthly" id="record_monthly" >
									<?php 
									$months = array('January','February','March','April','May','June','July ','August','September','October','November','December');
									$i = 1;
									foreach ($months as $months_key => $months_value) {
										echo '<option value="'.$i.'">'.$i.' '.$months_value.'</option>';
										$i++;
									 } 
									 ?>
								</select>
								</div>
							</div>
							<div class="col-sm-3 filter_block" id="block_yearly" style="display: none;">
								<label>Select Year</label>
								<div class="form-group">
								<select class="form-control" name="record_yearly" id="record_yearly" >
									<?php 
									$current_year = date('Y');
									$lastYear = $current_year - 10;
									for($i=$current_year;$i>=$lastYear;$i--){
									    echo '<option value='.$i.'>'.$i.'</option>';
									}
									?>
								</select>
								</div>
							</div>
							<div class="col-sm-4 block_custom_date" style="display: none;">
								<label>Start date</label>
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Start date" id="filter_start_date">
									<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
								</div><!-- input-group -->
							</div>
							<div class="col-sm-4 block_custom_date" style="display: none;">
								<label>End date</label>
								<div class="input-group">
									<input type="text" class="form-control" placeholder="End date" id="filter_end_date">
									<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
								</div><!-- input-group -->
							</div>
							<div class="col-sm-3">
								<label>&nbsp;</label>
								<div class="input-group">
									<button class="btn btn-info" style='float: left;' onclick="getExternalDeliveryReport(1)">Filter</button>
								</div>
							</div>
						</div>
					</div>

			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
					
					<table id="admin_report_list" class="table  table-striped">
						<thead>
							<tr>
								<!--<th width="20%" data-priority="1">Name Of Delivery Guy</th>-->
								<th width="20%" data-priority="2">Code</th>
								<th width="20%" data-priority="3">Number Of Delivery  </th>
								<th width="20%" data-priority="4">Total Amount Of Order Delivered</th>
								<th width="20%" data-priority="5">Total Delivered Return</th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
					<div class="col-sm-12"><br></div>
					<div class="col-sm-8 total_summary" id="total_summary"></div>
					<div class="col-sm-2 perpage_container" id="perpage_container">
						<select id="perpage" class="form-control">
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
					</div>
					<div class="col-sm-2" id="pagination"><ul class="pagination"></ul></div>
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

