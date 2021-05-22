@extends('layouts.app')
@section('content')
<?php 
$user = auth()->user();
?>
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title">Dashboard</h4>
		<?php 
		if($user->user_type=='pharmacy'){
		?>
		<p class="text-muted page-title-alt">Welcome to pharmacy panel !</p>
        <div class="row wel-panel">
        	<div class="col-sm-4">
            	<div class="widget-panel bg-green">
                	<div class="row">
                    	<div class="col-lg-6 col-md-12 col-sm-12">
                        	<h2>{{$today_earning}}</h2>
                            <span>Today Earning</span>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                        	<h2>{{$total_earning}}</h2>
                            <span>Total Earning</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
            	<div class="widget-panel bg-blue">
                	<div class="row">
                    	<div class="col-lg-6 col-md-12 col-sm-12">
                        	<h2>{{$today_delivery}}</h2>
                            <span>Today Delivery</span>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                        	<h2>{{$total_delivery}}</h2>
                            <span>Total Delivery</span>
                        </div>
                    </div>
                </div>
            </div>
			<div class="col-sm-4">
            	<div class="widget-panel bg-orange">
                	<div class="row">
						<div class="col-lg-6 col-md-12 col-sm-12">
                        	<h2>{{$total_delivery}}</h2>
                            <span>Used Delivery</span>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                        	<h2>{{$available_delivery}}</h2>
                            <span>Available Delivery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    	<?php } ?>
	</div>
