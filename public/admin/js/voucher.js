if(action=='page_voucher'){
	getvoucherlist(1);
}

function getvoucherlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var order_type = $('#order_type').val();
	var filter_start_date = $('#filter_start_date').val();
    var filter_end_date = $('#filter_end_date').val();
    
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
    var sortord=$('#sortord').val();

	$.ajax({
		type: "post",
		url: base_url+'/getvoucherlist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&order_type="+order_type+"&ord_field="+ord_field+"&sortord="+sortord+"&filter_start_date="+filter_start_date+"&filter_end_date="+filter_end_date,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_voucher_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}

function voucher_confirmed(id) {
	var token = document.getElementsByName("_token")[0].value;

    $.ajax({
		type: "post",
		url: base_url+'/voucher_confirmed',
		data: 'voucher_id='+id+"&_token="+token,
		success: function (responce) {	
            location.reload();
		}
	});
}