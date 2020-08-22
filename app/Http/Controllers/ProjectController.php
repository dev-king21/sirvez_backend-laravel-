<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Project;
use App\Project_site;
use App\Site;
use App\Task;
use App\Notification;
use App\User;
use App\Room;
use App\Company;
use App\Product;
use App\Company_customer;
use App\Project_user;
class ProjectController extends Controller
{
    public function updateProject(Request $request){
        $v = Validator::make($request->all(), [
            //company info
            'customer_id' => 'required',
            'project_name' => 'required',
            'manager_id' => 'required',
            'contact_number' => 'required',
            'survey_start_date' => 'required',
            'project_summary' => 'required'
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $project = array();
        $id = $request->id;
        if($request->hasFile('upload_doc')){

            $fileName = time().'.'.$request->upload_doc->extension();  
            $request->upload_doc->move(public_path('upload\file'), $fileName);
            $project['upload_doc']  = $fileName;
        }
        $project['company_id'] = $request->customer_id;
        $project['project_name']  = $request->project_name;
        //$project['user_id']  = $request->user_id;
        $project['manager_id']  = $request->manager_id;
        $project['contact_number']  = $request->contact_number;
        $project['survey_start_date']  = $request->survey_start_date;
        $project['created_by']  = $request->user->id;
        $project['project_summary']  = $request->project_summary;   
        $action = "updated";
        if(!isset($id) || $id=="" || $id=="null" || $id=="undefined"){
            $project = Project::create($project);
            $action = "created";
            $id = $project->id;
            if($request->has('assign_to'))
            {
                $array_res = array();
                $array_res =json_decode($request->assign_to,true);
                foreach($array_res as $row)
                {
                    Project_user::create(['project_id'=>$id,'user_id'=>$row]);

                }
            }
        }
        else{
            Project::whereId($id)->update($project);
            if($request->has('assign_to'))
            {
                Project_user::where(['project_id'=>$id,'type'=>'1'])->delete();
                $array_res = array();
                $array_res =json_decode($request->assign_to,true);
                foreach($array_res as $row)
                {
                    Project_user::insert(['project_id'=>$id,'user_id'=>$row]);

                }
            }
        }

        //$notice_type ={1:pending_user,2:createcustomer 3:project}  
        $insertnotificationndata = array(
            'notice_type'		=> '3',
            'notice_id'			=> $id,
            'notification'		=> $project['project_name'].' have been '.$action.' by  '.$request->user->first_name.' ('.$request->user->company_name.').',
            'created_by'		=> $request->user->id,
            'company_id'		=> $request->company_id,
            'created_date'		=> date("Y-m-d H:i:s"),
            'is_read'	    	=> 0,
        );
        Notification::create($insertnotificationndata);

        $response = ['status'=>'success', 'msg'=>'Project Saved Successfully!'];  
        return response()->json($response);
    }
    public function deleteProject(Request $request)
    {
        $id = $request->id;
        Project::whereId($id)->update(['archived'=>1,'archived_day'=>date('Y-m-d')]);
        // Project::where(['id'=>$id])->delete();
        // Project_site::where('project_id',$id)->delete();
        // Room::where('project_id',$id)->delete();
        // Task::where('project_id',$id)->delete();
         $res["status"] = "success";
        return response()->json($res);
    }
    public function projectList(Request $request){
        $res = array();
        if($request->user->user_type==1){
            $id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
            $project_array = Project::whereIn('projects.company_id',$id)->where('archived',$request->archived)
            ->join('companies','companies.id','=','projects.company_id')
            ->join('users','users.id','=','projects.manager_id')
            ->select('projects.*', 'companies.name AS customer','users.first_name AS account_manager','users.profile_pic')->get();
        }
        else{
            $id = $request->user->company_id;
            $project_array = Project::where('projects.company_id',$id)->where('archived',$request->archived)
            ->join('companies','companies.id','=','projects.company_id')
            ->join('users','users.id','=','projects.manager_id')
            ->select('projects.*', 'companies.name AS customer','users.first_name AS account_manager','users.profile_pic')->get();
        }
        foreach($project_array as $key => $row){
            $project_array[$key]['site_count'] = Project_site::where('project_id',$row['id'])->count();
            $project_array[$key]['room_count'] = Room::where('project_id',$row['id'])->count();
            $project_array[$key]['messages'] = Notification::where('notice_type','3')->where('notice_id',$row['id'])->count();
        }
        $res["projects"] = $project_array;
        $res['status'] = "success";
        return response()->json($res);
    }
    public function projectDetail(Request $request){
        $res = array();
        $project = Project::where('projects.id',$request->id)
        ->leftJoin('companies','projects.company_id','=','companies.id')
        ->select('projects.*','companies.logo_img','companies.name AS company_name')->first();
        
        if(User::where('company_id',$project->company_id)->count() > 0)
            $project['customer_user'] = User::where('company_id',$project->company_id)->first()->first_name;
        else
            $project['customer_user'] = '';
        $project['site_count'] = Project_site::where('project_id',$project['id'])->count();
        $project['room_count'] = Room::where('project_id',$project['id'])->count();
        $project['user_notifications'] = Notification::where('notice_type','3')->where('notice_id',$request->id)->count();
        $res['sites'] = Project_site::where('project_id',$project['id'])
            ->leftjoin('sites','project_sites.site_id','=','sites.id')->select('project_sites.*','sites.site_name','sites.city','sites.address','sites.postcode')->withCount('rooms')->get();
        $res['rooms'] = Room::where('rooms.project_id',$project['id'])
            ->join('sites','rooms.site_id','=','sites.id')->select('rooms.*','sites.site_name')->get();
        $room_ids = Room::where('project_id',$project['id'])->pluck('id');
        $products = Product::whereIn('room_id',$room_ids)->get();
        foreach($products as $key => $product)
        {
            $products[$key]['room_name'] = Room::whereId($product->room_id)->first()->room_number;
            $products[$key]['to_room_name'] = Room::whereId($product->to_room_id)->first()->room_number;
            $products[$key]['to_site_name'] = Site::whereId($product->to_site_id)->first()->site_name;
        }
        $res['products'] = $products;
        $tasks = Task::where('project_id',$project['id'])->get();
        foreach($tasks as $key=>$row){
            $tasks[$key]['assign_to'] = Project_user::leftJoin('users','users.id','=','project_users.user_id')->where(['project_users.project_id'=>$row->id,'type'=>'2'])->pluck('users.first_name');
        }
        $res['tasks'] = $tasks;
        $res["project"] = $project;

        $res['customer_sites']= Site::where('company_id',$project->company_id)->get();

        $res['status'] = "success";
        return response()->json($res);
    }
    public function getProjectInfo(Request $request){
        //return response()->json($request);
        $res = array();
        if ($request->has('id')) {
            $id = $request->id;
            $res['project'] = Project::whereId($id)->first();
            $res['project']['assign_to'] = Project_user::where(['project_id'=>$id,'type'=>'1'])->pluck('user_id');
        }
        if($request->user->user_type ==1){
            $company_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
            $res['customer'] = Company::whereIn('id',$company_id)->get();
            $res['account_manager'] = User::whereIn('user_type',[1,3])->where('status',1)->where('company_id',$request->user->company_id)->select('id','first_name','last_name')->get();
        }
        else{
            $res['customer'] = Company::where('id',$request->user->company_id)->get();
            $com_id = Company_customer::where('customer_id',$request->user->company_id)->first()->company_id;           
            $res['account_manager'] = User::whereIn('user_type',[1,3])->where('status',1)->where('company_id',$com_id)->select('id','first_name','last_name')->get();

        }
        if($request->user->user_type ==1||$request->user->user_type ==2)
            $com_id = $request->user->company_id;
        else
            $com_id = Company_customer::where('customer_id',$request->user->company_id)->first()->company_id;           
        $res['assign_to'] = User::where('company_id',$com_id)->whereIn('user_type',[1,5])->where('status',1)->get();
        
        $res['status'] = 'success';        
        return response()->json($res);
    }
    public function setFavourite(request $request)
    {
        Project::whereId($request->id)->update(['favourite'=>$request->favourite]);
        $res = array();
        $res['status'] = 'success';
        return response()->json($res);
    }
}
