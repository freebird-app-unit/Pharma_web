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
							<h3 class="panel-title">External Delivery Report</h3>
						</div>
					</div>
			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
					
					<table id="admin_report_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="20%" data-priority="1">Name Of Delivery Guy</th>
								<th width="30%" data-priority="2">Number Of Delivery  </th>
								<th width="30%" data-priority="3">Total Amount Of Order Delivered</th>
								<th width="30%" data-priority="4">Total Delivered Return</th>
							</tr>
						</thead>
						<tbody>
							{!! $html !!}
						</tbody>
					</table>
					<div class="col-sm-12"><br></div>
					<div class="col-sm-8 total_summary" id="total_summary">{{!! $total_summary !!}}</div>
					<div class="col-sm-2" id="pagination"><ul class="pagination">{!! $pagination !!}</ul></div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

