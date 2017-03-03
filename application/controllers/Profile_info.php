<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profile_info extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Profile_info');
        $this->controller_url='profile_info';
    }
    public function index($action="details",$id=0)
    {
        if($action=="details")
        {
            $this->system_details();
        }
        else
        {
            $this->system_details();
        }
    }

    private function system_details()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $user=User_helper::get_user();
            $user_id=$user->user_id;
            $this->db->from($this->config->item('table_pos_setup_user').' user');
            $this->db->select('user.user_name,user.status');
            $this->db->select('user_info.user_id,user_info.name,user_info.ordering,user_info.date_birth,user_info.gender,user_info.status_marital,user_info.blood_group,user_info.mobile_no,user_info.nid,user_info.address');
            $this->db->select('ug.name group_name');
            $this->db->select('designation.name designation_name');
            $this->db->join($this->config->item('table_pos_setup_user_info').' user_info','user.id = user_info.user_id','INNER');
            $this->db->join($this->config->item('table_system_user_group').' ug','ug.id = user_info.user_group','LEFT');
            $this->db->join($this->config->item('table_pos_setup_designation').' designation','designation.id = user_info.designation','LEFT');
            $this->db->where('user_info.revision',1);
            $this->db->where('user.id',$user_id);

            $data['user_info']=$this->db->get()->row_array();
            $data['title']=$data['user_info']['name'];

            $this->db->from($this->config->item('table_pos_setup_user_outlet').' uo');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id = uo.customer_id','INNER');
            $this->db->where('uo.revision',1);
            $this->db->where('uo.user_id',$user_id);
            $data['assigned_outlets']=$this->db->get()->result_array();

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }

}
