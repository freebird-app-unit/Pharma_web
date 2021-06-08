<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Pharmacyrating;
use App\new_address;
use App\Orders;
use DB;
use Log;
use File;
use Validator;

use App\new_users;
use App\new_logistics;
use App\new_pharmacies;
use App\new_orders;

use App\new_pharma_logistic_employee;
use Illuminate\Validation\Rule;
use App\new_countries;
use App\new_states;
use App\new_cities;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PharmacyController extends Controller
{
    public function _pre($all_array){
		echo '<pre>';
		print_r($all_array);
		echo '</pre>';
	}
	public function pharmacylist(Request $request)
	{
		$response = array();
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		$data = $request->input('data');
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data);
		$user_id  = isset($content->user_id) ? trim($content->user_id) : '';
		$address_id  = isset($content->address_id) ? trim($content->address_id) : '';
		$searchtext  = isset($content->searchtext) ? trim($content->searchtext) : '';
		$sortings  = isset($content->sortings) ? trim($content->sortings) : '';
		$filter_discount = isset($content->filter_discount) ? trim($content->filter_discount) : '';
		$filter_ratings  = isset($content->filter_ratings) ? trim($content->filter_ratings) : '';
		$filter_distance = isset($content->filter_distance) ? trim($content->filter_distance) : ''; 
		$current_latitude = isset($content->current_latitude) ? trim($content->current_latitude) : '';
		$current_longitude = isset($content->current_longitude) ? trim($content->current_longitude) : '';
		$page = isset($content->page) ? trim($content->page) : '';

		$params = [
        	'user_id'  	=> $user_id,
        	'searchtext'   => $searchtext,
            'sortings'   => $sortings,
            'filter_discount'      => $filter_discount,
            'filter_ratings'  => $filter_ratings,
            'filter_distance'   => $filter_distance,
            'current_latitude'   => $current_latitude,
			'current_longitude'   => $current_longitude,
			'address_id' => $address_id,
			'page' => $page
        ];

        $validator = Validator::make($params, [
        	'locality'  => 'required',
        	'user_id'  	=> 'required',
        	'searchtext'  => 'required',
            'sortings'  => 'required',
            'filter_discount'  => 'required',
            'filter_ratings'  => 'required',
            'filter_distance'   => 'required',
            'current_latitude'   => 'required',
			'current_longitude'   => 'required',
			'address_id' => 'required'
        ]);

		$response['status'] = 200;
		$response['message'] = '';
        $response['data']['currentPageIndex'] = 0;
        $response['data']['totalPage']=0;
        $response['data']['content'] = array();

		$userdata = new_users::find($user_id);
		$address_data = new_address::where('id',$address_id)->get();

		$current_latitude = ($current_latitude !== '')?$current_latitude:0;
		$current_longitude = ($current_longitude !== '')?$current_longitude:0;

		$content->current_latitude = $current_latitude;
		$content->current_longitude = $current_longitude;

		// $pincode_data = isset($address_data[0]->pincode)?($address_data[0]->pincode):'';
		if(empty($userdata)){
			
			$response['status'] = 401;
			$response['message'] = 'User Not Found';
			$response = json_encode($response);
			//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
			return response($response, 200);
		}
		$current_time = date('H:i:s');
		$area_cover_by_city_list = $this->CityGeofenceCheck($content);
		//$this->_pre($area_cover_by_city_list);
		if($area_cover_by_city_list){
			
			$geo_location_inter_section = $area_cover_by_city_list[0]->geo_location_inter_section;
			$GeofenceCheck = $this->GeofenceCheck($content,$geo_location_inter_section);
			$city = $area_cover_by_city_list[0]->city;
			$start_time = $area_cover_by_city_list[0]->start_time;
			$close_time = $area_cover_by_city_list[0]->close_time;
			$params['is_intersection'] = "-2";
			//if($geo_location_inter_section > 0){
				if($GeofenceCheck == "true"){
					$params['is_intersection'] = "1";
					if($close_time >= $current_time){
						$params['city'] = $city;
						$params['user_id'] = $user_id;
						$params['logistic_current_status'] = true;
						$params['logistic_id'] = -1;
						$params['next_logistic_working_day'] = "";
						return $this->GetPharmacyLogisticWise($params,$encryption);
						// Free PAid 
						//"logistic_current_status" = true
						// next_logistic_working_day = ""
					}else{
						$params['city'] = $city;
						$params['user_id'] = $user_id;
						$params['logistic_current_status'] = false;
						$params['logistic_id'] = -1;
						$params['next_logistic_working_day'] = $this->GetNextDayLogic();
						return $this->GetPharmacyLogisticWise($params,$encryption);
						// Free PAid 
						//"logistic_current_status" = false
						// next_logistic_working_day = Set Next Working Day
					}
				}else{
					$params['is_intersection'] = "0";
					$final_logistics_detail = array();
					$area_covered_single_logistics_list = \DB::table('area_covered_single_logistics')->select('*')->where('city', $city)->get();
					foreach ($area_covered_single_logistics_list as $area_covered_single_logistics_list_key => $area_covered_single_logistics_list_value) {
						$geo_fencing_id = $area_covered_single_logistics_list_value->geo_fencing_id;
						$InnerGeofenceCheck = $this->GeofenceCheck($content,$geo_fencing_id);
						if($InnerGeofenceCheck == "true"){
							$db_logistic_id = $area_covered_single_logistics_list_value->logistic_id;
							$logistics_detail = new_logistics::where('is_active', 1)->where('is_available', 1)->where('id',$db_logistic_id)->first();
							$params['logistic_id'] = $logistics_detail->id;
							if($logistics_detail){
								$final_logistics_detail = $logistics_detail;
								break;
							}
							
						}
					}
					if($final_logistics_detail){
						$city = $final_logistics_detail->city;
						$timing_logistic_detail = new_logistics::where('id',$final_logistics_detail->id)->where('close_time','>=', $current_time)->first();
						if($timing_logistic_detail){
							$params['city'] = $city;
							$params['user_id'] = $user_id;
							$params['logistic_current_status'] = true;
							$params['next_logistic_working_day'] = "";
							return $this->GetPharmacyLogisticWise($params,$encryption);
							// Free PAid 
							//"logistic_current_status" = true
							// next_logistic_working_day 

						}elseif($final_logistics_detail->is_received_order_when_closed == 1){
							$params['city'] = $city;
							$params['logistic_current_status'] = false;
							$params['next_logistic_working_day'] = $this->GetNextDayLogic();
							return $this->GetPharmacyLogisticWise($params,$encryption);
							// Free PAid 
							//"logistic_current_status" = false
							// next_logistic_working_day = Set Next Working Day					
						}else{
							//////// cron job set

						}
					}
				}
			//}
		}else{ 
			$pharmacy_arr = array();
			$response['status'] = 200;
			$response['message'] = 'Pharmacy';
			$response['data']['content'] = $pharmacy_arr;
			$response = json_encode($response);
			//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
			return response($response, 200);
		}
	}
	public function GetNextDayLogic(){
		return date('Y-m-d',strtotime("+1 day"));
	}
	public function GetPharmacyLogisticWise($search_array,$encryption){
		$secretyKey = env('ENC_KEY');
		$isLogistics = 'false';
		$current_time = date('H:i:s');
		$city = isset($search_array['city']) ? $search_array['city'] : 0;
		$logistic_current_status = isset($search_array['logistic_current_status']) ? $search_array['logistic_current_status'] : 0;
		$logistic_id = isset($search_array['logistic_id']) ? $search_array['logistic_id'] : 0;
		$is_intersection = isset($search_array['is_intersection']) ? $search_array['is_intersection'] : 0;
		$next_logistic_working_day = isset($search_array['next_logistic_working_day']) ? $search_array['next_logistic_working_day'] : 0;
		$searchtext = isset($search_array['searchtext']) ? $search_array['searchtext'] : '';
		$filter_ratings = isset($search_array['filter_ratings']) ? $search_array['filter_ratings'] : '';
		$sortings = isset($search_array['sortings']) ? $search_array['sortings'] : '';
		$current_latitude = isset($search_array['current_latitude']) ? $search_array['current_latitude'] : '';
		$current_longitude = isset($search_array['current_longitude']) ? $search_array['current_longitude'] : '';
		$page = isset($search_array['page']) ? $search_array['page'] : '';
		$user_id = isset($search_array['user_id']) ? $search_array['user_id'] : '';

		$pharmacies = $this->pharamcyList($city);
		if(isset($pharmacies) && count($pharmacies)){
			$pharmacyIds = array();
			foreach($pharmacies as $key=>$val){
				array_push($pharmacyIds, $val->pharmacyValue->id);
			}
			$isLogistics = 'true';
			$pharmacyFree = new_pharmacies::select('new_pharmacies.*',DB::raw("'false' as is_paid"), DB::raw('AVG(pharmacy_rating.rating) AS rating'), DB::raw("6371 * acos(cos(radians(" . $current_latitude . ")) * cos(radians(new_pharmacies.lat)) * cos(radians(new_pharmacies.lon) - radians(" . $current_longitude . ")) + sin(radians(" . $current_latitude . ")) * sin(radians(new_pharmacies.lat))) AS distance"))->leftJoin('pharmacy_rating', 'pharmacy_rating.pharmacy_id', '=', 'new_pharmacies.id')->groupBy('new_pharmacies.id')->havingRaw('distance <= radius')->whereIn('new_pharmacies.id', $pharmacyIds)->where('new_pharmacies.is_active', 1);
			$queries = DB::getQueryLog();
			DB::connection()->enableQueryLog();
			$pharmacyPaid = new_pharmacies::select('new_pharmacies.*', DB::raw("'true' as is_paid"), DB::raw('AVG(pharmacy_rating.rating) AS rating'), DB::raw("6371 * acos(cos(radians(" . $current_latitude . ")) * cos(radians(new_pharmacies.lat)) * cos(radians(new_pharmacies.lon) - radians(" . $current_longitude . ")) + sin(radians(" . $current_latitude . ")) * sin(radians(new_pharmacies.lat))) AS distance"))->leftJoin('pharmacy_rating', 'pharmacy_rating.pharmacy_id', '=', 'new_pharmacies.id')->groupBy('new_pharmacies.id')->havingRaw('distance >= radius')->whereIn('new_pharmacies.id', $pharmacyIds)->where('new_pharmacies.is_active', 1);
			$queries = DB::getQueryLog();
		}
		$pharmacy_arr = array();
		if($isLogistics !== 'true'){
			$response['status'] = 200;
			$response['message'] = 'Pharmacy';
			$response['data']['content'] = $pharmacy_arr;
			$response = json_encode($response);
			//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
			return response($response, 200);
		}
		if($searchtext!=''){
			$pharmacyFree = $pharmacyFree->where(function ($query) use($searchtext) {
				$query->where('new_pharmacies.name', 'like', '%'.$searchtext.'%');
				/*->orWhere('new_pharmacies.email', 'like', '%'.$searchtext.'%')
				->orWhere('new_pharmacies.mobile_number', 'like', '%'.$searchtext.'%');*/
			});
			if($isLogistics == 'true'){
				$pharmacyPaid = $pharmacyPaid->where(function ($query) use($searchtext) {
					$query->where('new_pharmacies.name', 'like', '%'.$searchtext.'%');
					/*->orWhere('new_pharmacies.email', 'like', '%'.$searchtext.'%')
					->orWhere('new_pharmacies.mobile_number', 'like', '%'.$searchtext.'%');*/
				});
			}
		}
		if($filter_ratings!=''){
			$pharmacyFree= $pharmacyFree->where('rating', '=', $filter_ratings);
			if($isLogistics=='true'){
				$pharmacyPaid= $pharmacyPaid->where('rating', '=', $filter_ratings);
			}
		} else if ($sortings == 'rating'){
			$pharmacyFree= $pharmacyFree->orderBy('rating', 'DESC');
			if($isLogistics=='true'){
				$pharmacyPaid= $pharmacyPaid->orderBy('rating', 'DESC');
			}
		}
		if($sortings == 'new_arrival'){
			$pharmacyFree= $pharmacyFree->orderBy('created_at', 'DESC');
			if($isLogistics=='true'){
				$pharmacyPaid= $pharmacyPaid->orderBy('created_at', 'DESC');
			}
		}
		if($sortings == 'fast_delivery'){
			$pharmacyFree= $pharmacyFree->orderBy('delivery_hour', 'ASC');
			if($isLogistics=='true'){
				$pharmacyPaid= $pharmacyPaid->orderBy('delivery_hour', 'ASC');
			}
		}
		$pharmacyFree = $pharmacyFree->get();
		if($isLogistics == 'true'){
			foreach ($pharmacyFree as $key => $value) {
				if (($key = array_search($value['id'], $pharmacyIds)) !== false) {
					unset($pharmacyIds[$key]);
				}
			}
			$pharmacyPaid = $pharmacyPaid->whereIn('new_pharmacies.id', $pharmacyIds);
			$pharmacyPaid = $pharmacyPaid->get();
		}
		/*if(count($pharmacyFree)>0 && isset($pharmacyPaid)){*/
		if(!empty($pharmacyFree) || isset($pharmacyPaid)){
			$pharmacyPaid = array_merge($pharmacyFree->toArray(), $pharmacyPaid->toArray());
		} else {
			$pharmacyPaid = $pharmacyFree->toArray();
		}

		$pharmacy = $pharmacyPaid;
		$total = count($pharmacy);
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['status'] = 200;
			$response['message'] = 'Pharmacy';
            $response['data']['currentPageIndex'] = (int)$page;
            $response['data']['totalPage'] = ceil($total/$per_page);
            $orders = $this->paginate($pharmacy,$per_page,$page,[]);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];

		//$this->_pre($pharmacy);
		usort($pharmacy, "sort_pharmacy_array_distance");
		usort($pharmacy, "sort_pharmacy_array_is_paid");
		
		$free_open = [];
		$free_close = [];
		$paid_open = [];
		$paid_close = [];
		$referral_code = [];

		if(count($data_array)>0){
			$cnt = 0;
			foreach($data_array as $val){
				$val = (object)$val;
				// $orders = Orders::where('pharmacy_id',$val->id)->get();
				//$rating = get_pharmacy_rating($val->id);
				$rating = new_pharmacies::where('id',$val->id)->first();
				$average_star1 = round($rating->average_star,1);
				$average_star2 = (string)$average_star1;
				$radius = is_numeric($val->radius)?number_format($val->radius, 2):'';
				$pharmacy_id = number_format($val->id, 2);
				$address = isset($val->address) ? $val->address : '';
				$mobile_number = ($val->mobile_number!='')?$val->mobile_number:'';
				$discount = ($val->discount!='' && $val->discount>0)?$val->discount.'% off ':'0 % off';
				$delivery_hour = (isset($val->delivery_hour) && $val->delivery_hour!='' && $val->delivery_hour>0)?$val->delivery_hour.' Hour delivery':'';
				$close_time = ($val->close_time!='')?$val->close_time:'';
				
				$profile_image = '';
				if (!empty($val->profile_image)) {
					$filename = storage_path('app/public/uploads/new_pharmacy/' . $val->profile_image);
					if (File::exists($filename)) {
						$profile_image = asset('storage/app/public/uploads/new_pharmacy/' . $val->profile_image);
					}
				}
				
				if($close_time >= $current_time){
					$pharmacy_current_status = true;
					$next_pharmacy_working_day = "";
				}else{
					$pharmacy_current_status = false;
					$next_pharmacy_working_day = $this->GetNextDayLogic();
				}
				$user_data = new_users::where('id',$user_id)->first();
				if($user_data->referral_code != NULL){
					if($user_data->referral_code == $val->referral_code){
							$referral_code[] = [
							'logistic_id' => $logistic_id,
							'is_paid' => $val->is_paid,
							'pharmacy_distance' => number_format($val->distance, 2),
							'pharmacy_radius' => $radius,
							'pharmacy_id' => $pharmacy_id,
							'distance_unit' => 'Km',
							'id'          => $val->id,
							'name'          => $val->name,
							'address' => $address,
							'profile_image' => $profile_image,
							'email' => $val->email,
							'mobile_number' => $mobile_number,
							'rating' => ($average_star2)?$average_star2:'0.0',
							'discount' => $discount,
							'delivery_hour' => $delivery_hour,
							'total_order' => 0,
							'logistic_current_status' => $logistic_current_status,
							'is_intersection' => $is_intersection,
							'next_logistic_working_day' => $next_logistic_working_day,
							'close_time' => $close_time,
							'pharmacy_current_status' => $pharmacy_current_status,
							'next_pharmacy_working_day' => $next_pharmacy_working_day
						];
					}		
				}
				if ($val->is_paid == 'false' && $pharmacy_current_status) {
					$free_open[] = [
						'logistic_id' => $logistic_id,
						'is_paid' => $val->is_paid,
						'pharmacy_distance' => number_format($val->distance, 2),
						'pharmacy_radius' => $radius,
						'pharmacy_id' => $pharmacy_id,
						'distance_unit' => 'Km',
						'id'          => $val->id,
						'name'          => $val->name,
						'address' => $address,
						'profile_image' => $profile_image,
						'email' => $val->email,
						'mobile_number' => $mobile_number,
						'rating' => ($average_star2)?$average_star2:'0.0',
						'discount' => $discount,
						'delivery_hour' => $delivery_hour,
						'total_order' => 0,
						'logistic_current_status' => $logistic_current_status,
						'is_intersection' => $is_intersection,
						'next_logistic_working_day' => $next_logistic_working_day,
						'close_time' => $close_time,
						'pharmacy_current_status' => $pharmacy_current_status,
						'next_pharmacy_working_day' => $next_pharmacy_working_day
					];
				}
				if ($val->is_paid == 'false' && !$pharmacy_current_status) {
					$free_close[] = [
						'logistic_id' => $logistic_id,
						'is_paid' => $val->is_paid,
						'pharmacy_distance' => number_format($val->distance, 2),
						'pharmacy_radius' => $radius,
						'pharmacy_id' => $pharmacy_id,
						'distance_unit' => 'Km',
						'id'          => $val->id,
						'name'          => $val->name,
						'address' => $address,
						'profile_image' => $profile_image,
						'email' => $val->email,
						'mobile_number' => $mobile_number,
						'rating' => ($average_star2)?$average_star2:'0.0',
						'discount' => $discount,
						'delivery_hour' => $delivery_hour,
						'total_order' => 0,
						'logistic_current_status' => $logistic_current_status,
						'is_intersection' => $is_intersection,
						'next_logistic_working_day' => $next_logistic_working_day,
						'close_time' => $close_time,
						'pharmacy_current_status' => $pharmacy_current_status,
						'next_pharmacy_working_day' => $next_pharmacy_working_day
					];
				}
				
				if ($val->is_paid == 'true' && $pharmacy_current_status) {
					$paid_open[] = [
						'logistic_id' => $logistic_id,
						'is_paid' => $val->is_paid,
						'pharmacy_distance' => number_format($val->distance, 2),
						'pharmacy_radius' => $radius,
						'pharmacy_id' => $pharmacy_id,
						'distance_unit' => 'Km',
						'id'          => $val->id,
						'name'          => $val->name,
						'address' => $address,
						'profile_image' => $profile_image,
						'email' => $val->email,
						'mobile_number' => $mobile_number,
						'rating' => ($average_star2)?$average_star2:'0.0',
						'discount' => $discount,
						'delivery_hour' => $delivery_hour,
						'total_order' => 0,
						'logistic_current_status' => $logistic_current_status,
						'is_intersection' => $is_intersection,
						'next_logistic_working_day' => $next_logistic_working_day,
						'close_time' => $close_time,
						'pharmacy_current_status' => $pharmacy_current_status,
						'next_pharmacy_working_day' => $next_pharmacy_working_day
					];
				}
				if ($val->is_paid == 'true' && !$pharmacy_current_status) {
					$paid_close[] = [
						'logistic_id' => $logistic_id,
						'is_paid' => $val->is_paid,
						'pharmacy_distance' => number_format($val->distance, 2),
						'pharmacy_radius' => $radius,
						'pharmacy_id' => $pharmacy_id,
						'distance_unit' => 'Km',
						'id'          => $val->id,
						'name'          => $val->name,
						'address' => $address,
						'profile_image' => $profile_image,
						'email' => $val->email,
						'mobile_number' => $mobile_number,
						'rating' => ($average_star2)?$average_star2:'0.0',
						'discount' => $discount,
						'delivery_hour' => $delivery_hour,
						'total_order' => 0,
						'logistic_current_status' => $logistic_current_status,
						'is_intersection' => $is_intersection,
						'next_logistic_working_day' => $next_logistic_working_day,
						'close_time' => $close_time,
						'pharmacy_current_status' => $pharmacy_current_status,
						'next_pharmacy_working_day' => $next_pharmacy_working_day
					];
				}
				$cnt++;
			}
		} else {
			$response['status'] = 404;
			$response['message'] = 'Pharmacy';
		}
		if($sortings == 'most_popular'){
			usort($pharmacy_arr, "sort_pharmacy_array_most_popular");
		}
		if($sortings == 'ratings'){
			usort($pharmacy_arr, "sort_pharmacy_array_ratings");
		}
		$final_array_merge = array_merge($referral_code,$free_open, $free_close, $paid_open, $paid_close);
		$final_array_unique = array_unique($final_array_merge,SORT_REGULAR);
		$final_arr =  array_values($final_array_unique);
		$response['data']['content'] = $final_arr;
		
		$response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        return response($response, 200);
	}
	public function paginate($items, $perPage = null, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
	public function CityGeofenceCheck($content)
	{
		$coordinates = array();
		$coordinates[0] = $content->current_latitude;
		$coordinates[1] = $content->current_longitude;
		$area_cover_by_city_list = \DB::table('area_cover_by_city')->select('*')->get();
		foreach ($area_cover_by_city_list as $key => $area_cover_by_city_value) { 
			$geo_fencings = \DB::table('geo_fencings')->select('*')->where('id', '=', $area_cover_by_city_value->geo_location_entire_city)->get();
			$result = 'false';
			if(count($geo_fencings)>0){
				foreach ($geo_fencings as $key => $value) { 
					switch ($value->type) {
						case 'circle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRound($coords, $value->radius, $coordinates);
							break;
						
						case 'polygon':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinPolygon($coords, $coordinates);
							break;
	
						case 'rectangle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRectangle($coords, $coordinates);
							break;
					}
					if($result == 'true'){
						return array($area_cover_by_city_value);
					}
				}
			}
		}
	}
	public function GeofenceCheck($content,$geo_location_inter_section)
	{
		$coordinates = array();
		$coordinates[0] = $content->current_latitude;
		$coordinates[1] = $content->current_longitude;
		$geo_fencings = \DB::table('geo_fencings')->select('*')->where('id', '=', $geo_location_inter_section)->get();
		$result = 'false';
		if(count($geo_fencings)>0){
			foreach ($geo_fencings as $key => $value) { 
				switch ($value->type) {
					case 'circle':
						$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
						$coords = explode(",", $coordsSet);
						$result = $this->checkWithinRound($coords, $value->radius, $coordinates);
						break;
					
					case 'polygon':
						$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
						$coords = explode(",", $coordsSet);
						$result = $this->checkWithinPolygon($coords, $coordinates);
						break;

					case 'rectangle':
						$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
						$coords = explode(",", $coordsSet);
						$result = $this->checkWithinRectangle($coords, $coordinates);
						break;
				}
			}
		}
		return $result;
	}
	public function pharamcyGeofenceCheck($logistics, $content)
	{
		$coordinates = array();
		$coordinates[0] = $content->lat;
		$coordinates[1] = $content->lon;

		foreach ($logistics as $key => $value) { 
			$logistic = $value;
			$geo_fencings = \DB::table('geo_fencings')->select('*')->where('user_id', '=', $value->id)->get();

			$result = 'false';
			if(count($geo_fencings)>0){
				foreach ($geo_fencings as $key => $value) { 
					switch ($value->type) {
						case 'circle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRound($coords, $value->radius, $coordinates);
							break;
						
						case 'polygon':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinPolygon($coords, $coordinates);
							break;
	
						case 'rectangle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRectangle($coords, $coordinates);
							break;
					}
					if($result == 'true'){
						return array($result, $logistic);
					}
				}
			}
		}
	}

	public function logisticGeofenceCheck($logistics, $content)
	{

		$coordinates = array();
		$logisticChecked = array();
		$coordinates[0] = $content->current_latitude;
		$coordinates[1] = $content->current_longitude;
		foreach ($logistics as $key => $value) { 
			$logistic = $value;
			$geo_fencings = \DB::table('geo_fencings')->select('*')->where('user_id', '=', $value->id)->get();

			$result = 'false';
			if(count($geo_fencings)>0){
				foreach ($geo_fencings as $key => $value) { 

					switch ($value->type) {
						case 'circle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRound($coords, $value->radius, $coordinates);
							break;
						
						case 'polygon':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinPolygon($coords, $coordinates);
							break;
	
						case 'rectangle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRectangle($coords, $coordinates);
							break;
					}

					if($result == 'true'){
						$logisticChecked[count($logisticChecked)] = $logistic;
						break;
					}
				}
			}

		}

		return $logisticChecked;
	}

	public function checkWithinRound($center, $radius, $coordinates)
	{
		// https://stackoverflow.com/questions/12439801/how-to-check-if-a-certain-coordinates-fall-to-another-coordinates-radius-using-p
		try {
			$earth_radius = 6371;
			$radius = $radius/1000; // for KM value
			$dLat = deg2rad($center[0] - $coordinates[0]);  
			$dLon = deg2rad($center[1] - $coordinates[1]);
			
			$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($coordinates[0])) * cos(deg2rad($center[0])) * sin($dLon/2) * sin($dLon/2);  
			$c = 2 * asin(sqrt($a));  
			$d = $earth_radius * $c;  
			
			if($d <  $radius) {
				return 'true';
			} else {
				return 'false';
			}
		} catch (Exception $e) {
			return 'false';
		}
	}

	public function checkWithinPolygon($coords, $coordinates)
	{
		// https://stackoverflow.com/questions/5065039/find-point-in-polygon-php
		try {
			$vertices_x = array();
			$vertices_y = array();

			foreach ($coords as $key => $value) { 
				if(!ctype_space($value)){
					if (($key%2) == 0) array_push($vertices_x, $value);
					else array_push($vertices_y, $value);
				}
			}

			$points_polygon = count($vertices_x) - 1;

			$longitude_x = $coordinates[0];  // x-coordinate of the point to test
			$latitude_y = $coordinates[1]; 

			$i = $j = $c = 0;
			for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
				if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
				($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
			}

			if ($c){
				return 'true';
			}
			else return 'false';

		} catch (Exception $e) {
			return 'false';
		}
	}

	public function checkWithinRectangle($coords, $coordinates)
	{
		// https://stackoverflow.com/questions/5065039/find-point-in-polygon-php
		try {
			$vertices_x = array();
			$vertices_y = array();

			foreach ($coords as $key => $value) { 
				if(!ctype_space($value)){
					if (($key%2) == 0) array_push($vertices_x, $value);
					else array_push($vertices_y, $value);
				}
			}

			$points_polygon = count($vertices_x) - 1;

			$longitude_x = $coordinates[0];  // x-coordinate of the point to test
			$latitude_y = $coordinates[1]; 

			$i = $j = $c = 0;
			for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
				if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
				($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
			}

			if ($c){
				return 'true';
			}
			else return 'false';

		} catch (Exception $e) {
			return 'false';
		}
	}

	public function pharamcyList($pharmacies_city)
	{
		$pharmacyChecked = array();
		DB::connection()->enableQueryLog();
		$pharmacy = new_pharmacies::select('new_pharmacies.*', DB::raw('AVG(pharmacy_rating.rating) AS rating'))
		->leftJoin('pharmacy_rating', 'pharmacy_rating.pharmacy_id', '=', 'new_pharmacies.id')
		->where('new_pharmacies.city', $pharmacies_city)->groupBy('new_pharmacies.id')->get();
		$queries = DB::getQueryLog();
		foreach ($pharmacy as $key => $pharmacyValue) { 
			//$result = $this->pharamcyGeofenceCheck($logistics, $pharmacyValue);
			//if($result[0] == 'true'){
				$Array = (object)[];
				$Array->pharmacyValue = $pharmacyValue;
				$Array->logisticValue = "";
				$pharmacyChecked[count($pharmacyChecked)] = $Array;
			//}
		}
		return $pharmacyChecked;
	}
}
