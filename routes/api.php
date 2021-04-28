<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['namespace' => 'Api'], function () {
	Route::get('/event', 'EventController@event');
	Route::post('/registration_otp', 'LoginController@registration_otp');
	Route::post('/createaccount', 'LoginController@createaccount');
	Route::post('/login', 'LoginController@index');
	Route::post('/forgotpassword', 'LoginController@forgotpassword');
	Route::post('/resetpassword', 'LoginController@resetpassword');
	Route::post('/verify_otp', 'LoginController@verify_otp');
	Route::post('/resend_otp', 'LoginController@resend_otp');
	Route::post('/change_password', 'LoginController@change_password');
	Route::post('/changepassword', 'LoginController@changepassword');
	Route::post('/profile', 'ProfileController@index');
	Route::post('/profile_verify_otp', 'ProfileController@profile_verify_otp');
	Route::post('/editprofile', 'ProfileController@editprofile');
	Route::post('/privacypolicy', 'PagesController@privacypolicy');
	Route::post('/manageaddress', 'AddressController@manageaddress');
	Route::post('/createaddress', 'AddressController@createaddress');
	Route::post('/editaddress', 'AddressController@editaddress');
	Route::post('/deleteaddress', 'AddressController@deleteaddress');
	Route::post('/notification', 'NotificationController@notification');
	Route::post('/clearallnotification', 'NotificationController@clearallnotification');
	Route::post('/dashboardimageslider', 'ImagesliderController@dashboardimageslider');
	Route::post('/articleslist', 'ArticlesController@articleslist');
	Route::post('/articledetail', 'ArticlesController@articledetail');
	Route::post('/cancelreasonlist', 'CancelreasonController@cancelreasonlist');
	Route::post('/orderfeedback', 'OrderfeedbackController@orderfeedback');
	Route::post('/reorder', 'OrderController@reorder');
	Route::post('/cancelorderlist', 'OrderController@cancelorderlist');
	Route::post('/pharmacylist', 'PharmacyController@pharmacylist');
	Route::post('/new_pharmacylist', 'New_pharmacycontroller@pharmacylist');
	Route::post('/addrecord', 'OrderController@addrecord');
	Route::post('/createorder', 'OrderController@createorder');
	Route::post('/cancelorder', 'OrderController@cancelorder');
	Route::post('/savedarticleslist', 'ArticlesController@savedarticleslist');
	Route::post('/mycartlist', 'OrderController@mycartlist');
	Route::post('/mycartdetail', 'OrderController@mycartdetail');
	Route::post('/notification_user', 'OrderController@notification_user');
	Route::post('/new_notification_user', 'New_orderController@notification_user');
	Route::post('/create_pillreminder', 'PillreminderController@create_pillreminder');
	Route::post('/edit_pillreminder', 'PillreminderController@edit_pillreminder');
	Route::post('/pillreminder', 'PillreminderController@pillreminder');
	Route::post('/pillreminderdetail', 'PillreminderController@pillreminderdetail');
	Route::post('/removedose', 'PillreminderController@removedose');
	Route::post('/misseddoselist', 'PillreminderController@misseddoselist');
	Route::post('/takennow', 'PillreminderController@takennow');

	
	Route::post('/healthsummaryallergies', 'AllergyController@healthsummaryallergies');
	Route::post('/new_healthsummaryallergies', 'New_allergycontroller@healthsummaryallergies');
	Route::post('/createallergy', 'AllergyController@createallergy');
	Route::post('/helthsummarytimeline', 'HelthsummarytimelineController@helthsummarytimeline');
	Route::post('/new_helthsummarytimeline', 'New_helthsummarytimelinecontroller@helthsummarytimeline');
	Route::post('/helthsummarytimelinedetail', 'HelthsummarytimelineController@helthsummarytimelinedetail');
	Route::post('/add_disease', 'HelthsummarytimelineController@add_disease');
	Route::post('/edit_disease', 'HelthsummarytimelineController@edit_disease');
	Route::get('/get_pill_shape', 'PillreminderController@get_pill_shape');
	Route::get('/get_pill_color', 'PillreminderController@get_pill_color');
	Route::post('/get_family_members', 'HelthsummarytimelineController@get_family_members');
	Route::get('/get_disease', 'HelthsummarytimelineController@get_disease');
	Route::post('/prescription_list', 'PrescriptionController@prescription_list');
	Route::post('/prescription_list_imagedata', 'PrescriptionController@prescription_list_imagedata');
	Route::post('/new_prescription_list', 'New_prescriptioncontroller@prescription_list');
	Route::post('/save_prescription', 'PrescriptionController@save_prescription');
	Route::post('/save_prescription_imagedata', 'PrescriptionController@save_prescription_imagedata');
	Route::post('/delete_prescription', 'PrescriptionController@delete_prescription');
	Route::post('/delete_prescription_imagedata', 'PrescriptionController@delete_prescription_imagedata');
	Route::post('/delete_disease_report', 'HelthsummarytimelineController@delete_disease_report');
	Route::post('/delete_presscription_report', 'HelthsummarytimelineController@delete_presscription_report');
	Route::post('/check_valid_user_code', 'HelthsummarytimelineController@check_valid_user_code');
	Route::post('/verify_family_member_otp', 'HelthsummarytimelineController@verify_family_member_otp');
	Route::post('/disease_list', 'HelthsummarytimelineController@disease_list');

	Route::get('/country', 'NewAddressController@country');
	Route::post('/state', 'NewAddressController@state');
	Route::post('/city', 'NewAddressController@city');
	Route::post('/add_address', 'NewAddressController@add_address');
	Route::post('/number_list', 'NewAddressController@number_list');
	Route::post('/get_address', 'NewAddressController@get_address');
	Route::post('/edit_address', 'NewAddressController@edit_address');
	Route::post('/delete_address', 'NewAddressController@delete_address');

	Route::post('/report', 'reportController@index');
	Route::post('/contact_report', 'reportController@contact_report');
	Route::post('/delivery_charges', 'OrderController@delivery_charges');

	Route::post('/profile_otp', 'ProfileController@profile_otp');
	Route::post('/logout', 'LoginController@logout');

	Route::get('/create_transaction/{order_id}', 'PaykunController@create_transaction');
	Route::get('/payment_succes', 'PaykunController@payment_success');
	Route::get('/payment_fail', 'PaykunController@payment_fail');
	Route::post('/elt/callback', 'CallbackController@callback');
	Route::get('/callback_get/{pass_data?}', 'CallbackController@callback_get');
	Route::post('/update_response_data', 'UpdateResponseController@update_response_data');
	Route::post('/update_transaction', 'OrderController@update_transaction');
	Route::post('/get_order_status', 'OrderController@get_order_status');
	Route::post('/webhook/receive_order', 'PaykunController@receive_order');
	
	Route::get('/webhook/notify', 'WebhooknotifyController@webhooknotify');
	Route::post('/checkversion', 'CheckversionController@index');
	Route::post('/add_records', 'OrderController@add_records');	
});


