<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Incompletereason;
use App\new_orders;
use App\Events\AssignOrderLogistic;
use App\SellerModel\invoice;
use App\SellerModel\new_pharma_logistic_employee;
use App\SellerModel\new_address;
use App\new_delivery_charges;
use App\SellerModel\new_pharmacies;
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
}
