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
		@if(Session::has('success_message'))
			<div class="alert alert-success alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				{{ Session::get('success_message') }}
	        </div>
		@endif
			<form class="form-horizontal" method="POST" action="{{ route('settings') }}" id="clinic_detail-form" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="site_name">Site name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('site_name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('site_name', isset($setting) ? $setting->site_name : null) }}}" name="site_name" id="site_name">
						@if ($errors->has('site_name')) <div class="errors_msg">{{ $errors->first('site_name') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="site_email">Site email<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('site_email')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('site_email', isset($setting) ? $setting->site_email : null) }}}" name="site_email" id="site_email">
						@if ($errors->has('site_email')) <div class="errors_msg">{{ $errors->first('site_email') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="site_contact">Site contact<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('site_contact')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('site_contact', isset($setting) ? $setting->site_contact : null) }}}" name="site_contact" id="site_contact">
						@if ($errors->has('site_contact')) <div class="errors_msg">{{ $errors->first('site_contact') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="site_address">Address<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('site_address')) bad @endif">
						<textarea class="form-control" name="site_address" id="site_address">{{{ old('site_address', isset($setting) ? $setting->site_address : null) }}}</textarea>
						@if ($errors->has('site_address')) <div class="errors_msg">{{ $errors->first('site_address') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="site_logo">Logo<span class="required">*</span></label>
					<div class="col-md-4 col-sm-4 col-xs-12  @if($errors->has('site_logo')) bad @endif">
						<input type="file" class="form-control" name="site_logo" id="site_logo">
						@if ($errors->has('site_logo')) <div class="errors_msg">{{ $errors->first('site_logo') }}</div>@endif
					</div>
					<div class="col-md-4 col-sm-4 col-xs-12">
						<?php 
						$file_url = '';
						if($setting->site_logo!=''){
							$destinationPath = base_path() . '/uploads/'.$setting->site_logo;
							if(file_exists($destinationPath)){
								$file_url = url('/').'/uploads/'.$setting->site_logo;
							}else{
								$file_url = url('/').'/uploads/placeholder.png';
							}
						}else{
							$file_url = url('/').'/uploads/placeholder.png';
						}
						?>
						<img width="100" src="<?php echo $file_url; ?>"/>
						<input type="hidden" name="old_logo" value="{{ $setting->site_logo }}"/>
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
				site_name : {
					required : true
				},
				site_email : {
					email:true,
					required : true
				},
				site_address : 'required'
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