Route::group(['namespace' => 'Api\Seller'], function () {
	Route::post('/sellerlogin', 'LoginController@index');
	Route::post('/sellerforgotpassword', 'LoginController@forgotpassword');
	Route::post('/sellerresetpassword', 'LoginController@resetpassword');
	Route::post('/sellerverify_otp', 'LoginController@verify_otp');
	Route::post('/sellerchange_password', 'LoginController@change_password');
	Route::post('/sellerprofile', 'ProfileController@index');
	Route::post('/sellereditprofile', 'ProfileController@editprofile');
	Route::post('/sellerlogout', 'LoginController@logout');

	Route::post('/checking_by', 'AcceptorderController@checking_by');
	Route::post('/order_list', 'AcceptorderController@order_list');
	Route::post('/acceptorderlist', 'AcceptorderController@accept_order_list');
	Route::post('/deliveryboylist', 'AcceptorderController@deliveryboy_list');
	Route::post('/outoforderlist', 'AcceptorderController@outof_order_list');
	Route::post('/rejectorderlist', 'AcceptorderController@reject_order_list');
	Route::post('/cancelorderlist_seller', 'AcceptorderController@cancel_order_list');
	Route::post('/completeorderlist', 'AcceptorderController@complete_order_list');
	Route::post('/order_details', 'AcceptorderController@order_details');
	Route::post('/upcoming_order_details', 'AcceptorderController@upcoming_order_details');
	Route::post('/complete_order_details', 'AcceptorderController@complete_order_details');
	Route::post('/order_detail', 'AcceptorderController@order_detail');
	Route::post('/accept_upcoming', 'AcceptorderController@accept_upcoming');
	Route::post('/reject_upcoming', 'AcceptorderController@reject_upcoming');
	Route::post('/invoice', 'AcceptorderController@invoice');
	Route::post('/callhistory', 'AcceptorderController@call_history');
	Route::post('/assign_order', 'AcceptorderController@assign_order');
	Route::post('/reject_order', 'AcceptorderController@reject_order');
	Route::get('/reasonlist', 'AcceptorderController@reason_list');
	Route::post('/add_time', 'AcceptorderController@add_time');
	Route::post('/return_order_list', 'AcceptorderController@return_order_list');
	Route::post('/delivery_charges_list', 'AcceptorderController@delivery_charges_list');
	Route::post('/set_delivery_charges', 'AcceptorderController@set_delivery_charges');
	Route::post('/external_deliveryboy_list', 'AcceptorderController@external_deliveryboy_list');
	Route::post('/logistic_list', 'AcceptorderController@logistic_list');
	Route::post('/cancel_order', 'AcceptorderController@cancel_order');
	Route::post('/return_confirm', 'AcceptorderController@return_confirm');
	Route::post('/notification_seller', 'AcceptorderController@notification_seller');
	Route::post('/clearallnotification_seller', 'AcceptorderController@clearallnotification');
});


