<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Notification;
use App\Company;
use App\Company_customer;
use App\Project;
use App\Room;
use App\User;
use App\Site;
use App\Project_user;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function updateTask(Request $request){
       
        $v = Validator::make($request->all(), [
            'task' =>'required',
            'company_id' => 'required',
            'project_id' => 'required',
            'site_id' => 'required',
            'room_id' => 'required',
            'due_by_date' => 'required',
            'priority' => 'required'
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $task = array();
        $id = $request->id;
        $task['task'] = $request->task;
        $task['company_id'] = $request->company_id;
        $task['project_id']  = $request->project_id;
        $task['site_id']  = $request->site_id;
        $task['room_id']  = $request->room_id;
        $task['due_by_date']  = $request->due_by_date;
        $task['priority']  = $request->priority;
        $task['description']  = $request->description;
        $action = "updated";
        if($request->hasFile('task_img')){

            $fileName = time().'task.'.$request->task_img->extension();  
            $request->task_img->move(public_path('upload\img'), $fileName);
            $task['task_img']  = $fileName;
        }
        if(!isset($id) || $id=="" || $id=="null" || $id=="undefined"){
            $task['created_by']  = $request->user->id;
            $result = Task::create($task);
            $id = $result->id;
            $action = "created";
            if($request->has('assign_to'))
            {
                Project_user::where(['project_id'=>$id,'type'=>'2'])->delete();
                $array_res = array();
                $array_res =json_decode($request->assign_to,true);
                foreach($array_res as $row)
                {
                    Project_user::create(['project_id'=>$id,'user_id'=>$row,'type'=>'2']);

                }
            }
        }
        else{
            $task['updated_by'] = $request->user->id;
            Task::whereId($id)->update($task);
            if($request->has('assign_to'))
            {
                Project_user::where(['project_id'=>$id,'type'=>'2'])->delete();
                $array_res = array();
                $array_res =json_decode($request->assign_to,true);
                foreach($array_res as $row)
                {
                    Project_user::create(['project_id'=>$id,'user_id'=>$row,'type'=>'2']);

                }
            }
        }
       //$notice_type ={1:pending_user,2:createcustomer 3:project 4:task}  
       $insertnotificationndata = array(
        'notice_type'		=> '4',
        'notice_id'			=> $id,
        'notification'		=> $task['task'].' have been '.$action.' by  '.$request->user->first_name.' ('.$request->user->company_name.').',
        'created_by'		=> $request->user->id,
        'company_id'		=> $request->company_id,
        'created_date'		=> date("Y-m-d H:i:s"),
        'is_read'	    	=> 0,
        );
        Notification::create($insertnotificationndata);

        $response = ['status'=>'success', 'msg'=>'Task Saved Successfully!'];  
        return response()->json($response);
    }
    public function deleteTask(Request $request)
    {
        //$request = {'id':{}}
        Task::where(['id'=>$request->id])->delete();
        $res["status"] = "success";
        return response()->json($res);
    }
    public function taskList(Request $request){
        $res = array();
        if($request->user->user_type > 1)
            $tasks = Task::where('tasks.company_id',$request->user->company_id)
                ->leftJoin('projects','projects.id','=','tasks.project_id')
                ->leftJoin('sites','sites.id','=','tasks.site_id')
                ->leftJoin('rooms','rooms.id','=','tasks.room_id')
                ->select('tasks.*','projects.project_name','sites.site_name','rooms.room_number')
                ->get();
        else{
            $customer_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
            $tasks = Task::whereIn('tasks.company_id',$customer_id)
                ->leftJoin('projects','projects.id','=','tasks.project_id')
                ->leftJoin('sites','sites.id','=','tasks.site_id')
                ->leftJoin('rooms','rooms.id','=','tasks.room_id')
                ->select('tasks.*','projects.project_name','sites.site_name','rooms.room_number')
                ->get();
        }
        $res["tasks"] = $tasks;
        $res['status'] = "success";
        return response()->json($res);
    }
    public function getTaskInfo(Request $request){
        //return response()->json($request);
        if ($request->has('id')) {
            $id = $request->id;
        
            $res = array();
            $res['status'] = 'success';
            $res['task'] = Task::whereId($id)->first();
            $res['task']['assign_to'] = Project_user::where(['project_id'=>$id,'type'=>'2'])->pluck('user_id');
        }
        if($request->has('company_id')){
            $company_id = $request->company_id;
            $res['customer'] = Company::where('id',$company_id)->get();
            $res['project'] = Project::where('company_id',$company_id)->get();
            $res['customer_site'] = Site::where('company_id',$company_id)->get();
            $res['room'] = Room::where('company_id',$company_id)->get();
            $company_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');            
        }
        else{
            $company_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
            $res['customer'] = Company::whereIn('id',$company_id)->get();
            $res['project'] = Project::whereIn('company_id',$company_id)->get();
            $res['customer_site'] = Site::whereIn('company_id',$company_id)->get();
            $res['room'] = Room::whereIn('company_id',$company_id)->get();
        }
       
        if($request->user->user_type ==1||$request->user->user_type ==2)
            $com_id = $request->user->company_id;
        else
            $com_id = Company_customer::where('customer_id',$request->user->company_id)->first()->company_id;           
        
        $res['assign_to'] = User::where('company_id',$com_id)->whereIn('user_type',[1,5])->where('status',1)->get();
        
        return response()->json($res);
    }
}
