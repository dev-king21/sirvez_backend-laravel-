<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Site;
use App\Project_site;
use App\Room;
use App\Notification;
class SiteController extends Controller
{
    public function updateSite(Request $request){
       
        $v = Validator::make($request->all(), [
            //company info
            'customer_id' => 'required',
            'site_name' => 'required',
            'contact_number' => 'required',
            'contact_name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'postcode' => 'required',
            'site_instructions' => 'required',
            //'parking_instructions' => 'required',
            'access_hour' => 'required',
            'comment' => 'required',
            //'status' => 'required'
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $site = array();
        $id = $request->id;
        $site['company_id'] = $request->customer_id;
        $site['site_name']  = $request->site_name;
        $site['contact_number']  = $request->contact_number;
        $site['contact_name']  = $request->contact_name;
        $site['address']  = $request->address;
        $site['city']  = $request->city;
        $site['postcode']  = $request->postcode;
        $site['site_instructions']  = $request->site_instructions;
        $site['parking_instructions']  = $request->parking_instructions;
        $site['access_hour']  = $request->access_hour;
        $site['comment']  = $request->comment;
        $site['status']  = $request->status;
        $action = "updated";
        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined"){
            $site['created_by']  = $request->user->id;
            $site = Site::create($site);
            $id = $site->id;
        }
        else{
            $site['updated_by'] = $request->user->id;
            Site::whereId($id)->update($site);
        }
        //$notice_type ={1:pending_user,2:createcustomer 3:project 4:site}  
        $insertnotificationndata = array(
            'notice_type'		=> '4',
            'notice_id'			=> $id,
            'notification'		=> $site['site_name'].' have been '.$action.' by  '.$request->user->first_name.').',
            'created_by'		=> $request->user->id,
            'company_id'		=> $request->customer_id,
            'created_date'		=> date("Y-m-d H:i:s"),
            'is_read'	    	=> 0,
        );

        $response = ['status'=>'success', 'msg'=>'Site Saved Successfully!'];  
        return response()->json($response);
    }
    public function deleteSite(Request $request)
    {
        //$request = {'id':{}}
       
        Site::where(['id'=>$request->id])->delete();
        $site_id = Project_site::where('site_id',$request->id)->pluck('id');
        Project_site::whereIn('id',$site_id)->delete();
        Room::whereIn('site_id',$site_id)->delete();
        $res["status"] = "success";
        return response()->json($res);
    }
    public function siteList(Request $request){
        $res = array();
        $sites = Site::where('company_id',$request->company_id)->get();
        $res["sites"] = $sites;
        $res['status'] = "success";
        return response()->json($res);
    }
    public function siteInfo(Request $request){
        $res = array();
        $site = Site::where('id',$request->id)->first();       
        $res["site"] = $site;
        $res['status'] = "success";
        return response()->json($res);
    }
}
