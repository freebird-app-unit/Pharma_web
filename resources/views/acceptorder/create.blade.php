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
		@if(Session::has('unsuccess_message'))
			<div class="alert alert-danger alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				{{ Session::get('unsuccess_message') }}
	        </div>
		@endif
			<form class="form-horizontal" method="POST" action="{{ route('acceptorder.create') }}" id="user_detail-form" enctype="multipart/form-data">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="order_number">Order Number<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('order_number')) bad @endif">
						<select class="form-control" name="order_number" id="order_number" disabled>
							<option value=''>Order Number</option>
							
						</select>
						@if ($errors->has('order_number')) <div class="errors_msg">{{ $errors->first('order_number') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="customer_id">Customer Name<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('customer_id')) bad @endif">
						<select class="form-control" name="customer_id" id="customer_id" disabled>
							<option value=''>Customer Name</option>
							
						</select>
						@if ($errors->has('customer_id')) <div class="errors_msg">{{ $errors->first('customer_id') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="seller_id">Seller<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('seller_id')) bad @endif">
						<select class="form-control" name="seller_id" id="seller_id" disabled>
							<option value=''>seller</option>
							
						</select>
						@if ($errors->has('seller_id')) <div class="errors_msg">{{ $errors->first('seller_id') }}</div>@endif
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="accept_reject">Choose Accpet Or Reject<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('accept_reject')) bad @endif">
						<select class="form-control" name="accept_reject" id="accept_reject">
							<option>select</option>
							<option value="accept">Accept</option>
							<option value="reject">Reject</option>
						</select>
						@if ($errors->has('accept_reject')) <div class="errors_msg">{{ $errors->first('accept_reject') }}</div>@endif
					</div>
				</div>

				<div class="form-group" id="order_amount" style="display: none">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="order_amount">Order Amount<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('order_amount')) bad @endif">
						<input type="text" placeholder="" class="form-control" name="order_amount" id="order_amount">
						@if ($errors->has('order_amount')) <div class="errors_msg">{{ $errors->first('order_amount') }}</div>@endif
					</div>
				</div>

				<div class="form-group" id="invoice" style="display: none">
					<label class="control-label col-md-2 col-sm-2 col-xs-12">Invoice</label> 
					<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('invoice')) bad @endif">
						<input type="file" class="form-control" id="invoice" name="invoice"  data-input="false">
						@if ($errors->has('invoice')) <div class="errors_msg">{{ $errors->first('invoice') }}</div>@endif
					</div>
				</div>

				<div class="form-group" id="reject_reason" style="display: none">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="reject_reason">Reject Reason<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('reject_reason')) bad @endif">
						<input type="text" placeholder="" class="form-control" name="reject_reason" id="reject_reason">
						@if ($errors->has('reject_reason')) <div class="errors_msg">{{ $errors->first('reject_reason') }}</div>@endif
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
     
 $('#pharmacy_id').change(function(){
  var PharmacyID = $(this).val();  
  if(PharmacyID){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('get-order-list')}}?pharmacy_id="+PharmacyID,
      success:function(res){        
      if(res){
		$("#order_number").prop("disabled", false);
        $("#order_number").empty();
        $.each(res,function(key,value){
          $("#order_number").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#order_number").empty();
      }
      }
    });
  }else{
    $("#order_number").empty();
  }   
  });

 $('#pharmacy_id').change(function(){
  var PharmacyID = $(this).val();  
  if(PharmacyID){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('get-seller-list')}}?pharmacy_id="+PharmacyID,
      success:function(res){        
      if(res){
		$("#seller_id").prop("disabled", false);
        $("#seller_id").empty();
        $.each(res,function(key,value){
          $("#seller_id").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#seller_id").empty();
      }
      }
    });
  }else{
    $("#seller_id").empty();
  }   
  });

$('#order_number').change(function(){
  var OrderNumber = $(this).val();  
  if(OrderNumber){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('get-customer-list')}}?order_number="+OrderNumber,
      success:function(res){        
      if(res){
		$("#customer_id").prop("disabled", false);
        $("#customer_id").empty();
        $.each(res,function(key,value){
          $("#customer_id").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#customer_id").empty();
      }
      }
    });
  }else{
    $("#customer_id").empty();
  }   
  });

	$('#accept_reject').change(function(){
	            if ($(this).val() == "accept") {
					$("#order_amount").show();
					$("#invoice").show();
					$("#reject_reason").hide();
	            } else {
	               	$("#order_amount").hide();
	               	$("#invoice").hide();
				   	$("#reject_reason").show();
	            }
	    });
  
 </script>
	
@endsection
