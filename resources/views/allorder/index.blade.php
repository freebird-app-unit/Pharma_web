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
									<button class="btn btn-info" onclick="getallorderlist(1)">Filter</button>
								</div><!-- input-group -->
							</div>
						</div>
					</div>
					
					<div class="col-sm-7"></div>
					<table id="admin_client_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Image</th>
								<th width="10%" data-priority="2">Name</th>
								<th width="10%" data-priority="3">User Type</th>
								<th width="20%" data-priority="4">Order completed</th>
								<th width="20%" data-priority="5">Order incomplete</th>
								<th width="20%" data-priority="6">Order rejected</th>
								<th width="20%" data-priority="7">Total order</th> 
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
