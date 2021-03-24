 <div class="modal-body">
    <form id="sample-form" name="sample-form" method="POST" class="form-horizontal broad_cast_notification_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="alert alert-success hidden" role="alert"></div>
        <div class="alert alert-danger hidden" role="alert"></div>
        <div class="form-group">
            <label>Title: *</label>
			<input type="text" name="name" class="form-control" value="{{ isset($data->name) ? $data->name : '' }}">
        </div>
        <div class="form-group">
            <label>Description: *</label>
            <textarea name="description" class="form-control">{{ isset($data->description) ? $data->description : '' }}</textarea>
        </div>
        <div class="form-group">
            <label>Image:</label> 
            <input type="file" class="filestyle" name="image" accept="image/x-png,image/gif,image/jpeg" data-input="false">
        </div>
     </form>
 </div>

 <!--   Validation JS-->
<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
<script type="text/javascript">

	$(document).ready(function() {
		
		$(".broad_cast_notification_form").validate({
			rules: {
				name : {
					required : true,
				},
				description : {
					required : true,
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
         