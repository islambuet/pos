<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_DETAILS"),
    'href'=>site_url($CI->controller_url.'/index/details/'.$item['id'])
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_SAVE"),
    'id'=>'button_action_save',
    'data-message-confirm'=>'Are you Sure to Cancel This sale?',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div style="width: 320px;font-size: 10px;text-align: center; font-weight: bold;line-height: 10px;margin-left:-40px; background-color: #F7F7F7;">
    <div style="font-size:14px;line-height: 16px;">Malik Seeds</div>
    <div style="font-size:12px;line-height: 14px;"><?php echo $item['outlet_short_name'];?></div>
    <img src="<?php echo site_url('barcode_generator/get_image/invoice/'.($item['id']));  ?>">
    <div style="margin:5px 0;padding: 5px;border-bottom: 2px solid #000000;border-top: 2px solid #000000;text-align: left;">
        <div><?php echo $CI->lang->line('LABEL_DATE');?> :<?php echo System_helper::display_date_time($item['date_sale']);?></div>
        <div><?php echo $CI->lang->line('LABEL_INVOICE_NO');?> :<?php echo System_helper::get_invoice_barcode($item['id']);?></div>
        <div><?php echo $CI->lang->line('LABEL_CUSTOMER_NAME');?> :<?php echo $item['farmer_name'];?></div>
        <div><?php echo $CI->lang->line('LABEL_MOBILE_NO');?> :<?php echo $item['mobile_no'];?></div>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
            <th><?php echo $CI->lang->line('LABEL_PRICE_PACK'); ?></th>
            <th><?php echo $CI->lang->line('LABEL_QUANTITY_PIECES'); ?></th>
            <th><?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $total_quantity=0;
        $total_weight=0;
        $total_price=0;
        foreach($details as $row)
        {
            ?>
            <tr>
                <td style="padding: 0 5px;"><label><?php echo $row['variety_name'].'('.$row['pack_size'].'g)'; ?></label></td>
                <td style="padding: 0 5px;"><label><?php echo number_format($row['price_unit'],2); ?></label></td>
                <td style="padding: 0 5px;"><label><?php echo $row['quantity_sale']; ?></label></td>
                <td style="padding: 0 5px;" class="text-right">
                    <label>
                        <?php
                        $total_quantity+=$row['quantity_sale'];
                        $total_weight+=$row['quantity_sale']*$row['pack_size'];
                        $total_price+=$row['quantity_sale']*$row['price_unit'];
                        echo number_format($row['quantity_sale']*$row['price_unit'],2);
                        ?>
                    </label>
                </td>
            </tr>
        <?php
        }
        ?>

        </tbody>
        <tfoot>
        <tr>
            <td style="padding: 0 5px;" colspan="2">&nbsp;</td>
            <td style="padding: 0 5px;"><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?> :</label></td>
            <td style="padding: 0 5px;" class="text-right"><label><?php echo number_format($total_price,2); ?></label></td>
        </tr>
        <?php
        $total_discount=$total_price*$item['discount_percentage']/100;
        if($total_discount>0)
        {
            ?>
            <tr>
                <td style="padding: 0 5px;" colspan="2">&nbsp;</td>
                <td style="padding: 0 5px;"><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?> :</label></td>
                <td style="padding: 0 5px;" class="text-right">
                    <label>
                        <?php
                        echo number_format($total_discount,2);
                        ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td style="padding: 0 5px;" colspan="2">&nbsp;</td>
                <td style="padding: 0 5px;"><label>Payable :</label></td>
                <td style="padding: 0 5px;" class="text-right"><label><?php echo number_format($total_price-$total_discount,2); ?></label></td>
            </tr>

        <?php
        }
        ?>
        <tr>
            <td style="padding: 0 5px;" colspan="2">&nbsp;</td>
            <td style="padding: 0 5px;"><label>Paid</label></td>
            <td style="padding: 0 5px;" class="text-right"><label><?php echo number_format($item['amount_cash'],2); ?></label></td>

        </tr>
        <?php
        if(($item['amount_cash']-$total_price+$total_discount)>0)
        {
            ?>
            <tr>
                <td style="padding: 0 5px;" colspan="2">&nbsp;</td>
                <td style="padding: 0 5px;"><label>Change</label></td>
                <td style="padding: 0 5px;" class="text-right"><label><?php echo number_format($item['amount_cash']-$total_price+$total_discount,2); ?></label></td>
            </tr>

        <?php
        }
        ?>

        </tfoot>
    </table>
</div>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_cancel');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
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
                <label class="control-label pull-right">Cancel Reason<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea class="form-control" name="remarks"></textarea>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
