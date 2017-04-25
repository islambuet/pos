<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_SAVE"),
    'id'=>'button_action_save',
    'data-form'=>'#save_form'
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_CLEAR"),
    'id'=>'button_action_clear',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_outlet');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $user_info['user_id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div style="overflow-x: auto;" class="row show-grid">
            <table class="table table-bordered" style="width: 600px;">
                <thead>
                <tr>
                    <th style="">Outlet</th>
                    <th>Commission %</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($outlets as $item)
                {
                    ?>
                    <tr>
                        <td>
                            <div class="checkbox" style="margin: 0;">
                                <label title="<?php echo $item['text']; ?>">
                                    <input type="checkbox" name="items[]" value="<?php echo $item['value']; ?>" <?php if(isset($assigned_outlets[$item['value']])){echo 'checked';} ?>><?php echo $item['text']; ?>
                                </label>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="commission[<?php echo $item['value']; ?>]" class="form-control" value="<?php if(isset($assigned_outlets[$item['value']])){echo $assigned_outlets[$item['value']]['commission'];}else{echo '0';} ?>"/>
                        </td>

                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="clearfix"></div>
</form>