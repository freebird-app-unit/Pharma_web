if(action=='page_deliveryreport'){
	getdeliveryreportlist(1);
}

function getdeliveryreportlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	var filter_start_date = $('#filter_start_date').val();
	var filter_end_date = $('#filter_end_date').val();
	$.ajax({
			type: "post",
			url: base_url+'/getdeliveryreportlist',
			data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord+"&filter_start_date="+filter_start_date+"&filter_end_date="+filter_end_date,
			success: function (responce) {	
				var data = responce.split('##');
				$('#admin_client_list tbody').html(data[0]);
				$('.pagination').html(data[1]);
				$('#total_summary').html(data[2]);
			}
		});
}

$("#search_text").keyup(function() {
	getdeliveryreportlist(1);
});

$("#perpage").change(function() {
	getdeliveryreportlist(1);
});

function delete_row(id){
	if(confirm('Are you sure you want to delete this ?')){
		window.location.href = base_url+"/seller/delete/"+id;
	}
}