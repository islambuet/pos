<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div style="width: 300px;font-size: 10px;text-align: center; font-weight: bold;line-height: 12px">
    <div style="width: 150px;float: left;">
        <div style="font-size:12px;line-height: 14px;"><?php echo $item['line1']; ?></div>
        <img src="<?php echo site_url('barcode_generator/get_image/'.bin2hex($item['bar_code']));  ?>">
        <div><?php echo $item['variety_name'];?></div>
        <div>Pack Size: <?php echo $item['pack_size_name'];?>g</div>
        <div>MRP(Tk.): <?php echo number_format($item['price'],2);?></div>
        <div><?php echo $item['outlet_name']; ?></div>
    </div>
    <div style="width: 150px;float: left;">
        <div style="font-size:12px;line-height: 14px;"><?php echo $item['line1']; ?></div>
        <img src="<?php echo site_url('barcode_generator/get_image/'.bin2hex($item['bar_code']));  ?>">
        <div><?php echo $item['variety_name'];?></div>
        <div>Pack Size: <?php echo $item['pack_size_name'];?>g</div>
        <div>MRP(Tk.): <?php echo number_format($item['price'],2);?></div>
        <div><?php echo $item['outlet_name']; ?></div>
    </div>
</div>