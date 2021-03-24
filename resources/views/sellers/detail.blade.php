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
	<?php 
	$image_url = asset('/').'/uploads/placeholder.png';
    if (!empty($user_detail->profile_image)) {
		if (file_exists(storage_path('app/public/uploads/new_seller/'.$user_detail->profile_image))){
			$image_url = asset('storage/app/public/uploads/new_seller/' . $user_detail->profile_image);
		}
    }
	?>
		<div class="card-box">
			<div class="row">
				<div class="col-sm-6">
					<div class="gallery"> 
						<a href="{{ $image_url }}" class="big"><img src="{{ $image_url }}" style="width:150px;"></a>
						<div class="clear"></div>
					</div>
				</div>
				<div class="col-sm-6">
					<div>
						Name:&nbsp;&nbsp;<?php echo $user_detail->name ?><br><br>
						Email:&nbsp;&nbsp;<?php echo $user_detail->email ?><br><br>
						Number:&nbsp;&nbsp;<?php echo $user_detail->mobile_number ?><br><br>
					</div>
					<br><br>
                    <div>
                        <strong>Service Info</strong><br><br>
						Pharmacy:&nbsp;&nbsp;<?php echo $user_detail->pharmacy_name ?><br><br>
                    </div>
					<br><br>
					<div>
						<strong>Location Info</strong><br><br>
						Address:&nbsp;&nbsp;<?php echo $user_detail->address ?><br><br>
						Block:&nbsp;&nbsp;<?php echo $user_detail->block ?><br><br>
						Street:&nbsp;&nbsp;<?php echo $user_detail->street ?><br><br>
						Pincode:&nbsp;&nbsp;<?php echo $user_detail->pincode ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
