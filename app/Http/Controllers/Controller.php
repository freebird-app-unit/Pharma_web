<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\User;
use DB;
use Auth;
class Controller extends BaseController
{
	protected $data = array();
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	public function __construct(){
		$this->middleware('auth');
		$this->data['site_title'] = (get_settings('site_name')!='')?get_settings('site_name'):'Pharma';
		$this->data['site_email'] = (get_settings('site_email')!='')?get_settings('site_email'):'';
		$this->data['site_contact'] = (get_settings('site_contact')!='')?get_settings('site_contact'):'';
		$this->data['site_address'] = (get_settings('site_address')!='')?get_settings('site_address'):'';
		$this->data['site_logo'] = (get_settings('site_logo')!='')?get_settings('site_logo'):'';
	}
}
