<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<form id="reinvoice_form" class="external" action="<?php echo site_url($CI->controller_url.'/index/save_reinvoice');?>" method="post">
    <input type="hidden" name="id" value="<?php echo $invoice_info['id']; ?>" />
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $farmer_info['name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $farmer_info['mobile_no'];?></label>
        </div>
    </div>
    <?php
    if(strlen($farmer_info['nid'])>0)
    {
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $farmer_info['nid'];?></label>
            </div>
        </div>
        <?php
    }
    ?>
    <?php
    if(strlen($farmer_info['address'])>0)
    {
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $farmer_info['address'];?></label>
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
            <label class="control-label" id="discount"><?php echo $invoice_info['discount_percentage'];?></label>%
            <input type="hidden" id="discount_non_coupon" value="<?php echo $invoice_info['discount_percentage'];?>">
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="widget-header">
        <div class="title">
            Items
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo 'Variety '.$this->lang->line('LABEL_BARCODE');?></label>
        </div>
        <div class="col-sm-4 col-xs-4">
            <input type="text" id="variety_barcode" class="form-control" value=""/>
        </div>
        <div class="col-sm-4 col-xs-4">
            <div class="action_button">
                <button id="button_action_variety_add" type="button" class="btn"><?php echo $this->lang->line('LABEL_ACTION1');?></button>
            </div>
        </div>
    </div>
    <div style="overflow-x: auto;" class="row show-grid" id="order_items_container">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PRICE_PACK'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CURRENT_STOCK_PIECES'); ?></th>
                <th style="min-width: 100px;">Previous Quantity</th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY_PIECES'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_WEIGHT_KG'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?></th>
                <th style="min-width: 150px;"><?php echo $CI->lang->line('ACTION'); ?></th>
            </tr>
            </thead>
            <tbody>
                <?php
                foreach($invoice_details as $details)
                {
                    ?>
                    <tr>
                        <td>
                            <label class="crop_name"><?php echo $details['crop_name']; ?></label>
                        </td>
                        <td>
                            <label class="type_name"><?php echo $details['type_name']; ?></label>
                        </td>
                        <td>
                            <label class="variety_name"><?php echo $details['variety_name']; ?></label>
                        </td>
                        <td>
                            <label class="pack_size" id="pack_size_<?php echo System_helper::get_variety_barcode($details['crop_id'],$details['variety_id'],$details['pack_size_id']); ?>"><?php echo $details['pack_size']; ?></label>
                        </td>
                        <td class="text-right">
                            <label class="pack_size_price" id="pack_size_price_<?php echo System_helper::get_variety_barcode($details['crop_id'],$details['variety_id'],$details['pack_size_id']); ?>">&nbsp;</label>
                        </td>
                        <td class="text-right">
                            <label class="current_stock" id="current_stock_<?php echo System_helper::get_variety_barcode($details['crop_id'],$details['variety_id'],$details['pack_size_id']); ?>"><?php echo $stock_info[System_helper::get_variety_barcode($details['crop_id'],$details['variety_id'],$details['pack_size_id'])]; ?></label>
                        </td>
                        <td class="text-right">
                            <label><?php echo $details['quantity_sale']; ?></label>
                        </td>
                        <td class="text-right">
                            <input type="text"class="form-control text-right quantity integer_type_positive" id="quantity_<?php echo System_helper::get_variety_barcode($details['crop_id'],$details['variety_id'],$details['pack_size_id']); ?>" name="varieties[<?php echo $details['variety_id']; ?>][<?php echo $details['pack_size_id']; ?>][quantity]" value="<?php echo $details['quantity_sale']; ?>"/>
                        </td>
                        <td class="text-right">
                            <label class="weight" id="weight_<?php echo System_helper::get_variety_barcode($details['crop_id'],$details['variety_id'],$details['pack_size_id']); ?>">&nbsp;</label>
                        </td>
                        <td class="text-right">
                            <label class="price" id="price_<?php echo System_helper::get_variety_barcode($details['crop_id'],$details['variety_id'],$details['pack_size_id']); ?>">&nbsp;</label>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?></label></td>
                    <td class="text-right"><label id="total_quantity">&nbsp;</label></td>
                    <td class="text-right"><label id="total_weight">&nbsp;</label></td>
                    <td class="text-right"><label id="total_price">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?></label></td>
                    <td class="text-right"><label id="total_discount">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                    <td><label>Payable</label></td>
                    <td class="text-right"><label id="total_payable">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                    <td><label>Previously Paid</label></td>
                    <td class="text-right">
                        <label id="total_previous_paid">
                            <?php echo number_format($invoice_info['amount_total']-$invoice_info['amount_total']*$invoice_info['discount_percentage']/100,2); ?>
                        </label>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                    <td><label>Current Payable</label></td>
                    <td class="text-right">
                        <label id="total_current_payable">
                            &nbsp;
                        </label>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                    <td><label>Paid</label></td>
                    <td class="text-right"><input id="total_paid" name="amount_paid" type="text"class="form-control text-right float_type_positive" value=""/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                    <td><label>Change</label></td>
                    <td class="text-right"><label id="total_change">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Re-Invoice Reason<span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <textarea class="form-control" name="remarks"></textarea>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">

        </div>
        <div class="col-sm-4 col-xs-4">
            <div class="action_button">
                <button id="button_action_save" type="button" class="btn" data-form="#reinvoice_form" data-message-confirm="Are you sure?"><?php echo $this->lang->line('ACTION_SAVE');?></button>
            </div>
        </div>
        <div class="col-sm-4 col-xs-4">

        </div>
    </div>
</form>
<div id="system_content_add_more" style="display: none;">
    <table>
        <tbody>
        <tr>
            <td>
                <label class="crop_name">&nbsp;</label>
            </td>
            <td>
                <label class="type_name">&nbsp;</label>
            </td>
            <td>
                <label class="variety_name">&nbsp;</label>
            </td>
            <td>
                <label class="pack_size">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="pack_size_price">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="current_stock">&nbsp;</label>
            </td>
            <td class="text-right">
                <label>&nbsp;</label>
            </td>
            <td class="text-right">
                <input type="text"class="form-control text-right quantity integer_type_positive" value="1"/>
            </td>
            <td class="text-right">
                <label class="weight">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="price">&nbsp;</label>
            </td>
            <td><button type="button" class="btn btn-danger system_button_add_delete"><?php echo $CI->lang->line('ACTION_DELETE'); ?></button></td>
        </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
        $("#order_items_container tbody .pack_size").each( function( index, element )
        {
            var variety_barcode=$(this).attr('id').substr(10);
            $('#pack_size_price_'+variety_barcode).html(number_format(varieties_info[variety_barcode]['price'],2));
            var quantity=0;
            if($('#quantity_'+variety_barcode).val()==parseFloat($('#quantity_'+variety_barcode).val()))
            {
                quantity=parseFloat($('#quantity_'+variety_barcode).val());
            }
            $('#weight_'+variety_barcode).html(number_format(varieties_info[variety_barcode]['pack_size']*quantity/1000,3,'.',''));
            $('#price_'+variety_barcode).html(number_format(varieties_info[variety_barcode]['price']*quantity,2));

        });
        calculate_total();
    });
</script>