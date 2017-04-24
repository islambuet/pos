<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_sale extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Reports_sale');
        $this->controller_url='reports_sale';
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
        elseif($action=='get_items_outlet_invoice')
        {
            $this->system_get_items_outlet_invoice();
        }
        elseif($action=='get_items_farmers_sale')
        {
            $this->system_get_items_farmers_sale();
        }
        elseif($action=='get_items_farmer_invoice')
        {
            $this->system_get_items_farmer_invoice();
        }
        elseif($action=='get_items_variety_sale')
        {
            $this->system_get_items_variety_sale();
        }
        elseif($action=="details_invoice")
        {
            $this->system_details_invoice($id);
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
            //$report_name=$this->input->post('report_name');
            //$data['report_name']=$report_name;
            $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC','id ASC'));
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url);
            /*if($report_name=='outlet_invoice')
            {
                $ajax['system_content'][]=array("id"=>"#report_search_container","html"=>$this->load->view($this->controller_url."/search_outlet_invoice",$data,true));
            }
            elseif($report_name=='farmer_sale')
            {
                $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC','id ASC'));
                $ajax['system_content'][]=array("id"=>"#report_search_container","html"=>$this->load->view($this->controller_url."/search_farmer_sale",$data,true));
            }
            elseif($report_name=='variety_sale')
            {
                $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC','id ASC'));
                $ajax['system_content'][]=array("id"=>"#report_search_container","html"=>$this->load->view($this->controller_url."/search_variety_sale",$data,true));
            }
            else
            {
                $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
                $ajax['system_page_url']=site_url($this->controller_url);
            }*/


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
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $report_name=$this->input->post('report_name');
            $reports=$this->input->post('report');
            $reports['date_end']=System_helper::get_time($reports['date_end']);
            $reports['date_end']=$reports['date_end']+3600*24-1;
            $reports['date_start']=System_helper::get_time($reports['date_start']);
            if($reports['date_start']>=$reports['date_end'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Starting Date should be less than End date';
                $this->json_return($ajax);
            }
            $data['options']=$reports;


            $ajax['status']=true;
            if($report_name=='outlet_invoice')
            {
                $data['title']="Invoice Wise Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_outlet_invoice",$data,true));
            }
            elseif($report_name=='farmer_sale')
            {
                if($reports['farmer_id']>0)
                {
                    $data['title']="Farmer's Invoice Report";
                    $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_farmer_invoice",$data,true));
                }
                else
                {
                    $data['title']="Farmers Sales Report";
                    $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_farmers_sale",$data,true));
                }

            }
            elseif($report_name=='variety_sale')
            {
                $data['title']="Product Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_variety_sale",$data,true));
            }
            else
            {
                $this->message='Invalid Report type';
            }

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
    private function system_get_items_outlet_invoice()
    {

        //$customer_ids=$this->input->post('customer_ids');
        $customer_id=$this->input->post('customer_id');

        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name farmer_name');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id =sale.customer_id','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        //$this->db->where_in('customer_id',$customer_ids);
        $this->db->where('customer_id',$customer_id);
        $where='(sale.date_sale >='.$date_start.' AND sale.date_sale <='.$date_end.')';
        $where.=' OR (sale.date_canceled >='.$date_start.' AND sale.date_canceled <='.$date_end.')';

        $this->db->where('('.$where.')');
        $this->db->order_by('cus.ordering DESC');
        $this->db->order_by('cus.id ASC');
        $this->db->order_by('sale.date_sale DESC');

        $results=$this->db->get()->result_array();
        $outlet_total=array();
        $grand_total=array();
        $grand_total['id']=$outlet_total['id']=0;
        $grand_total['outlet_name']=$outlet_total['outlet_name']='';
        $grand_total['date_sale']='Grand Total';
        $outlet_total['date_sale']='Outlet Total';
        $grand_total['date_canceled']=$outlet_total['date_canceled']=0;
        $grand_total['invoice_no']=$outlet_total['invoice_no']=0;
        $grand_total['farmer_name']=$outlet_total['farmer_name']='';
        $grand_total['amount_total']=$outlet_total['amount_total']=0;
        $grand_total['amount_discount']=$outlet_total['amount_discount']=0;
        $grand_total['amount_payable']=$outlet_total['amount_payable']=0;
        $grand_total['amount_actual']=$outlet_total['amount_actual']=0;
        $grand_total['invoice_old_id']=$outlet_total['invoice_old_id']=0;
        $grand_total['invoice_new_id']=$outlet_total['invoice_new_id']=0;
        $grand_total['remarks']=$outlet_total['remarks']='';
        $grand_total['status']=$outlet_total['status']='Active';
        $items=array();
        $prev_outlet_name='';
        $first_row=true;
        foreach($results as $result)
        {
            if(!$first_row)
            {
                if($prev_outlet_name!=$result['outlet_name'])
                {
                    $items[]=$this->get_outlet_invoice_row($outlet_total);
                    $outlet_total['amount_total']=0;
                    $outlet_total['amount_discount']=0;
                    $outlet_total['amount_payable']=0;
                    $outlet_total['amount_actual']=0;
                    $prev_outlet_name=$result['outlet_name'];
                }
                else
                {
                    $result['outlet_name']='';
                }
            }
            else
            {
                $prev_outlet_name=$result['outlet_name'];
                $first_row=false;
            }
            $result['amount_discount']=$result['amount_total']-$result['amount_payable'];
            if($result['status']==$this->config->item('system_status_active'))
            {
                $result['amount_actual']=$result['amount_payable'];
                $outlet_total['amount_total']+=$result['amount_total'];
                $grand_total['amount_total']+=$result['amount_total'];

                $outlet_total['amount_discount']+=$result['amount_discount'];
                $grand_total['amount_discount']+=$result['amount_discount'];


            }
            else
            {
                if($result['date_sale']<$date_start)
                {
                    $result['amount_actual']=0-$result['amount_payable'];

                    $outlet_total['amount_total']-=$result['amount_total'];
                    $grand_total['amount_total']-=$result['amount_total'];

                    $outlet_total['amount_discount']-=$result['amount_discount'];
                    $grand_total['amount_discount']-=$result['amount_discount'];
                }
                elseif($result['date_canceled']>$date_end)
                {
                    $result['amount_actual']=$result['amount_payable'];

                    $outlet_total['amount_total']+=$result['amount_total'];
                    $grand_total['amount_total']+=$result['amount_total'];

                    $outlet_total['amount_discount']+=$result['amount_discount'];
                    $grand_total['amount_discount']+=$result['amount_discount'];

                }
                else
                {
                    $result['amount_actual']=0;
                }

            }
            $outlet_total['amount_actual']+=$result['amount_actual'];
            $grand_total['amount_actual']+=$result['amount_actual'];
            $items[]=$this->get_outlet_invoice_row($result);
        }
        $items[]=$this->get_outlet_invoice_row($outlet_total);
        $items[]=$this->get_outlet_invoice_row($grand_total);
        $this->json_return($items);


    }
    private function get_outlet_invoice_row($info)
    {
        $row=array();
        $row['id']=$info['id'];
        $row['outlet_name']=$info['outlet_name'];
        if($info['date_sale']>0)
        {
            $row['date_sale']=System_helper::display_date_time($info['date_sale']);
        }
        else
        {
            $row['date_sale']=$info['date_sale'];
        }
        if($info['date_canceled']>0)
        {
            $row['date_canceled']=System_helper::display_date_time($info['date_canceled']);
        }
        else
        {
            $row['date_canceled']='';
        }
        if($info['id']>0)
        {
            $row['invoice_no']=System_helper::get_invoice_barcode($info['id']);
        }
        else
        {
            $row['invoice_no']='';
        }
        $row['farmer_name']=$info['farmer_name'];
        if($info['amount_total']>0)
        {
            $row['amount_total']=number_format($info['amount_total'],2);
        }
        else
        {
            $row['amount_total']='';
        }
        if($info['amount_discount']>0)
        {
            $row['amount_discount']=number_format($info['amount_discount'],2);
        }
        else
        {
            $row['amount_discount']='';
        }
        /*if(($info['amount_total']-$info['amount_payable'])>0)
        {
            $row['amount_discount']=number_format($info['amount_total']-$info['amount_payable'],2);
        }
        else
        {
            $row['amount_discount']='';
        }*/
        if($info['amount_payable']>0)
        {
            $row['amount_payable']=number_format($info['amount_payable'],2);
        }
        else
        {
            $row['amount_payable']='';
        }
        if($info['amount_actual']!=0)
        {
            $row['amount_actual']=number_format($info['amount_actual'],2);
        }
        else
        {
            $row['amount_actual']='';
        }
        if($info['invoice_old_id']>0)
        {
            $row['invoice_old_id']=System_helper::get_invoice_barcode($info['invoice_old_id']);
        }
        else
        {
            $row['invoice_old_id']='';
        }
        if($info['invoice_new_id']>0)
        {
            $row['invoice_new_id']=System_helper::get_invoice_barcode($info['invoice_new_id']);
        }
        else
        {
            $row['invoice_new_id']='';
        }
        $row['remarks']=$info['remarks'];
        $row['status']=$info['status'];
        if($info['id']>0)
        {
            $row['details_button']=true;
        }
        else
        {
            $row['details_button']=false;
        }
        return $row;

    }
    private function system_details_invoice($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else if(($this->input->post('id')))
            {
                $item_id=$this->input->post('id');
            }
            else
            {
                $item_id=$id;
            }
            $this->db->from($this->config->item('table_pos_sale').' sale');
            $this->db->select('sale.*');
            $this->db->select('cus.name outlet_name,cus.name_short outlet_short_name');
            $this->db->select('f.name farmer_name,f.mobile_no,f.nid,f.address');
            $this->db->select('ft.name type_name');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id =sale.customer_id','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.type_id','INNER');
            $this->db->where('sale.id',$item_id);

            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details',$item_id,'Trying to access Invalid Sale id');
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
            $this->db->from($this->config->item('table_pos_sale_details').' sd');
            $this->db->select('sd.*');
            $this->db->select('v.name variety_name');
            $this->db->select('type.name type_name');
            $this->db->select('crop.name crop_name');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id =sd.variety_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id =type.crop_id','INNER');
            $this->db->where('sd.sale_id',$item_id);

            $data['details']=$this->db->get()->result_array();

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            if($data['item']['user_canceled']>0)
            {
                $user_ids[$data['item']['user_canceled']]=$data['item']['user_canceled'];
            }

            $data['users']=System_helper::get_users_info($user_ids);
            /*$this->db->from($this->config->item('table_pos_setup_farmer_outlet').' fo');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id = fo.farmer_id','INNER');
            $this->db->where('fo.revision',1);
            $this->db->where('fo.farmer_id',$item_id);
            $data['assigned_outlets']=$this->db->get()->result_array();*/

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#popup_content","html"=>$this->load->view($this->controller_url."/details_invoice",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            //$ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_farmer_invoice()
    {

        $farmer_id=$this->input->post('farmer_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');


        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->select('f.name farmer_name');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where('sale.farmer_id',$farmer_id);
        $where='(sale.date_sale >='.$date_start.' AND sale.date_sale <='.$date_end.')';
        $where.=' OR (sale.date_canceled >='.$date_start.' AND sale.date_canceled <='.$date_end.')';

        $this->db->where('('.$where.')');
        $this->db->order_by('f.ordering DESC');
        $this->db->order_by('f.id ASC');
        $this->db->order_by('sale.date_sale DESC');

        $results=$this->db->get()->result_array();
        $farmer_total=array();
        $grand_total=array();
        $grand_total['id']=$farmer_total['id']=0;
        $grand_total['date_sale']='Grand Total';
        $farmer_total['date_sale']='Farmer Total';
        $grand_total['date_canceled']=$farmer_total['date_canceled']=0;
        $grand_total['invoice_no']=$farmer_total['invoice_no']=0;
        $grand_total['farmer_name']=$farmer_total['farmer_name']='';
        $grand_total['amount_total']=$farmer_total['amount_total']=0;
        $grand_total['amount_discount']=$farmer_total['amount_discount']=0;
        $grand_total['amount_payable']=$farmer_total['amount_payable']=0;
        $grand_total['amount_actual']=$farmer_total['amount_actual']=0;
        $grand_total['invoice_old_id']=$farmer_total['invoice_old_id']=0;
        $grand_total['invoice_new_id']=$farmer_total['invoice_new_id']=0;
        $grand_total['remarks']=$farmer_total['remarks']='';
        $grand_total['status']=$farmer_total['status']='Active';
        $items=array();
        $prev_farmer_name='';
        $first_row=true;
        foreach($results as $result)
        {
            if(!$first_row)
            {
                if($prev_farmer_name!=$result['farmer_name'])
                {
                    $items[]=$this->get_farmer_invoice_row($farmer_total);
                    $farmer_total['amount_actual']=0;
                    $prev_farmer_name=$result['farmer_name'];
                }
                else
                {
                    $result['farmer_name']='';
                }
            }
            else
            {
                $prev_farmer_name=$result['farmer_name'];
                $first_row=false;
            }
            if($result['status']==$this->config->item('system_status_active'))
            {
                $result['amount_actual']=$result['amount_payable'];
            }
            else
            {
                if($result['date_sale']<$date_start)
                {
                    $result['amount_actual']=0-$result['amount_payable'];
                }
                elseif($result['date_canceled']>$date_end)
                {
                    $result['amount_actual']=$result['amount_payable'];
                }
                else
                {
                    $result['amount_actual']=0;
                }

            }
            $farmer_total['amount_actual']+=$result['amount_actual'];
            $grand_total['amount_actual']+=$result['amount_actual'];
            $items[]=$this->get_farmer_invoice_row($result);
        }
        $items[]=$this->get_farmer_invoice_row($grand_total);
        $this->json_return($items);


    }
    private function get_farmer_invoice_row($info)
    {
        $row=array();
        $row['id']=$info['id'];
        $row['farmer_name']=$info['farmer_name'];
        if($info['date_sale']>0)
        {
            $row['date_sale']=System_helper::display_date_time($info['date_sale']);
        }
        else
        {
            $row['date_sale']=$info['date_sale'];
        }
        if($info['date_canceled']>0)
        {
            $row['date_canceled']=System_helper::display_date_time($info['date_canceled']);
        }
        else
        {
            $row['date_canceled']='';
        }
        if($info['id']>0)
        {
            $row['invoice_no']=System_helper::get_invoice_barcode($info['id']);
        }
        else
        {
            $row['invoice_no']='';
        }
        if($info['amount_total']>0)
        {
            $row['amount_total']=number_format($info['amount_total'],2);
        }
        else
        {
            $row['amount_total']='';
        }
        if(($info['amount_total']-$info['amount_payable'])>0)
        {
            $row['amount_discount']=number_format($info['amount_total']-$info['amount_payable'],2);
        }
        else
        {
            $row['amount_discount']='';
        }
        if($info['amount_payable']>0)
        {
            $row['amount_payable']=number_format($info['amount_payable'],2);
        }
        else
        {
            $row['amount_payable']='';
        }
        if($info['amount_actual']!=0)
        {
            $row['amount_actual']=number_format($info['amount_actual'],2);
        }
        else
        {
            $row['amount_actual']='';
        }
        if($info['invoice_old_id']>0)
        {
            $row['invoice_old_id']=System_helper::get_invoice_barcode($info['invoice_old_id']);
        }
        else
        {
            $row['invoice_old_id']='';
        }
        if($info['invoice_new_id']>0)
        {
            $row['invoice_new_id']=System_helper::get_invoice_barcode($info['invoice_new_id']);
        }
        else
        {
            $row['invoice_new_id']='';
        }
        $row['remarks']=$info['remarks'];
        $row['status']=$info['status'];
        if($info['id']>0)
        {
            $row['details_button']=true;
        }
        else
        {
            $row['details_button']=false;
        }
        return $row;

    }
    private function system_get_items_farmers_sale()
    {

        $customer_id=$this->input->post('customer_id');
        $farmer_type=$this->input->post('farmer_type');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        //total sales
        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.farmer_id');
        $this->db->select('SUM(sale.amount_payable) amount_payable');


        $this->db->where('sale.customer_id',$customer_id);
        if($farmer_type>0)
        {
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
            $this->db->where('f.type_id',$farmer_type);
        }
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->where('sale.date_sale <=',$date_end);
        $this->db->group_by('sale.farmer_id');
        $results=$this->db->get()->result_array();
        $sale_info=array();
        $farmer_ids=array();
        foreach($results as $result)
        {
            $sale_info[$result['farmer_id']]=$result;
            $sale_info[$result['farmer_id']]['amount_cancel']=0;
            $farmer_ids[$result['farmer_id']]=$result['farmer_id'];
        }
        //total cancel
        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.farmer_id');
        $this->db->select('SUM(sale.amount_payable) amount_cancel');


        $this->db->where('sale.customer_id',$customer_id);
        $this->db->where('sale.status',$this->config->item('system_status_inactive'));
        if($farmer_type>0)
        {
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
            $this->db->where('f.type_id',$farmer_type);
        }
        $this->db->where('sale.date_canceled >=',$date_start);
        $this->db->where('sale.date_canceled <=',$date_end);
        $this->db->group_by('sale.farmer_id');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($sale_info[$result['farmer_id']]))
            {
                $sale_info[$result['farmer_id']]['amount_cancel']=$result['amount_cancel'];
            }
            else
            {
                $sale_info[$result['farmer_id']]=$result;
                $sale_info[$result['farmer_id']]['amount_payable']=0;
            }
            $farmer_ids[$result['farmer_id']]=$result['farmer_id'];
        }
        $items=array();
        $grand_total=array();
        $grand_total['farmer_id']=0;
        $grand_total['farmer_name']='Grand Total';
        $grand_total['amount_payable']=0;
        $grand_total['amount_cancel']=0;
        if(sizeof($farmer_ids)>0)
        {
            //farmer info
            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
            $this->db->select('f.id,f.name farmer_name');
            $this->db->where_in('f.id',$farmer_ids);
            $this->db->order_by('f.ordering DESC');
            $this->db->order_by('f.id ASC');
            $results=$this->db->get()->result_array();


            foreach($results as $result)
            {
                $info=$sale_info[$result['id']];
                $info['farmer_name']=$result['farmer_name'];
                $grand_total['amount_payable']+=$info['amount_payable'];
                $grand_total['amount_cancel']+=$info['amount_cancel'];
                $items[]=$this->get_farmer_sale_row($info);
            }

        }
        $items[]=$this->get_farmer_sale_row($grand_total);
        $this->json_return($items);
    }
    private function get_farmer_sale_row($info)
    {
        $row=array();
        $row['id']=$info['farmer_id'];
        $row['farmer_name']=$info['farmer_name'];

        if($info['amount_payable']>0)
        {
            $row['amount_payable']=number_format($info['amount_payable'],2);
        }
        else
        {
            $row['amount_payable']='';
        }
        if($info['amount_cancel']>0)
        {
            $row['amount_cancel']=number_format($info['amount_cancel'],2);
        }
        else
        {
            $row['amount_cancel']='';
        }
        if(($info['amount_payable']-$info['amount_cancel'])!=0)
        {
            $row['amount_actual']=number_format(($info['amount_payable']-$info['amount_cancel']),2);
        }
        else
        {
            $row['amount_actual']='';
        }
        return $row;

    }
    private function system_get_items_variety_sale()
    {
        $items=array();
        $customer_id=$this->input->post('customer_id');
        $farmer_type=$this->input->post('farmer_type');
        $farmer_id=$this->input->post('farmer_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        //get variety infos
        $this->db->from($this->config->item('table_pos_sale_details').' pod');
        $this->db->select('pod.variety_id,pod.pack_size_id,pod.pack_size');
        $this->db->select('v.name variety_name');
        $this->db->select('type.id type_id,type.name type_name');

        $this->db->select('crop.id crop_id,crop.name crop_name');
        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id =pod.sale_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id =pod.variety_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id =type.crop_id','INNER');
        $this->db->where('sale.customer_id',$customer_id);
        $where='(sale.date_sale >='.$date_start.' AND sale.date_sale <='.$date_end.')';
        $where.=' OR (sale.date_canceled >='.$date_start.' AND sale.date_canceled <='.$date_end.')';
        $this->db->where('('.$where.')');
        if($crop_id>0)
        {
            $this->db->where('crop.id',$crop_id);
            if($crop_type_id>0)
            {
                $this->db->where('type.id',$crop_type_id);
                if($variety_id>0)
                {
                    $this->db->where('v.id',$variety_id);
                }
            }
        }
        if($farmer_id>0)
        {
            $this->db->where('sale.farmer_id',$farmer_id);
        }
        $this->db->group_by(array('pod.variety_id','pod.pack_size_id'));
        $this->db->order_by('crop.ordering ASC');
        $this->db->order_by('type.ordering ASC');
        $this->db->order_by('v.ordering ASC');
        $results=$this->db->get()->result_array();
        $variety_ids=array();
        $varieties=array();
        foreach($results as $result)
        {
            $varieties[$result['variety_id']][$result['pack_size_id']]=$result;
            $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_quantity']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_quantity_kg']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_amount']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_discount']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_payable']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_quantity']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_quantity_kg']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_amount']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_discount']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_payable']=0;
            $variety_ids[$result['variety_id']]=$result['variety_id'];
        }
        if(sizeof($variety_ids)>0)
        {
            //sale count start to end
            $this->db->from($this->config->item('table_pos_sale_details').' pod');
            $this->db->select('pod.variety_id,pod.pack_size_id,pod.pack_size');
            $this->db->select('SUM(pod.quantity_sale) invoice_quantity');
            $this->db->select('SUM(pod.quantity_sale * pod.pack_size) invoice_quantity_kg');
            $this->db->select('SUM(pod.quantity_sale * pod.price_unit) invoice_amount');
            $this->db->select('SUM(pod.quantity_sale * pod.price_unit * sale.discount_percentage/100) invoice_discount');
            $this->db->join($this->config->item('table_pos_sale').' sale','sale.id =pod.sale_id','INNER');
            if($farmer_type>0)
            {
                $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
                $this->db->where('f.type_id',$farmer_type);
                if($farmer_id>0)
                {
                    $this->db->where('sale.farmer_id',$farmer_id);
                }
            }
            $this->db->where('pod.revision',1);
            $this->db->where('sale.customer_id',$customer_id);
            $this->db->where('sale.date_sale <=',$date_end);
            $this->db->where('sale.date_sale >=',$date_start);
            $this->db->where_in('pod.variety_id',$variety_ids);
            $this->db->group_by(array('pod.variety_id','pod.pack_size_id'));
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_quantity']=$result['invoice_quantity'];
                $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_quantity_kg']=$result['invoice_quantity_kg'];
                $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_amount']=$result['invoice_amount'];
                $varieties[$result['variety_id']][$result['pack_size_id']]['invoice_discount']=$result['invoice_discount'];
            }

            //sale cancel start to end
            $this->db->from($this->config->item('table_pos_sale_details').' pod');
            $this->db->select('pod.variety_id,pod.pack_size_id,pod.pack_size');
            $this->db->select('SUM(pod.quantity_sale) cancel_quantity');
            $this->db->select('SUM(pod.quantity_sale * pod.pack_size) cancel_quantity_kg');
            $this->db->select('SUM(pod.quantity_sale * pod.price_unit) cancel_amount');
            $this->db->select('SUM(pod.quantity_sale * pod.price_unit * sale.discount_percentage/100) cancel_discount');
            $this->db->join($this->config->item('table_pos_sale').' sale','sale.id =pod.sale_id','INNER');
            if($farmer_type>0)
            {
                $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
                $this->db->where('f.type_id',$farmer_type);
                if($farmer_id>0)
                {
                    $this->db->where('sale.farmer_id',$farmer_id);
                }
            }
            $this->db->where('sale.status',$this->config->item('system_status_inactive'));
            $this->db->where('pod.revision',1);
            $this->db->where('sale.customer_id',$customer_id);
            $this->db->where('sale.date_canceled <=',$date_end);
            $this->db->where('sale.date_canceled >=',$date_start);
            $this->db->where_in('pod.variety_id',$variety_ids);
            $this->db->group_by(array('pod.variety_id','pod.pack_size_id'));
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_quantity']=$result['cancel_quantity'];
                $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_quantity_kg']=$result['cancel_quantity_kg'];
                $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_amount']=$result['cancel_amount'];
                $varieties[$result['variety_id']][$result['pack_size_id']]['cancel_discount']=$result['cancel_discount'];
            }
            $type_total=array();
            $crop_total=array();
            $grand_total=array();
            $type_total['crop_name']='';
            $type_total['type_name']='';
            $type_total['variety_name']='Total Type';

            $crop_total['crop_name']='';
            $crop_total['type_name']='Total Crop';
            $crop_total['variety_name']='';

            $grand_total['crop_name']='Grand Total';
            $grand_total['type_name']='';
            $grand_total['variety_name']='';

            $grand_total['pack_size']=$crop_total['pack_size']=$type_total['pack_size']='';
            $grand_total['invoice_quantity']=$crop_total['invoice_quantity']=$type_total['invoice_quantity']=0;
            $grand_total['invoice_quantity_kg']=$crop_total['invoice_quantity_kg']=$type_total['invoice_quantity_kg']=0;
            $grand_total['invoice_amount']=$crop_total['invoice_amount']=$type_total['invoice_amount']=0;
            $grand_total['invoice_discount']=$crop_total['invoice_discount']=$type_total['invoice_discount']=0;
            $grand_total['invoice_payable']=$crop_total['invoice_payable']=$type_total['invoice_payable']=0;
            $grand_total['cancel_quantity']=$crop_total['cancel_quantity']=$type_total['cancel_quantity']=0;
            $grand_total['cancel_quantity_kg']=$crop_total['cancel_quantity_kg']=$type_total['cancel_quantity_kg']=0;
            $grand_total['cancel_amount']=$crop_total['cancel_amount']=$type_total['cancel_amount']=0;
            $grand_total['cancel_discount']=$crop_total['cancel_discount']=$type_total['cancel_discount']=0;
            $grand_total['cancel_payable']=$crop_total['cancel_payable']=$type_total['cancel_payable']=0;
            $prev_crop_name='';
            $prev_type_name='';
            $first_row=true;
            foreach($varieties as $pack)
            {
                foreach($pack as $info)
                {
                    if(!$first_row)
                    {
                        if($prev_crop_name!=$info['crop_name'])
                        {
                            $items[]=$this->get_variety_sale_row($type_total);
                            $items[]=$this->get_variety_sale_row($crop_total);
                            $crop_total['invoice_quantity']=$type_total['invoice_quantity']=0;
                            $crop_total['invoice_quantity_kg']=$type_total['invoice_quantity_kg']=0;
                            $crop_total['invoice_amount']=$type_total['invoice_amount']=0;
                            $crop_total['invoice_discount']=$type_total['invoice_discount']=0;
                            $crop_total['invoice_payable']=$type_total['invoice_payable']=0;
                            $crop_total['cancel_quantity']=$type_total['cancel_quantity']=0;
                            $crop_total['cancel_quantity_kg']=$type_total['cancel_quantity_kg']=0;
                            $crop_total['cancel_amount']=$type_total['cancel_amount']=0;
                            $crop_total['cancel_discount']=$type_total['cancel_discount']=0;
                            $crop_total['cancel_payable']=$type_total['cancel_payable']=0;
                            $prev_crop_name=$info['crop_name'];
                            $prev_type_name=$info['type_name'];
                            //sum and reset type total
                            //sum and reset crop total
                        }
                        elseif($prev_type_name!=$info['type_name'])
                        {
                            $items[]=$this->get_variety_sale_row($type_total);
                            $type_total['invoice_quantity']=0;
                            $type_total['invoice_quantity_kg']=0;
                            $type_total['invoice_amount']=0;
                            $type_total['invoice_discount']=0;
                            $type_total['invoice_payable']=0;
                            $type_total['cancel_quantity']=0;
                            $type_total['cancel_quantity_kg']=0;
                            $type_total['cancel_amount']=0;
                            $type_total['cancel_discount']=0;
                            $type_total['cancel_payable']=0;
                            $info['crop_name']='';
                            $prev_type_name=$info['type_name'];
                            //sum and reset type total
                        }
                        else
                        {
                            $info['crop_name']='';
                            $info['type_name']='';
                        }
                    }
                    else
                    {
                        $prev_crop_name=$info['crop_name'];
                        $prev_type_name=$info['type_name'];
                        $first_row=false;
                    }
                    $type_total['invoice_quantity']+=$info['invoice_quantity'];
                    $crop_total['invoice_quantity']+=$info['invoice_quantity'];
                    $grand_total['invoice_quantity']+=$info['invoice_quantity'];
                    $type_total['invoice_quantity_kg']+=$info['invoice_quantity_kg'];
                    $crop_total['invoice_quantity_kg']+=$info['invoice_quantity_kg'];
                    $grand_total['invoice_quantity_kg']+=$info['invoice_quantity_kg'];
                    $type_total['invoice_amount']+=$info['invoice_amount'];
                    $crop_total['invoice_amount']+=$info['invoice_amount'];
                    $grand_total['invoice_amount']+=$info['invoice_amount'];
                    $type_total['invoice_discount']+=$info['invoice_discount'];
                    $crop_total['invoice_discount']+=$info['invoice_discount'];
                    $grand_total['invoice_discount']+=$info['invoice_discount'];

                    $type_total['cancel_quantity']+=$info['cancel_quantity'];
                    $crop_total['cancel_quantity']+=$info['cancel_quantity'];
                    $grand_total['cancel_quantity']+=$info['cancel_quantity'];
                    $type_total['cancel_quantity_kg']+=$info['cancel_quantity_kg'];
                    $crop_total['cancel_quantity_kg']+=$info['cancel_quantity_kg'];
                    $grand_total['cancel_quantity_kg']+=$info['cancel_quantity_kg'];
                    $type_total['cancel_amount']+=$info['cancel_amount'];
                    $crop_total['cancel_amount']+=$info['cancel_amount'];
                    $grand_total['cancel_amount']+=$info['cancel_amount'];
                    $type_total['cancel_discount']+=$info['cancel_discount'];
                    $crop_total['cancel_discount']+=$info['cancel_discount'];
                    $grand_total['cancel_discount']+=$info['cancel_discount'];
                    $items[]=$this->get_variety_sale_row($info);
                }
            }
            $items[]=$this->get_variety_sale_row($type_total);
            $items[]=$this->get_variety_sale_row($crop_total);
            $items[]=$this->get_variety_sale_row($grand_total);
        }
        $this->json_return($items);
    }
    private function get_variety_sale_row($info)
    {
        $row=array();
        $row['crop_name']=$info['crop_name'];
        $row['type_name']=$info['type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];

        if($info['invoice_quantity']>0)
        {
            $row['invoice_quantity']=$info['invoice_quantity'];
        }
        else
        {
            $row['invoice_quantity']='';
        }
        if($info['invoice_amount']>0)
        {
            $row['invoice_amount']=number_format($info['invoice_amount'],2);
        }
        else
        {
            $row['invoice_amount']='';
        }
        if($info['invoice_discount']>0)
        {
            $row['invoice_discount']=number_format($info['invoice_discount'],2);
        }
        else
        {
            $row['invoice_discount']='';
        }
        if(($info['invoice_amount']-$info['invoice_discount'])!=0)
        {
            $row['invoice_payable']=number_format($info['invoice_amount']-$info['invoice_discount'],2);
        }
        else
        {
            $row['invoice_payable']='';
        }

        if($info['cancel_quantity']>0)
        {
            $row['cancel_quantity']=$info['cancel_quantity'];
        }
        else
        {
            $row['cancel_quantity']='';
        }
        if($info['cancel_amount']>0)
        {
            $row['cancel_amount']=number_format($info['cancel_amount'],2);
        }
        else
        {
            $row['cancel_amount']='';
        }
        if($info['cancel_discount']>0)
        {
            $row['cancel_discount']=number_format($info['cancel_discount'],2);
        }
        else
        {
            $row['cancel_discount']='';
        }
        if(($info['cancel_amount']-$info['cancel_discount'])!=0)
        {
            $row['cancel_payable']=number_format($info['cancel_amount']-$info['cancel_discount'],2);
        }
        else
        {
            $row['cancel_payable']='';
        }
        if($info['invoice_quantity']-$info['cancel_quantity']!=0)
        {
            $row['actual_quantity']=$info['invoice_quantity']-$info['cancel_quantity'];
        }
        else
        {
            $row['actual_quantity']='';
        }
        if(($info['invoice_quantity_kg']-$info['cancel_quantity_kg'])!=0)
        {
            $row['actual_quantity_kg']=number_format((($info['invoice_quantity_kg']-$info['cancel_quantity_kg'])/1000),3,'.','');
        }
        else
        {
            $row['actual_quantity_kg']='';
        }


        if(($info['invoice_amount']-$info['cancel_amount'])!=0)
        {
            $row['actual_amount']=number_format($info['invoice_amount']-$info['cancel_amount'],2);
        }
        else
        {
            $row['actual_amount']='';
        }
        if(($info['invoice_discount']-$info['cancel_discount'])!=0)
        {
            $row['actual_discount']=number_format($info['invoice_discount']-$info['cancel_discount'],2);
        }
        else
        {
            $row['actual_discount']='';
        }
        if((($info['invoice_amount']-$info['cancel_amount'])-($info['invoice_discount']-$info['cancel_discount']))!=0)
        {
            $row['actual_payable']=number_format((($info['invoice_amount']-$info['cancel_amount'])-($info['invoice_discount']-$info['cancel_discount'])),2);
        }
        else
        {
            $row['actual_payable']='';
        }
        return $row;

    }
}
