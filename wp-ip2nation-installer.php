<?php
/*
Plugin Name: WP-IP2Nation-Installer
Plugin URI: http://www.daveligthart.com/plugins/wp-ip2nation-installer/
Description: Get country by ip. Installs the IP2Nation database from <a href="http://www.ip2nation.com/">ip2nation.com</a>.
Version: 1.1
Author: Dave Ligthart
Author URI: http://daveligthart.com
*/

/**
 * Usage example:
 *
 * include this code in your template:
 * <code>

   <?php
	if(function_exists('wp_ip2nation_getcountry')) {
		$nation = wp_ip2nation_getcountry();
		echo $nation->country;
		echo '-';
		echo $nation->code;
	}
	?>

 * </code>
 */

$wp_ip_to_nation_version = '1.1';

/**
 * Install IP 2 Nation database.
 * @see http://www.ip2nation.com
 * @access public
 * @version 0.1_11-11-08
 * @author dligthart <info@daveligthart.com>
 */
function wp_ip2nation_install() {
	global $wpdb;
	global $wp_version;
	global $wp_ip_to_nation_version;

	$table_name = 'ip2nation';

	$installed = ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name);

	// Table already created?
	if(!$installed){
		// Not, create table.
		ob_start();
	    include('ip2nation.sql'); //11-11-08 database version.
	    $sql = ob_get_contents();
	    ob_end_clean();

		if($wp_version >= 2.3) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		} else{
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		}

      	dbDelta($sql);
	}
	return $installed;
}

/**
 * Get country by ip.
 * @param String $ip optional
 * @access public
 * @author dligthart <info@daveligthart.com>
 * @return object
 * @version 0.1
 */
function wp_ip2nation_getcountry($ip = null) {
	global $wpdb;

	if(null == $ip) {
		$ip  = $_SERVER['REMOTE_ADDR'];
	}

	$sql = sprintf("" .
	 "SELECT
		 c.country,
		 c.code
	  FROM
		 ip2nationCountries c,
		 ip2nation i
	  WHERE
		 i.ip < INET_ATON('%s')
	  AND
		 c.code = i.country
	  ORDER BY
		 i.ip DESC
	  LIMIT 0,1", $ip);

	 $row = $wpdb->get_row($sql);

	 return $row;
}

/**
 * Assert that countrycode equals parameter code.
 * @param String $countrycode Country code
 * @access public
 * @version 0.1
 * @return equals boolean
 */
function wp_ip2nation_equals($countrycode = '') {
	$n = wp_ip2nation_getcountry();
	return ($n->code == $countrycode);
}

/**
 * Get Image Flag html.
 * @param string $ip optional
 * @return html flag
 * @access public
 * @version 0.1
 */
function wp_ip2nation_flag($ip = null){
	$n = wp_ip2nation_getcountry($ip);
	$code = $n->code;
	$country = $n->country;
	return '<img src="../wp-content/plugins/wp-ip2nation-installer/resources/images/flags/png/'. $code .'.png" alt="'.$country.'" />';
}

/**
 * Render dashboard.
 * @access private
 */
function wp_ip2nation_admin_menu() {
	if (function_exists('add_options_page')) {
			add_options_page(__('WP-IP2-Nation', 'wpip2nation'),
			 	__('WP-IP2-Nation', 'wpip2nation'),
				 10,
			 	basename(dirname(__FILE__)),
			 	'wp_ip2nation_render_view'
			 );
	}
}

/**
 * Render admin menu page.
 * @access private
 */
function wp_ip2nation_render_view() {
	echo '<p style="margin:20px;">';

	_e('WP-IP2Nation succesfully installed!: you are from ', 'wpip2nation');

	echo wp_ip2nation_flag();

	$n = wp_ip2nation_getcountry();

	echo '&nbsp;&nbsp;';

	echo $n->country;

	echo '</p>';

	echo '<p style="margin:20px;">';

	echo '<small>WP-IP2-Nation by <a href="http://www.daveligthart.com" target="_blank"/>daveligthart.com</a></small>';

	echo '</p>';
}

// Install ip2nation database.
register_activation_hook(__FILE__,'wp_ip2nation_install');

// Add dashboard info.
add_action('admin_menu','wp_ip2nation_admin_menu');

// Do not uninstall because the database might be shared between other plugins.
//register_deactivation_hook(__FILE__,'wp_ip2nation_deinstall');
?>