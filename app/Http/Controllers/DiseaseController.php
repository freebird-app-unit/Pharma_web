<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Disease;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
 
class DiseaseController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$data['page_title'] = 'Disease';
		$data['page_condition'] = 'page_disease';
		$data['site_title'] = 'Disease | ' . $this->data['site_title'];
        return view('disease.index', $data);
    }
	public function list()
    {
		$data = Disease::get();
	      
        return Datatables::of($data)
            ->addIndexColumn()
			->addColumn('action', function ($row) {
                $btn = '<a class="action-icon" data-toggle="modal" href="javascript:void(0)" onclick="loadForm('.trim($row->id).');" data-id="'.$row->id.'"><i class="fa fa-pencil text-success"></i></a> ';
                   $btn .= '<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deleteDisease" data-id="'.$row->id.'" ><i class="fa fa-trash text-danger"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
	}
	public function loadForm($id)
    {
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		 
        $data = Disease::find($id);
		
        $html = view('disease.create')->with(["data" => $data])->render();

        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
    	$params = $request->all();
		
		$validation = 'required|unique:disease,name';
        if (!empty($params['disease_id'])) {
            $validation = 'required|unique:disease,name,'.$params['disease_id'];
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
         	'name' => $params['name']
        ];

        if (!empty($params['disease_id'])) {
            $msg = 'Record updated successfully';
        } else {
            $msg = 'Record saved successfully';
        }

        Disease::updateOrCreate(['id' => $request->disease_id], $updateData );

        return response()->json([
            'status_code' => 200,
            'message'     => $msg,
        ], 200);
    }
	
	public function delete($id)
    {
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$disease = Disease::find($id);
		if($disease){
			$disease->delete();
		}
		return redirect(route('user.index'))->with('success_message', trans('Deleted Successfully'));
	}
}
