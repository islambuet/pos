<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_controller extends Root_Controller
{
    private  $message;
    public function __construct()
    {
        parent::__construct();
        $this->message="";

    }
    public function get_dropdown_farmers_by_cusfarmertypeid()
    {
        $html_container_id='#farmer_id';
        if($this->input->post('html_container_id'))
        {
            $html_container_id=$this->input->post('html_container_id');
        }

        $farmer_type = $this->input->post('farmer_type');
        $customer_id = $this->input->post('customer_id');

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' fo');
        $this->db->select('f.id value,f.name text');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = fo.farmer_id','INNER');
        $this->db->where('fo.customer_id',$customer_id);
        $this->db->where('f.type_id',$farmer_type);
        $this->db->group_by('f.id');
        $this->db->order_by('f.ordering DESC');
        $this->db->order_by('f.id DESC');
        $data['items']=$this->db->get()->result_array();
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>$html_container_id,"html"=>$this->load->view("dropdown_with_select",$data,true));
        $this->json_return($ajax);
    }
}
