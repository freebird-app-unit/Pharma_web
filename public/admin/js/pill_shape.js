
$('#simpleDatatable').DataTable({
   processing: true,
   responsive: true,
   serverSide: true,
   ajax:{
		url:base_url+'/getpillshapelist',
   },
   columns: [
		{data : 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
		{data : 'name', name: 'name'},
		{data : 'action', name: 'action', orderable: false, searchable: false},
	 ],
});

function loadForm(pill_shape_id) {
	console.log(pill_shape_id)
	$.ajax({
		headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
		url : base_url+ '/pill_shape/load_form/'+pill_shape_id,
		type: 'POST',
		success: function (data) { 
			if (!pill_shape_id) {
				var title_msg = "Add Pill Shape";
			} else {
				var title_msg = "Edit Pill Shape";
			}
			opencommonmodal(title_msg, data.html,'<button type="button" class="btn btn-info waves-effect waves-light save_btn" >Save changes</button>');
		}
	});
}

var ajax_request = null;
$(document.body).on('click','.save_btn',function(e) {
	e.preventDefault();
	
	var formData =  new FormData($(".pill_shape_form")[0]);
	
	
	if ($(".pill_shape_form").valid()) { 
	
		ajax_request = $.ajax({
			headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
			url: base_url + "/pill_shape_add",
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

$(document).on('click', '.deletePillShape', function () {
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
			url: base_url +'/pill_shape/delete/' + CategoryId,
			type: "POST",
			success: function (res) {

				if (res.status_code == '400') {
					swal('Cancelled',"Something went wrong)", "error");
				} else {
					swal('Deleted!', 'Pill Shape has been deleted.', "success");
					obj.parents('tr').fadeOut();
				}
			},
			error: function () {
			}
		});
	});
	return false;
});