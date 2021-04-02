$( document ).ready(function() {
	getreportorderlist(1);
});

function getreportorderlist(pageno){

	var token = document.getElementsByName("_token")[0].value;
	var order_type = $('#order_type').val();
	var pharmacy_id = $('#pharmacy_id').val();
	var logistic_id = $('#logistic_id').val();

	var filter_start_date = $('#filter_start_date').val();
    var filter_end_date = $('#filter_end_date').val();
    
	var perpage = $('#perpage').val();
	var ord_field=$('#sortfield').val();
	var sortord=$('#sortord').val();
	
	$.ajax({
		type: "post",
		url: base_url+'/getdeliverychargesorderlist',
		data: 'pageno='+pageno+"&perpage="+perpage+"&_token="+token+"&order_type="+order_type+"&pharmacy_id="+pharmacy_id+"&logistic_id="+logistic_id+"&ord_field="+ord_field+"&sortord="+sortord+"&filter_start_date="+filter_start_date+"&filter_end_date="+filter_end_date,
		success: function (responce) {	
			var data = responce.split('##');
			$('#admin_report_list tbody').html(data[0]);
			$('.pagination').html(data[1]);
			$('#total_summary').html(data[2]);
			$(".selected_order").change(function() {
				if($('input.selected_order:checked').length > 0) {
					$('#pay').prop('disabled', false);
				}
			});
		}
	});
}

$('#all').change(function() {
    console.log(this.checked);
})

function pay_pending(){
	$("#payment-form").validate({
		rules: {
			voucher_type : 'required',
		},
		highlight: function(element) {
			$(element).removeClass('is-valid').addClass('is-invalid');
		},
		unhighlight: function(element) {
			$(element).removeClass('is-invalid').addClass('is-valid');
		},
	});
}



$('#cash').click(function() {
	$('#transation_number').rules('remove', 'required');
	$('#transactionBlock').hide();
})

$('#bank').click(function() {
	$('#transactionBlock').show();
	$('#transation_number').rules('add',  { 'required': true });
})

// $('#order_type').on('change', function() {
// 	var token = document.getElementsByName("_token")[0].value;
// 	var order_type = $('#order_type').val();
// 	var pharmacy_id = $('#pharmacy_id').val();
// 	var logistic_id = $('#logistic_id').val();

//     $.ajax({
// 		type: "post",
// 		url: base_url+'/getreportordertotal',
// 		data: 'order_type='+order_type+'&_token='+token+'&pharmacy_id='+pharmacy_id+'&logistic_id='+logistic_id,
// 		success: function (resp) {
//             var resp = JSON.parse(resp);
//             $('#total_amount').text(resp.total_amount);
//             $('#pending_amount').text(resp.pending_amount);
//         }
//     })

//     if(this.value == 1){
// 		$('#pay').prop('disabled', true);
//         $('#total_title').text('Total Order Amount');
//     }else{
// 		$('#pay').prop('disabled', false);
//         $('#total_title').text('Total Delivery Charge');
//     }

// 	$('#admin_report_list tbody').html('');
//     getreportorderlist(1);
// });