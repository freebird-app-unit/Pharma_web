<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\PillShape;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Storage;
use Image;
use File;
use Validator;
 
class PillShapeController extends Controller
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
		$data['page_title'] = 'Pill Shape';
		$data['page_condition'] = 'page_pill_shape';
		$data['site_title'] = 'Pill Shape | ' . $this->data['site_title'];
        return view('pill_shape.index', $data);
    }
	public function list()
    {
		$data = PillShape::get();
	      
        return Datatables::of($data)
            ->addIndexColumn()
			->addColumn('action', function ($row) {
                $btn = '<a class="action-icon" data-toggle="modal" href="javascript:void(0)" onclick="loadForm('.trim($row->id).');" data-id="'.$row->id.'"><i class="fa fa-pencil text-success"></i></a> ';
                   $btn .= '<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deletePillShape" data-id="'.$row->id.'" ><i class="fa fa-trash text-danger"></i></a>';
                return $btn;
            })
			->editColumn('image', function ($row) {
                if (!empty($row->image) && file_exists(storage_path('app/public/uploads/piil_shape/'.$row->image))){
                    $image_path = asset('storage/app/public/uploads/pill_shape/' . $row->image);
                    $image = '<img id="image_'.$row->EpisodeId.'" src="'.$image_path.'"  class="img-responsive img-thumbnail" width="100">';
                } else {
                    $image = '';
                }
                return $image;
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
	}
	public function loadForm($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		 
        $data = PillShape::find($id);
		
        $html = view('pill_shape.create')->with(["data" => $data])->render();

        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
    	$params = $request->all();
		
		$validation = 'required|unique:pill_shape';
        if (!empty($params['pill_shape_id'])) {
            $validation = 'required';
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
		
		$image_name = $params['hidden_image'];

        if ($request->hasFile('image')) {
            
            $filename = storage_path('app/public/uploads/piil_shape/' . $image_name);
            
            if (File::exists($filename)) {
                File::delete($filename);
            }

            $image         = $request->file('image');
            $image_name = time() . '.' . $image->getClientOriginalExtension();

            $img = Image::make($image->getRealPath());
            $img->stream(); // <-- Key point

            Storage::disk('public')->put('uploads/piil_shape/'.$image_name, $img, 'public');
        }

        $updateData = [
         	'name' => $params['name'],
			'image' => $image_name
        ];

        if (!empty($params['pill_shape_id'])) {
            $msg = 'Record updated successfully';
        } else {
            $msg = 'Record saved successfully';
        }

        PillShape::updateOrCreate(['id' => $request->pill_shape_id], $updateData );

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
		$pill_shape = PillShape::find($id);
		
		if (!empty($pill_shape->image)) {

            $filename = storage_path('app/public/uploads/piil_shape/' . $pill_shape->image);
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		if($pill_shape){
			$pill_shape->delete();
		}
	}
	
	public function delete_image(Request $request)
    {
		$id = $request->id;
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		
		$pill_shape = PillShape::find($id);
		
		if (!empty($pill_shape->image)) {

            $filename = storage_path('app/public/uploads/piil_shape/' . $pill_shape->image);
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		$pill_shape->image = '';
        $pill_shape->save();

	}
}
