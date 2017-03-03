<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_users_info extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_users_info');
        $this->controller_url='setup_users_info';

    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list($id);
        }
        elseif($action=='get_items')
        {
            $this->system_get_items();
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="edit_password")
        {
            $this->system_edit_password($id);
        }
        elseif($action=="edit_outlet")
        {
            $this->system_edit_outlet($id);
        }
        elseif($action=="edit_status")
        {
            $this->system_edit_status($id);
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="save_password")
        {
            $this->system_save_password();
        }
        elseif($action=="save_outlet")
        {
            $this->system_save_outlet();
        }
        elseif($action=="save_status")
        {
            $this->system_save_status();
        }
        else
        {
            $this->system_list($id);
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']="List of Users";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }

    }
    private function system_get_items()
    {
        $user = User_helper::get_user();

        $this->db->from($this->config->item('table_pos_setup_user').' user');
        $this->db->select('user.id,user.user_name,user.status');
        $this->db->select('user_info.name,user_info.ordering,user_info.blood_group,user_info.mobile_no');
        $this->db->select('ug.name group_name');
        $this->db->select('designation.name designation_name');
        $this->db->select('count(customer_id) total_outlet',true);
        $this->db->join($this->config->item('table_pos_setup_user_info').' user_info','user.id = user_info.user_id','INNER');
        $this->db->join($this->config->item('table_system_user_group').' ug','ug.id = user_info.user_group','INNER');
        $this->db->join($this->config->item('table_pos_setup_designation').' designation','designation.id = user_info.designation','INNER');

        $this->db->join($this->config->item('table_pos_setup_user_outlet').' uo','uo.user_id = user.id and uo.revision =1','LEFT');
        $this->db->where('user_info.revision',1);

        if($user->user_group!=1)
        {
            $this->db->where('user_info.user_group !=',1);
        }
        $this->db->order_by('user_info.ordering ASC');
        $this->db->group_by('user.id');
        $items=$this->db->get()->result_array();
        $this->json_return($items);

    }

    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $user=User_helper::get_user();

            $data['title']="Create New User";
            $data["user"] = Array(
                'id' => 0,
                'user_name' => ''
            );
            $data["user_info"] = Array(
                'name' => '',
                'designation' => '',
                'user_group' => '',
                'date_birth' => '',
                'gender' => 'Male',
                'status_marital' => 'Un-Married',
                'nid' => '',
                'address' => '',
                'blood_group' => '',
                'mobile_no' => '',
                'ordering' => 999
            );
            $data['designations']=Query_helper::get_info($this->config->item('table_pos_setup_designation'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            if($user->user_group==1)
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            }
            else
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id !=1'));
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            $user=User_helper::get_user();
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }

            $data['user']=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('id','user_name'),array('id ='.$user_id),1);
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            $data['title']="Edit User (".$data['user_info']['name'].')';

            $data['designations']=Query_helper::get_info($this->config->item('table_pos_setup_designation'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            if($user->user_group==1)
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            }
            else
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id !=1'));
            }

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit_password($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            $data['title']="Reset Password of (".$data['user_info']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_password",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_password/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit_outlet($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            $data['title']="Assign Outlet For(".$data['user_info']['name'].')';
            $ajax['status']=true;
            $data['outlets']=Query_helper::get_info($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers'),array('id value','CONCAT(customer_code," - ",name) text'),array('status ="'.$this->config->item('system_status_active').'"','type ="Outlet"'));
            $results=Query_helper::get_info($this->config->item('table_pos_setup_user_outlet'),array('customer_id'),array('user_id ='.$user_id,'revision =1'));
            $data['assigned_outlets']=array();
            foreach($results as $result)
            {
                $data['assigned_outlets'][]=$result['customer_id'];
            }
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_outlet",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_outlet/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit_status($id)
    {
        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $data['user']=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('status'),array('id ='.$user_id),1);
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            $data['title']="Reset Password of (".$data['user_info']['name'].')';
            $data['title']=$data['user_info']['name'];
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_status",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_status/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
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
            $data['title']="Details of User (".$data['user_info']['name'].')';

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

    private function system_save()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
        }
        else
        {
            if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();

            }
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $time=time();

            $this->db->trans_start();  //DB Transaction Handle START
            if($id==0)
            {
                $data_user=$this->input->post('user');
                $data_user['password']=md5($data_user['user_name']);
                $data_user['status']=$this->config->item('system_status_active');
                $data_user['user_created'] = $user->user_id;
                $data_user['date_created'] = $time;
                $user_id=Query_helper::add($this->config->item('table_pos_setup_user'),$data_user);
                if($user_id===false)
                {
                    $this->db->trans_complete();
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                    $this->json_return($ajax);
                    die();
                }
                else
                {
                    $id=$user_id;
                }
            }
            $this->db->where('user_id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_pos_setup_user_info'));
            $data_user_info=$this->input->post('user_info');
            $data_user_info['user_id']=$id;
            $data_user_info['date_birth']=System_helper::get_time($data_user_info['date_birth']);
            $data_user_info['user_created'] = $user->user_id;
            $data_user_info['date_created'] = $time;
            $data_user_info['revision'] = 1;
            Query_helper::add($this->config->item('table_pos_setup_user_info'),$data_user_info);
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $save_and_new=$this->input->post('system_save_new_status');
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                if($save_and_new==1)
                {
                    $this->system_add();
                }
                else
                {
                    $this->system_list();
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_save_password()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_password())
        {

            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {

            $this->db->trans_start();  //DB Transaction Handle START
            $data['password']=md5($this->input->post('new_password'));
            $data['user_updated'] = $user->user_id;
            $data['date_updated'] = time();
            Query_helper::update($this->config->item('table_pos_setup_user'),$data,array("id = ".$id));

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {

                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_save_outlet()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        {

            $this->db->trans_start();  //DB Transaction Handle START
            $this->db->where('user_id',$id);
            $this->db->where('revision >',1);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_pos_setup_user_outlet'));

            $this->db->where('user_id',$id);
            $this->db->where('revision',1);
            $this->db->set('revision',2);
            $this->db->set('user_updated',$user->user_id);
            $this->db->set('date_updated',$time);
            $this->db->update($this->config->item('table_pos_setup_user_outlet'));

            $items=$this->input->post('items');
            if(is_array($items))
            {
                foreach($items as $customer_id)
                {
                    $data=array();
                    $data['user_id']=$id;
                    $data['customer_id']=$customer_id;
                    $data['user_created'] = $user->user_id;
                    $data['date_created'] = $time;
                    $data['revision'] = 1;
                    Query_helper::add($this->config->item('table_pos_setup_user_outlet'),$data);
                }
            }

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {

                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_save_status()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_status())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {

            $this->db->trans_start();  //DB Transaction Handle START
            $data['status']=$this->input->post('status');
            $data['user_updated'] = $user->user_id;
            $data['date_updated'] = time();
            Query_helper::update($this->config->item('table_pos_setup_user'),$data,array("id = ".$id));

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {

                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function check_validation()
    {
        $id = $this->input->post("id");
        $this->load->library('form_validation');
        if($id==0)
        {
            $this->form_validation->set_rules('user[user_name]',$this->lang->line('LABEL_USERNAME'),'required');
            $user_user=$this->input->post("user");
            if(sizeof(explode(' ',$user_user['user_name']))>1)
            {
                $this->message="Invalid User name.<br>User name should be one word.<br>Please avoid space.";
                return false;
            }
            $exists=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('user_name'),array('user_name ="'.$user_user['user_name'].'"'),1);
            if($exists)
            {
                $this->message="User Name already Exists";
                return false;
            }
        }
        $this->form_validation->set_rules('user_info[name]',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('user_info[user_group]',$this->lang->line('LABEL_USER_GROUP'),'required');
        $this->form_validation->set_rules('user_info[designation]',$this->lang->line('LABEL_DESIGNATION_NAME'),'required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function check_validation_password()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('new_password',$this->lang->line('LABEL_PASSWORD'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        if($this->input->post('new_password')!=$this->input->post('re_password'))
        {
            $this->message="Password did not Match";
            return false;
        }
        return true;
    }
    private function check_validation_status()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('status',$this->lang->line('STATUS'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }


}
