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
    <form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/list');?>" method="post">
            <div class="row show-grid">
                <div class="col-xs-6">
                    <div class="row show-grid">
                        <div class="col-xs-6">
                            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_OUTLET_NAME');?><span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-xs-6">
                            <?php
                            if(sizeof($CI->user_outlets)>1)
                            {
                                ?>
                                <select id="customer_id" name="report[customer_id]" class="form-control">
                                    <?php
                                    foreach($CI->user_outlets as $row)
                                    {?>
                                        <option value="<?php echo $row['id']?>"><?php echo $row['name'];?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                <?php
                            }
                            else
                            {
                                ?>
                                <label class="control-label"><?php echo $CI->user_outlets[0]['name'];?></label>
                                <input id="customer_id" type="hidden" name="report[customer_id]" value="<?php echo $CI->user_outlets[0]['id'];?>">
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div id="container_farmer" style="display: none;">
                        <div class="row show-grid">
                            <div class="col-xs-6">
                                <label class="control-label pull-right">Farmer Type</label>
                            </div>
                            <div class="col-xs-6">
                                <select id="farmer_type" name="report[farmer_type]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                    <?php
                                    foreach($farmer_types as $row)
                                    {?>
                                        <option value="<?php echo $row['id']?>"><?php echo $row['name'];?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div style="display: none;" class="row show-grid" id="farmer_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CUSTOMER_NAME');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="farmer_id" name="report[farmer_id]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                </select>

                            </div>
                        </div>
                    </div>
                    <div id="container_product" style="display: none;">
                        <div style="" class="row show-grid" id="crop_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="crop_id" name="report[crop_id]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                </select>
                            </div>
                        </div>
                        <div style="display: none;" class="row show-grid" id="crop_type_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_TYPE');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="crop_type_id" name="report[crop_type_id]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                </select>
                            </div>
                        </div>
                        <div style="display: none;" class="row show-grid" id="variety_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_VARIETY_NAME');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="variety_id" name="report[variety_id]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="row show-grid">
                        <div class="col-xs-6">
                            <select id="report_name" name="report_name" class="form-control">
                                <option value="outlet_invoice">Outlet Invoice Wise</option>
                                <option value="farmer_sale">Farmer Sales Report</option>
                                <option value="variety_sale">Product wise</option>
                            </select>
                        </div>
                        <div class="col-xs-6">
                            <label class="control-label">Report Type<span style="color:#FF0000">*</span></label>
                        </div>
                    </div>
                    <div class="row show-grid">
                        <div class="col-xs-6">
                            <input type="text" name="report[date_start]" class="form-control date_large" value="<?php echo System_helper::display_date(time()); ?>">
                        </div>
                        <div class="col-xs-6">
                            <label class="control-label"><?php echo $this->lang->line('LABEL_DATE_START');?></label>
                        </div>
                    </div>
                    <div class="row show-grid">
                        <div class="col-xs-6">
                            <input type="text" name="report[date_end]" class="form-control date_large" value="<?php echo System_helper::display_date(time()); ?>">
                        </div>
                        <div class="col-xs-6">
                            <label class="control-label"><?php echo $this->lang->line('LABEL_DATE_END');?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">

                </div>
                <div class="col-xs-4">
                    <div class="action_button pull-right">
                        <button id="button_action_report" type="button" class="btn" data-form="#save_form"><?php echo $CI->lang->line("ACTION_REPORT"); ?></button>
                    </div>

                </div>
                <div class="col-xs-4">

                </div>
            </div>
    </form>
</div>
<div class="clearfix"></div>


<div id="system_report_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(".date_large").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "2015:+0"});

        $("#crop_id").html(get_dropdown_with_select(system_crops));
        $(document).off("change", "#crop_id");
        $(document).on("change","#crop_id",function()
        {
            $("#system_report_container").html("");
            $("#crop_type_id").val("");
            $("#variety_id").val("");

            var crop_id=$('#crop_id').val();
            if(crop_id>0)
            {
                $('#crop_type_id_container').show();
                $('#variety_id_container').hide();
                if(system_types[crop_id]!==undefined)
                {
                    $("#crop_type_id").html(get_dropdown_with_select(system_types[crop_id]));
                }
            }
            else
            {
                $('#crop_type_id_container').hide();
                $('#variety_id_container').hide();

            }
        });
        $(document).off("change", "#crop_type_id");
        $(document).on("change","#crop_type_id",function()
        {
            $("#system_report_container").html("");
            $("#variety_id").val("");
            var crop_type_id=$('#crop_type_id').val();
            if(crop_type_id>0)
            {
                $('#variety_id_container').show();
                if(system_varieties[crop_type_id]!==undefined)
                {
                    $("#variety_id").html(get_dropdown_with_select(system_varieties[crop_type_id]));
                }
            }
            else
            {
                $('#variety_id_container').hide();

            }
        });
        $(document).off("change", "#customer_id");
        $(document).on("change","#customer_id",function()
        {
            $("#system_report_container").html("");
            $("#farmer_id").val("");
            $("#farmer_type").val("");
            $('#farmer_id_container').hide();
        });
        $(document).off("change", "#farmer_type");
        $(document).on("change","#farmer_type",function()
        {
            $("#system_report_container").html("");
            $("#farmer_id").val("");
            var farmer_type=$('#farmer_type').val();
            var customer_id=$('#customer_id').val();
            if((customer_id>0)&&farmer_type>0)
            {
                $('#farmer_id_container').show();
                $.ajax({
                    url: '<?php echo site_url("common_controller/get_dropdown_farmers_by_cusfarmertypeid/");?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{customer_id:customer_id,farmer_type:farmer_type},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });

            }
            else
            {
                $('#farmer_id_container').hide();

            }
        });
        $(document).off("change", "#report_name");
        $(document).on("change","#report_name",function()
        {
            $("#system_report_container").html("");
            var report_name=$('#report_name').val();
            if(report_name=='outlet_invoice')
            {
                $('#container_farmer').hide();
                $('#container_product').hide();
            }
            else if(report_name=='farmer_sale')
            {
                $('#container_farmer').show();
                $('#container_product').hide();
            }
            else if(report_name=='variety_sale')
            {
                $('#container_farmer').show();
                $('#container_product').show();
            }

        });
        $(document).off("click", ".pop_up");
        $(document).on("click", ".pop_up", function(event)
        {
            $('#popup_content').html('');
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
