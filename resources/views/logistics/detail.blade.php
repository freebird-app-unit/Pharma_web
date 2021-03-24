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
            if (file_exists(storage_path('app/public/uploads/new_logistic/'.$user_detail->profile_image))){
                $image_url = asset('storage/app/public/uploads/new_logistic/' . $user_detail->profile_image);
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
						Address:&nbsp;&nbsp;<?php echo $user_detail->address ?>
					</div>
					<br><br>
                    <div>
                        <strong>Service Info</strong><br><br>
						Start Time:&nbsp;&nbsp;<?php echo $user_detail->start_time ?><br><br>
						Close Time:&nbsp;&nbsp;<?php echo $user_detail->close_time ?>
                    </div>
					<br><br>
					<div>
						<strong>Location Info</strong><br><br>
						Country:&nbsp;&nbsp;<?php echo $user_detail->country ?><br><br>
						State:&nbsp;&nbsp;<?php echo $user_detail->state ?><br><br>
						City:&nbsp;&nbsp;<?php echo $user_detail->city ?><br><br>
						Block:&nbsp;&nbsp;<?php echo $user_detail->block ?><br><br>
						Street:&nbsp;&nbsp;<?php echo $user_detail->street ?><br><br>
						Pincode:&nbsp;&nbsp;<?php echo $user_detail->pincode ?><br><br>
						Latitude:&nbsp;&nbsp;<?php echo $user_detail->lat ?>&nbsp;&nbsp;Longitude:<?php echo $user_detail->lon ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
