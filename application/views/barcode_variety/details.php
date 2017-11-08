<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if(isset($CI->permissions['action4']) && ($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>'Generate Barcode',
        'id'=>'button_action_report',
        'data-form'=>'#save_form'
    );
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'onClick'=>"window.print()"
    );
}
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/details/'.$item['id'])
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<form class="hidden-print" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['crop_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_TYPE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['type_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_VARIETY_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['variety_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PACK_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['pack_size_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PRICE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['price'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Expire <?php echo $CI->lang->line('LABEL_DATE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['date_expire'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Line 1<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[line1]" id="line1" class="form-control" value="<?php echo $item['line1']; ?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[outlet]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($outlets as $row)
                    {?>
                        <option value="<?php echo $row['name_short']?>"><?php echo $row['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Barcode Purpose<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[barcode_purpose]" class="form-control">
                    <option value="sticker">Sticker</option>
                    <option value="packet">Packet</option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Show Expire date<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[show_expire]" class="form-control">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>

    </div>

    <div class="clearfix"></div>
</form>
<div id="system_report_container">
</div>