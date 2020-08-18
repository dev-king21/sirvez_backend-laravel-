<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\User;
use App\Company;
use App\Company_customer;
use App\Notification;
use JWTAuth;
use JWTAuthException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    private function getToken($email, $password)
    {
        $token = null;
        try {
            if (!$token = JWTAuth::attempt( ['email'=>$email, 'password'=>$password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid',
                    'token'=>$token
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }

    public function login(Request $request)
    {
        $user = \App\User::where('email', $request->email)->get()->first();
        if (is_null($user)) return response()->json(['status'=>'error','msg'=>'That email does not exist!']);
        if($user->status !='1') return response()->json(['status'=>'error','msg'=>'Please wait until allow!']);
        if ($user && \Hash::check($request->password, $user->password)) // The passwords match...
        {
            $token = self::getToken($request->email, $request->password);
            JWTAuth::setToken($token);
            $user->auth_token = $token;
            $user->save();
            $response = ['status'=>'success', 'data'=>$user];           
        }
        else 
          $response = ['status'=>'error', 'msg'=>'Record doesnt exists'];
        

        return response()->json($response, 201);
    }
    public function register(Request $request)
    { 
        //validate
        $v = Validator::make($request->all(), [
            //company info
            'company_name' => 'required',
            'user_name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'postcode' => 'required',

            //user info
            'password' => 'required|min:6',
            'first_name' => 'required',
            'last_name' => 'required',

            'job_title' => 'required',
            'mobile' => 'required',
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $v = Validator::make($request->all(), [
            //company info
            'email' => 'email|required|unique:users',
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'The User email has been already exist!'
            ]);
        }
        $v = Validator::make($request->all(), [
            'website' => 'required|unique:companies',
            'company_email' => 'email|required|unique:companies'
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'The website or company email has been already exist!'
            ]);
        }
        //register country
        
        $fileName = '';
        if($request->hasFile('logo_img')){
            $fileName = time().'logo.'.$request->logo_img->extension();  
            $request->logo_img->move(public_path('upload\img'), $fileName);
        }

        $response = array();
        $company = new Company;
        $company->name = $request->post("company_name");
        $company->user_name = $request->post("user_name");
        $company->image = $request->post("image");
        $company->bg_image = $request->post("bg_image");

        $company->logo_img = $fileName;
        $company->website = $request->post("website");
        $company->company_email = $request->post("company_email");
        $company->address = $request->post("address");
        $company->city = $request->post("city");
        $company->postcode = $request->post("postcode");
        $company->company_type = 1;
        $company->status = 1;
        $company->save();     

        //User Register
        $fileName = '';
        if($request->hasFile('profile_pic')){

            $fileName = time().'pic.'.$request->profile_pic->extension();  
            $request->profile_pic->move(public_path('upload\img'), $fileName);
        }

        $payload = [
            'company_id' => $company->id,
            'company_name' => $company->name,
            'password'=>\Hash::make($request->password),
            'email'=>$request->email,
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'job_title' => $request->job_title,
            'mobile' => $request->mobile,
            'profile_pic' => $fileName,
            'status' => 1,
            'user_type' => 1,
            'auth_token'=> ''
        ];
      
        $user = new \App\User($payload);
        $user->save();
        $token = self::getToken($request->email, $request->password); // generate user token
        JWTAuth::setToken($token);
        if (!is_string($token))  return response()->json(['status'=>'success','msg'=>'Token generation failed'], 201);
        
        $user = \App\User::where('email', $request->email)->get()->first();
        $user->auth_token = $token; // update user token
        $user->save();
        $response = ['status'=>'success', 'msg'=>'You are Registered!'];        
      
        
        return response()->json($response, 201);
    }

    public function CustomerUpdateUser(Request $request)
    {
        //return response()->json($request);
        $v = Validator::make($request->all(), [
            //user info
            'first_name' => 'required',
            'last_name' => 'required',
            'job_title' => 'required',
            'user_type' => 'required',
            'status' => 'required',
            'mobile' => 'required',
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $user_info = array();
        $fileName = '';
        if($request->hasFile('profile_pic')){

            $fileName = time().'pic.'.$request->profile_pic->extension();  
            $request->profile_pic->move(public_path('upload\img'), $fileName);
            $user_info['profile_pic'] = $fileName;
        }
        
        if($request->password!=""){
            $user_info['password'] = bcrypt($request->password);
        }
        
        if($request->has('customer_id')){
            $user_info['company_id'] = $request->customer_id;
            $user_info['company_name'] = Company::whereId($request->customer_id)->first()->name;
        }
        else{
            $user_info['company_id'] = $request->user->company_id;
            $user_info['company_name'] = $request->user->company_name;
        }
        
        $user_info['email'] = $request->email;
        $user_info['first_name'] = $request->first_name;
        $user_info['last_name'] = $request->last_name;
        $user_info['job_title'] = $request->job_title;
        $user_info['user_type'] = $request->user_type;
        $user_info['status'] = $request->status;
        $user_info['mobile'] = $request->mobile;
        $token = self::getToken($request->email, $request->password);
        $user_info['auth_token'] = $token;
        $id = $request->id;
        
        if(!isset($id) || $id==""|| $id=='null'|| $id=='undefined'|| $id < 1){
            if(!$request->has('password'))
                return response()->json([
                    'status' => 'error',
                    'msg' => 'You must input password!'
                ]);

            $v = Validator::make($request->all(), [
                'email' => 'email|required|unique:users',
            ]);
            if ($v->fails())
            {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'The email has already been taken!'
                ]);
            }
            User::create($user_info);        
        }
        else{
            $count = User::where('id','<>',$id)->where('email',$request->email)->count() ;
            
            if($count>0)
            {
                $res = array();
                $res['status'] = "error";
                $res['msg'] = 'The email has already been taken!';
                return response()->json($res);
            }
            User::whereId($id)->update($user_info);
        }
        return response()->json(['status' => "success",'msg'=>'Save success']);
        
    }

    public function DeleteUser(Request $request)
    {
        //$request = {'id':{},'company_id':{}}
       
        $data = user::where(['id'=>$request->id])->delete();
        $res["status"] = "success";
        return response()->json($res);
    }
    
    public function userList(request $request){
        $res = array();
        $res['status'] = 'success';
        $company_id = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
        $res['users'] = User::whereIn('company_id',$company_id)->orwhere('company_id',$request->user->company_id)->where('id','<>',$request->user->id)->get();
        return response()->json($res);
    }
    public function userInfo(request $request){
        if($request->has('id'))
            $id = $request->id;
        else
            $id = $request->user->id;
        $res = array();
        $res['status'] = 'success';
        $res['user'] = User::whereId($id)->first();
        return response()->json($res);
    }
    public function totalUserlist(request $request){
        $res = array();
        $res['status'] = 'success';
        $res['users'] = User::where('id','<>',$request->user->id)->get();
        $res['company'] = Company::where('company_type','1')->get();
        return response()->json($res);
    }
    public function saveUser(request $request){
        //return response()->json($request);
        $v = Validator::make($request->all(), [
            //user info
            'first_name' => 'required',
            'last_name' => 'required',
            'job_title' => 'required',
            'user_type' => 'required',
            'status' => 'required',
            'mobile' => 'required',
        ]);
        if ($v->fails())
        {
            return response()->json([
                'status' => 'error',
                'msg' => 'You must input data in the field!'
            ]);
        }
        $user_info = array();
        $fileName = '';
        if($request->hasFile('profile_pic')){

            $fileName = time().'pic.'.$request->profile_pic->extension();  
            $request->profile_pic->move(public_path('upload\img'), $fileName);
            $user_info['profile_pic'] = $fileName;
        }
        
        if($request->password!=""){
            $user_info['password'] = bcrypt($request->password);
        }
       
        $user_info['email'] = $request->email;
        $user_info['first_name'] = $request->first_name;
        $user_info['last_name'] = $request->last_name;
        $user_info['job_title'] = $request->job_title;
        $user_info['user_type'] = $request->user_type;
        $user_info['status'] = $request->status;
        $user_info['mobile'] = $request->mobile;
        $token = self::getToken($request->email, $request->password);
        $user_info['auth_token'] = $token;
        $id = $request->user->id;
        
        
        $count = User::where('id','<>',$id)->where('email',$request->email)->count() ;
        
        if($count>0)
        {
            $res = array();
            $res['status'] = "error";
            $res['msg'] = 'The email has already been taken!';
            return response()->json($res);
        }
        User::whereId($id)->update($user_info);
        
        return response()->json(['status' => "success",'msg'=>'Save success']);
    }
}
