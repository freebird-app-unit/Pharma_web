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
                </div>
                <!-- table start -->
                <table id="simpleDatatable" class="table table-stripped simpleDatatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pharmacy name</th>
							<th>First name</th>
							<th>Last name</th>
							<th>Address</th>
							<th>Email</th>
							<th>Phone</th>
							<th>Date of application</th>
							<th>Action</th>
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
				url:base_url+'/onboardingrequest_list',
				data: function (d) {
                d.logistic = $('#logistic').val()
				}
		   },
		   columns: [
				{data : 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
				{data : 'name', name: 'name'},
				{data : 'first_name', name: 'first_name'},
				{data : 'last_name', name: 'last_name'},
				{data : 'address', name: 'address'},
				{data : 'email', name: 'email'},
				{data : 'phone', name: 'phone'},
				{data : 'dateofapplication', name: 'dateofapplication'},
				{data : 'action', name: 'action', orderable: false, searchable: false},
			 ],
		});
		
		$('#logistic').change(function(){
			table.draw();
			var logistic_id = $('#logistic').val();
			$.ajax({
				type: "get",
				url: base_url+'/getlogisticdepositeamount/'+logistic_id,
				data: '',
				success: function (responce) {	
					var obj = responce.split('##');
					$('.logistic_total_deposit').html(obj[0]);
					$('.logistic_current_deposit').html(obj[1]);
				}
			});
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

		$(document).on('click', '.approverequest', function () {
			var obj = $(this);
			var request_id = obj.data('id');
			swal({
				title: 'Are you sure want to approve?',
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: 'Yes, approve it!',
				closeOnConfirm: false
			}, function () {
				$.ajax({
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url +'/onboardingrequestapprove/' + request_id,
					type: "GET",
					success: function (res) {

						if (res.status_code == '400') {
							swal('Cancelled',"Something went wrong)", "error");
						} else {
							swal('Deleted!', 'Request has been approved.', "success");
							obj.parents('tr').fadeOut();
						}
					},
					error: function () {
					}
				});
			});
			return false;
		});
		
		$(document).on('click', '.rejectrequest', function () {
			var obj = $(this);
			var request_id = obj.data('id');
			swal({
				title: 'Are you sure want to reject?',
				type: "input",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: 'Yes, reject it!',
				closeOnConfirm: false,
				inputPlaceholder: "Write reject reason"
			}, function (inputValue) {
				if (inputValue === false) return false;      
				if (inputValue === "") {     
					swal.showInputError("You need to write something!");     
					return false   
				}    
				$.ajax({
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url +'/onboardingrequestreject/' + request_id + '?reject_reason='+inputValue,
					type: "GET",
					success: function (res) {

						if (res.status_code == '400') {
							swal('Cancelled',"Something went wrong)", "error");
						} else {
							swal('Rejected!', 'Request has been rejected.', "success");
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