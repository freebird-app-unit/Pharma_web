<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\new_users;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use App\notification_user;
use App\Notification;
use Storage;
use Image;
use File;
use Validator;
use Helper; 

class BroadCastNotificationController extends Controller
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
		$data['page_title'] = 'Announcement';
		$data['page_condition'] = 'page_broad_cast_notification';
		$data['site_title'] = 'Announcement | ' . $this->data['site_title'];
        return view('broad_cast_notification.index', $data);
    }
	public function list()
    {
		$data_list = Notification::select('broad_cast_unique')->where('broad_cast_unique','!=',"")->groupBy('broad_cast_unique')->orderBy('id', 'DESC')->get();
        $selected_array = array();
        foreach ($data_list as $data_list_key => $data_list_value) {
            $broad_cast_unique = $data_list_value->broad_cast_unique; 
            $selected_detail = Notification::select('id')->where('broad_cast_unique',$broad_cast_unique)->orderBy('id', 'DESC')->first();
            if($selected_detail){
                $selected_array[] = $selected_detail->id;
            }
        }
        $data = Notification::whereIn('id',$selected_array)->get();
        return Datatables::of($data)
            ->addIndexColumn()
			->addColumn('action', function ($row) {
                $btn = '';
                return $btn;
            })
			->editColumn('image', function ($row) {
                if (!empty($row->image) && file_exists(storage_path('app/public/uploads/broad_cast_notification/'.$row->image))){
                    $image_path = asset('storage/app/public/uploads/broad_cast_notification/' . $row->image);
                    $image = '<img id="image_'.$row->EpisodeId.'" src="'.$image_path.'"  class="img-responsive img-thumbnail" width="100">';
                } else {
                    $image = '';
                }
                return $image;
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
	}
	public function loadForm()
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
        $data = [];
        $html = view('broad_cast_notification.create')->with(["data" => $data])->render();
        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
		$admin_id = Auth::user()->id;
    	$params = $request->all();
        $validation = 'required';
        $validator = Validator::make($params, [
            'name' => $validation,
            'description' => $validation,
            'image' => 'mimes:jpeg,jpg,png',
        ],
        [
            'name.required' => 'The title field is required.',
        ]);
        if ($validator->fails()) {
        	return response()->json([
	            'status_code' => 400,
	            'message'     => $validator->errors()->all(),
	        ]);
        }
        $broad_cast_unique = md5(uniqid(rand(), true));
        $image_file = "";
        if ($request->hasFile('image')) {
            $image         = $request->file('image');
            $image_file = time() . '.' . $image->getClientOriginalExtension();
            $img = Image::make($image->getRealPath());
            $img->stream(); // <-- Key point
            Storage::disk('public')->put('uploads/broad_cast_notification/'.$image_file, $img, 'public');
        }
        $customer_list =  new_users::where('is_active',"1")->whereNotNull('fcm_token')->get();
        $fcm_token = array();
        $ids = array();
        $insert_data = array();
        foreach ($customer_list as $key => $customerdetail) {
            if($customerdetail->fcm_token!=''){
                $fcm_token[] = $customerdetail->fcm_token;
                $ids[] = $customerdetail->id;
            }
            $insert_data[] = array(
                'broad_cast_unique'=>$broad_cast_unique,
                'user_id'=> $customerdetail->id,
                'description' => $params['description'],
                'title' => $params['name'],
                'image'=>$image_file,
                'created_at'=>date('Y-m-d H:i:s'),
            );
        }
        Notification::insert($insert_data);
        $msg_body = $params['description'];
        $msg_title = $params['name'];
        if (count($fcm_token) > 0) {                  
            Helper::sendNotificationUser($fcm_token, $msg_body, $msg_title, $admin_id, 'admin', $customerdetail->id, 'user', $customerdetail->fcm_token);
        }
        $msg = 'Record saved successfully';
        return response()->json([
            'status_code' => 200,
            'message'     => $msg,
        ], 200);
    }
}
