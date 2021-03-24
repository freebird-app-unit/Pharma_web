<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\PillColor;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
 
class PillColorController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Pill Color';
		$data['page_condition'] = 'page_pill_color';
		$data['site_title'] = 'Pill Color | ' . $this->data['site_title'];
        return view('pill_color.index', $data);
    }
	public function list()
    {
		$data = PillColor::get();
	      
        return Datatables::of($data)
            ->addIndexColumn()
			->addColumn('action', function ($row) {
                $btn = '<a class="action-icon" data-toggle="modal" href="javascript:void(0)" onclick="loadForm('.trim($row->id).');" data-id="'.$row->id.'"><i class="fa fa-pencil text-success"></i></a> ';
                   $btn .= '<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deletePillColor" data-id="'.$row->id.'" ><i class="fa fa-trash text-danger"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
	}
	public function loadForm($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		 
        $data = PillColor::find($id);
		
        $html = view('pill_color.create')->with(["data" => $data])->render();

        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
    	$params = $request->all();
		
		$validation = 'required|unique:pill_color';
        if (!empty($params['pill_color_id'])) {
            $validation = 'required';
        }

        $validator = Validator::make($params, [
            'name' => $validation,
            'color' => 'required',
        ]);

        if ($validator->fails()) {
        	return response()->json([
	            'status_code' => 400,
	            'message'     => $validator->errors()->all(),
	        ]);
        }

        $updateData = [
         	'name' => $params['name'],
			'color' => $params['color']
        ];

        if (!empty($params['pill_color_id'])) {
            $msg = 'Record updated successfully';
        } else {
            $msg = 'Record saved successfully';
        }

        PillColor::updateOrCreate(['id' => $request->pill_color_id], $updateData );

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
		$pill_color = PillColor::find($id);
		if($pill_color){
			$pill_color->delete();
		}
		return redirect(route('user.index'))->with('success_message', trans('Deleted Successfully'));
	}
}
