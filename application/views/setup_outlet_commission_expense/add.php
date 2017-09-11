<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();

?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="0" />
    <input type="hidden" name="item[year]"  value="<?php echo $item['year']; ?>" />
    <input type="hidden" name="item[month]" value="<?php echo $item['month']; ?>" />
    <input type="hidden" name="item[customer_id]" value="<?php echo $item['customer_id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                Sales Info
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Actual Total</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($item['amount_actual_sale'],2);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Actual Discount</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($item['amount_actual_discount'],2);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Actual Payable</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($item['amount_actual_payable'],2);?></label>
            </div>
        </div>
        <div class="widget-header">
            <div class="title">
                Commission
            </div>
            <div class="clearfix"></div>
        </div>

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>
                        <?php
                        if($item['incharge']=='Customer')
                        {
                            echo 'Farmer Type';
                        }
                        elseif($item['incharge']=='Arm')
                        {
                            echo 'Name';
                        }
                        ?>
                    </th>
                    <th>
                        <?php
                        if($item['incharge']=='Customer')
                        {
                            echo 'Sale amount';
                        }
                        elseif($item['incharge']=='Arm')
                        {
                            echo 'Payable Sale amount';
                        }
                        ?>
                    </th>
                    <th>Commission %</th>
                    <th>Amount</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($commissions as $commission)
                {

                    ?>
                    <tr>
                        <td>
                            <label class="control-label"><?php echo $commission['name']; ?></label>
                        </td>
                        <td class="text-right">
                            <label class="control-label"><?php echo number_format($commission['amount_based_on'],2); ?></label>
                        </td>
                        <td class="text-right">
                            <label class="control-label"><?php echo $commission['commission_percentage']; ?></label>
                        </td>
                        <td class="text-right">
                            <?php
                            ?>
                            <label class="control-label"><?php echo number_format($commission['amount_commission'],2); ?></label>
                        </td>

                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td colspan="3" class="text-right">
                        Total
                    </td>
                    <td class="text-right">
                        <label class="control-label"><?php echo number_format($item['amount_commission_total'],2); ?></label>
                    </td>

                </tr>
                </tbody>
            </table>
        <div class="widget-header">
            <div class="title">
                Payment And Expense
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_AMOUNT_PAYMENT');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[amount_payment_total]" class="form-control float_type_all" value=""/>
            </div>
        </div>
        <?php
        foreach($expense_items as $row)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $row['name'];?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <input type="text" name="expense[<?php echo $row['id'];?>]" class="form-control float_type_all" value=""/>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_REMARKS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea class="form-control" name="item[remarks]"></textarea>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
