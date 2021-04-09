<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\Onboardingrequest;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Storage;
use Image;
use File;
use Mail;
use Illuminate\Support\Facades\Crypt;

use App\Termscondition;
use Illuminate\Validation\Rule;

class TermsconditionController extends Controller
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
		$data['page_title'] = 'Terms & condition';
		$data['page_condition'] = 'page_termscondition';
		$data['site_title'] = 'Terms & condition | ' . $this->data['site_title'];
        return view('termscondition.index', $data);
    }
	public function getlist()
    {
		$html='';
		$pagination='';
		$total_summary='';
		$user_detail = array();
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';

		//count total
		$Termscondition = Termscondition::select('id', 'file', 'created_at');

		if($searchtxt!=''){
			$Termscondition = $Termscondition->where(function ($query) use($searchtxt) {
                $query->where('file', 'like', '%'.$searchtxt.'%');
			});
		}
		
		
		$total_result = $Termscondition->get();
		$total = count($total_result);
		$total_page = ceil($total/$per_page);

		$Termscondition_detail = $Termscondition->orderby('created_at', 'DESC')->paginate($per_page,'','',$page);
		if(count($Termscondition_detail)>0){
			foreach($Termscondition_detail as $user){
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				$file_name = $user->file;

				$html.='<tr>
					<td>'.$file_name.'</td>
					<td>'.$created_at.'</td>';
					$html.='<td><a class="btn btn-info waves-effect waves-light" href="'.url('/termscondition/edit/'.$user->id.'/'.$user->user_type).'" title="Edit terms & condition"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteTermscondition" href="javascript:;" title="Delete terms & condition"><i class="fa fa-trash"></i></a>';
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
						<a class="page-link" onclick="gettermsconditionlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="gettermsconditionlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Create Terms & condition';
		$data['page_condition'] = 'page_termscondition_create';
		$data['site_title'] = 'Create Terms & condition | ' . $this->data['site_title'];
		return view('termscondition.create', array_merge($this->data, $data));
	}
	public function store(Request $request){
		$validation_arr = array(
			'file' => 'required|max:1024',
		);

		$validate = $request->validate($validation_arr);
		if($validate){
			$termscondition = new Termscondition;
			$put = 'uploads/terms_condition/';
			if ($request->hasFile('file')) {
				$filenameWithExt = $request->file('file')->getClientOriginalName();
				//Get just filename
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				// Get just ext
				$extension = $request->file('file')->getClientOriginalExtension();
				// Filename to store
				$fileNameToStore = $filename.'_'.time().'.'.$extension;
				// Upload Image
				$path = $request->file('file')->storeAs('public/uploads/terms_condition/',$fileNameToStore);
			
				$termscondition->file = $fileNameToStore;
			}
			$termscondition->created_at = date('Y-m-d H:i:s');

			if($termscondition->save()){
				$Onboardingrequest = Onboardingrequest::get();
				if(count($Onboardingrequest)>0){
					foreach($Onboardingrequest as $onboarding){
						//send email
						$data = [
							'name' => $onboarding->first_name.' '.$onboarding->last_name,
							'termscondition_id' => $termscondition->id,
							'onboarding_id' => $onboarding->id,
						];
						$email = $onboarding->email;
						$message = "";
						Mail::send('email.termscondition', $data, function ($message) use ($email) {
							$message->to($email)->subject('Accept new terms & condition');
						});
					}
				}
				return redirect(route('termscondition.index'))->with('success_message', trans('Added Successfully'));
			}
		}
	}
	public function edit($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$termscondition = Termscondition::find($id);
		$data = array();
		$data['page_title'] = 'Edit Terms & condition';
		$data['page_condition'] = 'page_termscondition_create';
		$data['termscondition'] = $termscondition;
		$data['site_title'] = 'Edit Terms & condition | ' . $this->data['site_title'];
		return view('termscondition.create', array_merge($this->data, $data));
	}

	public function update(Request $request, $id){
		$validation_arr = array(
			'file' => 'required|max:1024',
		);

		$validate = $request->validate($validation_arr);
		if($validate){
			$termscondition = Termscondition::find($id);
			$image_name = $termscondition->file;
			$put = 'uploads/terms_condition/';
			$storage_path = storage_path('app/public/uploads/terms_condition/');
			if ($request->hasFile('file')) {
				$filename = $storage_path . $image_name;
				if (File::exists($filename)) {
					File::delete($filename);
				}
				$filenameWithExt = $request->file('file')->getClientOriginalName();
				//Get just filename
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				// Get just ext
				$extension = $request->file('file')->getClientOriginalExtension();
				// Filename to store
				$fileNameToStore = $filename.'_'.time().'.'.$extension;
				// Upload Image
				$path = $request->file('file')->storeAs('public/uploads/terms_condition/',$fileNameToStore);
			
				$termscondition->file = $fileNameToStore;
			}
			
			$termscondition->updated_at = date('Y-m-d H:i:s');
			
			if($termscondition->save()){
				return redirect(route('termscondition.index'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}

	public function delete($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$Termscondition = Termscondition::find($id);
		$Termscondition->delete();
		return redirect(route('user.index'))->with('success_message', trans('Deleted Successfully'));
	}
	
	public function delete_image(Request $request)
    {
		$id = auth()->user()->id;
		$user = User::find($id);
		
		if (!empty($user->profile_image)) {

            $filename = storage_path('app/public/uploads/users/' . $user->profile_image);
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		$user->profile_image = '';
        $user->save();

	}
}
