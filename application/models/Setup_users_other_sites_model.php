<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Setup_users_other_sites_model extends CI_Model
{

    public function __construct() {
        parent::__construct();
    }
    public function get_sites()
    {
        $CI = & get_instance();
        $this->db->from($CI->config->item('table_system_other_sites'));
        $this->db->where('status',$this->config->item('system_status_active'));
        $this->db->order_by('ordering');
        $results=$this->db->get()->result_array();

        return $results;

    }
    public function get_assigned_sites($user_id)
    {
        $CI = & get_instance();
        $this->db->from($CI->config->item('table_setup_users_other_sites'));
        $this->db->where('revision',1);
        $this->db->where('user_id',$user_id);
        $results=$this->db->get()->result_array();
        $assigned_sites=array();
        foreach($results as $result)
        {
            $assigned_sites[]=$result['site_id'];
        }
        return $assigned_sites;

    }
}