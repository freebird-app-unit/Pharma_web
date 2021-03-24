if(action=='page_complete_logistic'){
	getcompletelistlogistic(1);
}

var ajax_request = null;
function getcompletelistlogistic(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	
	ajax_request = $.ajax({
		type: "post",
		url: base_url+'/getcompletelistlogistic',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord,
		beforeSend: function (xhr) {
			if (ajax_request != null) {
				ajax_request.abort();
			}
		},
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_order_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}

$("#search_text").keyup(function() {
	getcompletelistlogistic(1);
});

$("#perpage").change(function() {
	getcompletelistlogistic(1);
});

function assign_order(id){
	$('#assign_id').val(id);
}