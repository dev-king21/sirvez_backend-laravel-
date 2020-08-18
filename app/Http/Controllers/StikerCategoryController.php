<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Stiker_category;
use App\Stiker;

class StikerCategoryController extends Controller
{
    public function updateCategory(request $request){
        $v = Validator::make($request->all(), [
            //company info
            'name' => 'required',
            'description' => 'required'
           
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $category = array();
        $id = $request->id;
     
        $category['name']  = $request->name;
        $category['description']  = $request->description;
        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined")
            Stiker_category::create($category);
        else
            Stiker_category::whereId($id)->update($category);
        $res["status"] = "success";
        return response()->json($res);
    }
    public function deleteCategory(Request $request){
        //$stiker = {stiker_id}
        Stiker_category::where(['id'=>$request->id])->delete();
        $res["status"] = "success";
        
        return response()->json($res);
    }
    public function categoryList(Request $request){
        $res = array();
        $res['category'] = Stiker_category::withCount('stikers')->get();
        $res["status"] = "success";
        return response()->json($res);
    }
    public function getCategoryInfo(Request $request){
        $res = array();
        $res['category'] = Stiker_category::whereId($request->id)->first();
        $res['stickers'] =Stiker::where('category_id',$request->id)->get();
        $res["status"] = "success";
        return response()->json($res);
    }
}
