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
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="crop_name"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="type_name"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="variety_name"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="pack_size"><?php echo $CI->lang->line('LABEL_PACK_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="starting_stock">Starting Stock</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="stock_current">Current Stock</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="stock_min">Minimum Stock</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="stock_max">Maximum Stock</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="stock_dif_min">Difference with Minimum Stock</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="stock_dif_max">Difference with Maximum Stock</label>
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

        var url = "<?php echo base_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_name', type: 'string' },
                { name: 'type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'pack_size', type: 'string' },
                { name: 'stock_current', type: 'string' },
                { name: 'stock_min', type: 'string' },
                { name: 'stock_max', type: 'string' },
                { name: 'stock_dif_min', type: 'string' },
                { name: 'stock_dif_max', type: 'string' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
           // console.log(defaultHtml);

            if ((record.stock_dif_min<0)&&(column=='variety_name'))
            {
                element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if ((record.stock_dif_max<0)&&(column=='variety_name'))
            {
                element.css({ 'background-color': '#00FF00','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }

            return element[0].outerHTML;

        };
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var aggregates=function (total, column, element, record)
        {
            if(record.crop_name=="Grand Total")
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
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'type_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Pack Size(gm)', dataField: 'pack_size',cellsalign: 'right',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Current Stock', dataField: 'stock_current',width:'100',cellsalign: 'right',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { text: 'minimum stock', dataField: 'stock_min',width:'100',cellsalign: 'right',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { text: 'maximum stock', dataField: 'stock_max',width:'150',cellsalign: 'right',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { text: 'Diff with Min', dataField: 'stock_dif_min',width:'150',cellsalign: 'right',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { text: 'Diff with Max', dataField: 'stock_dif_max',width:'150',cellsalign: 'right',cellsrenderer: cellsrenderer,rendered: tooltiprenderer}
                ]
            });
    });
</script>