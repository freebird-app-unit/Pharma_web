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
		<a href="{{ route('adminrejected.index') }}" class="nav-link">Rejected Orders</a>
	</li>
	<li class="nav-item">
		<a href="{{ route('adminreturn.index') }}" class="nav-link">Return Orders</a>
	</li>
	<li class="nav-item">
		<a href="{{ route('admincancelled.index') }}" class="nav-link active">Cancelled Orders</a>
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
						<label>Search</label>
						<input type="text" class="form-control" name="search_text" tabindex="1" placeholder="Search" id="search_text"/>
					</div>
					<div class="col-sm-4">
						<label>Pharmacy</label>
						<div class="form-group">
						<select class="form-control" name="pharmacy_id" id="pharmacy_id">
							<option value=''>All Pharmacy</option>
							@foreach($pharmacies as $pharmacy)
								<option value='{{ $pharmacy->id }}'>{{ $pharmacy->name }}</option>
							@endforeach
						</select>
						</div>
					</div>
					<div class="col-sm-4">
						<label>logistic</label>
						<div class="form-group">
						<select class="form-control" name="logistic_id" id="logistic_id">
							<option value=''>All logistic</option>
							@foreach($logistics as $logistic)
								<option value='{{ $logistic->id }}'>{{ $logistic->name }}</option>
							@endforeach
						</select>
						</div>
					</div>
					<div class="col-sm-4">
					</div>
					<div class="col-sm-4">
						<div class="input-group">
							<button class="btn btn-info" onclick="return getlist(1);">Filter</button>
						</div><!-- input-group -->
					</div>
					<div class="col-sm-7"></div>
					<div class="col-sm-1">
						
					</div>
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<!-- <th width="10%" data-priority="1">Prescription</th> -->
								<th width="10%" data-priority="1">Order number</th>
								<!-- <th width="10%" data-priority="2">Order type</th> -->
								<!-- <th width="10%" data-priority="3">Prescription Name</th> -->
								<!-- <th width="10%" data-priority="4">Order note</th> -->
								<th width="15%" data-priority="5">Customer name</th>
								<th width="15%" data-priority="6">Customer contact number</th>
								<th width="20%" data-priority="6">Address</th>
								<th width="15%" data-priority="7">Cancel By</th>
								<th width="15%" data-priority="7">Pharmacy</th>
								<th width="15%" data-priority="7">Order Type</th>
								<th width="10%" data-priority="8">Order date</th>
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