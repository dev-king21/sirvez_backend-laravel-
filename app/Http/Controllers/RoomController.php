<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Project_site;
use App\Room;
use App\Site;
use App\Task;
use App\Product;
use App\Project;
use App\Project_user;
use App\Room_photo;
use App\Notification;
use App\Company_customer;
use App\Department;
use App\Building;
use App\Floor;
class RoomController extends Controller
{
    public function updateRoom(Request $request){
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
            ->leftJoin('companies','companies.id','=','rooms.company_id')
            ->select('rooms.*','projects.project_name','companies.name as company_name')->first(); 
            $room['img_files'] = Room_photo::where('room_id',$request->id)->get();
            $res["room"] = $room;
            $products= Product::where('room_id',$request->id)->get();
            foreach($products as $key => $product)
            {
                $products[$key]['room_name'] = Room::whereId($product->room_id)->first()->room_number;
                $products[$key]['to_room_name'] = Room::whereId($product->to_room_id)->first()->room_number;
                $products[$key]['to_site_name'] = Site::whereId($product->to_site_id)->first()->site_name;
            }
            $res['products'] = $products;
            $tasks = Task::where('room_id',$request->id)->get();
            foreach($tasks as $key=>$row){
                $tasks[$key]['assign_to'] = Project_user::leftJoin('users','users.id','=','project_users.user_id')->where(['project_users.project_id'=>$row->id,'type'=>'2'])->pluck('users.first_name');
            }
            $res['tasks'] = $tasks;
        }
        if(isset($request->project_id)&& $request->project_id>0){
            $company_id = Project::whereId($request->project_id)->first()->company_id;
            $res['sites'] = Site::where('company_id',$company_id)->get();
            $res['projects'] = Project::where('company_id',$company_id)->get();
            $site_id = Site::where('company_id',$company_id)->pluck('id');
            $res['departments'] = Department::whereIn('site_id',$site_id)->get();
            $res['buildings'] = Building::whereIn('site_id',$site_id)->get();
            $res['floors'] = Floor::whereIn('site_id',$site_id)->get();
        }
        else if(isset($request->customer_id)&& $request->customer_id>0){
            $res['projects'] = Project::where('company_id',$request->customer_id)->get();
            $res['sites'] = Site::where('company_id',$request->customer_id)->get();
            $site_id = Site::where('company_id',$request->customer_id)->pluck('id');
            $res['departments'] = Department::whereIn('site_id',$site_id)->get();
            $res['buildings'] = Building::whereIn('site_id',$site_id)->get();
            $res['floors'] = Floor::whereIn('site_id',$site_id)->get();
        }
        else{
            if($request->user->user_type ==1){
                $customer_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
                $res['sites'] = Site::whereIn('id',$customer_id)->get();
                $res['projects'] = Project::whereIn('company_id',$customer_id)->get();
                
            }
            else{
                $res['sites'] = Site::where('id',$request->user->company_id)->get();
                $res['projects'] = Project::where('company_id',$request->user->$company_id)->get();
            }
            $res['department_id'] = building::whereId($request->building_id)->first()->department_id;
            $res['departments'] = Department::where('site_id',$request->site_id)->get();
            $res['buildings'] = Building::where('site_id',$request->site_id)->get();
            $res['floors'] = Floor::where('building_id',$request->building_id)->get();
        }
        $res['status'] = "success";
        return response()->json($res);
    }
}
