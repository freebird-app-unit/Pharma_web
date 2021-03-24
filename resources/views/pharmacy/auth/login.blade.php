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
					<input id="user_type" type="hidden" name="user_type" value="new_pharmacies">
					<div class="form-group">
						<div class="col-xs-12">
							<input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" autofocus placeholder="Email">
							@error('email')
								<span class="invalid-feedback errors_msg" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group">
						<div class="col-xs-12">
							<input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password" placeholder="Password">
							@error('password')
								<span class="invalid-feedback errors_msg" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group ">
						<div class="col-xs-12">
							<div class="checkbox checkbox-primary">
								<input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
								<label for="remember">
									Remember me
								</label>
							</div>
						</div>
					</div>
						
					<div class="form-group text-center m-t-40">
						<div class="col-xs-12">
							<button class="btn btn-info btn-block text-uppercase waves-effect waves-light" type="submit">{{ __('Login') }}</button>			
						</div>
					</div>
					@if (Route::has('resetpassword'))				
					<div class="form-group m-t-30 m-b-0">
						<div class="col-sm-12">
							<a href="{{ route('resetpassword') }}" class="text-dark"><i class="fa fa-lock m-r-5"></i>{{ __('Forgot Your Password?') }}</a>
						</div>
					</div>
					@endif
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
