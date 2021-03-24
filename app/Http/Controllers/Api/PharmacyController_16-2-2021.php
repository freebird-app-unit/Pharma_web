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

//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class PharmacyController extends Controller
{
	public function pharmacylist(Request $request)
	{

		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		// $user_id = $request->user_id;
		// $searchtext = $request->searchtext;
		// $sortings = $request->sortings;
		// $filter_discount = $request->filter_discount;
		// $filter_ratings = $request->filter_ratings;
		// $filter_distance = $request->filter_distance;
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);

		$user_id  = isset($content->user_id) ? trim($content->user_id) : '';
		$address_id  = isset($content->address_id) ? trim($content->address_id) : '';
		$searchtext  = isset($content->searchtext) ? trim($content->searchtext) : '';
		$sortings  = isset($content->sortings) ? trim($content->sortings) : '';
		$filter_discount = isset($content->filter_discount) ? trim($content->filter_discount) : '';
		$filter_ratings  = isset($content->filter_ratings) ? trim($content->filter_ratings) : '';
		$filter_distance = isset($content->filter_distance) ? trim($content->filter_distance) : ''; 
		$current_latitude = isset($content->current_latitude) ? trim($content->current_latitude) : '';
		$current_longitude = isset($content->current_longitude) ? trim($content->current_longitude) : '';

		$params = [
        	'user_id'  	=> $user_id,
        	'searchtext'   => $searchtext,
            'sortings'   => $sortings,
            'filter_discount'      => $filter_discount,
            'filter_ratings'  => $filter_ratings,
            'filter_distance'   => $filter_distance,
            'current_latitude'   => $current_latitude,
			'current_longitude'   => $current_longitude,
			'address_id' => $address_id
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
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$userdata = new_users::find($user_id);
		$address_data = new_address::where('id',$address_id)->get();

		$current_latitude = ($current_latitude !== '')?$current_latitude:0;
		$current_longitude = ($current_longitude !== '')?$current_longitude:0;

		$content->current_latitude = $current_latitude;
		$content->current_longitude = $current_longitude;
		// $pincode_data = isset($address_data[0]->pincode)?($address_data[0]->pincode):'';
		$isLogistics = 'false';
		
		// $pharmacyFree = new_pharmacies::select('new_pharmacies.*', DB::raw("'false' as is_paid"), DB::raw('AVG(pharmacy_rating.rating) AS rating'), DB::raw("6371 * acos(cos(radians(" . $current_latitude . ")) * cos(radians(new_pharmacies.lat)) * cos(radians(new_pharmacies.lon) - radians(" . $current_longitude . ")) + sin(radians(" . $current_latitude . ")) * sin(radians(new_pharmacies.lat))) AS distance"))->leftJoin('pharmacy_rating', 'pharmacy_rating.pharmacy_id', '=', 'new_pharmacies.id')->groupBy('new_pharmacies.id')->havingRaw('distance <= radius');
		// $queries = DB::getQueryLog();

		// return json_encode(['q'=> end($queries)]);
		// if(isset($pincode_data)){
		// 	$logistics = \DB::table('users')
		// 	->select('*')
		// 	->where('users.user_type', '=', 'logistic')
		// 	->where('users.pincode', 'like', '%' . $pincode_data . '%')->get();
		// } else {
			$logistics = new_logistics::where('is_active', 1)->get();
		// }

		if(count($logistics)>0){
			$logistic = $this->logisticGeofenceCheck($logistics, $content);

			if(count($logistic)>0){
				$pharmacies = $this->pharamcyList($logistic);

				if(isset($pharmacies) && count($pharmacies)){
					$pharmacyIds = array();
					foreach($pharmacies as $key=>$val){
						array_push($pharmacyIds, $val->pharmacyValue->id);
					}

					$isLogistics = 'true';

					$pharmacyFree = new_pharmacies::select('new_pharmacies.*', DB::raw("'false' as is_paid"), DB::raw('AVG(pharmacy_rating.rating) AS rating'), DB::raw("6371 * acos(cos(radians(" . $current_latitude . ")) * cos(radians(new_pharmacies.lat)) * cos(radians(new_pharmacies.lon) - radians(" . $current_longitude . ")) + sin(radians(" . $current_latitude . ")) * sin(radians(new_pharmacies.lat))) AS distance"))->leftJoin('pharmacy_rating', 'pharmacy_rating.pharmacy_id', '=', 'new_pharmacies.id')->groupBy('new_pharmacies.id')->havingRaw('distance <= radius')->whereIn('new_pharmacies.id', $pharmacyIds)->where('new_pharmacies.is_active', 1);

					DB::connection()->enableQueryLog();
					$pharmacyPaid = new_pharmacies::select('new_pharmacies.*', DB::raw("'true' as is_paid"), DB::raw('AVG(pharmacy_rating.rating) AS rating'), DB::raw("6371 * acos(cos(radians(" . $current_latitude . ")) * cos(radians(new_pharmacies.lat)) * cos(radians(new_pharmacies.lon) - radians(" . $current_longitude . ")) + sin(radians(" .$current_latitude. ")) * sin(radians(new_pharmacies.lat))) AS distance"))->leftJoin('pharmacy_rating', 'pharmacy_rating.pharmacy_id', '=', 'new_pharmacies.id')->where('new_pharmacies.is_active', 1)->groupBy('new_pharmacies.id');	

					$queries = DB::getQueryLog();
				}
			}
		}

		$pharmacy_arr = array();
		if($isLogistics !== 'true'){
			$response['status'] = 200;
			$response['message'] = 'Pharmacy';
			$response['data'] = $pharmacy_arr;
			
			$response = json_encode($response);
			$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
			return response($cipher, 200);
		}

		if($searchtext!=''){
			$pharmacyFree = $pharmacyFree->where(function ($query) use($searchtext) {
				$query->where('name', 'like', '%'.$searchtext.'%')
				->orWhere('email', 'like', '%'.$searchtext.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtext.'%');
			});
			
			if($isLogistics == 'true'){
				$pharmacyPaid = $pharmacyPaid->where(function ($query) use($searchtext) {
					$query->where('name', 'like', '%'.$searchtext.'%')
					->orWhere('email', 'like', '%'.$searchtext.'%')
					->orWhere('mobile_number', 'like', '%'.$searchtext.'%');
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

		if(count($pharmacyFree)>0 && isset($pharmacyPaid)){
			$pharmacyPaid = array_merge($pharmacyFree->toArray(), $pharmacyPaid->toArray());
		} else {
			$pharmacyPaid = $pharmacyFree->toArray();
		}

		$pharmacy = $pharmacyPaid;

		usort($pharmacy, "sort_pharmacy_array_distance");
		usort($pharmacy, "sort_pharmacy_array_is_paid");

		if(count($pharmacy)>0){
			$cnt = 0;
			foreach($pharmacy as $val){

				$val = (object)$val;
				// $orders = Orders::where('pharmacy_id',$val->id)->get();
				$rating = get_pharmacy_rating($val->id);

				$pharmacy_arr[$cnt]['logistic_id'] = 0;
				$pharmacy_arr[$cnt]['is_paid'] = $val->is_paid;
				$pharmacy_arr[$cnt]['pharmacy_distance'] = number_format($val->distance, 2);

				$pharmacy_arr[$cnt]['pharmacy_radius'] = is_numeric($val->radius)?number_format($val->radius, 2):'';
				$pharmacy_arr[$cnt]['pharmacy_id'] = number_format($val->id, 2);
				$pharmacy_arr[$cnt]['distance_unit'] = 'Km';

				if($pharmacy_arr[$cnt]['is_paid'] == 'true'){
					foreach($pharmacies as $key=>$pharmacyVal){
						if($pharmacyVal->pharmacyValue->id == $val->id){
							$pharmacy_arr[$cnt]['logistic_id'] = $pharmacyVal->logisticValue->id;
						}
					}
				}

				$pharmacy_arr[$cnt]['id'] = $val->id;
				$pharmacy_arr[$cnt]['name'] = $val->name;
				$pharmacy_arr[$cnt]['address'] = isset($val->address) ? $val->address : '';
				
				$profile_image = '';
				if (!empty($val->profile_image)) {

					$filename = storage_path('app/public/uploads/users/' . $val->profile_image);
				
					if (File::exists($filename)) {
						$profile_image = asset('storage/app/public/uploads/users/' . $val->profile_image);
					}
				}
					
				$pharmacy_arr[$cnt]['profile_image'] = $profile_image;
				$pharmacy_arr[$cnt]['email'] = $val->email;
				$pharmacy_arr[$cnt]['mobile_number'] = ($val->mobile_number!='')?$val->mobile_number:'';
				$pharmacy_arr[$cnt]['rating'] = (float)$rating;
				$pharmacy_arr[$cnt]['discount'] = ($val->discount!='' && $val->discount>0)?$val->discount.'% off ':'0 % off';
				$pharmacy_arr[$cnt]['delivery_hour'] = (isset($val->delivery_hour) && $val->delivery_hour!='' && $val->delivery_hour>0)?$val->delivery_hour.' Hour delivery':'';
				// $pharmacy_arr[$cnt]['total_order'] = count($orders);
				$pharmacy_arr[$cnt]['total_order'] = 0;
				$cnt++;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}

		if($sortings == 'most_popular'){
			usort($pharmacy_arr, "sort_pharmacy_array_most_popular");
		}

		if($sortings == 'ratings'){
			usort($pharmacy_arr, "sort_pharmacy_array_ratings");
		}

		$response['message'] = 'Pharmacy';
		$response['data'] = $pharmacy_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
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

	public function pharamcyList($logistics)
	{

		$logisticPincode = array();
		$pharmacyChecked = array();
		// foreach ($logistics as $key => $value) { 
		// 	array_push($logisticPincode, $value->pincode);
		// }

		foreach ($logistics as $key => $value) { 
			if(isset($value->city)){
				$Arr = explode(",", $value->city);
				if(count($Arr)>0){
					foreach ($Arr as $Arrkey => $Arrvalue) { 
						array_push($logisticPincode, trim($Arrvalue));
					}
				} else {
					array_push($logisticPincode, trim($value->city));
				}
			}
		}

		if(count($logisticPincode)){
			$logisticPincode = array_unique($logisticPincode);
			DB::connection()->enableQueryLog();

			$pharmacy = new_pharmacies::select('new_pharmacies.*', DB::raw('AVG(pharmacy_rating.rating) AS rating'))
			->leftJoin('pharmacy_rating', 'pharmacy_rating.pharmacy_id', '=', 'new_pharmacies.id')
			->whereIn('new_pharmacies.city', $logisticPincode)->groupBy('new_pharmacies.id')->get();
			$queries = DB::getQueryLog();

			foreach ($pharmacy as $key => $pharmacyValue) { 
				$result = $this->pharamcyGeofenceCheck($logistics, $pharmacyValue);
				if($result[0] == 'true'){
					$Array = (object)[];
					$Array->pharmacyValue = $pharmacyValue;
					$Array->logisticValue = $result[1];
					$pharmacyChecked[count($pharmacyChecked)] = $Array;
				}
			}
			return $pharmacyChecked;
		}
	}
}	
