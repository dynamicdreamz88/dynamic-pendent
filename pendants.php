<?php
/*
 * Plugin Name: Pendants
 * Version: 1.0.0
 * */

defined('NAMED_PENDANTS_DIR') || define('NAMED_PENDANTS_DIR', trailingslashit( __DIR__) );
defined('NAMED_PENDANTS_FONTS_DIR') || define('NAMED_PENDANTS_FONTS_DIR', trailingslashit( __DIR__).'fonts');
defined('NAMED_PENDANTS_URI') || define('NAMED_PENDANTS_URI', trailingslashit( plugin_dir_url(__FILE__) ));

class NAMED_PENDANTS {
    private static $instance = null;
    private static $images = null;
    public static function get_instance(){
        if( is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function images() {
        if(is_null(self::$images)) {
            require_once constant('NAMED_PENDANTS_DIR') . 'inc/Images.php';
            self::$images = \NAMED_PENDANTS\Images::get_instance();
        }
        return self::$images;
    }

    public function admin() {
        // admin facing scripts
        require_once trailingslashit( __DIR__ ) . 'template/admin.php';
    }

    public function generate_image($name,$font='vegan') {
        self::images()->generate_image($name,$font);
    }

    public function user() {
        // public or user facing scripts
    }
}

add_action('plugins_loaded',function () {

    if(!empty($_POST['_wpnonce']) and wp_verify_nonce($_POST['_wpnonce'],'generate_image_objects')) {
        \NAMED_PENDANTS::images()->create_objects(constant('NAMED_PENDANTS_FONTS_DIR'));
    }

    if(is_admin()) {
        add_action( 'admin_menu',function () {
            add_menu_page(
                __( 'Pendant'),
                'Pendant',
                'manage_options',
                'pendant',
                function () {
                    \NAMED_PENDANTS::get_instance()->admin();
                },
                'dashicons-superhero-alt',
                6
            );
        });
    } else {
        if(!empty($_GET['name']) and !empty(sanitize_text_field($_GET['name'])) and !empty(sanitize_text_field($_GET['font']))) {
            \NAMED_PENDANTS::get_instance()->generate_image(sanitize_text_field($_GET['name']),sanitize_text_field($_GET['font']));
        }
    }
});


function generate_image_objects(){
    if(!empty($_POST['_wpnonce']) and function_exists('wp_verify_nonce') and wp_verify_nonce($_POST['_wpnonce'], 'generate_image_objects')) {
        echo \NAMED_PENDANTS::images()->create_objects(constant('NAMED_PENDANTS_FONTS_DIR'));
    } else {
        echo false;
    }
    die();
}

add_action('wp_ajax_nopriv_generate_image_objects','generate_image_objects');
add_action('wp_ajax_generate_image_objects','generate_image_objects');


/* page template... */
add_filter('theme_page_templates', function ($templates) {
    $templates[plugin_dir_path(__FILE__) . 'template/named-pendant.php'] = __('Named Pendant', 'text-domain');
    return $templates;
});

add_filter('template_include', function ($template){

    if (is_page()) {
        $meta = get_post_meta(get_the_ID());

        if(!empty($meta['_wp_page_template']) and current($meta['_wp_page_template'])===plugin_dir_path(__FILE__) . 'template/named-pendant.php') {
            return plugin_dir_path(__FILE__) . 'template/named-pendant.php';
        }
        /*if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template) {
            $template = $meta['_wp_page_template'][0];
        }*/
    }
    return $template;
},99);


function dynamic_pendant_image() {
    $name = 'Donj';
    if(!empty($_POST['name'])) {
        $name = sanitize_text_field($_POST['name']);
    }

    $font = 'vegan';
    if(!empty($_POST['font'])) {
        $font = sanitize_text_field($_POST['font']);
    }

    \NAMED_PENDANTS::get_instance()->generate_image($name,$font);
    die();
}
if(!empty($_POST['action']) and $_POST['action']==='dynamic_pendant_image') {
    dynamic_pendant_image();
    /*add_action('wp_ajax_nopriv_dynamic_pendant_image', 'dynamic_pendant_image');
    add_action('wp_ajax_dynamic_pendant_image', 'dynamic_pendant_image');*/
    die();
}

add_action( 'rest_api_init', function() {
    register_rest_route( 'dynamic_pendant', '/get_image', array(
        'methods'   => \WP_REST_Server::CREATABLE,
        'callback'  => function(\WP_REST_Request $request){
            $params = $request->get_body_params();
            echo "<pre>";
            print_r($params);
            die;
        },
        'permission_callback' => function() { return ''; }
    ) );
} );



