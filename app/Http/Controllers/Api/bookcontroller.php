<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\books;
use DB;
class bookcontroller extends Controller
{
    public function get_test()
    {
    	$data = books::all();
    	return response($data, 200);
    }

    public function add_test(Request $request)
    {
    	$b = new books();
    	$b->name = 'keshariya';
    	$b->save();
    }
}
