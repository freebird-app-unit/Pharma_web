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
		<div class="panel panel-color panel-inverse" style="border:1px solid #4c5667;">
						<div class="panel-heading">
							<h3 class="panel-title">Order Report</h3>
						</div>
						<div class="panel-body">
							<div class="col-sm-3">
								<label>Search</label>
								<div class="form-group">
								<select class="form-control" name="record_display" id="record_display" >
									<option value='weekly'>Weekly</option>
									<option value='monthly'>Monthly</option>
									<option value='yearly'>Yearly</option>
								</select>
								</div>
							</div>
							<div class="col-sm-3 filter_block" id="block_monthly" style="display: none;">
								<label>Select Month</label>
								<div class="form-group">
								<select class="form-control" name="record_monthly" id="record_monthly" >
									<?php 
									$months = array('January','February','March','April','May','June','July ','August','September','October','November','December');
									$i = 1;
									foreach ($months as $months_key => $months_value) {
										echo '<option value="'.$i.'">'.$i.' '.$months_value.'</option>';
										$i++;
									 } 
									 ?>
								</select>
								</div>
							</div>
							<div class="col-sm-3 filter_block" id="block_yearly" style="display: none;">
								<label>Select Year</label>
								<div class="form-group">
								<select class="form-control" name="record_yearly" id="record_yearly" >
									<?php 
									$current_year = date('Y');
									$lastYear = $current_year - 10;
									for($i=$current_year;$i>=$lastYear;$i--){
									    echo '<option value='.$i.'>'.$i.'</option>';
									}
									?>
								</select>
								</div>
							</div>
							<div class="col-sm-3">
								<label>&nbsp;</label>
								<div class="input-group">
									<button class="btn btn-info" style='float: left;' onclick="getPharmaOrderReport(1)">Filter</button>
								</div>
							</div>
						</div>
					</div>
			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
					<table id="admin_report_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="20%" data-priority="1">Order Id</th>
								<th width="20%" data-priority="2">User Name</th>
								<th width="20%" data-priority="3">Address</th>
								<th width="20%" data-priority="4">Seller Name</th>
								<th width="20%" data-priority="5">Delivery Name</th>
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


