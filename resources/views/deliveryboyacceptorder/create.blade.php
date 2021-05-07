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
			<form class="form-horizontal" method="POST" action="{{ route('deliveryboyacceptorder.create') }}" id="user_detail-form" enctype="multipart/form-data">
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="assign">Order Status<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('assign')) bad @endif">
						<input class="form-control" name="assign" id="assign" value="assign" disabled>
						@if ($errors->has('assign')) <div class="errors_msg">{{ $errors->first('assign') }}</div>@endif
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="free_paid">Order Free Or Paid<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('free_paid')) bad @endif">
						<select class="form-control" name="free_paid" id="free_paid" disabled>
							<option value=''>Order Free Or Paid</option>
							
						</select>
						@if ($errors->has('free_paid')) <div class="errors_msg">{{ $errors->first('free_paid') }}</div>@endif
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
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="deliverylocation">Delivery Location<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('deliverylocation')) bad @endif">
						<select class="form-control" name="deliverylocation" id="deliverylocation" disabled>
							<option value=''>Delivery Location</option>
							
						</select>
						@if ($errors->has('deliverylocation')) <div class="errors_msg">{{ $errors->first('deliverylocation') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="pickuplocation">Pickup Location<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('pickuplocation')) bad @endif">
						<select class="form-control" name="pickuplocation" id="pickuplocation" disabled>
							<option value=''>Pickup Location</option>
							
						</select>
						@if ($errors->has('pickuplocation')) <div class="errors_msg">{{ $errors->first('pickuplocation') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="leavewithneighbour">Leave With Neighbour<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('leavewithneighbour')) bad @endif">
						<select class="form-control" name="leavewithneighbour" id="leavewithneighbour" disabled>
							<option value=''>Leave With Neighbour</option>
							
						</select>
						@if ($errors->has('leavewithneighbour')) <div class="errors_msg">{{ $errors->first('leavewithneighbour') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="seller">Order Assign By<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('seller')) bad @endif">
						<select class="form-control" name="seller" id="seller" disabled>
							<option value=''>Order Assign By</option>
							
						</select>
						@if ($errors->has('seller')) <div class="errors_msg">{{ $errors->first('seller') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="deliveryboy_id">Deliveryboy<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('deliveryboy_id')) bad @endif">
						<select class="form-control" name="deliveryboy_id" id="deliveryboy_id" disabled>
							<option value=''>Deliveryboy</option>
							
						</select>
						@if ($errors->has('deliveryboy_id')) <div class="errors_msg">{{ $errors->first('deliveryboy_id') }}</div>@endif
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
						<div id="accept" style="display: none">
							<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="button" value="Accept">
						</div>
						<div id="reject" style="display: none">
							<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="button" value="Reject">
						</div>
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
      url:"{{url('deliveryboyacceptorder/get-order-list')}}?pharmacy_id="+PharmacyID,
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
      url:"{{url('deliveryboyacceptorder/get-deliveryboy-list')}}?pharmacy_id="+PharmacyID,
      success:function(res){        
      if(res){
		$("#deliveryboy_id").prop("disabled", false);
        $("#deliveryboy_id").empty();
        $.each(res,function(key,value){
          $("#deliveryboy_id").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#deliveryboy_id").empty();
      }
      }
    });
  }else{
    $("#deliveryboy_id").empty();
  }   
  });

$('#order_number').change(function(){
  var OrderNumber = $(this).val();  
  if(OrderNumber){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('assign/get-customer-list')}}?order_number="+OrderNumber,
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

$('#order_number').change(function(){
  var OrderNumber = $(this).val();  
  if(OrderNumber){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('deliveryboyacceptorder/getDeliveryLocation')}}?order_number="+OrderNumber,
      success:function(res){        
      if(res){
		$("#deliverylocation").prop("disabled", false);
        $("#deliverylocation").empty();
        $.each(res,function(key,value){
          $("#deliverylocation").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#deliverylocation").empty();
      }
      }
    });
  }else{
    $("#deliverylocation").empty();
  }   
  });

$('#order_number').change(function(){
  var OrderNumber = $(this).val();  
  if(OrderNumber){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('deliveryboyacceptorder/getPikcupLocation')}}?order_number="+OrderNumber,
      success:function(res){        
      if(res){
		$("#pickuplocation").prop("disabled", false);
        $("#pickuplocation").empty();
        $.each(res,function(key,value){
          $("#pickuplocation").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#pickuplocation").empty();
      }
      }
    });
  }else{
    $("#pickuplocation").empty();
  }   
  });

$('#order_number').change(function(){
  var OrderNumber = $(this).val();  
  if(OrderNumber){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('deliveryboyacceptorder/getleaveWithNeighbourLocation')}}?order_number="+OrderNumber,
      success:function(res){        
      if(res){
		$("#leavewithneighbour").prop("disabled", false);
        $("#leavewithneighbour").empty();
        $.each(res,function(key,value){
          $("#leavewithneighbour").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#leavewithneighbour").empty();
      }
      }
    });
  }else{
    $("#leavewithneighbour").empty();
  }   
  });

$('#order_number').change(function(){
  var OrderNumber = $(this).val();  
  if(OrderNumber){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('deliveryboyacceptorder/getOrderAssign')}}?order_number="+OrderNumber,
      success:function(res){        
      if(res){
		$("#seller").prop("disabled", false);
        $("#seller").empty();
        $.each(res,function(key,value){
          $("#seller").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#seller").empty();
      }
      }
    });
  }else{
    $("#seller").empty();
  }   
  });

$('#order_number').change(function(){
  var OrderNumber = $(this).val();  
  if(OrderNumber){
    $.ajax({
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      type:"GET",
      url:"{{url('deliveryboyacceptorder/getOrderFreePaid')}}?order_number="+OrderNumber,
      success:function(res){        
      if(res){
		$("#free_paid").prop("disabled", false);
        $("#free_paid").empty();
        $.each(res,function(key,value){
        	if(value == 0){
        		$("#free_paid").append('<option value="'+key+'">free</option>');
        		$("#reject").hide();
        		$("#accept").show();
        	}else{
        		$("#free_paid").append('<option value="'+key+'">paid</option>');
        		$("#accept").show();
        		$("#reject").show();
        	}	
        });
      
      }else{
        $("#free_paid").empty();
      }
      }
    });
  }else{
    $("#free_paid").empty();
  }   
  });
 </script>
	
@endsection
