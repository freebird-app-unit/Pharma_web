if(action=='page_user_customer_detail'){
	getorderfilter(1);
}

function getorderfilter(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var pharmacy_id = [];
	$("#pharmacy_id").find('option:selected').each(function(){
		 var optionValue = $(this).val();
	    pharmacy_id.push(optionValue);
	});
	var pharmacy_delivery_id = [];
	$("#pharmacy_delivery_id").find('option:selected').each(function(){
		 var optionValue = $(this).val();
	    pharmacy_delivery_id.push(optionValue);
	});
	var pharmacy_seller_id = [];
	$("#pharmacy_seller_id").find('option:selected').each(function(){
		 var optionValue = $(this).val();
	    pharmacy_seller_id.push(optionValue);
	});
	var logistic_id = [];
	$("#logistic_id").find('option:selected').each(function(){
		 var optionValue = $(this).val();
	    logistic_id.push(optionValue);
	});
    var logistic_delivery_id = [];
    $("#logistic_delivery_id").find('option:selected').each(function(){
		 var optionValue = $(this).val();
	    logistic_delivery_id.push(optionValue);
	});
	var order_status = [];
    $("#order_status").find('option:selected').each(function(){
		 var optionValue = $(this).val();
	    order_status.push(optionValue);
	});
	
	var filter_start_date = $('#filter_start_date').val();
    var filter_end_date = $('#filter_end_date').val();
    var order_type = $('#order_type').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
    var sortord=$('#sortord').val();
	var user_id = $('#view_user_id').val();

	$.ajax({
		type: "post",
		url: base_url+'/getorderhistory',
		data: 'user_id='+user_id+'&pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&order_type="+order_type+"&pharmacy_id="+pharmacy_id+"&pharmacy_delivery_id="+pharmacy_delivery_id+"&pharmacy_seller_id="+pharmacy_seller_id+"&logistic_id="+logistic_id+"&logistic_delivery_id="+logistic_delivery_id+"&ord_field="+ord_field+"&sortord="+sortord+"&filter_start_date="+filter_start_date+"&filter_end_date="+filter_end_date+"&order_status="+order_status,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_order_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}
$("#perpage").change(function() {
	getorderfilter(1);
});




