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
                <!-- table start -->
                <table id="simpleDatatable" class="table table-stripped simpleDatatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Color</th>
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
	<link href="{{asset('public/admin/plugins/mjolnic-bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css') }}" rel="stylesheet">
	<script src="{{asset('public/admin/plugins/mjolnic-bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js') }}"></script>
	<script>
		$('#simpleDatatable').DataTable({
		   processing: true,
		   responsive: true,
		   serverSide: true,
		   ajax:{
				url:base_url+'/pill_color_list',
		   },
		   columns: [
				{data : 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
				{data : 'name', name: 'name'},
				{data : 'color', name: 'color'},
				{data : 'action', name: 'action', orderable: false, searchable: false},
			 ],
		});

		function loadForm(pill_color_id) {
			console.log(pill_color_id)
			$.ajax({
				headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
				url : base_url+ '/pill_color/load_form/'+pill_color_id,
				type: 'POST',
				success: function (data) { 
					if (!pill_color_id) {
						var title_msg = "Add Pill Color";
					} else {
						var title_msg = "Edit Pill Color";
					}
					opencommonmodal(title_msg, data.html,'<button type="button" class="btn btn-info waves-effect waves-light save_btn" >Save changes</button>');
				}
			});
		}

		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			var formData =  new FormData($(".pill_color_form")[0]);
			
			
			if ($(".pill_color_form").valid()) { 
			
				ajax_request = $.ajax({
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url + "/pill_color_add",
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

		$(document).on('click', '.deletePillColor', function () {
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
					url: base_url +'/pill_color/delete/' + CategoryId,
					type: "POST",
					success: function (res) {

						if (res.status_code == '400') {
							swal('Cancelled',"Something went wrong)", "error");
						} else {
							swal('Deleted!', 'Pill Color has been deleted.', "success");
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