if(action=='page_adminoutfordelivery'){
	getoutfordeliverylist(1);
}

function getoutfordeliverylist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	var pharmacy_id=$('#pharmacy_id').val();
	var logistic_id=$('#logistic_id').val();
	$.ajax({
			type: "post",
			url: base_url+'/getadminoutfordeliverylist',
			data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord+"&pharmacy_id="+pharmacy_id+"&logistic_id="+logistic_id,
			success: function (responce) {	
				var data = responce.split('##');
				$('#admin_order_list tbody').html(data[0]);
				$('.pagination').html(data[1]);
				$('#total_summary').html(data[2]);
			}
		});
}

$("#search_text").keyup(function() {
	getoutfordeliverylist(1);
});

$("#perpage").change(function() {
	getoutfordeliverylist(1);
});
