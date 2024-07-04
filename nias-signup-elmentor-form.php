<?php
/**
 * Plugin Name: Elementor Form Create New User
 * Description: Create a new user using elementor pro form
 * Author:      علیرضا علی نیا
 * Author URI:  https://Nias.ir
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.0.0
 */

add_action( 'elementor_pro/forms/new_record',  'nias_elementor_form_create_new_user' , 10, 2 );

function nias_elementor_form_create_new_user($record,$ajax_handler) // creating function 
{
    $form_name = $record->get_form_settings('form_name');
    
    //Check that the form is the "Sign Up" if not - stop and return;
    if ('ثبت نام' !== $form_name) {
        return;
    }
    
    $form_data  = $record->get_formatted_data();
 
    $username   = $form_data['ایمیل'];
    $email      = $form_data['ایمیل']; 
    $password   = $form_data['پسورد']; 

	    // Check if the password is empty
    if (empty($password)) {
        $ajax_handler->add_error_message("پسورد نمی‌تواند خالی باشد.");
        $ajax_handler->is_success = false;
        return;
    }
    
    $user = wp_create_user($username,$password,$email); 

    if (is_wp_error($user)){ 
        $ajax_handler->add_error_message("خطا در ساخت کاربر جدید: ".$user->get_error_message()); 
        $ajax_handler->is_success = false;
        return;
    }

    // Assign Primary field value in the created user profile
    $first_name   =$form_data["نام"]; 
    $last_name    =$form_data["نام خانوادگی"];
    wp_update_user(array("ID"=>$user,"first_name"=>$first_name,"last_name"=>$last_name)); 

    // Assign Additional added field value in the created user profile
    $user_phone   =$form_data["Phone"]; 
    $user_bio     =$form_data["Bio"];
    update_user_meta($user, 'user_phone', $user_phone);    
    update_user_meta($user, 'user_bio', $user_bio); 

    /* Automatically log in the user and redirect the user to the home page */
    $creds= array(
        "user_login"=>$username,
        "user_password"=>$password,
        "remember"=>true
    );
    
    $signon = wp_signon($creds); 
    
    if ($signon) {
        $ajax_handler->add_response_data( 'redirect_url', get_home_url() );
    }
} 
// Disable admin topbar for all roles except admins:
add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
    show_admin_bar(false);
}}
