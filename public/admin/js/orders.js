if(action=='page_orders'){
	getorderslist(1);
}

function getorderslist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord = $('#sortord').val();
	var pharmacy_seller_id = $('#pharmacy_seller_id').val();
	var order_status = $('#order_status').val();
	var order_delivery_type = $('#order_delivery_type').val();
	var filter_start_date = $('#filter_start_date').val();
    var filter_end_date = $('#filter_end_date').val();
	$.ajax({
			type: "post",
			url: base_url+'/getorderslist',
			data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord+"&pharmacy_seller_id="+pharmacy_seller_id+"&order_status="+order_status+"&order_delivery_type="+order_delivery_type+"&filter_start_date="+filter_start_date+"&filter_end_date="+filter_end_date,
			success: function (responce) {	
				var data = responce.split('##');
				$('#admin_order_list tbody').html(data[0]);
				$('.pagination').html(data[1]);
				$('#total_summary').html(data[2]);
			}
		});
}

$("#search_text").keyup(function() {
	getorderslist(1);
});

$("#perpage").change(function() {
	getorderslist(1);
});

function reject_order(id){
	$('#reject_id').val(id);
}