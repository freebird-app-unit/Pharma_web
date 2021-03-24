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
			<form class="form-horizontal" method="POST" action="@if(isset($user_detail)){{ route('logistic.edit',array('id'=>$user_detail->id)) }} @else{{ route('logistic.create') }}@endif" id="user_detail-form" enctype="multipart/form-data">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<input type="hidden" name="user_type" id="user_type" value="logistic"/>
			<input type="hidden" name="parentuser_id" id="parentuser_id" value="{{ Auth::user()->id }}"/>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="name">Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('name', isset($user_detail) ? $user_detail->name : null) }}}" name="name" id="name">
						@if ($errors->has('name')) <div class="errors_msg">{{ $errors->first('name') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="owner_name">Owner Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('owner_name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('owner_name', isset($user_detail) ? $user_detail->owner_name : null) }}}" name="owner_name" id="owner_name">
						@if ($errors->has('owner_name')) <div class="errors_msg">{{ $errors->first('owner_name') }}</div>@endif
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
						<input type="text" placeholder="" class="form-control only_number" value="{{{ old('mobile_number', isset($user_detail) ? $user_detail->mobile_number : null) }}}" name="mobile_number" id="mobile_number" maxlength="10">
						@if ($errors->has('mobile_number')) <div class="errors_msg">{{ $errors->first('mobile_number') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-6" for="profile_image">Profile image<span class="required">*</span></label>
					<input type="hidden" name="old_profile_image" value="{{ isset($user_detail) ? $user_detail->profile_image : null }}"/>
					<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('profile_image')) bad @endif">
						<input type="file" class="form-control" name="profile_image" id="profile_image">
						@if ($errors->has('profile_image')) <div class="errors_msg">{{ $errors->first('profile_image') }}</div>@endif
					</div>
					@if(!empty($user_detail->profile_image) && isset($user_detail->profile_image))
					<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-6">
						<a href="javascript:void(0)">
							@if (file_exists(storage_path('app/public/uploads/new_logistic/'.$user_detail->profile_image)))
								@php $image_path = asset('storage/app/public/uploads/new_logistic/'.$user_detail->profile_image) @endphp
							@else 
								{{ $image_path = '' }}
							@endif
							<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
						</a>
					</div>
					@endif
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Area<span class="required">*</span></label>
					<div>
						<button type="button" id="clear-button">Clear</button>
					</div>
					<div id="map"></div>
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="address">Office Address<span class="required">*</span></label>
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="block">Street<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('street')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('street', isset($user_detail) ? $user_detail->street : null) }}}" name="street" id="street">
						@if ($errors->has('street')) <div class="errors_msg">{{ $errors->first('street') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Location<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('lat') && $errors->has('lon')) bad @endif">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Lat</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('location')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('lat', isset($user_detail) ? $user_detail->lat : null) }}}" name="lat" id="lat">
						</div>

						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Lon</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('lon')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('lon', isset($user_detail) ? $user_detail->lon : null) }}}" name="lon" id="lon">
						</div>
						@if ($errors->has('lat')) <div class="errors_msg">{{ $errors->first('lat') }}</div>@endif
						@if ($errors->has('lon')) <div class="errors_msg">{{ $errors->first('lon') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="country">Country<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('country')) bad @endif">
						<select class="form-control" name="country" id="country">
							<option value=''>Select Country</option>
							<?php
							if(count($countries)>0){
								foreach($countries as $country){
									if(isset($user_detail) && $user_detail->country==$country->name){
										$sel = 'selected';
									}else{
										$sel = '';
									}
									echo '<option '.$sel.' id="COUNTRY'.$country->name.'" value="'.$country->name.'" data-country-id="'.$country->id.'">'.$country->name.'</option>';
								}
							}
							?>
						</select>
						@if ($errors->has('country')) <div class="errors_msg">{{ $errors->first('country') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="state">State<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('state')) bad @endif">
						<select class="form-control" name="state" id="state" disabled>
							<option value=''>Select State</option>
							<?php
								if(isset($user_detail) && isset($user_detail->state)){
									echo '<option selected value="'.$user_detail->state.'" >'.$user_detail->state.'</option>';
								}
							?>
						</select>
						@if ($errors->has('state')) <div class="errors_msg">{{ $errors->first('state') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="state">City<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('city')) bad @endif">
						<select class="form-control" name="city" id="city" disabled>
							<option value=''>Select City</option>
							<?php
								if(isset($user_detail) && isset($user_detail->city)){
									echo '<option selected value="'.$user_detail->city.'" >'.$user_detail->city.'</option>';
								}
							?>
						</select>
						@if ($errors->has('city')) <div class="errors_msg">{{ $errors->first('city') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Time">Time<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('start_time') && $errors->has('close_time')) bad @endif">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Time">Start</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('Time')) bad @endif">
							<input type="text" data-format="hh:mm:ss" placeholder="" class="form-control" value="{{{ old('start_time', isset($user_detail) ? $user_detail->start_time : null) }}}" name="start_time" id="start_time">
						</div>

						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Time">Close</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('close_time')) bad @endif">
							<input type="text" data-format="hh:mm:ss" placeholder="" class="form-control" value="{{{ old('close_time', isset($user_detail) ? $user_detail->close_time : null) }}}" name="close_time" id="close_time">
						</div>
						@if ($errors->has('start_time')) <div class="errors_msg">{{ $errors->first('start_time') }}</div>@endif
						@if ($errors->has('close_time')) <div class="errors_msg">{{ $errors->first('close_time') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pincode">Pincode<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('pincode')) bad @endif">
						<input type="text" placeholder="" class="form-control only_number" value="{{{ old('pincode', isset($user_detail) ? $user_detail->pincode : null) }}}" name="pincode" id="pincode">
						@if ($errors->has('pincode')) <div class="errors_msg">{{ $errors->first('pincode') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
						<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="submit" id="submit" value="Save">
						<a href="{{ route('logistic.index') }}" class="btn btn-sm btn-warning cancel">Cancel</a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

@section('script')
	
	<script type="text/javascript">

	$(document).ready(function() {
		$(document).on('keydown', '.only_number', function(e) {
			// Allow: backspace, delete, tab, escape, enter and .
			if ($.inArray(e.keyCode, [32,46, 8, 9, 27, 13, 110, 190]) !== -1 ||
					// Allow: Ctrl+A, Command+A
				(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
					// Allow: home, end, left, right, down, up
				(e.keyCode >= 35 && e.keyCode <= 40)) {
						// let it happen, don't do anything
						return;
			}
			// Ensure that it is a number and stop the keypress
			if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
				e.preventDefault();
			}
		 });
		$(function () {
			$('#start_time').datetimepicker({  format: 'HH:mm:ss' });
			$('#close_time').datetimepicker({ format: 'HH:mm:ss' });

			$("#start_time").on("dp.change", function (e) {
				$('#close_time').data("DateTimePicker").minDate(e.date);
			});
			
			$("#close_time").on("dp.change", function (e) {
				$('#start_time').data("DateTimePicker").maxDate(e.date);
			});
		});
		$("#user_detail-form").validate({
			rules: {
				email : {
					email:true,
					required : true
				},
				name : 'required',
				owner_name : 'required',
				mobile_number : {
					required:true,
					minlength:10,
				  	maxlength:10,
				  	number: true
				},
				password: {
					required: true,
					minlength: 5
				},
				confirm_password: {
					required: true,
					minlength: 5,
					equalTo: "#password"
				},
				address : 'required',
				country : 'required',
				state : 'required',
				city: 'required',
				street : 'required',
				block : 'required',
				pincode : {
					required:true,
					minlength:6,
				  	maxlength:6,
				  	number: true
				},
				lat : 'required',
				lon : 'required',
				start_time : 'required',
				close_time : 'required',

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
				required: true,
				extension: "jpg|jpeg|png|ico|bmp",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});
		@endif
		
		$('#user_detail-form').on('click', function(e) {
			$('<input />').attr('type', 'hidden')
			.attr('name', 'area')
			.attr('value', JSON.stringify(boundery))
			.appendTo('#user_detail-form');
			// e.preventDefault();

			if ($("#user_detail-form").valid()) { 
				$("#user_detail-form").submit();
			}
		});
	});
     
 </script>
@endsection
