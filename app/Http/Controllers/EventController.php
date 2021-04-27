<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Incompletereason;
use App\new_orders;
use App\Events\AssignOrderLogistic;
use App\Events\CreateNewOrder;
use App\SellerModel\invoice;
use App\SellerModel\new_pharma_logistic_employee;
use App\SellerModel\new_address;
use App\new_delivery_charges;
use App\SellerModel\new_pharmacies;
use App\Prescription;
use App\new_users;
use File;
use Image;
use Storage;
use DB;
class EventController extends Controller
{
    public function event()
    {
        if(!empty($_REQUEST['order_id'])){
                $orders = new_orders::where('id',$_REQUEST['order_id'])->first();
                                        $assignOrderEmit = (object)[];
                                        $assignOrderEmit->pharmacy_id = $orders->pharmacy_id;
                                        $assignOrderEmit->logistic_id = $orders->logistic_user_id;
                                        $invoice = invoice::where('order_id',$orders->id)->first();
                                        $image_url = '';
                                        if(!empty($invoice)){
                                             if($invoice->invoice!=''){
                                            $destinationPath = base_path() . '/storage/app/public/uploads/invoice/'.$invoice->invoice;
                                            if(file_exists($destinationPath)){
                                                $image_url = url('/').'/storage/app/public/uploads/invoice/'.$invoice->invoice;
                                            }else{
                                                $image_url = url('/').'/uploads/placeholder.png';
                                            }
                                        }else{
                                            $image_url = url('/').'/uploads/placeholder.png';
                                        }
                                        }
                                        $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$orders->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$orders->id.'</span>';
                                        $assignOrderEmit->id = '<a href="'.url('/logisticupcoming/order_details/'.$orders->id).'"><img src="'.$image_url.'" width="50"/><span>'.$orders->order_number.'</span></a>';
                                        $assignOrderEmit->order_number = $orders->order_number;
                                        $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $orders->delivery_charges_id)->value('delivery_type');
                                        $assignOrderEmit->delivery_address = new_address::where('id', $orders->address_id)->value('address');
                                        $assignOrderEmit->pickup_address = new_pharmacies::where('id',$orders->pharmacy_id)->value('address');
                                        $assignOrderEmit->sellername = new_pharma_logistic_employee::where('id',$orders->process_user_id)->value('name');
                                        $assignOrderEmit->order_amount = $orders->order_amount;
                                        $assignOrderEmit->assign_datetime = $orders->assign_datetime;
                                        $assignOrderEmit->action = '<a onclick="assign_order('.$orders->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$orders->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

                                        event(new AssignOrderLogistic($assignOrderEmit));
            }
    }
    public function event_neworder()
    {
        $neworder=new_orders::where('id',$_REQUEST['orderid'])->first();
        if($neworder->is_external_delivery == 0){
             
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
            $newOrderEmit->created_at = $neworder->created_at;
            $newOrderEmit->order_time = $neworder->create_datetime;
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
    }
}
