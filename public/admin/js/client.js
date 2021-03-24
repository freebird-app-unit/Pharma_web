if(action=='page_client'){
	getclientlist(1);
}

function getclientlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
			type: "post",
			url: base_url+'/getclientlist',
			data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord,
			success: function (responce) {	
				var data = responce.split('##');
				$('#admin_client_list tbody').html(data[0]);
				$('.pagination').html(data[1]);
				$('#total_summary').html(data[2]);
			}
		});
}

$("#search_text").keyup(function() {
	getclientlist(1);
});

$("#perpage").change(function() {
	getclientlist(1);
});

function delete_row(id){
	if(confirm('Are you sure you want to delete this ?')){
		window.location.href = base_url+"/client/delete/"+id;
	}
}