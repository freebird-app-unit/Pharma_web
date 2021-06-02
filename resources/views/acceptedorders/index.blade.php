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
<ul class="nav nav-tabs">
	<li class="nav-item">
		<a href="{{ route('upcomingorders.index') }}" class="nav-link">Upcoming Orders</a>
	</li>
	<li class="nav-item">
		<a href="{{ route('acceptedorders.index') }}" class="nav-link active">Accepted Orders</a>
	</li>
	<li class="nav-item">
		<a href="{{ route('outfordelivery.index') }}" class="nav-link">Ready For Pickup</a>
	</li>
	<li class="nav-item">
		<a href="{{ route('pickup.index') }}" class="nav-link">Out For Delivery</a>
	</li>
</ul>
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
						<input type="text" class="form-control" name="search_text" placeholder="Search" id="search_text"/>
					</div>
					<div class="col-sm-7"></div>
					<div class="col-sm-1">
						
					</div>
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<!-- <th width="10%" data-priority="1">Prescription</th> -->
								<!-- <th width="10%" data-priority="2">Order type</th> -->
								<!-- <th width="10%" data-priority="3">Prescription Name</th> -->
								<!-- <th width="10%" data-priority="4">Order note</th> -->
								<th width="20%" data-priority="6">Order number</th>
								<th width="20%" data-priority="5">Customer name</th>
								<th width="20%" data-priority="6">Address</th>
								<th width="20%" data-priority="7">Accept By</th>
								<th width="10%" data-priority="8">Accept Order date</th>
								<th width="20%" data-priority="9">Action</th>
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

<div id="assign_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel">Assign to</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('acceptedorders.assign') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" id="assign_id" name="assign_id" value=""/>
				<div class="form-group" >
					<label>Assign</label><br>
					<input type="radio" name="delivery_assign_type" value="deliveryboy" checked>&nbsp;Deliveryboy
					<input type="radio" name="delivery_assign_type" value="logistic">&nbsp;Logistic
				</div>
				<div  class="form-group">
					<select id="delivery_boy" name="delivery_boy" class="form-control" required>
						<option value="">Select delivery boy</option>
						<?php 
							foreach($deliveryboy_list as $raw){
								echo '<option value="'.$raw->id.'">'.$raw->name.'</option>';
							}
						?>
					</select>
				</div>
				<div  class="form-group" id='deliveryChargesBlock' style="display: none;">
					<select id="delivery_charges_id" name="delivery_charges_id" class="form-control">
						<!-- <option value="">Select delivery type</option> -->
						<option value="">Standard Delivery</option>
					</select>
				</div>
				<br>
				<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
				<input type="submit" name="submit" value="Send" class="btn btn-success"/>
				</form>
				<script>
					deliveryboy_list = '{!! $deliveryboy_list !!}';
					logistic_list = '{!! $logistic_list !!}';
					delivery_chargess_list = '{!! $delivery_charges !!}';
					pharmacy_id = '{!! $id !!}';
				</script>
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
						echo '<option value="'.$raw->reason.'">'.$raw->reason.'</option>';
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