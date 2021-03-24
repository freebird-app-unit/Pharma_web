 <div class="modal-body">
    <form id="sample-form" name="sample-form" method="POST" class="form-horizontal disease_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="alert alert-success hidden" role="alert"></div>
        <div class="alert alert-danger hidden" role="alert"></div>

        <input type="hidden" name="disease_id" id="disease_id" value="{{ isset($data->id) ? $data->id : '' }}">
		 
        <div class="form-group">
            <label>Name: *</label>
			<input type="text" name="name" class="form-control" value="{{ isset($data->name) ? $data->name : '' }}">
        </div>
     </form>
 </div> 

 <!--   Validation JS-->
<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
<script type="text/javascript">

	$(document).ready(function() {
		
		$(".disease_form").validate({
			rules: {
				name : {
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
         