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
						<label>City</label>
						<div class="form-group">
						<select class="form-control" name="search_city" id="search_city">
							<option value=''>All City</option>
							<?php 
							foreach($logistic_city as $logistic_city_val){
								if($logistic_city_val->city != ""){
									echo '<option value="'.$logistic_city_val->city.'">'.$logistic_city_val->city.'</option>';
								}	
							}
							?>
						</select>
						</div>
					</div>
					<div class="col-sm-11"></div>
					<div class="col-sm-1">
						<a href="{{ route('logistic.create') }}" class="btn btn-success waves-effect waves-light">Create</a>
					</div>
					<table id="admin_client_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Image</th>
								<th width="10%" data-priority="1">Name</th>
								<th width="10%" data-priority="1">Owner</th>
								<th width="10%" data-priority="2">Email</th>
								<th width="20%" data-priority="3">Mobile number</th>
								<th width="20%" data-priority="4">Address</th>
								<!-- <th width="10%" data-priority="5">Created at</th>
								<th width="10%" data-priority="6">Updated at</th> -->
								<th width="30%" data-priority="7">Action</th>
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
