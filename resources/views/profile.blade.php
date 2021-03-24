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
			<form class="form-horizontal" method="POST" action="{{ route('profile') }}" id="clinic_detail-form" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="hidden_image" id="hidden_image" value="{{ isset($user_detail->profile_image) ? $user_detail->profile_image : '' }}">
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="clinic_name">Email<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('clinic_name')) bad @endif">
					{{ $user_detail->email }}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="name">Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('name', isset($user_detail) ? $user_detail->name : null) }}}" name="name" id="name">
						@if ($errors->has('name')) <div class="errors_msg">{{ $errors->first('name') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="mobile_number">Mobile number<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('mobile_number')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('mobile_number', isset($user_detail) ? $user_detail->mobile_number : null) }}}" name="mobile_number" id="mobile_number">
						@if ($errors->has('mobile_number')) <div class="errors_msg">{{ $errors->first('mobile_number') }}</div>@endif
					</div>
				</div>
				<?php if(Auth::user()->user_type == 'pharmacy'){ ?>
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="discount">Discount<span class="required">*</span></label>
						<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('discount')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('discount', isset($user_detail) ? $user_detail->discount : null) }}}" name="discount" id="discount">
							@if ($errors->has('discount')) <div class="errors_msg">{{ $errors->first('discount') }}</div>@endif
						</div>
					</div>
				<?php } ?>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">Profile Image:</label> 
					<input type="file" class="filestyle" name="profile_image" accept="image/x-png,image/gif,image/jpeg" data-input="false">
					@if(!empty($user_detail->profile_image) && isset($user_detail->profile_image))
					<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-12">
						<a href="javascript:void(0)">
							@if (file_exists(storage_path('app/public/uploads/users/'.$user_detail->profile_image)))
								@php $image_path = asset('storage/app/public/uploads/users/' . $user_detail->profile_image) @endphp
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
		
		$(".deleteImage").click(function(){
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url:'{{ url("/user/delete_image") }}',
                type: 'POST',
                success: function (data) {
                    $('.image_div').remove();
                    $('#image_'+id).remove();
                }
            });
        });
		 
		$("#clinic_detail-form").validate({
			rules: {
				name : 'required',
				mobile_number : 'required'
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
			console.log('clicked')
			e.preventDefault();
			
			if ($("#clinic_detail-form").valid()) { 
				$("#clinic_detail-form").submit();
			}
		}); 
	});
     
 </script>
 @endsection
