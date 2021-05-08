<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <!--<link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">-->

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
	
	<link rel="shortcut icon" href="{{ asset('public/uploads/site_logo.png') }}">
    <!--Morris Chart CSS -->
	<link rel="stylesheet" href="{{ asset('public/admin/plugins/morris/morris.css') }}">
    <link href="{{ asset('public/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/admin/css/core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/admin/css/components.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/admin/css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/admin/css/pages.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/admin/css/responsive.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('public/admin/css/style.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('public/admin/js/modernizr.min.js') }}"></script>
</head>
<body>
			<!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="account-pages"></div>
			<div class="clearfix"></div>
				<div class="wrapper-page" style="width:100%;margin:0;">
					@yield('content')
				</div>
	<script>
		var resizefunc = [];
    </script>

    <!-- jQuery  -->
	<script src="{{ asset('public/admin/js/jquery.min.js') }}"></script>
	<script src="{{ asset('public/admin/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('public/admin/js/detect.js') }}"></script>
	<script src="{{ asset('public/admin/js/fastclick.js') }}"></script>

	<script src="{{ asset('public/admin/js/jquery.slimscroll.js') }}"></script>
	<script src="{{ asset('public/admin/js/jquery.blockUI.js') }}"></script>
	<script src="{{ asset('public/admin/js/waves.js') }}"></script>
	<script src="{{ asset('public/admin/js/wow.min.js') }}"></script>
	<script src="{{ asset('public/admin/js/jquery.nicescroll.js') }}"></script>
	<script src="{{ asset('public/admin/js/jquery.scrollTo.min.js') }}"></script>

	<script src="{{ asset('public/admin/plugins/peity/jquery.peity.min.js') }}"></script>

	<!-- jQuery  -->
	<script src="{{ asset('public/admin/plugins/waypoints/lib/jquery.waypoints.js') }}"></script>
	<script src="{{ asset('public/admin/plugins/counterup/jquery.counterup.min.js') }}"></script>

	<script src="{{ asset('public/admin/plugins/morris/morris.min.js') }}"></script>
	<script src="{{ asset('public/admin/plugins/raphael/raphael-min.js') }}"></script>

	<script src="{{ asset('public/admin/plugins/jquery-knob/jquery.knob.js') }}"></script>

	<script src="{{ asset('public/admin/pages/jquery.dashboard.js') }}"></script>

	<script src="{{ asset('public/admin/js/jquery.core.js') }}"></script>
	<script src="{{ asset('public/admin/js/jquery.app.js') }}"></script>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.counter').counterUp({
			delay: 100,
			time: 1200
		});
		$(".knob").knob();
	});
	</script>
</body>
</html>
