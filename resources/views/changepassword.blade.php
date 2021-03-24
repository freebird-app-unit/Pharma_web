@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title">{{ $page_title }}</h4>
			<ol class="breadcrumb">
				<li><a href="{{ url('/') }}">Dashboard</a></li>
				<li><a href="{{ url('/profile') }}">My Profile</a></li>
				<li class="active">{{ $page_title }}</li>
			</ol>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
		@if(Session::has('error'))
			<div class="alert alert-danger alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				{{ Session::get('error') }}
	        </div>
		@endif
		@if(Session::has('success_message'))
			<div class="alert alert-success alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				{{ Session::get('success_message') }}
	        </div>
		@endif
			<form class="form-horizontal" method="POST" action="{{ route('changepassword') }}" id="clinic_detail-form" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="current_password">Current password<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('current_password')) bad @endif">
						<input type="password" placeholder="" class="form-control" value="{{{ old('current_password', null) }}}" name="current_password" id="current_password">
						@if ($errors->has('current_password')) <div class="errors_msg">{{ $errors->first('current_password') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="new_password">New password<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('new_password')) bad @endif">
						<input type="password" placeholder="" class="form-control" value="{{{ old('new_password', null) }}}" name="new_password" id="new_password">
						@if ($errors->has('new_password')) <div class="errors_msg">{{ $errors->first('new_password') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="confirm_new_password">Confirm new password<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('confirm_new_password')) bad @endif">
						<input type="password" placeholder="" class="form-control" value="{{{ old('confirm_new_password', null) }}}" name="confirm_new_password" id="confirm_new_password">
						@if ($errors->has('confirm_new_password')) <div class="errors_msg">{{ $errors->first('confirm_new_password') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
						<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="button" value="Save">
						<a href="{{ route('home') }}" class="btn btn-sm btn-warning cancel">Cancel</a>
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
		 
		$("#clinic_detail-form").validate({
			rules: {
				current_password : 'required',
				new_password: {
					required: true,
					minlength: 5
				},
				confirm_new_password: {
					required: true,
					minlength: 5,
					equalTo: "#new_password"
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
			
			if ($("#clinic_detail-form").valid()) { 
				$("#clinic_detail-form").submit();
			}
		});
	});
     
 </script>
	
@endsection

