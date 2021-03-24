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
						<input type="text" class="form-control" name="search_text" placeholder="Search" id="search_text"/>
					</div>
					<div class="col-sm-7"></div>
					<div class="col-sm-1">
						
					</div>
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<th data-priority="1">Order number</th>
                                <th data-priority="2">Customer Name</th>
                                <th data-priority="3">Customer Number</th>
                                <th data-priority="4">Reason</th>
                                <!-- <th width="10%" data-priority="8">Action</th> -->
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
				<form method="post" action="{{ route('incomplete.assign') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" id="assign_id" name="assign_id" value=""/>
				<div class="form-group" >
					<label>Assign</label><br>
					<input type="radio" name="delivery_assign_type" value="deliveryboy" checked>deliveryboy
					<input type="radio" name="delivery_assign_type" value="logistic">logistic
				</div>

				<div  class="form-group" id='deliveryChargesBlock' style="display: none;">
					<select id="delivery_charges_id" name="delivery_charges_id" class="form-control">
						<option value="">Select delivery type</option>
					</select>
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
				<br>
				<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
				<input type="submit" name="submit" value="Send" class="btn btn-success"/>
				</form>
				<script>
					deliveryboy_list = '{!! $deliveryboy_list !!}';
					logistic_list = '{!! $logistic_list !!}';
					delivery_chargess_list = '{!! $delivery_charges !!}';
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
				<form method="post" action="{{ route('incomplete.reject') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" id="reject_id" name="reject_id" value=""/>
				<label>Type Cancel reason</label>
				<input type="text" name="rejectreason" class="form-control" required>
				<br>
				<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
				<input type="submit" name="submit" value="Send" class="btn btn-success"/>
				</form>
				
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
									
@endsection