</div>
<?php 
if($user->user_type=='admin'){
?>
<p class="text-muted page-title-alt">Welcome to admin panel !</p>
<div class="row">
	<a href="{{ route('pharmacy.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_pharmacy; ?></h2>
			<div class="text-muted m-t-5">Total Pharmacy</div>
		</div>
	</div>
	</a>
	<a href="{{ route('seller.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_seller; ?></h2>
			<div class="text-muted m-t-5">Total Seller</div>
		</div>
	</div>
</a>
<a href="{{ route('deliveryboy.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_deliveryboy; ?></h2>
			<div class="text-muted m-t-5">Total Delivery boy</div>
		</div>
	</div>
</a>
<a href="{{ route('user.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_customer; ?></h2>
			<div class="text-muted m-t-5">Total Customer</div>
		</div>
	</div>
</a>
<a href="{{ route('logistic.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_logistic; ?></h2>
			<div class="text-muted m-t-5">Total Logistic</div>
		</div>
	</div>
	</a>
	<a href="{{ route('admin.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_admin; ?></h2>
			<div class="text-muted m-t-5">Total Admin</div>
		</div>
	</div>
</a>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_users; ?></h2>
			<div class="text-muted m-t-5">Total Users</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_orders; ?></h2>
			<div class="text-muted m-t-5">Total Orders</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-3 col-sm-6">
		<a href="{{ url('/adminupcomingorders') }}" class="btn btn-primary btn-lg btn-block">Live Order</a>
	</div>
	<div class="col-lg-3 col-sm-6">
		<a href="{{ url('/adminrejected') }}" class="btn btn-primary btn-lg btn-block">Incomplete Order</a>
	</div>
	<div class="col-lg-3 col-sm-6">
		<a href="{{ url('/admincomplete') }}" class="btn btn-primary btn-lg btn-block">Completed Order</a>
	</div>
</div>
<?php 
}
if($user->user_type=='superadmin'){
?>
<p class="text-muted page-title-alt">Welcome to super admin panel !</p>
<div class="row">
	<a href="{{ route('pharmacy.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_pharmacy; ?></h2>
			<div class="text-muted m-t-5">Total Pharmacy</div>
		</div>
	</div>
	</a>
	<a href="{{ route('seller.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_seller; ?></h2>
			<div class="text-muted m-t-5">Total Seller</div>
		</div>
	</div>
	</a>
	<a href="{{ route('deliveryboy.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_deliveryboy; ?></h2>
			<div class="text-muted m-t-5">Total Delivery boy</div>
		</div>
	</div>
	</a>
	<a href="{{ route('user.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_customer; ?></h2>
			<div class="text-muted m-t-5">Total Customer</div>
		</div>
	</div>
	</a>
	<a href="{{ route('logistic.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_logistic; ?></h2>
			<div class="text-muted m-t-5">Total Logistic</div>
		</div>
	</div>
	</a>
	<a href="{{ route('admin.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_admin; ?></h2>
			<div class="text-muted m-t-5">Total Admin</div>
		</div>
	</div>
</a>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_users; ?></h2>
			<div class="text-muted m-t-5">Total Users</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_orders; ?></h2>
			<div class="text-muted m-t-5">Total Orders</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-3 col-sm-6">
		<a href="{{ url('/adminupcomingorders') }}" class="btn btn-primary btn-lg btn-block">Live Order</a>
	</div>
	<div class="col-lg-3 col-sm-6">
		<a href="{{ url('/adminrejected') }}" class="btn btn-primary btn-lg btn-block">Incomplete Order</a>
	</div>
	<div class="col-lg-3 col-sm-6">
		<a href="{{ url('/admincomplete') }}" class="btn btn-primary btn-lg btn-block">Completed Order</a>
	</div>
</div>
<?php 
}
if($user->user_type=='logistic'){
?>
<p class="text-muted page-title-alt">Welcome to logistic panel !</p>
<div class="row">
	<a href="{{ route('logisticupcoming.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_upcoming; ?></h2>
			<div class="text-muted m-t-5">Upcoming order</div>
		</div>
	</div>
	</a>
	<a href="{{ route('logisticassign.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_outfordelivery; ?></h2>
			<div class="text-muted m-t-5">Out for delivery</div>
		</div>
	</div>
	</a>
	<a href="{{ route('logisticpickup.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_readyforpickup; ?></h2>
			<div class="text-muted m-t-5">Ready For Pickup</div>
		</div>
	</div>
</a>
<a href="{{ route('logistic.complete.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_complete; ?></h2>
			<div class="text-muted m-t-5">Completed order</div>
		</div>
	</div>
	</a>
	<a href="{{ route('logistic.incomplete.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_incomplete; ?></h2>
			<div class="text-muted m-t-5">Incompleted order</div>
		</div>
	</div>
	</a>
	<a href="{{ route('logistic.cancelled.index') }}">
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_canceled; ?></h2>
			<div class="text-muted m-t-5">Cancelled order</div>
		</div>
	</div>
	</a>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<?php echo '&#8377;'; ?><h2 class="m-0 text-dark counter font-600"><?php echo $total_deposit; ?></h2>
			<div class="text-muted m-t-5">Total Deposit</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="widget-panel widget-style-2 bg-white">
			<?php echo '&#8377;'; ?><h2 class="m-0 text-dark counter font-600"><?php echo $current_deposit; ?></h2>
			<div class="text-muted m-t-5">Current Deposit</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
				<div class="col-sm-4">
						<input type="text" class="form-control" name="order_search_text" placeholder="Search" id="order_search_text"/>
					</div>
					<table id="admin_dashboardorder_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1" style="text-align:center;">Invoice</th>
								<th width="10%" data-priority="2" style="text-align:center;">Delivery type</th>
								<th width="10%" data-priority="3" style="text-align:center;">Pickup Location</th>
								<th width="10%" data-priority="4" style="text-align:center;">Delivery Location</th>
								<th width="10%" data-priority="5" style="text-align:center;">Accept By</th>
								<th width="10%" data-priority="5" style="text-align:center;">Order Amount</th>
								<th width="10%" data-priority="6" style="text-align:center;">Date</th>
								<th width="10%" data-priority="6" style="text-align:center;">Action</th>
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

<div id="assign_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel">Assign to</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('logisticupcoming.assign') }}">
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
				<form method="post" action="{{ route('logisticupcoming.reject') }}">
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

