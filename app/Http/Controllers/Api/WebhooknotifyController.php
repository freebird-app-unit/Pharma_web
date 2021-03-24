<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\Orders;
use App\Address;
use App\delivery_charges;
use App\Incompletereason;
use App\SellerModel\invoice;

use App\DeliveryboyModel\new_pharma_logistic_employee;
use App\new_orders;
use App\new_pharmacies;
use App\new_users;
use App\new_address;
use App\DeliveryboyModel\new_order_images;
use App\DeliveryboyModel\new_order_history;
use App\new_delivery_charges;

use Validator;
use Storage;
use Image;
use File;
use DB;
use App\Prescription;
use App\Events\CreateNewOrder;
use Paykun\Checkout\Payment;
use App\Paymenttransaction;

class WebhooknotifyController extends Controller
{
    public function webhooknotify()
    {
        /* echo '<pre>';
		print_r($_REQUEST);
		echo '===============================';
		print_r($_POST);exit; */
		$Paymenttransaction = new Paymenttransaction;
		$Paymenttransaction->payment_id = $_REQUEST['payment_id'];
		$Paymenttransaction->merchant_email = $_REQUEST['merchant_email'];
		$Paymenttransaction->merchant_id = $_REQUEST['merchant_id'];
		$Paymenttransaction->status = $_REQUEST['status'];
		$Paymenttransaction->status_flag = $_REQUEST['status_flag'];
		$Paymenttransaction->payment_mode = $_REQUEST['payment_mode'];
		$Paymenttransaction->order_id = $_REQUEST['order_id'];
		$Paymenttransaction->product_name = $_REQUEST['product_name'];
		$Paymenttransaction->gross_amount = $_REQUEST['gross_amount'];
		$Paymenttransaction->gateway_fee = $_REQUEST['gateway_fee'];
		$Paymenttransaction->tax = $_REQUEST['tax'];
		$Paymenttransaction->transaction_date = date('Y-m-d',strtotime($_REQUEST['transaction_date']));
		$Paymenttransaction->signature = $_REQUEST['signature'];
		$Paymenttransaction->created_at = date('Y-m-d H:i:s');
		$Paymenttransaction->updated_at = date('Y-m-d H:i:s');
		$Paymenttransaction->save();
    }
}