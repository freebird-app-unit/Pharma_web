<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\referralcode;

class ReferralcodeController extends Controller
{
    public function index()
    {
		$data = array();
		$data['page_title'] = 'Referral Code';
		$data['page_condition'] = 'page_referralcode';
		$data['site_title'] = 'Referral Code | ' . $this->data['site_title'];
        $data['referralcode'] = referralcode::where('id','1')->first();
        return view('referralcode.index', $data);
    }

    public function onoff(Request $request)
    {
    	$data = referralcode::where('id','1')->first();
    	$data->toggle = $request->toggle;
    	$data->save();
    }
}