Route::group(['namespace' => 'Api\deliveryboy'], function () {
	Route::post('/deliveryboylogin', 'LoginController@index');
	Route::post('/deliveryboylogout', 'LoginController@logout');
	Route::post('/deliveryboyforgotpassword', 'LoginController@forgotpassword');
	Route::post('/deliveryboyresetpassword', 'LoginController@resetpassword');
	Route::post('/deliveryboyverify_otp', 'LoginController@verify_otp');
	Route::post('/deliveryboyprofile', 'ProfileController@index');
	Route::post('/deliveryboyeditprofile', 'ProfileController@editprofile');

	Route::post('/upcomingorderlist', 'UpcomingOrderController@upcomingorderlist');
	Route::post('/upcomingorderdetail', 'UpcomingOrderController@upcomingorderdetail');
	Route::post('/orderaccept', 'UpcomingOrderController@orderaccept');
	Route::post('/orderreject', 'UpcomingOrderController@orderreject');

	Route::post('/pickuporderlist', 'PickupOrderController@pickuporderlist');
	Route::post('/pickuporderdetail', 'PickupOrderController@pickuporderdetail');
	Route::post('/orderpickup', 'PickupOrderController@orderpickup');

	Route::post('/deliveryorderlist', 'DeliveryOrderController@deliveryorderlist');
	Route::post('/deliveryorderdetail', 'DeliveryOrderController@deliveryorderdetail');
	Route::post('/orderdelivered', 'DeliveryOrderController@orderdelivered');

	Route::post('/getincompletereasons', 'DeliveryOrderController@getincompletereasons');
	Route::post('/orderreturn', 'DeliveryOrderController@orderreturn');

	Route::post('/completeorderdetail', 'CompleteOrderController@completeorderdetail');
	Route::post('/orderhistorylist', 'OrderHistoryController@orderhistorylist');
	Route::post('/pharmacy_list', 'OrderHistoryController@pharmacy_list');

	Route::post('/orderdetail', 'OrderHistoryController@orderdetail');
	Route::post('/set_availability', 'LoginController@set_availability');
	Route::post('/notification_deliveryboy', 'DeliveryOrderController@notification_deliveryboy');
	Route::post('/clearallnotification_deliveryboy', 'DeliveryOrderController@clearallnotification');
});