<?php
if(!function_exists('get_settings')) {
 
    function get_settings($key) {
        $data = \DB::table('settings')
            ->select($key)
            ->where('id',1)
            ->first();
		if($data){
			return $data->$key;
		}else{
			return '';
		}
    }
}

if(!function_exists('get_image')) {
 
    function get_image($table,$field,$id) {
        $data = \DB::table($table)
            ->select($field)
            ->where('id',$id)
            ->first();
		if($data){
			return $data->$field;
		}else{
			return '';
		}
    }
}
if(!function_exists('get_name')) {
 
    function get_name($table,$field,$id) {
        $data = \DB::table($table)
            ->select($field)
            ->where('id',$id)
            ->first();
		if($data){
			return $data->$field;
		}else{
			return '';
		}
    }
}

if(!function_exists('get_order_time')) {
 
    function get_order_time($order_id,$deliveryboy_id) {
        $data = \DB::table('order_assign')
            ->select('created_at')
            ->where('order_id',$order_id)
			->where('deliveryboy_id',$deliveryboy_id)
            ->first();
		if($data){
			if($data->created_at!=''){
				return date('h:i A',strtotime($data->created_at));
			}else{
				return '';
			}
		}else{
			return '';
		}
    }
}

if(!function_exists('get_order_delivered_time')) {
 
    function get_order_delivered_time($order_id,$deliveryboy_id) {
        $data = \DB::table('order_assign')
            ->select('updated_at')
            ->where('order_id',$order_id)
			->where('deliveryboy_id',$deliveryboy_id)
            ->first();
		if($data){
			if($data->updated_at!=''){
				return date('h:i A',strtotime($data->updated_at));
			}else{
				return '';
			}
		}else{
			return '';
		}
    }
}

if(!function_exists('get_incomplete_reason')) {
 
    function get_incomplete_reason($id) {
        $data = \DB::table('incompletereason')
            ->select('reason')
            ->where('id',$id)
            ->first();
		if($data){
			return $data->reason;
		}else{
			return '';
		}
    }
}

if(!function_exists('get_reject_reason')) {
 
    function get_reject_reason($id) {
        $data = \DB::table('rejectreason')
            ->select('reason')
            ->where('id',$id)
            ->first();
		if($data){
			return $data->reason;
		}else{
			return '';
		}
    }
}

if(!function_exists('get_cancel_reason')) {
 
    function get_cancel_reason($id) {
        $data = \DB::table('cancelreason')
            ->select('reason')
            ->where('id',$id)
            ->first();
		if($data){
			return $data->reason;
		}else{
			return '';
		}
    }
}

if(!function_exists('get_completed_order')) {
 
    function get_completed_order($id,$filter_start_date,$filter_end_date,$user_type="") {
        $data = \DB::table('new_order_history')
		->select('*')
		->where('order_status','complete');
		if($user_type = 'seller'){
			$data = $data->where('process_user_id',$id);
		}else{
			$data = $data->where('deliveryboy_id',$id);
		}
		if($filter_start_date!='' && $filter_end_date!=''){
			$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
				$query->whereRaw(
						"(created_at >= ? AND created_at <= ?)", 
						[$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
					);
			});
		}
		
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_incomplete_order')) {
 
    function get_incomplete_order($id,$filter_start_date,$filter_end_date,$user_type="") {
        $data = \DB::table('new_orders')
            ->select('*')
            ->where('order_status','incomplete');
			if($user_type = 'seller'){
				$data = $data->where('process_user_id',$id);
			}else{
				$data = $data->where('deliveryboy_id',$id);
			}
			if($filter_start_date!='' && $filter_end_date!=''){
				$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
					$query->whereRaw(
						  "(created_at >= ? AND created_at <= ?)", 
						  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
						);
				});
			}
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_rejected_order')) {
 
    function get_rejected_order($id,$filter_start_date,$filter_end_date,$user_type="") {
        $data = \DB::table('new_orders')
            ->select('*')
            ->where('order_status','reject');
			if($user_type = 'seller'){
				$data = $data->where('process_user_id',$id);
			}else{
				$data = $data->where('deliveryboy_id',$id);
			}
			if($filter_start_date!='' && $filter_end_date!=''){
				$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
					$query->whereRaw(
						  "(created_at >= ? AND created_at <= ?)", 
						  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
						);
				});
			}
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_total_order')) { 
 
    function get_total_order($id,$filter_start_date,$filter_end_date,$user_type="") {
        $data = \DB::table('new_orders')
			->select('*');
			if($user_type = 'seller'){
				$data = $data->where('process_user_id',$id);
			}else{
				$data = $data->where('deliveryboy_id',$id);
			}
			if($filter_start_date!='' && $filter_end_date!=''){
				$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
					$query->whereRaw(
						  "(created_at >= ? AND created_at <= ?)", 
						  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
						);
				});
			}
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_deliveryboy_completed_order')) {
 
    function get_deliveryboy_completed_order($id,$filter_start_date,$filter_end_date) {
        $data = \DB::table('new_orders')
            ->select('*')
            ->where('order_status','complete')
			->where('deliveryboy_id',$id);
			if($filter_start_date!='' && $filter_end_date!=''){
				$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
					$query->whereRaw(
						  "(created_at >= ? AND created_at <= ?)", 
						  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
						);
				});
			}
		
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_deliveryboy_incomplete_order')) {
 
    function get_deliveryboy_incomplete_order($id,$filter_start_date,$filter_end_date) {
        $data = \DB::table('new_orders')
            ->select('*')
            ->where('order_status','incomplete')
			->where('deliveryboy_id',$id);
			if($filter_start_date!='' && $filter_end_date!=''){
				$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
					$query->whereRaw(
						  "(created_at >= ? AND created_at <= ?)", 
						  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
						);
				});
			}
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_deliveryboy_rejected_order')) {
 
    function get_deliveryboy_rejected_order($id,$filter_start_date,$filter_end_date) {
        $data = \DB::table('new_orders')
            ->select('*')
            ->where('order_status','reject')
			->where('deliveryboy_id',$id);
			if($filter_start_date!='' && $filter_end_date!=''){
				$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
					$query->whereRaw(
						  "(created_at >= ? AND created_at <= ?)", 
						  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
						);
				});
			}
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_deliveryboy_total_order')) {
 
    function get_deliveryboy_total_order($id,$filter_start_date,$filter_end_date) {
        $data = \DB::table('new_orders')
            ->select('*')
			->where('deliveryboy_id',$id);
			if($filter_start_date!='' && $filter_end_date!=''){
				$data= $data->where(function ($query) use($filter_start_date,$filter_end_date) {
					$query->whereRaw(
						  "(created_at >= ? AND created_at <= ?)", 
						  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
						);
				});
			}
        $data = $data->get();
		if($data){
			return count($data);
		}else{
			return '';
		}
    }
}

