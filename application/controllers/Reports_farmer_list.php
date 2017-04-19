<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_farmer_list extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Reports_farmer_list');
        $this->controller_url='reports_farmer_list';
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
        elseif($action=="details")
        {
            $this->system_details($id);
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
            $data['title']="List of Farmers/Customers";
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
        $current_records = $this->input->post('total_records');
        if(!$current_records)
        {
            $current_records=0;
        }
        $pagesize = $this->input->post('pagesize');
        if(!$pagesize)
        {
            $pagesize=40;
        }
        else
        {
            $pagesize=$pagesize*2;
        }

        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
        $this->db->select('f.*');
        $this->db->select('ft.name farmer_type');
        $this->db->select('count(sale.id) total_invoice',true);
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.type_id','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' fo','fo.farmer_id = f.id and fo.revision =1','LEFT');
        $this->db->join($this->config->item('table_pos_sale').' sale','sale.farmer_id = f.id','LEFT');
        $this->db->where_in('fo.customer_id',$this->user_outlet_ids);
        $this->db->order_by('f.id DESC');
        $this->db->group_by('f.id');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        $this->json_return($items);
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if(($this->input->post('id')))
            {
                $item_id=$this->input->post('id');
            }
            else
            {
                $item_id=$id;
            }
            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
            $this->db->select('f.*');
            $this->db->select('ft.name type_name,ft.discount_coupon,ft.discount_non_coupon');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.type_id','INNER');
            $this->db->where('f.id',$item_id);

            $data['item']=$this->db->get()->row_array();
            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            if($data['item']['user_updated']>0)
            {
                $user_ids[$data['item']['user_updated']]=$data['item']['user_updated'];
            }

            $data['users']=System_helper::get_users_info($user_ids);

            $data['title']="Details of Framer (".$data['item']['name'].')';

            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' fo');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id = fo.customer_id','INNER');
            $this->db->where('fo.revision',1);
            $this->db->where('fo.farmer_id',$item_id);
            $data['assigned_outlets']=$this->db->get()->result_array();

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$item_id);
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
