<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Department;
use App\Building;
use App\Floor;
use App\Room;
use Illuminate\Support\Facades\Validator;
class DepartmentController extends Controller
{
    public function updateDepartment(request $request){
        $v = Validator::make($request->all(), [
            //company info
            'site_id' => 'required',
            'department_name' => 'required',
            'colour' => 'required',
            'archive' => 'required'
           
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $department = array();
        $id = $request->id;
        $department['site_id']  = $request->site_id;
        $department['department_name']  = $request->department_name;
        $department['colour']  = $request->colour;
        $department['archive']  = $request->archive;
        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined"){
            $department['created_by']  = $request->user->id;
            Department::create($department);
        }
        else{
            $department['updated_by']  = $request->user->id;
            Department::whereId($id)->update($department);
        }
        $res["status"] = "success";
        return response()->json($res);
    }
    public function deleteDepartment(Request $request){
        //$stiker = {stiker_id}
        Department::where(['id'=>$request->id])->delete();
        $res["status"] = "success";
        
        return response()->json($res);
    }
    public function departmentList(Request $request){
        $res = array();
        $departments= Department::withCount('buildings')->where('site_id',$request->site_id)->get();
        foreach($departments as $key =>$department){
            $departments[$key]['floors_count'] = Floor::where('department_id',$department->id)->count();
            $departments[$key]['rooms_count'] = Room::where('department_id',$department->id)->count();
        }
        $res['department'] = $departments;
        $res["status"] = "success";
        return response()->json($res);
    }
    public function departmentInfo(Request $request){
        $res = array();
        $res['department'] = Department::whereId($request->id)->first();
        $res["status"] = "success";
        return response()->json($res);
    }
}
