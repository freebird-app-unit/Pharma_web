 <div class="modal-body">
    <form id="sample-form" name="sample-form" method="POST" class="form-horizontal pill_shape_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="alert alert-success hidden" role="alert"></div>
        <div class="alert alert-danger hidden" role="alert"></div>

        <input type="hidden" name="pill_shape_id" id="pill_shape_id" value="{{ isset($data->id) ? $data->id : '' }}">
		<input type="hidden" name="hidden_image" id="hidden_image" value="{{ isset($data->Image) ? $data->Image : '' }}">
         
        <div class="form-group">
            <label>Name: *</label>
            <input type="text" id="name" name="name" value="{{ isset($data->name) ? $data->name : '' }}" class="form-control" >
        </div>
		
		<div class="form-group">
            <label>Image:</label> 
            <input type="file" class="filestyle" name="image" accept="image/x-png,image/gif,image/jpeg" data-input="false">
            @if(!empty($data->image) && isset($data->image))
            <div class="m-t-15 image_div">
                <a href="javascript:void(0)">
                    @if (file_exists(storage_path('app/public/uploads/piil_shape/'.$data->image)))
                        @php $image_path = asset('storage/app/public/uploads/piil_shape/' . $data->image) @endphp
                    @else 
                        {{ $image_path = '' }}
                    @endif
                    <img src="{{ $image_path }}"  class="img-responsive img-thumbnail" width="100">
					<a style="cursor: pointer;" class="m-l-10 action-icon deleteImage" data-id="{{ isset($data->id) ? $data->id : '' }}" ><i class="fa fa-trash text-danger"></i></a>
                </a>
            </div>
            @endif
        </div>
     </form>
 </div>

 <!--   Validation JS-->
<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
<script type="text/javascript">

	$(document).ready(function() {
		
		$(".deleteImage").click(function(){
            var id = $(this).data('id');

            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url:'{{ url("/pill_shape/delete_image") }}',
                type: 'POST',
                data:  {id : id},
                success: function (data) {
                    $('.image_div').remove();
                    $('#image_'+id).remove();
                }
            });
        });
		
		$(".pill_shape_form").validate({
			rules: {
				name : {
					required : true,
				},
				image : {
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
         