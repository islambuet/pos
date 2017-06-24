<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_stock_min_max extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Reports_stock_min_max');
        $this->controller_url='reports_stock_min_max';
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
        elseif($action=='get_items')
        {
            $this->system_get_items();
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
            $data['title']="Min Max Stock Report Search";
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
            $reports=$this->input->post('report');
            $data['options']=$reports;
            if($reports['report_unit']=='weight')
            {
                $data['title']="Min Max Stock Report In Kg";
            }
            else
            {
                $data['title']="Min Max Stock Report In Quantity";
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
    private function system_get_items()
    {
        $report_unit=$this->input->post('report_unit');
        $customer_id=$this->input->post('customer_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        //get stock in and out from ems
        //sale receive and return quantity till end date
        $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_sales_po_receives').' por');
        $this->db->select('por.quantity_receive,por.quantity_bonus_receive,por.date_receive');
        $this->db->select('pod.pack_size_id,pod.pack_size');
        $this->db->select('pod.bonus_pack_size_id,pod.bonus_pack_size');
        $this->db->select('pod.quantity_return,pod.quantity_bonus_return,pod.date_return');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->select('type.id type_id,type.name type_name');
        $this->db->select('crop.id crop_id,crop.name crop_name');


        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_sales_po_details').' pod','pod.id =por.sales_po_detail_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_sales_po').' po','po.id =pod.sales_po_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id =pod.variety_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id =type.crop_id','INNER');

        $this->db->where('po.status_received',$this->config->item('system_status_receive'));
        $this->db->where('po.customer_id',$customer_id);
        $this->db->where('por.revision',1);
        $this->db->where('pod.revision',1);
        if($crop_id>0)
        {
            $this->db->where('crop.id',$crop_id);
        }
        if($crop_type_id>0)
        {
            $this->db->where('type.id',$crop_type_id);
        }
        if($variety_id>0)
        {
            $this->db->where('v.id',$variety_id);
        }
        $this->db->order_by('crop.ordering ASC');
        $this->db->order_by('type.ordering ASC');
        $this->db->order_by('v.ordering ASC');
        $results=$this->db->get()->result_array();
        $stocks=array();
        foreach($results as $result)
        {
            if(!(isset($stocks[$result['variety_id']][$result['pack_size_id']])))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['crop_id']=$result['crop_id'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['crop_name']=$result['crop_name'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['type_id']=$result['type_id'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['type_name']=$result['type_name'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['variety_id']=$result['variety_id'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['variety_name']=$result['variety_name'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['pack_size_id']=$result['pack_size_id'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['pack_size']=$result['pack_size'];
                $stocks[$result['variety_id']][$result['pack_size_id']]['stock_current']=0;
                $stocks[$result['variety_id']][$result['pack_size_id']]['stock_min']=0;
                $stocks[$result['variety_id']][$result['pack_size_id']]['stock_max']=0;
            }
            if($result['bonus_pack_size_id']>0)
            {
                if(!(isset($stocks[$result['variety_id']][$result['bonus_pack_size_id']])))
                {
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['crop_id']=$result['crop_id'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['crop_name']=$result['crop_name'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['type_id']=$result['type_id'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['type_name']=$result['type_name'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['variety_id']=$result['variety_id'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['variety_name']=$result['variety_name'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['pack_size_id']=$result['bonus_pack_size_id'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['pack_size']=$result['bonus_pack_size'];
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_current']=0;
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_min']=0;
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_max']=0;
                }
            }
            if($report_unit=='weight')
            {
                $result['quantity_receive']=$result['quantity_receive']*$result['pack_size'];
                $result['quantity_bonus_receive']=$result['quantity_bonus_receive']*$result['bonus_pack_size'];
                $result['quantity_return']=$result['quantity_return']*$result['pack_size'];
                $result['quantity_bonus_return']=$result['quantity_bonus_return']*$result['bonus_pack_size'];

            }

            $stocks[$result['variety_id']][$result['pack_size_id']]['stock_current']+=($result['quantity_receive']);
            if($result['bonus_pack_size_id']>0)
            {
                $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_current']+=($result['quantity_bonus_receive']);
            }

            if($result['date_return']>0)
            {

                $stocks[$result['variety_id']][$result['pack_size_id']]['stock_current']-=($result['quantity_return']);
                if($result['bonus_pack_size_id']>0)
                {
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_current']-=($result['quantity_bonus_return']);
                }

            }
        }
        $this->db->from($this->config->item('table_pos_sale_details').' pod');
        $this->db->select('pod.variety_id,pod.pack_size_id,pod.pack_size');
        $this->db->select('SUM(pod.quantity_sale) quantity');
        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id =pod.sale_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id =pod.variety_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');
        $this->db->where('sale.status',$this->config->item('system_status_active'));
        $this->db->where('pod.revision',1);
        $this->db->where('sale.customer_id',$customer_id);
        if($crop_id>0)
        {
            $this->db->where('type.crop_id',$crop_id);
        }
        if($crop_type_id>0)
        {
            $this->db->where('type.id',$crop_type_id);
        }
        if($variety_id>0)
        {
            $this->db->where('v.id',$variety_id);
        }
        $this->db->group_by(array('pod.variety_id','pod.pack_size_id'));
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            if($report_unit=='weight')
            {
                $result['quantity']=$result['quantity']*$result['pack_size'];
            }
            $stocks[$result['variety_id']][$result['pack_size_id']]['stock_current']-=$result['quantity'];
        }

        $min_max_stocks=array();
        $results=Query_helper::get_info($this->config->item('table_pos_setup_stock_min_max'),'*',array('customer_id ='.$customer_id));
        foreach($results as $result)
        {
            $min_max_stocks[$result['variety_id']][$result['pack_size_id']]=$result;
        }

        $items=array();
        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;
        foreach($stocks as $variety_id=>$varieties)
        {
            foreach($varieties as $pack_size_id=>$info)
            {
                if(!$first_row)
                {
                    if($prev_crop_name!=$info['crop_name'])
                    {
                        $prev_crop_name=$info['crop_name'];
                        $prev_type_name=$info['type_name'];
                        //sum and reset type total
                        //sum and reset crop total
                    }
                    elseif($prev_type_name!=$info['type_name'])
                    {
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

                if(isset($min_max_stocks[$variety_id][$pack_size_id]))
                {
                    if($report_unit=='weight')
                    {
                        $info['stock_min']=$min_max_stocks[$variety_id][$pack_size_id]['quantity_min']*$info['pack_size'];
                        $info['stock_max']=$min_max_stocks[$variety_id][$pack_size_id]['quantity_max']*$info['pack_size'];
                    }
                    else
                    {
                        $info['stock_min']=$min_max_stocks[$variety_id][$pack_size_id]['quantity_min'];
                        $info['stock_max']=$min_max_stocks[$variety_id][$pack_size_id]['quantity_max'];
                    }
                }
                $items[]=$this->get_grid_row($info,$report_unit);
            }
        }
        $this->json_return($items);


    }
    private function get_grid_row($info,$report_unit)
    {
        $row=array();
        $row['crop_name']=$info['crop_name'];
        $row['type_name']=$info['type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];
        if($report_unit=='weight')
        {
            $row['stock_current']=number_format($info['stock_current']/1000,3,'.','');
        }
        else
        {
            $row['stock_current']=$info['stock_current'];
        }
        if($info['stock_min']==0)
        {
            $row['stock_min']='';
        }
        else
        {
            if($report_unit=='weight')
            {
                $row['stock_min']=number_format($info['stock_min']/1000,3,'.','');
            }
            else
            {
                $row['stock_min']=$info['stock_min'];
            }
        }
        if(($info['stock_current']-$info['stock_min'])==0)
        {
            $row['stock_dif_min']='';
        }
        else
        {
            if($report_unit=='weight')
            {
                $row['stock_dif_min']=number_format(($info['stock_current']-$info['stock_min'])/1000,3,'.','');
            }
            else
            {
                $row['stock_dif_min']=($info['stock_current']-$info['stock_min']);
            }
        }

        if($info['stock_max']==0)
        {
            $row['stock_max']='';
            $row['stock_dif_max']='';

        }
        else
        {
            if($report_unit=='weight')
            {
                $row['stock_max']=number_format($info['stock_max']/1000,3,'.','');
                $row['stock_dif_max']=number_format(($info['stock_max']-$info['stock_current'])/1000,3,'.','');
            }
            else
            {
                $row['stock_max']=$info['stock_max'];
                $row['stock_dif_max']=$info['stock_max']-$info['stock_current'];
            }
        }

        return $row;

    }
}
