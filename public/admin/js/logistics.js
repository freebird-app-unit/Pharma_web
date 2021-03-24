if(action=='page_logistic'){
	getlogisticlist(1);
}

function getlogisticlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var search_city = $('#search_city').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
        type: "post",
        url: base_url+'/getlogisticlist',
        data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord+"&search_city="+search_city,
        success: function (responce) {	
            var data = responce.split('##');
            $('#admin_client_list tbody').html(data[0]);
            $('.pagination').html(data[1]);
            $('#total_summary').html(data[2]);
        }
    });
}

$("#search_text").keyup(function() {
	getlogisticlist(1);
});
$("#search_city").change(function() {
	getlogisticlist(1);
});
$("#perpage").change(function() {
	getlogisticlist(1);
});

$("#country").on('change', function() {
	var country = $('#COUNTRY'+this.value).data('country-id');
	var token = document.getElementsByName("_token")[0].value;

	$.ajax({
		type: "post",
		url: base_url+'/getstatelist',
		data: "_token="+token+"&country="+country+"",
		dataType: 'json',
		success: function (responce) {	
			$("#state").prop("disabled", false);
			var states = responce.states;
			var option = '';
			states.forEach(element => {
				option += '<option id="STATE'+element.name+'" value="'+element.name+'" data-state-id="'+element.id+'">'+element.name+'</option>';
			});
			$('#state')
			.find('option')
			.remove()
			.end()
			.append(option);
		}
	});
});

$("#state").on('change', function() {
	var state = $('#STATE'+this.value).data('state-id');
	var token = document.getElementsByName("_token")[0].value;

	$.ajax({
		type: "post",
		url: base_url+'/getcitylist',
		data: "_token="+token+"&state="+state+"",
		dataType: 'json',
		success: function (responce) {	
			$("#city").prop("disabled", false);
			var cities = responce.cities;
			var option = '';
			cities.forEach(element => {
				option += '<option id="CITY'+element.name+'" value="'+element.name+'" data-state-id="'+element.id+'">'+element.name+'</option>';
			});
			$('#city')
			.find('option')
			.remove()
			.end()
			.append(option);
		}
	});
});
/*
function delete_row(id){
	if(confirm('Are you sure you want to delete this ?')){
		window.location.href = base_url+"/logistic/delete/"+id;
	}
}
*/
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
			url: base_url +'/logistic/delete/' + CategoryId,
			type: "GET",
			success: function (res) {

				if (res.status_code == '400') {
					swal('Cancelled',"Something went wrong)", "error");
				} else {
					swal('Deleted!', 'Logistic has been deleted.', "success");
					obj.parents('tr').fadeOut();
				}
			},
			error: function () {
			}
		});
	});
	return false;
});