<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USERNAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['user_name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_GROUP');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['group_name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DESIGNATION_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['designation_name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_BIRTH');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::display_date($user_info['date_birth']);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_GENDER');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['gender'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MARITAL_STATUS');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['status_marital'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['nid'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['address'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BLOOD_GROUP');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['blood_group'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['mobile_no'];?></label>
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

</div>

<div class="clearfix"></div>
