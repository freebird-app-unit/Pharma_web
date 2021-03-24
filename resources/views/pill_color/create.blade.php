 <div class="modal-body">
    <form id="sample-form" name="sample-form" method="POST" class="form-horizontal pill_color_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="alert alert-success hidden" role="alert"></div>
        <div class="alert alert-danger hidden" role="alert"></div>

        <input type="hidden" name="pill_color_id" id="pill_color_id" value="{{ isset($data->id) ? $data->id : '' }}">
		 
        <div class="form-group">
            <label>Name: *</label>
			<input type="text" name="name" class="form-control" value="{{ isset($data->name) ? $data->name : '' }}">
        </div>
		<div class="form-group">
            <label>Color: *</label>
            <input type="text" class="colorpicker-default form-control" name="color" value="{{ isset($data->color) ? $data->color : '' }}">
        </div>
     </form>
 </div> 

 <!--   Validation JS-->
<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
<script type="text/javascript">

	$(document).ready(function() {
		
		$('.colorpicker-default').colorpicker();
		
		$(".pill_color_form").validate({
			rules: {
				name : {
					required : true
				},
				color : {
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
         