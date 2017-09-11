<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_outlet_commission_expense extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Setup_outlet_commission_expense');
        $this->controller_url='setup_outlet_commission_expense';
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
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="load_add_form")
        {
            $this->system_add_form();
        }
        elseif($action=="delete")
        {
            $this->system_delete($id);
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="save")
        {
            $this->system_save();
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
            $data['title']="Outlets Commission Expense and Payment";
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
        $this->db->from($this->config->item('table_pos_setup_outlet_commission_expense').' com_exp');
        $this->db->select('com_exp.*');
        $this->db->select('cus.name outlet_name');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id =com_exp.customer_id','INNER');
        $this->db->where_in('customer_id',$this->user_outlet_ids);
        $this->db->where('com_exp.status !=',$this->config->item('system_status_delete'));
        $this->db->order_by('com_exp.id DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['month']=$this->lang->line('LABEL_MONTH_'.$item['month']);
            $item['amount_balance']=number_format($item['amount_actual_payable']-$item['amount_commission_total']-$item['amount_payment_total']-$item['amount_expense_total'],2);
            $item['amount_actual_sale']=number_format($item['amount_actual_sale'],2);
            $item['amount_actual_discount']=number_format($item['amount_actual_discount'],2);
            $item['amount_actual_payable']=number_format($item['amount_actual_payable'],2);
            $item['amount_commission_total']=number_format($item['amount_commission_total'],2);
            $item['amount_payment_total']=number_format($item['amount_payment_total'],2);
            $item['amount_expense_total']=number_format($item['amount_expense_total'],2);

        }
        $this->json_return($items);
    }

    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $data['title']="New Setup";
            $year=date('Y');
            $month=date('n');
            $month--;
            if($month==0)
            {
                $year--;
                $month=12;
            }
            $data["item"] = Array(
                'id' => 0,
                'year' => $year,
                'month' => $month

            );
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
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
    private function system_add_form()
    {
        $year=$this->input->post('year');
        $month=$this->input->post('month');
        $customer_id=$this->input->post('customer_id');
        if(!$this->check_validation_time($year,$month))
        {
            $ajax['status']=false;
            $ajax['system_message']="You cannot select current or future month";
            $this->json_return($ajax);
        }
        if(!$this->check_validation_exists($year,$month,$customer_id))
        {
            $ajax['status']=false;
            $ajax['system_message']="Already Setup Done.";
            $this->json_return($ajax);
        }
        $ajax['status']=true;
        $customer_info=Query_helper::get_info($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers'),'*',array('id ='.$customer_id),1);
        $data['item']['incharge']=$customer_info['incharge'];
        $data['item']['year']=$year;
        $data['item']['month']=$month;
        $data['item']['customer_id']=$customer_id;
        $sale_commission_info=$this->get_sale_commission_info($year,$month,$customer_id,$customer_info['incharge']);
        $data['item']['amount_actual_sale']=$sale_commission_info['amount_actual_sale'];
        $data['item']['amount_actual_discount']=$sale_commission_info['amount_actual_discount'];
        $data['item']['amount_actual_payable']=$sale_commission_info['amount_actual_payable'];
        $data['item']['amount_commission_total']=$sale_commission_info['amount_commission_total'];
        $data['commissions']=$sale_commission_info['commissions'];
        $data['expense_items']=Query_helper::get_info($this->config->item('table_pos_setup_expense_items'),'*',array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);
    }
    private function check_validation_time($year,$month)
    {
        if($month==12)
        {
            $year++;
            $month=1;
        }
        else
        {
            $month++;
        }
        if(mktime(0,0,0,$month,1,$year)>time())
        {
            return false;
        }
        else
        {
            return true;
        }


    }
    private function check_validation_exists($year,$month,$customer_id)
    {
        $row=Query_helper::get_info($this->config->item('table_pos_setup_outlet_commission_expense'),'*',array('year ='.$year,'month ='.$month,'customer_id ='.$customer_id,'status ="'.$this->config->item('system_status_active').'"'),1);
        if($row)
        {
            return false;
        }
        else
        {
            return true;
        }


    }
    private function get_sale_commission_info($year,$month,$customer_id,$incharge)
    {
        $date_start=mktime(0,0,0,$month,1,$year);
        if($month==12)
        {
            $year++;
            $month=1;
        }
        else
        {
            $month++;
        }
        $date_end=mktime(0,0,0,$month,1,$year);
        $date_end--;

        $farmer_types=array();
        $results=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
        foreach($results as $result)
        {
            $farmer_types[$result['id']]['id']=$result['id'];
            $farmer_types[$result['id']]['name']=$result['name'];
            $farmer_types[$result['id']]['commission_distributor']=$result['commission_distributor'];
            $farmer_types[$result['id']]['amount_actual_sale']=0;
            $farmer_types[$result['id']]['amount_actual_payable']=0;
        }
        //total sale
        $this->db->from($this->config->item('table_pos_sale').' sale');
        //$this->db->select('*');
        $this->db->select('SUM(sale.amount_total) sale_total');
        $this->db->select('SUM(sale.amount_payable) payable_total');
        $this->db->select('f.type_id farmer_type_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where('sale.customer_id',$customer_id);
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->where('sale.date_sale <=',$date_end);
        $this->db->group_by('f.type_id');
        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {

            $farmer_types[$result['farmer_type_id']]['amount_actual_sale']=$result['sale_total'];
            $farmer_types[$result['farmer_type_id']]['amount_actual_payable']=$result['payable_total'];
        }
        //total cancel
        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('SUM(sale.amount_total) sale_canceled');
        $this->db->select('SUM(sale.amount_payable) payable_canceled');
        $this->db->select('f.type_id farmer_type_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where('sale.customer_id',$customer_id);
        $this->db->where('sale.date_canceled >=',$date_start);
        $this->db->where('sale.date_canceled <=',$date_end);
        $this->db->group_by('f.type_id');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {

            $farmer_types[$result['farmer_type_id']]['amount_actual_sale']-=$result['sale_canceled'];
            $farmer_types[$result['farmer_type_id']]['amount_actual_payable']-=$result['payable_canceled'];
        }
        $data=array();
        $data['amount_actual_sale']=0;
        $data['amount_actual_payable']=0;
        foreach($farmer_types as $result)
        {
            $data['amount_actual_sale']+=$result['amount_actual_sale'];
            $data['amount_actual_payable']+=$result['amount_actual_payable'];
        }
        $data['amount_actual_discount']=$data['amount_actual_sale']-$data['amount_actual_payable'];
        $data['amount_commission_total']=0;
        $data['commissions']=array();
        if($incharge=='Customer')
        {
            foreach($farmer_types as $result)
            {
                if($result['amount_actual_sale']!=0)
                {
                    $row=array();
                    $row['farmer_type_id']=$result['id'];
                    $row['name']=$result['name'];
                    $row['commission_percentage']=$result['commission_distributor'];
                    $row['amount_based_on']=$result['amount_actual_sale'];
                    $row['amount_commission']=$row['amount_based_on']*$row['commission_percentage']/100;
                    $data['commissions'][]=$row;
                    $data['amount_commission_total']+=$row['amount_commission'];
                }

            }

        }
        else if($incharge=="Arm")
        {
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' uo');
            $this->db->select('uo.commission');
            $this->db->select('ui.name,ui.user_id');
            $this->db->join($this->config->item('table_pos_setup_user_info').' ui','ui.user_id = uo.user_id','INNER');
            $this->db->where('ui.revision',1);
            $this->db->where('uo.customer_id',$customer_id);
            $this->db->where('uo.revision',1);
            $this->db->where('uo.commission >',0);
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $row=array();
                $row['user_id']=$result['user_id'];
                $row['name']=$result['name'];
                $row['commission_percentage']=$result['commission'];
                $row['amount_based_on']=$data['amount_actual_payable'];
                $row['amount_commission']=$row['amount_based_on']*$row['commission_percentage']/100;
                $data['commissions'][]=$row;
                $data['amount_commission_total']+=$row['amount_commission'];
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']="Invalid Incharge.";
            $this->json_return($ajax);
        }
        return $data;

    }
    private function system_delete($id)
    {
        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
        {
            if(($this->input->post('id')))
            {
                $item_id=$this->input->post('id');
            }
            else
            {
                $item_id=$id;
            }
            $user = User_helper::get_user();
            $time=time();
            $data['status']=$this->config->item('system_status_delete');
            $data['date_updated']=$time;
            $data['user_updated']=$user->user_id;

            $this->db->trans_start();  //DB Transaction Handle START
            Query_helper::update($this->config->item('table_pos_setup_outlet_commission_expense'),$data,array('id ='.$item_id));
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
                $item_id=$this->input->post('id');
            }
            else
            {
                $item_id=$id;
            }
            $this->db->from($this->config->item('table_pos_setup_outlet_commission_expense').' com_exp');
            $this->db->select('com_exp.*');
            $this->db->select('cus.name outlet_name');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id =com_exp.customer_id','INNER');
            $this->db->where('com_exp.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details',$item_id,'Trying to access Invalid or Deleted Expense id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if(!in_array($data['item']['customer_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Details',$item_id,'Trying to access other Outlets data');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $data['commissions']=array();
            if($data['item']['incharge']=='Arm')
            {
                $this->db->from($this->config->item('table_pos_setup_outlet_commission_details').' com');
                $this->db->select('ui.name');
                $this->db->select('com.*');
                $this->db->join($this->config->item('table_pos_setup_user_info').' ui','ui.user_id =com.user_id','INNER');
                $this->db->where('com.item_id',$item_id);
                $this->db->where('ui.revision',1);
                $data['commissions']=$this->db->get()->result_array();

            }
            else if($data['item']['incharge']=='Customer')
            {
                $this->db->from($this->config->item('table_pos_setup_outlet_commission_details').' com');
                $this->db->select('ft.name');
                $this->db->select('com.*');
                $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id =com.farmer_type_id','INNER');
                $this->db->where('com.item_id',$item_id);
                $data['commissions']=$this->db->get()->result_array();
            }
            $this->db->from($this->config->item('table_pos_setup_outlet_expense_details').' exp');
            $this->db->select('exp_item.name');
            $this->db->select('exp.*');
            $this->db->join($this->config->item('table_pos_setup_expense_items').' exp_item','exp_item.id =exp.expense_item_id','INNER');
            $this->db->where('exp.item_id',$item_id);
            $data['expenses']=$this->db->get()->result_array();

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

    private function system_save()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();

        }
        $expense_items=Query_helper::get_info($this->config->item('table_pos_setup_expense_items'),'*',array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
        if(!$this->check_validation($expense_items))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $this->db->trans_start();  //DB Transaction Handle START
            {
                $item=$this->input->post('item');
                if(!$this->check_validation_time($item['year'],$item['month']))
                {
                    $ajax['status']=false;
                    $ajax['system_message']="You cannot select current or future month";
                    $this->json_return($ajax);
                }
                if(!$this->check_validation_exists($item['year'],$item['month'],$item['customer_id']))
                {
                    $ajax['status']=false;
                    $ajax['system_message']="Already Setup Done.";
                    $this->json_return($ajax);
                }
                $customer_info=Query_helper::get_info($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers'),'*',array('id ='.$item['customer_id']),1);
                $item['incharge']=$customer_info['incharge'];
                $sale_commission_info=$this->get_sale_commission_info($item['year'],$item['month'],$item['customer_id'],$customer_info['incharge']);

                $item['amount_actual_sale']=$sale_commission_info['amount_actual_sale'];
                $item['amount_actual_discount']=$sale_commission_info['amount_actual_discount'];
                $item['amount_actual_payable']=$sale_commission_info['amount_actual_payable'];
                $item['amount_commission_total']=$sale_commission_info['amount_commission_total'];
                $item['amount_expense_total']=0;
                $expenses=$this->input->post('expense');
                foreach($expenses as $row)
                {
                    $item['amount_expense_total']+=$row;
                }
                $item['user_created'] = $user->user_id;
                $item['date_created'] = $time;
                $item_id=Query_helper::add($this->config->item('table_pos_setup_outlet_commission_expense'),$item);
                //commission details
                foreach($sale_commission_info['commissions'] as $result)
                {
                    $data=array();
                    $data['item_id']=$item_id;
                    if($customer_info['incharge']=='Arm')
                    {
                        $data['user_id']=$result['user_id'];
                        $data['farmer_type_id']=0;
                    }
                    elseif($customer_info['incharge']=='Customer')
                    {
                        $data['user_id']=0;
                        $data['farmer_type_id']=$result['farmer_type_id'];
                    }
                    else//invalid case
                    {
                        $data['user_id']=0;
                        $data['farmer_type_id']=0;
                    }
                    $data['commission_percentage']=$result['commission_percentage'];
                    $data['amount_based_on']=$result['amount_based_on'];
                    $data['amount_commission']=$result['amount_commission'];
                    $data['user_created'] = $user->user_id;
                    $data['date_created'] = $time;
                    Query_helper::add($this->config->item('table_pos_setup_outlet_commission_details'),$data);
                }
                //expense details
                foreach($expenses as $i=>$row)
                {
                    $data=array();
                    $data['item_id']=$item_id;
                    $data['expense_item_id']=$i;
                    $data['amount']=$row;
                    $data['user_created'] = $user->user_id;
                    $data['date_created'] = $time;
                    Query_helper::add($this->config->item('table_pos_setup_outlet_expense_details'),$data);
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
    private function check_validation($expense_items)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[year]','Year?','required');
        $this->form_validation->set_rules('item[month]','Month?','required');
        $this->form_validation->set_rules('item[customer_id]','customer?','required');
        $this->form_validation->set_rules('item[amount_payment_total]',$this->lang->line('LABEL_AMOUNT_PAYMENT'),'required');
        foreach($expense_items as $result)
        {
            $this->form_validation->set_rules('expense['.$result['id'].']',$result['name'],'required');
        }

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
}
