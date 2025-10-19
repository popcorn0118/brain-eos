<?php
/**
 * EOS 模組
 * Plugin URI: https://www.eoscreative.co/
 * Description: EOS 模組
 * Version: 0.1
 * Author URI: https://www.eoscreative.co/
 * Author: Ted @ EOS Creative
 * Plugin Name: EOS 模組
  * @author  2023/11/20 Ted@Eoscreative
 */
if (!defined('WPINC')) {
    die;
}

function check_and_remove_role($role_name){
    // 使用 get_role 函数检查角色是否存在
    if( get_role( $role_name ) ) {
        // 如果角色存在，则使用 remove_role 函数删除它
        remove_role( $role_name );
    }
}

function reg_admin_role(){
	global $wp_roles;
	if(!isset($wp_roles)){
		$wp_roles = new WP_Roles();
	}
	$administrator = $wp_roles->get_role('administrator');
	check_and_remove_role('webadmin');
	$wp_roles->add_role('webadmin', '後台管理員', $administrator->capabilities);
	
	/*$webadmin = $wp_roles->get_role('webadmin');
	if(!$webadmin){
		$wp_roles->add_role('webadmin', '後台管理員', $administrator->capabilities);
	} else {
		$webadmin = $wp_roles->get_role('webadmin');
	}*/
	
	
	$webadmin = $wp_roles->get_role('webadmin');

	if($webadmin){
		//$capabilities = $webadmin->capabilities;
		$webadmin->remove_cap('switch_themes');
		$webadmin->remove_cap('edit_themes');
		$webadmin->remove_cap('activate_plugins');
		$webadmin->remove_cap('edit_plugins');
		$webadmin->remove_cap('install_plugins');
		$webadmin->remove_cap('update_themes');
		$webadmin->remove_cap('install_themes');
		$webadmin->remove_cap('update_core');
		$webadmin->remove_cap('edit_theme_options');
		$webadmin->remove_cap('delete_themes');
		$webadmin->remove_cap('manage_woocommerce');
		$webadmin->remove_cap('update_plugins');
		$webadmin->remove_cap('delete_plugins');
		$webadmin->remove_cap('manage_options');
		$webadmin->remove_cap('import');
		$webadmin->remove_cap('export');
		
		$capabilities = $webadmin->capabilities;
		//echo '<pre>' . print_r($capabilities, true) . '</pre>';
		// error_log('webadmin capabilities');
		// error_log(print_r($capabilities, true));
	}
}

register_activation_hook( __FILE__, function(){
	reg_admin_role();
});


class ClassEOS {
    public function __construct()
    {
        // add_action('init', [$this,'reg_admin_role']);
		add_action( 'admin_bar_menu', [$this,'remove_webadmin_menu_bar'], 999 );

		// 如果有安裝 woocommerce
		if(class_exists('WooCommerce')){
			add_filter( 'woocommerce_checkout_fields' , [$this,'custom_override_checkout_fields'] );
			add_filter( 'woocommerce_billing_fields' , [$this,'custom_override_billing_fields'] );
		}
    }
	
	public function reg_admin_role(){
		global $wp_roles;
		if(!isset($wp_roles)){
			$wp_roles = new WP_Roles();
		}
		$administrator = $wp_roles->get_role('administrator');
		check_and_remove_role('webadmin');
		$wp_roles->add_role('webadmin', '後台管理員', $administrator->capabilities);
		
		/*$webadmin = $wp_roles->get_role('webadmin');
		if(!$webadmin){
			$wp_roles->add_role('webadmin', '後台管理員', $administrator->capabilities);
		} else {
			$webadmin = $wp_roles->get_role('webadmin');
		}*/
		
		
		$webadmin = $wp_roles->get_role('webadmin');

		if($webadmin){
			//$capabilities = $webadmin->capabilities;
			$webadmin->remove_cap('switch_themes');
			$webadmin->remove_cap('edit_themes');
			$webadmin->remove_cap('activate_plugins');
			$webadmin->remove_cap('edit_plugins');
			$webadmin->remove_cap('install_plugins');
			$webadmin->remove_cap('update_themes');
			$webadmin->remove_cap('install_themes');
			$webadmin->remove_cap('update_core');
			$webadmin->remove_cap('edit_theme_options');
			$webadmin->remove_cap('delete_themes');
			$webadmin->remove_cap('manage_woocommerce');
			$webadmin->remove_cap('update_plugins');
			$webadmin->remove_cap('delete_plugins');
			$webadmin->remove_cap('manage_options');
			$webadmin->remove_cap('import');
			$webadmin->remove_cap('export');
			
			$capabilities = $webadmin->capabilities;
			//echo '<pre>' . print_r($capabilities, true) . '</pre>';
			// error_log('webadmin capabilities');
			// error_log(print_r($capabilities, true));
		}
	}

