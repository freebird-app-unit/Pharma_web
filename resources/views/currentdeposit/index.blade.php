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
			<div class="card-box">
                <div class="row">
                    <div class="col-sm-6">
                         <h4 class="page-title">{{ $page_title }}</h4>
                    </div>
                    <div class="col-sm-6" align="right" style="margin-bottom: 10px;">
                        <a href="javascript:void(0)" onclick="loadForm();"  class="btn btn-primary float-right mt-2">Add</a><br/>
                    </div>
                </div>
				<div class="row">
					<div class="form-group">
						<label>Logistic: *</label>
						<select name="logistic" id="logistic" class="form-control">
							<option value="">Select Logistic</option>
							<?php 
							if(isset($logistic_list) && count($logistic_list)>0){
								foreach($logistic_list as $logistic){
									echo '<option value="'.$logistic->id.'">'.$logistic->name.'</option>';
								}
							}
							?>
						</select>
					</div>	
				</div>
                <!-- table start -->
                <table id="simpleDatatable" class="table table-stripped simpleDatatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order number</th>
							<th>Customer</th>
							<th>Mobile</th>
							<th>Address</th>
							<th>Logistic</th>
							<th>Order status</th>
							<th>Order amount</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
            </div>
		</div>
	</div>
</div>
@endsection
@section('script')
	<script>
	$(function () {
		var table = $('#simpleDatatable').DataTable({
		   processing: true,
		   responsive: true,
		   serverSide: true,
		   ajax:{
				url:base_url+'/currentdeposit_list',
				data: function (d) {
                d.logistic = $('#logistic').val()
				}
		   },
		   columns: [
				{data : 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
				{data : 'order_number', name: 'order_number'},
				{data : 'customer_name', name: 'customer_name'},
				{data : 'customer_number', name: 'customer_number'},
				{data : 'myaddress', name: 'myaddress'},
				{data : 'logistic_name', name: 'logistic_name'},
				{data : 'order_status', name: 'order_status'},
				{data : 'order_amount', name: 'order_amount'},
				//{data : 'action', name: 'action', orderable: false, searchable: false},
			 ],
		});
		
		$('#logistic').change(function(){
			table.draw();
		});
	});
		function loadForm(allergy_id) {
			console.log(allergy_id)
			$.ajax({
				headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
				url : base_url+ '/deposit/load_form/'+allergy_id,
				type: 'POST',
				success: function (data) { 
					if (!allergy_id) {
						var title_msg = "Add Deposit";
					} else {
						var title_msg = "Edit Deposit";
					}
					opencommonmodal(title_msg, data.html,'<button type="button" class="btn btn-info waves-effect waves-light save_btn" >Save changes</button>');
				}
			});
		}

		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			var formData =  new FormData($(".deposit_form")[0]);
			
			
			if ($(".deposit_form").valid()) { 
			
				ajax_request = $.ajax({
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url + "/deposit_add",
					type: "POST",
					dataType : 'JSON',
					data:  formData,
					cache: false,
					contentType: false,
					processData: false,
					beforeSend: function (xhr) {
						if (ajax_request != null) {
							ajax_request.abort();
						}
						$('.save_btn').prop( "disabled", true );
						$('.reset-btn').prop( "disabled", true );
					},
					success: function(data) {
						$('.save_btn').prop( "disabled", false );
						$('.reset-btn').prop( "disabled", false );
						if (data.status_code == '200') {
							$('#modelcommon').modal('hide'); 
							$('#simpleDatatable').DataTable().draw(true);
							$.growl.notice({ title: "Success", message: data.message});
						} else {
							$.growl.error({ title: "Failed", message: data.message});
						}
					}, error: function() {}             
				});
			} 
		});

		$(document).on('click', '.deleteAllergy', function () {
			var obj = $(this);
			var CategoryId = obj.data('id');
			swal({
				title: 'Are you sure want to delete?',
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: 'Yes, delete it!',
				closeOnConfirm: false
			}, function () {
				$.ajax({
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url +'/allergy/delete/' + CategoryId,
					type: "POST",
					success: function (res) {

						if (res.status_code == '400') {
							swal('Cancelled',"Something went wrong)", "error");
						} else {
							swal('Deleted!', 'Allergy has been deleted.', "success");
							obj.parents('tr').fadeOut();
						}
					},
					error: function () {
					}
				});
			});
			return false;
		});
	</script>
@endsection