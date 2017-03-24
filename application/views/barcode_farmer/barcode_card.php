<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div style="width: 200px;font-size: 10px;text-align: center; font-weight: bold;line-height: 10px;margin-left:-40px; ">
    <div style="font-size:12px;line-height: 14px;"><?php echo $item['line1']; ?></div>
    <img src="<?php echo site_url('barcode_generator/get_image/farmer/'.($item['id']));  ?>">
    <div><?php echo System_helper::get_farmer_barcode($item['id']);?></div>
    <div><?php echo $item['name'];?></div>
    <div><?php echo $item['type_name'];?></div>
    <div>Mobile No: <?php echo $item['mobile_no'];?></div>
</div>
