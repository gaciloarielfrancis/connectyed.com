<?php

use App\Models\User;
use App\Models\BookMeeting;
use App\Models\AdvisorDetail;
use Torann\GeoIP\Facades\GeoIP;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;


// use Timezone;


function getname($id){
    $data = User :: where('id',$id)->first();
    if($data != ''){
        return $data->name;
    }
    else{
        return '';
    }
}
function getAdvisorCategory($id){
    $data = User :: where('id',$id)->first();
    if($data != ''){
        return $data->category;
    }
    else{
        return '';
    }
}

function getAdvisorImage($id){
    $data = AdvisorDetail :: where('advisor_id',$id)->first();
    if($data != ''){
        return $data->photo;
    }
    else{
        return '';
    }
}

function getAdvisorIntro($id){
    $data = AdvisorDetail :: where('advisor_id',$id)->first();
    if($data != ''){
        return $data->introduction;
    }
    else{
        return '';
    }
}

function getrole($id){
    $data = User :: where('id',$id)->first();
    if($data->role == 1){
        return 'Advisor';
    }
    else{
        return 'Client';
    }
}
function getemail($id){
    $data = User :: where('id',$id)->first();
    if($data != ''){
        return $data->email;
    }
    else{
        return '';
    }
}

function getAdvisorTimeZone($id){
    $data = BookMeeting::where('id', $id)->first();
    $advisor_id = $data->advisor_id;
    $client_id = $data->client_id;

    $advisor = User::where('id', $advisor_id)->first();
    $advisorTimezone = $advisor->timezone;

    $client = User::where('id', $client_id)->first();
    $clientTimezone = $client->timezone;

    $advisorDateTime = Carbon::createFromFormat('Y-m-d H:i A', $data->meeting_schedule_date . ' ' . $data->meeting_start_time, $advisorTimezone);

    $clientDateTime = $advisorDateTime->copy()->setTimezone($clientTimezone);

    $clientDate = $clientDateTime->isoFormat('MMMM D, YYYY');
    $clientTime = $clientDateTime->format('h:i A');

    return $clientTime;

}

function getDateTimeZone($id){
    $data = BookMeeting::where('id', $id)->first();
    $advisor_id = $data->advisor_id;
    $client_id = $data->client_id;

    $advisor = User::where('id', $advisor_id)->first();
    $advisorTimezone = $advisor->timezone;

    $client = User::where('id', $client_id)->first();
    $clientTimezone = $client->timezone;

    $advisorDateTime = Carbon::createFromFormat('Y-m-d H:i A', $data->meeting_schedule_date . ' ' . $data->meeting_start_time, $advisorTimezone);

    $clientDateTime = $advisorDateTime->copy()->setTimezone($clientTimezone);

    $clientDate = $clientDateTime->isoFormat('MMMM D, YYYY');
    $clientTime = $clientDateTime->format('h:i A');

    return $clientDate;

}

function getEmailTimeZone($advisor_id, $client_id, $date, $start_time){
   $advisor = User::where('id', $advisor_id)->first();
    $advisorTimezone = $advisor->timezone;
    $client = User::where('id', $client_id)->first();
    if($client){
        $clientTimezone = $client->timezone;
    }
    else{
        $ipAddress = $request->ip();
        $timezone = GeoIP::getLocation($ipAddress)->timezone;
        $client->timezone = $timezone;
        $clientTimezone = $client->timezone;
    }
    $advisorDateTime = Carbon::createFromFormat('Y-m-d H:i A', $date . ' ' . $start_time, $advisorTimezone);
    $clientDateTime = $advisorDateTime->copy()->setTimezone($clientTimezone);
    $clientDate = $clientDateTime->isoFormat('MMMM D, YYYY');
    $clientTime = $clientDateTime->format('h:i A');
    $carbon = Carbon::now($clientTimezone);
    $abbreviation = $carbon->format('T');
    $data['clientDate'] = $clientDate;
    $data['clientTime'] = $clientTime;
    $data['abbreviation'] = $abbreviation;
    return $data;
}

function getClientTime($time){
    $carbon = Carbon::createFromFormat('h:i A', $time);
    $carbon->addMinutes(30);
    $formattedTime = $carbon->format('Y-m-d\TH:i:s\Z');
    $endtime = Carbon::parse($formattedTime);
    $endforTime = $endtime->format('g:i A');
    return $endforTime;
}

function getBookTimeZone($start_time){
    $advisor = User::where('id', Session::get('advisor_id_email'))->first();
    $advisorTimezone = $advisor->timezone;
    $client = User::where('id', Session::get('client_id'))->first();
    if($client == ''){
        $timezone = Session::get('SessionTimeZone');
        $clientTimezone = $timezone;
    }
    else{
        $clientTimezone = $client->timezone;
    }
    $date = Session::get('clientdate');
    $advisorDateTime = Carbon::createFromFormat('Y-m-d H:i A', $date . ' ' . $start_time, $advisorTimezone);
    $clientDateTime = $advisorDateTime->copy()->setTimezone($clientTimezone);
    $clientDate = $clientDateTime->isoFormat('MMMM D, YYYY');
    $clientTime = $clientDateTime->format('h:i A');
    // $data['clientDate'] = $clientDate;
    $data = $clientTime;
    return $data;
}

function getCalBookTimeZone($start_time,$date){
    $advisor = User::where('id', Session::get('advisor_id_email'))->first();
    $advisorTimezone = $advisor->timezone;
    $client = User::where('id', Session::get('client_id'))->first();
    if($client == ''){
        $timezone = Session::get('SessionTimeZone');
        $clientTimezone = $timezone;
    }
    else{
        $clientTimezone = $client->timezone;
    }
    $advisorDateTime = Carbon::createFromFormat('Y-m-d H:i A', $date . ' ' . $start_time, $advisorTimezone);
    $clientDateTime = $advisorDateTime->copy()->setTimezone($clientTimezone);
    $clientDate = $clientDateTime->isoFormat('MMMM D, YYYY');
    $clientTime = $clientDateTime->format('h:i A');
    $data = $clientTime;
    return $data;
}

function getcategory($id){
    $data = BookMeeting::where('id',$id)->first();
    $cate = User::where('id',$data->advisor_id)->first();
    return $cate->category;
}
function getTimezoneAbbrevation($id){
    $currentTime = \Carbon\Carbon::now();
    $currentTime->setTimezone($id);
    $carbon = Carbon::now($id);
    $abbreviation = $carbon->format('T');
    if($id == ''){
        return 'EST';
    }
    elseif($id != 'PST' || $id != 'EST'){
        return 'EST';
    }
    return $abbreviation;
}
function usdate($id){
    $carbonDate = \Carbon\Carbon::parse($id);
    $formattedDate = $carbonDate->format('m/d/Y');
    return $formattedDate;
}
// function getComTimezoneAbbrevation($id){
//     $currentTime = \Carbon\Carbon::now();
//     $currentTime->setTimezone($id);
//     $carbon = Carbon::now($id);
//     $abbreviation = $carbon->format('e');
//     if($id == ''){
//         return 'CDT';
//     }
//     return $abbreviation;
// }

function getAdvisorInfo($id){
    $data = User :: where('id',$id)->first();
    if($data != ''){
        return $data;
    }
    else{
        return '';
    }
}

function getRating($id){
    $data = BookMeeting::where('advisor_id', '=', $id)->where('rating', '>', 0)->avg('rating');
    if($data == ''){
        return 0.0;
    }
    return $data;
}


