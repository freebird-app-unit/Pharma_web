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
			<form class="form-horizontal" method="POST" action="@if(isset($user_detail)){{ route('seller.edit',array('id'=>$user_detail->id)) }} @else{{ route('seller.create') }}@endif" id="user_detail-form" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<input type="hidden" name="user_type" id="user_type" value="seller"/>
			<input type="hidden" name="edit_id" id="edit_id" value="@if(isset($user_detail)) {{$user_detail->id}} @else 0 @endif"/>
			<input type="hidden" name="pharma_logistic_id" id="pharma_logistic_id" value="{{ Auth::user()->user_id }}"/>
			<?php 
				if(Auth::user()->user_type=='admin'){
				?>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pharmacy">Pharmacy</label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('pharmacy')) bad @endif">
						<select class="form-control" name="pharmacy" id="pharmacy">
							<option value="">Select Pharmacy</option>
							<?php 
							foreach($pharmacy_list as $pharmacy){
								if(isset($user_detail) && $user_detail->pharma_logistic_id==$pharmacy->id){
										$sel = 'selected';
									}else if(old('pharmacy')==$pharmacy->id){
										$sel = 'selected';
									}else{
										$sel = '';
									}
								echo '<option '.$sel.' value="'.$pharmacy->id.'">'.$pharmacy->name.'</option>';
							}
							?>
						</select>
						@if ($errors->has('pharmacy')) <div class="errors_msg">{{ $errors->first('pharmacy') }}</div>@endif
					</div>
				</div>
				<?php 
				}else{
				?>
					<input type="hidden" name="pharmacy" id="pharmacy" value="{{ Auth::user()->user_id }}"/>
				<?php	
				}
				?>
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
						<input type="text" placeholder="" class="form-control only_number" value="{{{ old('mobile_number', isset($user_detail) ? $user_detail->mobile_number : null) }}}" name="mobile_number" id="mobile_number" maxlength="10">
						@if ($errors->has('mobile_number')) <div class="errors_msg">{{ $errors->first('mobile_number') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">ProfileImage:</label> 
					<input type="file" class="filestyle" name="profile_image" data-input="false">
					@if(!empty($user_detail->profile_image) && isset($user_detail->profile_image))
					<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-12">
						<a href="javascript:void(0)">
							@if (file_exists(storage_path('app/public/uploads/new_seller/'.$user_detail->profile_image)))
								@php $image_path = asset('storage/app/public/uploads/new_seller/' . $user_detail->profile_image) @endphp
							@else 
								{{ $image_path = '' }}
							@endif
							<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							<a style="cursor: pointer;" class="m-l-10 action-icon deleteImage"><i class="fa fa-trash text-danger"></i></a>
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
						<input type="text" placeholder="" class="form-control only_number" value="{{{ old('pincode', isset($user_detail) ? $user_detail->pincode : null) }}}" name="pincode" id="pincode">
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

		$("#user_detail-form").validate({
			rules: {
				pharmacy: 'required',
				email : {
					email:true,
					required : true
				},
				name : 'required',
				mobile_number : {
					required:true,
					minlength:10,
				  	maxlength:10,
				  	number: true
				},
				address : 'required',
				block : 'required',
				street : 'required',
				pincode : {
					required:true,
					minlength:6,
				  	maxlength:6,
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
			},
			highlight: function(element) {
			  $(element).removeClass('is-valid').addClass('is-invalid');
			},
			unhighlight: function(element) {
			  $(element).removeClass('is-invalid').addClass('is-valid');
			},
		});
		
		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			if ($("#user_detail-form").valid()) { 
				$("#user_detail-form").submit();
			}
		});
	});

	$(".deleteImage").click(function(){
		edit_id = $('#edit_id').val();
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url:'{{ url("/seller/delete_image") }}',
                type: 'POST',
                data: 'edit_id='+edit_id,
                success: function (data) {
                    $('.image_div').remove();
                    $('#image_'+id).remove();
                }
            });
        });
     
 </script>
	
@endsection
