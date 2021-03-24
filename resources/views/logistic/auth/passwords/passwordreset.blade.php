@extends('layouts.loginapp')

@section('content')
<div class=" card-box" style="padding:0">
	<div class="panel-body" style="padding:0 15px;">
		<div class="row">
			<div class="col-sm-3" style="padding-top:120px;">
				<h1 align="center">Reset password</h1>
				@if (\Session::has('error'))
					<div class="alert alert-danger alert-dismissable">
	                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	                    {!! \Session::get('error') !!}
	                </div>
				@endif
				<form method="POST" action="{{ route('passwordreset') }}" class="form-horizontal m-t-20" >
				@csrf
					<input type="hidden" name="id" value="{{ $id }}"/>
					<div class="form-group">
						<div class="col-xs-12">
							<label>New password</label>
							<input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" autocomplete="password" autofocus>

							@error('password')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<label>Confirm password</label>
							<input id="confirm_password" type="password" class="form-control @error('confirm_password') is-invalid @enderror" name="confirm_password" value="{{ old('confirm_password') }}" autocomplete="confirm_password" autofocus>

							@error('confirm_password')
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
