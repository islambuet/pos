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
    'label'=>$CI->lang->line("ACTION_SAVE_NEW"),
    'id'=>'button_action_save_new',
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
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $user['id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USERNAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if($user['id']==0)
                {
                    ?>
                    <input type="text" name="user[user_name]" id="user_name" class="form-control" value="<?php echo $user['user_name'];?>"/>
                <?php
                }
                else
                {
                    ?>
                    <label class="control-label"><?php echo $user['user_name'];?></label>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="user_info[name]" id="name" class="form-control" value="<?php echo $user_info['name'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_GROUP');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="user_group" name="user_info[user_group]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($user_groups as $user_group)
                    {?>
                        <option value="<?php echo $user_group['value']?>" <?php if($user_group['value']==$user_info['user_group']){ echo "selected";}?>><?php echo $user_group['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DESIGNATION_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="designation" name="user_info[designation]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($designations as $designation)
                    {?>
                        <option value="<?php echo $designation['value']?>" <?php if($designation['value']==$user_info['designation']){ echo "selected";}?>><?php echo $designation['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_BIRTH');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="user_info[date_birth]" id="date_birth" class="form-control dob" value="<?php echo System_helper::display_date($user_info['date_birth']);?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_GENDER');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <div class="radio-inline">
                    <label><input type="radio" value="Male" <?php if($user_info['gender']=='Male'){echo 'checked';} ?> name="user_info[gender]">Male</label>
                </div>
                <div class="radio-inline">
                    <label><input type="radio" value="Female" <?php if($user_info['gender']=='Female'){echo 'checked';} ?> name="user_info[gender]">Female</label>
                </div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MARITAL_STATUS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <div class="radio-inline">
                    <label><input type="radio" value="Married" <?php if($user_info['status_marital']=='Married'){echo 'checked';} ?> name="user_info[status_marital]">Married</label>
                </div>
                <div class="radio-inline">
                    <label><input type="radio" value="Un-Married" <?php if($user_info['status_marital']=='Un-Married'){echo 'checked';} ?> name="user_info[status_marital]">Un-Married</label>
                </div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="user_info[nid]" id="nid" class="form-control" value="<?php echo $user_info['nid'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea class="form-control" name="user_info[address]"><?php echo $user_info['address'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BLOOD_GROUP');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="blood_group" name="user_info[blood_group]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <option value="A+" <?php if($user_info['blood_group']=='A+'){ echo "selected";}?>>A+</option>
                    <option value="A-" <?php if($user_info['blood_group']=='A-'){ echo "selected";}?>>A-</option>
                    <option value="AB+" <?php if($user_info['blood_group']=='AB+'){ echo "selected";}?>>AB+</option>
                    <option value="AB-" <?php if($user_info['blood_group']=='AB-'){ echo "selected";}?>>AB-</option>
                    <option value="B+" <?php if($user_info['blood_group']=='B+'){ echo "selected";}?>>B+</option>
                    <option value="B-" <?php if($user_info['blood_group']=='B-'){ echo "selected";}?>>B-</option>
                    <option value="O+" <?php if($user_info['blood_group']=='O+'){ echo "selected";}?>>O+</option>
                    <option value="O-" <?php if($user_info['blood_group']=='O-'){ echo "selected";}?>>O-</option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="user_info[mobile_no]" id="mobile_no" class="form-control" value="<?php echo $user_info['mobile_no'];?>"/>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ORDER');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="user_info[ordering]" id="ordering" class="form-control" value="<?php echo $user_info['ordering'] ?>" >
            </div>
        </div>

    </div>

    <div class="clearfix"></div>
</form>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(".dob").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "-100:+0"});
    });
</script>
