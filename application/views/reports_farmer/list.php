<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
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
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="sl_no"><?php echo $CI->lang->line('LABEL_SL_NO');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="barcode"><?php echo $CI->lang->line('LABEL_BARCODE'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="name"><?php echo $CI->lang->line('LABEL_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="mobile_no"><?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="farmer_type"><?php echo $CI->lang->line('LABEL_TYPE');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="status_card">Card Required?</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="address"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column" checked value="total_invoice">#Invoice</label>
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
        var url="<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'barcode', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'mobile_no', type: 'string' },
                { name: 'farmer_type', type: 'string' },
                { name: 'status_card', type: 'string' },
                { name: 'address', type: 'string' },
                { name: 'total_invoice', type: 'string'}
            ],
            id: 'id',
            type: 'POST',
            url: url,
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if(column=='details_button')
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                <?php
                if(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))
                {
                    ?>
                    element.html('<div><button class="btn btn-primary pop_up" data-action-link="<?php echo site_url($CI->controller_url.'/index/details_farmer'); ?>/'+record.id+'">View Bardcode</button></div>');
                    <?php
                }
                else
                {
                    ?>
                    element.html('<div></div>');
                    <?php
                }
                ?>

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
                enablebrowserselection:true,
                columnsreorder: true,
                rowsheight: 40,
                columns: [
                    {
                        text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',datafield: 'sl_no',width:'30', columntype: 'number',cellsalign: 'right', sortable: false, menu: false,
                        cellsrenderer: function(row, column, value, defaultHtml, columnSettings, record)
                        {
                            var element = $(defaultHtml);
                            element.html(value+1);
                            return element[0].outerHTML;
                        }
                    },
                    { text: '<?php echo $CI->lang->line('LABEL_BARCODE'); ?>', dataField: 'barcode',width:'100',cellsAlign:'right'},
                    { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'name',width:'300'},
                    { text: '<?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?>', dataField: 'mobile_no',width:'110'},
                    { text: '<?php echo $CI->lang->line('LABEL_TYPE'); ?>', dataField: 'farmer_type',filtertype: 'list',width:'110'},
                    { text: 'Card Required?', dataField: 'status_card',width:'100',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_ADDRESS'); ?>', dataField: 'address'},
                    { text: '#Invoice', dataField: 'total_invoice',width:'50',cellsAlign:'right',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('ACTION_DETAILS'); ?>',dataField: 'details_button',width:'150',cellsrenderer:cellsrenderer}

                ]
            });
    });
</script>