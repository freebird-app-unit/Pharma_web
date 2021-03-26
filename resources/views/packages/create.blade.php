 <div class="modal-body">
    <form id="sample-form" name="sample-form" method="POST" class="form-horizontal package_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="alert alert-success hidden" role="alert"></div>
        <div class="alert alert-danger hidden" role="alert"></div>

        <input type="hidden" name="package_id" id="package_id" value="{{ isset($data->id) ? $data->id : '' }}">
		 
        <div class="form-group">
            <label>Name: *</label>
			<input type="text" name="name" class="form-control" value="{{ isset($data->name) ? $data->name : '' }}">
        </div>
		
		<div class="form-group">
            <label>Price: *</label>
			<input type="text" name="price" class="form-control" value="{{ isset($data->price) ? $data->price : '' }}">
        </div>
		
		<div class="form-group">
            <label>Total Delivery: *</label>
			<input type="text" name="total_delivery" class="form-control" value="{{ isset($data->total_delivery) ? $data->total_delivery : '' }}">
        </div>
		<?php 
		if(isset($data->is_active)){
		?>
		<div class="form-group">
            <label>Active:</label>
			<select class="form-control" id="is_active" name="is_active">
				<option value="">Select status</option>
				<option <?php echo (isset($data->is_active) && $data->is_active==1)?'selected':''; ?> value="1">Active</option>
				<option <?php echo (isset($data->is_active) && $data->is_active==0)?'selected':''; ?> value="0">Inactive</option>
			</select>
        </div>
		<?php
		}
		?>
     </form>
 </div> 

 <!--   Validation JS-->
<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
<script type="text/javascript">

	$(document).ready(function() {
		
		$(".package_form").validate({
			rules: {
				name : {
					required : true
				},
				price : {
					required : true
				},
				total_delivery : {
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
         