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
            $report_name=$this->input->post('report_name');
            if($report_name=='outlet_invoice')
            {
                $ajax['system_content'][]=array("id"=>"#report_search_container","html"=>$this->load->view($this->controller_url."/search_outlet_invoice",$data,true));
            }
            else
            {
                $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
                $ajax['system_page_url']=site_url($this->controller_url);
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
                if(!isset($reports['customer_ids']))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Please Select at least one outlet';
                    $this->json_return($ajax);
                    die();
                }
                $data['title']="Invoice Wise Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_outlet_invoice",$data,true));
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
    public function get_items_outlet_invoice()
    {

        $customer_ids=$this->input->post('customer_ids');

        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name farmer_name');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id =sale.customer_id','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where_in('customer_id',$customer_ids);
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
                    $items[]=$this->get_grid_row($outlet_total);
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
                else
                {
                    $result['amount_actual']=0;
                }

            }
            $outlet_total['amount_actual']+=$result['amount_actual'];
            $grand_total['amount_actual']+=$result['amount_actual'];
            $items[]=$this->get_grid_row($result);
        }
        $items[]=$this->get_grid_row($outlet_total);
        $items[]=$this->get_grid_row($grand_total);
        $this->json_return($items);


    }
    private function get_grid_row($info)
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
