<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Packages;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
 
class PackagesController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='admin' && Auth::user()->user_type!='pharmacy'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Packages';
		$data['page_condition'] = 'page_packages';
		$data['site_title'] = 'Packages | ' . $this->data['site_title'];
        return view('packages.index', $data);
    }
	public function list()
    {
		$html = '';
		$packages = Packages::where('is_delete',0)->get();
	    if(isset($packages) && count($packages)>0){
			foreach($packages as $package){
				if(Auth::user()->user_type=='admin'){
					$html.='<div class="col-sm-4">';
						$html.='<div class="package_box">';
							$html.='<a class="action-icon edit" data-toggle="modal" href="javascript:void(0)" onclick="loadForm('.trim($package->id).');" data-id="'.$package->id.'"><i class="fa fa-pencil text-success"></i></a>';
							$html.='<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deletePackage" data-id="'.$package->id.'" ><i class="fa fa-trash text-danger"></i></a>';
							$html.='<h2 class="package_name">'.$package->name.'</h2>';
							$html.='<h1 class="package_price"><span>&#8377;</span>'.$package->price.'</h1>';
							$html.='<div class="package_delivery"><div class="row"><div class="col-sm-6">Delivery</div><div class="col-sm-6">'.$package->total_delivery.'</div></div></div>';
						$html.='</div>';
					$html.='</div>';
				}else if(Auth::user()->user_type=='pharmacy'){
					$html.='<div class="col-sm-4">';
						$html.='<div class="package_box">';
							$html.='<h2 class="package_name">'.$package->name.'</h2>';
							$html.='<h1 class="package_price"><span>&#8377;</span>'.$package->price.'</h1>';
							$html.='<div class="package_delivery"><div class="row"><div class="col-sm-6">Delivery</div><div class="col-sm-6">'.$package->total_delivery.'</div></div></div>';
							$html.='<div class="payment_btn"><a href="" class="payment_btn_link">Pay</a></div>';
						$html.='</div>';
					$html.='</div>';
				}
			}
		}
		echo $html;
	}
	public function loadForm($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		 
        $data = Packages::find($id);
		
        $html = view('packages.create')->with(["data" => $data])->render();

        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
    	$params = $request->all();
		
		$validation = 'required|unique:package,name';
        if (!empty($params['package_id'])) {
            $validation = 'required|unique:package,name,'.$params['package_id'];
        }

        $validator = Validator::make($params, [
            'name' => $validation
        ]);

        if ($validator->fails()) {
        	return response()->json([
	            'status_code' => 400,
	            'message'     => $validator->errors()->all(),
	        ]);
        }

        $updateData = [
         	'name' => $params['name'],
			'price' => $params['price'],
			'total_delivery' => $params['total_delivery']
        ];

        if (!empty($params['package_id'])) {
            $msg = 'Record updated successfully';
			$updateData['is_active'] = $params['is_active'];
			$updateData['updated_at'] = date('Y-m-d H:i:s');
        } else {
			$updateData['is_active'] = 1;
			$updateData['is_delete'] = 0;
			$updateData['created_at'] = date('Y-m-d H:i:s');
            $msg = 'Record saved successfully';
        }

        Packages::updateOrCreate(['id' => $request->package_id], $updateData );

        return response()->json([
            'status_code' => 200,
            'message'     => $msg,
        ], 200);
    }
	
	public function delete($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$Packages = Packages::find($id);
		if($Packages){
			$Packages->is_delete = 1;
			$Packages->save();
		}
		return redirect(route('user.index'))->with('success_message', trans('Deleted Successfully'));
	}
}
