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
</div>
<div class="clearfix"></div>


<div id="system_report_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(".date_large").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "2015:+0"});
        $(document).off("change", "#customer_id");
        $(document).on("change","#customer_id",function()
        {
            $("#system_report_container").html("");
        });
    });
</script>
