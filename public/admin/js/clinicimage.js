//clinicimage list

if(action=='page_clinicimage'){
	getclinicimagelist(1);
}
function getclinicimagelist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
			type: "post",
			url: base_url+'/getclinicimagelist',
			data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord,
			success: function (responce) {	
				var data = responce.split('##');
				$('#admin_clinicimage_list tbody').html(data[0]);
				$('.pagination').html(data[1]);
				$('#total_summary').html(data[2]);
			}
		});
}

$("#search_text").keyup(function() {
	getclinicimagelist(1);
});

$("#perpage").change(function() {
	getclinicimagelist(1);
});

function delete_row(id){
	if(confirm('Are you sure you want to delete this ?')){
		window.location.href = base_url+"/clinicimage/delete/"+id;
	}
}
//clinicimage list