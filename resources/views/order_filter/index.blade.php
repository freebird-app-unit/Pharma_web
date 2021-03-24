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
			@if(Session::has('success_message'))
				<div class="alert alert-success alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					{{ Session::get('success_message') }}
				</div>
			@endif
			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
					<div class="panel panel-color panel-inverse" style="border:1px solid #4c5667;">
						<div class="panel-heading">
							<h3 class="panel-title">Order filter</h3>
						</div>
						<div class="panel-body">
							
							@if( Auth::user()->user_type == 'admin')
							<div class="col-sm-4">
								<label>Pharmacy</label>
								<div class="form-group">
								<select class="form-control" name="pharmacy_id[]" id="pharmacy_id" multiple="">
									@foreach($pharmacies as $pharmacy)
										<option value='{{ $pharmacy->id }}'>{{ $pharmacy->name }}</option>
									@endforeach
								</select>
								</div>
							</div>
							<div class="col-sm-4">
								<label>Pharmacy Delivery Boy</label>
								<div class="form-group">
								<select class="form-control" name="pharmacy_delivery_id[]" id="pharmacy_delivery_id" multiple="">
									
								</select>
								</div>
							</div>
							<div class="col-sm-4">
								<label>Pharmacy Seller</label>
								<div class="form-group">
								<select class="form-control" name="pharmacy_seller_id[]" id="pharmacy_seller_id" multiple="">
									
								</select>
								</div>
							</div>
							<div class="col-sm-4">
								<label>logistic</label>
								<div class="form-group">
								<select class="form-control" name="logistic_id[]" id="logistic_id" multiple="multiple">
									@foreach($logistics as $logistic)
										<option value='{{ $logistic->id }}'>{{ $logistic->name }}</option>
									@endforeach
								</select>
								</div>
							</div>
							<div class="col-sm-4">

								<label>logistic Delivery Boy</label>
								<div class="form-group">
								<select class="form-control" name="logistic_delivery_id[]" id="logistic_delivery_id" multiple="multiple">
									
								</select>
								</div>
							</div>
							<div class="col-sm-4">
								<label>Order Status</label>
								<div class="form-group">
								<select class="form-control" name="order_status[]" id="order_status" multiple="">
									<option value='new'>New</option>
									<option value='accept'>Accept</option>
									<option value='assign'>Assign</option>
									<option value='pickup'>Pickup</option>
									<option value='complete'>Complete</option>
									<option value='incomplete'>InComplete</option>
									<option value='cancel'>Cancel</option>
									<option value='reject'>Reject</option>
									<option value='payment_pending'>Payment Pending</option>
								</select>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
								<select class="form-control" name="order_type" id="order_type">
									<option value='all_order'>All Orders</option>
									<option value='new_order'>New Orders </option>
									<option value='order_history'>Orders History</option>
								</select>
								</div>
							</div>
							@endif
							<div class="col-sm-4">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Start date" id="filter_start_date">
									<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
								</div><!-- input-group -->
							</div><br><br>
							<div class="col-sm-4">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="End date" id="filter_end_date">
									<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
								</div><!-- input-group -->
							</div><br>
							<div class="col-sm-4">
								<div class="input-group">
									<button class="btn btn-info" onclick="return getorderfilter(1);">Filter</button>
								</div><!-- input-group -->
							</div>
						</div>
					</div>
					<div class="col-sm-7"></div>
					<div class="col-sm-1">
						
					</div>
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Order Number</th>
								<th width="20%" data-priority="2">Customer name</th>
								<th width="20%" data-priority="3">Pharmacy Name</th>
								<th width="20%" data-priority="4">Delivery Boy</th>
								<th width="10%" data-priority="5">Logistic</th>
								<th width="20%" data-priority="6">Date/Time</th>
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