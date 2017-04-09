<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
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
if(sizeof($action_buttons)>0)
{
    $CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
}

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
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" value="outlet_name"><?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="date_sale">Sale <?php echo $CI->lang->line('LABEL_DATE'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" value="date_canceled">Cancel <?php echo $CI->lang->line('LABEL_DATE'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="invoice_no"><?php echo $CI->lang->line('LABEL_INVOICE_NO'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="farmer_name"><?php echo $CI->lang->line('LABEL_CUSTOMER_NAME');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_total"><?php echo $CI->lang->line('LABEL_TOTAL');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_discount"><?php echo $CI->lang->line('LABEL_DISCOUNT');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_payable"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="amount_actual">Actual amount</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" value="invoice_old_id">Old Invoice No</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" value="invoice_new_id">New Invoice No</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" value="remarks"><?php echo $CI->lang->line('LABEL_REMARKS');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="details_button"><?php echo $CI->lang->line('ACTION_DETAILS');?></label>

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
        //var grand_total_color='#AEC2DD';
        var grand_total_color='#AEC2DD';

        var url = "<?php echo base_url($CI->controller_url.'/index/get_items_outlet_invoice');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'outlet_name', type: 'string' },
                { name: 'date_sale', type: 'string' },
                { name: 'date_canceled', type: 'string' },
                { name: 'invoice_no', type: 'string' },
                { name: 'farmer_name', type: 'string' },
                { name: 'amount_total', type: 'string' },
                { name: 'amount_discount', type: 'string' },
                { name: 'amount_payable', type: 'string' },
                { name: 'amount_actual', type: 'string' },
                { name: 'invoice_old_id', type: 'string' },
                { name: 'invoice_new_id', type: 'string' },
                { name: 'remarks', type: 'string' },
                { name: 'details_button', type: 'string' },
                { name: 'status', type: 'string' }
            ],
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
           // console.log(defaultHtml);
            if ((record.status=='In-Active')&& (column!="outlet_name")&& (column!="date_sale")&& (column!="invoice_no")&& (column!="details_button"))
            {
                element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if (record.date_sale=="Outlet Total")
            {
                if(!((column=='details_button')))
                {
                    element.css({ 'background-color': '#6CAB44','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.date_sale=="Grand Total")
            {

                element.css({ 'background-color': grand_total_color,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            if(column=='details_button')
            {
                if(record.details_button)
                {
                    element.html('<div><button class="btn btn-primary pop_up" data-action-link="<?php echo site_url($CI->controller_url.'/index/details_invoice'); ?>/'+record.id+'">View Details</button><a class="btn btn-primary" style="margin-left:5px;" href="<?php echo site_url('sales_sale/index/details'); ?>/'+record.id+'">Go To Invoice</button></div>');
                }
                else
                {
                    element.html('');
                }


            }
            return element[0].outerHTML;

        };
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var aggregates=function (total, column, element, record)
        {
            if(record.date_sale=="Grand Total")
            {
                //console.log(element);
                return record[element];

            }
            return total;
            //return grand_starting_stock;
        };
        var aggregatesrenderer=function (aggregates)
        {
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+grand_total_color+';">' +aggregates['total']+'</div>';

        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'350px',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                enabletooltips: true,
                showaggregates: true,
                showstatusbar: true,
                rowsheight: 40,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>',hidden:true, dataField: 'outlet_name',width:'200',cellsrenderer: cellsrenderer},
                    { text: 'Sale <?php echo $CI->lang->line('LABEL_DATE'); ?>', dataField: 'date_sale',width:'200',cellsrenderer: cellsrenderer},
                    { text: 'Cancel <?php echo $CI->lang->line('LABEL_DATE'); ?>',hidden:true, dataField: 'date_canceled',width:'200',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_INVOICE_NO'); ?>', dataField: 'invoice_no',width:'100',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CUSTOMER_NAME'); ?>', dataField: 'farmer_name',width:'200',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL'); ?>', dataField: 'amount_total',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_DISCOUNT'); ?>', dataField: 'amount_discount',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE'); ?>', dataField: 'amount_payable',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer},
                    { text: 'Actual amount', dataField: 'amount_actual',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: 'Old Invoice no',hidden:true, dataField: 'invoice_old_id',width:'100',cellsrenderer: cellsrenderer},
                    { text: 'New Invoice no',hidden:true, dataField: 'invoice_new_id',width:'100',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_REMARKS'); ?>',hidden:true, dataField: 'remarks',width:'200',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('ACTION_DETAILS'); ?>',dataField: 'details_button',width:'250',cellsrenderer:cellsrenderer}
                ]
            });
    });
</script>