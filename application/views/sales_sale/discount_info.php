<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
?>
<div class="row show-grid">
    <div class="col-xs-4">
        <label class="control-label pull-right">Coupon Holder <?php echo $CI->lang->line('LABEL_NAME');?></label>
    </div>
    <div class="col-sm-4 col-xs-8">
        <label class="control-label"><?php echo $item['name'];?></label>
    </div>
</div>
<div class="row show-grid">
    <div class="col-xs-4">
        <label class="control-label pull-right">Coupon Holder <?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
    </div>
    <div class="col-sm-4 col-xs-8">
        <label class="control-label"><?php echo $item['mobile_no'];?></label>
    </div>
</div>
<div class="row show-grid">
    <div class="col-xs-4">
        <label class="control-label pull-right">Coupon <?php echo $CI->lang->line('LABEL_DISCOUNT');?></label>
    </div>
    <div class="col-sm-4 col-xs-8">
        <?php
        if(sizeof($assigned)>0)
        {
            ?>
            <label class="control-label"><?php echo $farmer_type['discount_coupon'];?></label>%
            <input type="hidden" name="discount_farmer_id" value="<?php echo $item['id']; ?>">
            <?php
        }
        else
        {
            ?>
            <label class="control-label">This Coupon is allowed in this Outlet.</label>
            <?php
        }
        ?>

    </div>
</div>