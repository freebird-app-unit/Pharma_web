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
					<div class="col-sm-4">
						<label>Search</label>
						<input type="text" class="form-control" name="search_text" placeholder="Search" id="search_text"/>
					</div>
					<div class="col-sm-4">
						<label>Seller</label>
						<div class="form-group">
						<select class="form-control" name="pharmacy_seller_id" id="pharmacy_seller_id">
							<option value=''>All Seller</option>
							@foreach($sellers as $seller)
								<option value='{{ $seller->id }}'>{{ $seller->name }}</option>
							@endforeach
						</select>
						</div>
					</div>
					<div class="col-sm-4">
						<label>Order Status</label>
						<div class="form-group">
						<select class="form-control" name="order_status" id="order_status">
							<option value=''>All Order Status</option>
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
						<label>Delivery Type</label>
						<div class="form-group">
						<select class="form-control" name="order_delivery_type" id="order_delivery_type">
							<option value=''>All Delivery Type</option>
							<option value='external_delivery'>External Delivery</option>
							<option value='internal_delivery'>Internal Delivery</option>
						</select>
						</div>
					</div>
					<div class="col-sm-4">
						<label>Start date</label>
						<div class="input-group">
							<input type="text" class="form-control" placeholder="Start date" id="filter_start_date">
							<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
						</div><!-- input-group -->
					</div>
					<div class="col-sm-4">
						<label>End date</label>
						<div class="input-group">
							<input type="text" class="form-control" placeholder="End date" id="filter_end_date">
							<span class="input-group-addon bg-custom b-0 text-white"><i class="icon-calender"></i></span>
						</div><!-- input-group -->
					</div>
					<div class="col-sm-4"><br>
						<div class="form-group">
							<button class="btn btn-info" onclick="return getorderslist(1);">Filter</button>
						</div><!-- input-group -->
					</div>
					<div class="col-sm-7"></div>
					<div class="col-sm-1">
						
					</div>
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<th data-priority="1">Order Id</th>
								<th data-priority="1">Full Name</th>
								<th data-priority="6" width="20%">Address</th>	
								<th data-priority="4">Seller name</th>
								<th data-priority="4">Delivery name</th>
								<th data-priority="8">Status</th>
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
<!-- /.modal -->
									
@endsection
