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
					<?php if(Auth::user()->user_type!='pharmacy'){ ?>
					<div class="col-sm-4">
						<label>Pharmacy</label>
						<div class="form-group">
						<select class="form-control" name="search_pharmacy" id="search_pharmacy">
							<option value=''>All Pharmacy</option>
							<?php 
							foreach($pharmacy_list as $pharmacy_list_val){
								echo '<option value="'.$pharmacy_list_val->id.'">'.$pharmacy_list_val->name.'</option>';
							}
							?>
						</select>
						</div>
					</div>
					<?php }else{ ?>
					<input type="hidden" name="search_pharmacy" id="search_pharmacy" value="<?php echo Auth::user()->user_id; ?>"/>
					<?php } ?>
					<div class="col-sm-11"></div>
					<div class="col-sm-1">
						<a href="{{ route('seller.create') }}" class="btn btn-success waves-effect waves-light">Create</a>
					</div>
					<table id="admin_client_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Image</th>
								<th width="10%" data-priority="2">Name</th>
								<th width="10%" data-priority="3">Email</th>
								<th width="15%" data-priority="4">Mobile number</th>
								<th width="15%" data-priority="5">Address</th>
								<th width="10%" data-priority="6">Pharmacy Name</th>
								<th width="10%" data-priority="6">No. of orders<br> Pending</th>
								<th width="10%" data-priority="6">Created</th>
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
