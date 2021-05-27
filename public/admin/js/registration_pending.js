if(search_text_global!=''){
	$('#search_text').val(search_text_global);
}

if(action=='page_registration_pending'){
	getregistrationpendinglist(1);
}

function getregistrationpendinglist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
		type: "post",
		url: base_url+'/getregistrationpendinglist',
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
	getregistrationpendinglist(1);
});

$("#perpage").change(function() {
	getregistrationpendinglist(1);
});
