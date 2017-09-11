<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
{
    $action_buttons[]=array(
        'label'=>$CI->lang->line("ACTION_NEW"),
        'href'=>site_url($CI->controller_url.'/index/add')
    );
}
if(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_DELETE"),
        'class'=>'button_jqx_action',
        'data-message-confirm'=>'Are you Sure to Delete?',
        'data-action-link'=>site_url($CI->controller_url.'/index/delete')
    );
}
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_DETAILS"),
    'class'=>'button_jqx_action',
    'data-action-link'=>site_url($CI->controller_url.'/index/details')
);
if(isset($CI->permissions['action4']) && ($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'class'=>'button_action_download',
        'data-title'=>"Print",
        'data-print'=>true
    );
}
if(isset($CI->permissions['action5']) && ($CI->permissions['action5']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_DOWNLOAD"),
        'class'=>'button_action_download',
        'data-title'=>"Download"
    );
}
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/list')

);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    if(isset($CI->permissions['action6']) && ($CI->permissions['action6']==1))
    {
        ?>
        <div class="col-xs-12" style="margin-bottom: 20px;">
            <div class="col-xs-12" style="margin-bottom: 20px;">
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="id"><?php echo $CI->lang->line('LABEL_ID'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="year"><?php echo $CI->lang->line('LABEL_YEAR'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="month"><?php echo $CI->lang->line('LABEL_MONTH'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="outlet_name"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_actual_sale">Actual <?php echo $CI->lang->line('LABEL_AMOUNT_SALE');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_actual_discount">Actual <?php echo $CI->lang->line('LABEL_AMOUNT_DISCOUNT');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_actual_payable">Actual <?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_commission_total">Total <?php echo $CI->lang->line('LABEL_AMOUNT_COMMISSION');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_payment_total">Total <?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_expense_total">Total <?php echo $CI->lang->line('LABEL_AMOUNT_EXPENSE');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_balance">Balance</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="remarks">Balance <?php echo $CI->lang->line('LABEL_REMARKS');?></label>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        var url="<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'year', type: 'string' },
                { name: 'month', type: 'string' },
                { name: 'outlet_name', type: 'string' },
                { name: 'amount_actual_sale', type: 'string' },
                { name: 'amount_actual_discount', type: 'string' },
                { name: 'amount_actual_payable', type: 'string' },
                { name: 'amount_commission_total', type: 'string' },
                { name: 'amount_payment_total', type: 'string' },
                { name: 'amount_expense_total', type: 'string' },
                { name: 'amount_balance', type: 'string' },
                { name: 'remarks', type: 'string' }
            ],
            id: 'id',
            type: 'POST',
            url: url
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,
                pageable: true,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pagesize:20,
                pagesizeoptions: ['20', '50', '100', '200','300','500'],
                selectionmode: 'singlerow',
                altrows: true,
                autoheight: true,
                enablebrowserselection:true,
                columnsreorder: true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_ID'); ?>', dataField: 'id',width:'50',cellsAlign:'right'},
                    { text: '<?php echo $CI->lang->line('LABEL_YEAR'); ?>', dataField: 'year',width:'50',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_MONTH'); ?>', dataField: 'month',width:'50',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>', dataField: 'outlet_name',width:'200',filtertype: 'list'},
                    { text: 'Actual <?php echo $CI->lang->line('LABEL_AMOUNT_SALE'); ?>', dataField: 'amount_actual_sale',width:'120',cellsAlign:'right'},
                    { text: 'Actual <?php echo $CI->lang->line('LABEL_AMOUNT_DISCOUNT'); ?>', dataField: 'amount_actual_discount',width:'120',cellsAlign:'right'},
                    { text: 'Actual <?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE'); ?>', dataField: 'amount_actual_payable',width:'120',cellsAlign:'right'},
                    { text: 'Total <?php echo $CI->lang->line('LABEL_AMOUNT_COMMISSION'); ?>', dataField: 'amount_commission_total',width:'120',cellsAlign:'right'},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT'); ?>', dataField: 'amount_payment_total',width:'120',cellsAlign:'right'},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_EXPENSE'); ?>', dataField: 'amount_expense_total',width:'120',cellsAlign:'right'},
                    { text: 'Balance', dataField: 'amount_balance',width:'120',cellsAlign:'right'},
                    { text: '<?php echo $CI->lang->line('LABEL_REMARKS'); ?>', dataField: 'remarks',cellsAlign:'right'}

                ]
            });
    });
</script>