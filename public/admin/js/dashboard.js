getorderslist(1);
function getorderslist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#order_search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
		type: "post",
		url: base_url+'/getorderslist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord+"&home=home",
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_dashboardorder_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}

function reject_order(id){
	$('#reject_id').val(id);
}

$("#order_search_text").keyup(function() {
	getorderslist(1);
});

$("#perpage").change(function() {
	getorderslist(1);
});
if(window.laravel_echo_port !== undefined){
	window.laravel_echo_port='{{env("LARAVEL_ECHO_PORT")}}';

	var i = 0;
	window.Echo.channel('user-channel')
	 .listen('.UserEvent', (data) => {
		i++;
		$("#notification").append('<div class="alert alert-success">'+i+'.'+data.title+'</div>');
	});
}

