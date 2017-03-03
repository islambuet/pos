<?php

class User_helper
{
    public static $logged_user = null;
    function __construct($id)
    {
        $CI = & get_instance();

        $user=Query_helper::get_info($CI->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$id,'revision =1'),1);
        if ($user)
        {
            foreach ($user as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }
    public static function login($username, $password)
    {
        $CI = & get_instance();
        $user=Query_helper::get_info($CI->config->item('table_pos_setup_user'),array('id'),array('user_name ="'.$username.'"','password ="'.md5($password).'"','status ="'.$CI->config->item('system_status_active').'"'),1);

        if ($user)
        {
            $CI->session->set_userdata("user_id", $user['id']);
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }



    public static function get_user()
    {
        $CI = & get_instance();
        if (User_helper::$logged_user)
        {
            return User_helper::$logged_user;
        }
        else
        {
            if($CI->session->userdata("user_id")!="")
            {
                $user=Query_helper::get_info($CI->config->item('table_pos_setup_user'),array('id'),array('id ='.$CI->session->userdata('user_id'),'status ="'.$CI->config->item('system_status_active').'"'),1);

                if($user)
                {
                    User_helper::$logged_user = new User_helper($CI->session->userdata('user_id'));
                    return User_helper::$logged_user;
                }
                return null;
            }
            else
            {
                return null;
            }

        }
    }
    public static function get_html_menu()
    {
        $user=User_helper::get_user();
        $CI = & get_instance();
        $CI->db->order_by('ordering');
        $tasks=$CI->db->get($CI->config->item('table_system_task'))->result_array();

        $roles=Query_helper::get_info($CI->config->item('table_system_user_group_role'),'*',array('revision =1','action0 =1','user_group_id ='.$user->user_group));
        $role_data=array();
        foreach($roles as $role)
        {
            $role_data[]=$role['task_id'];

        }
        $menu_data=array();
        foreach($tasks as $task)
        {
            if($task['type']=='TASK')
            {
                if(in_array($task['id'],$role_data))
                {
                    $menu_data['items'][$task['id']]=$task;
                    $menu_data['children'][$task['parent']][]=$task['id'];
                }
            }
            else
            {
                $menu_data['items'][$task['id']]=$task;
                $menu_data['children'][$task['parent']][]=$task['id'];
            }
        }

        $html='';
        if(isset($menu_data['children'][0]))
        {
            foreach($menu_data['children'][0] as $child)
            {
                $html.=User_helper::get_html_submenu($child,$menu_data,1);
            }
        }
        return $html;

    }
    public static function get_html_submenu($parent,$menu_data,$level)
    {
        if(isset($menu_data['children'][$parent]))
        {
            $sub_html='';
            foreach($menu_data['children'][$parent] as $child)
            {
                $sub_html.=User_helper::get_html_submenu($child,$menu_data,$level+1);

            }
            $html='';
            if($sub_html)
            {
                if($level==1)
                {
                    $html.='<li class="menu-item dropdown">';
                    $html.='<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$menu_data['items'][$parent]['name'].'<b class="caret"></b></a>';
                }
                else
                {
                    $html.='<li class="menu-item dropdown dropdown-submenu">';
                    $html.='<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$menu_data['items'][$parent]['name'].'</a>';
                }

                $html.='<ul class="dropdown-menu">';
                $html.=$sub_html;
                $html.='</ul></li>';
            }

            return $html;

        }
        else
        {
            if($menu_data['items'][$parent]['type']=='TASK')
            {
                return '<li><a href="'.site_url(strtolower($menu_data['items'][$parent]['controller'])).'">'.$menu_data['items'][$parent]['name'].'</a></li>';
            }
            else
            {
                return '';
            }

        }
    }
    public static function get_permission($controller_name)
    {
        $CI = & get_instance();
        $user=User_helper::get_user();
        $CI->db->from($CI->config->item('table_system_user_group_role').' ugr');
        $CI->db->select('ugr.*');

        $CI->db->join($CI->config->item('table_system_task').' task','task.id = ugr.task_id','INNER');
        $CI->db->where("controller",$controller_name);
        $CI->db->where("user_group_id",$user->user_group);
        $CI->db->where("revision",1);
        $result=$CI->db->get()->row_array();
        return $result;
    }
    public static function get_assigned_outlets()
    {
        $CI = & get_instance();
        $user=User_helper::get_user();
        $CI->db->from($CI->config->item('table_pos_setup_user_outlet').' uo');
        $CI->db->select('cus.*');
        $CI->db->select('d.id district_id,d.name district_name');
        $CI->db->select('t.id territory_id,t.name territory_name');
        $CI->db->select('zone.id zone_id,zone.name zone_name');
        $CI->db->select('division.id division_id,division.name division_name');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_csetup_customers').' cus','cus.id = uo.customer_id','INNER');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');
        $CI->db->where('uo.revision',1);
        $CI->db->where('uo.user_id',$user->user_id);
        $CI->db->order_by('division.ordering ASC');
        $CI->db->order_by('zone.ordering ASC');
        $CI->db->order_by('t.ordering ASC');
        $CI->db->order_by('d.ordering ASC');
        $CI->db->order_by('cus.ordering ASC');
        return $CI->db->get()->result_array();
    }
}