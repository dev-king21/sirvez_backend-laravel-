<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Project_site;
use App\Room;
use App\Site;
use App\Task;
use App\Project;
use App\Room_photo;
use App\Notification;
class RoomController extends Controller
{
    public function updateRoom(Request $request){
       //return response()->json($request);
        $v = Validator::make($request->all(), [
            //company info
            //'customer_id' => 'required',
            'project_id' => 'required',
            'site_id' => 'required',
            'room_number' => 'required',
            'estimate_day' => 'required',
            'estimate_time' => 'required',
            'notes' => 'required'
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $room = array();
        $id = $request->id;
        //$room['company_id'] = $request->customer_id;
        $room['company_id'] = Project::whereId($request->project_id)->select('company_id')->first()->company_id;
        $room['project_id']  = $request->project_id;
        $room['site_id']  = $request->site_id;
        $room['department_id']  = $request->department_id;
        $room['building_id']  = $request->building_id;
        $room['floor_id']  = $request->floor_id;
        $room['room_number']  = $request->room_number;
        $room['estimate_day']  = $request->estimate_day;
        $room['estimate_time']  = $request->estimate_time;
        $room['notes']  = $request->notes;
        if($request->has('ceiling_height'))
            $room['ceiling_height']  = $request->ceiling_height;
        if($request->has('ceiling'))
            $room['ceiling']  = $request->ceiling;
        if($request->has('wall'))
            $room['wall']  = $request->wall;
        if($request->has('asbestos'))
            $room['asbestos']  = $request->asbestos;
        $action = "updated";
        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined"){
            $room['created_by']  = $request->user->id;
            $room = Room::create($room);
            $id = $room->id;
            $action = "created";
        }
        else{
            $room['updated_by'] = $request->user->id;
            Room::whereId($id)->update($room);
        }
        //remove room_photh using room_array
        $imgs = Room_photo::where('room_id',$id)->get();
        $res_val = array();
        foreach($imgs as $key => $row){
            if(strpos($request->img_array,$row->img_name)===false) 
            Room_photo::whereId($row->id)->delete();
        }

        $images = $request->file('room_img');
        $n = 0;
        if(isset($images) && count($images) > 0 ){
            foreach($images as $img_file) {
                if (isset($img_file)) {
                    $n++;
                    $fileName = time().'_'.$n.'.'.$img_file->extension();  
                    $img_file->move(public_path('upload\img'), $fileName);
                    Room_photo::create(['room_id'=>$id,'user_id'=>$request->user->id,'img_name'=>$fileName]);
                }
            }
        }

        //$notice_type ={1:pending_user,2:createcustomer 3:project 4:site 5:room}  
        $insertnotificationndata = array(
            'notice_type'		=> '4',
            'notice_id'			=> $id,
            'notification'		=> $room['room_number'].' have been '.$action.' by  '.$request->user->first_name.').',
            'created_by'		=> $request->user->id,
            'company_id'		=> $request->customer_id,
            'created_date'		=> date("Y-m-d H:i:s"),
            'is_read'	    	=> 0,
        );

        $response = ['status'=>'success', 'msg'=>'Room Saved Successfully!'];  
        return response()->json($response);
    }
    public function deleteRoom(Request $request)
    {
        //$request = {'id':{}}
        Room::where(['id'=>$request->id])->delete();
        Room_photo::where(['room_id'=>$request->id])->delete();
        Task::where(['room_id'=>$request->id])->delete();
        $res["status"] = "success";
        return response()->json($res);
    }
    public function roomInfo(Request $request){
        $res = array();
        if ($request->has('id')) {
            $room = Room::where('rooms.id',$request->id)
            ->leftJoin('projects','projects.id','=','rooms.project_id')
            ->select('rooms.*','projects.project_name')->first(); 
            $room['img_files'] = Room_photo::where('room_id',$request->id)->get();
            $res["room"] = $room;
        }
        if(isset($request->customer_id)&& $request->customer_id>0){
            $res['projects'] = Project::where('company_id',$request->customer_id)->get();
            $res['sites'] = Site::where('company_id',$request->customer_id)->get();
        }
        if(isset($request->project_id)&& $request->project_id>0){
            $company_id = Project::whereId($request->project_id)->first()->company_id;
            $res['sites'] = Site::where('company_id',$company_id)->get();
        }
        $res['status'] = "success";
        return response()->json($res);
    }
}
