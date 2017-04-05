<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/list');?>" method="post">
    <input type="hidden" name="report_name" value="<?php echo $report_name;?>" />
    <div class="row show-grid">
        <div class="col-xs-6">
            <div class="row show-grid">
                <div class="col-xs-6">
                    <label class="control-label pull-right">Select Farmer<span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-xs-6">
                    <div class="checkbox">
                        <label title="Select All">
                            <input type="checkbox" class="system_check_all" data-checkable=".farmers">SELECT ALL
                        </label>
                    </div>
                    <?php
                    $i=0;
                    foreach($farmers as $row)
                    {
                        $i++;
                        ?>
                        <div class="checkbox">
                            <label title="<?php echo $row['name']; ?>">
                                <input class="farmers" type="checkbox" name="report[farmers][]" value="<?php echo $row['id']; ?>" <?php if($i==1){echo 'checked';} ?>><?php echo $row['name']; ?>
                            </label>
                        </div>

                    <?php
                    }
                    ?>

                </div>
            </div>
        </div>
        <div class="col-xs-6">
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

<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(".date_large").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "2015:+0"});
        $("#crop_id").html(get_dropdown_with_select(system_crops));
        $(document).off("change", "#crop_id");
        $(document).on("change","#crop_id",function()
        {
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
    });
</script>