<?php 
}
if($user->user_type=='pharmacy' || $user->user_type=='seller'){
?>
<h5 class="page-title">Today's Orders</h5>
<div class="row">
<a href="{{ route('upcomingorders.index') }}">
	<div class="col-lg-2 col-sm-12">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_incomplete; ?></h2>
			<div class="text-muted m-t-5"><a href="{{ url('/upcomingorders') }}">Pending</a></div>
		</div>
	</div>
</a>
<a href="{{ route('acceptedorders.index') }}">
	<div class="col-lg-2 col-sm-12">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_accepted; ?></h2>
			<div class="text-muted m-t-5"><a href="{{ url('/acceptedorders') }}">Accepted</a></div>
		</div>
	</div>
</a>
<a href="{{ route('outfordelivery.index') }}">
	<div class="col-lg-2 col-sm-12">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_outfordelivery; ?></h2>
			<div class="text-muted m-t-5"><a href="{{ url('/outfordelivery') }}">Delivery</a></div>
		</div>
	</div>
</a>
<a href="{{ route('complete.index') }}">
	<div class="col-lg-2 col-sm-12">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_complete; ?></h2>
			<div class="text-muted m-t-5"><a href="{{ url('/complete') }}">Completed</a></div>
		</div>
	</div>
</a>
<a href="{{ route('cancelled.index') }}">
	<div class="col-lg-2 col-sm-12">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_canceled; ?></h2>
			<div class="text-muted m-t-5"><a href="{{ url('/canceled') }}">Cancelled</a></div>
		</div>
	</div>
</a>
<a href="{{ route('pharmacyrejected.index') }}">
	<div class="col-lg-2 col-sm-12">
		<div class="widget-panel widget-style-2 bg-white">
			<h2 class="m-0 text-dark counter font-600"><?php echo $total_rejected; ?></h2>
			<div class="text-muted m-t-5"><a href="{{ url('/pharmacyrejected') }}">Rejected</a></div>
		</div>
	</div>
</a>
</div>

<br />
<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
		<div class="table-rep-plugin">
            <h4 class="page-title">Upcoming Orders</h4>
			<div class="table-responsive" data-pattern="priority-columns">
				<div class="col-sm-5 pull-right order_search_box">
						<input type="text" class="form-control" name="order_search_text" placeholder="Search" id="order_search_text"/>
					</div>
				<table id="admin_dashboardorder_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Order number</th>
								<th width="10%" data-priority="2">Customer name</th>
								<th width="10%" data-priority="3">Customer contact number</th>
								<th width="20%" data-priority="4">Address</th>
								<!--<th width="20%" data-priority="5">Accept By</th>-->
								<th width="10%" data-priority="6">Order date</th>
								<th width="20%" data-priority="7">Action</th>
							</tr>
						</thead>
						<tbody id="admin_dashboardorder_body">
							
						</tbody>
					</table>
					<div class="col-sm-12"><br></div>
					<div class="col-sm-6 total_summary" id="total_summary"></div>
					<div class="col-sm-2 perpage_container" id="perpage_container">
						<select id="perpage" class="form-control">
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
					</div>
					<div class="col-sm-4" id="pagination"><ul class="pagination"></ul></div>
				</div>
			</div>
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
				<input type="hidden" name="home" value="home">
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


<?php 
}

if($user->user_type=='pharmacy' || $user->user_type=='seller'){
?>
<div id="accept_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="mySmallModalLabel"></h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('orders.accept') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="home" value="home">
				<input type="hidden" id="accept_id" name="accept_id" value=""/>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="order_amount">OrderAmount<span class="required">*</span></label>
					<input type="text" placeholder="Order Amount" class="form-control" name="order_amount" id="order_amount">
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="invoice">Invoice<span class="required">*</span></label>
					<input type="file" class="form-control" id="invoice" name="invoice"  data-input="false">
				</div>
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
				<form method="post" action="{{ route('orders.reject') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="home" value="home">
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
<?php } ?>
@endsection
