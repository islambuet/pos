<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div style="width: 320px;font-size: 10px;text-align: center; font-weight: bold;line-height: 10px;margin-left:-40px; ">
    <div style="width: 150px;float: left;">
        <div style="font-size:12px;line-height: 14px;"><?php echo $item['line1']; ?></div>
        <img src="<?php echo site_url('barcode_generator/get_image/'.($item['bar_code']));  ?>">
        <div><?php echo $item['bar_code'];?></div>
        <div><?php echo $item['variety_name'];?></div>
        <div>MRP(Tk.): <?php echo number_format($item['price'],2);?></div>
        <div><?php echo $item['outlet']; ?></div>
    </div>
    <div style="width: 150px;float: left;margin-left: 20px;">
        <div style="font-size:12px;line-height: 14px;"><?php echo $item['line1']; ?></div>
        <img src="<?php echo site_url('barcode_generator/get_image/'.($item['bar_code']));  ?>">
        <div><?php echo $item['bar_code'];?></div>
        <div><?php echo $item['variety_name'];?></div>
        <div>MRP(Tk.): <?php echo number_format($item['price'],2);?></div>
        <div><?php echo $item['outlet']; ?></div>
    </div>
</div>