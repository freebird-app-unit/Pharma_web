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
                <!DOCTYPE html>
				<html>
				<head>
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<style>
				.switch {
				  position: relative;
				  display: inline-block;
				  width: 60px;
				  height: 34px;
				}

				.switch input { 
				  opacity: 0;
				  width: 0;
				  height: 0;
				}

				.slider {
				  position: absolute;
				  cursor: pointer;
				  top: 0;
				  left: 0;
				  right: 0;
				  bottom: 0;
				  background-color: #ccc;
				  -webkit-transition: .4s;
				  transition: .4s;
				}

				.slider:before {
				  position: absolute;
				  content: "";
				  height: 26px;
				  width: 26px;
				  left: 4px;
				  bottom: 4px;
				  background-color: white;
				  -webkit-transition: .4s;
				  transition: .4s;
				}

				input:checked + .slider {
				  background-color: #2196F3;
				}

				input:focus + .slider {
				  box-shadow: 0 0 1px #2196F3;
				}

				input:checked + .slider:before {
				  -webkit-transform: translateX(26px);
				  -ms-transform: translateX(26px);
				  transform: translateX(26px);
				}

				/* Rounded sliders */
				.slider.round {
				  border-radius: 34px;
				}

				.slider.round:before {
				  border-radius: 50%;
				}
				</style>
				</head>
				<body>

				<label class="switch">
				  <input type="checkbox" id="togBtn" checked>
				  <span class="slider round"></span>
				</label>

				</body>
				</html> 

@endsection
@section('script')
	<script type="text/javascript">
		var switchStatus = true;
		$("#togBtn").on('change', function() {
		    if ($(this).is(':checked')) {
		        switchStatus = $(this).is(':checked');
		        alert(switchStatus);// To verify
		    }
		    else {
		       switchStatus = $(this).is(':checked');
		       alert(switchStatus);// To verify
		    }
		});	
</script>
@endsection