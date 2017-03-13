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
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_DETAILS"),
    'class'=>'button_jqx_action',
    'data-action-link'=>site_url($CI->controller_url.'/index/details')
);
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array(
        'label'=>'Re Invoice',
        'href'=>site_url($CI->controller_url.'/index/re_invoice')
    );
}
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
    'href'=>site_url($CI->controller_url.'/index/list/short')

);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>'Load More..',
    'id'=>'button_jqx_load_more'
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
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="outlet_name"><?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="date_sale"><?php echo $CI->lang->line('LABEL_DATE'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="invoice_no"><?php echo $CI->lang->line('LABEL_INVOICE_NO'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="farmer_name"><?php echo $CI->lang->line('LABEL_CUSTOMER_NAME');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_total"><?php echo $CI->lang->line('LABEL_TOTAL');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_discount"><?php echo $CI->lang->line('LABEL_DISCOUNT');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_payable"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE');?></label>

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
        var url="<?php echo site_url($CI->controller_url.'/index/get_items/');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'outlet_name', type: 'string' },
                { name: 'date_sale', type: 'string' },
                { name: 'invoice_no', type: 'string' },
                { name: 'farmer_name', type: 'string' },
                { name: 'amount_total', type: 'string' },
                { name: 'amount_discount', type: 'string' },
                { name: 'amount_payable', type: 'string' },
                { name: 'status', type: 'string' }

            ],
            id: 'id',
            type: 'POST',

            url: url
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if ((record.status=='In-Active'))
            {
                element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            return element[0].outerHTML;

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
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>', dataField: 'outlet_name',width:'200',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE'); ?>', dataField: 'date_sale',width:'200',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_INVOICE_NO'); ?>', dataField: 'invoice_no',width:'100',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CUSTOMER_NAME'); ?>', dataField: 'farmer_name',width:'200',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL'); ?>', dataField: 'amount_total',cellsAlign:'right',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_DISCOUNT'); ?>', dataField: 'amount_discount',cellsAlign:'right',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE'); ?>', dataField: 'amount_payable',cellsAlign:'right'}
                ]
            });
    });
</script>