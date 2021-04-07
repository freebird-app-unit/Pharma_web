@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title">{{ $page_title }}</h4>
			<ol class="breadcrumb">
				<li><a href="{{ url('/') }}">Dashboard</a></li>
				<li class="active">{{ $page_title }}</li>
			</ol>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="card-box">
		<?php 
		$image_path = 'app/public/uploads/terms_condition/'; 
		$img = 'storage/app/public/uploads/terms_condition/';
		?>
			<form class="form-horizontal" method="POST" action="@if(isset($termscondition)){{ route('termscondition.edit',array('id'=>$termscondition->id)) }} @else{{ route('termscondition.create') }}@endif" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div id='imageBlock'>
					<div class="form-group">
						<label class="control-label col-md-2 col-sm-2 col-xs-6"  for="file">Terms & Condition</label>
						<div class="col-md-4 col-sm-4 col-xs-6  @if($errors->has('file')) bad @endif">
							<input type="file" class="form-control" name="file" id="file" data-input="false">
							@if ($errors->has('file')) <div class="errors_msg">{{ $errors->first('file') }}</div>@endif
						</div>
						@if(!empty($termscondition->file) && isset($termscondition->file))
						<div class="m-t-15 image_div col-md-2 col-sm-2 col-xs-6">
								@if (file_exists(storage_path($image_path.$termscondition->file)))
									@php $image_path = $termscondition->file @endphp
								@else 
									{{ $image_path = '' }}
								@endif
								{{ $image_path }}
						</div>
						@endif
					</div>
				</div>
				
				<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
						<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="submit" value="Save">
						<a href="{{ route('termscondition.index') }}" class="btn btn-sm btn-warning cancel">Cancel</a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection