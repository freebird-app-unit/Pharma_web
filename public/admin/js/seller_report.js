if(action=='page_seller_report'){
	getsellerreport(1);
}

function getsellerreport(pageno){
	var token = document.getElementsByName("_token")[0].value;

	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	var record_display = $('#record_display').val();
	var record_monthly = $('#record_monthly').val();
	var record_yearly = $('#record_yearly').val();
	$.ajax({
		type: "post",
		url: base_url+'/getsellerreport',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&ord_field="+ord_field+"&sortord="+sortord+"&record_display="+record_display+"&record_monthly="+record_monthly+"&record_yearly="+record_yearly,
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
    }
})


