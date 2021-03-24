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
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a href="{{ route('report.index') }}" class="nav-link active">New Report</a>
		</li>
		<li class="nav-item">
			<a href="{{ route('reportresolve.index') }}" class="nav-link">Resolve Report</a>
		</li>
	</ul>
	
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
				
					<table id="admin_report_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1"></th>
								<th width="10%" data-priority="2">Image</th>
								<th width="10%" data-priority="3">User</th>
								<th width="10%" data-priority="4">Order</th>
								<th width="10%" data-priority="5">Mobile</th>
								<th width="20%" data-priority="6">Detail</th>
								<th width="20%" data-priority="7">Created</th>
								<th width="20%" data-priority="8">Action</th>
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
