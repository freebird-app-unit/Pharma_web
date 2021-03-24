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
		<ul class="nav nav-tabs">
		  <li class="active"><a class="nav-link" data-toggle="tab" href="#user_detail">User detail</a></li>
		  <li><a class="nav-link" data-toggle="tab" href="#order_history">Order history</a></li>
		</ul>
<?php 
	$image_url = asset('/').'/uploads/placeholder.png';
	if (!empty($user_detail->profile_image)) {
		if (file_exists(storage_path('app/public/uploads/new_user/'.$user_detail->profile_image))){
			$image_url = asset('storage/app/public/uploads/new_user/' . $user_detail->profile_image);
		}
    }
	?>
		<div class="tab-content">
		  <div id="user_detail" class="tab-pane fade in active">
			  <div class="row">
				<div class="col-sm-6">
					<div class="order_description order_note" style="padding: 10px 20px;background:#333333;opacity:0.9;text-align:center;color: white">
						<?php echo $user_detail->name ?>
					</div>
					<br>
					<div class="gallery"> 
						<a href="{{ $image_url }}" class="big"><img src="{{ $image_url }}" style="width:150px;"></a>
						<div class="clear"></div>
					</div>
					<br><br>
				</div>
				<div class="col-sm-6">
					<div>
						Name:&nbsp;&nbsp;<?php echo $user_detail->name ?><br><br>
						Email:&nbsp;&nbsp;<?php echo $user_detail->email ?><br><br>
						Mobile Number:&nbsp;&nbsp;<?php echo $user_detail->mobile_number ?><br><br>
					</div>
				</div>
			  </div>
		  </div>
		  <div id="order_history" class="tab-pane fade">
			<div class="row">
			<input type="hidden" name="view_user_id" id="view_user_id" value="<?php echo $user_detail->id; ?>"/>
				<div class="col-sm-12">
					<table id="admin_order_list" class="table  table-striped">
						<thead>
							<tr>
								<th width="10%" data-priority="1">Order Number</th>
								<th width="15%" data-priority="2">Customer name</th>
								<th width="15%" data-priority="3">Pharmacy Name</th>
								<th width="15%" data-priority="4">Delivery Boy</th>
								<th width="15%" data-priority="5">Logistic</th>
								<th width="15%" data-priority="6">Date/Time</th>
								<th width="15%" data-priority="7">Status</th>
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
</div>
@endsection
