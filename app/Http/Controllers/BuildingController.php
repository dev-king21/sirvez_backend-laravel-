<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Department;
use App\Building;
use App\Floor;
use App\Room;
use Illuminate\Support\Facades\Validator;
class BuildingController extends Controller
{
    public function updateBuilding(request $request){
        $v = Validator::make($request->all(), [
            //company info
            'site_id' => 'required',
            'department_id' => 'required',
            'building_name' => 'required',
            'address' => 'required',
            'postcode' => 'required'
           
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $building = array();
        $id = $request->id;
        $building['site_id']  = $request->site_id;
        $building['department_id']  = $request->department_id;
        $building['building_name']  = $request->building_name;
        $building['address']  = $request->address;
        $building['postcode']  = $request->postcode;
        if($request->hasFile('upload_img')){
            $fileName = time().'.'.$request->upload_img->extension();  
            $request->upload_img->move(public_path('upload\img'), $fileName);
            $building['upload_img']  = $fileName;
        }

        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined"){
            $building['created_by']  = $request->user->id;
            Building::create($building);
        }
        else{
            $building['updated_by']  = $request->user->id;
            Building::whereId($id)->update($building);
        }
        $res["status"] = "success";
        $res['msg'] = "Data is saved";
        return response()->json($res);
    }
    public function deleteBuilding(Request $request){
        //$stiker = {stiker_id}
        Building::where(['id'=>$request->id])->delete();
        $res["status"] = "success";
        
        return response()->json($res);
    }
    public function buildingList(Request $request){
        $res = array();
        $buildings= Building::withCount('floors')->where('department_id',$request->department_id)->get();
        foreach($buildings as $key =>$building){
            $buildings[$key]['rooms_count'] = Room::where('building_id',$building->id)->count();
        }
        $res['building'] = $buildings;
        $res["status"] = "success";
        return response()->json($res);
    }
    public function buildingInfo(Request $request){
        $res = array();
        $res['building'] = Building::whereId($request->id)->first();
        $floors =Floor::withCount('rooms')->where('building_id',$request->id)->get();
        $res['floors'] = $floors;
        $res["status"] = "success";
        return response()->json($res);
    }
    public function getBuildingInfo(Request $request){
        $res = array();
        if ($request->has('id')) {
            $building = Building::where('buildings.id',$request->id)
            ->leftJoin('sites','sites.id','=','buildings.site_id')
            ->select('buildings.*','sites.site_name')->first(); 
            $res["building"] = $building;
        }
          
        $res['departments'] = Department::where('site_id',$request->site_id)->get();
        $res['status'] = "success";
        return response()->json($res);
    }
}
