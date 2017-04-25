<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div style="width: 150px;font-size: 10px;text-align: center; font-weight: bold;line-height: 10px;margin-left:-40px; ">
    <img src="<?php echo site_url('barcode_generator/get_image/variety/'.($item['bar_code']));  ?>">
    <div><?php echo $item['bar_code'].' ('.$item['pack_size_name'].' g)';?></div>
</div>