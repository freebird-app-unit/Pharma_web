if(action=='page_voucher_detail'){
	getvoucherorderlist(1);
}

function getvoucherorderlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
    var search_text = $('#search_text').val();
    
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
    var sortord=$('#sortord').val();
    var voucher_id=$('#voucher_id').val();

	$.ajax({
		type: "post",
		url: base_url+'/get_voucher_orderlist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&search_text="+search_text+"&ord_field="+ord_field+"&sortord="+sortord+"&voucher_id="+voucher_id,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_order_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}