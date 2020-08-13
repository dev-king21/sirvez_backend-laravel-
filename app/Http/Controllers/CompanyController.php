<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;

class CompanyController extends Controller
{
    public function updateCompany(request $request){

        $v = Validator::make($request->all(), [
            //company info
            'company_name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'postcode' => 'required',
            'telephone' => 'required',
        ]);
        
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $company = array();
        $id = $request->user->company_id;
        if($request->hasFile('logo_img')){
            $fileName = time().'logo.'.$request->logo_img->extension();  
            $request->logo_img->move(public_path('upload\img'), $fileName);
            $company['logo_img']  = $fileName;
        }
        if($request->hasFile('bg_img')){
            $fileName = time().'bg.'.$request->bg_img->extension();  
            $request->bg_img->move(public_path('upload\img'), $fileName);
            $company['bg_image']  = $fileName;
        }
        $company['name'] = $request->post("company_name");
        $company['website']  = $request->post("website");
        $company['company_email']  = $request->post("company_email");
        $company['address']  = $request->post("address");
        $company['address2']  = $request->post("address2");
        $company['city']  = $request->post("city");
        $company['postcode']  = $request->post("postcode");
        $company['status']  = $request->post("status");
        $company['telephone']  = $request->post("telephone");
        
           
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
        return response()->json(['status'=>'success']);
           
            
         
    }
    public function getCompanyInfo(request $request){
        $id = $request->user->company_id;
        $res = array();
        $res['status'] = 'success';
        $res['company'] = Company::whereId($id)->first();
        return response()->json($res);
    }
}
