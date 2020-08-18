<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Department;
use App\Building;
use App\Floor;
use App\Room;
use Illuminate\Support\Facades\Validator;
class FloorController extends Controller
{
    public function updateFloor(request $request){
        $v = Validator::make($request->all(), [
            //company info
            'site_id' => 'required',
            'department_id' => 'required',
            'building_id' => 'required',
            'floor_name' => 'required'
           
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $floor = array();
        $id = $request->id;
        $floor['site_id']  = $request->site_id;
        $floor['department_id']  = $request->department_id;
        $floor['building_id']  = $request->building_id;
        $floor['floor_name']  = $request->floor_name;
        if($request->hasFile('upload_img')){
            $fileName = time().'.'.$request->upload_img->extension();  
            $request->upload_img->move(public_path('upload\img'), $fileName);
            $floor['upload_img']  = $fileName;
        }

        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined"){
            $floor['created_by']  = $request->user->id;
            Floor::create($floor);
        }
        else{
            $floor['updated_by']  = $request->user->id;
            Floor::whereId($id)->update($floor);
        }
        $res["status"] = "success";
        return response()->json($res);
    }
    public function deleteFloor(Request $request){
        //$stiker = {stiker_id}
        Floor::where(['id'=>$request->id])->delete();
        $res["status"] = "success";
        
        return response()->json($res);
    }
    public function FloorList(Request $request){
        $res = array();
        $floors= Floor::withCount('rooms')->where('building_id',$request->building_id)->get();
        $res['floors'] = $floors;
        $res["status"] = "success";
        return response()->json($res);
    }
    public function floorInfo(Request $request){
        $res = array();
        $res['floor'] = Floor::whereId($request->id)->first();
        $res["status"] = "success";
        return response()->json($res);
    }
}
