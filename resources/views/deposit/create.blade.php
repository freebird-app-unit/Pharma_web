 <div class="modal-body">
    <form id="sample-form" name="sample-form" method="POST" class="form-horizontal deposit_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="alert alert-success hidden" role="alert"></div>
        <div class="alert alert-danger hidden" role="alert"></div>

        <input type="hidden" name="deposit_id" id="deposit_id" value="{{ isset($data->id) ? $data->id : '' }}">
        <input type="hidden" name="user_id" id="user_id" value="{{ isset($data->user_id) ? $data->user_id : 0 }}">
		
		<div class="form-group">
            <label>Delivery boy: *</label>
			<select name="logistic_id" id="logistic_id" class="form-control">
				<option value="">Select Delivery boy</option>
				<?php 
				if(isset($delivery_boy_list) && count($delivery_boy_list)>0){
					foreach($delivery_boy_list as $delivery_boy){
						echo '<option value="'.$delivery_boy->id.'">'.$delivery_boy->name.'</option>';
					}
				}
				?>
			</select>
        </div>		
		<div class="form-group">
            <label>Reference Number: *</label>
			<input type="text" name="reference_number" id="reference_number" class="form-control" value="{{ isset($data->reference_number) ? $data->reference_number : '' }}">
        </div>
        <div class="form-group">
            <label>Amount: *</label>
			<input type="text" name="amount" id="amount" class="form-control" value="{{ isset($data->amount) ? $data->amount : '' }}">
        </div>
     </form>
 </div> 

 <!--   Validation JS-->
<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
<script type="text/javascript">

	$(document).ready(function() {
		
		$(".deposit_form").validate({
			rules: {
				logistic_id : {
					required : true
				},
				amount : {
					required : true
				},
				reference_number : {
					required : true
				}
			},
			highlight: function(element) {
			  $(element).removeClass('is-valid').addClass('is-invalid');
			},
			unhighlight: function(element) {
			  $(element).removeClass('is-invalid').addClass('is-valid');
			},
		});
	});
     
 </script>
         