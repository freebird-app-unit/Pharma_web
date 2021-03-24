<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Customnotificationcontroller extends Controller
{
    public function create()
    {
       /*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='seller'){
			return redirect(route('home'));
		}*/
		$data = array();
		$data['page_title'] = 'Notification';
		$data['page_condition'] = 'page_notification';
		$data['site_title'] = 'Notification | ' . $this->data['site_title'];
		//$data['seller_list'] = User::where('user_type','seller')->get();
		return view('customnotification', array_merge($this->data, $data));
    }
}
