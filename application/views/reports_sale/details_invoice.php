<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();

?>
<div class="row widget hidden-print">
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['outlet_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_INVOICE_NO');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::get_invoice_barcode($item['id']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Invoice <?php echo $CI->lang->line('LABEL_DATE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date_time($item['date_sale']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CUSTOMER_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['farmer_name'];?></label>
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
        <?php
        if(strlen($item['nid'])>0)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $item['nid'];?></label>
                </div>
            </div>
        <?php
        }
        ?>
        <?php
        if(strlen($item['address'])>0)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $item['address'];?></label>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISCOUNT');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['discount_percentage'];?></label>%
            </div>
        </div>
        <?php
        if(($item['discount_farmer_id']>0)&&($item['discount_farmer_id']!=$item['farmer_id']))
        {
            $discount_farmer_info=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$item['discount_farmer_id']),1);
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Coupon Holder <?php echo $CI->lang->line('LABEL_NAME');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $discount_farmer_info['name'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Coupon Holder <?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $discount_farmer_info['mobile_no'];?></label>
                </div>
            </div>
            <?php
        }
        ?>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Invoice Created Time</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date_time($item['date_created']);?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Invoice Created By</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $users[$item['user_created']]['name'];?></label>
            </div>
        </div>
        <?php
        if($item['status']==$CI->config->item('system_status_inactive'))
        {
            ?>
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Invoice Canceled Time</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo System_helper::display_date_time($item['date_canceled']);?></label>
                </div>
            </div>
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Invoice Canceled By</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $users[$item['user_canceled']]['name'];?></label>
                </div>
            </div>
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Reason</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $item['remarks'];?></label>
                </div>
            </div>
        <?php
        }
        ?>
        <?php
        if($item['invoice_old_id']>0)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Previous <?php echo $CI->lang->line('LABEL_INVOICE_NO');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo System_helper::get_invoice_barcode($item['invoice_old_id']);?></label>
                </div>
            </div>
        <?php
        }
        ?>
        <?php
        if($item['invoice_new_id']>0)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">New <?php echo $CI->lang->line('LABEL_INVOICE_NO');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo System_helper::get_invoice_barcode($item['invoice_new_id']);?></label>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="widget-header">
            <div class="title">
                Items
            </div>
            <div class="clearfix"></div>
        </div>
        <div style="overflow-x: auto;" class="row show-grid">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_NAME'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PRICE_PACK'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY_PIECES'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_WEIGHT_KG'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $total_quantity=0;
                $total_weight=0;
                foreach($details as $row)
                {
                    ?>
                    <tr>
                        <td><label><?php echo $row['crop_name']; ?></label></td>
                        <td><label><?php echo $row['type_name']; ?></label></td>
                        <td><label><?php echo $row['variety_name']; ?></label></td>
                        <td class="text-right"><label><?php echo $row['pack_size']; ?></label></td>
                        <td class="text-right"><label><?php echo number_format($row['price_unit'],2); ?></label></td>
                        <td class="text-right"><label><?php echo $row['quantity_sale']; ?></label></td>
                        <td class="text-right"><label><?php echo number_format($row['quantity_sale']*$row['pack_size']/1000,3,'.',''); ?></label></td>
                        <td class="text-right">
                            <label>
                                <?php
                                $total_quantity+=$row['quantity_sale'];
                                $total_weight+=$row['quantity_sale']*$row['pack_size'];
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
                    <td colspan="4">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?></label></td>
                    <td class="text-right"><label><?php echo $total_quantity; ?></label></td>
                    <td class="text-right"><label><?php echo number_format($total_weight/1000,3,'.',''); ?></label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_total'],2); ?></label></td>
                </tr>
                <?php
                $total_discount=$item['amount_total']-$item['amount_payable'];
                if($total_discount>0)
                {
                    ?>
                    <tr>
                        <td colspan="6">&nbsp;</td>
                        <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?></label></td>
                        <td class="text-right">
                            <label>
                                <?php
                                echo number_format($total_discount,2);
                                ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">&nbsp;</td>
                        <td><label>Payable</label></td>
                        <td class="text-right"><label><?php echo number_format($item['amount_payable'],2); ?></label></td>

                    </tr>
                <?php
                }
                if($item['invoice_old_id']>0)
                {
                    ?>
                    <tr>
                        <td colspan="6">&nbsp;</td>
                        <td><label>Previously Paid</label></td>
                        <td class="text-right"><label><?php echo number_format($item['amount_previous_paid'],2); ?></label></td>
                    </tr>
                    <tr>
                        <td colspan="6">&nbsp;</td>
                        <td><label>Current Payable</label></td>
                        <td class="text-right"><label><?php echo number_format($item['amount_payable']-$item['amount_previous_paid'],2); ?></label></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td colspan="6">&nbsp;</td>
                    <td><label>Paid</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_cash'],2); ?></label></td>

                </tr>
                <?php
                if(($item['amount_cash']-$item['amount_payable']+$item['amount_previous_paid'])>0)
                {
                    ?>
                    <tr>
                        <td colspan="6">&nbsp;</td>
                        <td><label>Change</label></td>
                        <td class="text-right"><label><?php echo number_format($item['amount_cash']-$item['amount_payable']+$item['amount_previous_paid'],2); ?></label></td>
                    </tr>
                <?php
                }
                ?>
                </tfoot>
            </table>
        </div>
</div>

