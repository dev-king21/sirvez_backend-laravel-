<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\Company_customer;
use App\Site;
use App\User;
use App\Task;
use App\Project;
use App\Room;
use App\Project_user;
use App\Notification;
use Mail;
use Illuminate\Support\Facades\Validator;
class CompanyCustomerController extends Controller
{
    public function addCompanyCustomer(Request $request){

        
        $v = Validator::make($request->all(), [
            //company info
            'company_name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'postcode' => 'required',
            'manager' => 'required'
        ]);
        
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $company = array();
        $id = $request->id;
        if($request->hasFile('logo_img')){
            $fileName = time().'.'.$request->logo_img->extension();  
            $request->logo_img->move(public_path('upload\img'), $fileName);
            $company['logo_img']  = $fileName;
        }
        $company['name'] = $request->post("company_name");
        $company['website']  = $request->post("website");
        $company['company_email']  = $request->post("company_email");
        $company['address']  = $request->post("address");
        $company['city']  = $request->post("city");
        $company['postcode']  = $request->post("postcode");
        $company['company_type']  = 3;
        $company['status']  = 1;
        $company['manager']  = $request->post("manager");
        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined"){
            
            $v = Validator::make($request->all(), [
                'website' => 'required|unique:companies',
                'company_email' => 'email|required|unique:companies'
            ]);
            if ($v->fails())
            {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'The website or company email has already been taken!'
                ]);
            }
          $company = company::create($company);
          $id = $company->id;
        }
        else{
           
            $count = Company::where('id','<>',$id)->where('website',$request->website)->count() +
                Company::where('id','<>',$id)->where('company_email',$request->company_email)->count();
            
            if($count>0)
            {
                $res = array();
                $res['status'] = "error";
                $res['msg'] = 'The website or company email has already been taken!';
                return response()->json($res);
            }
          
            company::whereId($request->id)->update($company);
           
            
        }
        
        //insert company_customer
        $companyCustomer = array();
        $companyCustomer['company_id'] = $request->user->company_id;
        $companyCustomer['customer_id'] = $id;
        $action = "";
        if(!isset($request->id) || $request->id==""|| $request->id=='null'){
            $companyCustomer['created_by'] = $request->user->id;
            $companyCustomer = Company_customer::create($companyCustomer);
            $action = "created";
        }
        else{
            $companyCustomer =Company_customer::where('customer_id',$request->id)->first();
            $companyCustomer['updated_by'] = $request->user->id;
            $companyCustomer->save();
            $action = "updated";
        }
        //insert notification
        //$notice_type ={1:pending_user,2:createcustomer}  
        
        $insertnotificationndata = array(
            'notice_type'		=> '2',
            'notice_id'			=> $companyCustomer->id,
            'notification'		=> $company['name'].' have been '.$action.' by  '.$request->user->first_name.' ('.$request->user->company_name.').',
            'created_by'		=> $request->user->id,
            'company_id'		=> $id,
            'created_date'		=> date("Y-m-d H:i:s"),
            'is_read'	    	=> 0,
        );
        
        Notification::create($insertnotificationndata);
        
        //insert site
        if($request->is_site)
        {
            $site['company_id'] = $id;
            $site['site_name'] = $request->post("company_name")." Head Office";
            $site['address'] = $request->post("address");
            $site['city'] = $request->post("city");
            $site['postcode'] = $request->post("postcode");
            $site['created_by'] = $request->user->id;
            $site['updated_by'] = $request->user->id;
            if(!isset($request->id) || $request->id==""|| $request->id=='null')
                $site = Site::create($site);
            else
                $site = Site::where('company_id',$request->id)->update($site);
            
        }
        

        $response = ['status'=>'success', 'id'=>$id];  
        return response()->json($response);

    }

    public function getCompanyCustomer(Request $request){
        
        $res = array();
        $companyid = Company_customer::where(['company_id'=>$request->user->company_id])->pluck('customer_id');
        
        $customers = array();
        $company_array = Company::whereIn('id',$companyid)->get();
        foreach($company_array as $key => $row){
            $row['user_count'] = User::where('company_id',$row['id'])->where('status',1)->count();
            $row['project_count'] = Project::where('company_id',$row['id'])->count();
            $row['site_count'] = Site::where('company_id',$row['id'])->count();
            $row['room_count'] = Room::where('company_id',$row['id'])->count();
            $customers[$key] = $row;
        }
        // $customers = Company::with(['company_customers'=> function ($query) use($userid) {
        //     $query->with('company')->where('company_id', $userid);
        // }])->get();
        $res["customers"] = $customers;
        $res['status'] = "success";
        return response()->json($res);
    }
    public function DeleteCompanyCustomer(Request $request){
        $company_id = $request->id;
        Company::whereId($company_id)->delete();
        Company_customer::where('company_id',$company_id)->delete();
        Project::where('company_id',$company_id)->delete();
        Site::where('company_id',$company_id)->delete();
        Room::where('company_id',$company_id)->delete();
        $res['status'] = "success";
        return response()->json($res);
    }
    public function CompanyCustomerInfo(Request $request){
        $company_id = $request->id;
        $res = array();
        $res['status'] = 'success';
        $res['company'] = Company::whereId($company_id)->first();
        $res['users'] = User::where('company_id',$company_id)->where('status',1)->get();
        $projects = Project::where('company_id',$company_id)->get();
        if(!is_null($projects)){
            foreach($projects as $key=>$project){
                $projects[$key]['user_name'] = User::whereId($project->created_by)->first()->first_name;
                $projects[$key]['manager_name'] = User::whereId($project->manager_id)->first()->first_name;
                $projects[$key]['rooms'] = Room::where('project_id',$project->id)->count();
            }
        }
        $res['projects'] = $projects;
        $sites = Site::where('company_id',$company_id)->get();
        foreach($sites as $key=>$site){
           
            $sites[$key]['rooms'] = Room::where('site_id',$site->id)->count();    
            $sites[$key]['projects'] = Project::where('company_id',$company_id)->count();    
        }

        $res['sites'] =$sites;
        $res['rooms'] = Room::where('rooms.company_id',$company_id)
            ->join('sites','rooms.site_id','=','sites.id')->select('rooms.*','sites.site_name')->get();
        $tasks = Task::where('company_id',$company_id)->get();
        foreach($tasks as $key=>$row){
            $tasks[$key]['assign_to'] = Project_user::leftJoin('users','users.id','=','project_users.user_id')->where(['project_users.project_id'=>$row->id,'type'=>'2'])->pluck('users.first_name');
        }
        $res['tasks'] = $tasks;
        return response()->json($res);
    }
    public function getCustomerInfo(Request $request){
        if ($request->has('id')) {
            $customer_id = $request->id;
        
            $res = array();
            $res['status'] = 'success';
            $res['company'] = Company::whereId($customer_id)->first();
        }
        
        $res['account_manager'] = User::whereIn('user_type',[1,3])->where('status',1)->where('company_id',$request->user->company_id)->select('id','first_name','last_name')->get();
        
        return response()->json($res);
    }
    public function userList(Request $request){
        $res = array();
        $res['status'] = 'success';
        if($request->has('company_id'))
        {
            $res['users'] = User::where('company_id',$request->company_id)->where('id','<>',$request->user->id)->where('status',1)->join('companys','users.cpmoany_id','=','cpmpany.id')->select('users.*','companies.name')->get();
        }
        else{
            $company_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
            $res['users'] = User::whereIn('company_id',$company_id)->orwhere('company_id',$request->user->company_id)->where('id','<>',$request->user->id)->where('status',1)->get();
        }
        return response()->json($res);
    }
    public function pendingUser(request $request){
        
        //$request = {pending_users = [email,first_name,user_role],company_id,company_name}
        
        
        $v = Validator::make($request->all(), [
            //company info
            'company_id' => 'required',
            'company_name' => 'required'
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'errors' => 'You must input Company Name!'
            ]);
        }      
        foreach($request->pending_users as $pending_user){
            $v = Validator::make($pending_user, [
                //company info
                'email' => 'email|required|unique:users',
                'first_name' => 'required',
                'user_role' => 'required'
            ]);
            if ($v->fails())
            {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'The Email that you are going to invite has been already taken!'
                ]);
            }

            //add usertable new user by pending
            $user = array();
            $user['email'] = $pending_user['email'];
            $user['first_name'] = $pending_user['first_name'];
            $user['user_type'] = $pending_user['user_role'];
            $user['company_id'] = $request->company_id;
            $user['status'] = '0';
            $user = User::create($user);

            //add notification
            //$notice_type ={1:pending_user}  
            $user_role = ['2'=> 'admin','6'=>'nomal'];
            $insertnotificationndata = array(
                'notice_type'		=> '1',
                'notice_id'			=> $user->id,
                'notification'		=> $user['first_name'].' have been added as an account manager to '.$user_role[$pending_user['user_role']].' by  '.$request->user->first_name.' ('.$request->company_name.').',
                'created_by'		=> $request->user->id,
                'company_id'		=> $user['company_id'],
                'created_date'		=> date("Y-m-d H:i:s"),
                'is_read'	    	=> 0,
            );
            Notification::create($insertnotificationndata);
            
                        // $data = array('name'=>"Virat Gandhi");
                        // Mail::send('mail', $data, function($message) {
                        //    $message->to('sergey.anishchenko2019@gmail.com', 'Tutorials Point')->subject
                        //       ('Laravel HTML Testing Mail');
                        //    $message->from('xyz@gmail.com','Virat Gandhi');
                        // });

            //sending gail to user
            /* $to_name = $pending_user['first_name'];
            $to_email = $pending_user['email'];
            $data = array('name'=>$pending_user['first_name'], "body" => "Invite You!");
            Mail::send([], $data, function($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)
                        ->subject('sergey invite you. please join our site.');
                $message->from('goodhelper21@gmail.com','sergey');
            }); */
        }
        $res['status'] = "success";
        return response()->json($res);
    }
    public function getDashboard(Request $request){
        $res = array();
        $res['status'] = "success";
        $id = $request->user->id;
        
        $res['lives'] = Project::where('company_id',$id)->count();
        $res['messages'] = Notification::where('company_id',$id)->count();
        $res['tasks'] = Task::where('company_id',$id)->count();
        $res['customers'] = Company_customer::where('company_id',$id)->count();
        $customerId = Company_customer::where('company_id',$id)->pluck('customer_id');
        $res['users'] = User::whereIn('company_id',$customerId)->orwhere('company_id',$id)->count();
        $res['sites'] = Site::where('company_id',$id)->count();
        $res['rooms'] = Room::where('company_id',$id)->count();
       
        return response()->json($res);
    }


    
   

}
