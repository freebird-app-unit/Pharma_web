if(action=='page_deliveryboy'){
	getuserlist(1);
}

function getuserlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var search_pharmacy = $('#search_pharmacy').val();
	var search_parent_type = $('#search_parent_type').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
			type: "post",
			url: base_url+'/getdeliveryboylist',
			data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord+"&search_pharmacy="+search_pharmacy+"&search_parent_type="+search_parent_type,
			success: function (responce) {	
				var data = responce.split('##');
				$('#admin_client_list tbody').html(data[0]);
				$('.pagination').html(data[1]);
				$('#total_summary').html(data[2]);
			}
		});
}

$("#search_text").keyup(function() {
	getuserlist(1);
});
$("#search_parent_type").change(function() {
	getuserlist(1);
});
$("#search_pharmacy").change(function() {
	getuserlist(1);
});
$("#perpage").change(function() {
	getuserlist(1);
});
/*
function delete_row(id){
	if(confirm('Are you sure you want to delete this ?')){
		window.location.href = base_url+"/deliveryboy/delete/"+id;
	}
}*/
$(document).on('click', '.deleteUser', function () {
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
			//headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
			url: base_url +'/deliveryboy/delete/' + CategoryId,
			type: "GET",
			success: function (res) {

				if (res.status_code == '400') {
					swal('Cancelled',"Something went wrong)", "error");
				} else {
					swal('Deleted!', 'Deliveryboy has been deleted.', "success");
					obj.parents('tr').fadeOut();
				}
			},
			error: function () {
			}
		});
	});
	return false;
});