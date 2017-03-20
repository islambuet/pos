<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_sale extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Sales_sale');
        $this->controller_url='sales_sale';
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
            $this->system_list();
        }
        elseif($action=='get_items')
        {
            $this->system_get_items($id);
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="search_farmer")
        {
            $this->system_search_farmer();
        }
        elseif($action=="save_farmer")
        {
            $this->system_save_farmer();
        }
        elseif($action=="get_coupon_info")
        {
            $this->system_get_coupon_info();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="delete")
        {
            $this->system_delete($id);
        }
        elseif($action=="save_cancel")
        {
            $this->system_save_cancel();
        }
        elseif($action=="reinvoice")
        {
            $this->system_reinvoice();
        }
        elseif($action=="search_reinvoice")
        {
            $this->system_search_reinvoice();
        }
        elseif($action=="save_reinvoice")
        {
            $this->system_save_reinvoice();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        else
        {
            $this->system_list();
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']="List of Farmers/Customers";
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
        $current_records = $this->input->post('total_records');
        if(!$current_records)
        {
            $current_records=0;
        }
        $pagesize = $this->input->post('pagesize');
        if(!$pagesize)
        {
            $pagesize=40;
        }
        else
        {
            $pagesize=$pagesize*2;
        }
        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name farmer_name');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id =sale.customer_id','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where_in('customer_id',$this->user_outlet_ids);
        $this->db->order_by('sale.id DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date_sale']=System_helper::display_date_time($item['date_sale']);
            $item['invoice_no']=System_helper::get_invoice_barcode($item['id']);
            $item['amount_discount']=number_format($item['amount_total']-$item['amount_payable'],2);
            $item['amount_payable']=number_format($item['amount_payable'],2);
            $item['amount_total']=number_format($item['amount_total'],2);

        }
        $this->json_return($items);
    }

    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $data['title']="New Sale";
            $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price').' vp');
            $this->db->select('vp.id price_id,vp.price');
            $this->db->select('v.id variety_id,v.name variety_name');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->select('type.name type_name,type.id type_id');
            $this->db->select('pack.name pack_size,pack.id pack_id');

            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id = vp.variety_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack','pack.id = vp.pack_size_id','INNER');
            $this->db->where('vp.revision',1);
            $results=$this->db->get()->result_array();
            $data['varieties_info']=array();
            foreach($results as $result)
            {
                $data['varieties_info'][System_helper::get_variety_barcode($result['crop_id'],$result['variety_id'],$result['pack_id'])]=$result;
            }
            //$data['stock_info']=array();
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add",$data,true));
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
    private function system_search_farmer()
    {
        $customer_id=$this->input->post("customer_id");
        $code=$this->input->post("code");
        $farmer_info=System_helper::get_farmer_from_barcode($code);
        if(sizeof($farmer_info)>0)
        {
            $this->system_load_sale_from($farmer_info['id'],$customer_id);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']='Customer '.$this->lang->line("MSG_NOT_FOUND");
            $this->json_return($ajax);
        }

    }
    private function system_save_farmer()
    {
        $user = User_helper::get_user();
        $time=time();

        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }

        if(!$this->check_validation_save_farmer())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $this->db->trans_start();  //DB Transaction Handle START
            $data=array();
            $data['name'] = $this->input->post("name");
            $data['type_id'] = 1;
            $data['mobile_no'] = $this->input->post("mobile_no");
            $data['nid'] = $this->input->post("nid");
            $data['address'] = $this->input->post("address");
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $farmer_id=Query_helper::add($this->config->item('table_pos_setup_farmer_farmer'),$data);

            $data=array();
            $data['farmer_id'] = $farmer_id;
            $data['customer_id'] = $this->input->post("customer_id");
            $data['revision'] = 1;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            Query_helper::add($this->config->item('table_pos_setup_farmer_outlet'),$data);

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {

                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_load_sale_from($farmer_id,$this->input->post("customer_id"));
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function check_validation_save_farmer()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('customer_id',$this->lang->line('LABEL_OUTLET_NAME'),'required');
        $this->form_validation->set_rules('name',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('mobile_no',$this->lang->line('LABEL_MOBILE_NO'),'required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        $mobile_no=$this->input->post("mobile_no");
        $exists=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('id'),array('mobile_no ="'.$mobile_no.'"'),1);
        if($exists)
        {
            $this->message="Mobile No already Exists";
            return false;
        }
        return true;
    }
    private function system_get_coupon_info()
    {
        $customer_id=$this->input->post("customer_id");
        $coupon_barcode=$this->input->post("coupon_barcode");

        $farmer_info=System_helper::get_farmer_from_barcode($coupon_barcode,'barcode_farmer');
        if(sizeof($farmer_info)>0)
        {
            $data['item']=$farmer_info;
            $data['item']['customer_id']=$customer_id;
            $data['farmer_type']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('id ='.$data['item']['type_id']),1);
            $data['assigned']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),array('customer_id'),array('farmer_id ='.$data['item']['id'],'revision =1','customer_id ='.$customer_id),1);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#container_discount_info","html"=>$this->load->view($this->controller_url."/discount_info",$data,true));
            if(sizeof($data['assigned'])>0)
            {
                $ajax['system_style'][]=array("id"=>"#button_action_discount_clear","display"=>true);
                $ajax['system_content'][]=array("id"=>"#discount","html"=>$data['farmer_type']['discount_coupon']);

            }
            //$ajax['system_style'][]=array("id"=>"#container_new_customer","display"=>false);

            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']='Customer '.$this->lang->line("MSG_NOT_FOUND");
            $this->json_return($ajax);
        }
    }
    private function system_load_sale_from($farmer_id,$customer_id)
    {
        $data['item']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$farmer_id),1);
        $data['farmer_type']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('id ='.$data['item']['type_id']),1);
        if(sizeof($data['item'])>0)
        {
            $data['item']['customer_id']=$customer_id;


            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#container_sale","html"=>$this->load->view($this->controller_url."/sale_form",$data,true));
            $ajax['system_style'][]=array("id"=>"#container_new_customer","display"=>false);
            $results=System_helper::get_varieties_stocks($customer_id);
            $ajax['stock_info']=array();
            foreach($results as $pack_info)
            {
                foreach($pack_info as $item)
                {
                    $ajax['stock_info'][System_helper::get_variety_barcode($item['crop_id'],$item['variety_id'],$item['pack_id'])]=$item['current_stock'];
                }

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
            $ajax['system_message']='Failed to Load Customer Information.';
            $this->json_return($ajax);
        }
    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        //check permission
        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        //check outlet access
        $data = $this->input->post("item");
        if(!in_array($data['customer_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Save',0,'outlet id '.$data['customer_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        //checking validation

        $items=$this->input->post('varieties');
        //checking if input correctly
        if(sizeof($items)==0)
        {
            $ajax['status']=false;
            $ajax['system_message']="No Item Added For Sale";
            $this->json_return($ajax);
            die();
        }

        //checking stock ok
        $variety_pack_sizes=array();
        $pack_ids=array();
        $variety_ids=array();
        foreach($items as $variety_id =>$pack_info)
        {
            foreach($pack_info as $pack_id =>$quantity)
            {
                $variety_pack_sizes[]=array('variety_id'=>$variety_id,'pack_size_id'=>$pack_id);
                $pack_ids[]=$pack_id;
                $variety_ids[]=$variety_id;
            }

        }
        $stock_validation=true;
        $stock_info=System_helper::get_varieties_stocks($data['customer_id'],$variety_pack_sizes);
        $new_stock=array();
        foreach($items as $variety_id =>$pack_info)
        {
            foreach($pack_info as $pack_id =>$info)
            {
                $cur_stock=0;
                if(isset($stock_info[$variety_id][$pack_id]))
                {
                    $cur_stock=$stock_info[$variety_id][$pack_id]['current_stock'];
                    $new_stock[System_helper::get_variety_barcode($stock_info[$variety_id][$pack_id]['crop_id'],$stock_info[$variety_id][$pack_id]['variety_id'],$stock_info[$variety_id][$pack_id]['pack_id'])]=$cur_stock;
                }
                if($info['quantity']>$cur_stock)
                {
                    $stock_validation=false;
                }
            }

        }
        if(!$stock_validation)
        {
            $ajax['status']=false;
            $ajax['system_message']="Sale Quantity Cannot me more than Current Stock";
            $ajax['new_stock']=$new_stock;
            $this->json_return($ajax);
            die();
        }
        //checking stock finish
        //getting pack info
        $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack');
        $this->db->select('id,name');
        $this->db->where_in('id',$pack_ids);
        $results=$this->db->get()->result_array();
        $pack_size_info=array();
        foreach($results as $result)
        {
            $pack_size_info[$result['id']]=$result['name'];
        }
        //getting price info
        $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price'));
        $this->db->where_in('variety_id',$variety_ids);
        $this->db->where('revision',1);
        $results=$this->db->get()->result_array();
        $price_info=array();
        foreach($results as $result)
        {
            $price_info[$result['variety_id']][$result['pack_size_id']]=$result['price'];
        }
        //total and details calculating
        $data_items=array();
        $data['amount_total']=0;
        foreach($items as $variety_id =>$pack_info)
        {
            foreach($pack_info as $pack_id =>$info)
            {
                $result=array();
                $result['variety_id']=$variety_id;
                $result['pack_size_id']=$pack_id;
                $result['pack_size']=$pack_size_info[$pack_id];
                $result['price_unit']=0;
                if(isset($price_info[$variety_id][$pack_id]))
                {
                    $result['price_unit']=$price_info[$variety_id][$pack_id];
                }
                $result['quantity_sale']=$info['quantity'];
                $data['amount_total']+=$info['quantity']*$result['price_unit'];
                $data_items[]=$result;
            }

        }
        $discount_farmer_id=$this->input->post('discount_farmer_id');
        $data['discount_percentage']=0;
        if($discount_farmer_id>0)
        {
            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
            $this->db->select('ft.*');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.type_id','INNER');
            $this->db->where('f.id',$discount_farmer_id);
            $result=$this->db->get()->row_array();
            if($result)
            {
                $data['discount_percentage']=$result['discount_coupon'];
                $data['discount_farmer_id']=$discount_farmer_id;
            }
        }
        else
        {
            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
            $this->db->select('ft.*');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.type_id','INNER');
            $this->db->where('f.id',$data['farmer_id']);
            $result=$this->db->get()->row_array();
            if($result)
            {
                $data['discount_percentage']=$result['discount_non_coupon'];
            }
        }
        $data['date_sale']=$time;
        $data['date_created']=$time;
        $data['user_created']=$user->user_id;
        $data['amount_cash']=$this->input->post('amount_paid');
        $data['amount_payable']=$data['amount_total']-($data['amount_total']*$data['discount_percentage']/100);
        if($data['amount_cash']<$data['amount_payable'])
        {
            $ajax['status']=false;
            $ajax['system_message']="Payment amount cannot be less than purchase amount";
            $this->json_return($ajax);
            die();
        }
        $this->db->trans_start();  //DB Transaction Handle START
        $sale_id=Query_helper::add($this->config->item('table_pos_sale'),$data);
        foreach($data_items as $data_details)
        {

            $data_details['sale_id']=$sale_id;
            $data_details['date_created']=$time;
            $data_details['user_created']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_sale_details'),$data_details);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_details($sale_id);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
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
                System_helper::invalid_try('Sale Cancel view',$item_id,'Trying to access Invalid Sale id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if(!in_array($data['item']['customer_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Sale Cancel view',$item_id,'Trying to access other Outlets data');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if($data['item']['status']!=$this->config->item('system_status_active'))
            {
                $ajax['status']=false;
                $ajax['system_message']="This sale already Canceled";
                $this->json_return($ajax);
                die();
            }
            if($data['item']['invoice_old_id']>0)
            {
                $ajax['status']=false;
                $ajax['system_message']="This sale already Reinvoiced.<br>You cannot Cancel it.";
                $this->json_return($ajax);
                die();
            }
            if(!((System_helper::display_date(time())==System_helper::display_date($data['item']['date_sale']))||($user->user_group==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']="You cannot cancel this sale now";
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
            $data['title']='Cancel Invoice ('.System_helper::get_invoice_barcode($item_id).')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/delete",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/delete/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_cancel()
    {
        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
        {
            $item_id=$this->input->post('id');
            $user = User_helper::get_user();
            $time=time();
            $item_info=Query_helper::get_info($this->config->item('table_pos_sale'),'*',array('id ='.$item_id),1);
            if(!$item_info)
            {
                System_helper::invalid_try('Sale Cancel Save',$item_id,'Trying to access Invalid Sale id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if(!in_array($item_info['customer_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Sale Cancel Save',$item_id,'Trying to Cancel other Outlets data');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if($item_info['status']!=$this->config->item('system_status_active'))
            {
                $ajax['status']=false;
                $ajax['system_message']="This sale already Canceled";
                $this->json_return($ajax);
                die();
            }
            if($item_info['invoice_old_id']>0)
            {
                $ajax['status']=false;
                $ajax['system_message']="This sale already Reinvoiced.<br>You cannot Cancel it.";
                $this->json_return($ajax);
                die();
            }
            if(!((System_helper::display_date(time())==System_helper::display_date($item_info['date_sale']))||($user->user_group==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']="You cannot cancel this sale now";
                $this->json_return($ajax);
                die();
            }
            $data['remarks']=$this->input->post('remarks');
            if(!$data['remarks'])
            {
                $ajax['status']=false;
                $ajax['system_message']="Sale Cancel Reason Required";
                $this->json_return($ajax);
                die();
            }
            $data['status']=$this->config->item('system_status_inactive');
            $data['date_canceled']=$time;
            $data['user_canceled']=$user->user_id;

            $this->db->trans_start();  //DB Transaction Handle START
            Query_helper::update($this->config->item('table_pos_sale'),$data,array('id ='.$item_id));
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
    private function system_reinvoice()
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            $data['title']="Re Invoice";
            $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price').' vp');
            $this->db->select('vp.id price_id,vp.price');
            $this->db->select('v.id variety_id,v.name variety_name');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->select('type.name type_name,type.id type_id');
            $this->db->select('pack.name pack_size,pack.id pack_id');

            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id = vp.variety_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack','pack.id = vp.pack_size_id','INNER');
            $this->db->where('vp.revision',1);
            $results=$this->db->get()->result_array();
            $data['varieties_info']=array();
            foreach($results as $result)
            {
                $data['varieties_info'][System_helper::get_variety_barcode($result['crop_id'],$result['variety_id'],$result['pack_id'])]=$result;
            }
            //$data['stock_info']=array();
            $ajax['system_page_url']=site_url($this->controller_url."/index/reinvoice");
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/reinvoice",$data,true));
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
    private function system_search_reinvoice()
    {
        $invoice_code=$this->input->post("invoice_no");
        $invoice_info=System_helper::get_invoice_from_barcode($invoice_code);
        if(sizeof($invoice_info)>0)
        {
            if($invoice_info['status']!=$this->config->item('system_status_active'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Invoice already Canceled';
                $this->json_return($ajax);
                die();
            }
            elseif(!(in_array($invoice_info['customer_id'],$this->user_outlet_ids)))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Invoice Is not valid in this Outlet';
                $this->json_return($ajax);
                die();
            }
            else
            {
                $data=array();
                $data['invoice_info']=$invoice_info;
                $data['farmer_info']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$invoice_info['farmer_id']),1);
                $this->db->from($this->config->item('table_pos_sale_details').' sd');
                $this->db->select('sd.*');
                $this->db->select('v.name variety_name');
                $this->db->select('type.name type_name');
                $this->db->select('crop.name crop_name,crop.id crop_id');
                $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id =sd.variety_id','INNER');
                $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');
                $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crops').' crop','crop.id =type.crop_id','INNER');
                $this->db->where('sd.sale_id',$invoice_info['id']);

                $data['invoice_details']=$this->db->get()->result_array();
                $ajax['status']=true;
                $results=System_helper::get_varieties_stocks($invoice_info['customer_id']);
                $ajax['stock_info']=array();
                foreach($results as $pack_info)
                {
                    foreach($pack_info as $item)
                    {
                        $ajax['stock_info'][System_helper::get_variety_barcode($item['crop_id'],$item['variety_id'],$item['pack_id'])]=$item['current_stock'];
                    }

                }
                $data['stock_info']=$ajax['stock_info'];
                $ajax['system_content'][]=array("id"=>"#container_sale","html"=>$this->load->view($this->controller_url."/reinvoice_form",$data,true));
                if($this->message)
                {
                    $ajax['system_message']=$this->message;
                }
                $this->json_return($ajax);
                //$this->system_load_sale_from($farmer_info['id'],$customer_id);
            }

        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']='Invoice not Found';
            $this->json_return($ajax);
            die();
        }

    }
    private function system_save_reinvoice()
    {

        $user = User_helper::get_user();
        $time=time();
        //check permission
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        //check outlet access
        $old_invoice_id=$this->input->post('id');
        $old_invoice_info=Query_helper::get_info($this->config->item('table_pos_sale'),'*',array('id ='.$old_invoice_id),1);
        if(!in_array($old_invoice_info['customer_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Re invoice Save',0,'outlet id '.$old_invoice_info['customer_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if($old_invoice_info['status']!=$this->config->item('system_status_active'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Invoice already Canceled or Re-invoiced';
            $this->json_return($ajax);
            die();
        }
        //checking validation

        $items=$this->input->post('varieties');
        //checking if input correctly
        if(sizeof($items)==0)
        {
            $ajax['status']=false;
            $ajax['system_message']="No Item Added For Sale";
            $this->json_return($ajax);
            die();
        }

        //checking stock ok
        $variety_pack_sizes=array();
        $pack_ids=array();
        $variety_ids=array();
        foreach($items as $variety_id =>$pack_info)
        {
            foreach($pack_info as $pack_id =>$quantity)
            {
                $variety_pack_sizes[]=array('variety_id'=>$variety_id,'pack_size_id'=>$pack_id);
                $pack_ids[]=$pack_id;
                $variety_ids[]=$variety_id;
            }

        }
        $results=Query_helper::get_info($this->config->item('table_pos_sale_details'),'*',array('sale_id ='.$old_invoice_id,'revision =1'));
        $old_invoice_details=array();
        foreach($results as $result)
        {
            $old_invoice_details[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $stock_validation=true;
        $stock_info=System_helper::get_varieties_stocks($old_invoice_info['customer_id'],$variety_pack_sizes);
        $new_stock=array();
        foreach($items as $variety_id =>$pack_info)
        {
            foreach($pack_info as $pack_id =>$info)
            {
                $cur_stock=0;
                if(isset($stock_info[$variety_id][$pack_id]))
                {
                    $cur_stock=$stock_info[$variety_id][$pack_id]['current_stock'];
                    $new_stock[System_helper::get_variety_barcode($stock_info[$variety_id][$pack_id]['crop_id'],$stock_info[$variety_id][$pack_id]['variety_id'],$stock_info[$variety_id][$pack_id]['pack_id'])]=$cur_stock;
                }
                $new_quantity=$info['quantity'];
                if(isset($old_invoice_details[$variety_id][$pack_id]))
                {
                    $new_quantity-=$old_invoice_details[$variety_id][$pack_id]['quantity_sale'];
                }
                if($new_quantity>$cur_stock)
                {
                    $stock_validation=false;
                }
            }

        }
        if(!$stock_validation)
        {
            $ajax['status']=false;
            $ajax['system_message']="Sale Quantity Cannot me more than Current Stock";
            $ajax['new_stock']=$new_stock;
            $this->json_return($ajax);
            die();
        }

        //checking stock finish
        //getting pack info
        $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack');
        $this->db->select('id,name');
        $this->db->where_in('id',$pack_ids);
        $results=$this->db->get()->result_array();
        $pack_size_info=array();
        foreach($results as $result)
        {
            $pack_size_info[$result['id']]=$result['name'];
        }
        //getting price info
        $this->db->from($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_variety_price'));
        $this->db->where_in('variety_id',$variety_ids);
        $this->db->where('revision',1);
        $results=$this->db->get()->result_array();
        $price_info=array();
        foreach($results as $result)
        {
            $price_info[$result['variety_id']][$result['pack_size_id']]=$result['price'];
        }
        //total and details calculating
        $data=array();
        $data['customer_id']=$old_invoice_info['customer_id'];
        $data['farmer_id']=$old_invoice_info['farmer_id'];
        $data['discount_farmer_id']=$old_invoice_info['discount_farmer_id'];
        $data['discount_percentage']=$old_invoice_info['discount_percentage'];
        $data_items=array();

        $data['amount_total']=0;
        foreach($items as $variety_id =>$pack_info)
        {
            foreach($pack_info as $pack_id =>$info)
            {
                $result=array();
                $result['variety_id']=$variety_id;
                $result['pack_size_id']=$pack_id;
                $result['pack_size']=$pack_size_info[$pack_id];
                $result['price_unit']=0;
                if(isset($price_info[$variety_id][$pack_id]))
                {
                    $result['price_unit']=$price_info[$variety_id][$pack_id];
                }
                $result['quantity_sale']=$info['quantity'];
                $data['amount_total']+=$info['quantity']*$result['price_unit'];
                $data_items[]=$result;
            }
        }
        $data['amount_payable']=$data['amount_total']-($data['amount_total']*$data['discount_percentage']/100);
        $data['amount_previous_paid']=$old_invoice_info['amount_payable'];
        $data['amount_cash']=$this->input->post('amount_paid');
        if($data['amount_payable']<$data['amount_previous_paid'])
        {
            $ajax['status']=false;
            $ajax['system_message']="Payable amount cannot be less than previously paid amount";
            $this->json_return($ajax);
            die();
        }

        $data['date_sale']=$time;
        $data['invoice_count']=$old_invoice_info['invoice_count']+1;
        $data['invoice_old_id']=$old_invoice_info['id'];
        $data['remarks']=$this->input->post('remarks');
        $data['date_created']=$time;
        $data['user_created']=$user->user_id;


        if($data['amount_cash']<($data['amount_payable']-$data['amount_previous_paid']))
        {
            $ajax['status']=false;
            $ajax['system_message']="Payment amount cannot be less than purchase amount";
            $this->json_return($ajax);
            die();
        }
        $this->db->trans_start();  //DB Transaction Handle START
        $sale_id=Query_helper::add($this->config->item('table_pos_sale'),$data);
        foreach($data_items as $data_details)
        {

            $data_details['sale_id']=$sale_id;
            $data_details['date_created']=$time;
            $data_details['user_created']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_sale_details'),$data_details);
        }
        $data_cancel=array();
        $data_cancel['invoice_new_id']=$sale_id;
        $data_cancel['date_canceled']=$time;
        $data_cancel['user_canceled']=$user->user_id;
        $data_cancel['date_updated']=$time;
        $data_cancel['user_updated']=$user->user_id;
        $data_cancel['status']=$this->config->item('system_status_inactive');
        Query_helper::update($this->config->item('table_pos_sale'),$data_cancel,array('id ='.$old_invoice_id));
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_details($sale_id);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function system_details($id)
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
            $data['title']='Sale Details of ('.System_helper::get_invoice_barcode($item_id).')';

            /*$this->db->from($this->config->item('table_pos_setup_farmer_outlet').' fo');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
            $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_csetup_customers').' cus','cus.id = fo.farmer_id','INNER');
            $this->db->where('fo.revision',1);
            $this->db->where('fo.farmer_id',$item_id);
            $data['assigned_outlets']=$this->db->get()->result_array();*/

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



}
