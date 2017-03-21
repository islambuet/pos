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
        /*elseif($action=="list")
        {
            $this->system_list();
        }*/
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
            /*$data['warehouses']=Query_helper::get_info($this->config->item('table_basic_setup_warehouse'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['crops']=Query_helper::get_info($this->config->item('table_setup_classification_crops'),array('id value','name text'),array());
            $data['pack_sizes']=Query_helper::get_info($this->config->item('table_setup_classification_vpack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('name ASC'));
            $fiscal_years=Query_helper::get_info($this->config->item('table_basic_setup_fiscal_year'),'*',array());
            $data['fiscal_years']=array();
            foreach($fiscal_years as $year)
            {
                $data['fiscal_years'][]=array('text'=>$year['name'],'value'=>System_helper::display_date($year['date_start']).'/'.System_helper::display_date($year['date_end']));
            }*/
            $data['date_start']='';
            $data['date_end']=System_helper::display_date(time());
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("reports_stock/search",$data,true));
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

        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $reports=$this->input->post('report');
            $reports['date_end']=System_helper::get_time($reports['date_end']);
            $reports['date_start']=System_helper::get_time($reports['date_start']);
            if($reports['date_start']>=$reports['date_end'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Starting Date should be less than End date';
                $this->jsonReturn($ajax);
            }
            $reports['date_end']=$reports['date_end']+3600*24-1;
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
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_stock/list",$data,true));

            /*if($reports['report_type']=='weight')
            {
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_stock/list_weight",$data,true));
            }
            else
            {
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_stock/list_quantity",$data,true));
            }*/
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }

    }
    public function get_items()
    {
        $report_type=$this->input->post('report_type');
        $warehouse_id=$this->input->post('warehouse_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');


        $starting_items=$this->get_stocks($date_end,$warehouse_id,$crop_id,$crop_type_id,$variety_id,$pack_size_id);
        $items=array();
        if(sizeof($starting_items)>0)
        {
            $prices=$this->get_current_price($warehouse_id,$crop_id,$crop_type_id,$variety_id,$pack_size_id);
            $initial_items=$this->get_stocks($date_start,$warehouse_id,$crop_id,$crop_type_id,$variety_id,$pack_size_id);
            $prev_crop_name='';
            $prev_crop_type_name='';
            $count=0;

            $type_starting_stock=0;
            $crop_starting_stock=0;
            $grand_starting_stock=0;

            $type_stock_in=0;
            $crop_stock_in=0;
            $grand_stock_in=0;

            $type_excess=0;
            $crop_excess=0;
            $grand_excess=0;

            $type_sales=0;
            $crop_sales=0;
            $grand_sales=0;

            $type_sales_return=0;
            $crop_sales_return=0;
            $grand_sales_return=0;

            $type_sales_bonus=0;
            $crop_sales_bonus=0;
            $grand_sales_bonus=0;

            $type_sales_return_bonus=0;
            $crop_sales_return_bonus=0;
            $grand_sales_return_bonus=0;

            $type_short=0;
            $crop_short=0;
            $grand_short=0;

            $type_rnd=0;
            $crop_rnd=0;
            $grand_rnd=0;

            $type_sample=0;
            $crop_sample=0;
            $grand_sample=0;

            $type_demonstration=0;
            $crop_demonstration=0;
            $grand_demonstration=0;

            $type_current=0;
            $crop_current=0;
            $grand_current=0;

            $type_total_price=0;
            $crop_total_price=0;
            $grand_total_price=0;

            foreach($starting_items as $vid=>$variety)
            {
                foreach($variety as $pack_id=>$pack)
                {
                    $count++;
                    $initial=array();
                    $initial['stock_in']=0;
                    $initial['excess']=0;
                    $initial['sales']=0;
                    $initial['sales_return']=0;
                    $initial['sales_bonus']=0;
                    $initial['sales_return_bonus']=0;
                    $initial['short']=0;
                    $initial['rnd']=0;
                    $initial['sample']=0;
                    $initial['demonstration']=0;
                    if(isset($initial_items[$vid][$pack_id]))
                    {
                        $initial=$initial_items[$vid][$pack_id];
                    }
                    $info=array();
                    if($count>1)
                    {
                        if($prev_crop_name!=$pack['crop_name'])
                        {

                            $items[]=$this->get_type_total_row($report_type,$type_starting_stock,$type_stock_in,$type_excess,$type_sales,$type_sales_return,$type_sales_bonus,$type_sales_return_bonus,$type_short,$type_rnd,$type_sample,$type_demonstration,$type_current,$type_total_price);
                            $items[]=$this->get_crop_total_row($report_type,$crop_starting_stock,$crop_stock_in,$crop_excess,$crop_sales,$crop_sales_return,$crop_sales_bonus,$crop_sales_return_bonus,$crop_short,$crop_rnd,$crop_sample,$crop_demonstration,$crop_current,$crop_total_price);

                            $type_starting_stock=0;
                            $type_stock_in=0;
                            $type_excess=0;
                            $type_sales=0;
                            $type_sales_return=0;
                            $type_sales_bonus=0;
                            $type_sales_return_bonus=0;
                            $type_short=0;
                            $type_rnd=0;
                            $type_sample=0;
                            $type_demonstration=0;
                            $type_current=0;
                            $type_total_price=0;

                            $crop_starting_stock=0;
                            $crop_stock_in=0;
                            $crop_excess=0;
                            $crop_sales=0;
                            $crop_sales_return=0;
                            $crop_sales_bonus=0;
                            $crop_sales_return_bonus=0;
                            $crop_short=0;
                            $crop_rnd=0;
                            $crop_sample=0;
                            $crop_demonstration=0;
                            $crop_current=0;
                            $crop_total_price=0;
                            $info['crop_name']=$pack['crop_name'];
                            $prev_crop_name=$pack['crop_name'];

                            $info['crop_type_name']=$pack['crop_type_name'];
                            $prev_crop_type_name=$pack['crop_type_name'];
                        }
                        elseif($prev_crop_type_name!=$pack['crop_type_name'])
                        {
                            $items[]=$this->get_type_total_row($report_type,$type_starting_stock,$type_stock_in,$type_excess,$type_sales,$type_sales_return,$type_sales_bonus,$type_sales_return_bonus,$type_short,$type_rnd,$type_sample,$type_demonstration,$type_current,$type_total_price);
                            $type_starting_stock=0;
                            $type_stock_in=0;
                            $type_excess=0;
                            $type_sales=0;
                            $type_sales_return=0;
                            $type_short=0;
                            $type_rnd=0;
                            $type_sample=0;
                            $type_demonstration=0;
                            $type_current=0;
                            $type_total_price=0;
                            $info['crop_name']='';
                            $info['crop_type_name']=$pack['crop_type_name'];
                            $prev_crop_type_name=$pack['crop_type_name'];
                        }
                        else
                        {
                            $info['crop_name']='';
                            $info['crop_type_name']='';
                        }
                    }
                    else
                    {
                        $info['crop_name']=$pack['crop_name'];
                        $prev_crop_name=$pack['crop_name'];
                        $info['crop_type_name']=$pack['crop_type_name'];
                        $prev_crop_type_name=$pack['crop_type_name'];
                    }



                    $info['variety_name']=$pack['variety_name'];
                    $info['stock_id']=$pack['stock_id'];
                    $info['pack_size_name']=$pack['pack_size_name'];
                    $info['starting_stock']=$initial['stock_in']+$initial['excess']-$initial['sales']+$initial['sales_return']-$initial['sales_bonus']+$initial['sales_return_bonus']-$initial['short']-$initial['rnd']-$initial['sample']-$initial['demonstration'];


                    $info['current']=$pack['stock_in']+$pack['excess']-$pack['sales']+$pack['sales_return']-$pack['sales_bonus']+$pack['sales_return_bonus']-$pack['short']-$pack['rnd']-$pack['sample']-$pack['demonstration'];

                    $info['stock_in']=$pack['stock_in']-$initial['stock_in'];
                    $info['excess']=$pack['excess']-$initial['excess'];
                    $info['sales']=$pack['sales']-$initial['sales'];
                    $info['sales_return']=$pack['sales_return']-$initial['sales_return'];
                    $info['sales_bonus']=$pack['sales_bonus']-$initial['sales_bonus'];
                    $info['sales_return_bonus']=$pack['sales_return_bonus']-$initial['sales_return_bonus'];
                    $info['short']=$pack['short']-$initial['short'];
                    $info['rnd']=$pack['rnd']-$initial['rnd'];
                    $info['sample']=$pack['sample']-$initial['sample'];
                    $info['demonstration']=$pack['demonstration']-$initial['demonstration'];

                    $info['current_price']='Not Set';
                    $info['current_total_price']='N/A';
                    if(isset($prices[$vid][$pack_id]))
                    {
                        $unit_price=$prices[$vid][$pack_id]['price'];
                        $total_price=$info['current']*$unit_price;
                        $type_total_price+=$total_price;
                        $crop_total_price+=$total_price;
                        $grand_total_price+=$total_price;
                        $info['current_price']=number_format($unit_price,2);
                        if($report_type=='weight')
                        {
                            $info['current_price']=number_format($unit_price*1000/$info['pack_size_name'],2);
                        }
                        $info['current_total_price']=number_format($total_price,2);
                    }

                    if($report_type=='weight')
                    {
                        $type_starting_stock+=$info['starting_stock']*$info['pack_size_name'];
                        $crop_starting_stock+=$info['starting_stock']*$info['pack_size_name'];
                        $grand_starting_stock+=$info['starting_stock']*$info['pack_size_name'];
                        $type_stock_in+=$info['stock_in']*$info['pack_size_name'];
                        $crop_stock_in+=$info['stock_in']*$info['pack_size_name'];
                        $grand_stock_in+=$info['stock_in']*$info['pack_size_name'];
                        $type_excess+=$info['excess']*$info['pack_size_name'];
                        $crop_excess+=$info['excess']*$info['pack_size_name'];
                        $grand_excess+=$info['excess']*$info['pack_size_name'];
                        $type_sales+=$info['sales']*$info['pack_size_name'];
                        $crop_sales+=$info['sales']*$info['pack_size_name'];
                        $grand_sales+=$info['sales']*$info['pack_size_name'];
                        $type_sales_return+=$info['sales_return']*$info['pack_size_name'];
                        $crop_sales_return+=$info['sales_return']*$info['pack_size_name'];
                        $grand_sales_return+=$info['sales_return']*$info['pack_size_name'];
                        $type_sales_bonus+=$info['sales_bonus']*$info['pack_size_name'];
                        $crop_sales_bonus+=$info['sales_bonus']*$info['pack_size_name'];
                        $grand_sales_bonus+=$info['sales_bonus']*$info['pack_size_name'];
                        $type_sales_return_bonus+=$info['sales_return_bonus']*$info['pack_size_name'];
                        $crop_sales_return_bonus+=$info['sales_return_bonus']*$info['pack_size_name'];
                        $grand_sales_return_bonus+=$info['sales_return_bonus']*$info['pack_size_name'];
                        $type_short+=$info['short']*$info['pack_size_name'];
                        $crop_short+=$info['short']*$info['pack_size_name'];
                        $grand_short+=$info['short']*$info['pack_size_name'];
                        $type_rnd+=$info['rnd']*$info['pack_size_name'];
                        $crop_rnd+=$info['rnd']*$info['pack_size_name'];
                        $grand_rnd+=$info['rnd']*$info['pack_size_name'];
                        $type_sample+=$info['sample']*$info['pack_size_name'];
                        $crop_sample+=$info['sample']*$info['pack_size_name'];
                        $grand_sample+=$info['sample']*$info['pack_size_name'];

                        $type_demonstration+=$info['demonstration']*$info['pack_size_name'];
                        $crop_demonstration+=$info['demonstration']*$info['pack_size_name'];
                        $grand_demonstration+=$info['demonstration']*$info['pack_size_name'];

                        $type_current+=$info['current']*$info['pack_size_name'];
                        $crop_current+=$info['current']*$info['pack_size_name'];
                        $grand_current+=$info['current']*$info['pack_size_name'];

                        $info['starting_stock']=number_format($info['starting_stock']*$info['pack_size_name']/1000,3,'.','');
                        $info['stock_in']=number_format($info['stock_in']*$info['pack_size_name']/1000,3,'.','');
                        $info['excess']=number_format($info['excess']*$info['pack_size_name']/1000,3,'.','');
                        $info['sales']=number_format($info['sales']*$info['pack_size_name']/1000,3,'.','');
                        $info['sales_return']=number_format($info['sales_return']*$info['pack_size_name']/1000,3,'.','');
                        $info['sales_bonus']=number_format($info['sales_bonus']*$info['pack_size_name']/1000,3,'.','');
                        $info['sales_return_bonus']=number_format($info['sales_return_bonus']*$info['pack_size_name']/1000,3,'.','');
                        $info['short']=number_format($info['short']*$info['pack_size_name']/1000,3,'.','');
                        $info['rnd']=number_format($info['rnd']*$info['pack_size_name']/1000,3,'.','');
                        $info['sample']=number_format($info['sample']*$info['pack_size_name']/1000,3,'.','');
                        $info['demonstration']=number_format($info['demonstration']*$info['pack_size_name']/1000,3,'.','');
                        $info['current']=number_format($info['current']*$info['pack_size_name']/1000,3,'.','');
                    }
                    else
                    {
                        $type_starting_stock+=$info['starting_stock'];
                        $crop_starting_stock+=$info['starting_stock'];
                        $grand_starting_stock+=$info['starting_stock'];

                        $type_stock_in+=$info['stock_in'];
                        $crop_stock_in+=$info['stock_in'];
                        $grand_stock_in+=$info['stock_in'];
                        $type_excess+=$info['excess'];
                        $crop_excess+=$info['excess'];
                        $grand_excess+=$info['excess'];
                        $type_sales+=$info['sales'];
                        $crop_sales+=$info['sales'];
                        $grand_sales+=$info['sales'];
                        $type_sales_return+=$info['sales_return'];
                        $crop_sales_return+=$info['sales_return'];
                        $grand_sales_return+=$info['sales_return'];
                        $type_sales_bonus+=$info['sales_bonus'];
                        $crop_sales_bonus+=$info['sales_bonus'];
                        $grand_sales_bonus+=$info['sales_bonus'];
                        $type_sales_return_bonus+=$info['sales_return_bonus'];
                        $crop_sales_return_bonus+=$info['sales_return_bonus'];
                        $grand_sales_return_bonus+=$info['sales_return_bonus'];
                        $type_short+=$info['short'];
                        $crop_short+=$info['short'];
                        $grand_short+=$info['short'];
                        $type_rnd+=$info['rnd'];
                        $crop_rnd+=$info['rnd'];
                        $grand_rnd+=$info['rnd'];
                        $type_sample+=$info['sample'];
                        $crop_sample+=$info['sample'];
                        $grand_sample+=$info['sample'];
                        $type_demonstration+=$info['demonstration'];
                        $crop_demonstration+=$info['demonstration'];
                        $grand_demonstration+=$info['demonstration'];
                        $type_current+=$info['current'];
                        $crop_current+=$info['current'];
                        $grand_current+=$info['current'];

                    }

                    $items[]=$info;
                }
            }
            $items[]=$this->get_type_total_row($report_type,$type_starting_stock,$type_stock_in,$type_excess,$type_sales,$type_sales_return,$type_sales_bonus,$type_sales_return_bonus,$type_short,$type_rnd,$type_sample,$type_demonstration,$type_current,$type_total_price);
            $items[]=$this->get_crop_total_row($report_type,$crop_starting_stock,$crop_stock_in,$crop_excess,$crop_sales,$crop_sales_return,$crop_sales_bonus,$crop_sales_return_bonus,$crop_short,$crop_rnd,$crop_sample,$crop_demonstration,$crop_current,$crop_total_price);
            $items[]=$this->get_grand_total_row($report_type,$grand_starting_stock,$grand_stock_in,$grand_excess,$grand_sales,$grand_sales_return,$grand_sales_bonus,$grand_sales_return_bonus,$grand_short,$grand_rnd,$grand_sample,$grand_demonstration,$grand_current,$grand_total_price);
        }

        $this->jsonReturn($items);


    }
    private function get_stocks($time,$warehouse_id,$crop_id,$crop_type_id,$variety_id,$pack_size_id)
    {
        $stocks=array();
        if($time==0)
        {
            return $stocks;
        }
        //stock in
        $this->db->from($this->config->item('table_stockin_varieties').' stv');
        $this->db->select('variety_id,pack_size_id');
        $this->db->select('SUM(quantity) stock_in');

        $this->db->select('pack.name pack_size_name');
        $this->db->select('crop.name crop_name,crop.id crop_id');
        $this->db->select('type.name crop_type_name,type.id type_id');
        $this->db->select('v.name variety_name');
        $this->db->select('v.stock_id stock_id');

        $this->db->group_by(array('variety_id','pack_size_id'));

        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =stv.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_vpack_size').' pack','pack.id =stv.pack_size_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');

        $this->db->where('stv.status',$this->config->item('system_status_active'));
        $this->db->where('stv.date_stock_in <=',$time);
        if($warehouse_id>0)
        {
            $this->db->where('stv.warehouse_id',$warehouse_id);
        }
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
            $this->db->where('stv.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('pack.id',$pack_size_id);
        }
        $this->db->order_by('crop.ordering');
        $this->db->order_by('type.ordering');
        $this->db->order_by('v.ordering');
        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            $stocks[$result['variety_id']][$result['pack_size_id']]['stock_in']=$result['stock_in'];
            $stocks[$result['variety_id']][$result['pack_size_id']]['excess']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['sales']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['sales_return']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['sales_bonus']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['sales_return_bonus']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['short']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['rnd']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['sample']=0;
            $stocks[$result['variety_id']][$result['pack_size_id']]['demonstration']=0;

            $stocks[$result['variety_id']][$result['pack_size_id']]['pack_size_name']=$result['pack_size_name'];
            $stocks[$result['variety_id']][$result['pack_size_id']]['stock_id']=$result['stock_id'];
            $stocks[$result['variety_id']][$result['pack_size_id']]['variety_name']=$result['variety_name'];
            $stocks[$result['variety_id']][$result['pack_size_id']]['crop_type_name']=$result['crop_type_name'];
            $stocks[$result['variety_id']][$result['pack_size_id']]['crop_name']=$result['crop_name'];
        }

        //excess
        $this->db->from($this->config->item('table_stockin_excess_inventory').' ste');
        $this->db->select('ste.variety_id,ste.pack_size_id');
        $this->db->select('SUM(ste.quantity) stock_in');
        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =ste.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->group_by(array('ste.variety_id','ste.pack_size_id'));
        $this->db->where('ste.status',$this->config->item('system_status_active'));
        $this->db->where('ste.date_stock_in <=',$time);
        if($warehouse_id>0)
        {
            $this->db->where('ste.warehouse_id',$warehouse_id);
        }
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
            $this->db->where('ste.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('ste.pack_size_id',$pack_size_id);
        }
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['pack_size_id']]))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['excess']=$result['stock_in'];
            }

        }

        //stock out
        $this->db->from($this->config->item('table_stockout').' sout');
        $this->db->select('sout.variety_id,sout.pack_size_id,sout.purpose');
        $this->db->select('SUM(sout.quantity) stockout');
        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =sout.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->group_by(array('sout.variety_id','sout.pack_size_id','sout.purpose'));
        $this->db->where('sout.status',$this->config->item('system_status_active'));
        $this->db->where('sout.date_stock_out <=',$time);
        if($warehouse_id>0)
        {
            $this->db->where('sout.warehouse_id',$warehouse_id);
        }
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
            $this->db->where('sout.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('sout.pack_size_id',$pack_size_id);
        }
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['pack_size_id']]))
            {
                if($result['purpose']==$this->config->item('system_purpose_short'))
                {
                    $stocks[$result['variety_id']][$result['pack_size_id']]['short']=$result['stockout'];
                }
                elseif($result['purpose']==$this->config->item('system_purpose_rnd'))
                {
                    $stocks[$result['variety_id']][$result['pack_size_id']]['rnd']=$result['stockout'];
                }
                elseif($result['purpose']==$this->config->item('system_purpose_customer'))
                {
                    $stocks[$result['variety_id']][$result['pack_size_id']]['sample']=$result['stockout'];
                }
                elseif($result['purpose']==$this->config->item('system_purpose_demonstration'))
                {
                    $stocks[$result['variety_id']][$result['pack_size_id']]['demonstration']=$result['stockout'];
                }
            }

        }

        //sales
        $this->db->from($this->config->item('table_sales_po_details').' spd');
        $this->db->select('spd.variety_id,spd.pack_size_id');
        $this->db->select('SUM(spd.quantity) sales');
        $this->db->join($this->config->item('table_sales_po').' sp','sp.id =spd.sales_po_id','INNER');

        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =spd.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');

        $this->db->group_by(array('variety_id','pack_size_id'));

        $this->db->where('sp.status_approved',$this->config->item('system_status_po_approval_approved'));
        $this->db->where('spd.revision',1);
        $this->db->where('sp.date_approved <=',$time);

        if($warehouse_id>0)
        {
            $this->db->where('sp.warehouse_id',$warehouse_id);
        }
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
            $this->db->where('spd.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('spd.pack_size_id',$pack_size_id);
        }

        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['pack_size_id']]))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['sales']=$result['sales'];
            }
        }

        //sales return
        $this->db->from($this->config->item('table_sales_po_details').' spd');
        $this->db->select('variety_id,pack_size_id');
        $this->db->select('SUM(quantity_return) sales_return');
        $this->db->join($this->config->item('table_sales_po').' sp','sp.id =spd.sales_po_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =spd.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');

        $this->db->group_by(array('variety_id','pack_size_id'));

        $this->db->where('sp.status_received',$this->config->item('system_status_po_received_received'));
        $this->db->where('spd.revision',1);
        $this->db->where('spd.date_return <=',$time);
        if($warehouse_id>0)
        {
            $this->db->where('sp.warehouse_id',$warehouse_id);
        }
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
            $this->db->where('spd.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('spd.pack_size_id',$pack_size_id);
        }
        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['pack_size_id']]))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['sales_return']=$result['sales_return'];
            }

        }

        //sales bonus
        $this->db->from($this->config->item('table_sales_po_details').' spd');
        $this->db->select('variety_id,bonus_pack_size_id pack_size_id');
        $this->db->select('SUM(quantity_bonus) sales_bonus');
        $this->db->join($this->config->item('table_sales_po').' sp','sp.id =spd.sales_po_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =spd.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->group_by(array('variety_id','bonus_pack_size_id'));

        $this->db->where('bonus_details_id >',0);
        $this->db->where('sp.status_approved',$this->config->item('system_status_po_approval_approved'));
        $this->db->where('spd.revision',1);
        $this->db->where('sp.date_approved <=',$time);
        if($warehouse_id>0)
        {
            $this->db->where('sp.warehouse_id',$warehouse_id);
        }
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
            $this->db->where('spd.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('spd.pack_size_id',$pack_size_id);
        }
        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['pack_size_id']]))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['sales_bonus']=$result['sales_bonus'];
            }

        }
        //sales bonus return
        $this->db->from($this->config->item('table_sales_po_details').' spd');
        $this->db->select('variety_id,bonus_pack_size_id pack_size_id');
        $this->db->select('SUM(quantity_bonus_return) sales_return_bonus');
        $this->db->join($this->config->item('table_sales_po').' sp','sp.id =spd.sales_po_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =spd.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->group_by(array('variety_id','bonus_pack_size_id'));

        $this->db->where('bonus_details_id >',0);
        $this->db->where('sp.status_received',$this->config->item('system_status_po_received_received'));
        $this->db->where('spd.revision',1);
        $this->db->where('spd.date_return <=',$time);
        if($warehouse_id>0)
        {
            $this->db->where('sp.warehouse_id',$warehouse_id);
        }
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
            $this->db->where('spd.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('spd.pack_size_id',$pack_size_id);
        }
        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            if(isset($stocks[$result['variety_id']][$result['pack_size_id']]))
            {
                $stocks[$result['variety_id']][$result['pack_size_id']]['sales_return_bonus']=$result['sales_return_bonus'];
            }

        }
        return $stocks;

    }
    private function get_current_price($warehouse_id,$crop_id,$crop_type_id,$variety_id,$pack_size_id)
    {
        //$warehouse_id not used because need another join

        $this->db->from($this->config->item('table_setup_classification_variety_price').' vp');
        $this->db->select('vp.variety_id,vp.pack_size_id,vp.price');
        $this->db->join($this->config->item('table_setup_classification_varieties').' v','v.id =vp.variety_id','INNER');
        $this->db->join($this->config->item('table_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('vp.revision',1);
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
            $this->db->where('vp.variety_id',$variety_id);
        }
        if($pack_size_id>0)
        {
            $this->db->where('vp.pack_size_id',$pack_size_id);
        }
        $prices=array();

        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            $prices[$result['variety_id']][$result['pack_size_id']]['price']=$result['price'];
        }
        return $prices;
    }
    private function get_type_total_row($report_type,$starting_stock,$stock_in,$excess,$sales,$sales_return,$sales_bonus,$sales_return_bonus,$short,$rnd,$sample,$demonstration,$current,$current_total_price)
    {
        $row=array();
        $row['crop_name']='';
        $row['crop_type_name']='';
        $row['variety_name']='Total Type';
        $row['stock_id']='';
        if($report_type=='weight')
        {

            $row['starting_stock']=number_format($starting_stock/1000,3,'.','');
            $row['stock_in']=number_format($stock_in/1000,3,'.','');
            $row['excess']=number_format($excess/1000,3,'.','');
            $row['sales']=number_format($sales/1000,3,'.','');
            $row['sales_return']=number_format($sales_return/1000,3,'.','');
            $row['sales_bonus']=number_format($sales_bonus/1000,3,'.','');
            $row['sales_return_bonus']=number_format($sales_return_bonus/1000,3,'.','');
            $row['short']=number_format($short/1000,3,'.','');
            $row['rnd']=number_format($rnd/1000,3,'.','');
            $row['sample']=number_format($sample/1000,3,'.','');
            $row['demonstration']=number_format($demonstration/1000,3,'.','');
            $row['current']=number_format($current/1000,3,'.','');
        }
        else
        {
            $row['starting_stock']=$starting_stock;
            $row['stock_in']=$stock_in;
            $row['excess']=$excess;
            $row['sales']=$sales;
            $row['sales_return']=$sales_return;
            $row['sales_bonus']=$sales_bonus;
            $row['sales_return_bonus']=$sales_return_bonus;
            $row['short']=$short;
            $row['rnd']=$rnd;
            $row['sample']=$sample;
            $row['demonstration']=$demonstration;
            $row['current']=$current;

        }
        $row['current_total_price']=number_format($current_total_price,2);

        return $row;
    }
    private function get_crop_total_row($report_type,$starting_stock,$stock_in,$excess,$sales,$sales_return,$sales_bonus,$sales_return_bonus,$short,$rnd,$sample,$demonstration,$current,$current_total_price)
    {
        $row=array();
        $row['crop_name']='';
        $row['crop_type_name']='Total Crop';
        $row['variety_name']='';
        $row['stock_id']='';

        if($report_type=='weight')
        {
            $row['starting_stock']=number_format($starting_stock/1000,3,'.','');
            $row['stock_in']=number_format($stock_in/1000,3,'.','');
            $row['excess']=number_format($excess/1000,3,'.','');
            $row['sales']=number_format($sales/1000,3,'.','');
            $row['sales_return']=number_format($sales_return/1000,3,'.','');
            $row['sales_bonus']=number_format($sales_bonus/1000,3,'.','');
            $row['sales_return_bonus']=number_format($sales_return_bonus/1000,3,'.','');
            $row['short']=number_format($short/1000,3,'.','');
            $row['rnd']=number_format($rnd/1000,3,'.','');
            $row['sample']=number_format($sample/1000,3,'.','');
            $row['demonstration']=number_format($demonstration/1000,3,'.','');
            $row['current']=number_format($current/1000,3,'.','');
        }
        else
        {
            $row['starting_stock']=$starting_stock;
            $row['stock_in']=$stock_in;
            $row['excess']=$excess;
            $row['sales']=$sales;
            $row['sales_return']=$sales_return;
            $row['sales_bonus']=$sales_bonus;
            $row['sales_return_bonus']=$sales_return_bonus;
            $row['short']=$short;
            $row['rnd']=$rnd;
            $row['sample']=$sample;
            $row['demonstration']=$demonstration;
            $row['current']=$current;
        }
        $row['current_total_price']=number_format($current_total_price,2);
        return $row;
    }
    private function get_grand_total_row($report_type,$starting_stock,$stock_in,$excess,$sales,$sales_return,$sales_bonus,$sales_return_bonus,$short,$rnd,$sample,$demonstration,$current,$current_total_price)
    {
        $row=array();
        $row['crop_name']='Grand Total';
        $row['crop_type_name']='';
        $row['variety_name']='';
        $row['stock_id']='';
        $row['pack_size_name']='';
        $row['current_price']='';

        if($report_type=='weight')
        {
            $row['starting_stock']=number_format($starting_stock/1000,3,'.','');
            $row['stock_in']=number_format($stock_in/1000,3,'.','');
            $row['excess']=number_format($excess/1000,3,'.','');
            $row['sales']=number_format($sales/1000,3,'.','');
            $row['sales_return']=number_format($sales_return/1000,3,'.','');
            $row['sales_bonus']=number_format($sales_bonus/1000,3,'.','');
            $row['sales_return_bonus']=number_format($sales_return_bonus/1000,3,'.','');
            $row['short']=number_format($short/1000,3,'.','');
            $row['rnd']=number_format($rnd/1000,3,'.','');
            $row['sample']=number_format($sample/1000,3,'.','');
            $row['demonstration']=number_format($demonstration/1000,3,'.','');
            $row['current']=number_format($current/1000,3,'.','');
        }
        else
        {
            $row['starting_stock']=$starting_stock;
            $row['stock_in']=$stock_in;
            $row['excess']=$excess;
            $row['sales']=$sales;
            $row['sales_return']=$sales_return;
            $row['sales_bonus']=$sales_bonus;
            $row['sales_return_bonus']=$sales_return_bonus;
            $row['short']=$short;
            $row['rnd']=$rnd;
            $row['sample']=$sample;
            $row['demonstration']=$demonstration;
            $row['current']=$current;
        }
        $row['current_total_price']=number_format($current_total_price,2);
        return $row;
    }

}
