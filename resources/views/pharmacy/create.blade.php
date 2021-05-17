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
			<form class="form-horizontal" method="POST" action="@if(isset($user_detail)){{ route('pharmacy.edit',array('id'=>$user_detail->id)) }} @else{{ route('pharmacy.create') }}@endif" id="user_detail-form" enctype="multipart/form-data">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="name">Pharmacy Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('name', isset($user_detail) ? $user_detail->name : null) }}}" name="name" id="name">
						@if ($errors->has('name')) <div class="errors_msg">{{ $errors->first('name') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="first_name">First Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('first_name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('first_name', isset($user_detail) ? $user_detail->first_name : null) }}}" name="first_name" id="first_name">
						@if ($errors->has('first_name')) <div class="errors_msg">{{ $errors->first('first_name') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="last_name">Last Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('last_name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('last_name', isset($user_detail) ? $user_detail->last_name : null) }}}" name="last_name" id="last_name">
						@if ($errors->has('last_name')) <div class="errors_msg">{{ $errors->first('last_name') }}</div>@endif
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="mobile_number">Mobile
						Number<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('mobile_number')) bad @endif">
						<input type="text" placeholder="" class="form-control only_number" value="{{{ old('mobile_number', isset($user_detail) ? $user_detail->mobile_number : null) }}}" name="mobile_number" id="mobile_number" maxlength="10">
						@if ($errors->has('mobile_number')) <div class="errors_msg">{{ $errors->first('mobile_number') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="address">Address<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('address')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('address', isset($user_detail) ? $user_detail->address : null) }}}" name="address" id="address">
						@if ($errors->has('address')) <div class="errors_msg">{{ $errors->first('address') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="block">Block<!-- <span class="required">*</span> --></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('block')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('block', isset($user_detail) ? $user_detail->block : null) }}}" name="block" id="block">
						@if ($errors->has('block')) <div class="errors_msg">{{ $errors->first('block') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="street">Street<!-- <span class="required">*</span> --></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('street')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('street', isset($user_detail) ? $user_detail->street : null) }}}" name="street" id="street">
						@if ($errors->has('street')) <div class="errors_msg">{{ $errors->first('street') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">Profile Image:</label> 
					<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('profile_image')) bad @endif">
						<input type="file" class="form-control" id="profile_image" name="profile_image"  data-input="false">
						@if ($errors->has('profile_image')) <div class="errors_msg">{{ $errors->first('profile_image') }}</div>@endif
					</div>
					@if(!empty($user_detail->profile_image) && isset($user_detail->profile_image))
					<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-12">
						<a href="javascript:void(0)">
							@if (file_exists(storage_path('app/public/uploads/new_users/'.$user_detail->profile_image)))
								@php $image_path = asset('storage/app/public/uploads/new_users/' . $user_detail->profile_image) @endphp
							@else 
								{{ $image_path = '' }}
							@endif
							<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							<a style="cursor: pointer;" class="m-l-10 action-icon deleteImageProfile"><i class="fa fa-trash text-danger"></i></a>
						</a>
					</div>
					@endif
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">Adhar Card:</label> 
					<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('adharcard_image')) bad @endif">
						<input type="file" class="form-control" id="adharcard_image" name="adharcard_image"  data-input="false">
						@if ($errors->has('adharcard_image')) <div class="errors_msg">{{ $errors->first('adharcard_image') }}</div>@endif
					</div>
					@if(!empty($user_detail->adharcard_image) && isset($user_detail->adharcard_image))
					<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-12">
						<a href="javascript:void(0)">
							@if (file_exists(storage_path('app/public/uploads/new_pharmacy/adharcard/'.$user_detail->adharcard_image)))
								@php $image_path = asset('storage/app/public/uploads/new_pharmacy/adharcard/' . $user_detail->adharcard_image) @endphp
							@else 
								{{ $image_path = '' }}
							@endif
							<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							<a style="cursor: pointer;" class="m-l-10 action-icon deleteImage"><i class="fa fa-trash text-danger"></i></a>
						</a>
					</div>
					@endif
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">Pan Card:</label> 
					<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('pancard_image')) bad @endif">
						<input type="file" class="form-control" id="pancard_image" name="pancard_image"  data-input="false">
						@if ($errors->has('pancard_image')) <div class="errors_msg">{{ $errors->first('pancard_image') }}</div>@endif
					</div>
					@if(!empty($user_detail->pancard_image) && isset($user_detail->pancard_image))
					<div class="m-t-15 image_div_pan col-md-2 col-sm-2 col-xs-12">
						<a href="javascript:void(0)">
							@if (file_exists(storage_path('app/public/uploads/new_pharmacy/pancard/'.$user_detail->pancard_image)))
								@php $image_path = asset('storage/app/public/uploads/new_pharmacy/pancard/' . $user_detail->pancard_image) @endphp
							@else 
								{{ $image_path = '' }}
							@endif
							<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							<a style="cursor: pointer;" class="m-l-10 action-icon deleteImagepan"><i class="fa fa-trash text-danger"></i></a>
						</a>
					</div>
					@endif
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">Drug License:</label> 
					<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('druglicense_image')) bad @endif">
						<input type="file" class="form-control" id="druglicense_image" name="druglicense_image"  data-input="false">
						@if ($errors->has('druglicense_image')) <div class="errors_msg">{{ $errors->first('druglicense_image') }}</div>@endif
					</div>
					@if(!empty($user_detail->druglicense_image) && isset($user_detail->druglicense_image))
					<div class="m-t-15 image_div_pan col-md-2 col-sm-2 col-xs-12">
						<a href="javascript:void(0)">
							@if (file_exists(storage_path('app/public/uploads/new_pharmacy/druglicense/'.$user_detail->druglicense_image)))
								@php $image_path = asset('storage/app/public/uploads/new_pharmacy/druglicense/' . $user_detail->druglicense_image) @endphp
							@else 
								{{ $image_path = '' }}
							@endif
							<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							<a style="cursor: pointer;" class="m-l-10 action-icon deleteImagepan"><i class="fa fa-trash text-danger"></i></a>
						</a>
					</div>
					@endif
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="country">Country<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('country')) bad @endif">
						<select class="form-control" name="country" id="country">
							<option value="">Select Country</option>
							<?php 
							foreach($countries as $country){
								if(isset($user_detail) && $user_detail->country==$country->name){
										$sel = 'selected';
									}else{
										$sel = '';
									}
								echo '<option '.$sel.' value="'.$country->id.'">'.$country->name.'</option>';
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Location<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('lat') && $errors->has('lon')) bad @endif">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Lat</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('location')) bad @endif">
							<input type="text" placeholder="" class="form-control only_number" value="{{{ old('lat', isset($user_detail) ? $user_detail->lat : null) }}}" name="lat" id="lat">
						</div>

						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Lon</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('lon')) bad @endif">
							<input type="text" placeholder="" class="form-control only_number" value="{{{ old('lon', isset($user_detail) ? $user_detail->lon : null) }}}" name="lon" id="lon">
						</div>
						@if ($errors->has('lat')) <div class="errors_msg">{{ $errors->first('lat') }}</div>@endif
						@if ($errors->has('lon')) <div class="errors_msg">{{ $errors->first('lon') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Time">Time<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('start_time') && $errors->has('close_time')) bad @endif">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Time">Start</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('Time')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('start_time', isset($user_detail) ? $user_detail->start_time : null) }}}" name="start_time" id="start_time">
						</div>

						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Time">Close</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('close_time')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('close_time', isset($user_detail) ? $user_detail->close_time : null) }}}" name="close_time" id="close_time">
						</div>
						@if ($errors->has('start_time')) <div class="errors_msg">{{ $errors->first('start_time') }}</div>@endif
						@if ($errors->has('close_time')) <div class="errors_msg">{{ $errors->first('close_time') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="radius">Radius<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('radius')) bad @endif">
						<input type="text" placeholder="" class="form-control only_number" value="{{{ old('radius', isset($user_detail) ? $user_detail->radius : null) }}}" name="radius" id="radius">
						@if ($errors->has('radius')) <div class="errors_msg">{{ $errors->first('radius') }}</div>@endif
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pincode">Discount<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('discount')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('discount', isset($user_detail) ? $user_detail->discount : null) }}}" name="discount" id="discount">
						@if ($errors->has('discount')) <div class="errors_msg">{{ $errors->first('discount') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="password">Password<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('password')) bad @endif">
						<input type="password" placeholder="" class="form-control" value="{{{ old('password', isset($user_detail) ? $user_detail->password : null) }}}" name="password" id="password">
						@if ($errors->has('password')) <div class="errors_msg">{{ $errors->first('password') }}</div>@endif
					</div>
				</div>
				<?php if(!isset($user_detail)){ ?>
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="confirm_password">Confirm password<span class="required">*</span></label>
						<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('confirm_password')) bad @endif">
							<input type="password" placeholder="" class="form-control" value="{{{ old('confirm_password', isset($user_detail) ? $user_detail->confirm_password : null) }}}" name="confirm_password" id="confirm_password">
							@if ($errors->has('confirm_password')) <div class="errors_msg">{{ $errors->first('confirm_password') }}</div>@endif
						</div>
					</div>
				<?php } ?>
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
				name : 'required',
				email : {
					email:true,
					required : true
				},
				mobile_number : {
					required:true,
					minlength:10,
				  	maxlength:10,
				  	number: true
				},
				first_name : 'required',
				last_name : 'required',
				address : 'required',
				/*block : 'required',*/
				/*street : 'required',*/
				country : 'required',
				state : 'required',
				city : 'required',
				pincode : {
					required:true,
					minlength:6,
				  	maxlength:6,
				  	number: true
				},
				lat : {
					required:true,
				  	number: true
				},
				lon : {
					required:true,
				  	number: true
				},
				radius : 'required',
				start_time : 'required',
				close_time : 'required',
				discount : 'required',
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
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});

			$( "#adharcard_image" ).rules( "add", {
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp|pdf",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});

			$( "#pancard_image" ).rules( "add", {
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp|pdf",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});
			$( "#druglicense_image" ).rules( "add", {
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp|pdf",
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
     
 $('#country').change(function(){
  var countryID = $(this).val();  
  if(countryID){
    $.ajax({
      type:"GET",
      url:"{{url('get-state-list')}}?country_id="+countryID,
      success:function(res){        
      if(res){
		$("#state").prop("disabled", false);
        $("#state").empty();
        $("#state").append('<option>Select</option>');
        $.each(res,function(key,value){
          $("#state").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#state").empty();
      }
      }
    });
  }else{
    $("#state").empty();
    $("#city").empty();
  }   
  });
  $('#state').on('change',function(){
  var stateID = $(this).val();  
  if(stateID){
    $.ajax({
      type:"GET",
      url:"{{url('get-city-list')}}?state_id="+stateID,
      success:function(res){        
      if(res){
		$("#city").prop("disabled", false);
        $("#city").empty();
        $("#city").append('<option>Select</option>');
        $.each(res,function(key,value){
          $("#city").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#city").empty();
      }
      }
    });
  }else{
    $("#city").empty();
  }
    
  });

  $(".deleteImage").click(function(){
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url:'{{ url("/pharmacy/delete_image") }}',
                type: 'POST',
                success: function (data) {
                    $('.image_div').remove();
                    $('#image_'+id).remove();
                }
            });
        });

  $(".deleteImagepan").click(function(){
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url:'{{ url("/pharmacy_pan/delete_image_pan") }}',
                type: 'POST',
                success: function (data) {
                    $('.image_div_pan').remove();
                    $('#image_'+id).remove();
                }
            });
        });

  $(".deleteImageProfile").click(function(){
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url:'{{ url("/pharmacy_profile/delete_image_profile") }}',
                type: 'POST',
                success: function (data) {
                    $('.image_div_pan').remove();
                    $('#image_'+id).remove();
                }
            });
        });
 </script>
	
@endsection
