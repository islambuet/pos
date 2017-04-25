<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
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
            <label class="control-label pull-right">Commission base on </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            if(sizeof($commission_users)>0)
            {
                $amount=$outlet['sale_total']-$outlet['sale_canceled'];
                $total_commission=0;
                ?>
                <label class="control-label">Actual Sale: <?php echo number_format($amount,2);?></label>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th style="">Name</th>
                        <th>Commission %</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($commission_users as $user)
                    {

                        ?>
                        <tr>
                            <td>
                                <label class="control-label"><?php echo $user['name']; ?></label>
                            </td>
                            <td>
                                <label class="control-label"><?php echo $user['commission']; ?></label>
                            </td>
                            <td class="text-right">
                                <?php
                                $commission=$user['commission']*$amount/100;
                                $total_commission+=$commission;
                                ?>
                                <label class="control-label"><?php echo number_format($commission,2); ?></label>
                            </td>

                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan="2" class="text-right">
                            Total
                        </td>
                        <td class="text-right">
                            <label class="control-label"><?php echo number_format($total_commission,2); ?></label>
                        </td>

                    </tr>
                    </tbody>
                </table>
                <?php

            }
            else
            {
                ?>
                <label class="control-label">No User Found</label>
                <?php
            }
            ?>

        </div>
    </div>

    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Invoice Total</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['sale_total'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Invoice Discount</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['sale_total']-$outlet['payable_total'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Invoice Payable</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['payable_total'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Canceled Total</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['sale_canceled'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Canceled Discount</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['sale_canceled']-$outlet['payable_canceled'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Canceled Payable</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['payable_canceled'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Actual Total</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['sale_total']-$outlet['sale_canceled'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Actual Discount</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['sale_total']-$outlet['sale_canceled']-$outlet['payable_total']+$outlet['payable_canceled'],2);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Actual Payable</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo number_format($outlet['payable_total']-$outlet['payable_canceled'],2);?></label>
        </div>
    </div>
</div>
