<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller
{
    private  $message;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
    }
    public function index()
    {
        $year=2017;
        $month=5;
        echo (System_helper::get_time('1-may-2017')).'<br/>';
        echo mktime(0,0,0,$month,1,$year);
    }

}
