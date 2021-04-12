@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title">Dashboard</h4>
		<p class="text-muted page-title-alt">Welcome to logistic panel !</p>
	</div>
</div>
<?php 
$user = auth()->user();
  
if($user->user_type=='admin'){
?>
<div class="row">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_pharmacy; ?></h2>
			<div class="text-muted m-t-5">Total pharmacy</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_seller; ?></h2>
			<div class="text-muted m-t-5">Total seller</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_deliveryboy; ?></h2>
			<div class="text-muted m-t-5">Total delivery boy</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_customer; ?></h2>
			<div class="text-muted m-t-5">Total Customer</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_admin; ?></h2>
			<div class="text-muted m-t-5">Total Admin</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_users; ?></h2>
			<div class="text-muted m-t-5">Total users</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_orders; ?></h2>
			<div class="text-muted m-t-5">Total Orders</div>
		</div>
	</div>
</div>
<?php 
}
if($user->user_type=='logistic'){
?>
<div class="row">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_accepted; ?></h2>
			<div class="text-muted m-t-5">Accepted order</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_outfordelivery; ?></h2>
			<div class="text-muted m-t-5">Out for delivery</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_complete; ?></h2>
			<div class="text-muted m-t-5">Completed order</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_upcoming; ?></h2>
			<div class="text-muted m-t-5">Upcoming order</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
		<div class="table-rep-plugin">
			<h3>Upcoming Orders</h3>
			<div class="table-responsive" data-pattern="priority-columns">
				<div class="col-sm-4">
						<input type="text" class="form-control" name="order_search_text" placeholder="Search" id="order_search_text"/>
					</div>
				<table id="admin_dashboardorder_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Invoice</th>
								<th width="10%" data-priority="2">Delivery type</th>
								<th width="20%" data-priority="3">Pickup Location</th>
								<th width="20%" data-priority="4">Delivery Location</th>
								<th width="20%" data-priority="5">Order Amount</th>
								<th width="20%" data-priority="6">Action</th>
							</tr>
						</thead>
						<tbody id="admin_dashboardorder_body">
							
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
<?php 
}
if($user->user_type=='pharmacy' || $user->user_type=='seller'){
?>
<div class="row">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_accepted; ?></h2>
			<div class="text-muted m-t-5">Accepted order</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_outfordelivery; ?></h2>
			<div class="text-muted m-t-5">Out for delivery</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_complete; ?></h2>
			<div class="text-muted m-t-5">Completed order</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_incomplete; ?></h2>
			<div class="text-muted m-t-5">Incompleted order</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_rejected; ?></h2>
			<div class="text-muted m-t-5">Rejected order</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_canceled; ?></h2>
			<div class="text-muted m-t-5">Canceled order</div>
		</div>
	</div>
</div>

<?php
}

if($user->user_type=='delivery_boy'){
?>
<div class="row">
	<div class="col-lg-4 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_received; ?></h2>
			<div class="text-muted m-t-5">Received order</div>
		</div>
	</div>
	<div class="col-lg-4 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_complete; ?></h2>
			<div class="text-muted m-t-5">Completed order</div>
		</div>
	</div>
	<div class="col-lg-4 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_incomplete; ?></h2>
			<div class="text-muted m-t-5">Incompleted order</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
			<div class="table-rep-plugin">
			<h3>Received Orders</h3>
				<div class="table-responsive" data-pattern="priority-columns">
					<div class="col-sm-4">
						<input type="text" class="form-control" name="search_text" placeholder="Search" id="search_text"/>
					</div>
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Invoice</th>
								<!-- <th width="10%" data-priority="1">Order type</th> -->
								<!-- <th width="10%" data-priority="2">Order note</th> -->
								<th width="20%" data-priority="3">Customer name</th>
								<th width="20%" data-priority="4">Customer contact number</th>
								<th width="10%" data-priority="5">Order date</th>
								<th width="20%" data-priority="7">Action</th>
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
<?php 
}
?>

<div id="assign_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel">Assign to</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('logistic.upcoming.assign') }}">
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
				<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
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
				<form method="post" action="{{ route('logistic.upcoming.reject') }}">
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
