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
                            <th>Package</th>
                            <th>Package Amount</th>
							<th>Package Purchase Date</th>
							<th>Total Delivery</th>
							<th>Order Number</th>
							<th>Payment ID</th>
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
		$('#simpleDatatable').DataTable({
		   processing: true,
		   responsive: true,
		   serverSide: true,
		   ajax:{
				url:base_url+'/packageshistory_list',
		   },
		   columns: [
				{data : 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
				{data : 'name', name: 'name'},
				{data : 'package_amount', name: 'package_amount'},
				{data : 'package_purchase_date', name: 'package_purchase_date'},
				{data : 'total_delivery', name: 'total_delivery'},
				{data : 'order_number', name: 'order_number'},
				{data : 'payment_id', name: 'payment_id'},
			 ],
		});

		function loadForm(allergy_id) {
			console.log(allergy_id)
			$.ajax({
				headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
				url : base_url+ '/allergy/load_form/'+allergy_id,
				type: 'POST',
				success: function (data) { 
					if (!allergy_id) {
						var title_msg = "Add Allergy";
					} else {
						var title_msg = "Edit Allergy";
					}
					opencommonmodal(title_msg, data.html,'<button type="button" class="btn btn-info waves-effect waves-light save_btn" >Save changes</button>');
				}
			});
		}

		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			var formData =  new FormData($(".allergy_form")[0]);
			
			
			if ($(".allergy_form").valid()) { 
			
				ajax_request = $.ajax({
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url + "/allergy_add",
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