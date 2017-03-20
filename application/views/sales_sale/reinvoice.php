<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_CLEAR"),
    'href'=>site_url($CI->controller_url.'/index/reinvoice')
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div id="container_sale">
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">
                    <?php echo $this->lang->line('LABEL_INVOICE_NO');?>
                </label>
            </div>
            <div class="col-sm-4 col-xs-4">
                <input type="text" id="invoice_no" class="form-control" value=""/>
            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button">
                    <button id="button_action_invoice_search" type="button" class="btn"><?php echo $this->lang->line('LABEL_SEARCH');?></button>
                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    <?php
    if(sizeof($varieties_info)>0)
    {
        ?>
        var varieties_info=JSON.parse('<?php echo json_encode($varieties_info);?>');
        <?php
    }
    else
    {
        ?>
        var varieties_info={};
        <?php
    }
    ?>
    var stock_info={};

    function search_invoice()
    {
        var invoice_no=$('#invoice_no').val();
        if(invoice_no.length>0)
        {
            $.ajax({
                url:'<?php echo site_url($CI->controller_url.'/index/search_reinvoice') ?>',
                type: 'POST',
                datatype: "JSON",
                data:{invoice_no:invoice_no},
                success: function (data, status)
                {
                    load_stocks(data);


                },
                error: function (xhr, desc, err)
                {
                    console.log("error");

                }
            });
        }
    }
    function calculate_total()
    {
        var total_quantity=0;
        $("#order_items_container tbody .quantity").each( function( index, element )
        {
            if($(this).val()==parseFloat($(this).val()))
            {
                total_quantity=total_quantity+parseFloat($(this).val());
            }
        });
        $('#total_quantity').html(number_format(total_quantity,'0','.',''));
        var total_weight=0;
        $("#order_items_container tbody .weight").each( function( index, element ){
            total_weight=total_weight+parseFloat($(this).html().replace(/,/g,''));
        });
        $('#total_weight').html(number_format(total_weight,3,'.',''));

        var total_price=0;
        $("#order_items_container tbody .price").each( function( index, element ){
            total_price=total_price+parseFloat($(this).html().replace(/,/g,''));
        });
        $('#total_price').html(number_format(total_price,2));
        var discount=parseFloat($('#discount').html());
        var total_discount=total_price*discount/100;

        $('#total_discount').html(number_format(total_discount,2));

        $('#total_payable').html(number_format(total_price-total_discount,2));
        var previously_paid=parseFloat($('#total_previous_paid').html().replace(/,/g,''));
        $('#total_payable').html(number_format(total_price-total_discount,2));
        $('#total_current_payable').html(number_format(total_price-total_discount-previously_paid,2));
        var total_paid=0;
        if($('#total_paid').val()==parseFloat($('#total_paid').val()))
        {
            total_paid=parseFloat($('#total_paid').val());
        }
        var total_change=total_paid+total_discount+previously_paid-total_price;
        $('#total_change').html(number_format(total_change,2));

    }
    function add_variety()
    {
        var variety_barcode=$('#variety_barcode').val();
        if(varieties_info[variety_barcode]===undefined)
        {
            animate_message("Invalid Barcode.");
        }
        else
        {
            if(($('#'+'quantity_'+variety_barcode).length)>0)
            {
                var cur_quantity=parseFloat($('#'+'quantity_'+variety_barcode).val());
                cur_quantity=cur_quantity+1;
                $('#'+'quantity_'+variety_barcode).val(cur_quantity);
                $('#'+'weight_'+variety_barcode).html(number_format(cur_quantity*varieties_info[variety_barcode]['pack_size']/1000,3,'.',''));
                $('#'+'price_'+variety_barcode).html(number_format(cur_quantity*varieties_info[variety_barcode]['price'],2));
            }
            else
            {
                var content_id='#system_content_add_more table tbody';
                $(content_id+' .crop_name').html(varieties_info[variety_barcode]['crop_name']);
                $(content_id+' .type_name').html(varieties_info[variety_barcode]['type_name']);
                $(content_id+' .variety_name').html(varieties_info[variety_barcode]['variety_name']);

                $(content_id+' .pack_size').html(varieties_info[variety_barcode]['pack_size']);
                $(content_id+' .pack_size').attr('id','pack_size_'+variety_barcode);

                $(content_id+' .pack_size_price').html(number_format(varieties_info[variety_barcode]['price'],2));
                $(content_id+' .pack_size_price').attr('id','pack_size_price_'+variety_barcode);
                $(content_id+' .current_stock').attr('id','current_stock_'+variety_barcode);
                if(stock_info[variety_barcode]===undefined)
                {
                    $(content_id+' .current_stock').html(0);
                }
                else
                {
                    $(content_id+' .current_stock').html(stock_info[variety_barcode]);
                }

                $(content_id+' .quantity').attr('id','quantity_'+variety_barcode);
                $(content_id+' .quantity').attr('name','varieties['+varieties_info[variety_barcode]['variety_id']+']['+varieties_info[variety_barcode]['pack_id']+'][quantity]');

                $(content_id+' .weight').html(number_format(varieties_info[variety_barcode]['pack_size']/1000,3,'.',''));
                $(content_id+' .weight').attr('id','weight_'+variety_barcode);

                $(content_id+' .price').html(number_format(varieties_info[variety_barcode]['price'],2));
                $(content_id+' .price').attr('id','price_'+variety_barcode);

                var html=$(content_id).html();
                $("#order_items_container tbody").append(html);
                $(content_id+' .pack_size').removeAttr('id');
                $(content_id+' .pack_size_price').removeAttr('id');
                $(content_id+' .quantity').removeAttr('id');
                $(content_id+' .quantity').removeAttr('name');
                $(content_id+' .weight').removeAttr('id');
                $(content_id+' .price').removeAttr('id');
            }
            calculate_total();
            $('#variety_barcode').val('');
        }
    }
    function load_stocks(data)
    {
        if(data['stock_info']!==undefined)
        {
            $.each(data['stock_info'],function(key,item)
            {
                stock_info[key]=item;
            });
        }
    }
    jQuery(document).ready(function()
    {

        $(document).off("click", "#button_action_invoice_search");
        $(document).on("click","#button_action_invoice_search",function()
        {
            search_invoice();

        });
        $(document).off("keypress", "#invoice_no");
        $(document).on("keypress","#invoice_no",function(event)
        {
            if(event.which == 13)
            {
                search_invoice();
            }


        });

        $(document).off("keypress", "#variety_barcode");
        $(document).on("keypress","#variety_barcode",function(event)
        {
            if(event.which == 13)
            {
                add_variety();
                return false;
            }

        });
        $(document).off("click", "#button_action_variety_add");
        $(document).on("click", "#button_action_variety_add", function(event)
        {
            add_variety();
        });



         // Delete more button
        $(document).off("click", ".system_button_add_delete");
        $(document).on("click", ".system_button_add_delete", function(event)
        {
         $(this).closest('tr').remove();
         calculate_total();

        });
        $(document).off("input", "#total_paid");
        $(document).on("input", "#total_paid", function(event)
        {
            calculate_total();
        });
        $(document).off("input", ".quantity");
        $(document).on("input", ".quantity", function(event)
        {
            var variety_barcode=$(this).attr('id');
            variety_barcode=variety_barcode.substr(9);
            //var pack_size=parseFloat($('#pack_size_'+variety_barcode).val());

            var quantity=parseFloat($(this).val());
            $('#'+'weight_'+variety_barcode).html(number_format(quantity*varieties_info[variety_barcode]['pack_size']/1000,3,'.',''));
            $('#'+'price_'+variety_barcode).html(number_format(quantity*varieties_info[variety_barcode]['price'],2));
            calculate_total();
        });
        $(document).off("submit", "#reinvoice_form");
        $(document).on("submit", "#reinvoice_form", function(event)
        {
            event.preventDefault();
            $.ajax({
                url: $(this).attr("action"),
                type: $(this).attr("method"),
                dataType: "JSON",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (data, status)
                {
                    if(data['status']==false)
                    {
                        if(data['new_stock']!==undefined)
                        {
                            $.each(data['new_stock'],function(key,item)
                            {
                                stock_info[key]=item;
                                $('#current_stock_'+key).html(item);
                            });
                        }
                    }

                },
                complete: function (xhr, status)
                {

                },
                error: function (xhr, desc, err)
                {
                }
            });
        });

    });
</script>