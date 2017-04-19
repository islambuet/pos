<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup_exp_date extends Root_Controller
{
    private $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->permissions=User_helper::get_permission('Setup_exp_date');
        $this->controller_url='setup_exp_date';
    }
    public function index($action='add')
    {
        if($action=='add')
        {
            $this->system_add();
        }
        elseif($action=='save')
        {
            $this->system_save();
        }
        else
        {
            $this->system_add();
        }
    }
    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $data['title']='Set Expire Date';
            $result=Query_helper::get_info($this->config->item('table_pos_setup_exp_date'),'date_expire',array(),1,0,array('id desc'));
            $data['item']=array();
            if($result)
            {
                $data['item']['date_expire']=System_helper::display_date($result['date_expire']);
            }
            else
            {
                $data['item']['date_expire']=System_helper::display_date(time());
            }

            $ajax['system_page_url']=site_url($this->controller_url.'/index/add');
            $ajax['status']=true;
            $ajax['system_content'][]=array('id'=>'#system_content','html'=>$this->load->view($this->controller_url.'/add_edit',$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('YOU_DONT_HAVE_ACCESS');
            $this->json_return($ajax);
        }
    }
    private function system_save()
    {
        $user=User_helper::get_user();
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        $data=$this->input->post('item');
        $this->db->trans_start(); //DB Transaction Handle START

        $data['date_expire']=System_helper::get_time($data['date_expire']);
        $data['user_created']=$user->user_id;
        $data['date_created']=time();
        Query_helper::add($this->config->item('table_pos_setup_exp_date'),$data);

        $this->db->trans_complete(); //DB Transaction Handle END
        if ($this->db->trans_status()===true)
        {
            $this->message=$this->lang->line('MSG_SAVED_SUCCESS');
            $this->system_add();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_SAVED_FAIL');
            $this->json_return($ajax);
        }
    }
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[date_expire]',$this->lang->line('EXPIRE_DATE'),'required');
        if($this->form_validation->run()==false)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
}
