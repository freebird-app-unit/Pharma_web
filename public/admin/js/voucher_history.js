if(action=='page_voucher_history'){
	getvoucherhistorylist(1);
}

function getvoucherhistorylist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var filter_start_date = $('#filter_start_date').val();
    var filter_end_date = $('#filter_end_date').val();
    
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
    var sortord=$('#sortord').val();

	$.ajax({
		type: "post",
		url: base_url+'/getvoucherhistorylist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&ord_field="+ord_field+"&sortord="+sortord+"&filter_start_date="+filter_start_date+"&filter_end_date="+filter_end_date,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_voucher_history_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}