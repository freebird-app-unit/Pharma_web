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
<ul class="nav nav-tabs">
	<li class="nav-item">
		<a href="javascript:;" class="nav-link active">Search Orders</a>
	</li>
</ul>
<div class="row">
	<div class="col-sm-12">
	
		<div class="card-box">
		@if(Session::has('success_message'))
			<div class="alert alert-success alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				{{ Session::get('success_message') }}
	        </div>
		@endif
			<div class="table-rep-plugin">
				<div class="table-responsive" data-pattern="priority-columns">
				<input type="hidden" name="search_text" id="search_text" value="{{ $search_text }}"/>
					<span class="text-danger">Order not found</span>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection