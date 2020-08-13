<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Site;
use App\Project;
use App\Project_site;
use App\Room;

class ProjectSiteController extends Controller
{
    public function updateSite(Request $request){
       
        $v = Validator::make($request->all(), [
            //company info
            'project_id' => 'required',
            'site_id' => 'required'            
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
        $site['project_id'] = $request->project_id;
        $site['site_id']  = $request->site_id;
        $site['survey_date']  = $request->survey_date;
        $site['status']  = 1;
        
        if(!isset($request->survey_date) && $request->survey_date==""){
            // $data = Project::whereId($request->project_id)->first();
            // return response()->json($data);
            $site['survey_date'] = Project::whereId($request->project_id)->first()->survey_start_date;
        }
        if(!isset($id) || $id==""|| $id=="null"|| $id=="undefined"){
            $site['created_by']  = $request->user->id;
            Project_site::create($site);
        }
        else{
            $site['updated_by'] = $request->user->id;
            Project_site::whereId($id)->update($site);
        }

        $response = ['status'=>'success', 'msg'=>'Site Saved Successfully!'];  
        return response()->json($response);
    }
    public function deleteSite(Request $request)
    {
        //$request = {'id':{}}
        Project_site::where(['id'=>$request->id])->delete();
        Room::where('site_id',$$request->id)->delete();
        $res["status"] = "success";
        return response()->json($res);
    }
    public function siteList(Request $request){
        $res = array();
        $sites = Project_site::whereIn('project_id',$request->project_id)->get();
        $res["sites"] = $sites;
        $res['status'] = "success";
        return response()->json($res);
    }
    public function siteInfo(Request $request){
        $res = array();
        $site = Project_site::whereIn('id',$request->id)->first();       
        $res["site"] = $site;
        $res['status'] = "success";
        return response()->json($res);
    }
}
