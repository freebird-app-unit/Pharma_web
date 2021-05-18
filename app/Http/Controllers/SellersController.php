<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\new_pharmacies;
use App\new_pharma_logistic_employee;
use DB;
use Auth;
use Storage;
use Image;
use File;
use App\new_order_history;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

use App\new_users;
use App\new_logistics;

class SellersController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		/*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$pharmacy_list = new_pharmacies::select('new_pharmacies.id','new_pharmacies.name')->get();
		$data['pharmacy_list'] = $pharmacy_list;
		$data['page_title'] = 'Sellers';
		$data['page_condition'] = 'page_sellers';
		$data['site_title'] = 'Sellers | ' . $this->data['site_title'];
        return view('sellers.index', $data);
    }
	public function getlist()
    {
		$user_id = Auth::user()->user_id;
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		$search_pharmacy=(isset($_POST['search_pharmacy']) && $_POST['search_pharmacy']!='')?$_POST['search_pharmacy']:'';
		//get list
		if(Auth::user()->user_type=='admin'){
			$user_detail = new_pharma_logistic_employee::select('new_pharma_logistic_employee.*')->where('user_type','seller')->where('is_delete','1');
		}else{
			$user_detail = new_pharma_logistic_employee::select('new_pharma_logistic_employee.*')->where(['user_type'=>'seller','is_active'=>'1','is_delete'=>'1'])->where('pharma_logistic_id',$user_id);
		}

		if($searchtxt!=''){
			$user_detail= $user_detail->where(function ($query) use($searchtxt) {
                $query->where('name', 'like','%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
            });
		}
		if($search_pharmacy!=''){
			$user_detail= $user_detail->where('pharma_logistic_id', $search_pharmacy);
		}
		$total = $user_detail->count();
		$total_page = ceil($total/$per_page);

		$user_detail = $user_detail->orderby('new_pharma_logistic_employee.created_at','DESC');
		$user_detail = $user_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				$updated_at = ($user->updated_at!='')?date('d-M-Y',strtotime($user->updated_at)):'';
				$image_url = '';
				if($user->profile_image!=''){
					$destinationPath = base_path() . '/storage/app/public/uploads/new_seller/'.$user->profile_image;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/storage/app/public/uploads/new_seller/'.$user->profile_image;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				/* $order_detail = new_order_history::select('new_order_history.id')
					->where('new_order_history.logistic_user_id',$user->id)
					->where('new_order_history.order_status','complete');
					$total_complete_order = $order_detail->count(); */
					
				$total_res = DB::table('new_orders')->select('new_orders.*')
				->where('order_status','incomplete')->where('process_user_type','=','seller')->where('process_user_id', $user->id);
				$total_pending_order= $total_res->count();
			
				$pharmacy_name = get_name("new_pharmacies","name",$user->pharma_logistic_id);	
				$html.='<tr>
					<td><img src="'.$image_url.'" width="50"/></td>
					<td>'.$user->name.'</td>
					<td>'.$user->email.'</td>
					<td>'.$user->mobile_number.'</td>
					<td>'.$user->address.'</td>
					<td>'.$pharmacy_name.'</td>
					<td>'.$total_pending_order.'</td>';
						
					$html.='<td><a class="btn btn-success waves-effect waves-light" href="'.url('/seller/detail/'.$user->id).'" title="Detail"><i class="fa fa-eye"></i></a><a class="btn btn-info waves-effect waves-light" href="'.url('/seller/edit/'.$user->id).'" title="Edit user"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteUser" href="javascript:;" title="Delete user"><i class="fa fa-trash"></i></a>';
					if($user->is_active == 1){
                        $html.='<a href="'.url('/seller/'.$user->id.'/inactive/').'" onClick="return confirm(\'Are you sure you want to inactive this?\');" rel="tooltip" title="InActive" class="btn btn-default btn-xs"><i class="fa fa-circle text-success"></i></a>';  
                    }else{ 
                    	$html.='<a href="'.url('/seller/'.$user->id.'/active/').'" onClick="return confirm(\'Are you sure you want to active this?\');" rel="tooltip" title="Active" class="btn btn-default btn-xs"><i class="fa fa-circle text-danger"></i></a>';
                    }
					$html.='</td>';
				$html.='</tr>';
			}
			if($page==1){
				$prev='disabled';
			}else{
				$prev='';
			}
			if($total_page==$page){
				$next='disabled';
			}else{
				$next='';
			}
			$pagination.='<li class="page-item '.$prev.'">
						<a class="page-link" onclick="getuserlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getuserlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
					</li>';
					$from = ($per_page*($page-1));
					if($from<=0){$from=1;}
					$to = ($page*$per_page);
					if($to>=$total){$to= $total;}
			$total_summary.='&nbsp;&nbsp;'.$from.'-'.$to.' of '.$total;
		}else{
			$html.="<tr><td colspan='7'>No record found</td></tr>";
		}
		
		echo $html."##".$pagination."##".$total_summary;
	}
	public function create()
    {
		/*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$data['page_title'] = 'Create Sellers';
		$data['page_condition'] = 'page_user_sellers';
		$data['site_title'] = 'Create Seller | ' . $this->data['site_title'];
		$data['pharmacy_list'] = new_pharmacies::where('is_active','1')->get();
		return view('sellers.create', array_merge($this->data, $data));
	}
	public function store(Request $request){
		$validate = $request->validate([
			'user_type' => 'required',
			'name' => 'required',
			'email' => 'required|email|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,email|max:255',
			'mobile_number' => 'required|digits:10|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,mobile_number',
			'address' => 'required',
			'profile_image' => 'image|max:1024',
			'password' => 'required|min:4|max:255',
			'confirm_password' => 'required|min:4|max:255|same:password',
			'address' => 'required',
			'block' => 'required',
			'street' => 'required',
			'pincode' => 'required',
		]);
		
		if($validate){
			$user = new new_pharma_logistic_employee();
			
			if ($request->hasFile('image')) {
				$file1 = $request->file('image');
				$fileName = time().'.'.$file1->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_seller';
				$file1->move($destinationPath, $fileName);
				$user->profile_image = $fileName;
				
			}else{
				$user->profile_image = (isset($request->profile_image))?$request->profile_image:'';
			}

			$user->user_type = $request->user_type;
			$user->pharma_logistic_id = (Auth::user()->user_type != 'pharmacy')?($request->pharmacy):($request->pharma_logistic_id);
			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->address  = $request->address;
			$user->block  = $request->block;
			$user->street  = $request->street;
			$user->pincode  = $request->pincode;
			$user->password = Hash::make($request->password);
			$user->created_at = date('Y-m-d H:i:s');
			if($user->save()){
				return redirect(route('seller.index'))->with('success_message', trans('Added Successfully'));
			}
		}
	}
	public function edit($id)
    {
		$user_id = Auth::user()->user_id;
		
		/*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/

		$user_detail = new_pharma_logistic_employee::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}

		$data = array();
		$data['page_title'] = 'Edit Sellers';
		$data['page_condition'] = 'page_client_seller';
		$data['user_detail'] = $user_detail;
		$data['site_title'] = 'Edit Seller | ' . $this->data['site_title'];
		$data['pharmacy_list'] = new_pharmacies::where('is_active','1')->get();
		return view('sellers.create', array_merge($this->data, $data));
	}
	public function update(Request $request, $id){
		$validate = $request->validate([
			'user_type' => 'required',
			'name' => 'required',
			'email' =>  ['required',Rule::unique('new_pharma_logistic_employee','email')->ignore($id),Rule::unique('new_users','email')->ignore($id),Rule::unique('new_pharmacies','email')->ignore($id),Rule::unique('new_logistics','email')->ignore($id)],
			
			'mobile_number' =>  ['required',Rule::unique('new_pharma_logistic_employee','mobile_number')->ignore($id),Rule::unique('new_users','mobile_number')->ignore($id),Rule::unique('new_pharmacies','mobile_number')->ignore($id),Rule::unique('new_logistics','mobile_number')->ignore($id)],
			'address' => 'required',
			'profile_image' => 'image|max:1024',
			'address' => 'required',
			'block' => 'required',
			'street' => 'required',
			'pincode' => 'required',
		]);
		if($validate){
			$user = new_pharma_logistic_employee::find($id);
			if ($request->hasFile('profile_image')) {
				$file1 = $request->file('profile_image');
				$fileName = time().'.'.$file1->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_seller';
				$file1->move($destinationPath, $fileName);
				$user->profile_image = $fileName;
				
			}else{
				$user->profile_image = (isset($request->profile_image))?$request->profile_image:$user->profile_image;
			}
			$user->user_type = $request->user_type;
			$user->pharma_logistic_id = isset($request->pharmacy)?($request->pharmacy):$user->pharma_logistic_id;
			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->address  = $request->address;
			$user->block  = $request->block;
			$user->street  = $request->street;
			$user->pincode  = $request->pincode;
			$user->updated_at = date('Y-m-d H:i:s');
			
			if($user->save()){
				return redirect(route('seller.index'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}

	public function detail($id)
    {
		/*if(Auth::user()->user_type!='admin' && Auth::user()->user_type!='pharmacy'){
			return redirect(route('home'));
		}*/

		$user_detail = new_pharma_logistic_employee::where('id',$id)->first();
		$user_detail->pharmacy_name = '';

		$pharmacy_detail = new_pharmacies::select('name')->where('id',$user_detail->pharma_logistic_id)->first();
		if(isset($pharmacy_detail)){
			$user_detail->pharmacy_name = $pharmacy_detail->name;
		}

		$data['user_detail'] = $user_detail;
		$data['page_title'] = 'Detail seller';
		$data['page_condition'] = 'page_user_seller_detail';
		$data['site_title'] = 'Detail seller | ' . $this->data['site_title'];
		return view('sellers.detail', array_merge($this->data, $data));
	}

	public function delete($id)
    {
		$user_id = Auth::user()->user_id;
		/*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$user_detail = new_pharma_logistic_employee::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}
		$user = new_pharma_logistic_employee::find($id);
		$user->is_active=0;
		$user->is_delete='0';
		$user->mobile_number='';
		$user->email='';
		$user->save();
		return redirect(route('seller.index'))->with('success_message', trans('Deleted Successfully'));
	}
	public function setActivate($id){
		$user_id = Auth::user()->user_id;
		/*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$status_change = new_pharma_logistic_employee::where('id',$id)->first();
		$status_change->is_active=1;
		$status_change->save();
        return redirect(route('seller.index'))->with('success_message', trans('Active Successfully'));
    }
    public function setInactivate($id) {
    	$user_id = Auth::user()->user_id;
		/*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$status_change = new_pharma_logistic_employee::where('id',$id)->first();
		$status_change->is_active=0;
		$status_change->save();
        return redirect(route('seller.index'))->with('success_message', trans('InActive Successfully'));
    }
	public function delete_image(Request $request)
    {
		$id = $request->edit_id;
		$user = new_pharma_logistic_employee::find($id);
		if (!empty($user->profile_image)) {

            $filename = 'storage/app/public/uploads/new_seller' . $user->profile_image;
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		$user->profile_image = '';
        $user->save();

	}
}
