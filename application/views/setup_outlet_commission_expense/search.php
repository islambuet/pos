<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_SAVE"),
    'id'=>'button_action_save',
    'data-form'=>'#save_form'
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_CLEAR"),
    'id'=>'button_action_clear',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_YEAR');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <select id="year" class="form-control">
                <?php
                for($i=$item['year'];$i>=$item['year']-1;$i--)
                {?>
                    <option value="<?php echo $i;?>"><?php echo $i;?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <select id="month" class="form-control">
                <?php
                for($i=1;$i<13;$i++)
                {?>
                    <option value="<?php echo $i;?>" <?php if($i==$item['month']){ echo "selected";}?>><?php echo $CI->lang->line('LABEL_MONTH_'.$i);?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <select id="customer_id" class="form-control">
                <option value=""><?php echo $this->lang->line('SELECT');?></option>
                <?php
                foreach($CI->user_outlets as $row)
                {?>
                    <option value="<?php echo $row['id']?>"><?php echo $row['name'];?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<div id="system_report_container">

</div>

<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(document).off("change", "#year");
        $(document).on("change","#year",function()
        {
            $("#system_report_container").html("");
            $("#customer_id").val("");
        });
        $(document).off("change", "#month");
        $(document).on("change","#month",function()
        {
            $("#system_report_container").html("");
            $("#customer_id").val("");
        });
        $(document).off("change", "#customer_id");
        $(document).on("change","#customer_id",function()
        {
            $("#system_report_container").html("");
            var year=$('#year').val();
            var month=$('#month').val();
            var customer_id=$('#customer_id').val();
            if((year>0)&& (month>0) && (customer_id>0))
            {

                $.ajax({
                    url: '<?php echo site_url($CI->controller_url.'/index/load_add_form') ?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{customer_id:customer_id,year:year,month:month},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });

            }


        });
    });
</script>