<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $site_title }}</title>

    <!-- Scripts -->
    <script src="{{ asset('public/js/app.js') }}" defer></script>

    <!-- Fonts -->
    <!--<link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">-->

    <!-- Styles -->
    <link href="{{ asset('public/css/app.css') }}" rel="stylesheet">
	
	<link rel="shortcut icon" href="assets/images/favicon_1.ico">
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
	<link href="{{ asset('public/admin/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('public/admin/css/jquery.growl.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{asset('public/admin/plugins/sweetalert/sweetalert.css')}}" rel="stylesheet" type="text/css">
	<link href="{{asset('public/admin/plugins/lightbox/simple-lightbox.min.css')}}" rel="stylesheet" type="text/css">
	<link href="{{ asset('public/admin/css/pharmacy.css') }}" rel="stylesheet" type="text/css" />
	<style>
		#map {
			height: 300px;
			width: 40%;
		}
		/*.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
		    word-break: break-all;
		}*/
		.table>tbody>tr>td{
		    word-break: break-all;
		}
		#sidebar-menu ul li a img{margin-right:5px;}
	</style>
</head>
<body class="fixed-left">
    
	<!-- Begin page -->
    <div id="wrapper">
		<!-- Top Bar Start -->
		<div class="topbar">
			<!-- LOGO -->
			<div class="topbar-left">
				<div class="text-center">
					<a href="{{ url('/')}}" class="logo">
					<?php
						$logo = get_settings('site_logo');
						
						if($logo!=''){
							$destinationPath = base_path() . '/uploads/'.$logo;
							if(file_exists($destinationPath)){
					?>
						<img src="<?php echo url('/').'/uploads/'.$logo; ?>" width="50"/>
					<?php
							}else{
					?>
						<span>Pharma</span>	
					<?php
							}
						}else{
					?>
						<span>Pharma</span>
					<?php 
						} 
					?>
					</a>
				</div>
			</div>

			<!-- Button mobile view to collapse sidebar menu -->
			<div class="navbar navbar-default" role="navigation">
				<div class="container">
					<div class="">
						<div class="pull-left">
							<button class="button-menu-mobile open-left">
								<i class="ion-navicon"></i>
							</button>
							<span class="clearfix"></span>
						</div>
						<form role="search" class="navbar-left app-search pull-left hidden-xs" onsubmit="return search_all_order();">
							<input type="text" placeholder="Universal Search..." class="form-control" id="all_order_search" style="color:#c3c3c3;" value="<?php echo (isset($_REQUEST['search_text']))?$_REQUEST['search_text']:''; ?>">
							<a href="javascript:;" onclick="search_all_order()"><i class="fa fa-search"></i></a>
						</form>
						<ul class="nav navbar-nav navbar-right pull-right">
							<!--<li class="dropdown hidden-xs">
								<a href="#" data-target="#" class="dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="true">
									<i class="icon-bell"></i> <span class="badge badge-xs badge-danger">3</span>
								</a>
								<ul class="dropdown-menu dropdown-menu-lg">
									<li class="notifi-title"><span class="label label-default pull-right">New 3</span>Notification</li>
									<li class="list-group nicescroll notification-list">
									
										<a href="javascript:void(0);" class="list-group-item">
											<div class="media">
												<div class="pull-left p-r-10">
													<em class="fa fa-diamond fa-2x text-primary"></em>
												</div>
												<div class="media-body">
													<h5 class="media-heading">A new order has been placed A new order has been placed</h5>
														<p class="m-0">
															<small>There are new settings available</small>
														</p>
												</div>
											</div>
										</a>

										
										<a href="javascript:void(0);" class="list-group-item">
											<div class="media">
												<div class="pull-left p-r-10">
													<em class="fa fa-cog fa-2x text-custom"></em>
												</div>
												<div class="media-body">
													<h5 class="media-heading">New settings</h5>
													<p class="m-0">
														<small>There are new settings available</small>
													</p>
                                                 </div>
											</div>
										</a>

										
										<a href="javascript:void(0);" class="list-group-item">
											<div class="media">
												<div class="pull-left p-r-10">
													<em class="fa fa-bell-o fa-2x text-danger"></em>
                                                </div>
                                                <div class="media-body">
                                                    <h5 class="media-heading">Updates</h5>
                                                    <p class="m-0">
                                                        <small>There are <span class="text-primary font-600">2</span> new updates available</small>
                                                    </p>
                                                </div>
                                             </div>
                                         </a>

                                         
                                         <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left p-r-10">
                                                    <em class="fa fa-user-plus fa-2x text-info"></em>
                                                 </div>
                                                 <div class="media-body">
                                                    <h5 class="media-heading">New user registered</h5>
                                                    <p class="m-0">
                                                        <small>You have 10 unread messages</small>
                                                    </p>
                                                 </div>
                                              </div>
                                         </a>

                                         
                                         <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left p-r-10">
                                                    <em class="fa fa-diamond fa-2x text-primary"></em>
                                                 </div>
                                                 <div class="media-body">
                                                    <h5 class="media-heading">A new order has been placed A new order has been placed</h5>
                                                    <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                 </div>
                                              </div>
                                         </a>

                                         
                                         <a href="javascript:void(0);" class="list-group-item">
                                                <div class="media">
                                                    <div class="pull-left p-r-10">
                                                     <em class="fa fa-cog fa-2x text-custom"></em>
                                                    </div>
                                                    <div class="media-body">
                                                      <h5 class="media-heading">New settings</h5>
                                                      <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                    </div>
                                              </div>
                                         </a>
                                       </li>
                                       <li>
                                            <a href="javascript:void(0);" class="list-group-item text-right">
                                                <small class="font-600">See all notifications</small>
                                            </a>
                                        </li>
                                    </ul>
                                </li>-->
                                <li class="hidden-xs">
                                    <a href="#" id="btn-fullscreen" class="waves-effect waves-light"><i class="icon-size-fullscreen"></i></a>
                                </li>
								<!--<li class="dropdown hidden-xs">
                                    <a href="#" data-target="#" class="dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="true">
                                        <i class="icon-bell"></i> <span class="badge badge-xs badge-danger">3</span>
                                    </a>
								</li>-->
								<li class="hidden-xs">
                                    <a href="javascript:;" class="right-bar-toggle waves-effect waves-light">{{ Auth::user()->name }}</a>
                                </li>
                                <li class="dropdown">
                                    <a href="" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true"><!--<img src="assets/images/users/avatar-1.jpg" alt="user-img" class="img-circle">--><i class="ti-user m-r-5"></i> </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="{{ route('profile') }}"><i class="ti-user m-r-5"></i> Profile</a></li>
										<li><a href="{{ route('changepassword') }}"><i class="glyphicon glyphicon-edit"></i> Change password</a></li>
										<li>
											<a href="{{ route('logout') }}" onclick="event.preventDefault();
													document.getElementById('logout-form').submit();"><i class="ti-power-off m-r-5"></i>{{ __('Logout') }}</a>
											<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
												@csrf
											</form>		 
										</li>
                                    </ul>
                                </li>
                            </ul>
                          
                        </div>
                        <!--/.nav-collapse -->
                    </div>
                </div>
            </div>
            <!-- Top Bar End -->
			<!-- ========== Left Sidebar Start ========== -->

            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">
                    <!--- Divider -->
                    <div id="sidebar-menu">
                        <ul>
							
							<?php if(Auth::user()->user_type=='pharmacy' || Auth::user()->user_type=='seller'){ ?>
								<li>
									<a href="{{ route('home') }}" class="waves-effect <?php echo ($page_condition=='page_dashboard')?'active':''; ?>"><img src="{{ asset('public/images/dashboard.png') }}"/><span>{{ __('Dashboard') }}</span></a>
								</li>
								<li>
									<a href="{{ route('upcomingorders.index') }}" class="waves-effect <?php echo ($page_condition=='page_acceptedorders' || $page_condition=='page_upcomingorder' || $page_condition=='page_pickup' || $page_condition=='page_outfordelivery')?'active':''; ?>"><img src="{{ asset('public/images/live_order.png') }}"/><span>{{ __('Live Orders') }}</span></a>
								</li>
								<li>
									<a href="{{ route('complete.index') }}" class="waves-effect <?php echo ($page_condition=='page_complete')?'active':''; ?>"><img src="{{ asset('public/images/complete_order.png') }}"/><span>{{ __('Completed Orders') }}</span></a>
								</li>
								<li class="has_sub">
	                                <a href="javascript:void(0);" class="waves-effect"><img src="{{ asset('public/images/report.png') }}"/><span>{{ __('Reports') }} </span> <span class="menu-arrow"></span> </a>
	                                <ul class="list-unstyled">
	                                    <li><a href="{{ route('orders.index') }}" class="waves-effect <?php echo ($page_condition=='page_orders')?'active':''; ?>">{{ __('Orders') }}</a></li>
	                                    <li><a href="{{ route('seller_report.index') }}" class="waves-effect <?php echo ($page_condition=='page_seller_report')?'active':''; ?>">{{ __('Seller Orders') }}</a></li>
	                                    <li><a href="{{ route('pharma_delivery_report.index') }}" class="waves-effect <?php echo ($page_condition=='page_pharma_delivery_report')?'active':''; ?>">{{ __('Delivery Reports') }}</a></li>

	                                    <li><a href="{{ route('pharma_external_delivery_report.index') }}" class="waves-effect <?php echo ($page_condition=='page_pharma_external_delivery_report')?'active':''; ?>">{{ __('External Delivery Reports') }}</a></li>

	                                    <li><a href="">Hisab</a></li>
	                                    <li><a href="{{ route('myorder.index') }}" class="waves-effect <?php echo ($page_condition=='page_myorder')?'active':''; ?>">My Orders</a></li>
	                                </ul>
	                            </li>
	                            <li>
									<a href="{{ route('myorder.index') }}" class="waves-effect <?php echo ($page_condition=='page_myorder')?'active':''; ?>"><img src="{{ asset('public/images/my_team.png') }}"/><span>{{ __('My Team') }}</span></a>
								</li>
								<li>
									<a href="{{ route('pharma_order_report.index') }}" class="waves-effect <?php echo ($page_condition=='page_pharma_order_report')?'active':''; ?>"><img src="{{ asset('public/images/order_report.png') }}"/><span>{{ __('Orders Report') }}</span></a>
								</li>
							<?php } ?>

                			<?php if(Auth::user()->user_type=='logistic'){ ?>
                   			<li>
                                <a href="{{ route('home') }}" class="waves-effect <?php echo ($page_condition=='page_dashboard')?'active':''; ?>"><img src="{{ asset('public/images/dashboard.png') }}"/><span>{{ __('Dashboard') }}</span></a>
                            </li>
							<li>
								<a href="{{ route('logisticupcoming.index') }}" class="waves-effect <?php echo ($page_condition=='page_logisticupcoming')?'active':''; ?>"><img src="{{ asset('public/images/live_order.png') }}"/><span>{{ __('Live Orders') }}</span></a>
							</li>
              				<li>
                                <a href="{{ route('logistic.upcoming.index') }}" class="waves-effect <?php echo ($page_condition=='page_upcoming')?'active':''; ?>"><i class="ti-write"></i> <span>{{ __('Upcoming Orders') }}</span></a>
                            </li>
                 			<li>
                                <a href="{{ route('logistic.pickup.index') }}" class="waves-effect <?php echo ($page_condition=='page_pickup')?'active':''; ?>"><i class="ti-shopping-cart-full"></i> <span>{{ __('Pickup Orders') }}</span></a>
                            </li>
                			<li>
                                <a href="{{ route('logistic.complete.index') }}" class="waves-effect <?php echo ($page_condition=='page_complete_logistic')?'active':''; ?>"><img src="{{ asset('public/images/complete_order.png') }}"/><span>{{ __('Completed Orders') }}</span></a>
                            </li>
              				<li>
                                <a href="{{ route('logistic.incomplete.index') }}" class="waves-effect <?php echo ($page_condition=='page_incomplete_logistic')?'active':''; ?>"><img src="{{ asset('public/images/incomplete_order.png') }}"/><span>{{ __('Incomplete Orders') }}</span></a>
                            </li>
                 			<li>
                                <a href="{{ route('logistic.canceled.index') }}" class="waves-effect <?php echo ($page_condition=='page_canceled_logistic')?'active':''; ?>"><img src="{{ asset('public/images/cancel.png') }}"/><span>{{ __('Canceled Orders') }}</span></a>
                            </li>
               				<li>
                                <a href="{{ route('logistic.deliveryboy.index') }}" class="waves-effect <?php echo ($page_condition=='page_deliveryboy_logistic')?'active':''; ?>"><img src="{{ asset('public/images/delivery_boy.png') }}"/><span>{{ __('Delivery boy') }}</span></a>
                            </li>
							<li>
								<a href="{{ route('logistic.order_report.index') }}" class="waves-effect <?php echo ($page_condition=='page_order_report_logistic')?'active':''; ?>"><img src="{{ asset('public/images/order_report.png') }}"/><span>{{ __('Order report') }}</span></a>
							</li>
							<li>
								<a href="{{ route('voucher.index') }}" class="waves-effect <?php echo ($page_condition=='page_voucher')?'active':''; ?>"><img src="{{ asset('public/images/vouchers.png') }}"/><span>{{ __('Voucher') }}</span></a>
							</li>
							<li>
								<a href="{{ route('voucher.history') }}" class="waves-effect <?php echo ($page_condition=='page_voucher_history')?'active':''; ?>"><img src="{{ asset('public/images/voucher_history.png') }}"/><span>{{ __('Voucher History') }}</span></a>
							</li>
              				<?php } ?>

							<?php if(Auth::user()->user_type=='pharmacy'){ ?>
							<li>
                                <a href="{{ route('allorder.index') }}" class="waves-effect <?php echo ($page_condition=='page_allorder')?'active':''; ?>"><img src="{{ asset('public/images/all_order.png') }}"/><span>{{ __('All Orders') }}</span></a>
                            </li>

             <!--  <li>
                                <a href="{{ route('custom_notification.create') }}" class="waves-effect <?php echo ($page_condition=='page_notification')?'active':''; ?>"><i class="ion-navicon-round"></i> <span>{{ __('Notification') }}</span></a>
                            </li> -->
							<?php } ?>
							
							<?php if(Auth::user()->user_type=='delivery_boy'){ ?>
							<li>
                                <a href="{{ route('deliverycomplete.index') }}" class="waves-effect <?php echo ($page_condition=='page_deliverycomplete')?'active':''; ?>"><img src="{{ asset('public/images/complete_order.png') }}"/><span>{{ __('Complete Orders') }}</span></a>
                            </li>
							<li>
                                <a href="{{ route('deliveryincomplete.index') }}" class="waves-effect <?php echo ($page_condition=='page_deliveryincomplete')?'active':''; ?>"><img src="{{ asset('public/images/incomplete_order.png') }}"/><span>{{ __('Incomplete Orders') }}</span></a>
                            </li>
							<?php } ?>
							
							<?php if(Auth::user()->user_type=='admin'){ ?>
							<li>
                               <!--  <a href="{{ route('createorder.create') }}" class="waves-effect <?php echo ($page_condition=='page_createorder')?'active':''; ?>"><img src="{{ asset('public/images/crete_order.png') }}"/><span>{{ __('Create Orders') }}</span></a> -->
                            </li>
                           <!--  <li>
                                <a href="{{ route('acceptorder.create') }}" class="waves-effect <?php echo ($page_condition=='page_acceptorder')?'active':''; ?>"><img src="{{ asset('public/images/live_order.png') }}"/><span>{{ __('Accept Orders Script') }}</span></a> 
                            </li> -->
							<li>
								<a href="{{ route('adminupcomingorders.index') }}" class="waves-effect <?php echo ($page_condition=='page_adminacceptedorders' || $page_condition=='page_adminupcomingorders')?'active':''; ?>"><img src="{{ asset('public/images/live_order.png') }}"/></i> <span>{{ __('Live Orders') }}</span></a>
							</li>
							<li>
								<a href="{{ route('adminrejected.index') }}" class="waves-effect <?php echo ($page_condition=='page_adminrejected')?'active':''; ?>"><img src="{{ asset('public/images/incomplete_order.png') }}"/><span>{{ __('Incomplete Order') }}</span></a>
							</li>
							<li>
									<a href="{{ route('admincomplete.index') }}" class="waves-effect <?php echo ($page_condition=='page_admincomplete')?'active':''; ?>"><img src="{{ asset('public/images/complete_order.png') }}"/><span>{{ __('Completed Orders') }}</span></a>
								</li>
							<li>
                                <a href="{{ route('user.index') }}" class="waves-effect <?php echo ($page_condition=='page_users')?'active':''; ?>"><img src="{{ asset('public/images/user.png') }}"/><span>{{ __('Users') }}</span></a>
							</li>
                   			<li>
                                <a href="{{ route('pharmacy.index') }}" class="waves-effect <?php echo ($page_condition=='page_pharmacy')?'active':''; ?>"><img src="{{ asset('public/images/pharmacy.png') }}"/><span>{{ __('pharmacy') }}</span></a>
                            </li>
                			<li>
                                <a href="{{ route('seller.index') }}" class="waves-effect <?php echo ($page_condition=='page_sellers')?'active':''; ?>"><img src="{{ asset('public/images/seller.png') }}"/><span>{{ __('sellers') }}</span></a>
                            </li>
							<li>
                                <a href="{{ route('logistic.index') }}" class="waves-effect <?php echo ($page_condition=='page_logistic')?'active':''; ?>"><img src="{{ asset('public/images/logistic.png') }}"/><span>{{ __('logistic') }}</span></a>
                            </li>
                <li>
                                <a href="{{ route('deliveryboy.index') }}" class="waves-effect <?php echo ($page_condition=='page_deliveryboy')?'active':''; ?>"><img src="{{ asset('public/images/delivery_boy.png') }}"/><span>{{ __('Delivery boy') }}</span></a>
                            </li>
							<li>
                                <a href="{{ url('slider') }}" class="waves-effect <?php echo ($page_condition=='page_slider')?'active':''; ?>"><img src="{{ asset('public/images/slider.png') }}"/><span>{{ __('Sliders') }}</span></a>
                            </li>
						<!-- 	<li>
                                <a href="{{ url('pill_shape') }}" class="waves-effect <?php echo ($page_condition=='page_pill_shape')?'active':''; ?>"><i class="glyphicon glyphicon-gift"></i> <span>{{ __('Pill Shape') }}</span></a>
                            </li>
							<li>
                                <a href="{{ url('pill_color') }}" class="waves-effect <?php echo ($page_condition=='page_pill_color')?'active':''; ?>"><i class="glyphicon glyphicon-text-color"></i> <span>{{ __('Pill Color') }}</span></a>
                            </li> -->
                            <li>
                                <a href="{{ url('broad_cast_notification') }}" class="waves-effect <?php echo ($page_condition=='page_broad_cast_notification')?'active':''; ?>"><img src="{{ asset('public/images/annoucement.png') }}"/><span>{{ __('Announcement') }}</span></a>
                            </li>
							<li>
                                <a href="{{ url('disease') }}" class="waves-effect <?php echo ($page_condition=='page_disease')?'active':''; ?>"><img src="{{ asset('public/images/disese.png') }}"/><span>{{ __('Disease') }}</span></a>
                            </li>
							<li>
                                <a href="{{ url('allergy') }}" class="waves-effect <?php echo ($page_condition=='page_allergy')?'active':''; ?>"><img src="{{ asset('public/images/allergy.png') }}"/><span>{{ __('Allergy') }}</span></a>
							</li>
							
							<li>
                                <a href="{{ url('report') }}" class="waves-effect <?php echo ($page_condition=='page_report' || $page_condition=='page_resolveindex')?'active':''; ?>"><img src="{{ asset('public/images/report.png') }}"/><span>{{ __('Report') }}</span></a>
							</li>
							
                			<li>
                                <a href="{{ route('settings') }}" class="waves-effect <?php echo ($page_condition=='page_settings')?'active':''; ?>"><img src="{{ asset('public/images/setting.png') }}"/><span>{{ __('Settings') }}</span></a>
                            </li>
							<li>
								<a href="{{ route('order_report.index') }}" class="waves-effect <?php echo ($page_condition=='page_order_report')?'active':''; ?>"><img src="{{ asset('public/images/order_report.png') }}"/><span>{{ __('Order Report') }}</span></a>
							</li>

							<li>
								<a href="{{ route('order_filter.index') }}" class="waves-effect <?php echo ($page_condition=='page_order_filter')?'active':''; ?>"><img src="{{ asset('public/images/order_filter.png') }}"/><span>{{ __('Order Filter') }}</span></a>
							</li>
							
							
							<li>
								<a href="{{ route('voucher.index') }}" class="waves-effect <?php echo ($page_condition=='page_order_voucher')?'active':''; ?>"><img src="{{ asset('public/images/vouchers.png') }}"/><span>{{ __('Voucher') }}</span></a>
							</li>
							<li>
								<a href="{{ route('voucher.history') }}" class="waves-effect <?php echo ($page_condition=='page_order_voucher_history')?'active':''; ?>"><img src="{{ asset('public/images/voucher_history.png') }}"/><span>{{ __('Voucher History') }}</span></a>
							</li>
							<?php } ?>
							<?php if(Auth::user()->user_type=='pharmacy'){ ?> 
							<li>
                                <a href="{{ route('seller.index') }}" class="waves-effect <?php echo ($page_condition=='page_sellers')?'active':''; ?>"><img src="{{ asset('public/images/seller.png') }}"/><span>{{ __('sellers') }}</span></a>
                            </li>
							<?php } ?>
							<?php if(Auth::user()->user_type=='pharmacy' || Auth::user()->user_type=='seller'){ ?>
							<li>
                                <a href="{{ route('deliveryboy.index') }}" class="waves-effect <?php echo ($page_condition=='page_deliveryboy')?'active':''; ?>"><img src="{{ asset('public/images/delivery_boy.png') }}"/><span>{{ __('Delivery boy') }}</span></a>
                            </li>
							<li>
								<a href="{{ route('order_report.index') }}" class="waves-effect <?php echo ($page_condition=='page_order_report')?'active':''; ?>"><img src="{{ asset('public/images/order_report.png') }}"/><span>{{ __('Order report') }}</span></a>
							</li>
							<li>
								<a href="{{ route('voucher.index') }}" class="waves-effect <?php echo ($page_condition=='page_order_voucher')?'active':''; ?>"><img src="{{ asset('public/images/vouchers.png') }}"/><span>{{ __('Voucher') }}</span></a>
							</li>
							<li>
								<a href="{{ route('voucher.history') }}" class="waves-effect <?php echo ($page_condition=='page_voucher_history')?'active':''; ?>"><img src="{{ asset('public/images/voucher_history.png') }}"/><span>{{ __('Voucher History') }}</span></a>
							</li>
							<?php } ?>
							
                            <!--<li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="ti-paint-bucket"></i> <span> UI Kit </span> <span class="menu-arrow"></span> </a>
                                <ul class="list-unstyled">
                                    <li><a href="ui-buttons.html">Buttons</a></li>
                                    <li><a href="ui-loading-buttons.html">Loading Buttons</a></li>
                                    <li><a href="ui-panels.html">Panels</a></li>
                                    <li><a href="ui-portlets.html">Portlets</a></li>
                                    <li><a href="ui-checkbox-radio.html">Checkboxs-Radios</a></li>
                                    <li><a href="ui-tabs.html">Tabs</a></li>
                                    <li><a href="ui-modals.html">Modals</a></li>
                                    <li><a href="ui-progressbars.html">Progress Bars</a></li>
                                    <li><a href="ui-notification.html">Notification</a></li>
                                    <li><a href="ui-images.html">Images</a></li>
                                    <li><a href="ui-carousel.html">Carousel</a>
                                    <li><a href="ui-bootstrap.html">Bootstrap UI</a></li>
                                    <li><a href="ui-typography.html">Typography</a></li>
                                </ul>
                            </li>-->
							
							<?php if(Auth::user()->user_type=='pharmacy' || Auth::user()->user_type=='admin'){ ?>
							<li>
								<a href="{{ url('/packages') }}" class="waves-effect <?php echo ($page_condition=='page_packages')?'active':''; ?>"><img src="{{ asset('public/images/voucher_history.png') }}"/><span>{{ __('Packages') }}</span></a>
							</li>
							<?php } ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <!-- Left Sidebar End -->
			
			<!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="content-page">
				<!-- Start content -->
				<div class="content">
					<div class="container">
						@yield('content')
					</div>
				</div>
			</div>
		</div>
	<script>
		var resizefunc = [];
    </script>
	<div class="modal fade" id="modelcommon" role="dialog" aria-labelledby="modalConfirmLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">       
				<div class="modal-header">
					<h3 class="modal-title" id="modalConfirmLabel"></h3>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body" id="model_content"></div>
				<div class="modal-footer" id="modal_footer">
					<button type="button" class="btn btn-secondary waves-effect close_model reset-btn" id="close_model"   data-dismiss="modal" tabindex=99 >Close</button>
				</div>
			</div>
		</div>
	</div>
	@if($page_condition=='page_upcomingorders')
	<div id="assign_modal" class="modal fade bs-example-modal-sm in" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="mySmallModalLabel">Assign to</h4>
				</div>
				<div class="modal-body">
					<form method="post" action="{{ route('logistic.upcoming.assign') }}">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					<input type="hidden" id="assign_id" name="assign_id" value=""/>
					<label>Assign</label>
					<select id="delivery_boy" name="delivery_boy" class="form-control" required>
						<option value="">Select delivery boy</option>
						<?php 
						foreach($deliveryboy_list as $raw){
							echo '<option value="'.$raw->id.'">'.$raw->name.'</option>';
						}
						?>
					</select>
					<br>
					<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
					<input type="submit" name="submit" value="Send" class="btn btn-success"/>
					</form>
					
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->	

	<div id="reject_modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="mySmallModalLabel">Reject reason</h4>
				</div>
				<div class="modal-body">
					<form method="post" action="{{ route('logistic.upcoming.reject') }}">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					<input type="hidden" id="reject_id" name="reject_id" value=""/>
					<label>Select reject reason</label>
					<select id="reject_reason" name="reject_reason" class="form-control" required>
						<option value="">Select reason</option>
						<?php 
						foreach($reject_reason as $raw){
							echo '<option value="'.$raw->reason.'">'.$raw->reason.'</option>';
						}
						?>
					</select>
					<br>
					<a href="javascript:;" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Cancel</a>
					<input type="submit" name="submit" value="Send" class="btn btn-success"/>
					</form>
					
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	@endif
	<script>var base_url = "<?php echo url('/'); ?>";</script>
	<script>var action = '<?php echo $page_condition; ?>'</script>
	<script>var search_text_global = "<?php echo (isset($_REQUEST['search_text']))?$_REQUEST['search_text']:''; ?>"</script>
    <!-- jQuery  -->
	@if($page_condition!='page_forms_create')
	<script src="{{ asset('public/admin/js/jquery.min.js') }}"></script>
	@endif
	<!--<script src="{{ asset('public/admin/js/jquery.min.js') }}"></script>-->
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
	<!-- 
	<script src="{{ asset('public/admin/pages/jquery.dashboard.js') }}"></script>
	-->
	<script src="{{ asset('public/admin/js/jquery.core.js') }}"></script>
	<script src="{{ asset('public/admin/js/jquery.app.js') }}"></script>
	<script src="{{ asset('public/admin/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
	
	<script src="{{ asset('public/admin/plugins/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('public/admin/plugins/datatables/dataTables.bootstrap.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/datatables/datatables.min.css') }}">
	
	<script src="{{ asset('/public/admin/js/jquery.growl.js') }}"></script> 
	<script src="{{ asset('public/admin/js/moment.min.js') }}"></script>
	<script src="{{ asset('public/admin/js/bootstrap-datetimepicker.min.js') }}"></script>
	<script src="{{asset('public/admin/plugins/sweetalert/sweetalert.min.js')}}"></script>
	@if($page_condition=='page_dashboard')
		<script src="{{ asset('/public/admin/js/alertify.min.js') }}"></script>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/alertify.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/default.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/semantic.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/bootstrap1.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/alertify.rtl.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/default.rtl.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/semantic.rtl.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/bootstrap.rtl.min.css') }}"/>
				
		<script src="//{{ Request::getHost() }}:{{env('LARAVEL_ECHO_PORT')}}/socket.io/socket.io.js"></script>
		<script src="{{ asset('public/js/echo.js') }}" type="text/javascript"></script>

		<script src="{{ asset('public/admin/js/dashboard.js') }}"></script>
		<script type="text/javascript">
			function formatDate(date) {
				var mnth = ['Jan', 'Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

				var d = new Date(date),
					month = '' + mnth[Number(d.getMonth())],
					day = '' + d.getDate(),
					year = d.getFullYear();

				if (month.length < 2) month = '0' + month;
				if (day.length < 2) day = '0' + day;
				var strTime = [day, month, year].join('-')+' ';

				var hours = d.getHours();
				var minutes = d.getMinutes();
				var ampm = hours >= 12 ? 'pm' : 'am';

				hours = hours % 12;
				hours = hours ? hours : 12; // the hour '0' should be '12'
				minutes = minutes < 10 ? '0'+minutes : minutes;
				strTime = strTime + hours + ':' + minutes + ' ' + ampm;
				return strTime;
			}

			var i = 0;
			var array =  <?php echo json_encode($all_pharmacy_ids); ?>;
			array.forEach(element => {
				console.log('Join Socket Channel :'+' CreateNewOrder'+element);
				window.Echo.channel('CreateNewOrder'+element)
				.listen('.NewOrder', (data) => {
					console.log(data);
					var i = 0;
		
					setTimeout(() => {
						alertify.success("New Order Placed..." + data.OrderDetail.order_number);
					}, 1000);

					i++;
					$("#notification").append('<div class="alert alert-success">'+i+'.'+'data.title'+'</div>');
					var specific_tbody = document.getElementById('admin_dashboardorder_body');

					var NewRow = specific_tbody.insertRow(0);
					var Newcell0 = NewRow.insertCell(0); 
					// var Newcell1 = NewRow.insertCell(1); 
					// var Newcell2 = NewRow.insertCell(2); 
					var Newcell1 = NewRow.insertCell(1); 
					var Newcell2 = NewRow.insertCell(2); 
					var Newcell3 = NewRow.insertCell(3); 
					var Newcell4 = NewRow.insertCell(4); 
					var Newcell5 = NewRow.insertCell(5); 

					var is_paid = (data.OrderDetail.is_external_delivery)?('<i class="ti-truck" style="color: orange;"></i> '):'';
					Newcell0.innerHTML = data.OrderDetail.number;
					// Newcell1.innerHTML = ''+ is_paid + data.OrderDetail.order_type;
					// Newcell2.innerHTML = data.OrderDetail.order_note;
					Newcell1.innerHTML = data.OrderDetail.customer_name;
					Newcell2.innerHTML = data.OrderDetail.customer_number;
					Newcell3.innerHTML = data.OrderDetail.address;
					Newcell4.innerHTML = formatDate(data.OrderDetail.created_at);
					Newcell5.innerHTML = '<a class="btn btn-success waves-effect waves-light" href="'+'/pharma/orders/accept/'+data.OrderDetail.id+'?home'+'" title="Accept order">Accept</a><a onclick="reject_order('+data.OrderDetail.id+')" class="btn btn-danger btn-custom waves-effect waves-light" href="" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';
				})
			})
		</script>
	@endif
	@if($page_condition=='page_order_report')
		<script src="{{ asset('public/admin/js/order_report.js') }}"></script>
	@endif
	@if($page_condition=='page_order_report_logistic')
		<script src="{{ asset('public/admin/js/logistic/order_report.js') }}"></script>
	@endif
	@if($page_condition=='page_voucher')
		<script src="{{ asset('public/admin/js/voucher.js') }}"></script>
	@endif
	@if($page_condition=='page_voucher_history')
		<script src="{{ asset('public/admin/js/voucher_history.js') }}"></script>
	@endif
	@if($page_condition=='page_voucher_detail')
		<script src="{{ asset('public/admin/js/voucher_detail.js') }}"></script>
	@endif
	@if($page_condition=='page_users' || $page_condition=='page_user_create' || $page_condition=='page_client_create')
		<script src="{{ asset('public/admin/js/users.js') }}"></script>
	@endif
   @if($page_condition=='page_pharmacy')
	<script src="{{ asset('public/admin/js/pharmacy.js') }}"></script>
  @endif
	@if($page_condition=='page_sellers')
		<script src="{{ asset('public/admin/js/sellers.js') }}"></script>
	@endif
	@if($page_condition=='page_logistic')
		<script src="{{ asset('public/admin/js/logistics.js') }}"></script>
	@endif
	@if($page_condition=='page_logistic_dashboard')
		<script src="{{ asset('public/admin/js/logistic/dashboard.js') }}"></script>

		<script src="{{ asset('/public/admin/js/alertify.min.js') }}"></script>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/alertify.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/default.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/semantic.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/bootstrap1.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/alertify.rtl.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/default.rtl.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/semantic.rtl.min.css') }}"/>
		<link rel="stylesheet" href="{{ asset('/public/admin/css/bootstrap.rtl.min.css') }}"/>
				
		<script src="//{{ Request::getHost() }}:{{env('LARAVEL_ECHO_PORT')}}/socket.io/socket.io.js"></script>
		<script src="{{ asset('public/js/echo.js') }}" type="text/javascript"></script>

		<script type="text/javascript">
			function formatDate(date) {
				var mnth = ['Jan', 'Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
				var d = new Date(date),
					month = '' + mnth[Number(d.getMonth())],
					day = '' + d.getDate(),
					year = d.getFullYear();
				if (month.length < 2) month = '0' + month;
				if (day.length < 2) day = '0' + day;
				return [day, month, year].join('-');
			}

			var i = 0;
			var logistic_id =  <?php echo json_encode($logistic_id); ?>;
			console.log('Join Socket Channel :'+' AssignNewOrder'+logistic_id);
			window.Echo.channel('AssignNewOrder'+logistic_id)
			.listen('.AssignOrder', (data) => {
				console.log(data);
				var i = 0;
	
				setTimeout(() => {
					alertify.success("New Order Assign..." + data.OrderDetail.order_number);
				}, 1000);

				i++;
				$("#notification").append('<div class="alert alert-success">'+i+'.'+'data.title'+'</div>');
				var specific_tbody = document.getElementById('admin_dashboardorder_body');

				var NewRow = specific_tbody.insertRow(0);
				var Newcell0 = NewRow.insertCell(0); 
				var Newcell1 = NewRow.insertCell(1); 
				var Newcell2 = NewRow.insertCell(2); 
				var Newcell3 = NewRow.insertCell(3); 
				var Newcell4 = NewRow.insertCell(4); 
				var Newcell5 = NewRow.insertCell(5); 

				var is_paid = (data.OrderDetail.is_paid)?('<i class="ti-truck" style="color: orange;"></i> '):'';
				Newcell0.innerHTML = data.OrderDetail.id;
				Newcell1.innerHTML = data.OrderDetail.delivery_type;
				Newcell2.innerHTML = data.OrderDetail.pickup_address;
				Newcell3.innerHTML = data.OrderDetail.delivery_address;
				Newcell4.innerHTML = data.OrderDetail.order_amount;
				Newcell5.innerHTML = data.OrderDetail.action;
			})
		</script>
	@endif
	@if($page_condition=='page_logistic_pickup')
		<script src="{{ asset('public/admin/js/logistic/pickup.js') }}"></script>
	@endif
	@if($page_condition=='page_logistic_create' || $page_condition=='page_logistic_edit')
		<script src="{{ asset('public/admin/js/logistics.js') }}"></script>
		<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
		<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=drawing&callback=initMap" async defer></script>
		<script>
		var polygonArray;
		var map;
		var boundery = [];
		
		function initMap() {
			const map = new google.maps.Map(document.getElementById("map"), {
				zoom: 7,
				center: { lat: 22.25623, lng: 77.654654 },
			});

			@if(!(isset($user_detail)))
			
			const drawingManager = new google.maps.drawing.DrawingManager({
					drawingMode: google.maps.drawing.OverlayType.POLYGON,
					drawingControl: true,
					drawingControlOptions: {
						position: google.maps.ControlPosition.BOTTOM_LEFT,
						drawingModes: [
							google.maps.drawing.OverlayType.CIRCLE,
							google.maps.drawing.OverlayType.POLYGON,
							google.maps.drawing.OverlayType.RECTANGLE,
						],
					}
			});

			drawingManager.setMap(map);

			google.maps.event.addListener(drawingManager, 'overlaycomplete', getPolygonCoords);
			google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
			google.maps.event.addListener(map, 'click', clearSelection);
			// google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
			
			function getPolygonCoords(event) {
				drawingManager.setDrawingMode(null);
				var newShape = event.overlay;
      			newShape.type = event.type;				
      			newShape.index = boundery.length;				

				switch (event.type) {
					case 'marker':
						console.log('marker not define area')

					case 'circle':
						var coordinates = [];
						var center = event.overlay.getCenter().toUrlValue(6);
						var radius = event.overlay.getRadius();
						console.log(center, event.overlay)
						boundery.push({type: event.type, center: center, radius: radius});

						google.maps.event.addListener(newShape, 'click', function() {
							google.maps.event.addListener(newShape, 'radius_changed', function () {
								console.log('radius changed');
							});
							setSelection(newShape);
						})
						break;

					case 'polygon':
						var coordinates = event.overlay.getPath().getArray();
						console.log(coordinates);
						boundery.push({type: event.type, coordinates: coordinates});
						google.maps.event.addListener(newShape, 'click', function() {
							google.maps.event.addListener(newShape.getPath(), 'set_at', function() {
								console.log("test");
							});

							google.maps.event.addListener(newShape.getPath(), 'insert_at', function() {
								console.log("test");
							});
							setSelection(newShape);
						});
						break;
						
					case 'rectangle':
						var coordinates = [];
						var bounds = event.overlay.getBounds();
						var NE = bounds.getNorthEast();
						var SW = bounds.getSouthWest();
						var NW = new google.maps.LatLng(NE.lat(), SW.lng());
						var SE = new google.maps.LatLng(SW.lat(), NE.lng());
						coordinates.push(NE);
						coordinates.push(SW);
						coordinates.push(NW);
						coordinates.push(SE);
						boundery.push({type: event.type, coordinates: coordinates});

						google.maps.event.addListener(newShape, 'click', function() {
							google.maps.event.addListener(newShape, 'bounds_changed', function() {
								console.log('radius changed');
							});
							setSelection(newShape);
						})
						break;
						
					default:
						console.log('polygon complete.');
						break;
				}
			};
			@endif

			function clearSelection() {
				if (polygonArray) {
					polygonArray.setEditable(false);
					polygonArray = null;
				}
			}

			function deleteSelectedShape() {
				if (polygonArray) {
					polygonArray.setMap(null);
					boundery.splice(polygonArray.index, 1);
				}
			}

			function setSelection(shape) {
				clearSelection();
				polygonArray = shape;
				shape.setEditable(true);
				shape.setDraggable(true);
			}

			
			@isset($user_detail->geo_fencings)
				var geo_fencings = '<?php echo $user_detail->geo_fencings ?>';
				geo_fencings = JSON.parse(geo_fencings);
				geo_fencings.forEach(e=> {
					switch (e.type) {
						case 'circle':
							var coordinatesStr =  (e.coordinates).replace(/[{()}]/g, '');
							var coordinatesArr = coordinatesStr.split(',');
							new google.maps.Circle({
								map, 
								center: { lat: Number(coordinatesArr[0]), lng: Number(coordinatesArr[1]) },
								radius: Number(e.radius),
							});
							break;

						case 'rectangle':
							var coordinatesStr = (e.coordinates).replace(/[{()}]/g, '');
							var coordinatesArr = coordinatesStr.split(',');
							coordinatesArr = coordinatesArr.filter(cords => cords.length > 3);
							var cordsArray = [];

							coordinatesArr.forEach((e, i) => {
								if(i%2 == 0){
									cordsArray.push({'lat': '', 'lng': ''});
									cordsArray[(cordsArray.length)-1]['lat'] = Number(e)
								}else{
									cordsArray[(cordsArray.length)-1]['lng'] = Number(e)
								}
							});

							new google.maps.Polygon({
								map, 
								paths: cordsArray
							})
							break;

						case 'polygon':
							var coordinatesStr = (e.coordinates).replace(/[{()}]/g, '');
							var coordinatesArr = coordinatesStr.split(',');
							coordinatesArr = coordinatesArr.filter(cords => cords.length > 3);
							var cordsArray = [];

							coordinatesArr.forEach((e, i) => {
								if(i%2 == 0){
									cordsArray.push({'lat': '', 'lng': ''});
									cordsArray[(cordsArray.length)-1]['lat'] = Number(e)
								}else{
									cordsArray[(cordsArray.length)-1]['lng'] = Number(e)
								}
							});

							new google.maps.Polygon({
								map, 
								paths: cordsArray
							})
							break;
					
						default:
							break;
					}
				})
			@endisset

			$("#clear-button").click(function() {
				deleteSelectedShape();
			});
		}
		</script>
	@endif
	@if($page_condition=='page_upcomingorders')
		<script src="{{ asset('public/admin/js/logistic/upcomingorders.js') }}"></script>
	@endif
	@if($page_condition=='page_createorder')
		<script src="{{ asset('public/admin/js/createorder.js') }}"></script>
	@endif
	@if($page_condition=='page_acceptorder')
		<script src="{{ asset('public/admin/js/acceptorder.js') }}"></script>
	@endif
	@if($page_condition=='page_order_filter')
		<script src="{{ asset('public/admin/js/order_filter.js') }}"></script>
	@endif
	@if($page_condition=='page_seller_report')
		<script src="{{ asset('public/admin/js/seller_report.js') }}"></script>
	@endif
	@if($page_condition=='page_pharma_delivery_report')
		<script src="{{ asset('public/admin/js/delivery_report.js') }}"></script>
	@endif
	@if($page_condition=='page_pharma_external_delivery_report')
		<script src="{{ asset('public/admin/js/external_delivery_report.js') }}"></script>
	@endif
	@if($page_condition=='page_pharma_order_report')
		<script src="{{ asset('public/admin/js/pharma_order_report.js') }}"></script>
	@endif
	@if($page_condition=='page_deliveryboy')
		<script src="{{ asset('public/admin/js/deliveryboy.js') }}"></script>
	@endif
	@if($page_condition=='page_orders')
		<script src="{{ asset('public/admin/js/orders.js') }}"></script>
	@endif
	@if($page_condition=='page_upcoming')
		<script src="{{ asset('public/admin/js/upcoming.js') }}"></script>
	@endif
	@if($page_condition=='page_user_customer_detail')
		<script src="{{ asset('public/admin/js/customer_detail.js') }}"></script>
	@endif
	@if($page_condition=='page_acceptedorders')
		<script src="{{ asset('public/admin/js/acceptedorders.js') }}"></script>
	@endif
	@if($page_condition=='page_upcomingorder')
		<script src="{{ asset('public/admin/js/upcomingorder.js') }}"></script>
	@endif
	@if($page_condition=='page_adminupcomingorders')
		<script src="{{ asset('public/admin/js/adminupcomingorders.js') }}"></script>
	@endif
	@if($page_condition=='page_adminacceptedorders')
		<script src="{{ asset('public/admin/js/adminacceptedorders.js') }}"></script>
	@endif
	@if($page_condition=='page_adminrejected')
		<script src="{{ asset('public/admin/js/adminrejected.js') }}"></script>
	@endif
	@if($page_condition=='page_adminreturn')
		<script src="{{ asset('public/admin/js/adminreturn.js') }}"></script>
	@endif
	@if($page_condition=='page_admincancelled')
		<script src="{{ asset('public/admin/js/admincancelled.js') }}"></script>
	@endif
	@if($page_condition=='page_admincomplete')
		<script src="{{ asset('public/admin/js/admincomplete.js') }}"></script>
	@endif
	@if($page_condition=='page_acceptedorders_logistic')
		<script src="{{ asset('public/admin/js/logistic/acceptedorders.js') }}"></script>
	@endif
	@if($page_condition=='page_incomplete_logistic')
		<script src="{{ asset('public/admin/js/logistic/incomplete.js') }}"></script>
	@endif
	@if($page_condition=='page_canceled_logistic')
		<script src="{{ asset('public/admin/js/logistic/canceled.js') }}"></script>
	@endif
	@if($page_condition=='page_adminpickup')
		<script src="{{ asset('public/admin/js/adminpickup.js') }}"></script>
	@endif
	@if($page_condition=='page_pickup')
		<script src="{{ asset('public/admin/js/pickup.js') }}"></script>
	@endif
	@if($page_condition=='page_adminoutfordelivery')
		<script src="{{ asset('public/admin/js/adminoutfordelivery.js') }}"></script>
	@endif
	@if($page_condition=='page_outfordelivery')
		<script src="{{ asset('public/admin/js/outfordelivery.js') }}"></script>
	@endif
	@if($page_condition=='page_outfordelivery_logistic')
		<script src="{{ asset('public/admin/js/logistic/outfordelivery.js') }}"></script>
	@endif
	@if($page_condition=='page_incomplete')
		<script src="{{ asset('public/admin/js/incomplete.js') }}"></script>
	@endif
	@if($page_condition=='page_rejected')
		<script src="{{ asset('public/admin/js/rejected.js') }}"></script>
	@endif
	@if($page_condition=='page_canceled')
		<script src="{{ asset('public/admin/js/canceled.js') }}"></script>
	@endif
	@if($page_condition=='page_deliveryboy_logistic')
		<script src="{{ asset('public/admin/js/deliveryboylogistic.js') }}"></script>
	@endif
	@if($page_condition=='page_complete')
		<script src="{{ asset('public/admin/js/complete.js') }}"></script>
	@endif
	@if($page_condition=='page_complete_logistic')
		<script src="{{ asset('public/admin/js/logistic/complete.js') }}"></script>
	@endif
	@if($page_condition=='page_myorder')
		<script src="{{ asset('public/admin/js/myorder.js') }}"></script>
	@endif
	@if($page_condition=='page_allorder')
		<script src="{{ asset('public/admin/js/allorder.js') }}"></script>
	@endif
	@if($page_condition=='page_deliveryreport')
		<script src="{{ asset('public/admin/js/deliveryreport.js') }}"></script>
	@endif
	@if($page_condition=='page_report')
		<script src="{{ asset('public/admin/js/report.js') }}"></script>
	@endif
	@if($page_condition=='page_resolveindex')
		<script src="{{ asset('public/admin/js/resolveindex.js') }}"></script>
	@endif
	@if($page_condition=='page_dashboard')
		<script src="{{ asset('public/admin/js/receivedorders.js') }}"></script>
	@endif
	@if($page_condition=='page_deliverycomplete')
		<script src="{{ asset('public/admin/js/deliverycomplete.js') }}"></script>
	@endif
	@if($page_condition=='page_deliveryincomplete')
		<script src="{{ asset('public/admin/js/deliveryincomplete.js') }}"></script>
	@endif
	@if($page_condition=='page_order_detail')
		<script src="{{ asset('public/admin/js/order_detail.js') }}"></script>
	@endif
	@if($page_condition=='page_searchorders')
		<script src="{{ asset('public/admin/js/searchorders.js') }}"></script>
	@endif
	
	<script src="{{asset('public/admin/plugins/jquery-validator/jquery.validate.js')}}"></script> 
	<script src="{{asset('public/admin/plugins/lightbox/simple-lightbox.js')}}"></script> 
	
	@yield('script')
	
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.counter').counterUp({
				delay: 100,
				time: 1200
			});
			$(".knob").knob();
			
			jQuery('#filter_start_date').datepicker({
				format: "dd/mm/yyyy",
			});
			jQuery('#filter_end_date').datepicker({
				format: "dd/mm/yyyy",
			});
		});
		function opencommonmodal(title, data, button) {
				
			//$('form:first *:input[type!=hidden]:first').focus();
			$('div#modelcommon').modal({backdrop: 'static', keyboard: false}) 
			$('div#modelcommon').find('h3.modal-title').html(title);
			$('div#modelcommon').find('#model_content').html(data);
			if ($('div#modelcommon').find('div#modal_footer').find('.btn').length > 1) {
				$('div#modelcommon').find('div#modal_footer .btn').not('.btn:first').remove();
				$('div#modelcommon').find('div#modal_footer').append(button);
			} else {
				$('div#modelcommon').find('div#modal_footer').append(button);
			}
			if ($(button).hasClass('color_legend')) {
				if ($('div#modelcommon').find('div#modal_footer').find('div.color_legend').length > 0) {
					$('div#modelcommon').find('div#modal_footer').find('div.color_legend').remove();
					$('div#modelcommon').find('div#modal_footer').prepend(button);
				} else {
					$('div#modelcommon').find('div#modal_footer').find('div.color_legend').remove();
				}
			} else {
				$('div#modelcommon').find('div#modal_footer').find('div.color_legend').remove();
			}
			$('#modelcommon').modal('show');
		}
		
		(function() {
			var $gallery = new SimpleLightbox('.gallery a', {});
		})();
		
		function search_all_order(){
			var search_text = $('#all_order_search').val();
			window.location.href = base_url+"/searchorders?search_text="+search_text;
			return false;
		}
	</script>
</body>
</html>