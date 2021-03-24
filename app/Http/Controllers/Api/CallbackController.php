<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Http;
use App\callback;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Validator;
use GuzzleHttp\Client;
use Redirect;

class CallbackController extends Controller
{
    public function callback(Request $request)
    {
    	$response = array();
    	$response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

    	$read_parameters = $request->post();
    	$orderid = $request->orderid;
    	$orderno = $request->orderno;
    	$orderlabel = $request->orderlabel;
    	$orderstatus = $request->orderstatus;
    	$statuscode = $request->statuscode;
    	$drivername = $request->drivername;
    	$drivernumber = $request->drivernumber;
    	$driverbodytemp = $request->driverbodytemp;
    	$pickuptime = $request->pickuptime;
    	$pickupphoto1 = $request->pickupphoto1;
    	$pickupphoto2 = $request->pickupphoto2;
    	$pickupphoto3 = $request->pickupphoto3;
    	$deliverytime = $request->deliverytime;
    	$deliveryphoto1 = $request->deliveryphoto1;
    	$deliveryphoto2 = $request->deliveryphoto2;
    	$deliveryphoto3 = $request->deliveryphoto3;

    	$params = [
			'orderid' => $orderid,
			'orderno' => $orderno,
			'orderlabel' => $orderlabel,
			'orderstatus' => $orderstatus,
			'statuscode' => $statuscode,
			'drivername' => $drivername,
			'drivernumber' => $drivernumber,
			'driverbodytemp' => $driverbodytemp,
			'pickuptime' => $pickuptime,
			'pickupphoto1' => $pickupphoto1,
			'pickupphoto2' => $pickupphoto2,
			'pickupphoto3' => $pickupphoto3,
			'deliverytime' => $deliverytime,
			'deliveryphoto1' => $deliveryphoto1,
			'deliveryphoto2' => $deliveryphoto2,
			'deliveryphoto3' => $deliveryphoto3,
		];
		
		$validator = Validator::make($params, [
            'orderid' => 'required',
            'orderno' => 'required',
            'orderlabel' => 'required',
            'orderstatus' => 'required',
            'statuscode' => 'required',
            'drivername' => 'required',
            'drivernumber' => 'required',
            'driverbodytemp' => 'required',
            'pickupphoto1' => 'required',
            'pickupphoto2' => 'required',
            'pickupphoto3' => 'required',
            'deliveryphoto1' => 'required',
            'deliveryphoto2' => 'required',
            'deliveryphoto3' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }

    	$callback = new callback();
    	$callback->orderid = $orderid;
    	$callback->orderno = $orderno;
    	$callback->orderlabel = $orderlabel;
    	$callback->orderstatus = $orderstatus;
    	$callback->statuscode = $statuscode;
    	$callback->drivername = $drivername;
    	$callback->drivernumber = $drivernumber;
    	$callback->driverbodytemp = $driverbodytemp;
    	$callback->pickuptime = date('Y-m-d H:i:s');
    	$destinationPath = 'storage/app/public/uploads/elt/pickupphoto/'; 
    	if($file=$request->file('pickupphoto1')){
    		$filename = time().'-'.$file->getClientOriginalName();
            $tesw = $file->move($destinationPath, $filename);
            $callback->pickupphoto1 = $filename;
    	}
    	$destinationPath = 'storage/app/public/uploads/elt/pickupphoto/'; 
    	if($file=$request->file('pickupphoto2')){
    		$filename = time().'-'.$file->getClientOriginalName();
            $tesw = $file->move($destinationPath, $filename);
            $callback->pickupphoto2 = $filename;
    	}
    	$destinationPath = 'storage/app/public/uploads/elt/pickupphoto/'; 
    	if($file=$request->file('pickupphoto3')){
    		$filename = time().'-'.$file->getClientOriginalName();
            $tesw = $file->move($destinationPath, $filename);
            $callback->pickupphoto3 = $filename;
    	}
    	$callback->deliverytime = date('Y-m-d H:i:s');
    	$destinationPath = 'storage/app/public/uploads/elt/deliveryphoto/'; 
    	if($file=$request->file('deliveryphoto1')){
    		$filename = time().'-'.$file->getClientOriginalName();
            $tesw = $file->move($destinationPath, $filename);
            $callback->deliveryphoto1 = $filename;
    	}
    	$destinationPath = 'storage/app/public/uploads/elt/deliveryphoto/'; 
    	if($file=$request->file('deliveryphoto2')){
    		$filename = time().'-'.$file->getClientOriginalName();
            $tesw = $file->move($destinationPath, $filename);
            $callback->deliveryphoto2 = $filename;
    	}
    	$destinationPath = 'storage/app/public/uploads/elt/deliveryphoto/'; 
    	if($file=$request->file('deliveryphoto3')){
    		$filename = time().'-'.$file->getClientOriginalName();
            $tesw = $file->move($destinationPath, $filename);
            $callback->deliveryphoto3 = $filename;
    	}
    	$callback->save();
    	$response['data'] = $callback;
    	$response['message'] = 'Callback URL';
    	$response['status'] = 200;
		$pass_data = (array)json_decode($callback);
        $this->callback_get($pass_data);
    	return response($response, 200);
    }
    public function callback_get($pass_data)
    {
		$url = 'http://167.172.146.209/pharma/api/update_response_data';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($pass_data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}

