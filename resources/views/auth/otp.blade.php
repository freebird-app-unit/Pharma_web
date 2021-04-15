@extends('layouts.loginapp')

@section('content')
<div class=" card-box" style="padding:0;">
	<div class="panel-body" style="padding:0 15px;">
		<div class="row">
			<div class="col-sm-3">
				<h1 align="center">Login</h1>
				@if (\Session::has('success'))
					<div class="alert alert-success alert-dismissable">
	                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	                    {!! \Session::get('success') !!}
	                </div>
				@endif
				<form method="POST" action="{{ route('login') }}" class="form-horizontal m-t-20" style="padding:80px 0;">
				@csrf
					<!-- <input id="user_type" type="hidden" name="user_type" value="admin"> -->
					<input id="mobile_number" type="hidden" name="mobile_number" value="{{ $mobile_number }}">

					<input id="password" type="hidden" name="password" value="{{ $password }}">
					<input id="mobile_otp" type="hidden" name="mobile_otp" value="{{ $mobile_otp }}">
					<div class="form-group">
						<div class="col-xs-12">
							<input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" value="{{ old('otp') }}" autocomplete="otp" autofocus placeholder="OTP">
							<span class="invalid-feedback errors_msg" role="alert">
								<strong>{{ $mobile_otp_msg }}</strong>
							</span>
						</div>
					</div>
					
					<div class="form-group text-center m-t-40">
						<div class="col-xs-12">
							<button class="btn btn-info btn-block text-uppercase waves-effect waves-light" type="submit">{{ __('Verify') }}</button>			
						</div>
					</div>
				</form> 
			</div>
			<?php $image = url('/').'/public/images/login.jpg' ?>
			<div class="col-sm-9" style="background:url('<?php echo $image; ?>');background-size: cover;background-position: center;height: 630px;">
			</div>
	</div>   
</div>                              
<!--<div class="row">
	<div class="col-sm-12 text-center">
		<p>{{ __("Don't have an account?") }} <a href="{{ route('register') }}" class="text-primary m-l-5"><b>{{ __('Sign Up') }}</b></a></p>
	</div>
</div>-->
@endsection
