<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TYPE');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['type_name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['mobile_no'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Coupon Discount %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['discount_coupon'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Non Coupon/Card Discount %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['discount_non_coupon'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['nid'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['address'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Assigned Outlet(s)</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            if(sizeof($assigned_outlets)>0)
            {
                foreach($assigned_outlets as $outlet)
                {
                ?>
                    <label class="control-label"><?php echo $outlet['text']; ?></label><br>
                <?php
                }
            }
            else
            {
                ?>
                <label class="control-label">No Outlet Assigned Yet.</label>
                <?php
            }
            ?>

        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Farmer Created By</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $users[$item['user_created']]['name'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Farmer Created Time</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::display_date_time($item['date_created']);?></label>
        </div>
    </div>
    <?php
    if($item['user_updated']>0)
    {
        ?>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Farmer Updated By</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $users[$item['user_updated']]['name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Farmer Updated Time</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date_time($item['date_updated']);?></label>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<div class="clearfix"></div>
