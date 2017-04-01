<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Barcode_variety extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Barcode_variety');
        $this->controller_url='barcode_variety';
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
            $data['title']="Varieties List";
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
        $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price').' vp');
        $this->db->select('vp.id,vp.price');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->select('crop.name crop_name,crop.id crop_id');
        $this->db->select('type.name crop_type_name,type.id type_id');
        $this->db->select('pack.name pack_size_name,pack.id pack_id');

        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id = vp.variety_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack','pack.id = vp.pack_size_id','INNER');
        $this->db->where('vp.revision',1);
        $this->db->order_by('crop.ordering ASC');
        $this->db->order_by('type.ordering ASC');
        $this->db->order_by('v.ordering ASC');

        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['bar_code']=System_helper::get_variety_barcode($item['crop_id'],$item['variety_id'],$item['pack_id']);
        }
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
            $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price').' vp');
            $this->db->select('vp.id,vp.price');
            $this->db->select('v.id variety_id,v.name variety_name');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->select('type.name type_name,type.id type_id');
            $this->db->select('pack.name pack_size_name,pack.id pack_id');

            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id = vp.variety_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack','pack.id = vp.pack_size_id','INNER');
            $this->db->where('vp.revision',1);
            $this->db->where('vp.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            $data['item']['line1']='Malik Seeds';
            $data['outlets']=Query_helper::get_info($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers'),array('name_short','CONCAT(customer_code," - ",name) text'),array('status ="'.$this->config->item('system_status_active').'"','type ="Outlet"'));
            $data['title']='Variety Barcode Generate';
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
        $item_id = $this->input->post("id");
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $item=$this->input->post('item');
            $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price').' vp');
            $this->db->select('vp.id,vp.price');
            $this->db->select('v.id variety_id,v.name variety_name');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->select('type.name type_name,type.id type_id');
            $this->db->select('pack.name pack_size_name,pack.id pack_id');

            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id = vp.variety_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack','pack.id = vp.pack_size_id','INNER');
            $this->db->where('vp.revision',1);
            $this->db->where('vp.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            $data['item']['line1']=$item['line1'];
            $data['item']['outlet']=$item['outlet'];
            $data['item']['bar_code']=System_helper::get_variety_barcode($data['item']['crop_id'],$data['item']['variety_id'],$data['item']['pack_id']);
            $ajax['status']=true;
            if($item['barcode_purpose']=='packet')
            {
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/barcode_packet",$data,true));
            }
            else
            {
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/barcode",$data,true));
            }


            $this->json_return($ajax);
        }
    }
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[line1]','Line 1','required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
}
