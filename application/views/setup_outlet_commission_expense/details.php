<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
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
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            Details Commission,Payment and Expense
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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_YEAR');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['year'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $CI->lang->line('LABEL_MONTH_'.$item['month']);?></label>
        </div>
    </div><div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Actual <?php echo $CI->lang->line('LABEL_AMOUNT_SALE');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($item['amount_actual_sale'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Actual <?php echo $CI->lang->line('LABEL_AMOUNT_DISCOUNT');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($item['amount_actual_discount'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Actual <?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($item['amount_actual_payable'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Total <?php echo $CI->lang->line('LABEL_AMOUNT_COMMISSION');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($item['amount_commission_total'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Total <?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($item['amount_payment_total'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Total <?php echo $CI->lang->line('LABEL_AMOUNT_EXPENSE');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($item['amount_expense_total'],2);?></label>
        </div>
    </div>
    <div class="widget-header">
        <div class="title">
            Commission Details
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
            Expense Details
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    foreach($expenses as $row)
    {
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $row['name'];?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($row['amount'],2);?></label>
            </div>
        </div>
    <?php
    }
    ?>
</div>

<div class="clearfix"></div>
