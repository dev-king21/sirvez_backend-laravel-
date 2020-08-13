<?php

namespace App\Http\Controllers;
use App\Notification;
use App\Company_customer;
use DB;
use Illuminate\Http\Request;
class NotificationController extends Controller
{
    public function getNotification(request $request){
        if($request->user->user_type >1){
            return response()->json($request->user->user_type);
            $notification = DB::table('notifications')
                ->leftJoin('users','users.id','=','notifications.created_by')
                ->where('notifications.company_id','=',$request->user->company_id)
                ->get();
        }
        else
        {
            $idx = Company_customer::where('company_id',$request->user->company_id)->pluck('customer_id');
            $notification = DB::table('notifications')
                ->leftJoin('users','users.id','=','notifications.created_by')
                ->whereIn('notifications.company_id',$idx)
                ->get();
        }
        //$notification = Notification::where('company_id',$request->user->company_id)->get()
        return response()->json($notification);

    }
}
