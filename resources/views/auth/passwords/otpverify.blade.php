@extends('layouts.loginapp')

@section('content')
<div class=" card-box" style="padding:0">
	<div class="panel-body" style="padding:0 15px;">
		<div class="row">
			<div class="col-sm-3" style="padding-top:120px;">
				<h1 align="center">OTP Verification</h1>
				<p style="text-align:center;">Enter the verification code we sent you on your email or mobile.</p>
				@if (\Session::has('error'))
					<div class="alert alert-danger alert-dismissable">
	                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	                    {!! \Session::get('error') !!}
	                </div>
				@endif
				<form method="POST" action="{{ route('otpverify') }}" class="form-horizontal m-t-20" >
				@csrf
				<input type="hidden" name="id" value="{{ $id }}"/>
					<div class="form-group">
						<div class="col-xs-12">
							<label>OTP</label>
							<input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" value="{{ old('otp') }}" autocomplete="otp" autofocus>

							@error('otp')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>
		 
					<div class="form-group text-center m-t-40">
						<div class="col-xs-12">
							<button class="btn btn-info btn-block text-uppercase waves-effect waves-light" type="submit">{{ __('Send') }}</button>			
						</div>
					</div>
				</form>
				<p style="text-align:center;">{{ __("Remember?") }} <a href="{{ route('login') }}" class="text-primary m-l-5"><b>{{ __('Signin') }}</b></a></p>				
			</div>
			<?php $image = url('/').'/public/images/login.jpg' ?>
			<div class="col-sm-9" style="background:url('<?php echo $image; ?>');background-size: cover;background-position: center;height: 630px;">
			</div>
		</div>
	</div>   
</div>
@endsection
