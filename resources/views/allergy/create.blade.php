 <div class="modal-body">
    <form id="sample-form" name="sample-form" method="POST" class="form-horizontal allergy_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="alert alert-success hidden" role="alert"></div>
        <div class="alert alert-danger hidden" role="alert"></div>

        <input type="hidden" name="allergy_id" id="allergy_id" value="{{ isset($data->id) ? $data->id : '' }}">
        <input type="hidden" name="user_id" id="user_id" value="{{ isset($data->user_id) ? $data->user_id : 0 }}">
		 
        <div class="form-group">
            <label>Name: *</label>
			<input type="text" name="allergy_name" class="form-control" value="{{ isset($data->allergy_name) ? $data->allergy_name : '' }}">
        </div>
     </form>
 </div> 

 <!--   Validation JS-->
<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
<script type="text/javascript">

	$(document).ready(function() {
		
		$(".allergy_form").validate({
			rules: {
				allergy_name : {
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
         