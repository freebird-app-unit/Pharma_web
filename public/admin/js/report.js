if(action=='page_report'){
	getreportlist(1);
}

function getreportlist(pageno){
	var token = document.getElementsByName("_token")[0].value;
	var searchtxt = $('#search_text').val();
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	$.ajax({
		type: "post",
		url: base_url+'/report_list',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&searchtxt="+searchtxt+"&ord_field="+ord_field+"&sortord="+sortord,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_report_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
		}
	});
}

$("#search_text").keyup(function() {
	getreportlist(1);
});

$("#perpage").change(function() {
	getreportlist(1);
});

function resolve(id, e){
	var token = document.getElementsByName("_token")[0].value;

    $('#id').val(id);
    
    $.ajax({
		type: "post",
		url: base_url+'/report_resolve',
		data: 'id='+id+"&_token="+token,
		success: function (responce) {	
            console.log(responce);
            var row = e.parentNode.parentNode;
            row.parentNode.removeChild(row);
		}
	});
}