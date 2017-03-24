<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />
    <div style="overflow-x: auto;" class="row show-grid" id="order_items_container">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_NAME'); ?></th>
                <th style="min-width: 100px;">Min <?php echo $CI->lang->line('LABEL_QUANTITY'); ?></th>
                <th style="min-width: 100px;">Max <?php echo $CI->lang->line('LABEL_QUANTITY'); ?></th>
            </tr>
            </thead>
            <tbody>
                <?php
                foreach($varieties as $row)
                {
                    ?>
                    <tr>
                        <td><?php echo $row['variety_name']; ?></td>
                        <td><?php echo $row['pack_name']; ?></td>
                        <td>
                            <input name="items[<?php echo $row['variety_id'] ?>][<?php echo $row['pack_size_id'] ?>][min]" type="text"class="form-control text-right float_type_positive" value="<?php if(isset($items[$row['variety_id']][$row['pack_size_id']])){echo $items[$row['variety_id']][$row['pack_size_id']]['quantity_min'];}else{echo '0';} ?>"/>
                        </td>
                        <td>
                            <input name="items[<?php echo $row['variety_id'] ?>][<?php echo $row['pack_size_id'] ?>][max]" type="text"class="form-control text-right float_type_positive" value="<?php if(isset($items[$row['variety_id']][$row['pack_size_id']])){echo $items[$row['variety_id']][$row['pack_size_id']]['quantity_max'];}else{echo '0';} ?>"/>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</form>
