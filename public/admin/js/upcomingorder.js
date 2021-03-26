if(search_text_global!=''){
	$('#search_text').val(search_text_global);
}

if(action=='page_upcomingorder'){
	getupcomingorderlist(1);
}

function getupcomingorderlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
		type: "post",
		url: base_url+'/getupcomingorderslist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_order_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}

$("#search_text").keyup(function() {
	getupcomingorderlist(1);
});

$("#perpage").change(function() {
	getupcomingorderlist(1);
});

function assign_order(id){
	$('#assign_id').val(id);
}

function reject_order(id){
	$('#reject_id').val(id);
}

$(document).ready(function($) {
	deliveryboy_Arr = JSON.parse(deliveryboy_list);
	logistic_Arr = JSON.parse(logistic_list);
	delivery_charges_Arr = JSON.parse(delivery_chargess_list);
	pharmacy_id = pharmacy_id;

	var op1 = '<option value="">Select delivery boy</option>';
	var op2 = '<option value="">Select Logistic Provider</option>';

	deliveryboy_Arr.forEach(e => {
		op1 += '<option value="'+e.id+'">'+e.name+'</option>';
	});

	logistic_Arr.forEach(e => {
		op2 += '<option value="'+e.id+'">'+e.name+'</option>';
	});

	$('input[type="radio"]').change(function(){
		var i = $(this);

		if(i.val() == 'logistic'){
			$('#deliveryChargesBlock').show();

			$("#delivery_boy").change(function() {
				var logisticId = $(this).val();
				var op = '<option value="">Select delivery type</option>';

				delivery_charges_Arr.forEach(e => {
					if(e.logistic_id == logisticId){
						op += '<option value="'+e.id+'">'+e.delivery_type+'</option>';
					}
				})
				
				$('#delivery_charges_id')
				.find('option')
				.remove()
				.end()
				.append(op);
				$('#delivery_charges_id').prop('required',true);
			});

			$('#delivery_boy')
			.find('option')
			.remove()
			.end()
			.append(op2);
		} else {
			$('#delivery_charges_id').prop('required',false);
			$('#deliveryChargesBlock').hide();
			$('#delivery_boy').off('change');
			$('#delivery_boy')
			.find('option')
			.remove()
			.end()
			.append(op1);
		}
	});
})