<?php
class System_helper
{
    public static function display_date($time)
    {
        if(is_numeric($time))
        {
            return date('d-M-Y',$time);
        }
        else
        {
            return '';
        }
    }
    public static function display_date_time($time)
    {
        if(is_numeric($time))
        {
            return date('d-M-Y h:i:s A',$time);
        }
        else
        {
            return '';
        }
    }
    public static function get_time($str)
    {
        $time=strtotime($str);
        if($time===false)
        {
            return 0;
        }
        else
        {
            return $time;
        }
    }
    public static function upload_file($save_dir='images',$allowed_types='gif|jpg|png')
    {
        $CI= & get_instance();
        $CI->load->library('upload');
        $config=array();
        $config['upload_path']=FCPATH.$save_dir;
        $config['allowed_types']=$allowed_types;
        $config['max_size']=10*1024;
        $config['overwrite']=false;
        $config['remove_spaces']=true;

        $uploaded_files=array();
        foreach ($_FILES as $key=>$value)
        {
            if(strlen($value['name'])>0)
            {
                $CI->upload->initialize($config);
                if($CI->upload->do_upload($key))
                {
                    $uploaded_files[$key]=array('status'=>true,'info'=>$CI->upload->data());
                }
                else
                {
                    $uploaded_files[$key]=array('status'=>false,'message'=>$value['name'].': '.$CI->upload->display_errors());
                }
            }
        }
        return $uploaded_files;
    }
    public static function invalid_try($action='',$action_id='',$other_info='')
    {
        $CI =& get_instance();
        $user = User_helper::get_user();
        $time=time();
        $data=array();
        $data['user_id']=$user->user_id;
        $data['controller']=$CI->router->class;
        $data['action']=$action;
        $data['action_id']=$action_id;
        $data['other_info']=$other_info;
        $data['date_created']=$time;
        $data['date_created_string']=System_helper::display_date($time);
        $CI->db->insert($CI->config->item('table_system_history_hack'), $data);
    }
    public static function get_variety_barcode($crop_id,$variety_id,$pack_id)
    {
        return str_pad($crop_id,2,0,STR_PAD_LEFT).str_pad($variety_id,4,0,STR_PAD_LEFT).str_pad($pack_id,2,0,STR_PAD_LEFT);
    }
    public static function get_farmer_barcode($id)
    {
        return 'F-'.str_pad($id,6,0,STR_PAD_LEFT);
    }
    public static function get_invoice_barcode($id)
    {
        return 'I-'.str_pad($id,7,0,STR_PAD_LEFT);
    }
    public static function get_farmer_from_barcode($barcode,$barcode_type='any')
    {
        $CI =& get_instance();
        $result=array();
        if((substr($barcode,0,2)=='F-')&&(($barcode_type=='any')||($barcode_type=='barcode_farmer')))
        {
            $result=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.intval(substr($barcode,2))),1);
        }
        else if((substr($barcode,0,2)=='I-')&&(($barcode_type=='any')||($barcode_type=='barcode_invoice')))
        {
            $CI->db->from($CI->config->item('table_pos_sale').' sale');
            $CI->db->join($CI->config->item('table_pos_setup_farmer_farmer').' f','f.id =sale.farmer_id','INNER');
            $CI->db->select('f.*');
            $CI->db->where('sale.id',intval(substr($barcode,2)));
            $result=$CI->db->get()->row_array();
            //$result=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.intval(substr($code,2))),1);
        }
        else if(($barcode_type=='any')||($barcode_type=='mobile_no'))
        {
            $result=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),'*',array('mobile_no ='.intval($barcode)),1);
        }
        return $result;

    }
    public static function get_varieties_stocks($customer_id,$variety_pack_sizes=array())
    {
        $CI = & get_instance();
        $stocks=array();
        //stock in
        $variety_ids=array();

        $where='';
        $where_bonus='';
        if(sizeof($variety_pack_sizes)>0)
        {
            foreach($variety_pack_sizes as $i=>$vp)
            {
                if($i==0)
                {
                    $where='(pod.variety_id='.$vp['variety_id'].' AND pod.pack_size_id='.$vp['pack_size_id'].')';
                    $where_bonus='(pod.variety_id='.$vp['variety_id'].' AND pod.bonus_pack_size_id='.$vp['pack_size_id'].')';
                }
                else
                {
                    $where.='OR (pod.variety_id='.$vp['variety_id'].' AND pod.pack_size_id='.$vp['pack_size_id'].')';
                    $where_bonus.='OR (pod.variety_id='.$vp['variety_id'].' AND pod.bonus_pack_size_id='.$vp['pack_size_id'].')';
                }
            }
        }

        //-sales and sales return
        //ems Receive and sales return
        //outlet stock in


        $CI->db->from($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_sales_po').' po');

        $CI->db->select('pod.variety_id,pod.pack_size_id');
        $CI->db->select('SUM(por.quantity_receive) sales_receive');
        $CI->db->select('SUM(pod.quantity_return) sales_return');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_sales_po_receives').' por','por.sales_po_id =po.id','INNER');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_sales_po_details').' pod','pod.id =por.sales_po_detail_id','INNER');

        if(strlen($where)>0)
        {
            $CI->db->where('('.$where.')');
        }
        $CI->db->where('po.status_received',$CI->config->item('system_status_receive'));
        $CI->db->where('po.customer_id',$customer_id);
        $CI->db->where('por.revision',1);
        $CI->db->where('pod.revision',1);
        $CI->db->group_by(array('pod.variety_id','pod.pack_size_id'));
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {
            /*$stocks[$result['variety_id']][$result['pack_size_id']]['sales_receive']=$result['sales_receive'];
            $stocks[$result['variety_id']][$result['pack_size_id']]['sales_return']=$result['sales_return'];
            $stocks[$result['variety_id']][$result['pack_size_id']]['sales']=0;*/
            $stocks[$result['variety_id']][$result['pack_size_id']]['current_stock']=($result['sales_receive']-$result['sales_return']);
            $variety_ids[]=$result['variety_id'];
        }
        //-sales bonus receive and bonus return
        $CI->db->from($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_sales_po').' po');

        $CI->db->select('pod.variety_id,pod.bonus_pack_size_id');
        $CI->db->select('SUM(por.quantity_bonus_receive) bonus_receive');
        $CI->db->select('SUM(pod.quantity_bonus_return) bonus_return');

        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_sales_po_receives').' por','por.sales_po_id =po.id','INNER');
        $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_sales_po_details').' pod','pod.id =por.sales_po_detail_id','INNER');

        if(strlen($where_bonus)>0)
        {
            $CI->db->where('('.$where_bonus.')');
        }
        $CI->db->where('po.status_received',$CI->config->item('system_status_receive'));
        $CI->db->where('po.customer_id',$customer_id);
        $CI->db->where('pod.bonus_details_id >',0);
        $CI->db->where('por.revision',1);
        $CI->db->where('pod.revision',1);
        $CI->db->group_by(array('pod.variety_id','pod.bonus_pack_size_id'));
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['bonus_pack_size_id']]))
            {
                $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['current_stock']+=($result['bonus_receive']-$result['bonus_return']);
            }
            else
            {
                $stocks[$result['variety_id']][$result['bonus_pack_size_id']]['current_stock']=($result['bonus_receive']-$result['bonus_return']);
                if(!in_array($result['variety_id'],$variety_ids))
                {
                    $variety_ids[]=$result['variety_id'];
                }
            }

        }
        //stock in from ems completed
        //sales from outlet
        $CI->db->from($CI->config->item('table_pos_sale_details').' pod');
        $CI->db->select('pod.variety_id,pod.pack_size_id');
        $CI->db->select('SUM(pod.quantity_sale) sale_quantity');
        $CI->db->join($CI->config->item('table_pos_sale').' sale','sale.id =pod.sale_id','INNER');
        $CI->db->where('sale.status',$CI->config->item('system_status_active'));
        $CI->db->where('pod.revision',1);
        $CI->db->where('sale.customer_id',$customer_id);
        if(strlen($where)>0)
        {
            $CI->db->where('('.$where.')');
        }
        $CI->db->group_by(array('pod.variety_id','pod.pack_size_id'));
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['pack_size_id']]))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['current_stock']-=($result['sale_quantity']);
            }
            else
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['current_stock']=(0-$result['sale_quantity']);
                if(!in_array($result['variety_id'],$variety_ids))
                {
                    $variety_ids[]=$result['variety_id'];
                }
            }

        }
        //sales from outlet finish
        if(sizeof($variety_ids)>0)
        {
            $CI->db->from($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_setup_classification_varieties').' v');
            $CI->db->select('v.id variety_id');
            $CI->db->select('type.id type_id');
            $CI->db->select('type.crop_id crop_id');
            $CI->db->join($CI->config->item('system_db_ems').'.'.$CI->config->item('table_ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');
            $CI->db->where_in('v.id',$variety_ids);
            $results=$CI->db->get()->result_array();
            foreach($results as $result)
            {
                foreach($stocks[$result['variety_id']] as $pack_id=>&$pack_info)
                {
                    $pack_info['crop_id']=$result['crop_id'];
                    $pack_info['type_id']=$result['type_id'];
                    $pack_info['variety_id']=$result['variety_id'];
                    $pack_info['pack_id']=$pack_id;

                }
            }
        }

        return $stocks;

    }
    public static function get_users_info($user_ids)
    {
        $CI =& get_instance();

        $CI->db->from($CI->config->item('table_pos_setup_user_info').' user_info');
        if(sizeof($user_ids)>0)
        {
            $CI->db->where_in('user_id',$user_ids);
        }
        $CI->db->where('revision',1);
        $results=$CI->db->get()->result_array();
        $users=array();
        foreach($results as $result)
        {
            $users[$result['user_id']]=$result;
        }
        return $users;

    }
}