if(!function_exists('get_pharmacy_rating')) {
 
    function get_pharmacy_rating($pharmacy_id) {
        $data = \DB::table('pharmacy_rating')
            ->select('*')
			->where('pharmacy_id',$pharmacy_id);
        $data = $data->get();
		if($data){
			$total_row = count($data);
			$total_rating = 0;
			foreach($data as $raw){
				$total_rating = $total_rating + $raw->rating;
			}
			$rating = 0;
			if($total_rating>0){
				$rating = $total_rating/$total_row;
			}
			return ceil($rating);
		}else{
			return 0;
		}
    }
}

if(!function_exists('sort_pharmacy_array_most_popular')) {
 
    function sort_pharmacy_array_most_popular($a,$b) {
        return $b["total_order"] - $a["total_order"];
    }
}

if(!function_exists('sort_pharmacy_array_ratings')) {
 
    function sort_pharmacy_array_ratings($a,$b) {
        /* return $b["rating"] - $a["rating"]; */
		if ($a["rating"] == $b["rating"]) {
                return 0;
        }
        return ($b["rating"] < $a["rating"]) ? -1 : 1;
    }
}

if(!function_exists('sort_pharmacy_array_is_paid')) {
 
    function sort_pharmacy_array_is_paid($a,$b) {
        if($a['is_paid'] == $b['is_paid']) return 0;
        else if($a['is_paid'] == 'true') return 1;
        else return -1;
    }
}
if(!function_exists('encode_string')) {
	function encode_string($data)
	    {
	    	$encryption = new \MrShan0\CryptoLib\CryptoLib();
			$secretyKey = env('ENC_KEY');
			
			$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
			
	        return $plainText;
	    }
}
if(!function_exists('decode_string')) {
	function decode_string($response)
	    {
			$encryption = new \MrShan0\CryptoLib\CryptoLib();
			$secretyKey = env('ENC_KEY');
			
			if (empty($data)) {
	            $data = (object)[];
	        } 
	    	
			$response = json_encode($response);
			$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
			
	        return response($cipher, 200);
	    }
}
if(!function_exists('validation_error')) {
	function validation_error($error, $data = [], $code = 200)
	    {
			$encryption = new \MrShan0\CryptoLib\CryptoLib();
			$secretyKey = env('ENC_KEY');
			
	        if (empty($data)) {
	            $data = (object)[];
	        }
	    	$response = [
	            'status' => 404,
	            'data'    => $data,
	            'message' => $error,
	        ];
			
			$response = json_encode($response);
			$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
			
	        return response($cipher, $code);
	    }
}
if(!function_exists('sort_pharmacy_array_distance')) {
 
    function sort_pharmacy_array_distance($a, $b) {
		if ((float) $a["distance"] == (float) $b["distance"])  return 0;
        return ((float) $b["distance"] < (float) $a["distance"]) ? 1 : -1;
    }

    function number_of_delivery_count($user_id, $deliveryboy_id) {
		$number_of_delivery_new_order = \App\new_orders::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$deliveryboy_id)->where('order_status','=','complete');

		$number_of_delivery_count = \App\new_order_history::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$deliveryboy_id)->where('order_status','=','complete');
		$number_of_delivery_count = $number_of_delivery_count->union($number_of_delivery_new_order)->count();
		return $number_of_delivery_count;
    }
    function delivered_return_count($user_id, $deliveryboy_id) {
		$delivered_return_new_order = \App\new_orders::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$deliveryboy_id)->where('order_status','=','reject');
        $delivered_return_count = \App\new_order_history::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$deliveryboy_id)->where('order_status','=','reject');
        $delivered_return_count = $delivered_return_count->union($delivered_return_new_order)->count();
        return $delivered_return_count;
    }
    function total_amount($user_id, $deliveryboy_id) {
    	$total_amount_new_order = \App\new_orders::where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$deliveryboy_id)->where('order_status','=','complete');
                
        $total_amount_new_order = $total_amount_new_order->sum('order_amount');
        $total_amount_new_order_history = \App\new_order_history::where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$deliveryboy_id)->where('order_status','=','complete');
        
        $total_amount_new_order_history = $total_amount_new_order_history->sum('order_amount');
        $total_amount = $total_amount_new_order + $total_amount_new_order_history;
        return $total_amount;
    }
}
?>