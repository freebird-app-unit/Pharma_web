<?php

namespace App\Http\Controllers;

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
use App\DeliveryboyModel\new_orders;
use App\DeliveryboyModel\new_pharmacies;
use App\SellerModel\new_users;
use App\DeliveryboyModel\new_order_images;
use App\DeliveryboyModel\new_order_history;
use App\new_delivery_charges;
use Validator;
use Storage;
use Image;
use File;
use DB;

use Paykun\Checkout\Payment;

class PaykunController extends Controller
{
    public function create_transaction(Request $request)
    {
        dd(1);
        $order_id = $request->order_id;
        $order_detail = new_orders::where('id', $order_id)->first();

        if(isset($order_detail)){
            $customer_detail = new_users::where('id', $order_detail->customer_id)->first();
            $pharmacy_detail = new_pharmacies::where('id', $order_detail->pharmacy_id)->first();
            $obj = new Payment($_ENV['PAYKUN_MERCHANTID'], $_ENV['PAYKUN_ACCESSTOKEN'], $_ENV['PAYKUN_ENCRYPTIONKEY'], $_ENV['PAYKUN_LIVE']);
            $payment_success = "payment_success";
            $payment_fail = "payment_fail";
    
            $obj->initOrder("ORDER_TEST123", "PHAR_132145698798", "53.48", $payment_success, $payment_fail);
            $obj->addCustomer('rushabh', 'rushabhjain@gmail.com', '9773234230');
            $obj->addShippingAddress('India', 'Gujarat', 'Rajkot', '360001', '150 ring road, sheetal park');
            $obj->addBillingAddress('India', 'Gujarat', 'Rajkot', '360001', '150 ring road, sheetal park');
            echo $obj->submit();
        }
    }
}