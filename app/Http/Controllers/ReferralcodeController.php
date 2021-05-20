<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReferralcodeController extends Controller
{
    public function index()
    {
		$data = array();
		$data['page_title'] = 'Referral Code';
		$data['page_condition'] = 'page_referralcode';
		$data['site_title'] = 'Referral Code | ' . $this->data['site_title'];
        return view('referralcode.index', $data);
    }
}
