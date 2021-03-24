@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title">{{ $page_title }}</h4>
			<ol class="breadcrumb">
				<li><a href="{{ url('/') }}">Dashboard</a></li>
				<li class="active">{{ $page_title }}</li>
				<li class="active">Order No : {{ $order_details->order_number }}</li>
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
						<input type="text" class="form-control" name="search_text" placeholder="Search" id="search_text"/>
					</div>
					<div class="col-sm-7"></div>
					<div class="col-sm-1">
						
					</div>
					<table id="admin_order_feedback_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">User Name</th>
								<th width="10%" data-priority="2">Rating</th>
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
@section('script')
<script>
	getcompletelist(1);
	
	var ajax_request = null;
	function getcompletelist(pageno){
		var token = document.getElementsByName("_token")[0].value;
		var searchtxt = $('#search_text').val();
		var perpage = $('#perpage').val();
		var ord_field=$('#sortfield').val(); 
		var sortord=$('#sortord').val();
		ajax_request = $.ajax({
				type: "post",
				url: base_url+'/getuserfeedbacklist',
				data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord+"&order_id="+<?= $order_details->id ?>,
				beforeSend: function (xhr) {
					if (ajax_request != null) {
						ajax_request.abort();
					}
				},
				success: function (responce) {	
					var data = responce.split('##');
					$('#admin_order_feedback_list tbody').html(data[0]);
					$('.pagination').html(data[1]);
					$('#total_summary').html(data[2]);
				}
			});
	}

	$("#search_text").keyup(function() {
		getcompletelist(1);
	});

	$("#perpage").change(function() {
		getcompletelist(1);
	});
</script>
@endsection
