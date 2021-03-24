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
							@foreach($deliveryboy_list as $item)
							<tr>
								<?php  
								$delivery_boy_name = get_name('new_pharma_logistic_employee','name',$item->id);
								$number_of_delivery_count = number_of_delivery_count($user_id,$item->id);
								$delivered_return_count = delivered_return_count($user_id,$item->id);
								$total_amount = total_amount($user_id,$item->id);
								?>
								<td>{{$delivery_boy_name}}</td>
								<td>{{$number_of_delivery_count}}</td>
			                    <td>{{$delivered_return_count}}</td>
								<td>{{$total_amount}}</td>
							</tr>
						  @endforeach
						</tbody>
					</table>
					<div class="col-sm-12"><br></div>
					<div class="col-sm-8 total_summary" id="total_summary">
						{{trans('Showing')}} {{{ (($deliveryboy_list->currentPage() - 1)*$deliveryboy_list->perPage()) + 1 }}} to @if(($deliveryboy_list->currentPage()*$deliveryboy_list->perPage()) >= $deliveryboy_list->total()) {{{$deliveryboy_list->total() }}} @else  {{{ $deliveryboy_list->currentPage()*$deliveryboy_list->perPage() }}}  @endif of {{{ $deliveryboy_list->total() }}} {{trans('Entries')}}
					</div>
					<div class="col-sm-2" id="pagination">
						{{ $deliveryboy_list->links() }}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

