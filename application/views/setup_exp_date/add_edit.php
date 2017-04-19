<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_SAVE"),
    'id'=>'button_action_save',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>

<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>

        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label for="date_expire" class="control-label pull-right"><?php echo $CI->lang->line('DATE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="date" name="item[date_expire]" id="date_expire" class="form-control" value="<?php echo $item['date_expire']; ?>">
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
<script>
    jQuery(document).ready(function()
    {
        $("#date_expire").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "+0:+3"});
    });
</script>