	public function remove_webadmin_menu_bar( $wp_admin_bar ) {		
		$wp_admin_bar->remove_node( 'wpvivid_admin_menu' );
		$wp_admin_bar->remove_node( 'wpvivid_admin_menu_backup' );
		$wp_admin_bar->remove_node( 'wpvivid_admin_menu_staging' );
		$wp_admin_bar->remove_node( 'wpvivid_admin_menu_export_import' );
		$wp_admin_bar->remove_node( 'updraft_admin_node' );
		$wp_admin_bar->remove_node( 'delete-cache' );

		// error_log('remove_webadmin_menu_bar');
		// error_log(print_r($wp_admin_bar, true));
	}

	public function custom_override_checkout_fields( $fields ) {
		// Remove Fields
		unset($fields['billing']['billing_company']);
		unset($fields['billing']['billing_address_2']);
		unset($fields['billing']['billing_last_name']);
		unset($fields['billing']['billing_city']);
		unset($fields['billing']['billing_state']);
	
	
		// Change Label
		$fields['billing']['billing_first_name']['label'] = '姓名';
		$fields['billing']['billing_email']['label'] = '聯絡信箱';
		$fields['billing']['billing_phone']['label'] = '聯絡電話';
		$fields['billing']['billing_address_1']['label'] = '聯絡電話';
		$fields['billing']['billing_postcode']['label'] = '郵遞區號3碼';
		$fields['billing']['billing_country']['label'] = '運送國家';
	
		unset($fields['order']['order_comments']['label']);
	
	
		// Change Placeholder
		$fields['billing']['billing_first_name']['placeholder'] = '請輸入姓名';
		$fields['billing']['billing_email']['placeholder'] = '請輸入Email';
		$fields['billing']['billing_phone']['placeholder'] = '請輸入聯絡電話';
		$fields['billing']['billing_address_1']['placeholder'] = '請輸入聯絡地址';
		$fields['billing']['billing_postcode']['placeholder'] = '請輸入郵遞區號3碼';
		$fields['order']['order_comments']['placeholder'] = '您的訂單的備註，例如: 運送時的特別註記。如需統編請在此備註。';
	
		//Change Class
		$fields["billing"]["billing_first_name"]["class"] = array('form-row-wide');
	
	
	   return $fields;
	}
	
	
	
	public function custom_override_billing_fields( $fields ) {
		// Remove Fields
		unset($fields['billing_company']);
		unset($fields['billing_address_2']);
		unset($fields['billing_last_name']);
		unset($fields['billing_city']);
		unset($fields['billing_state']);
	
		// Change Label
		$fields['billing_first_name']['label'] = '姓名';
		$fields['billing_email']['label'] = '聯絡信箱';
		$fields['billing_phone']['label'] = '聯絡電話';
		$fields['billing_address_1']['label'] = '聯絡電話';
		$fields['billing_postcode']['label'] = '郵遞區號3碼';
		$fields['billing_country']['label'] = '運送國家';
	
		// Change Placeholder
		$fields['billing_first_name']['placeholder'] = '請輸入姓名';
		$fields['billing_email']['placeholder'] = '請輸入Email';
		$fields['billing_phone']['placeholder'] = '請輸入聯絡電話';
		$fields['billing_address_1']['placeholder'] = '請輸入聯絡地址';
		$fields['billing_postcode']['placeholder'] = '請輸入郵遞區號3碼';
	
		return $fields;
	}
	
	
};


function run_class_eos() {
    $eos_erp = new ClassEOS();    
}
add_action('plugins_loaded', 'run_class_eos');
?>