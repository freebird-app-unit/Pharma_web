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
use App\notification_seller;
use Paykun\Checkout\Payment;
use Helper;

class PaykunController extends Controller
{
    public function create_transaction(Request $request)
    {
        $order_id = $request->order_id;
        $order_detail = new_orders::where('id', $order_id)->first();
        $delivery_charge_detail = new_delivery_charges::where('id', $order_detail->delivery_charges_id)->first();

        if(isset($order_detail) && $delivery_charge_detail->delivery_price > 0){
            $customer_detail = new_users::where('id', $order_detail->customer_id)->first();
            $pharmacy_detail = new_pharmacies::where('id', $order_detail->pharmacy_id)->first();
            $address_detail = new_address::where('id', $order_detail->address_id)->first();
            $delivery_charge_detail = new_delivery_charges::where('id', $order_detail->delivery_charges_id)->first();
            $obj = new Payment($_ENV['PAYKUN_MERCHANTID'], $_ENV['PAYKUN_ACCESSTOKEN'], $_ENV['PAYKUN_ENCRYPTIONKEY'], false);
            $payment_success = "http://167.172.146.209/pharma/api/payment_success";
            $payment_fail = "http://167.172.146.209/pharma/api/payment_fail";

            $transaction_ref = $this->generate_unique_number();
            $obj->initOrder($transaction_ref, $order_detail->id, $delivery_charge_detail->delivery_price, $payment_success, $payment_fail);
            $obj->addCustomer($customer_detail->name, $customer_detail->email, $customer_detail->mobile_number);
            $obj->addShippingAddress('India', 'Gujarat', $address_detail->city, $address_detail->pincode, $address_detail->address);
            $obj->addBillingAddress('India', 'Gujarat', $pharmacy_detail->city, $pharmacy_detail->pincode, $pharmacy_detail->address);
            echo $obj->submit();
        } else {
            return json_encode(['success'=> true, 'detail'=>'Something get wrong.']);
        }
    }

    public function payment_success()
    {
        $obj = new Payment($_ENV['PAYKUN_MERCHANTID'], $_ENV['PAYKUN_ACCESSTOKEN'], $_ENV['PAYKUN_ENCRYPTIONKEY'], false);
        $transactionData = $obj->getTransactionInfo($_REQUEST['payment-id']);

        $order_number = $transactionData['data']['transaction']['order']['order_id'];
        $order_id = $transactionData['data']['transaction']['order']['product_name'];
        new_orders::where('id', $order_id)->update(array('order_status' => 'new'));
        //Notification When Order is Paid
        $orde_data = new_orders::where('id',$order_id)->first();
        $pharmacy_data = new_pharmacies::where('id',$orde_data->pharmacy_id)->first();
        $user = new_pharma_logistic_employee::where('pharma_logistic_id',$pharmacy_data->id)->first();
        $ids = array();
        $order_data = new_orders::where('id',$order_id)->first();
        $t_data = new_users::where('id',$order_data->customer_id)->first();
        $sellerdetails = new_pharma_logistic_employee::where(['pharma_logistic_id'=>$order_data->pharmacy_id,'user_type'=>'seller'])->get();
                        
        $message = ' Order Create '. $order_data->order_number.' User Name : '. $t_data->name;
        $seller_id = [];
        foreach ($sellerdetails as $sellerdetail) {
            if($sellerdetail->fcm_token!=''){
                $ids[] = $sellerdetail->fcm_token;
                $seller_id[] = $sellerdetail->id;
            }
            $msg = array
                            (
                'body'   => ' Order Create '. $order_data->order_number.' User Name : '. $t_data->name ,
                'title'     => 'Order Created'
            );
            if(count($ids)>0){
                $fields = array(
                    'to' => $sellerdetail->fcm_token,
                    'notification' => $msg
                );
                                // $this->sendPushNotificationSeller($fields);
            }
            $notification = new notification_seller();
            $notification->user_id=$sellerdetail->id;
            $notification->order_id=$order_data->id;
            $notification->order_status=$order_data->order_status;
            $notification->subtitle=$msg['body'];
            $notification->title=$msg['title'];
            $notification->created_at=date('Y-m-d H:i:s');
            $notification->save();
        }
                        
        if (count($ids) > 0) {                    
            Helper::sendNotification($ids, $message, 'Order Created', $user->id, 'user', $seller_id, 'seller', $ids);

        }
        $this->createorder_event($order_id);
        return json_encode(['success'=> true, 'detail'=> 'Your payment get success, please check your order detail.']);
    }
    public function createorder_event($order_id)
    {   
        $neworder=new_orders::where('id',$order_id)->first();
        $prescriptions = Prescription::where('id',$neworder->prescription_id)->first();
        $customer_name = new_users::where('id',$neworder->customer_id)->first();
            $address = new_address::where('id',$neworder->address_id)->first();
            $newOrderEmit = new new_orders();
            $newOrderEmit->pharmacy_id = $neworder->pharmacy_id;
            $image_url = url('/').'/uploads/placeholder.png';

            if (!empty($prescriptions->image)) {
                if (file_exists(storage_path('app/public/uploads/prescription/'.$prescriptions->image))){
                    $image_url = asset('storage/app/public/uploads/prescription/' . $prescriptions->image);
                }
            }

            $newOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$neworder->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$neworder->order_number.'</span>';
            $newOrderEmit->id = $neworder->id;
            $newOrderEmit->total_days = $neworder->total_days;
            //$newOrderEmit->checking_by    = '';
            $newOrderEmit->prescription_img = $image_url;
            $newOrderEmit->number = '<a href="'.url('/orders/prescription/'.$neworder->id).'"><span>'.$neworder->order_number.'</span></a>';
            $newOrderEmit->order_number = $neworder->order_number;
            $newOrderEmit->order_note = $neworder->order_note;
            $newOrderEmit->order_time = $neworder->create_datetime;
            $newOrderEmit->created_at = $neworder->created_at;
            $newOrderEmit->updated_at = $neworder->updated_at;
            $newOrderEmit->order_type = $neworder->order_type;
            $newOrderEmit->is_external_delivery = $neworder->is_external_delivery;
            $newOrderEmit->delivery_type = new_delivery_charges::where('id', $neworder->delivery_charges_id)->value('delivery_type');


            $newOrderEmit->prescription_name = $prescriptions->name;
            $newOrderEmit->customer_name = $customer_name->name;
            $newOrderEmit->address = $address->address;
            $newOrderEmit->customer_number = $customer_name->mobile_number;
            $newOrderEmit->active = '<a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$neworder->id.'?home').'" title="Accept order">Accept</a>';

            $newOrderEmit->reject = '<a onclick="reject_order('.$neworder->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';
            
            event(new CreateNewOrder($newOrderEmit));
    }
    public function payment_fail()
    {
        $obj = new Payment($_ENV['PAYKUN_MERCHANTID'], $_ENV['PAYKUN_ACCESSTOKEN'], $_ENV['PAYKUN_ENCRYPTIONKEY'], false);
        $transactionData = $obj->getTransactionInfo($_REQUEST['payment-id']);
        return json_encode(['success'=> false, 'detail'=> 'Your payment get failed, please retry.']);
    }

    public function generate_unique_number()
	{
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		return 'PAYKUN_'.rand(111111111,999999999); 
	}

}