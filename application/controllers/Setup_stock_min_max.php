<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_stock_min_max extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $user_outlets;
    public $user_outlet_ids;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_stock_min_max');
        $this->controller_url='setup_stock_min_max';
        $this->user_outlet_ids=array();
        $this->user_outlets=User_helper::get_assigned_outlets();
        if(sizeof($this->user_outlets)>0)
        {
            foreach($this->user_outlets as $row)
            {
                $this->user_outlet_ids[]=$row['id'];
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_OUTLET_NOT_ASSIGNED');
            $this->json_return($ajax);
        }
    }
    public function index($action="search",$id=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        else
        {
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']="Min Max Stock Setup";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
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

    private function system_list()
    {

        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['customer_id']=$this->input->post('customer_id');
            $data['crop_id']=$this->input->post('crop_id');
            $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price').' vp');

            $this->db->select('v.id variety_id,v.name variety_name');
            $this->db->select('pack.name pack_name,pack.id pack_size_id');

            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id = vp.variety_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack','pack.id = vp.pack_size_id','INNER');
            $this->db->where('vp.revision',1);
            $this->db->where('type.crop_id',$data['crop_id']);
            $this->db->order_by('type.ordering ASC');
            $this->db->order_by('v.ordering ASC');
            $data['varieties']=$this->db->get()->result_array();
            $data['items']=array();
            $results=Query_helper::get_info($this->config->item('table_pos_setup_stock_min_max'),'*',array('customer_id ='.$data['customer_id']));
            foreach($results as $result)
            {
                $data['items'][$result['variety_id']][$result['pack_size_id']]=$result;
            }
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list",$data,true));
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
    private function system_save()
    {
        $user=User_helper::get_user();
        $time=time();
        $customer_id=$this->input->post('customer_id');
        $items=$this->input->post('items');
        if(sizeof($items)==0)
        {
            $ajax['status']=false;
            $ajax['system_message']="Nothing Imported";
            $this->json_return($ajax);
            die();
        }
        $old_items=array();
        $results=Query_helper::get_info($this->config->item('table_pos_setup_stock_min_max'),'*',array('customer_id ='.$customer_id));
        foreach($results as $result)
        {
            $old_items[$result['variety_id']][$result['pack_size_id']]=$result;
        }


        $this->db->trans_start(); //DB Transaction Handle START
        foreach($items as $variety_id =>$pack_info)
        {
            foreach($pack_info as $pack_size_id=>$info)
            {
                $data=array();
                $data['customer_id']=$customer_id;
                $data['variety_id']=$variety_id;
                $data['pack_size_id']=$pack_size_id;
                $data['quantity_min']=$info['min'];
                $data['quantity_max']=$info['max'];
                if(isset($old_items[$variety_id][$pack_size_id]))
                {
                    $data['user_updated'] = $user->user_id;
                    $data['date_updated'] = $time;
                    Query_helper::update($this->config->item('table_pos_setup_stock_min_max'),$data,array("id = ".$old_items[$variety_id][$pack_size_id]['id']));
                }
                else
                {
                    $data['user_created'] = $user->user_id;
                    $data['date_created'] = $time;
                    Query_helper::add($this->config->item('table_pos_setup_stock_min_max'),$data);
                }
            }
        }
        $this->db->trans_complete(); //DB Transaction Handle END
        if ($this->db->trans_status()===true)
        {
            $this->system_search();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_SAVED_FAIL');
            $this->json_return($ajax);
        }
    }

}
