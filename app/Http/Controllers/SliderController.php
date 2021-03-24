<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Slider;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Storage;
use Image;
use File;
use Validator;
 
class SliderController extends Controller
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
		$data['page_title'] = 'Slider';
		$data['page_condition'] = 'page_slider';
		$data['site_title'] = 'Slider | ' . $this->data['site_title'];
        return view('slider.index', $data);
    }
	public function list()
    {
		$data = Slider::get();
	      
        return Datatables::of($data)
            ->addIndexColumn()
			->addColumn('action', function ($row) {
                   $btn = '<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deleteSlider" data-id="'.$row->id.'" ><i class="fa fa-trash text-danger"></i></a>';
                return $btn;
            })
			->editColumn('image', function ($row) {
                if (!empty($row->image) && file_exists(storage_path('app/public/uploads/slider/'.$row->image))){
                    $image_path = asset('storage/app/public/uploads/slider/' . $row->image);
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
		
        $html = view('slider.create')->with(["data" => $data])->render();

        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
    	$params = $request->all();
		
        $validation = 'required';
        
        $validator = Validator::make($params, [
            'image' => $validation
        ]);

        if ($validator->fails()) {
        	return response()->json([
	            'status_code' => 400,
	            'message'     => $validator->errors()->all(),
	        ]);
        }
		
		if ($request->hasFile('image')) {
            
            $image         = $request->file('image');
            $image_name = time() . '.' . $image->getClientOriginalExtension();

            $img = Image::make($image->getRealPath());
            $img->stream(); // <-- Key point

            Storage::disk('public')->put('uploads/slider/'.$image_name, $img, 'public');
        }

        $updateData = [
         	'image' => $image_name
        ];

        if (!empty($params['slider_id'])) {
            $msg = 'Record updated successfully';
        } else {
            $msg = 'Record saved successfully';
        }

        Slider::updateOrCreate(['id' => $request->slider_id], $updateData );

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
		$slider = Slider::find($id);
		
		if (!empty($slider->image)) {

            $filename = storage_path('app/public/uploads/piil_shape/' . $slider->image);
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		if($slider){
			$slider->delete();
		}
	}
}
