<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notification;
use Illuminate\Support\Facades\Validator;
use App\Stiker;

class StikerController extends Controller
{
    public function UpdateStiker(Request $request){
        //$request = {category = {},user_id = {},file,img_flag}
        $res = array();
        $v = Validator::make($request->all(), [
            //company info
            'category_id' => 'required',
            'name' => 'required',
            'status' => 'required',
           
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $stiker_info = array();
        $id = $request->id;
        
        if ($request->has('stiker_img') && isset($request->stiker_img) && $request->stiker_img!='null') {
            $fileName = time().'stiker.'.$request->stiker_img->extension();  
            $request->stiker_img->move(public_path('upload\img'), $fileName);
            $stiker_info['stiker_img'] = $fileName;
        } else if (!isset($id) || $id==""|| $id=="null"|| $id=="undefined") {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input image file!'
            ]);
        }
        $stiker_info['category_id'] = $request->category_id;
        $stiker_info['name']  = $request->name;
        $stiker_info['user_id']  = $request->user->id;
        $stiker_info['status']  = $request->status;
        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined")
            stiker::create($stiker_info);
        else
            stiker::whereId($id)->update($stiker_info);
        $res["status"] = "success";
        return response()->json($res);
    }
    public function DeleteStiker(Request $request){
        //$stiker = {stiker_id}
        stiker::where(['id'=>$request->id])->delete();
        $res["status"] = "success";
        return response()->json($res);
    }
    public function getStikerInfo(Request $request){
        $res = array();
        $res['status'] = "success";
        $res['stiker'] = Stiker::whereId($request->id)->first();
        
        return response()->json($res);
    }
    
}
