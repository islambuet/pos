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
        $this->load->library('email');
        $this->email->from('program2@malikseeds.com', 'Shaiful');
        $this->email->to('islambuet@gmail.com');
        $this->email->subject('Email Tests');
        $this->email->message('Testing the email class.');
        if($this->email->send())
        {
            echo 'successful';
        }
        else

        {
            echo 'failed';
        }
    }

}
