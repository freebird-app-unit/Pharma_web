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
<script>
	const pharmacies = [];
	@if(isset($pharmacies))
		@foreach ($pharmacies as $service)
			pharmacies.push(JSON.parse('{!! json_encode($service) !!}'));
		@endforeach
	@endif

	const logistics = [];
	@if(isset($logistics))
		@foreach ($logistics as $service)
			logistics.push(JSON.parse('{!! json_encode($service) !!}'));
		@endforeach
	@endif

	const countries = [];
	@if(isset($countries))
		@foreach ($countries as $country)
			countries.push(JSON.parse('{!! json_encode($country) !!}'));
		@endforeach
	@endif

</script>
<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
			<form class="form-horizontal" method="POST" action="@if(isset($user_detail)){{ route('user.edit',array('id'=>$user_detail->id)) }} @else{{ route('user.create') }}@endif" id="user_detail-form" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="user_type" value="customer">
				<?php /* 
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="user_type">Role<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('name')) bad @endif">
						<select class="form-control" name="user_type" id="user_type" onchange="display_parent_user(this.value)" <?php echo (isset($user_detail)) ?'disabled':''; ?>>
							<option value="">Select role</option>
							<option <?php echo ((isset($user_detail) && $user_detail->user_type=='pharmacy') || (old('user_type') == 'pharmacy'))?'selected':''; ?> value="pharmacy">Pharmacy</option>
							<option <?php echo ((isset($user_detail) && $user_detail->user_type=='seller') || (old('user_type') == 'seller'))?'selected':''; ?> value="seller">Seller</option>
							<option <?php echo ((isset($user_detail) && $user_detail->user_type=='delivery_boy') || (old('user_type') == 'delivery_boy'))?'selected':'';  ?> value="delivery_boy">Delivery boy</option>
							<option <?php echo ((isset($user_detail) && $user_detail->user_type=='customer') || (old('user_type') == 'customer'))?'selected':''; ?> value="customer">Customer</option>
						</select>
						@if ($errors->has('user_type')) <div class="errors_msg">{{ $errors->first('user_type') }}</div>@endif
					</div>
				</div>
				*/ ?>
				@if(isset($user_detail->user_type))
					<input type="hidden" name="user_type" value="{!! $user_detail->user_type !!}">
				@endif
				<?php 
					$dispParent = 'none';
					$dispParentType = 'none';
					$dispRadious = 'none';
					$displocation = 'none';
					$dispCountry = 'none';
					$dispimage = 'none';
					$dispdoc = 'none';
					$disptime = 'none';
					$dispdiscount = 'none';
					$dispOwner = 'none';
					$dispAddress = 'none';

					if(isset($user_detail)){
						switch ($user_detail->user_type) {
							case 'delivery_boy':
								$img = 'storage/app/public/uploads/new_delivery_boy/';
								$image_path = 'app/public/uploads/new_delivery_boy/';
								$dispParent = 'block';
								$dispParentType = 'block';
								$dispimage = 'block';
								$dispAddress = 'block';
								break;

							case 'seller':
								$img = 'storage/app/public/uploads/new_seller/';
								$image_path = 'app/public/uploads/new_seller/';
								$dispParent = 'block';
								$dispimage = 'block';
								$dispAddress = 'block';
								break;

							case 'pharmacy':
								$img_pancard = 'storage/app/public/uploads/new_pharmacy/pancard/';
								$img_license = 'storage/app/public/uploads/new_pharmacy/license/';
								$img = 'storage/app/public/uploads/new_pharmacy/';
								$image_path = 'app/public/uploads/new_pharmacy/';
								$pancard_path = 'app/public/uploads/new_pharmacy/pancard/';
								$license_path = 'app/public/uploads/new_pharmacy/license/';
								$displocation = 'block';
								$dispCountry = 'block';
								$dispRadious = 'block';
								$dispdoc = 'block';
								$disptime = 'block';
								$dispdiscount = 'block';
								$dispOwner = 'block';
								$dispimage = 'block';
								$dispAddress = 'block';
								break;
							
							default:
								$img = 'storage/app/public/uploads/new_user/';
								$image_path = 'app/public/uploads/new_user/';
								$dispimage = 'block';
								break;
						}
					}


					if(old('user_type')){
						switch (old('user_type')) {
							case 'delivery_boy':
								$img = 'storage/app/public/uploads/new_delivery_boy/';
								$image_path = 'app/public/uploads/new_delivery_boy/';
								$dispParent = 'block';
								$dispParentType = 'block';
								$dispimage = 'block';
								$dispAddress = 'block';
								break;

							case 'seller':
								$img = 'storage/app/public/uploads/new_seller/';
								$image_path = 'app/public/uploads/new_seller/';
								$dispParent = 'block';
								$dispimage = 'block';
								$dispAddress = 'block';
								break;

							case 'pharmacy':
								$img_pancard = 'storage/app/public/uploads/new_pharmacy/pancard/';
								$img_license = 'storage/app/public/uploads/new_pharmacy/license/';
								$img = 'storage/app/public/uploads/new_pharmacy/';
								$image_path = 'app/public/uploads/new_pharmacy/';
								$pancard_path = 'app/public/uploads/new_pharmacy/pancard/';
								$license_path = 'app/public/uploads/new_pharmacy/license/';
								$displocation = 'block';
								$dispCountry = 'block';
								$dispRadious = 'block';
								$dispdoc = 'block';
								$disptime = 'block';
								$dispdiscount = 'block';
								$dispOwner = 'block';
								$dispimage = 'block';
								$dispAddress = 'block';
								break;
							
							default:
								$img = 'storage/app/public/uploads/new_user/';
								$image_path = 'app/public/uploads/new_user/';
								$dispimage = 'block';
								break;
						}
					}
				?>
				<div class="form-group" id="parent_type_container" style="display:<?php echo $dispParentType; ?>;">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="parentuser_type">Parent Type<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('parentuser_type')) bad @endif">
						<label class="radio-inline">
							<input type="radio" name="parent_type" class='parent_type' id="parent_type_pharmacy" value='pharmacy' <?php echo ((isset($user_detail) && $user_detail->parent_type=='pharmacy') || (old('user_type') == 'customer'))?'checked':''; ?>>Pharmacy
						</label>
						<label class="radio-inline">
							<input type="radio" name="parent_type" class='parent_type' id="parent_type_logistic" value='logistic' <?php echo ((isset($user_detail) && $user_detail->parent_type=='logistic') || (old('user_type') == 'customer'))?'checked':''; ?>>Logistic
						</label>
					</div>
				</div>
			
				<div class="form-group" id="parent_user_container" style="display:<?php echo $dispParent; ?>;">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="parentuser_id">Parent user<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('parentuser_id')) bad @endif">
						<select class="form-control" name="parentuser_id" id="parentuser_id">
							<option value=''>Select Parent Type</option>
							<?php
							if(count($pharmacies)>0){
								foreach($pharmacies as $pharmacy){
									$sel = '';
									if(isset($user_detail) && $user_detail->pharma_logistic_id==$pharmacy->id){
										$sel = 'selected';
									}else if(old('pharma_logistic_id')==$pharmacy->id){
										$sel = 'selected';
									}
									echo '<option '.$sel.' value="'.$pharmacy->id.'">'.$pharmacy->name.'</option>';
								}
							}
							?>
						</select>
						@if ($errors->has('parentuser_id')) <div class="errors_msg">{{ $errors->first('parentuser_id') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="name">Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('name')) bad @endif">
						<input type="text" placeholder="" class="form-control" value="{{{ old('name', isset($user_detail) ? $user_detail->name : null) }}}" name="name" id="name">
						@if ($errors->has('name')) <div class="errors_msg">{{ $errors->first('name') }}</div>@endif
					</div>
				</div>
				<div class="form-group" id="ownerBlock" style="display:<?php echo $dispOwner; ?>;">
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
				<div id='imageBlock' style="display:<?php echo $dispimage; ?>;">
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-6"  for="profile_image">Profile Image</label>
						<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('profile_image')) bad @endif">
							<input type="file" class="form-control" name="profile_image" id="profile_image" data-input="false">
							@if ($errors->has('profile_image')) <div class="errors_msg">{{ $errors->first('profile_image') }}</div>@endif
						</div>
						@if(!empty($user_detail->profile_image) && isset($user_detail->profile_image))
						<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-6">
							<a href="javascript:void(0)">
								@if (file_exists(storage_path($image_path.$user_detail->profile_image)))
									@php $image_path = asset($img . $user_detail->profile_image) @endphp
								@else 
									{{ $image_path = '' }}
								@endif
								<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							</a>
						</div>
						@endif
					</div>
				</div>
				<div id='docBlock' style="display:<?php echo $dispdoc; ?>;">
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="license_image">License<span class="required">*</span></label>
						<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('license_image')) bad @endif">
							<input type="file" class="form-control" name="license_image" id="license_image">
							@if ($errors->has('license_image')) <div class="errors_msg">{{ $errors->first('license_image') }}</div>@endif
						</div>
						@if(!empty($user_detail->license_image) && isset($user_detail->license_image))
						<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-6">
							<a href="javascript:void(0)">
								@if (file_exists(storage_path($license_path.$user_detail->license_image)))
									@php $image_path = asset($img_license . $user_detail->license_image) @endphp
								@else 
									{{ $image_path = '' }}
								@endif
								<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							</a>
						</div>
						@endif
					</div>
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pancard_image">PAN-card<span class="required">*</span></label>
						<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('pancard_image')) bad @endif">
							<input type="file" class="form-control" name="pancard_image" id="pancard_image">
							@if ($errors->has('pancard_image')) <div class="errors_msg">{{ $errors->first('pancard_image') }}</div>@endif
						</div>
						@if(!empty($user_detail->pancard_image) && isset($user_detail->pancard_image))
						<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-6">
							<a href="javascript:void(0)">
								@if (file_exists(storage_path($pancard_path.$user_detail->pancard_image)))
									@php $image_path = asset($img_pancard . $user_detail->pancard_image) @endphp
								@else 
									{{ $image_path = '' }}
								@endif
								<img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
							</a>
						</div>
						@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="password">Password<span class="required"><?php if(isset($user_detail)){ }else{ echo '*';} ?></span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('password')) bad @endif">
						<input type="password" placeholder="" class="form-control" value="" name="password" id="password">
						@if ($errors->has('password')) <div class="errors_msg">{{ $errors->first('password') }}</div>@endif
					</div>
				</div>
				<div id='dispAddress' style="display:<?php echo $dispAddress; ?>;">
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
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="block">Street<span class="required">*</span></label>
						<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('street')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('street', isset($user_detail) ? $user_detail->street : null) }}}" name="street" id="street">
							@if ($errors->has('street')) <div class="errors_msg">{{ $errors->first('street') }}</div>@endif
						</div>
					</div>

					<div class="form-group">
						<div class="form-group">
							<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pincode">Pincode<span class="required">*</span></label>
							<div class="col-md-6 col-sm-6 col-xs-8  @if($errors->has('pincode')) bad @endif">
								<input type="text" placeholder="" class="form-control only_number" value="{{{ old('pincode', isset($user_detail) ? $user_detail->pincode : null) }}}" name="pincode" id="pincode">
								@if ($errors->has('pincode')) <div class="errors_msg">{{ $errors->first('pincode') }}</div>@endif
							</div>
						</div>
					</div>
				</div>

				<div id="countryBlock" style="display:<?php echo $dispCountry; ?>;">
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
				</div>

				<div class="form-group" id="location" style="display:<?php echo $displocation; ?>;">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="Location">Location<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('lat') && $errors->has('lon')) bad @endif">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="lat">Lat</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('lat')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('lat', isset($user_detail) ? $user_detail->lat : null) }}}" name="lat" id="lat">
						</div>

						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="lon">Long</span></label>
						<div class="col-md-3 col-sm-3 col-xs-3  @if($errors->has('lon')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('lon', isset($user_detail) ? $user_detail->lon : null) }}}" name="lon" id="lon">
						</div>
						@if ($errors->has('lat')) <div class="errors_msg">{{ $errors->first('lat') }}</div>@endif
						@if ($errors->has('lon')) <div class="errors_msg">{{ $errors->first('lon') }}</div>@endif
					</div>
				</div>

				<div class="form-group" id='timeBlock' style="display:<?php echo $disptime; ?>;">
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
				<div class="form-group" id="radiousBlock" style="display:<?php echo $dispRadious; ?>;">
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="radius">Radius( KM)<span class="required">*</span></label>
						<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('radius')) bad @endif">
							<input type="text" placeholder="value in KM" class="form-control" value="{{{ old('radius', isset($user_detail) ? $user_detail->radius : null) }}}" name="radius" id="radius">
							@if ($errors->has('radius')) <div class="errors_msg">{{ $errors->first('radius') }}</div>@endif
						</div>
					</div>
				</div>

				<div class="form-group" id="discountBlock" style="display:<?php echo $dispdiscount; ?>;">
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-12" for="discount">Discount<span class="required">*</span></label>
						<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('discount')) bad @endif">
							<input type="text" placeholder="" class="form-control" value="{{{ old('discount', isset($user_detail) ? $user_detail->discount : null) }}}" name="discount" id="discount">
							@if ($errors->has('discount')) <div class="errors_msg">{{ $errors->first('discount') }}</div>@endif
						</div>
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
<?php if(!isset($user_detail)){ $user_detail = (object)array(); $user_detail->user_type='0'; } ?>
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
				email : {
					email: true,
					required : true
				},
				user_type : 'required',
				name : 'required',
				mobile_number : {
						required:true,
						minlength:10,
					  	maxlength:10,
					  	number: true
				},
				//password : 'required',
				// address : 'required',
				// block : 'required',
				// street : 'required',
				// pincode: 'required',
			},
			highlight: function(element) {
			  $(element).removeClass('is-valid').addClass('is-invalid');
			},
			unhighlight: function(element) {
			  $(element).removeClass('is-invalid').addClass('is-valid');
			},
		});

		/*@if(isset($user_detail))
			$( "#image" ).rules( "add", {
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});

			@if($user_detail->user_type == 'pharmacy')
				$( "#license_image" ).rules( "add", {
					extension: "jpg|jpeg|png|ico|bmp|pdf",
					messages: {
						required: "Please upload file.",
						extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp, pdf)."
					}
				});

				$( "#pancard_image" ).rules( "add", {
					extension: "jpg|jpeg|png|ico|bmp|pdf",
					messages: {
						required: "Please upload file.",
						extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp, pdf)."
					}
				});
			@endif
		@endif*/

		var user_type = '{!! $user_detail->user_type !!}';

		if(user_type == 'pharmacy'){
			$('#radius').rules('add',  { 'required': true });
			$('#lat').rules('add',  { 'required': true });
			$('#lon').rules('add',  { 'required': true });
			$('#country').rules('add',  { 'required': true });
			$('#state').rules('add',  { 'required': true });
			$('#city').rules('add',  { 'required': true });
			$('#start_time').rules('add',  { 'required': true });
			$('#close_time').rules('add',  { 'required': true });
			$('#discount').rules('add',  { 'required': true });
			$('#owner_name').rules('add',  { 'required': true });
			$('#address').rules('add',  { 'required': true });
			$('#block').rules('add',  { 'required': true });
			$('#street').rules('add',  { 'required': true });
			$('#pincode').rules('add',  { 'required': true });
		}

		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			if ($("#user_detail-form").valid()) { 
				$("#user_detail-form").submit();
			}
		});
	});
     
 </script>
	
@endsection
