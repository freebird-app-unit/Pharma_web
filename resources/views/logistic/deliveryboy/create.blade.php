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
			<form class="form-horizontal" method="POST" action="@if(isset($user_detail)){{ route('logistic.deliveryboy.edit',array('id'=>$user_detail->id)) }} @else{{ route('logistic.deliveryboy.create') }}@endif" id="user_detail-form" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="user_type" id="user_type" value="delivery_boy"/>
				<input type="hidden" name="pharma_logistic_id" id="pharma_logistic_id" value="{{ Auth::user()->id }}"/>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="name">Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('name', isset($user_detail) ? $user_detail->name : null) }}}" name="name" id="name">
						@if ($errors->has('name')) <div class="errors_msg">{{ $errors->first('name') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="email">Email<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('email')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('email', isset($user_detail) ? $user_detail->email : null) }}}" name="email" id="email">
						@if ($errors->has('email')) <div class="errors_msg">{{ $errors->first('email') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="mobile_number">Mobile Number<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('mobile_number')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('mobile_number', isset($user_detail) ? $user_detail->mobile_number : null) }}}" name="mobile_number" id="mobile_number">
						@if ($errors->has('mobile_number')) <div class="errors_msg">{{ $errors->first('mobile_number') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">Image:</label> 
					<input type="file" class="filestyle" id='profile_image' name="profile_image" data-input="false">
					@if(!empty($user_detail->profile_image) && isset($user_detail->profile_image))
						<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-12">
							<a href="javascript:void(0)">
								@if (file_exists(storage_path('app/public/uploads/new_user/'.$user_detail->profile_image)))
									@php $image_path = asset('storage/app/public/uploads/new_user/' . $user_detail->profile_image) @endphp
								@else 
									{{ $image_path = '' }}
								@endif
								<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							</a>
						</div>
					@endif
				</div>
				<?php if(!isset($user_detail)){ ?>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="password">Password<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('password')) bad @endif">
						<input type="password" placeholder="" class="form-control" value="{{{ old('password', isset($user_detail) ? $user_detail->password : null) }}}" name="password" id="password">
						@if ($errors->has('password')) <div class="errors_msg">{{ $errors->first('password') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="confirm_password">Confirm password<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('confirm_password')) bad @endif">
						<input type="password" placeholder="" class="form-control" value="{{{ old('confirm_password', isset($user_detail) ? $user_detail->confirm_password : null) }}}" name="confirm_password" id="confirm_password">
						@if ($errors->has('confirm_password')) <div class="errors_msg">{{ $errors->first('confirm_password') }}</div>@endif
					</div>
				</div>
				
				
				<?php } ?>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="address">Address<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('address')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('address', isset($user_detail) ? $user_detail->address : null) }}}" name="address" id="address">
						@if ($errors->has('address')) <div class="errors_msg">{{ $errors->first('address') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="block">Block<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('block')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('block', isset($user_detail) ? $user_detail->block : null) }}}" name="block" id="block">
						@if ($errors->has('block')) <div class="errors_msg">{{ $errors->first('block') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="street">Street<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('street')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('street', isset($user_detail) ? $user_detail->street : null) }}}" name="street" id="street">
						@if ($errors->has('street')) <div class="errors_msg">{{ $errors->first('street') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pincode">Pincode<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('pincode')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('pincode', isset($user_detail) ? $user_detail->pincode : null) }}}" name="pincode" id="pincode">
						@if ($errors->has('pincode')) <div class="errors_msg">{{ $errors->first('pincode') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
						<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="button" value="Save">
						<a href="{{ route('user.index') }}" class="btn btn-sm btn-warning cancel">Cancel</a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
 
@section('script')
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.js"></script>

	<script type="text/javascript">

	$(document).ready(function() {
		 
		$("#user_detail-form").validate({
			rules: {
				email : {
					email:true,
					required : true
				},
				logistic : 'required',
				name : 'required',
				email : 'required',
				mobile_number : 'required',
				address : 'required',
				block : 'required',
				street : 'required',
				pincode : 'required',
				password: {
					required: true,
					minlength: 5
				},
				confirm_password: {
					required: true,
					minlength: 5,
					equalTo: "#password"
				},
			},
			highlight: function(element) {
			  $(element).removeClass('is-valid').addClass('is-invalid');
			},
			unhighlight: function(element) {
			  $(element).removeClass('is-invalid').addClass('is-valid');
			},
		});

		@if(!isset($user_detail))
			$( "#profile_image" ).rules( "add", {
					extension: "jpg|jpeg|png|ico|bmp",
					messages: {
						required: "Please upload file.",
						extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
					}
			});
		@endif
		
		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			if ($("#user_detail-form").valid()) { 
				$("#user_detail-form").submit();
			}
		});
	});
     
 </script>
	
@endsection

