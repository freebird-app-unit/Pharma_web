if(action=='page_pharma_external_delivery_report'){
	getExternalDeliveryReport(1);
}

function getExternalDeliveryReport(pageno){
	var token = document.getElementsByName("_token")[0].value;

	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	var record_display=$('#record_display').val();
	var record_monthly = $('#record_monthly').val();
	var record_yearly = $('#record_yearly').val();
	var filter_start_date = $('#filter_start_date').val();
	var filter_end_date = $('#filter_end_date').val();

	$.ajax({
		type: "post",
		url: base_url+'/getExternalDeliveryReport',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&ord_field="+ord_field+"&sortord="+sortord+"&record_display="+record_display+"&record_monthly="+record_monthly+"&record_yearly="+record_yearly+"&filter_start_date="+filter_start_date+"&filter_end_date="+filter_end_date,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_report_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}
$('#record_display').change(function() {
	$('.filter_block').hide();
    record_display = this.value;
    if(record_display == "monthly"){
    	$('#block_monthly').show();
    	$('#block_yearly').show();
    }else if(record_display == "yearly"){
    	$('#block_yearly').show();
		$('.block_custom_date').hide();
    }else if(record_display == "custom_date"){
    	$('.block_custom_date').show();
		$('#block_monthly').hide();
    	$('#block_yearly').hide();
    }
})



