<div class="wrap">
    <!--<form method="post">-->
        <?php //wp_nonce_field('generate_image_objects'); ?>
        <input type="submit" class="btn button primary" value="Generate Image Object Files" id="generate_image_object">
    <!--</form>-->

    <script>
        jQuery(document).ready(function () {
            window.sync_path = JSON.parse('<?php echo json_encode(\NAMED_PENDANTS::images()->get_image_directory_params(constant('NAMED_PENDANTS_FONTS_DIR'))); ?>');
            window.sync_index = 0;
            window.sync_method = function (index) {
                index = Number.parseInt(index);
                if( window.sync_path.hasOwnProperty(index) ) {
                    let path = window.sync_path[index];
                    jQuery.ajax({
                        'url':'<?php echo admin_url('admin-ajax.php'); ?>',
                        'method':'POST',
                        'data':{

                            'action' : 'generate_image_objects',
                            '_wpnonce' : '<?php echo wp_create_nonce('generate_image_objects'); ?>',
                            'font' : path.font,
                            'edge' : path.edge,
                        },
                        'success':function (){
                            window.sync_index++;
                            window.sync_method(window.sync_index);
                        }
                    });
                }
            }

            jQuery('#generate_image_object:not(.disabled)').click(function (){

                window.sync_index = 0;

                jQuery('#generate_image_object').addClass('disabled');
                window.sync_method(window.sync_index);
                jQuery('#generate_image_object').removeClass('disabled');
            })
        });
    </script>
</div>