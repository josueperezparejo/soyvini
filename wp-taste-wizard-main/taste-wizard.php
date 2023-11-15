<?php
/*
 * Plugin Name: Taste Wizard
 * Version:           0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

include plugin_dir_path( __FILE__ ) . 'taste-wizard-model.php';

global $taste_wizard_db_version;
$taste_wizard_db_version = '1.1';

add_action('wp_enqueue_scripts','taste_wizard_init');

function taste_wizard_init() {
    wp_register_script( 'taste-wizard-js', plugins_url( '/js/taste-wizard.js', __FILE__ ), array('jquery'));
    wp_enqueue_script( 'taste-wizard-js');
    wp_enqueue_style( 'taste-wizard-style', plugins_url( '/css/taste-wizard.css', __FILE__) );
}

add_shortcode('taste-wizard-form', 'taste_wizard_form');

function taste_wizard_form($atts) {
    global $error;
    $installed_db_ver = get_option( "taste_wizard_db_version" );
    if ( isset($_POST['taste_wizard_form']) && $_POST['taste_wizard_form'] == '1' ) {
        // Verify nonce for security
        if ( isset($_POST['taste_wizard_wpnounce']) && wp_verify_nonce($_POST['taste_wizard_wpnounce'], 'taste_wizard_form_action') ) {
            $taste_wizard = new TasteWizardModel($_POST, get_current_user_id());
            
            $taste_wizard->save();
            if (!$taste_wizard->errors->has_errors()) {
                $file_path = plugin_dir_path( __FILE__ ) . 'templates/taste-wizard-results.php';
                ob_start();
                require $file_path;
                $includedContent = ob_get_clean();
                return $includedContent;
            }
        } else {
            wp_redirect( home_url() );
            return '';
        }
    } else {
        if (is_user_logged_in()) {
            $taste_wizard = TasteWizardModel::fetchUserWizard(get_current_user_id());
        } else {
            $taste_wizard = new TasteWizardModel(
                array(
                    "data" => array(
                        "email" => '',
                        "first_name" => '',
                        "last_name"=> '',
                    ),
                )
            );
        }
    }

    $errors = $taste_wizard->errors->get_error_messages();
    $file_path = plugin_dir_path( __FILE__ ) . 'templates/taste-wizard.php';
    ob_start();
    require $file_path;
    $includedContent = ob_get_clean();
    return $includedContent;
}

function taste_wizard_save_table() {
    global $wpdb;
    global $taste_wizard_db_version;
    $installed_db_ver = get_option( "taste_wizard_db_version" );
    $responses_table_name = $wpdb->prefix . 'taste_wizard_user_responses';
    
    if ( $installed_db_ver != $taste_wizard_db_version ) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = [
                "CREATE TABLE $responses_table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                time datetime NOT NULL,
                question_key VARCHAR(30) NOT NULL,
                user_id bigint(20) NOT NULL,
                question_answer VARCHAR(50) NOT NULL,
                answer_order TINYINT UNSIGNED NOT NULL,
                UNIQUE (user_id, question_key, question_answer),
                PRIMARY KEY  (id)
            ) $charset_collate;"
        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $sql );

        add_option('taste_wizard_db_version', $taste_wizard_db_version );
    }
}

add_action( 'plugins_loaded', 'taste_wizard_save_table' );
register_activation_hook( __FILE__, 'taste_wizard_save_table' );
