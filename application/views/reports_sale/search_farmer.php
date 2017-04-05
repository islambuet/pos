<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

?>
<form id="search_farmer_form" action="<?php echo site_url($CI->controller_url.'/index/search_farmer');?>" method="post">
    <input type="hidden" name="report_name" value="<?php echo $report_name;?>" />
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
                                <?php
                                $i=0;
                                foreach($CI->user_outlets as $row)
                                {
                                    $i++;
                                    ?>
                                    <div class="checkbox">
                                        <label title="<?php echo $row['name']; ?>">
                                            <input type="checkbox" name="customer_ids[]" value="<?php echo $row['id']; ?>" <?php if($i==1){echo 'checked';} ?>><?php echo $row['name']; ?>
                                        </label>
                                    </div>

                                <?php
                                }
                                ?>
                            <?php
                        }
                        else
                        {
                            ?>
                            <label class="control-label"><?php echo $CI->user_outlets[0]['name'];?></label>
                            <input type="hidden" name="customer_ids[]" value="<?php echo $CI->user_outlets[0]['id'];?>">
                            <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <div class="checkbox">
                            <label title="Select All">
                                <input type="checkbox" class="system_check_all" data-checkable=".farmer_type">SELECT ALL
                            </label>
                        </div>
                        <?php
                        $i=0;
                        foreach($farmer_types as $row)
                        {
                            $i++;
                            ?>
                            <div class="checkbox">
                                <label title="<?php echo $row['name']; ?>">
                                    <input class="farmer_type" type="checkbox" name="farmer_types[]" value="<?php echo $row['id']; ?>" <?php if($i==1){echo 'checked';} ?>><?php echo $row['name']; ?>
                                </label>
                            </div>

                        <?php
                        }
                        ?>

                    </div>
                    <div class="col-xs-6">
                        <label class="control-label">Farmer Type<span style="color:#FF0000">*</span></label>
                    </div>

                </div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_search_farmer" type="button" class="btn">Load Farmer</button>
                </div>
            </div>
            <div class="col-xs-4">

            </div>
        </div>
</form>
<div id="system_farmer_form_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(".date_large").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "2015:+0"});
        $(document).off("click", "#button_action_search_farmer");
        $(document).on("click", "#button_action_search_farmer", function(event)
        {
            $('#system_farmer_form_container').html('');
            $('#system_report_container').html('');
            $('#search_farmer_form').submit();

        });
    });
</script>
