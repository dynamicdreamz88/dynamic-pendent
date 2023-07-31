<?php

get_header();




?>
<link rel="stylesheet" href="<?php echo esc_url(constant('NAMED_PENDANTS_URI') . 'css/main.css') ?>" type="text/css" />
<script>
    window.dynamicpendant = JSON.parse('<?php echo json_encode(array('ajax'=>admin_url('admin-ajax.php'))) ?>');
</script>
<script src="<?php echo esc_url(constant('NAMED_PENDANTS_URI') . 'js/main.js') ?>"></script>

<div class="width-100 dynamic-pendant-container">
    <div class="width-80">
        <img src="#" id="dynamic_pendant" width="1024" height="1024" style="display: none;"/>
        <img class="dynamic_loading" src="<?php echo esc_url(constant('NAMED_PENDANTS_URI') . 'img/loading.svg'); ?>">
    </div>
    <div class="width-20">


        <div class="input-field">
            <label>Name</label>
            <input type="text" name="dynamic_name" id="dynamic_name">
        </div>

        <div class="input-field">
            <label>Font</label>
            <span class="font-option">
                <label class="font-lable"> <input type="radio" name="dynamic_font" value="vegan" checked="checked">&nbsp;<span>Vegan</span></label>
            </span>
        </div>
    </div>
</div>
<?php

get_footer();