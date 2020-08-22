<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Department;
use App\Building;
use App\Floor;
use App\Room;
use App\Site;
use App\Company_customer;
use Illuminate\Support\Facades\Validator;
class FloorController extends Controller
{
    public function updateFloor(request $request){
        $v = Validator::make($request->all(), [
            //company info
            'site_id' => 'required',
            'department_id' => 'required',
            'building_id' => 'required',
            'floor_name' => 'required',
            'status' => 'required',
           
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
        $floor['status']  = $request->status;
        if($request->hasFile('upload_img')){
            $fileName = time().'floor.'.$request->upload_img->extension();  
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
        $rooms = Room::where('rooms.floor_id',$request->id)
        ->leftJoin('buildings','buildings.id','=','rooms.building_id')
        ->leftJoin('floors','floors.id','=','rooms.floor_id')
        ->leftJoin('projects','projects.id','=','rooms.project_id')
        ->select('rooms.*','buildings.building_name','floors.floor_name','projects.project_name')
        ->get();
        $res['rooms'] = $rooms;
        $res["status"] = "success";
        return response()->json($res);
    }
    public function getFloorInfo(Request $request){
        $res = array();
        if ($request->has('id')) {
            $floor = Floor::whereId($request->id)->first();
            $res["floor"] = $floor;
        }
        if($request->user->user_type ==1){
            $customer_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
            $res['sites'] = Site::whereIn('id',$customer_id)->get();
        }
        else{
            $res['sites'] = Site::where('id',$request->user->company_id)->get();
        }
        $res['department_id'] = building::whereId($request->building_id)->first()->department_id;
        $res['departments'] = Department::where('site_id',$request->site_id)->get();
        $res['buildings'] = Building::where('site_id',$request->site_id)->get();
        $res['status'] = "success";
        return response()->json($res);
    }
}
