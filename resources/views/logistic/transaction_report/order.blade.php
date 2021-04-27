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
		<div class="col-lg-3 col-sm-6">
			<div class="widget-panel widget-style-2 bg-white">
				<h2 class="m-0 text-dark counter font-600" id='total_amount'><?php echo $total_order_amount; ?></h2>
				<div class="text-muted m-t-5"><span id='total_title'>Total Order Amount</span></div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6">
			<div class="widget-panel widget-style-2 bg-white">
				<h2 class="m-0 text-dark counter font-600" id='pending_amount'><?php echo $total_pending; ?></h2>
				<div class="text-muted m-t-5"><span id='pending_title'>Total Pending Payment</span></div>
			</div>
		</div>
	<div class="col-sm-12">
		<div class="card-box">
		<div class="panel panel-color panel-inverse" style="border:1px solid #4c5667;">
						<div class="panel-heading">
							<h3 class="panel-title">Order filter</h3>
						</div>
						<div class="panel-body">
							<div class="col-sm-4">
								<select class="form-control" name="order_type" id="order_type">
									<option value='1'>Order Payment</option>
									<option value='0'>Delivery Payment</option>
								</select>
							</div>
							<div class="col-sm-4">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Start date" id="filter_start_date">
									<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
								</div><!-- input-group -->
							</div>
							<div class="col-sm-4">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="End date" id="filter_end_date">
									<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
								</div><!-- input-group -->
							</div>
							<div class="col-sm-4"><br>
								<div class="input-group">
									<button class="btn btn-info" style='float: left;' onclick="getreportorderlist(1)">Filter</button>
								</div>
							</div>
						</div>
					</div>


			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
					
					<table id="admin_report_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Order Number</th>
								<th width="20%" data-priority="2">User</th>
								<th width="20%" data-priority="3">Seller</th>
								<th width="10%" data-priority="4">Order Amount</th>
								<th width="10%" data-priority="5">Delivery Charge</th>
								<th width="20%" data-priority="6">Date/Time</th>
								<th width="20%" data-priority="6">Status</th>
								<th width="5%" data-priority="7"><button onclick="pay_pending()" class="btn btn-warning btn-custom waves-effect waves-light" title="Pay pending" data-toggle="modal" data-target="#pay_modal" id="pay" <?php if(Auth::user()->user_type == 'pharmacy'){ ?> disabled <?php } ?>>Pay</button></th>
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

<div id="pay_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel">Create Voucher</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('order_report.payment_create') }}" id="payment-form">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					<input type="hidden" name="_token" value="">
					<div class="form-group" >
						<label>Payment Type</label><br>
						<input type="radio" name="voucher_type" id="cash" value="cash" checked> Cash
						<input type="radio" name="voucher_type" id="bank" value="bank"> Bank Transaction
					</div>
					<div  class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="voucher_info">Info</label>
						<input type="text" class="form-control" name="voucher_info" placeholder="Info" id="voucher_info"/>
					</div>
					<div  class="form-group" id="transactionBlock" style="display: none;">
						<label class="control-label col-md-12 col-sm-12 col-xs-12" for="transation_number">Transation Number<span class="required">*</span></label>
						<input type="text" placeholder="" class="form-control" name="transation_number" id="transation_number">
					</div>
					<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
					<input type="submit" name="submit" value="Pay" class="btn btn-success" id="save_btn"/>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
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

			$.ajax({
				type: "post",
				url: base_url+'/logistic/payment_create',
				data: 'voucher_type='+voucher_type+'&orderIds='+orderIds+"&voucher_info="+voucher_info+"&_token="+token+"&transation_number="+transation_number,
				success: function (responce) {	
					 location.reload();
				}
			});
		}
	});
</script>
@endsection

