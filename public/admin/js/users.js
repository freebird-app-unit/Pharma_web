if(action=='page_users'){
	getuserlist(1);
}

function getuserlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var search_city = $('#search_city').val();
	var user_type = $('#user_type').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
		type: "post",
		url: base_url+'/getuserlist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&search_city="+search_city+"&ord_field="+ord_field+"&sortord="+sortord+"&user_type="+user_type,
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
$("#search_city").change(function() {
	getuserlist(1);
});
$("#user_type").change(function() {
	getuserlist(1);
});
$("#perpage").change(function() {
	getuserlist(1);
});

$("#parent_type").change(function() {
	alert();
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

// $("#state").on('change', function() {
// 	var country = $('#STATE'+this.value ).data('country');
// 	$("#country").val(country).change();
// });

// $("#city").on('change', function() {
// 	var state = $('#CITY'+this.value ).data('state');
// 	$("#state").val(state).change();
// 	var country = $('#STATE'+this.value ).data('country');
// });

/* function delete_row(id){
	if(confirm('Are you sure you want to delete this ?')){
		window.location.href = base_url+"/user/delete/"+id;
	}
} */

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
					headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
					url: base_url +'/user/delete/' + CategoryId,
					type: "GET",
					success: function (res) {

						if (res.status_code == '400') {
							swal('Cancelled',"Something went wrong)", "error");
						} else {
							swal('Deleted!', 'User has been deleted.', "success");
							obj.parents('tr').fadeOut();
						}
					},
					error: function () {
					}
				});
			});
			return false;
		});
