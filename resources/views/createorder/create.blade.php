@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title"></h4>
			<ol class="breadcrumb">
				<li><a href="{{ url('/') }}">Dashboard</a></li>
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
			<form class="form-horizontal" method="POST" action="{{ route('createorder.create') }}" id="user_detail-form" enctype="multipart/form-data">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="user_id">User<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('user_id')) bad @endif">
						<select class="form-control" name="user_id" id="user_id">
							<option selected>Select User</option>
							<?php 
							foreach($users as $user){
								echo '<option value="'.$user->id.'" >'.$user->name.'</option>';
							}
							?>
						</select>
						@if ($errors->has('user_id')) <div class="errors_msg">{{ $errors->first('user_id') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="prescription_id">Saved Prescription<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('prescription_id')) bad @endif">
						<select class="form-control" name="prescription_id" id="prescription_id" disabled>
							<option value=''>Select Prescription</option>
							
						</select>
						@if ($errors->has('prescription_id')) <div class="errors_msg">{{ $errors->first('prescription_id') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="address_id">Select Address<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('address_id')) bad @endif">
						<select class="form-control" name="address_id" id="address_id" disabled>
							<option value=''>Select Address</option>
							
						</select>
						@if ($errors->has('address_id')) <div class="errors_msg">{{ $errors->first('address_id') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pharmacy_id">Pharmacy<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('pharmacy_id')) bad @endif">
						<select class="form-control" name="pharmacy_id" id="pharmacy_id">
							<option selected>Select Pharmacy</option>
							<?php 
							foreach($pharmacies as $pharmacy){
								echo '<option value="'.$pharmacy->id.'" >'.$pharmacy->name.'</option>';
							}
							?>
						</select>
						@if ($errors->has('pharmacy_id')) <div class="errors_msg">{{ $errors->first('pharmacy_id') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="freepaid">Choose Order Free Or Paid<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('freepaid')) bad @endif">
						<select class="form-control" name="freepaid" id="freepaid">
							<option>select</option>
							<option value="free">free</option>
							<option value="paid">paid</option>
						</select>
						@if ($errors->has('freepaid')) <div class="errors_msg">{{ $errors->first('freepaid') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="leaved_with_neighbor">leaved with neighbor<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('leaved_with_neighbor')) bad @endif">
						<select class="form-control" name="leaved_with_neighbor" id="leaved_with_neighbor">
							<option>select</option>
							<option value="true">yes</option>
							<option value="false">no</option>
						</select>
						@if ($errors->has('leaved_with_neighbor')) <div class="errors_msg">{{ $errors->first('leaved_with_neighbor') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="ordertype">Order Type<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('ordertype')) bad @endif">
						<select class="form-control" name="ordertype" id="ordertype">
							<option>select</option>
							<option value="as_per_prescription">as_per_prescription</option>
							<option value="full_order">full_order</option>
							<option value="selected_item">selected_item</option>
						</select>
						@if ($errors->has('ordertype')) <div class="errors_msg">{{ $errors->first('ordertype') }}</div>@endif
					</div>
				</div>
				<div class="form-group" id="total_days" style="display: none">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="total_days">Total Days<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('total_days')) bad @endif">
						<input type="text" placeholder="" class="form-control" name="total_days" id="total_days">
						@if ($errors->has('total_days')) <div class="errors_msg">{{ $errors->first('total_days') }}</div>@endif
					</div>
				</div>

				<div class="form-group" id="order_note" style="display: none">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="order_note">Order Note<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('order_note')) bad @endif">
						<input type="text" placeholder="" class="form-control" name="order_note" id="order_note">
						@if ($errors->has('order_note')) <div class="errors_msg">{{ $errors->first('order_note') }}</div>@endif
					</div>
				</div>

				<div class="form-group" id="logistic_id" style="display: none">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="logistic_id">Logistic Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('logistic_id')) bad @endif">
						<input type="text" placeholder="" class="form-control" name="logistic_id" id="logistic_id" value="{{$logistics->name}}">
						@if ($errors->has('logistic_id')) <div class="errors_msg">{{ $errors->first('logistic_id') }}</div>@endif
					</div>
				</div>

				<div class="form-group" id="delivery_charges_id" style="display: none">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="delivery_charges_id">Delivery Charges<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('delivery_charges_id')) bad @endif">
						<select class="form-control" name="delivery_charges_id" id="delivery_charges_id">
							<option selected>Select Delivery Charges</option>
							<?php 
							foreach($delivery_charges as $delivery_charge){
								echo '<option value="'.$delivery_charge->id.'" >'.$delivery_charge->delivery_type.'</option>';
							}
							?>
						</select>
						@if ($errors->has('delivery_charges_id')) <div class="errors_msg">{{ $errors->first('delivery_charges_id') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
						<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="button" value="Save">
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
	var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			if ($("#user_detail-form").valid()) { 
				$("#user_detail-form").submit();
			}
		});
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
				user_id : 'required',
				prescription_id :'required',
				address_id : 'required',
				pharmacy_id : 'required',
				freepaid : 'required',
				leaved_with_neighbor : 'required',
				ordertype : 'required',
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

			$( "#license_image" ).rules( "add", {
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
		@endif

		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			if ($("#user_detail-form").valid()) { 
				$("#user_detail-form").submit();
			}
		});
	});
     
 $('#user_id').change(function(){
  var UserID = $(this).val();  
  if(UserID){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('get-prescription-list')}}?user_id="+UserID,
      success:function(res){        
      if(res){
		$("#prescription_id").prop("disabled", false);
        $("#prescription_id").empty();
        $.each(res,function(key,value){
          $("#prescription_id").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#prescription_id").empty();
      }
      }
    });
  }else{
    $("#prescription_id").empty();
  }   
  });

	 $('#user_id').change(function(){
	  var UserID = $(this).val();  
	  if(UserID){
	    $.ajax({
	      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
	      type:"GET",
	      url:"{{url('get-address-list')}}?user_id="+UserID,
	      success:function(res){        
	      if(res){
			$("#address_id").prop("disabled", false);
	        $("#address_id").empty();
	        $.each(res,function(key,value){
	          $("#address_id").append('<option value="'+key+'">'+value+'</option>');
	        });
	      
	      }else{
	        $("#address_id").empty();
	      }
	      }
	    });
	  }else{
	    $("#address_id").empty();
	  }   
	  });

	$('#freepaid').change(function(){
	            if ($(this).val() == "paid") {
					$("#logistic_id").show();
					$("#delivery_charges_id").show();
	                $('#ordertype').change(function(){
			            if ($(this).val() == "full_order") {
			            	$("#order_note").hide();
			                $("#total_days").show();
			            } else if($(this).val() == "selected_item"){
			            	$("#total_days").hide();
			            	$("#order_note").show();
			            }else {
			                $("#total_days").hide();
			                $("#order_note").hide();
			            }
	        		});
	            } else {
	                $("#logistic_id").hide();
	                $("#delivery_charges_id").hide();
	                $('#ordertype').change(function(){
			            if ($(this).val() == "full_order") {
			            	$("#order_note").hide();
			                $("#total_days").show();
			            } else if($(this).val() == "selected_item"){
			            	$("#total_days").hide();
			            	$("#order_note").show();
			            }else {
			                $("#total_days").hide();
			                $("#order_note").hide();
			            }
	        		});
	            }

	    });
  
 </script>
	
@endsection
