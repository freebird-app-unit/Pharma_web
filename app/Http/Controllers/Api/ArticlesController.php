<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Articles;
use App\new_users;
use App\Savedarticles;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class ArticlesController extends Controller
{
	public function articleslist(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id; 
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id = $content->user_id; 
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$articles = Articles::get();
		$articles_arr = array();
		if(count($articles)>0){
			foreach($articles as $key=>$val){
				$articles_arr[$key]['id'] = $val->id;
				$articles_arr[$key]['article_title'] = $val->article_title;
				$articles_arr[$key]['article_image'] = url('/').'/uploads/'.$val->article_image;
				$articles_arr[$key]['article_detail'] = ($val->article_detail!='')?$val->article_detail:'';
				$articles_arr[$key]['article_time'] = ($val->article_time!='')?date('H:i:s A',strtotime($val->article_time)):'';
				$articles_arr[$key]['article_for'] = ($val->article_for!='')?$val->article_for:'';
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Articles';
		$response['data'] = $articles_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	public function articledetail(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		// $article_id = $request->article_id;
		
		$data = $request->input('data');
		$content = json_decode($data);
		$user_id = $content->user_id;
		$article_id = $content->article_id;
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$articles = Articles::where('id',$article_id)->get();
		$articles_arr = array();
		if(count($articles)>0){
			foreach($articles as $key=>$val){
				$articles_arr[$key]['id'] = $val->id;
				$articles_arr[$key]['article_title'] = $val->article_title;
				$articles_arr[$key]['article_image'] = url('/').'/uploads/'.$val->article_image;
				$articles_arr[$key]['article_detail'] = ($val->article_detail!='')?$val->article_detail:'';
				$articles_arr[$key]['article_time'] = ($val->article_time!='')?date('H:i:s A',strtotime($val->article_time)):'';
				$articles_arr[$key]['article_for'] = ($val->article_for!='')?$val->article_for:'';
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Article detail';
		$response['data'] = $articles_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	public function savedarticleslist(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		 
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id = $content->user_id; 
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$savedarticles = Savedarticles::where('user_id',$user_id)->get();
		$articles_arr = array();
		if(count($savedarticles)>0){
			foreach($savedarticles as $key=>$val){
				$article = Articles::find($val->article_id);
				$articles_arr[$key]['id'] = $article->id;
				$articles_arr[$key]['article_title'] = $article->article_title;
				$articles_arr[$key]['article_image'] = url('/').'/uploads/'.$article->article_image;
				$articles_arr[$key]['article_detail'] = ($article->article_detail!='')?$article->article_detail:'';
				$articles_arr[$key]['article_time'] = ($article->article_time!='')?date('H:i:s A',strtotime($article->article_time)):'';
				$articles_arr[$key]['article_for'] = ($article->article_for!='')?$article->article_for:'';
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Saved Articles';
		$response['data'] = $articles_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
}	
