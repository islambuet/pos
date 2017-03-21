<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_stock extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Reports_stock');
        $this->controller_url='reports_stock';
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
            $data['title']="Stock Report Search";
            $ajax['status']=true;
            $data['outlets']=$this->user_outlets;
            $data['date_start']='';
            $data['date_end']=System_helper::display_date(time());
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
            $reports['date_end']=System_helper::get_time($reports['date_end']);
            $reports['date_end']=$reports['date_end']+3600*24-1;
            $reports['date_start']=System_helper::get_time($reports['date_start']);
            if($reports['date_start']>=$reports['date_end'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Starting Date should be less than End date';
                $this->json_return($ajax);
            }

            $keys=',';

            foreach($reports as $elem=>$value)
            {
                $keys.=$elem.":'".$value."',";
            }

            $data['keys']=trim($keys,',');
            if($reports['report_type']=='weight')
            {
                $data['title']="Stock Report In Kg";
            }
            else
            {
                $data['title']="Stock Report In Quantity";
            }

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list",$data,true));
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
    public function get_items()
    {
        $report_type=$this->input->post('report_type');
        $customer_id=$this->input->post('customer_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

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
        $this->db->where('por.date_receive <=',$date_end);
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
                $stocks[$result['variety_id']][$result['pack_size_id']]['starting_stock']=0;
                $stocks[$result['variety_id']][$result['pack_size_id']]['stock_in']=0;
                $stocks[$result['variety_id']][$result['pack_size_id']]['stock_return']=0;
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
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['starting_stock']=0;
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_in']=0;
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_return']=0;
                }
            }
            if($report_type=='weight')
            {
                $result['quantity_receive']=$result['quantity_receive']*$result['pack_size'];
                $result['quantity_bonus_receive']=$result['quantity_bonus_receive']*$result['bonus_pack_size'];
                $result['quantity_return']=$result['quantity_return']*$result['pack_size'];
                $result['quantity_bonus_return']=$result['quantity_bonus_return']*$result['bonus_pack_size'];

            }
            if(($date_start>0) && ($date_start>$result['date_receive']))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['starting_stock']+=($result['quantity_receive']);
                if($result['bonus_pack_size_id']>0)
                {
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['starting_stock']+=($result['quantity_bonus_receive']);
                }
            }
            else
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['stock_in']+=($result['quantity_receive']);
                if($result['bonus_pack_size_id']>0)
                {
                    $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_in']+=($result['quantity_bonus_receive']);
                }
            }
            if($result['date_return']>0)
            {
                if(($date_start>0) && ($date_start>$result['date_return']))
                {
                    $stocks[$result['variety_id']][$result['pack_size_id']]['starting_stock']-=($result['quantity_return']);
                    if($result['bonus_pack_size_id']>0)
                    {
                        $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['starting_stock']-=($result['quantity_bonus_return']);
                    }
                }
                else
                {
                    $stocks[$result['variety_id']][$result['pack_size_id']]['stock_return']+=($result['quantity_return']);
                    if($result['bonus_pack_size_id']>0)
                    {
                        $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['stock_return']+=($result['quantity_bonus_return']);
                    }
                }
            }
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
        $grand_total['starting_stock']=$crop_total['starting_stock']=$type_total['starting_stock']=0;
        $grand_total['stock_in']=$crop_total['stock_in']=$type_total['stock_in']=0;
        $grand_total['stock_return']=$crop_total['stock_return']=$type_total['stock_return']=0;

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
                        $items[]=$this->get_grid_row($type_total,$report_type);
                        $items[]=$this->get_grid_row($crop_total,$report_type);
                        $crop_total['starting_stock']=$type_total['starting_stock']=0;
                        $crop_total['stock_in']=$type_total['stock_in']=0;
                        $crop_total['stock_return']=$type_total['stock_return']=0;
                        //sum and reset type total
                        //sum and reset crop total
                    }
                    elseif($prev_type_name!=$info['type_name'])
                    {
                        $items[]=$this->get_grid_row($type_total,$report_type);
                        $type_total['starting_stock']=0;
                        $type_total['stock_in']=0;
                        $type_total['stock_return']=0;
                        $info['crop_name']='';
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
                $grand_total['starting_stock']+=$info['starting_stock'];
                $crop_total['starting_stock']+=$info['starting_stock'];
                $type_total['starting_stock']+=$info['starting_stock'];
                $grand_total['stock_in']+=$info['stock_in'];
                $crop_total['stock_in']+=$info['stock_in'];
                $type_total['stock_in']+=$info['stock_in'];
                $grand_total['stock_return']+=$info['stock_return'];
                $crop_total['stock_return']+=$info['stock_return'];
                $type_total['stock_return']+=$info['stock_return'];
                $items[]=$this->get_grid_row($info,$report_type);
            }
        }
        $items[]=$this->get_grid_row($type_total,$report_type);
        $items[]=$this->get_grid_row($crop_total,$report_type);
        $items[]=$this->get_grid_row($grand_total,$report_type);
        $this->json_return($items);


    }
    private function get_grid_row($info,$report_type)
    {
        $row=array();
        $row['crop_name']=$info['crop_name'];
        $row['type_name']=$info['type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];
        if($report_type=='weight')
        {
            $row['starting_stock']=number_format($info['starting_stock']/1000,3,'.','');
        }
        else
        {
            $row['starting_stock']=$info['starting_stock'];
        }
        if($report_type=='weight')
        {
            $row['stock_in']=number_format($info['stock_in']/1000,3,'.','');
        }
        else
        {
            $row['stock_in']=$info['stock_in'];
        }
        if($report_type=='weight')
        {
            $row['stock_return']=number_format($info['stock_return']/1000,3,'.','');
        }
        else
        {
            $row['stock_return']=$info['stock_return'];
        }
        $row['sales']='';
        $row['sales_cancel']='';
        $row['current_stock']=$info['starting_stock']+$info['stock_in']-$info['stock_return'];
        if($report_type=='weight')
        {
            $row['current_stock']=number_format($row['current_stock']/1000,3,'.','');
        }
        $row['current_unit_price']='';
        $row['current_total_price']='';
        return $row;

    }
}
