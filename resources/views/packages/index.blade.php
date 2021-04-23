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
					<?php if(Auth::user()->user_type=='admin'){ ?>
                        <a href="javascript:void(0)" onclick="loadForm();"  class="btn btn-primary float-right mt-2">Add</a><br/>
					<?php } ?>
                    </div>
					<div class="col-sm-12">
					@if (\Session::has('fail_msg'))
						<div class="alert alert-danger">
							{!! \Session::get('fail_msg') !!}
						</div>
					@endif
					</div>
					<div class="col-sm-12">
					@if (\Session::has('success_msg'))
						<div class="alert alert-success">
							{!! \Session::get('success_msg') !!}
						</div>
					@endif
					</div>

                </div>
                <div class="row" id="packages_data">
					
				</div>
            </div>
		</div>
	</div>
</div>
@endsection
@section('script')
	<script>
	get_package_data();
		function get_package_data(){
			$.ajax({
				headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
				url : base_url+ '/package_list',
				type: 'GET',
				success: function (data) { 
					$('#packages_data').html(data);
				}
			});
		}
		/* $('#simpleDatatable').DataTable({
		   processing: true,
		   responsive: true,
		   serverSide: true,
		   ajax:{
				url:base_url+'/package_list',
		   },
		   columns: [
				{data : 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
				{data : 'name', name: 'name'},
				{data : 'action', name: 'action', orderable: false, searchable: false},
			 ],
		}); */

		function loadForm(package_id) {
			console.log(package_id)
			$.ajax({
				headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
				url : base_url+ '/package/load_form/'+package_id,
				type: 'POST',
				success: function (data) { 
					if (!package_id) {
						var title_msg = "Add Package";
					} else {
						var title_msg = "Edit Package";
					}
					opencommonmodal(title_msg, data.html,'<button type="button" class="btn btn-info waves-effect waves-light save_btn" >Save changes</button>');
				}
			});
		}

		var ajax_request = null;
		$(document.body).on('click','.save_btn',function(e) {
			e.preventDefault();
			
			var formData =  new FormData($(".package_form")[0]);
			
			
			if ($(".package_form").valid()) { 
			
				ajax_request = $.ajax({
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url + "/package_add",
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
							//$('#simpleDatatable').DataTable().draw(true);
							get_package_data();
							$.growl.notice({ title: "Success", message: data.message});
						} else {
							$.growl.error({ title: "Failed", message: data.message});
						}
					}, error: function() {}             
				});
			} 
		});

		$(document).on('click', '.deletePackage', function () {
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
					url: base_url +'/package/delete/' + CategoryId,
					type: "POST",
					success: function (res) {

						if (res.status_code == '400') {
							swal('Cancelled',"Something went wrong)", "error");
						} else {
							swal('Deleted!', 'Package has been deleted.', "success");
							get_package_data();
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