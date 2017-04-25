<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_commission extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Reports_commission');
        $this->controller_url='reports_commission';
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
        else
        {
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']="Sale Report Search";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url);

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
    private function system_list()
    {
        $reports=$this->input->post('report');
        $customer_id=$reports['customer_id'];
        $date_end=System_helper::get_time($reports['date_end']);
        $date_end=$date_end+3600*24-1;
        $date_start=System_helper::get_time($reports['date_start']);
        if($date_start>=$date_end)
        {
            $ajax['status']=false;
            $ajax['system_message']='Starting Date should be less than End date';
            $this->json_return($ajax);
        }
        $data['outlet']['sale_total']=0;
        $data['outlet']['payable_total']=0;
        $data['outlet']['sale_canceled']=0;
        $data['outlet']['payable_canceled']=0;

        //total sales
        $this->db->from($this->config->item('system_db_pos').'.'.$this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.customer_id');
        $this->db->select('SUM(sale.amount_total) sale_total');
        $this->db->select('SUM(sale.amount_payable) payable_total');
        $this->db->where('sale.customer_id',$customer_id);
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->where('sale.date_sale <=',$date_end);
        $result=$this->db->get()->row_array();
        if($result)
        {
            $data['outlet']['sale_total']=$result['sale_total'];
            $data['outlet']['payable_total']=$result['payable_total'];
        }
        //total canceled
        $this->db->from($this->config->item('system_db_pos').'.'.$this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.customer_id customer_id');
        $this->db->select('SUM(sale.amount_total) sale_canceled');
        $this->db->select('SUM(sale.amount_payable) payable_canceled');
        $this->db->where('sale.customer_id',$customer_id);
        $this->db->where('sale.status',$this->config->item('system_status_inactive'));
        $this->db->where('sale.date_canceled >=',$date_start);
        $this->db->where('sale.date_canceled <=',$date_end);
        $result=$this->db->get()->row_array();
        if($result)
        {
            $data['outlet']['sale_canceled']=$result['sale_canceled'];
            $data['outlet']['payable_canceled']=$result['payable_canceled'];
        }
        $this->db->from($this->config->item('system_db_pos').'.'.$this->config->item('table_pos_setup_user_outlet').' uo');
        $this->db->select('uo.commission');
        $this->db->select('ui.name');
        $this->db->join($this->config->item('table_pos_setup_user_info').' ui','ui.user_id = uo.user_id','INNER');
        $this->db->where('ui.revision',1);
        $this->db->where('uo.customer_id',$customer_id);
        $this->db->where('uo.revision',1);
        $this->db->where('uo.commission >',0);
        $data['commission_users']=$this->db->get()->result_array();
        $data['title']="Commission Report";
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);
    }
}
