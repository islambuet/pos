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
            <label class="control-label pull-right">Report Type<span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <select id="report_name" class="form-control">
                <option value="outlet_invoice">Outlet Invoice Wise</option>
<!--                <option value="outlet_variety">Outlet Product wise</option>-->
                <option value="farmer_sale">Farmer Sales Report</option>
                <!--                <option value="customer_variety">Customer variety wise</option>-->
                <!--                <option value="variety_customer">Variety Customer wise</option>-->
            </select>
        </div>
    </div>
    <div id="report_search_container">
        <?php
        $CI->load->view($CI->controller_url."/search_outlet_invoice");
        ?>
    </div>
</div>
    <div class="clearfix"></div>


<div id="system_report_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(document).off("change", "#report_name");
        $(document).on("change","#report_name",function()
        {
            $("#report_search_container").html("");
            $.ajax({
                url:'<?php echo site_url($CI->controller_url.'/index/search') ?>',
                type: 'POST',
                datatype: "JSON",
                data:{report_name:$('#report_name').val()},
                success: function (data, status)
                {

                },
                error: function (xhr, desc, err)
                {


                }
            });

        });
        $(document).off("click", ".pop_up");
        $(document).on("click", ".pop_up", function(event)
        {
            var left=((($(window).width() - 550) / 2) +$(window).scrollLeft());
            var top=((($(window).height() - 550) / 2) +$(window).scrollTop());
            $("#popup_window").jqxWindow({position: { x: left, y: top  }});
            $.ajax(
                {
                    url: $(this).attr('data-action-link'),
                    type: 'POST',
                    datatype: "JSON",
                    success: function (data, status)
                    {
                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");
                    }
                });
            $("#popup_window").jqxWindow('open');
        });
    });
</script>
