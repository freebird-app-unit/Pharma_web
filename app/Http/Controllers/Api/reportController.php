<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\report;
use App\report_images;
use App\contact_report;
use App\contact_images;
use App\Prescription;
use App\new_users;
use App\new_orders;
use App\SellerModel\invoice;
use App\DeliveryboyModel\new_order_images;
use App\DeliveryboyModel\new_order_history;
use Validator;
use Storage;
use Image;
use File;
use Mail;
use Helper;
class reportController extends Controller
{
    public function index(Request $request)
    {
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();
        
        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);
        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
        $description = isset($content->description) ? $content->description : '';
        $image = isset($content->image) ? $content->image : '';
        
        $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'mobile_number' => $mobile_number,
            'description' => $description,
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'mobile_number' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
       
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();
        $token =  $request->bearerToken();
        $user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
            $report= new report();
            $report->user_id = $user_id;
            $report->order_id = $order_id;
            $report->mobile_number = $mobile_number;
            $report->description = $description;
            $report->save();
            $destinationPath = 'storage/app/public/uploads/report/' ; 
            $images=array(); 
            if($files=$request->file('image')){
                
                foreach($files as $key => $file){
                    
                    $filename= time().'-'.$file->getClientOriginalName();
                    $tesw = $file->move($destinationPath, $filename);
                    $report_images =  new report_images();
                    $report_images->report_id = $report->id;
                    $report_images->image = $filename;
                    $report_images->save();
                }
            }
            $user_data = new_users::where('id',$user_id)->first();
            $order_data = new_orders::where('id',$order_id)->first();
            $order_history_data = new_order_history::where('order_id',$order_id)->first();
            $report = report::where('order_id',$order_id)->orderBy('created_at','DESC')->first();
            if(!empty($order_data)){
                if(!empty($report)){
                    $report_data = report_images::where('report_id',$report->id)->first();
                    $profile_image = '';
                    if (!empty($report_data->image)) {
                        $filename = storage_path('app/public/uploads/report/' . $report_data->image);
                            
                        if (File::exists($filename)) {
                            $profile_image = asset('storage/app/public/uploads/report/' . $report_data->image);
                        } else {
                            $profile_image = '';
                        }
                    }
                }
                $prescription_image = '';
                $prescription_data = Prescription::where('id',$order_data->prescription_id)->first();
                if(!empty($prescription_data)){
                        if (!empty($prescription_data->image)) {

                        $filename = storage_path('app/public/uploads/prescription/' . $prescription_data->image);
                                
                        if (File::exists($filename)) {
                            $prescription_image = asset('storage/app/public/uploads/prescription/' . $prescription_data->image);
                        } else {
                            $prescription_image = '';
                        }
                    }
                }
                $name = $user_data->name;
                $email=$user_data->email;
                $mobile_number=$report->mobile_number;
                $description=$report->description;
                $image= $profile_image;
                $pre_image =$prescription_image;
                $subject='Pharma - Report Problem';
                Helper::sendReport($name,$email,$mobile_number,$description,$image,$pre_image,$subject);
                /*$email = ['bhavik@thefreebirdtech.com','ravi@thefreebirdtech.com'];
                $result = Mail::send('email.report1', $data, function ($message) use ($email) {
                         $message->to($email)->subject('Pharma - Report Problem');
                });*/
                $response['status'] = 200;
                $response['message'] = 'Report Added Successfully';
            }elseif (!empty($order_history_data)) {
                $order_his_data = new_order_history::where('order_id',$order_id)->first();
                if($order_his_data->is_external_delivery == 1){
                     $report = report::where('order_id',$order_id)->orderBy('created_at','DESC')->first();
                if(!empty($report)){
                    $report_data = report_images::where('report_id',$report->id)->first();
                    $profile_image = '';
                    if (!empty($report_data->image)) {
                        $filename = storage_path('app/public/uploads/report/' . $report_data->image);
                            
                        if (File::exists($filename)) {
                            $profile_image = asset('storage/app/public/uploads/report/' . $report_data->image);
                        } else {
                            $profile_image = '';
                        }
                    }
                }
                $prescription_image = '';
                $prescription_data = Prescription::where('id',$order_history_data->prescription_id)->first();
                if(!empty($prescription_data)){
                        if (!empty($prescription_data->image)) {

                        $filename = storage_path('app/public/uploads/prescription/' . $prescription_data->image);
                                
                        if (File::exists($filename)) {
                            $prescription_image = asset('storage/app/public/uploads/prescription/' . $prescription_data->image);
                        } else {
                            $prescription_image = '';
                        }
                    }
                }
                $invoice_image = '';
                $invoice_data = invoice::where('order_id',$order_history_data->order_id)->first();
                    if(!empty($invoice_data)){
                        if (!empty($invoice_data->invoice)) {

                            $filename = storage_path('app/public/uploads/invoice/' . $invoice_data->invoice);
                                
                            if (File::exists($filename)) {
                                $invoice_image = asset('storage/app/public/uploads/invoice/' . $invoice_data->invoice);
                            } else {
                                $invoice_image = '';
                            }
                        }
                    }
                $pickup_image = '';
                $pickup_data = new_order_images::where(['order_id'=>$order_history_data->order_id,'image_type'=>'pickup'])->first();
                    if(!empty($pickup_data)){
                        if (!empty($pickup_data->image_name)) {

                            $filename = storage_path('app/public/uploads/pickup/' . $pickup_data->image_name);
                                
                            if (File::exists($filename)) {
                                $pickup_image = asset('storage/app/public/uploads/pickup/' . $pickup_data->image_name);
                            } else {
                                $pickup_image = '';
                            }
                        }
                    }
                $deliver_image = '';
                $deliver_data = new_order_images::where(['order_id'=>$order_history_data->order_id,'image_type'=>'deliver'])->first();
                    if(!empty($deliver_data)){
                        if (!empty($deliver_data->image_name)) {

                            $filename = storage_path('app/public/uploads/deliver/' . $deliver_data->image_name);
                                
                            if (File::exists($filename)) {
                                $deliver_image = asset('storage/app/public/uploads/deliver/' . $deliver_data->image_name);
                            } else {
                                $deliver_image = '';
                            }
                        }
                    }
                
                $name = $user_data->name;
                $email=$user_data->email;
                $mobile_number=$report->mobile_number;
                $description=$report->description;
                $image= $profile_image;
                $pre_image =$prescription_image;
                $inv_image= $invoice_image;
                $pick_image= $pickup_image;
                $del_image= $deliver_image;
                $subject='Pharma - Report Problem';
                Helper::sendReportPaid($name,$email,$mobile_number,$description,$image,$pre_image,$inv_image,$pick_image,$del_image,$subject);
                }else{
                    $report = report::where('order_id',$order_id)->orderBy('created_at','DESC')->first();
                if(!empty($report)){
                    $report_data = report_images::where('report_id',$report->id)->first();
                    $profile_image = '';
                    if (!empty($report_data->image)) {
                        $filename = storage_path('app/public/uploads/report/' . $report_data->image);
                            
                        if (File::exists($filename)) {
                            $profile_image = asset('storage/app/public/uploads/report/' . $report_data->image);
                        } else {
                            $profile_image = '';
                        }
                    }
                }
                $prescription_image = '';
                $prescription_data = Prescription::where('id',$order_history_data->prescription_id)->first();
                if(!empty($prescription_data)){
                        if (!empty($prescription_data->image)) {

                        $filename = storage_path('app/public/uploads/prescription/' . $prescription_data->image);
                                
                        if (File::exists($filename)) {
                            $prescription_image = asset('storage/app/public/uploads/prescription/' . $prescription_data->image);
                        } else {
                            $prescription_image = '';
                        }
                    }
                }
                $invoice_image = '';
                $invoice_data = invoice::where('order_id',$order_history_data->order_id)->first();
                    if(!empty($invoice_data)){
                        if (!empty($invoice_data->invoice)) {

                            $filename = storage_path('app/public/uploads/invoice/' . $invoice_data->invoice);
                                
                            if (File::exists($filename)) {
                                $invoice_image = asset('storage/app/public/uploads/invoice/' . $invoice_data->invoice);
                            } else {
                                $invoice_image = '';
                            }
                        }
                    }
                $name = $user_data->name;
                $email=$user_data->email;
                $mobile_number=$report->mobile_number;
                $description=$report->description;
                $image= $profile_image;
                $pre_image =$prescription_image;
                $inv_image= $invoice_image;
                $subject='Pharma - Report Problem';
                Helper::sendReportFree($name,$email,$mobile_number,$description,$image,$pre_image,$inv_image,$subject);
                }
                $response['status'] = 200;
                $response['message'] = 'Report Added Successfully';
            }else{
                 $response['status'] = 404;
                 $response['message'] = 'Error Occured';
            }
            
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }
        $response = json_encode($response);
        $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
        return response($cipher, 200);
    }

    public function contact_report(Request $request){
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();
        
        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $name = isset($content->name) ? $content->name : '';
        $email = isset($content->email) ? $content->email : '';
        $mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
        $description = isset($content->description) ? $content->description : '';
        $image = isset($content->image) ? $content->image : '';
        
        $params = [
            'user_id' => $user_id,
            'name' => $name,
            'email' => $email,
            'mobile_number' => $mobile_number,
            'description' => $description,
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'mobile_number' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
       
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();
        $token =  $request->bearerToken();
        $user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
        $contactreport= new contact_report();
        $contactreport->user_id = $user_id;
        $contactreport->name = $name;
        $contactreport->email = $email;
        $contactreport->mobile_number = $mobile_number;
        $contactreport->description = $description;
        $contactreport->save();
        $destinationPath = 'storage/app/public/uploads/contact_report/' ; 
            $images=array(); 
            if($files=$request->file('image')){
                
                foreach($files as $key => $file){
                    
                    $filename= time().'-'.$file->getClientOriginalName();
                    $tesw = $file->move($destinationPath, $filename);
                    $contactimages =  new contact_images();
                    $contactimages->contact_report_id = $contactreport->id;
                    $contactimages->image = $filename;
                    $contactimages->save();
                }
            }
            
            $profile_image = '';
            if (!empty($contactimages->image)) {

                $filename = storage_path('app/public/uploads/contact_report/' . $contactimages->image);
                    
                if (File::exists($filename)) {
                    $profile_image = asset('storage/app/public/uploads/contact_report/' . $contactimages->image);
                } else {
                    $profile_image = '';
                }
            }
            $data = [
                'name' => $contactreport->name,
                'email'=>$contactreport->email,
                'mobile_number'=>$contactreport->mobile_number,
                'description'=>$contactreport->description,
                'image'=> $profile_image
            ];
            $email = ['bhavik@thefreebirdtech.com','ravi@thefreebirdtech.com'];
            $result = Mail::send('email.contactus', $data, function ($message) use ($email) {
                     $message->to($email)->subject('Pharma - ContactUs');
            });
        $response['status'] = 200;
        $response['message'] = 'Contact Report Added Successfully';
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }
        $response = json_encode($response);
        $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
        return response($cipher, 200);
    }
}
