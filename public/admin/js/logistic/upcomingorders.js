if(action=='page_upcomingorders'){
	getorderslist(1);
}

function getorderslist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
		type: "post",
		url: base_url+'/getupcominglist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord,
		success: function (responce) {	
			console.log(responce);
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

function assign_order(id){
	$('#assign_id').val(id);
}

function reject_order(id){
	$('#reject_id').val(id);
}

$('input[type="radio"]').change(function(){
	$('#delivery_boy')
	.find('option')
	.remove()
	.end()
	.append('<option value="">Select delivery boy</option>');
});