<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'HomeController@index')->name('home');

Route::post('/getstatelist', 'HomeController@statelist')->name('statelist');
Route::post('/getcitylist', 'HomeController@citylist')->name('citylist');

Route::get('/home', 'HomeController@index')->name('home');
Auth::routes();
// Route::get('/pharmacy/login', 'pharmacy\HomeController@index')->name('pharmacy_login');

// Route::get('/logistic/home', 'logistic\HomeController@index')->name('logistic_home');
Route::get('/logistic/login', 'logistic\Auth\LoginController@showLoginForm')->name('logistic_login');
Route::post('/logistic/login', 'logistic\Auth\LoginController@login')->name('logistic_logins');
Route::post('/logistic/logout', 'logistic\Auth\LoginController@logout')->name('logistic_logout');

// Registration Routes...
Route::get('logistic/register', 'logistic\Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('logistic/register', 'logistic\Auth\RegisterController@register');

// Password Reset Routes...
Route::get('logistic/password/reset', 'logistic\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('logistic/password/email', 'logistic\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('logistic/password/reset/{token}', 'logistic\Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('logistic/password/reset', 'logistic\Auth\ResetPasswordController@reset');
Route::get('/profile', array('as' => 'profile', 'uses' => 'ProfileController@index'));
Route::post('/profile', array('as' => 'profile', 'uses' => 'ProfileController@update'));

Route::get('/custom_notification', array('as' => 'custom_notification.create', 'uses' => 'Customnotificationcontroller@create'));
Route::get('/settings', array('as' => 'settings', 'uses' => 'SettingsController@index'));
Route::post('/settings', array('as' => 'settings', 'uses' => 'SettingsController@update'));

Route::get('/changepassword', array('as' => 'changepassword', 'uses' => 'ProfileController@changepassword'));
Route::post('/changepassword', array('as' => 'changepassword', 'uses' => 'ProfileController@updatepassword'));
Route::post('/user/delete_image', 'ProfileController@delete_image');

Route::get('/resetpassword', array('as' => 'resetpassword', 'uses' => 'PasswordController@resetpassword'));
Route::post('/sendotp', array('as' => 'sendotp', 'uses' => 'PasswordController@sendotp'));
Route::get('/otpverification/{slug}', array('as' => 'otpverification', 'uses' => 'PasswordController@otpverification'));
Route::post('/otpverify', array('as' => 'otpverify', 'uses' => 'PasswordController@otpverify'));

Route::get('/passwordreset/{slug}', array('as' => 'passwordreset', 'uses' => 'PasswordController@passwordreset'));
Route::post('/passwordreset', array('as' => 'passwordreset', 'uses' => 'PasswordController@savepassword'));

//Users
Route::get('/user', array('as' => 'user.index', 'uses' => 'UsersController@index'));
Route::post('/getuserlist', array('as' => 'user.getlist', 'uses' => 'UsersController@getlist'));
Route::get('/user/create', array('as' => 'user.create', 'uses' => 'UsersController@create'));
Route::post('/user/create', array('as' => 'user.create', 'uses' => 'UsersController@store'));
Route::get('/user/edit/{id}/{user_type}', array('as' => 'user.edit', 'uses' => 'UsersController@edit'));
Route::post('/user/edit/{id}', array('as' => 'user.edit', 'uses' => 'UsersController@update'));
Route::get('/user/delete/{id}', array('as' => 'user.delete', 'uses' => 'UsersController@delete'));
Route::get('/user/{id}/active/{user_type}', array('as' => 'active.user', 'uses'=>'UsersController@setActivate'));
Route::get('/user/{id}/inactive/{user_type}', array('as' => 'inactive.user', 'uses'=>'UsersController@setInactivate'));
Route::get('/user/detail/{id}', array('as' => 'user.detail', 'uses' => 'UsersController@detail'));
Route::post('/getorderhistory', array('as' => 'orderfilter.getorderhistory', 'uses' => 'UsersController@getorderhistory'));
Route::get('/user/order_details/{id}', array('as' => 'user.order_details', 'uses' => 'UsersController@order_details'));
Route::post('/sendprescriptionotpsms', array('as' => 'user.sendprescriptionotpsms', 'uses' => 'UsersController@sendprescriptionotpsms'));
Route::post('/verifyprescriptionotp', array('as' => 'user.verifyprescriptionotp', 'uses' => 'UsersController@verifyprescriptionotp'));

Route::post('/getstatelist', array('as' => 'user.getstatelist', 'uses' => 'UsersController@getstatelist'));
Route::post('/getcitylist', array('as' => 'user.getcitylist', 'uses' => 'UsersController@getcitylist'));


//Seller
Route::get('/seller', array('as' => 'seller.index', 'uses' => 'SellersController@index'));
Route::post('/getsellerlist', array('as' => 'seller.getlist', 'uses' => 'SellersController@getlist'));
Route::get('/seller/create', array('as' => 'seller.create', 'uses' => 'SellersController@create'));
Route::post('/seller/create', array('as' => 'seller.create', 'uses' => 'SellersController@store'));
Route::get('/seller/edit/{id}', array('as' => 'seller.edit', 'uses' => 'SellersController@edit'));
Route::post('/seller/edit/{id}', array('as' => 'seller.edit', 'uses' => 'SellersController@update'));
Route::get('/seller/delete/{id}', array('as' => 'seller.delete', 'uses' => 'SellersController@delete'));
Route::get('/seller/detail/{id}', array('as' => 'seller.detail', 'uses' => 'SellersController@detail'));
Route::get('/seller/{id}/active', array('as' => 'active.seller', 'uses'=>'SellersController@setActivate'));
Route::get('/seller/{id}/inactive', array('as' => 'inactive.seller', 'uses'=>'SellersController@setInactivate'));
Route::post('/seller/delete_image', 'SellersController@delete_image');

//pharmacy 
Route::get('/pharmacy', array('as' => 'pharmacy.index', 'uses' => 'Pharmacycontroller@index'));
Route::post('/getpharmacylist', array('as' => 'pharmacy.getlist', 'uses' => 'Pharmacycontroller@getlist'));
Route::get('/pharmacy/create', array('as' => 'pharmacy.create', 'uses' => 'Pharmacycontroller@create'));
Route::post('/pharmacy/create', array('as' => 'pharmacy.create', 'uses' => 'Pharmacycontroller@store'));
Route::get('/pharmacy/edit/{id}', array('as' => 'pharmacy.edit', 'uses' => 'Pharmacycontroller@edit'));
Route::post('/pharmacy/edit/{id}', array('as' => 'pharmacy.edit', 'uses' => 'Pharmacycontroller@update'));
Route::get('/pharmacy/detail/{id}', array('as' => 'pharmacy.detail', 'uses' => 'Pharmacycontroller@detail'));
Route::get('/pharmacy/delete/{id}', array('as' => 'pharmacy.delete', 'uses' => 'Pharmacycontroller@delete'));
Route::get('/pharmacy/{id}/active', array('as' => 'active.pharmacy', 'uses'=>'Pharmacycontroller@setActivate'));
Route::get('/pharmacy/{id}/inactive', array('as' => 'inactive.pharmacy', 'uses'=>'Pharmacycontroller@setInactivate'));
Route::post('/pharmacy/delete_image', 'Pharmacycontroller@delete_image');
Route::post('/pharmacy_pan/delete_image_pan', 'Pharmacycontroller@delete_image_pan');
Route::post('/pharmacy_profile/delete_image_profile', 'Pharmacycontroller@delete_image_profile');

// country state city
Route::get('dropdownlist','Pharmacycontroller@index');
Route::get('get-state-list','Pharmacycontroller@getStateList');
Route::get('get-city-list','Pharmacycontroller@getCityList');


//Logistic
Route::get('/logistic', array('as' => 'logistic.index', 'uses' => 'LogisticsController@index'));
Route::post('/getlogisticlist', array('as' => 'logistic.getlist', 'uses' => 'LogisticsController@getlist'));

Route::get('/logistic/create', array('as' => 'logistic.create', 'uses' => 'LogisticsController@create'));
Route::post('/logistic/create', array('as' => 'logistic.create', 'uses' => 'LogisticsController@store'));

Route::get('/logistic/edit/{id}', array('as' => 'logistic.edit', 'uses' => 'LogisticsController@edit'));
Route::post('/logistic/edit/{id}', array('as' => 'logistic.edit', 'uses' => 'LogisticsController@update'));

Route::get('/logistic/delete/{id}', array('as' => 'logistic.delete', 'uses' => 'LogisticsController@delete'));
Route::get('/logistic/detail/{id}', array('as' => 'logistic.detail', 'uses' => 'LogisticsController@detail'));
Route::get('/logistic/{id}/active', array('as' => 'active.logistic', 'uses'=>'LogisticsController@setActivate'));
Route::get('/logistic/{id}/inactive', array('as' => 'inactive.logistic', 'uses'=>'LogisticsController@setInactivate'));
//Delivery boy
Route::get('/deliveryboy', array('as' => 'deliveryboy.index', 'uses' => 'DeliveryboyController@index'));
Route::post('/getdeliveryboylist', array('as' => 'deliveryboy.getlist', 'uses' => 'DeliveryboyController@getlist'));
Route::get('/deliveryboy/create', array('as' => 'deliveryboy.create', 'uses' => 'DeliveryboyController@create'));
Route::post('/deliveryboy/create', array('as' => 'deliveryboy.create', 'uses' => 'DeliveryboyController@store'));
Route::get('/deliveryboy/edit/{id}', array('as' => 'deliveryboy.edit', 'uses' => 'DeliveryboyController@edit'));
Route::post('/deliveryboy/edit/{id}', array('as' => 'deliveryboy.edit', 'uses' => 'DeliveryboyController@update'));
Route::get('/deliveryboy/delete/{id}', array('as' => 'deliveryboy.delete', 'uses' => 'DeliveryboyController@delete'));
Route::get('/deliveryboy/detail/{id}', array('as' => 'deliveryboy.detail', 'uses' => 'DeliveryboyController@detail'));
Route::get('/deliveryboy/{id}/active', array('as' => 'active.deliveryboy', 'uses'=>'DeliveryboyController@setActivate'));
Route::get('/deliveryboy/{id}/inactive', array('as' => 'inactive.deliveryboy', 'uses'=>'DeliveryboyController@setInactivate'));
Route::post('/deliveryboy/delete_image', 'DeliveryboyController@delete_image');

//Order
Route::get('/orders', array('as' => 'orders.index', 'uses' => 'OrdersController@index'));
Route::post('/getorderslist', array('as' => 'orders.getlist', 'uses' => 'OrdersController@getlist'));
Route::get('/orders/accept/{id}', array('as' => 'orders.accept', 'uses' => 'OrdersController@accept'));
Route::post('/orders/reject', array('as' => 'orders.reject', 'uses' => 'OrdersController@reject'));
Route::get('/orders/order_details/{id}', array('as' => 'orders.order_details', 'uses' => 'OrdersController@order_details'));
Route::get('/orders/prescription/{id}', array('as' => 'orders.prescription', 'uses' => 'OrdersController@prescription'));

// Admin Rejected
Route::get('/adminrejected', array('as' => 'adminrejected.index', 'uses' => 'AdminRejectedController@index'));
Route::post('/getadminrejectedlist', array('as' => 'adminrejected.getlist', 'uses' => 'AdminRejectedController@getlist'));

// Admin Return
Route::get('/adminreturn', array('as' => 'adminreturn.index', 'uses' => 'AdminReturnController@index'));
Route::post('/getadminreturnlist', array('as' => 'adminreturn.getlist', 'uses' => 'AdminReturnController@getlist'));

// Admin Cancelled
Route::get('/admincancelled', array('as' => 'admincancelled.index', 'uses' => 'AdminCancelledController@index'));
Route::post('/getadmincancelledlist', array('as' => 'admincancelled.getlist', 'uses' => 'AdminCancelledController@getlist'));

// Admin pickup
Route::get('/adminpickup', array('as' => 'adminpickup.index', 'uses' => 'AdminpickupController@index'));
Route::post('/getadminpickuplist', array('as' => 'adminpickup.getlist', 'uses' => 'AdminpickupController@getlist'));

// pickup
Route::get('/pickup', array('as' => 'pickup.index', 'uses' => 'pickupController@index'));
Route::post('/getpickuplist', array('as' => 'pickup.getlist', 'uses' => 'pickupController@getlist'));

// admin upcoming orders
Route::get('/adminupcomingorders', array('as' => 'adminupcomingorders.index', 'uses' => 'AdminupcomingordersController@index'));
Route::post('/getadminupcomingorderslist', array('as' => 'adminupcomingorders.getlist', 'uses' => 'AdminupcomingordersController@getlist'));

// admin accepted orders
Route::get('/adminacceptedorders', array('as' => 'adminacceptedorders.index', 'uses' => 'AdmincceptedordersController@index'));
Route::post('/getadminacceptedorderslist', array('as' => 'adminacceptedorders.getlist', 'uses' => 'AdmincceptedordersController@getlist'));

// upcoming orders
Route::get('/upcomingorders', array('as' => 'upcomingorders.index', 'uses' => 'UpcomingordersController@index'));
Route::post('/getupcomingorderslist', array('as' => 'upcomingorders.getlist', 'uses' => 'UpcomingordersController@getlist'));

// accepted orders
Route::get('/acceptedorders', array('as' => 'acceptedorders.index', 'uses' => 'AcceptedordersController@index'));
Route::post('/getacceptedorderslist', array('as' => 'acceptedorders.getlist', 'uses' => 'AcceptedordersController@getlist'));
Route::post('/acceptedorders/assign', array('as' => 'acceptedorders.assign', 'uses' => 'AcceptedordersController@assign'));

// admin out for delivery
Route::get('/adminoutfordelivery', array('as' => 'adminoutfordelivery.index', 'uses' => 'AdminoutfordeliveryController@index'));
Route::post('/getadminoutfordeliverylist', array('as' => 'adminoutfordelivery.getlist', 'uses' => 'AdminoutfordeliveryController@getlist'));
// complete order
Route::get('/admincomplete', array('as' => 'admincomplete.index', 'uses' => 'AdmincompleteController@index'));
Route::post('/getadmincompletelist', array('as' => 'admincomplete.getlist', 'uses' => 'AdmincompleteController@getlist'));

// out for delivery
Route::get('/outfordelivery', array('as' => 'outfordelivery.index', 'uses' => 'OutfordeliveryController@index'));
Route::post('/getoutfordeliverylist', array('as' => 'outfordelivery.getlist', 'uses' => 'OutfordeliveryController@getlist'));

// incomplete order
Route::get('/incomplete', array('as' => 'incomplete.index', 'uses' => 'IncompleteController@index'));
Route::post('/getincompletelist', array('as' => 'incomplete.getlist', 'uses' => 'IncompleteController@getlist'));
Route::post('/incomplete/assign', array('as' => 'incomplete.assign', 'uses' => 'IncompleteController@assign'));
Route::post('/incomplete/reject', array('as' => 'incomplete.reject', 'uses' => 'IncompleteController@reject'));

// rejected order
Route::get('/rejected', array('as' => 'rejected.index', 'uses' => 'RejectedController@index'));
Route::post('/getrejectedlist', array('as' => 'rejected.getlist', 'uses' => 'RejectedController@getlist'));

// canceled order
Route::get('/canceled', array('as' => 'canceled.index', 'uses' => 'CanceledController@index'));
Route::post('/getcanceledlist', array('as' => 'canceled.getlist', 'uses' => 'CanceledController@getlist'));

// complete order
Route::get('/complete', array('as' => 'complete.index', 'uses' => 'CompleteController@index'));
Route::post('/getcompletelist', array('as' => 'complete.getlist', 'uses' => 'CompleteController@getlist'));
Route::get('/order_feedback/{id}', array('as' => 'complete.order_feedback', 'uses' => 'CompleteController@order_feedback'));
Route::post('getuserfeedbacklist', 'CompleteController@getuserfeedbacklist');
// accepted order

Route::get('/myorder', array('as' => 'myorder.index', 'uses' => 'MyorderController@index'));
Route::post('/getmyorderlist', array('as' => 'myorder.getlist', 'uses' => 'MyorderController@getlist'));

//Delivery boy for logistic
Route::get('logistic/deliveryboy', array('as' => 'logistic.deliveryboy.index', 'uses' => 'DeliveryboyController@index'));
Route::post('getdeliveryboylogisticlist', array('as' => 'logistic.deliveryboy.getlist', 'uses' => 'DeliveryboyController@getlist'));
Route::get('logistic/deliveryboy/create', array('as' => 'logistic.deliveryboy.create', 'uses' => 'logistic\DeliveryboyController@create'));
Route::post('logistic/deliveryboy/create', array('as' => 'logistic.deliveryboy.create', 'uses' => 'logistic\DeliveryboyController@store'));
Route::get('logistic/deliveryboy/edit/{id}', array('as' => 'logistic.deliveryboy.edit', 'uses' => 'logistic\DeliveryboyController@edit'));
Route::post('logistic/deliveryboy/edit/{id}', array('as' => 'logistic.deliveryboy.edit', 'uses' => 'logistic\DeliveryboyController@update'));
Route::get('logistic/deliveryboy/delete/{id}', array('as' => 'logistic.deliveryboy.delete', 'uses' => 'logistic\DeliveryboyController@delete'));

//logistic complete order
Route::get('logistic/complete', array('as' => 'logistic.complete.index', 'uses' => 'CompleteController@logistic_index'));
Route::post('/getcompletelistlogistic', array('as' => 'logistic.complete.getlist', 'uses' => 'CompleteController@logistic_getlist'));
Route::get('logistic/order_feedback/{id}', array('as' => 'logistic.complete.order_feedback', 'uses' => 'CompleteController@logistic_order_feedback'));
Route::post('getuserfeedbacklist', 'CompleteController@logistic_getuserfeedbacklist');
Route::get('logistic/complete/order_details/{id}', array('as' => 'logistic.complete.order_details', 'uses' => 'CompleteController@logistic_order_details'));


//logistic canceled order
Route::get('logistic/canceled', array('as' => 'logistic.canceled.index', 'uses' => 'CanceledController@logistic_index'));
Route::post('/getcanceledlistlogistic', array('as' => 'logistic.canceled.getlist', 'uses' => 'CanceledController@logistic_getlist'));
Route::get('/logistic/canceled/order_details/{id}', array('as' => 'logistic.canceled.order_details', 'uses' => 'CanceledController@logistic_order_details'));

//logistic out for delivery
Route::get('logistic/outfordelivery', array('as' => 'logistic.outfordelivery.index', 'uses' => 'logistic\OutfordeliveryController@index'));
Route::post('/getoutfordeliverylistlogistic', array('as' => 'logistic.outfordelivery.getlist', 'uses' => 'logistic\OutfordeliveryController@getlist'));
Route::get('/logistic/outfordelivery/order_details/{id}', array('as' => 'logistic.outfordelivery.order_details', 'uses' => 'logistic\OutfordeliveryController@order_details'));

//logistic pickup
Route::get('logistic/pickup', array('as' => 'logistic.pickup.index', 'uses' => 'pickupController@logistic_index'));
Route::post('logistic/getpickuplist', array('as' => 'logistic.pickup.getlist', 'uses' => 'pickupController@logistic_getlist'));
Route::get('/logistic/pickup/order_details/{id}', array('as' => 'logistic.pickup.order_details', 'uses' => 'pickupController@logistic_order_details'));

//logistic upcoming
Route::get('logisticupcoming', array('as' => 'logisticupcoming.index', 'uses' => 'LogisticupcomingController@index'));
Route::get('logisticpickup', array('as' => 'logisticpickup.index', 'uses' => 'LogisticpickupController@index'));

Route::get('logistic/upcoming', array('as' => 'logistic.upcoming.index', 'uses' => 'upcomingController@index'));
Route::post('/getupcominglist', array('as' => 'logistic.upcoming.getlist', 'uses' => 'upcomingController@getlist'));
Route::get('/logistic/upcoming/order_details/{id}', array('as' => 'logistic.upcoming.order_details', 'uses' => 'upcomingController@order_details'));
Route::post('logistic/upcoming/assign', array('as' => 'logistic.upcoming.assign', 'uses' => 'upcomingController@assign'));
Route::post('logistic/upcoming/reject', array('as' => 'logistic.upcoming.reject', 'uses' => 'upcomingController@reject'));

//logistic reports
Route::get('logistic/reports', array('as' => 'logistic.reports.index', 'uses' => 'logistic\ReportController@index'));
Route::post('logistic/getreportslist', array('as' => 'logistic.reports.getlist', 'uses' => 'logistic\ReportController@getlist'));

//logistic accepted orders
Route::get('logistic/acceptedorders', array('as' => 'logistic.acceptedorders.index', 'uses' => 'logistic\AcceptedordersController@index'));
Route::post('/getacceptedorderslistlogistic', array('as' => 'logistic.acceptedorders.getlist', 'uses' => 'logistic\AcceptedordersController@getlist'));
Route::post('/logistic/acceptedorders/assign', array('as' => 'logistic.acceptedorders.assign', 'uses' => 'logistic\AcceptedordersController@assign'));
Route::get('/logistic/acceptedorders/order_details/{id}', array('as' => 'logistic.acceptedorders.order_details', 'uses' => 'logistic\AcceptedordersController@order_details'));

//logistic incomplete order
Route::get('logistic/incomplete', array('as' => 'logistic.incomplete.index', 'uses' => 'IncompleteController@logistic_index'));
Route::post('/getincompletelistlogistic', array('as' => 'logistic.incomplete.getlist', 'uses' => 'IncompleteController@logistic_getlist'));
Route::get('/logistic/incomplete/order_details/{id}', array('as' => 'logistic.incomplete.order_details', 'uses' => 'IncompleteController@logistic_order_details'));
Route::post('logistic/incomplete/assign', array('as' => 'logistic.incomplete.assign', 'uses' => 'IncompleteController@logistic_assign'));
Route::post('logistic/incomplete/reject', array('as' => 'logistic.incomplete.reject', 'uses' => 'IncompleteController@logistic_reject'));

// order report
Route::get('/logistic/order_report', array('as' => 'logistic.order_report.index', 'uses' => 'OrderReportController@logistic_index'));
Route::post('/logistic/getreportorderlist', array('as' => 'logistic.order_report.getreportorderlist', 'uses' => 'OrderReportController@logistic_getreportorderlist'));
Route::post('/logistic/getreportordertotal', array('as' => 'logistic.order_report.getreportordertotal', 'uses' => 'OrderReportController@logistic_getreportordertotal'));
Route::post('/logistic/payment_create', array('as' => 'logistic.order_report.payment_create', 'uses' => 'OrderReportController@logistic_payment_create'));

// all order
Route::get('/allorder', array('as' => 'allorder.index', 'uses' => 'AllorderController@index'));
Route::post('/getallorderlist', array('as' => 'allorder.getlist', 'uses' => 'AllorderController@getlist'));

// delivery report
Route::get('/deliveryreport', array('as' => 'deliveryreport.index', 'uses' => 'DeliveryreportController@index'));
Route::post('/getdeliveryreportlist', array('as' => 'deliveryreport.getlist', 'uses' => 'DeliveryreportController@getlist'));

// order report
Route::get('/order_report', array('as' => 'order_report.index', 'uses' => 'OrderReportController@index'));
Route::post('/getreportorderlist', array('as' => 'order_report.getreportorderlist', 'uses' => 'OrderReportController@getreportorderlist'));
Route::post('/getreportordertotal', array('as' => 'order_report.getreportordertotal', 'uses' => 'OrderReportController@getreportordertotal'));
Route::post('/payment_create', array('as' => 'order_report.payment_create', 'uses' => 'OrderReportController@payment_create'));

//Order Filter
Route::get('/order_filter', array('as' => 'order_filter.index', 'uses' => 'OrderfilterController@index'));
Route::post('/getorderfilter', array('as' => 'orderfilter.getorderfilter', 'uses' => 'OrderfilterController@getorderfilter'));
Route::post('/getpharmacywiseseller', array('as' => 'orderfilter.getpharmacywiseseller', 'uses' => 'OrderfilterController@GetPharmacyWiseSeller'));
Route::post('/getlogisticwiseseller', array('as' => 'orderfilter.getlogisticwiseseller', 'uses' => 'OrderfilterController@GetLogisticWiseSeller'));

//Pharmacy Seller Report
Route::get('/seller_report', array('as' => 'seller_report.index', 'uses' => 'SellerReportController@index'));
Route::post('/getsellerreport', array('as' => 'sellerreport.getsellerreport', 'uses' => 'SellerReportController@getsellerreport'));

//Pharmacy Delivery Report
Route::get('/pharma_delivery_report', array('as' => 'pharma_delivery_report.index', 'uses' => 'PharmaDeliveryReportController@index'));
Route::post('/getDeliveryReport', array('as' => 'pharma_delivery_report.getDeliveryReport', 'uses' => 'PharmaDeliveryReportController@getDeliveryReport'));

//Pharmacy External Delivery Report
Route::get('/pharma_external_delivery_report_test', array('as' => 'pharma_external_delivery_test.index', 'uses' => 'PharmaExternalDeliveryReportTestController@index'));

Route::get('/pharma_external_delivery_report_test_direct', array('as' => 'pharma_external_delivery_report_test_direct.index', 'uses' => 'PharmaExternalDeliveryReportTestDirectController@index'));

Route::get('/pharma_external_delivery_report', array('as' => 'pharma_external_delivery_report.index', 'uses' => 'PharmaExternalDeliveryReportController@index'));
Route::post('/getExternalDeliveryReport', array('as' => 'pharma_external_delivery_report.getExternalDeliveryReport', 'uses' => 'PharmaExternalDeliveryReportController@getExternalDeliveryReport'));

//Pharmacy Order Report
Route::get('/pharma_order_report', array('as' => 'pharma_order_report.index', 'uses' => 'PharmaOrderReportController@index'));
Route::post('/getPharmaOrderReport', array('as' => 'pharma_order_report.getPharmaOrderReport', 'uses' => 'PharmaOrderReportController@getPharmaOrderReport'));
Route::get('/getPharmaOrderReport', array('as' => 'pharma_order_report.getPharmaOrderReport', 'uses' => 'PharmaOrderReportController@getPharmaOrderReport'));

//Live Order
Route::get('/live_order', array('as' => 'live_order.index', 'uses' => 'LiveAcceptedController@index'));

// voucher
Route::get('/voucher', array('as' => 'voucher.index', 'uses' => 'VoucherController@index'));
Route::get('/voucher/detail/{id}', array('as' => 'voucher.voucher_detail', 'uses' => 'VoucherController@voucher_detail'));
// Route::get('/voucher_report', array('as' => 'voucher_report.index', 'uses' => 'VoucherController@index'));
// Route::get('/voucher_report', array('as' => 'voucher_report.index', 'uses' => 'VoucherController@index'));
Route::post('/getvoucherlist', array('as' => 'voucher_report.getvoucherlist', 'uses' => 'VoucherController@getvoucherlist'));
Route::post('/voucher_confirmed', array('as' => 'voucher_report.voucher_confirmed', 'uses' => 'VoucherController@voucher_confirmed'));
Route::post('/get_voucher_orderlist', array('as' => 'voucher_report.get_voucher_orderlist', 'uses' => 'VoucherController@get_voucher_orderlist'));

Route::get('/voucher_history', array('as' => 'voucher.history', 'uses' => 'VoucherController@history'));
Route::post('/getvoucherhistorylist', array('as' => 'voucher_report.getvoucherhistorylist', 'uses' => 'VoucherController@getvoucherhistorylist'));


// received orders
Route::get('/receivedorders', array('as' => 'receivedorders.index', 'uses' => 'ReceivedordersController@index'));
Route::post('/getreceivedorderslist', array('as' => 'receivedorders.getlist', 'uses' => 'ReceivedordersController@getlist'));
Route::get('/receivedorders/delivered/{id}', array('as' => 'receivedorders.delivered', 'uses' => 'ReceivedordersController@delivered'));
Route::post('/receivedorders/reject', array('as' => 'receivedorders.reject', 'uses' => 'ReceivedordersController@reject'));
Route::get('/receivedorders/invoice/{id}', array('as' => 'receivedorders.invoice', 'uses' => 'ReceivedordersController@invoice'));

// complete order
Route::get('/deliverycomplete', array('as' => 'deliverycomplete.index', 'uses' => 'DeliverycompleteController@index'));
Route::post('/getdeliverycompletelist', array('as' => 'deliverycomplete.getlist', 'uses' => 'DeliverycompleteController@getlist'));
Route::get('/deliverycomplete/invoice/{id}', array('as' => 'deliverycomplete.invoice', 'uses' => 'DeliverycompleteController@invoice'));

// incomplete order
Route::get('/deliveryincomplete', array('as' => 'deliveryincomplete.index', 'uses' => 'DeliveryincompleteController@index'));
Route::post('/getdeliveryincompletelist', array('as' => 'deliveryincomplete.getlist', 'uses' => 'DeliveryincompleteController@getlist'));

// Pill shape
Route::get('/pill_shape', 'PillShapeController@index');
Route::post('pill_shape_add', 'PillShapeController@save');
Route::get('getpillshapelist', 'PillShapeController@list');
Route::post('pill_shape/delete/{id}', 'PillShapeController@delete');
Route::post('pill_shape/load_form/{id}', 'PillShapeController@loadForm');
Route::post('/pill_shape/delete_image', 'PillShapeController@delete_image');

// Pill color
Route::get('/pill_color', 'PillColorController@index');
Route::post('pill_color_add', 'PillColorController@save');
Route::get('pill_color_list', 'PillColorController@list');
Route::post('pill_color/delete/{id}', 'PillColorController@delete');
Route::post('pill_color/load_form/{id}', 'PillColorController@loadForm');


// Broad Cast Notification
Route::get('/broad_cast_notification', 'BroadCastNotificationController@index');
Route::post('broad_cast_notification_add', 'BroadCastNotificationController@save');
Route::get('broad_cast_notification_list', 'BroadCastNotificationController@list');
Route::get('broad_cast_notification/load_form/', 'BroadCastNotificationController@loadForm');

// Slider
Route::get('/slider', 'SliderController@index');
Route::post('slider_add', 'SliderController@save');
Route::get('slider_list', 'SliderController@list');
Route::post('slider/delete/{id}', 'SliderController@delete');
Route::get('slider/load_form/', 'SliderController@loadForm');

// disease
Route::get('/disease', 'DiseaseController@index');
Route::post('disease_add', 'DiseaseController@save');
Route::get('disease_list', 'DiseaseController@list');
Route::post('disease/delete/{id}', 'DiseaseController@delete');
Route::post('disease/load_form/{id}', 'DiseaseController@loadForm');

// Allergy
Route::get('/allergy', 'AllergyController@index');
Route::post('allergy_add', 'AllergyController@save');
Route::get('allergy_list', 'AllergyController@list');
Route::post('allergy/delete/{id}', 'AllergyController@delete');
Route::post('allergy/load_form/{id}', 'AllergyController@loadForm');

// Report
Route::get('/report', array('as' => 'report.index', 'uses' => 'ReportController@index'));
Route::post('/report_list', array('as' => 'report_list.getlist', 'uses' => 'ReportController@list'));
Route::post('report_resolve', 'ReportController@resolve');
Route::get('/reportresolve', array('as' => 'reportresolve.index', 'uses' => 'ReportController@resolveindex'));
Route::post('/reportresolve_list', array('as' => 'reportresolve_list.getlist', 'uses' => 'ReportController@resolvelist'));

//search order
Route::get('/searchorders', array('as' => 'searchorders.index', 'uses' => 'SearchordersController@index'));
Route::post('/getsearchorderslist', array('as' => 'searchorders.getlist', 'uses' => 'SearchordersController@getlist'));

//create order script
Route::get('/createorder/create', array('as' => 'createorder.create', 'uses' => 'Createordercontroller@create'));
Route::post('/createorder/create', array('as' => 'createorder.create', 'uses' => 'Createordercontroller@store'));
Route::get('get-prescription-list','Createordercontroller@getprescriptionList');
Route::get('get-address-list','Createordercontroller@getaddressList');

//accept and reject order script
Route::get('/acceptorder/accept', array('as' => 'acceptorder.create', 'uses' => 'Script_Acceptordercontroller@create'));
Route::get('get-order-list','Script_Acceptordercontroller@getorderList');
Route::get('get-customer-list','Script_Acceptordercontroller@getcustomerList');
Route::get('get-seller-list','Script_Acceptordercontroller@getsellerList');

// packages
Route::get('/packages', 'PackagesController@index');
Route::post('package_add', 'PackagesController@save');
Route::get('package_list', 'PackagesController@list');
Route::post('package/delete/{id}', 'PackagesController@delete');
Route::post('package/load_form/{id}', 'PackagesController@loadForm');
Route::get('/packages/payment/{package_id}', 'PackagesController@payment');
Route::get('/packages/success', 'PackagesController@success');
Route::get('/packages/fail', 'PackagesController@fail');

// Deposit
Route::get('/deposit', 'DepositController@index');
Route::get('deposit_list', 'DepositController@list');
Route::post('deposit_add', 'DepositController@save');
Route::post('deposit/load_form/{id}', 'DepositController@loadForm');

// Current Deposit
Route::get('/currentdeposit', 'CurrentdepositController@index');
Route::get('currentdeposit_list', 'CurrentdepositController@list');
Route::get('getlogisticdepositeamount/{id}', 'CurrentdepositController@getlogisticdepositeamount');

// Delivery Charges
Route::get('/deliverycharges', array('as' => 'deliverycharges.index', 'uses' => 'DeliveryChargesController@index'));
Route::post('/getdeliverychargesorderlist', array('as' => 'deliverycharges.getdeliverychargesorderlist', 'uses' => 'DeliveryChargesController@getdeliverychargesorderlist'));
Route::post('/deliverycharges_payment_create', array('as' => 'deliverycharges.deliverycharges_payment_create', 'uses' => 'DeliveryChargesController@deliverycharges_payment_create'));
Route::get('getlogisticpendingamount/{id}', 'DeliveryChargesController@getlogisticpendingamount');

// Onboarding Request
Route::get('/onboardingrequest', 'OnboardingrequestController@index');
Route::get('onboardingrequest_list', 'OnboardingrequestController@list');
Route::get('/onboardingrequestapprove/{id}', 'OnboardingrequestController@approve');

//Terms and condition
Route::get('/termscondition', array('as' => 'termscondition.index', 'uses' => 'TermsconditionController@index'));
Route::post('/gettermsconditionlist', array('as' => 'termscondition.getlist', 'uses' => 'TermsconditionController@getlist'));
Route::get('/termscondition/create', array('as' => 'termscondition.create', 'uses' => 'TermsconditionController@create'));
Route::post('/termscondition/create', array('as' => 'termscondition.create', 'uses' => 'TermsconditionController@store'));
Route::get('/termscondition/edit/{id}', array('as' => 'termscondition.edit', 'uses' => 'TermsconditionController@edit'));
Route::post('/termscondition/edit/{id}', array('as' => 'termscondition.edit', 'uses' => 'TermsconditionController@update'));
Route::get('/termscondition/delete/{id}', array('as' => 'termscondition.delete', 'uses' => 'TermsconditionController@delete'));

// pharmacy rejected order
Route::get('/pharmacyrejected', array('as' => 'pharmacyrejected.index', 'uses' => 'PharmacyrejectedController@index'));
Route::post('/getpharmacyrejectedlist', array('as' => 'pharmacyrejected.getlist', 'uses' => 'PharmacyrejectedController@getlist'));

// packages history
Route::get('/packageshistory', 'PackageshistoryController@index');
Route::get('packageshistory_list', 'PackageshistoryController@list');