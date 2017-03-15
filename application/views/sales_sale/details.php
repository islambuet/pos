<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$user = User_helper::get_user();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
{
    $action_buttons[]=array(
        'label'=>$CI->lang->line("ACTION_NEW"),
        'href'=>site_url($CI->controller_url.'/index/add')
    );
}
if(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))
{
    if(($item['status']==$CI->config->item('system_status_active')&&(!($item['invoice_old_id']>0))))
    {
        if(System_helper::display_date(time())==System_helper::display_date($item['date_sale']))
        {
            $action_buttons[]=array(
                'type'=>'button',
                'label'=>'Sale Cancel',
                'class'=>'button_jqx_action',
                'data-action-link'=>site_url($CI->controller_url.'/index/delete')
            );
        }
        elseif($user->user_group==1)
        {
            $action_buttons[]=array(
                'type'=>'button',
                'label'=>'Sale Cancel',
                'class'=>'button_jqx_action',
                'data-action-link'=>site_url($CI->controller_url.'/index/delete')
            );
        }
    }


}
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_PRINT"),
    'onClick'=>"window.print()"
);
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/details/'.$item['id'])
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div style="width: 320px;font-size: 10px;text-align: center; font-weight: bold;line-height: 10px;margin-left:-40px; background-color: #F7F7F7;">
    <div style="font-size:14px;line-height: 16px;">Malik Seeds</div>
    <div style="font-size:12px;line-height: 14px;"><?php echo $item['outlet_short_name'];?></div>
    <img src="<?php echo site_url('barcode_generator/get_image/'.(System_helper::get_invoice_barcode($item['id'])));  ?>">
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
<div class="row widget hidden-print">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
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
        <div class="widget-header">
            <div class="title">
                Items
            </div>
            <div class="clearfix"></div>
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
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY_PIECES'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_WEIGHT_KG'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?></th>
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
                    <td colspan="4">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?></label></td>
                    <td class="text-right"><label><?php echo $total_quantity; ?></label></td>
                    <td class="text-right"><label><?php echo number_format($total_weight/1000,3,'.',''); ?></label></td>
                    <td class="text-right"><label><?php echo number_format($total_price,2); ?></label></td>
                </tr>
                <tr>
                    <td colspan="6">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?></label></td>
                    <td class="text-right">
                        <label>
                            <?php
                            $total_discount=$total_price*$item['discount_percentage']/100;
                            echo number_format($total_discount,2);
                            ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">&nbsp;</td>
                    <td><label>Payable</label></td>
                    <td class="text-right"><label><?php echo number_format($total_price-$total_discount,2); ?></label></td>

                </tr>
                <tr>
                    <td colspan="6">&nbsp;</td>
                    <td><label>Paid</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_cash'],2); ?></label></td>

                </tr>
                <tr>
                    <td colspan="6">&nbsp;</td>
                    <td><label>Change</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_cash']-$total_price+$total_discount,2); ?></label></td>
                </tr>
                </tfoot>
            </table>
        </div>
</div>

