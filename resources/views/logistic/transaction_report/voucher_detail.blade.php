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
			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
					<div class="col-sm-4">
						<input type="hidden"  value="{{ $id }}" id="voucher_id"/>
						<input type="text" class="form-control" name="search_text" placeholder="Search" id="search_text"/>
					</div>
					<div class="col-sm-7"></div>
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="20%" data-priority="1">Order Number</th>
								<th width="20%" data-priority="2">Customer</th>
								<th width="20%" data-priority="3">Pharmacy</th>
								<th width="10%" data-priority="4">Amount</th>
								<th width="20%" data-priority="5">Delivery Charge</th>
								<th width="10%" data-priority="6">Date/Time</th>
								<th width="10%" data-priority="7">Status</th>
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
