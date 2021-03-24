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
			<form class="form-horizontal" method="POST" action="{{ route('custom_notification.create') }}" id="user_detail-form" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="title">Title<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('title')) bad @endif">
						<input type="text" placeholder="" class="form-control" name="title" id="title">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2 col-sm-2 col-xs-12" for="body">Body<span class="required">*</span></label>
					<div class="col-md-8 col-sm-8 col-xs-12  @if($errors->has('body')) bad @endif">
						<input type="text" placeholder="" class="form-control" name="body" id="body">
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
						<input class="btn btn-sm btn-primary submit save_btn" name="save_exit" type="button" value="Save">
						<!-- <a href="{{ route('user.index') }}" class="btn btn-sm btn-warning cancel">Cancel</a> -->
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

