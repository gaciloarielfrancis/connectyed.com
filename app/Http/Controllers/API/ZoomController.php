<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class ZoomController extends Controller
{

    protected $client_id;
    protected $client_secret;
    protected $redirect_url;
    protected $auth_url;
    protected $token_url;
    protected $api_url;
    protected $token_secret;
    protected $token_verify;

    public function __construct()
    {
        $client_id = config('services.zoom.client_id');
        $client_secret = config('services.zoom.client_secret');
        $redirect_url = config('services.zoom.client_id');
        $auth_url = config('services.zoom.auth_url');
        $token_url = config('services.zoom.token_url');
        $api_url = config('services.zoom.api_url');
        $token_secret = config('services.zoom.token_secret');
        $token_verify = config('services.zoom.token_verify');
        
    }

}