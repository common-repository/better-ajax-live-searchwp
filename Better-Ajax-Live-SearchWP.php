<?php
/**
 * Plugin Name: Better Ajax Live SearchWP
 * Author: Anil Ankola
 * Version: 1.0
 * Description: A WordPress Better Ajax Live SearchWP plugin created by Anil Ankola for your website live search plugin show instant search results as soon as you type in the keyword without reloading the page.
 * Text Domain: better-ajax-live-searchwp
 */
if(!defined('ABSPATH')) exit; // Prevent Direct Browsing

if ( ! function_exists( 'better_ajax_live_searchwp_script_method' ) ) {
	function better_ajax_live_searchwp_script_method() {
		wp_enqueue_script('jquery' );
		wp_enqueue_style( 'custom-style-css', plugin_dir_url( __FILE__ ) . 'css/style.css',false , '1.0', 'all' );
		wp_enqueue_script( 'balswp-custom-js', plugin_dir_url( __FILE__). 'js/custom.js',false , '1.0', 'all'  );
	}
}
add_action( 'wp_footer', 'better_ajax_live_searchwp_script_method' );

if ( ! function_exists( 'better_ajax_live_searchwp_form' ) ) {
	function better_ajax_live_searchwp_form(){
		?>
	    <div class="search_bar">
            <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" autocomplete="off">
                <input type="text" name="s" placeholder="Search here..." id="keyword" class="input_search" onkeyup="fetch()">
            </form>
            <div class="search_result" id="datafetch">
                <ul>
                    <li>Please wait..</li>
                </ul>
            </div>
        </div>
        <?php
	}
}

// shortcode: [better_ajax_live_search]
add_shortcode( 'better_ajax_live_search', 'shortcode_better_ajax_live_searchwp_form' ); 
// The callback function
function shortcode_better_ajax_live_searchwp_form() {
    ob_start();

    //get saved settings
    $background_color= get_option('background_color');
    $title_color= get_option('title_color');
    $text_color= get_option('text_color');
    $button_color= get_option('button_color');
    $button_hover_text_color= get_option('button_hover_text_color');
    $separator_line_color= get_option('separator_line_color');
    //default color settings
    if($background_color == ''){ $background_color='#000000'; }
    if($title_color == ''){ $title_color='#ffffff'; }
    if($text_color == ''){ $text_color='#ffffff'; }
    if($button_color == ''){ $button_color='#ffffff'; }
    if($button_hover_text_color == ''){ $button_hover_text_color='#000000'; }
    if($separator_line_color == ''){ $separator_line_color='#ffffff'; }
    ?>
    <style>
    .Search-result-main { background: <?php echo esc_attr($background_color,'better-ajax-live-searchwp');?>; color: <?php echo esc_attr($text_color,'better-ajax-live-searchwp');?>; }
    .search-title{color: <?php echo esc_attr($title_color,'better-ajax-live-searchwp');?>;}
    a.search-Btn{color: <?php echo esc_attr($button_color,'better-ajax-live-searchwp');?>; border: 1px solid <?php echo esc_attr($button_color,'better-ajax-live-searchwp');?>;}
    a.search-Btn:hover{background: <?php echo esc_attr($button_color,'better-ajax-live-searchwp');?>; color: <?php echo esc_attr($button_hover_text_color,'better-ajax-live-searchwp');?>;}
    .Search-result-box{border-bottom: 1px solid <?php echo esc_attr($separator_line_color,'better-ajax-live-searchwp');?>;}
    </style>
    <?php
    better_ajax_live_searchwp_form();
    return ob_get_clean();
}

// Ajax call
add_action( 'wp_footer', 'better_ajax_live_searchwp_ajax_fetch' );
function better_ajax_live_searchwp_ajax_fetch() {
    ?>
    <script type="text/javascript">
        function fetch(){
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                data: { action: 'better_ajax_live_searchwp_data_fetch', keyword: jQuery('#keyword').val() },
                success: function(data) {
                    jQuery('#datafetch').html( data );
                }
            });
        }
    </script>
    <?php
}

// the ajax function
add_action('wp_ajax_better_ajax_live_searchwp_data_fetch' , 'better_ajax_live_searchwp_data_fetch');
add_action('wp_ajax_nopriv_better_ajax_live_searchwp_data_fetch','better_ajax_live_searchwp_data_fetch');
function better_ajax_live_searchwp_data_fetch(){

    $args = array(
       'public'   => true,
       //'_builtin' => false,
    );
    $output = 'names'; // names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'
    $post_types = get_post_types( $args, $output, $operator );

    $the_query = new WP_Query( 
        array( 
            'posts_per_page' => -1,
            //'post_status' => 'publish', 
            's' => sanitize_text_field( $_POST['keyword'] ), 
            'post_type' => $post_types
        ) 
    );
    if( $the_query->have_posts() ) :
        echo '<div class="Search-result-main">';
            while( $the_query->have_posts() ): $the_query->the_post();                
                $image_url = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID() ), 'full');?>
                <div class="Search-result-box">
                    <div class="Search-result-left">
                        <?php if(!empty($image_url[0])){?>
                            <div class="Search-result-img">
                                <img src="<?php echo esc_url($image_url[0],'better-ajax-live-searchwp');?>" alt="<?php the_title();?>" >
                            </div>
                        <?php } ?>
                    </div>
                    <div class="Search-result-right">
                        <div class="Search-result-content">
                            <p class="search-title"><strong><?php echo substr(get_the_title(),0,20);if(strlen(get_the_title())>=20){echo ' ...';}?></strong></p>
                            <p class="search-content"><?php echo substr(strip_tags(get_the_content()),0,50);if(strlen(get_the_content())>=50){echo ' ...';}?></p>
                            <a class="search-Btn" href="<?php the_permalink();?>">Read more</a>
                        </div>
                    </div>
                </div>
            <?php endwhile;
        echo '</div>';
        wp_reset_postdata();  
    endif;
    die();
}

