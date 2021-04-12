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
							<div class="col-sm-4">
								<input type="text" class="form-control" name="search_text" placeholder="Search" id="search_text"/>
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
									<button class="btn btn-info" onclick="getmyorderlist(1)">Filter</button>
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
								<th width="10%" data-priority="1">Prescription</th>
								<!-- <th width="10%" data-priority="2">Order type</th> -->
								<!-- <th width="10%" data-priority="3">Prescription Name</th> -->
								<!-- <th width="10%" data-priority="4">Order note</th> -->
								<th width="20%" data-priority="4">Customer name</th>
								<th width="20%" data-priority="5">Customer contact number</th>
								<th width="10%" data-priority="6">Order date</th>
								<th width="20%" data-priority="7">Status</th>
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
									
@endsection
