<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Orderfeedback;
use App\new_orders;
use App\Orders;
use App\new_users;
use App\DeliveryboyModel\new_order_history;
use Validator;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class OrderfeedbackController extends Controller
{
	public function orderfeedback(Request $request)
    {
		$response = array();
		// $user_id = $request->input('user_id');
		// $order_id = $request->input('order_id');
		// $pharmacy_id = $request->input('pharmacy_id');
		// $rating = $request->input('rating');
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
		$pharmacy_id = isset($content->pharmacy_id) ? $content->pharmacy_id : '';
		$rating = isset($content->rating) ? $content->rating : '';
		
		$params = [
			'user_id' => $user_id,
			'order_id' => $order_id,
			'pharmacy_id' => $pharmacy_id,
			'rating' => $rating
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'pharmacy_id' => 'required',
            'rating' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
        $token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$data = new_orders::find($order_id);
		$data2 = new_order_history::where('order_id',$order_id)->first();
		if(!empty($data)){
			$orderfeedback = new Orderfeedback();
			$orderfeedback->user_id = $user_id;
			$orderfeedback->order_id = $order_id;
			$orderfeedback->pharmacy_id = $pharmacy_id;
			$orderfeedback->rating = $rating;
			$orderfeedback->created_at = date('Y-m-d H:i:s');
			$orderfeedback->updated_at = date('Y-m-d H:i:s');
			$orderfeedback->save();
			$response['status'] = 200;
			$response['message'] = 'Your feedback successfully submited';
		}elseif (!empty($data2)) {
			$orderfeedback = new Orderfeedback();
			$orderfeedback->user_id = $user_id;
			$orderfeedback->order_id = $order_id;
			$orderfeedback->pharmacy_id = $pharmacy_id;
			$orderfeedback->rating = $rating;
			$orderfeedback->created_at = date('Y-m-d H:i:s');
			$orderfeedback->updated_at = date('Y-m-d H:i:s');
			$orderfeedback->save();
			$total_star = DB::table('order_feedback')->where('pharmacy_id',$pharmacy_id)->get()->sum('rating');
			$data1 = Orderfeedback::where('pharmacy_id',$pharmacy_id)->get();
			$total_rated_order = count($data1);
			$average = $total_star/ $total_rated_order;
			$add_rating = new_pharmacies::where('id',$pharmacy_id)->first();
			$add_rating->total_star=$total_star;
			$add_rating->total_rated_order=$total_rated_order;
			$add_rating->average_star=$average;
			$add_rating->save();
			$response['status'] = 200;
			$response['message'] = 'Your feedback successfully submited';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Error occured!';
		}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        return response($cipher, 200); 
    }
}	