// Create Menu Dashboard Sidepanel Start
function better_ajax_live_searchwp_create_menu(){
    add_menu_page('Better Ajax Live SearchWP', 'Better Ajax Live SearchWP', 'manage_options', 'betterajaxlivesearchwp', 'better_ajax_live_searchWP_page', 'dashicons-search' );
}
add_action( 'admin_menu', 'better_ajax_live_searchwp_create_menu' );

function better_ajax_live_searchWP_page(){
    global $wpdb;
    $message = '';
    if(isset($_POST['submit'])) 
    {
        if(!wp_verify_nonce('better_ajax_live_searchwp_settings_submit_nonce','better_ajax_live_searchwp_settings_submit'))
        {       
            $background_color= sanitize_text_field( $_POST['background_color'] );
            $title_color= sanitize_text_field( $_POST['title_color'] );          
            $text_color = sanitize_text_field( $_POST['text_color'] );
            $button_color= sanitize_text_field( $_POST['button_color'] );
            $button_hover_text_color = sanitize_text_field( $_POST['button_hover_text_color'] );
            $separator_line_color = sanitize_text_field( $_POST['separator_line_color'] );
            
            $saved= sanitize_text_field( $_POST['saved'] );
            
            if(isset($background_color) ) {
                update_option('background_color', $background_color);
            }
            if(isset($title_color)) {
                update_option('title_color', $title_color);
            }            
            if(isset($text_color) ) {
                update_option('text_color', $text_color);
            }
            if(isset($button_color) ) {
                update_option('button_color', $button_color);
            }  
            if(isset($button_hover_text_color) ) {
                update_option('button_hover_text_color', $button_hover_text_color);
            }
            if(isset($separator_line_color)){
                update_option('separator_line_color', $separator_line_color);
            }
            if($saved==true) {
                $message='saved';
            } 
        }
    }
    if ( $message == 'saved' ) {
        echo ' <div class="updated settings-error"><p><strong>Settings Saved.</strong></p></div>';
    }
	echo '<div class="wrap">
        <h2>Better Ajax Live SearchWP Settings</h2>
    </div>';
    ?>
    <div class="wrap">
        <form method="post" id="balswpSettingForm" action="">
            <table class="form-table">
                <h3>Use this shortcode [better_ajax_live_search] show the live search form.</h3>
                <tr valign="top">
                    <th scope="row">
                        <label><?php echo esc_html__('Background Color','better-ajax-live-searchwp');?></label>
                    </th>
                    <td>
                    <input type="text" name="background_color" value="<?php echo esc_html__(get_option('background_color'),'better-ajax-live-searchwp');?>" class="wp-color-picker-field" data-default-color="#000000" >
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php echo esc_html__('Title Color','better-ajax-live-searchwp');?></label>
                    </th>
                    <td>
                    <input type="text" name="title_color" value="<?php echo esc_html__(get_option('title_color'),'better-ajax-live-searchwp');?>" class="wp-color-picker-field" data-default-color="#ffffff" >
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php echo esc_html__('Text Color','better-ajax-live-searchwp');?></label>
                    </th>
                    <td>
                    <input type="text" name="text_color" value="<?php echo esc_html__(get_option('text_color'),'better-ajax-live-searchwp');?>" class="wp-color-picker-field" data-default-color="#ffffff" >
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php echo esc_html__('Button Color','better-ajax-live-searchwp');?></label>
                    </th>
                    <td>
                    <input type="text" name="button_color" value="<?php echo esc_html__(get_option('button_color'),'better-ajax-live-searchwp');?>" class="wp-color-picker-field" data-default-color="#ffffff">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php echo esc_html__('Button Hover Text Color','better-ajax-live-searchwp');?></label>
                    </th>
                    <td>
                    <input type="text" name="button_hover_text_color" value="<?php echo esc_html__(get_option('button_hover_text_color'),'better-ajax-live-searchwp');?>" class="wp-color-picker-field" data-default-color="#000000">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php echo esc_html__('Separator Line Color','better-ajax-live-searchwp');?></label>
                    </th>
                    <td>
                    <input type="text" name="separator_line_color" value="<?php echo esc_html__(get_option('separator_line_color'),'better-ajax-live-searchwp');?>" class="wp-color-picker-field" data-default-color="#ffffff" >
                    </td>
                </tr>                
            </table>
            <p class="submit">
                <input type="hidden" name="saved" value="saved"/>
                <input type="submit" name="submit" class="button-primary" value="Save Changes" />
                <?php wp_nonce_field( 'better_ajax_live_searchwp_settings_submit', 'better_ajax_live_searchwp_settings_submit_nonce' );?>
            </p>
        </form>
    </div>
    <?php
}

// enqueue color picker js
add_action( 'admin_enqueue_scripts', 'better_ajax_live_searchwp_enqueue_color_picker' );
function better_ajax_live_searchwp_enqueue_color_picker( ) {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker-script', plugins_url('js/color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}