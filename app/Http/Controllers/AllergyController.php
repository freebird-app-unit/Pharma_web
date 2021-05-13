<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Allergy;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
 
class AllergyController extends Controller
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
		$data['page_title'] = 'Allergy';
		$data['page_condition'] = 'page_allergy';
		$data['site_title'] = 'Allergy | ' . $this->data['site_title'];
        return view('allergy.index', $data);
    }
	public function list()
    {
		$data = Allergy::get();
	      
        return Datatables::of($data)
            ->addIndexColumn()
			->addColumn('action', function ($row) {
                $btn = '<a class="action-icon" data-toggle="modal" href="javascript:void(0)" onclick="loadForm('.trim($row->id).');" data-id="'.$row->id.'"><i class="fa fa-pencil text-success"></i></a> ';
                   $btn .= '<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deleteAllergy" data-id="'.$row->id.'" ><i class="fa fa-trash text-danger"></i></a>';
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
		 
        $data = Allergy::find($id);
		
        $html = view('allergy.create')->with(["data" => $data])->render();

        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
    	$params = $request->all();
		
		$validation = 'required|unique:allergies,allergy_name';
        if (!empty($params['allergy_id'])) {
            $validation = 'required|unique:allergies,allergy_name,'.$params['allergy_id'];
        }

        $validator = Validator::make($params, [
            'allergy_name' => $validation
        ]);

        if ($validator->fails()) {
        	return response()->json([
	            'status_code' => 400,
	            'message'     => $validator->errors()->all(),
	        ]);
        }

        $updateData = [
         	'allergy_name' => $params['allergy_name'],
            'user_id' => $params['user_id']
        ];

        if (!empty($params['allergy_id'])) {
            $msg = 'Record updated successfully';
        } else {
            $msg = 'Record saved successfully';
        }

        Allergy::updateOrCreate(['id' => $request->allergy_id], $updateData );

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
		$allergy = Allergy::find($id);
		if($allergy){
			$allergy->delete();
		}
		return redirect(route('user.index'))->with('success_message', trans('Deleted Successfully'));
	}
}
