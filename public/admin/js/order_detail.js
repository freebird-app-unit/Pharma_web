function request_prescription(order_id){
	var token = document.getElementsByName("_token")[0].value;
	$.ajax({
		type: "post",
		url: base_url+'/sendprescriptionotpsms',
		data: 'order_id='+order_id+'&_token='+token,
		success: function (responce) {	
			var obj = JSON.parse(responce);
			if(obj.status == 'success'){
				$('#sent_otp').val(obj.otp);
				$('.otp_message').html('<span class="success">'+obj.message+'</span>');
				$('#verify_otp_container').show();
				$('.request_otp').hide();
			}else{
				$('#sent_otp').val(obj.otp);
				$('.otp_message').html('<span class="error">'+obj.message+'</span>')
			}
		}
	});
}

function verify_otp(order_id){
	var token = document.getElementsByName("_token")[0].value;
	var sent_otp = $('#sent_otp').val();
	var otp = $('#otp').val();
	$.ajax({
		type: "post",
		url: base_url+'/verifyprescriptionotp',
		data: 'order_id='+order_id+'&_token='+token+'&sent_otp='+sent_otp+'&otp='+otp,
		success: function (responce) {	
			var obj = JSON.parse(responce);
			if(obj.status == 'success'){
				$('.otp_message').html('<span class="success">'+obj.message+'</span>');
				$('#verify_otp_container').hide();
				$('#prescription_image_container').html(obj.data);
			}else{
				$('.otp_message').html('<span class="error">'+obj.message+'</span>')
			}
		}
	});
}