function display_parent_user(type){
	


	switch (type) {
		case 'seller':
			$('#imageBlock').show();

			$('#docBlock').hide();
			$('#discount').rules('remove', 'required');

			$('#parent_type_container').hide();
			$('#discountBlock').hide();

			$('#timeBlock').hide();
			$('#start_time').rules('remove', 'required');
			$('#close_time').rules('remove', 'required');

			$('#countryBlock').hide();
			$('#country').rules('remove',  'required');
			$('#state').rules('remove',  'required');
			$('#city').rules('remove',  'required');

			$('#ownerBlock').hide();
			$('#owner_name').rules('remove',  'required');

			$('#location').hide();
			$('#lat').rules('remove',  'required');
			$('#lon').rules('remove',  'required');

			$('#radiousBlock').hide();
			$('#radius').rules('remove',  'required');

			$('#parent_user_container').show();
			$('#parentuser_id').rules('add',  { 'required': true });

			$('#dispAddress').show();
			$('#address').rules('add',  { 'required': true });
			$('#block').rules('add',  { 'required': true });
			$('#street').rules('add',  { 'required': true });
			$('#pincode').rules('add',  { 'required': true });


			var option = '';
			pharmacies.forEach(element => {
				option += "<option value='"+element.id+"' >"+element.name+"</option>";
			});
			$('#parentuser_id')
			.find('option')
			.remove()
			.end()
			.append(option);

			$('#license_image').rules('remove',  'required');
			$('#pancard_image').rules('remove',  'required');

			$( "#profile_image" ).rules( "add", {
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});
		break;

		case 'delivery_boy':
			$('#docBlock').hide();
			$('#imageBlock').show();
		
			$('#discountBlock').hide();
			$('#discount').rules('remove', 'required');

			$('#timeBlock').hide();
			$('#start_time').rules('remove', 'required');
			$('#close_time').rules('remove', 'required');

			$('#countryBlock').hide();
			$('#country').rules('remove',  'required');
			$('#state').rules('remove',  'required');
			$('#city').rules('remove',  'required');

			$('#ownerBlock').hide();
			$('#owner_name').rules('remove',  'required');

			$('#location').hide();
			$('#lat').rules('remove',  'required');
			$('#lon').rules('remove',  'required');

			$('#radiousBlock').hide();
			$('#radius').rules('remove',  'required');

			$('#parent_type_container').hide();

			$('#parent_type_container').show();
			$('#parent_user_container').show();
			$('#parentuser_id').rules('add',  { 'required': true });
			$('.parent_type').rules('add',  { 'required': true });

			$('#license_image').rules('remove',  'required');
			$('#pancard_image').rules('remove',  'required');

			$('#dispAddress').show();
			$('#address').rules('add',  { 'required': true });
			$('#block').rules('add',  { 'required': true });
			$('#street').rules('add',  { 'required': true });
			$('#pincode').rules('add',  { 'required': true });


			$( "#profile_image" ).rules( "add", {
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});
		break;

		case 'pharmacy':
			$('#docBlock').show();
			$('#imageBlock').show();

			$('#timeBlock').show();
			$('#start_time').rules('add',  { 'required': true });
			$('#close_time').rules('add',  { 'required': true });

			$('#discountBlock').show();
			$('#discount').rules('add',  { 'required': true });

			$('#ownerBlock').show();
			$('#owner_name').rules('add',   { 'required': true });

			$('#parent_type_container').hide();
			$('#parent_user_container').hide();

			$('#countryBlock').show();
			$('#country').rules('add',   { 'required': true });
			$('#state').rules('add',   { 'required': true });
			$('#city').rules('add',   { 'required': true });

			$('#location').show();
			$('#lat').rules('add',   { 'required': true });
			$('#lon').rules('add',   { 'required': true });

			$('#radiousBlock').show();
			$('#user_detail-form').validate();
			$('#radius').rules('add',  { 'required': true });

			$('#dispAddress').show();
			$('#address').rules('add',  { 'required': true });
			$('#block').rules('add',  { 'required': true });
			$('#street').rules('add',  { 'required': true });
			$('#pincode').rules('add',  { 'required': true });


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
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp, pdf)."
				}
			});

			$( "#pancard_image" ).rules( "add", {
				// required: true,
				extension: "jpg|jpeg|png|ico|bmp|pdf",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp, pdf)."
				}
			});
		break;

		default:
			$('#docBlock').hide();
			$('#imageBlock').show();

			$('#discountBlock').hide();
			$('#discount').rules('remove', 'required');

			$('#timeBlock').hide();
			$('#start_time').rules('remove', 'required');
			$('#close_time').rules('remove', 'required');

			$('#parent_type_container').hide();

			$('#parent_user_container').hide();
			$('#parentuser_id').rules('remove',  'required');

			$('#ownerBlock').hide();
			$('#owner_name').rules('remove',  'required');

			$('#radiousBlock').hide();
			$('#radius').rules('remove',  'required');

			$('#countryBlock').hide();
			$('#country').rules('remove',  'required');
			$('#state').rules('remove',  'required');
			$('#city').rules('remove',  'required');

			$('#location').hide();
			$('#lat').rules('remove',  'required');
			$('#lon').rules('remove',  'required');

			$('#license_image').rules('remove',  'required');
			$('#pancard_image').rules('remove',  'required');

			$('#dispAddress').hide();
			$('#address').rules('remove',  'required');
			$('#block').rules('remove',  'required');
			$('#street').rules('remove',  'required');
			$('#pincode').rules('remove',  'required');

			$( "#profile_image" ).rules( "add", {
				required: true,
				extension: "jpg|jpeg|png|ico|bmp",
				messages: {
					required: "Please upload file.",
					extension: "Please upload file in these format only (jpg, jpeg, png, ico, bmp)."
				}
			});
		break;
	}
}


$('#parent_type_pharmacy').click(function() {
	var option = '';
	pharmacies.forEach(element => {
		option += "<option value='"+element.id+"' >"+element.name+"</option>";
	});
	$('#parentuser_id')
    .find('option')
    .remove()
    .end()
    .append(option);
})

$('#parent_type_logistic').click(function() {
	var option = '';
	logistics.forEach(element => {
		option += "<option value='"+element.id+"' >"+element.name+"</option>";
	});
	$('#parentuser_id')
    .find('option')
    .remove()
    .end()
    .append(option);
})