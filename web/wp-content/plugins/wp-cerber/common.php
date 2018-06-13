<?php
/*
 	Copyright (C) 2015-18 CERBER TECH INC., Gregory Markov, https://wpcerber.com

    Licenced under the GNU GPL.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/*

*========================================================================*
|                                                                        |
|	       ATTENTION!  Do not change or edit this file!                  |
|                                                                        |
*========================================================================*

*/

// If this file is called directly, abort executing.
//if ( ! defined( 'WPINC' ) ) { exit; }

/**
 * Known WP scripts
 * @since 6.0
 *
 */
function cerber_get_wp_scripts(){
	return array( WP_LOGIN_SCRIPT, WP_REG_URI, WP_XMLRPC_SCRIPT, WP_TRACKBACK_SCRIPT, WP_PING_SCRIPT, WP_PING_SCRIPT);
}

/**
 * Return a link (full URL) to a Cerber admin settings page.
 * Add a particular tab and GET parameters if they are specified
 *
 * @param string $tab   Tab on the page
 * @param array $args   GET arguments to add to the URL
 *
 * @return string   Full URL
 */
function cerber_admin_link($tab = '', $args = array()){
	//return add_query_arg(array('record_id'=>$record_id,'mode'=>'view_record'),admin_url('admin.php?page=storage'));

	if ( empty( $args['page'] ) ) {
		if ( in_array( $tab, array( 'recaptcha', 'antispam' ) ) ) {
			$page = 'cerber-recaptcha';
			$tab  = null;
		}
		elseif ( in_array( $tab, array( 'imex', 'diagnostic', 'license' ) ) ) {
			$page = 'cerber-tools';
		}
		elseif ( in_array( $tab, array( 'traffic', 'ti_settings' ) ) ) {
			$page = 'cerber-traffic';
		}
		elseif ( in_array( $tab, array( 'geo' ) ) ) {
			$page = 'cerber-rules';
		}
		elseif ( in_array( $tab, array( 'scanner' ) ) ) {
			$page = 'cerber-integrity';
		}
		else {
			$page = 'cerber-security';
		}
	}
	else {
		$page = $args['page'];
		unset( $args['page'] );
	}

	if ( ! is_multisite() ) {
		$link = admin_url( 'admin.php?page=' . $page );
	}
	else {
		$link = network_admin_url( 'admin.php?page=' . $page );
	}

	if ( $tab ) {
		$link .= '&tab=' . $tab;
	}

	if ( $args ) {
		foreach ( $args as $arg => $value ) {
			$link .= '&' . $arg . '=' . urlencode( $value );
		}
	}

	return $link;
}
function cerber_activity_link($set = array()){
	$filter = '';
	foreach ( $set as $item ) {
		$filter .= '&filter_activity[]=' . $item;
	}
	return cerber_admin_link( 'activity' ) . $filter;
}
function cerber_traffic_link($set = array(), $button = true){
	$ret = cerber_admin_link('traffic', $set);
	if ($button){
		$ret = ' <a class="crb-button-tiny" href="'.$ret.'">'.__('Check for requests','wp-cerber').'</a>';
	}

	return $ret;
}

function cerber_get_login_url(){
	$ret = '';

	if ($path = crb_get_settings( 'loginpath' )) {
		$ret = get_home_url() . '/' . $path . '/';
	}

	return $ret;
}

function cerber_calculate_kpi($period = 1){
	global $wpdb;

	$period = absint( $period );
	if ( ! $period ) {
		$period = 1;
	}

	// TODO: Add spam performance as percentage Denied / Allowed comments

	$stamp = time() - $period * 24 * 3600;
	$in = implode( ',', crb_get_activity_set( 'malicious' ) );
	//$unique_ip = $wpdb->get_var('SELECT COUNT(DISTINCT ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN ('.$in.') AND stamp > '.$stamp);
	$unique_ip = cerber_db_get_var( 'SELECT COUNT(DISTINCT ip) FROM ' . CERBER_LOG_TABLE . ' WHERE activity IN (' . $in . ') AND stamp > ' . $stamp );

	$kpi_list = array(
		//array( __('Incidents detected','wp-cerber').'</a>', cerber_count_log( array( 16, 40, 50, 51, 52, 53, 54 ) ) ),
		array(
			__( 'Malicious activities mitigated', 'wp-cerber' ) . '</a>',
			cerber_count_log( crb_get_activity_set( 'malicious' ), $period )
		),
		array( __( 'Spam comments denied', 'wp-cerber' ), cerber_count_log( array( 16 ), $period ) ),
		array( __( 'Spam form submissions denied', 'wp-cerber' ), cerber_count_log( array( 17 ), $period ) ),
		array( __( 'Malicious IP addresses detected', 'wp-cerber' ), $unique_ip ),
		array( __( 'Lockouts occurred', 'wp-cerber' ), cerber_count_log( array( 10, 11 ), $period ) ),
		//array( __('Locked out IP now','wp-cerber'), $kpi_locknum ),
	);

	return $kpi_list;
}


function cerber_pb_get_devices($token = ''){

	$ret = array();

	if ( ! $token ) {
		if ( ! $token = crb_get_settings( 'pbtoken' ) ) {
			return false;
		}
	}

	$curl = @curl_init();
	if (!$curl) return false;

	$headers = array(
		'Authorization: Bearer ' . $token
	);

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.pushbullet.com/v2/devices',
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_TIMEOUT => 4, // including CURLOPT_CONNECTTIMEOUT
		CURLOPT_DNS_CACHE_TIMEOUT => 4 * 3600,
	));

	$result = @curl_exec($curl);
	$curl_error = curl_error($curl);
	curl_close($curl);

	$response = json_decode( $result, true );

	if ( JSON_ERROR_NONE == json_last_error() && isset( $response['devices'] ) ) {
		foreach ( $response['devices'] as $device ) {
			$ret[ $device['iden'] ] = $device['nickname'];
		}
	}
	else {
		if ($response['error']){
			$e = 'Pushbullet ' . $response['error']['message'];
		}
		elseif ($curl_error){
			$e = $curl_error;
		}
		else $e = 'Unknown cURL error';

		cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) .' '. $e);
	}

	return $ret;
}

/**
 * Send push message via Pushbullet
 *
 * @param $title
 * @param $body
 *
 * @return bool
 */
function cerber_pb_send($title, $body){

	if (!$body) return false;
	if ( ! $token = crb_get_settings( 'pbtoken' ) ) {
		return false;
	}

	$params = array('type' => 'note', 'title' => $title, 'body' => $body, 'sender_name' => 'WP Cerber');

	if ($device = crb_get_settings('pbdevice')) {
		if ($device && $device != 'all' && $device != 'N') $params['device_iden'] = $device;
	}

	$headers = array('Access-Token: '.$token,'Content-Type: application/json');

	$curl = @curl_init();
	if (!$curl) return false;

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.pushbullet.com/v2/pushes',
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_POSTFIELDS => json_encode($params),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_TIMEOUT => 4, // including CURLOPT_CONNECTTIMEOUT
		CURLOPT_DNS_CACHE_TIMEOUT => 4 * 3600,
	));

	$result = @curl_exec($curl);
	$curl_error = curl_error($curl);
	curl_close($curl);

	return $curl_error;
}
/**
 * Alert admin if something wrong with the website or settings
 */
function cerber_check_environment(){
	if ( '' === crb_get_settings( 'tienabled' ) ) {
		cerber_admin_notice('Warning: Traffic inspection is disabled');
	}
	if ( ! in_array( 'curl', get_loaded_extensions() ) ) {
		cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) . ' cURL PHP library is not enabled on your website.' );
	}
	else {
		$curl = @curl_init();
		if ( ! $curl && ( $err_msg = curl_error( $curl ) ) ) {
			cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) . ' ' . $err_msg );
		}
		curl_close( $curl );
	}
	if ( cerber_get_mode() != crb_get_settings( 'boot-mode' ) ) {
		cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) . ' ' . 'The plugin is initialized in a different mode that does not match the settings.' );
	}
}

/**
 * Health check up and self-repairing vital parts
 *
 */
function cerber_watchdog( $full = false ) {
	if ( $full ) {
		cerber_create_db( false );
		cerber_upgrade_db();

		return;
	}
	if ( ! cerber_is_table( CERBER_LOG_TABLE )
	     || ! cerber_is_table( CERBER_BLOCKS_TABLE )
	     || ! cerber_is_table( CERBER_LAB_IP_TABLE )
	) {
		cerber_create_db( false );
		cerber_upgrade_db();
	}
}

/**
 * Detect and return remote client IP address
 *
 * @since 6.0
 * @return string Valid IP address
 */
function cerber_get_remote_ip(){
	static $remote_ip;

	if ( isset( $remote_ip ) ) {
		return $remote_ip;
	}

	$options = crb_get_settings();

	if ( defined( 'CERBER_IP_KEY' ) ) {
		$remote_ip = filter_var( $_SERVER[ CERBER_IP_KEY ], FILTER_VALIDATE_IP );
	}
	elseif ( $options['proxy'] && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$list = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
		foreach ( $list as $maybe_ip ) {
			$remote_ip = filter_var( trim( $maybe_ip ), FILTER_VALIDATE_IP );
			if ( $remote_ip ) {
				break;
			}
		}
		if ( ! $remote_ip && isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$remote_ip = filter_var( $_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP );
		}
	} else {
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$remote_ip = $_SERVER['REMOTE_ADDR'];
		} elseif ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$remote_ip = $_SERVER['HTTP_X_REAL_IP'];
		} elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$remote_ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( isset( $_SERVER['SERVER_ADDR'] ) ) {
			$remote_ip = $_SERVER['SERVER_ADDR'];
		}
		$remote_ip = filter_var( $remote_ip, FILTER_VALIDATE_IP );
	}
	// No IP address was found? Roll back to localhost.
	if ( ! $remote_ip ) { // including WP-CLI, other way is: if defined('WP_CLI')
		$remote_ip = '127.0.0.1';
	}

	$remote_ip = cerber_short_ipv6( $remote_ip );

	return $remote_ip;
}


/**
 * Get ip_id for IP.
 * The ip_id can be safely used for array indexes and in any HTML code
 * @since 2.2
 *
 * @param $ip string IP address
 * @return string ID for given IP
 */
function cerber_get_id_ip( $ip ) {
	$ip_id = str_replace( '.', '-', $ip, $count );
	if ( ! $count ) {  // IPv6
		$ip_id = str_replace( ':', '_', $ip_id );
	}
	return $ip_id;
}
/**
 * Get IP from ip_id
 * @since 2.2
 *
 * @param $ip_id string ID for an IP
 *
 * @return string IP address for given ID
 */
function cerber_get_ip_id( $ip_id ) {
	$ip = str_replace( '-', '.', $ip_id, $count );
	if ( ! $count ) {  // IPv6
		$ip = str_replace( '_', ':', $ip );
	}
	return $ip;
}
/**
 * Check if given IP address is a valid single IP v4 address
 * 
 * @param $ip
 *
 * @return bool
 */
function cerber_is_ipv4($ip){
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return true;
	return false;
}
/**
 * Check if a given IP address belongs to a private network (private IP).
 * Universal: support IPv6 and IPv4.
 *
 * @param $ip string An IP address to check
 *
 * @return bool True if IP is in the private range, false otherwise
 */
function is_ip_private($ip) {

	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE ) ) {
		return true;
	}
	elseif ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE ) ) {
		return true;
	}

	return false;
}

/**
 * Expands shortened IPv6 to full IPv6 address
 *
 * @param $ip string IPv6 address
 *
 * @return string Full IPv6 address
 */
function cerber_expand_ipv6( $ip ) {
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
		return $ip;
	}
	$hex = unpack( "H*hex", inet_pton( $ip ) );

	//return substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);
	return implode( ':', str_split( $hex['hex'], 4 ) );
}

/**
 * Compress full IPv6 to shortened
 *
 * @param $ip string IPv6 address
 *
 * @return string Full IPv6 address
 */
function cerber_short_ipv6( $ip ) {
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
		return $ip;
	}

	return inet_ntop( inet_pton( $ip ) );
}

/**
 * Convert multilevel object or array of objects to associative array recursively
 *
 * @param $var object|array
 *
 * @return array result of conversion
 * @since 3.0
 */
function obj_to_arr_deep($var) {
	if (is_object($var)) {
		$var = get_object_vars($var);
	}
	if (is_array($var)) {
		return array_map(__FUNCTION__, $var);
	}
	else {
		return $var;
	}
}

/**
 * Search for a key in the given multidimensional array
 *
 * @param $array
 * @param $needle
 *
 * @return bool
 */
function recursive_search_key($array, $needle){
	foreach($array as $key => $value){
		if ((string)$key == (string)$needle){
			return true;
		}
		if(is_array($value)){
			$ret = recursive_search_key($value, $needle);
			if ($ret == true) return true;
		}
	}
	return false;
}

/**
 * Return true if a REST API URL has been requested
 *
 * @return bool
 * @since 3.0
 */
function cerber_is_rest_url(){
	static $ret = null;

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	if ( isset( $_REQUEST['rest_route'] ) ) {
		return true;
	}

	if ( isset( $ret ) ) {
		return $ret;
	}

	$ret = false;
	$uri = '/' . trim( $_SERVER['REQUEST_URI'], '/' ) . '/';

	if ( 0 === strpos( $uri, '/' . rest_get_url_prefix() . '/' ) ) {
		if ( 0 === strpos( get_home_url() . urldecode( $uri ), get_rest_url() ) ) {
			$ret = true;
		}
	}

	return $ret;
}

/**
 * Check if the current query is HTTP and GET method is being
 *
 * @return bool true if request method is GET
 */
function cerber_is_http_get(){
	if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' ){
		return true;
	}

	return false;
}

/**
 * Check if the current query is HTTP and POST method is being
 *
 * @return bool true if request method is GET
 */
function cerber_is_http_post(){
	if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' ){
		return true;
	}

	return false;
}

/**
 * More neat way to get $_GET field with no warnings
 *
 * @param $key
 *
 * @return bool|string
 */
function cerber_get_get($key){
	if ( isset( $_GET[ $key ] ) ) {
		return $_GET[ $key ];
	}

	return false;
}

/**
 * Is requested REST API namespace whitelisted
 *
 * @return bool
 */
function cerber_is_route_allowed() {

	$list = crb_get_settings( 'restwhite' );

	if ( ! is_array( $list ) || empty( $list ) ) {
		return false;
	}

	$rest_path = crb_get_rest_path();

	$namespace = substr( $rest_path, 0, strpos( $rest_path, '/' ) );

	foreach ( $list as $exception ) {
		if ($exception == $namespace) {
			return true;
		}
	}

	return false;
}
/**
 * Is requested REST API route blocked (not allowed)
 *
 * @return bool
 */
function cerber_is_route_blocked() {
	if ( crb_get_settings( 'stopenum' ) ) {
		$path = explode( '/', crb_get_rest_path() );
		if ( $path && count( $path ) > 2 && $path[0] == 'wp' && $path[2] == 'users' ) {
			return true;
		}
	}
	return false;
}

function crb_get_rest_path() {
	static $ret;
	if ( isset( $ret ) ) {
		return $ret;
	}

	if (isset($_REQUEST['rest_route'])){
		$ret = ltrim( $_REQUEST['rest_route'], '/' );
	}
	elseif ( cerber_is_permalink_enabled() ) {
		$pos = strlen( get_rest_url() );
		$ret = substr( get_home_url() . urldecode( $_SERVER['REQUEST_URI'] ), $pos );
		$ret = trim( $ret, '/' );
	}

	return $ret;
}

/**
 * Return the last element in the path of the requested URI.
 *
 * @param bool $check_php if true check if a php script has been requested
 *
 * @return bool|string
 */
function cerber_last_uri( $check_php = false ) {
	static $ret;

	if ( isset( $ret ) ) {
		return $ret;
	}

	$ret = strtolower( $_SERVER['REQUEST_URI'] );

	if ( $pos = strpos( $ret, '?' ) ) {
		$ret = substr( $ret, 0, $pos );
	}

	$ret = rtrim( $ret, '/' );
	$ret = substr( strrchr( $ret, '/' ), 1 );

	return $ret;
}

/**
 * Return the name of an executable script in the requested URI if it's present
 *
 * @return bool|string script name or false if executable script is not detected
 */
function cerber_get_uri_script() {
	static $ret;

	if ( isset( $ret ) ) {
		return $ret;
	}

	$last = cerber_last_uri();
	if ( cerber_detect_exec_extension( $last ) ) {
		$ret = $last;
	}
	else {
		$ret = false;
	}

	return $ret;
}

/**
 * Detects an executable extension in a filename.
 * Supports double and N fake extensions.
 *
 * @param $line string Filename
 * @param array $extra A list of additional extensions to detect
 *
 * @return bool|string An extension if it's found, false otherwise
 */
function cerber_detect_exec_extension( $line, $extra = array() ) {
	$executable = array( 'php', 'phtm', 'phtml', 'phps', 'shtm', 'shtml', 'jsp', 'asp', 'aspx', 'exe', 'com', 'cgi', 'pl' );

	if ( $extra ) {
		$executable = array_merge( $executable, $extra );
	}

	$line = trim( $line );
	$line = trim( $line, '/' );

	if ( ! strrpos( $line, '.' ) ) {
		return false;
	}

	$parts = explode('.', $line);
	array_shift($parts);

	// First and last are critical for most server environments
	$first_ext = array_shift($parts);
	$last_ext = array_pop($parts);

	if ( $first_ext ) {
		$first_ext = strtolower( $first_ext );
		if ( in_array( $first_ext, $executable ) ) {
			return $first_ext;
		}
		if ( preg_match( '/php\d+/', $first_ext ) ) {
			return $first_ext;
		}
	}

	if ( $last_ext ) {
		$last_ext = strtolower( $last_ext );
		if ( in_array( $last_ext, $executable ) ) {
			return $last_ext;
		}
		if ( preg_match( '/php\d+/', $last_ext ) ) {
			return $last_ext;
		}
	}

	return false;
}

/**
 * Return home with subfolders removed
 *
 * @return string
 */
function cerber_get_site_root(){
	static $home_url;

	if ( isset( $home_url ) ) {
		return $home_url;
	}

	$home_url = get_home_url();
	$p1 = strpos( $home_url, '//' );
	$p2 = strpos( $home_url, '/', $p1 + 2 );
	if ( $p2 !== false ) {
		$home_url = substr( $home_url, 0, $p2 );
	}

	return $home_url;
}

/**
 * Clean up the requested URI from parameters and extra slashes
 *
 * @return bool|mixed|string
 */
function cerber_purify_uri() {
	static $ret;

	if ( isset( $ret ) ) {
		return $ret;
	}

	$ret = $_SERVER['REQUEST_URI'];

	if ( $pos = strpos( $ret, '?' ) ) {
		$ret = substr( $ret, 0, $pos );
	}

	$ret = rtrim( $ret, '/' );
	$ret = preg_replace( '/\/+/', '/', $ret );

	return $ret;
}

/**
 * Remove extra slashes \ / from a script file name
 *
 * @return string|bool
 */
function cerber_script_filename() {
	return preg_replace('/[\/\\\\]+/','/',$_SERVER['SCRIPT_FILENAME']); // Windows server
}

function cerber_script_exists( $uri ) {
	$script_filename = cerber_script_filename();
	if ( is_multisite() && ! is_subdomain_install() ) {
		$path = explode( '/', $uri );
		if ( count( $path ) > 1 ) {
			$last = array_pop( $path );
			$virtual_sub_folder = array_pop( $path );
			$uri = implode( '/', $path ) . '/' . $last;
		}
	}
	if ( false === strrpos( $script_filename, $uri ) ) {
		return false;
	}

	return true;
}

/*
 * Sets of human readable labels for vary activity/logs events
 * @since 1.0
 *
 */
function cerber_get_labels($type = 'activity'){
	$labels = array();
	if ($type == 'activity') {

		// User actions
		$labels[1]=__('User created','wp-cerber');
		$labels[2]=__('User registered','wp-cerber');
		$labels[5]=__('Logged in','wp-cerber');
		$labels[6]=__('Logged out','wp-cerber');
		$labels[7]=__('Login failed','wp-cerber');

		// Cerber actions - IP specific - lockouts
		$labels[10]=__('IP blocked','wp-cerber');
		$labels[11]=__('Subnet blocked','wp-cerber');
		// Cerber actions - common
		$labels[12]=__('Citadel activated!','wp-cerber');
		$labels[16]=__('Spam comment denied','wp-cerber');
		$labels[17]=__('Spam form submission denied','wp-cerber');
		$labels[18]=__('Form submission denied','wp-cerber');
		$labels[19]=__('Comment denied','wp-cerber');

		// Cerber status // TODO: should be separated as another list ---------
		//$labels[13]=__('Locked out','wp-cerber');
		//$labels[14]=__('IP blacklisted','wp-cerber');
		// @since 4.9
		//$labels[15]=__('by Cerber Lab','wp-cerber');
		//$labels[15]=__('Malicious activity detected','wp-cerber');
		// --------------------------------------------------------------

		// Other actions
		$labels[20]=__('Password changed','wp-cerber');
		$labels[21]=__('Password reset requested','wp-cerber');

		$labels[40]=__('reCAPTCHA verification failed','wp-cerber');
		$labels[41]=__('reCAPTCHA settings are incorrect','wp-cerber');
		$labels[42]=__('Request to the Google reCAPTCHA service failed','wp-cerber');

		$labels[50]=__('Attempt to access prohibited URL','wp-cerber');
		$labels[51]=__('Attempt to log in with non-existent username','wp-cerber');
		$labels[52]=__('Attempt to log in with prohibited username','wp-cerber');
		// @since 4.9 // TODO 53 & 54 should be a cerber action?
		$labels[53]=__('Attempt to log in denied','wp-cerber');
		$labels[54]=__('Attempt to register denied','wp-cerber');
		$labels[55]=__('Probing for vulnerable PHP code','wp-cerber');
		$labels[56]=__('Attempt to upload executable file denied', 'wp-cerber' );
		$labels[57]=__('File upload denied', 'wp-cerber' );

		$labels[70]=__('Request to REST API denied','wp-cerber');
		$labels[71]=__('XML-RPC request denied','wp-cerber');

	}
	elseif ( $type == 'status' ) {
		$labels[11] = __( 'Bot detected', 'wp-cerber' );
		$labels[12] = __( 'Citadel mode is active', 'wp-cerber' );
		$labels[13] = __( 'Locked out', 'wp-cerber' );
		$labels[14] = __( 'IP blacklisted', 'wp-cerber' );
		// @since 4.9
		$labels[15] = __( 'Malicious activity detected', 'wp-cerber' );
		$labels[16] = __( 'Blocked by country rule', 'wp-cerber' );
		$labels[17] = __( 'Limit reached', 'wp-cerber' );
		$labels[18] = __( 'Multiple suspicious activities', 'wp-cerber' );
		$labels[19] = __( 'Denied', 'wp-cerber' ); // @since 6.7.5
	}

	return $labels;
}

function crb_get_activity_set($slice = 'malicious') {
	switch ( $slice ) {
		case 'malicious':
			return array( 10, 11, 16, 17, 40, 50, 51, 52, 53, 54, 55, 56 );
		case 'suspicious':
			return array( 10, 11, 16, 17, 20, 40, 50, 51, 52, 53, 54, 55, 56, 70, 71);
		case 'black':
			return array( 16, 17, 40, 50, 51, 52, 55, 56 );
	}

	return array();
}


function cerber_get_reason($id){
	$labels = array();
	$ret = __('Unknown','wp-cerber');
	$labels[1]=	__('Limit on login attempts is reached','wp-cerber');
	$labels[2]= __('Attempt to access', 'wp-cerber' );
	$labels[3]= __('Attempt to log in with non-existent username','wp-cerber');
	$labels[4]= __('Attempt to log in with prohibited username','wp-cerber');
	$labels[5]=	__('Limit on failed reCAPTCHA verifications is reached','wp-cerber');
	$labels[6]=	__('Bot activity is detected','wp-cerber');
	$labels[7]=	__('Multiple suspicious activities were detected','wp-cerber');
	$labels[8]=	__('Probing for vulnerable PHP code','wp-cerber');

	if (isset($labels[$id])) $ret = $labels[$id];
	return $ret;
}

function cerber_db_error_log($msg = null){
	global $wpdb;
	if (!$msg) $msg = array($wpdb->last_error, $wpdb->last_query, date('Y-m-d H:i:s'));
	$old = get_site_option( '_cerber_db_errors');
	if (!$old) $old = array();
	update_site_option( '_cerber_db_errors', array_merge($old,$msg));
}


/**
 * Save admin error message for further displaying
 *
 * @param string|array $msg
 */
function cerber_admin_notice( $msg ) {
	if ( ! $msg ) {
		return;
	}
	$notice = get_site_option( 'cerber_admin_notice', null);
	if ( ! $notice ) {
		$notice = array();
	}
	if ( is_array( $msg ) ) {
		$notice = array_merge( $notice, $msg );
	}
	else {
		$notice [] = $msg;
	}
	update_site_option( 'cerber_admin_notice', $notice );
}

/**
 * Save admin info message for further displaying
 *
 * @param string $msg
 */
function cerber_admin_message($msg){
	if (!$msg) return;
	update_site_option('cerber_admin_message', $msg);
}
/**
 * Return human readable "ago" time
 * 
 * @param $time integer Unix timestamp - time of an event
 *
 * @return string
 */
function cerber_ago_time($time){

	return sprintf( __( '%s ago' ), human_time_diff( $time ) );
}

/**
 * Format date according to user settings and timezone
 *
 * @param $timestamp int Unix timestamp
 *
 * @return string
 */
function cerber_date( $timestamp ) {
	$timestamp  = absint( $timestamp );
	$gmt_offset = get_option( 'gmt_offset' ) * 3600;
	if ( $df = crb_get_settings( 'dateformat' ) ) {
		return date_i18n( $df, $gmt_offset + $timestamp );
	}
	else {
		$tf = get_option( 'time_format' );
		$df = get_option( 'date_format' );

		return date_i18n( $df, $gmt_offset + $timestamp ) . ', ' . date_i18n( $tf, $gmt_offset + $timestamp );
	}
}

function cerber_percent($one,$two){
	if ($one == 0) {
		if ($two > 0) $ret = '100';
		else $ret = '0';
	}
	else {
		$ret = round (((($two - $one)/$one)) * 100);
	}
	$style='';
	if ($ret < 0) $style='color:#008000';
	elseif ($ret > 0) $style='color:#FF0000';
	if ($ret > 0)	$ret = '+'.$ret;
	return '<span style="'.$style.'">'.$ret.' %</span>';
}

/**
 * Return a user by login or email with automatic detection
 *
 * @param $login_email string login or email
 *
 * @return false|WP_User
 */
function cerber_get_user( $login_email ) {
	if ( is_email( $login_email ) ) {
		return get_user_by( 'email', $login_email );
	}

	return get_user_by( 'login', $login_email );
}

/**
 * Check if a DB table exists
 *
 * @param $table
 *
 * @return bool true if table exists in the DB
 */
function cerber_is_table( $table ) {
	global $wpdb;
	if ( ! $wpdb->get_row( "SHOW TABLES LIKE '" . $table . "'" ) ) {
		return false;
	}

	return true;
}

/**
 * Check if a column is defined in a table
 *
 * @param $table string DB table name
 * @param $column string Field name
 *
 * @return bool true if field exists in a table
 */
function cerber_is_column( $table, $column ) {
	global $wpdb;
	$result = $wpdb->get_row( 'SHOW FIELDS FROM ' . $table . " WHERE FIELD = '" . $column . "'" );
	if ( ! $result ) {
		return false;
	}

	return true;
}

/**
 * Check if a table has an index
 *
 * @param $table string DB table name
 * @param $key string Index name
 *
 * @return bool true if an index defined for a table
 */
function cerber_is_index( $table, $key ) {
	global $wpdb;
	$result = $wpdb->get_results( 'SHOW INDEX FROM ' . $table . " WHERE KEY_NAME = '" . $key . "'" );
	if ( ! $result ) {
		return false;
	}

	return true;
}

/**
 * Return reCAPTCHA language code for reCAPTCHA widget
 *
 * @return string
 */
function cerber_recaptcha_lang() {
	static $lang = '';
	if (!$lang) {
		$lang = get_bloginfo( 'language' );
		//$trans = array('en-US' => 'en', 'de-DE' => 'de');
		//if (isset($trans[$lang])) $lang = $trans[$lang];
		$lang = substr( $lang, 0, 2 );
	}

	return $lang;
}

/*
	Checks for a new version of WP Cerber and creates messages if needed
*/
function cerber_check_version() {
	$ret = false;
	if ( $updates = get_site_transient( 'update_plugins' ) ) {
		$key = cerber_plug_in();
		if ( isset( $updates->checked[ $key ] ) && isset( $updates->response[ $key ] ) ) {
			$old = $updates->checked[ $key ];
			$new = $updates->response[ $key ]->new_version;
			if ( 1 === version_compare( $new, $old ) ) { // current version is lower than latest
				$msg = __( 'New version is available', 'wp-cerber' ) . ' <span class="dashicons dashicons-arrow-right"></span>';
				if ( is_multisite() ) {
					$href = network_admin_url( 'plugins.php?plugin_status=upgrade' );
				}
				else {
					$href = admin_url( 'plugins.php?plugin_status=upgrade' );
				}
				cerber_admin_message( '<b>' . $msg . '</b> <a href="' . $href . '">' . sprintf( __( 'Update to version %s of WP Cerber', 'wp-cerber' ), $new ) . '</a>' );
				$ret = array( 'msg' => '<a href="' . $href . '">' . $msg . '</a>', 'ver' => $new );
			}
		}
	}
	return $ret;
}

/**
 * Detects known browsers/crawlers and platform in User Agent string
 *
 * @param $ua
 *
 * @return string Sanitized browser name and platform on success
 * @since 6.0
 */
function cerber_detect_browser( $ua ) {
	$ua  = trim( $ua );
	if ( empty( $ua ) ) {
		return __( 'Not specified', 'wp-cerber' );
	}
	if ( preg_match( '/\(compatible\;(.+)\)/i', $ua, $matches ) ) {
		$bot_info = explode( ';', $matches[1] );
		foreach ( $bot_info as $item ) {
			if ( stripos( $item, 'bot' )
			     || stripos( $item, 'crawler' )
			     || stripos( $item, 'spider' )
			     || stripos( $item, 'Yahoo! Slurp' )
			) {
				return htmlentities( $item );
			}
		}
	}
	elseif (0 === strpos( $ua, 'Wget/' )){
		return htmlentities( $ua );
	}

	$browsers = array( 'Firefox'   => 'Firefox',
	                   'OPR'     => 'Opera',
	                   'Opera'     => 'Opera',
	                   'YaBrowser' => 'Yandex Browser',
	                   'Trident'   => 'Internet Explorer',
	                   'IE'        => 'Internet Explorer',
	                   'Edge'      => 'Microsoft Edge',
	                   'Chrome'    => 'Chrome',
	                   'Safari'    => 'Safari',
	                   'Lynx'    => 'Lynx',
	);

	$systems  = array( 'Android' , 'Linux', 'Windows', 'iPhone', 'iPad', 'Macintosh', 'OpenBSD', 'Unix' );

	$b = '';
	foreach ( $browsers as $browser_id => $browser ) {
		if ( false !== strpos( $ua, $browser_id ) ) {
			$b = $browser;
			break;
		}
	}

	$s = '';
	foreach ( $systems as $system ) {
		if ( false !== strpos( $ua, $system ) ) {
			$s = $system;
			break;
		}
	}

	if ($b == 'Lynx' && !$s) {
		$s = 'Linux';
	}

	if ( $b && $s ) {
		$ret = $b . ' on ' . $s;
	}
	else {
		$ret = __( 'Unknown', 'wp-cerber' );
	}

	return htmlentities($ret);
}

/**
 * Is user agent string indicates bot (crawler)
 *
 * @param $ua
 *
 * @return integer 1 if ua string contains a bot definition, 0 otherwise
 * @since 6.0
 */
function cerber_is_crawler( $ua ) {
	if ( ! $ua ) {
		return 0;
	}
	$ua = strtolower( $ua );
	if ( preg_match( '/\(compatible\;(.+)\)/', $ua, $matches ) ) {
		$bot_info = explode( ';', $matches[1] );
		foreach ( $bot_info as $item ) {
			if ( strpos( $item, 'bot' )
			     || strpos( $item, 'crawler' )
			     || strpos( $item, 'spider' )
			     || strpos( $item, 'Yahoo! Slurp' )
			) {
				return 1;
			}
		}
	}
	elseif (0 === strpos( $ua, 'Wget/' )){
		return 1;
	}

	return 0;
}


/**
 * Natively escape an SQL query
 * Based on $wpdb->_real_escape()
 *
 * The reason: https://make.wordpress.org/core/2017/10/31/changed-behaviour-of-esc_sql-in-wordpress-4-8-3/
 *
 * @param $string
 *
 * @return string
 * @since 6.0
 */
function cerber_real_escape($string){
	global $wpdb;
	if ( $wpdb->use_mysqli ) {
		$escaped = mysqli_real_escape_string( $wpdb->dbh, $string );
	}
	else {
		$escaped = mysql_real_escape_string( $string, $wpdb->dbh );
	}

	return $escaped;
}

/**
 * Execute generic direct SQL query on the site DB
 *
 * The reason: https://make.wordpress.org/core/2017/10/31/changed-behaviour-of-esc_sql-in-wordpress-4-8-3/
 *
 * @param $query string An SQL query
 *
 * @return bool|mysqli_result|resource
 * @since 6.0
 */
function cerber_db_query( $query ) {
	global $cerber_db_errors;

	if ( ! isset( $cerber_db_errors ) ) {
		$cerber_db_errors = array();
	}

	$db = cerber_get_db();

	if ( ! $db ) {
		$cerber_db_errors[] = 'DB ERROR: No active DB handler';

		return false;
	}

	if ( $db->use_mysqli ) {
		$ret = mysqli_query( $db->dbh, $query );
		if ( ! $ret ) {
			$cerber_db_errors[] = mysqli_error( $db->dbh ) . ' for the query: ' . $query;
		}
	}
	else {
		$ret = mysql_query( $query, $db->dbh ); // For compatibility reason only
		if ( ! $ret ) {
			$cerber_db_errors[] = mysql_error( $db->dbh ) . ' for the query: ' . $query; // For compatibility reason only
		}
	}

	return $ret;
}

function cerber_db_get_results( $query, $type = MYSQLI_ASSOC ) {
	$db = cerber_get_db();

	$ret = array();

	if ( $result = cerber_db_query( $query ) ) {
		if ( $db->use_mysqli ) {
			//$ret = $result->fetch_all( $type );
			if ( $type == MYSQLI_ASSOC ) {
				while ( $row = mysqli_fetch_assoc( $result ) ) {
					$ret[] = $row;
				}
			}
			else {
				while ( $row = mysqli_fetch_row( $result ) ) {
					$ret[] = $row;
				}
			}
			mysqli_free_result( $result );
		}
		else {
			if ( $type == MYSQL_ASSOC ) {
				while ( $row = mysql_fetch_assoc( $result ) ) { // For compatibility reason only
					$ret[] = $row;
				}
			}
			else {
				while ( $row = mysql_fetch_row( $result ) ) { // For compatibility reason only
					$ret[] = $row;
				}
			}
		}
	}

	return $ret;
}

function cerber_db_get_row( $query ) {
	$db = cerber_get_db();

	if ( $result = cerber_db_query( $query ) ) {
		if ( $db->use_mysqli ) {
			$ret = mysqli_fetch_array( $result, MYSQLI_ASSOC );
		}
		else {
			$ret = mysql_fetch_array( $result, MYSQL_ASSOC );  // For compatibility reason only
		}
	}
	else {
		$ret = false;
	}

	return $ret;
}

function cerber_db_get_var( $query ) {
	$db = cerber_get_db();

	if ( $result = cerber_db_query( $query ) ) {
		if ( $db->use_mysqli ) {
			//$r = $result->fetch_row();
			$r = mysqli_fetch_row( $result );
		}
		else {
			$r = mysql_fetch_row( $result );  // For compatibility reason only
		}

		return $r[0];
	}

	return false;
}

function cerber_get_db() {
	global $wpdb, $cerber_db_errors;
	static $db;

	if ( ! isset( $db ) || ! is_object( $db ) ) {
		// Check for connected DB handler
		if ( ! is_object( $wpdb ) || empty( $wpdb->dbh ) ) {
			if ( ! $db = cerber_db_connect() ) {
				return false;
			}
		}
		else {
			$db = $wpdb;
		}
	}

	return $db;
}

/**
 * Create a WP DB handler
 *
 * @return bool|wpdb
 */
function cerber_db_connect() {
	if ( ! defined( 'CRB_ABSPATH' ) ) {
		define( 'CRB_ABSPATH', cerber_dirname( __FILE__, 4 ) );
	}
	$db_class  = CRB_ABSPATH . '/wp-includes/wp-db.php';
	$wp_config = CRB_ABSPATH . '/wp-config.php';
	if ( file_exists( $db_class ) && $config = file_get_contents( $wp_config ) ) {
		$config = str_replace( '<?php', '', $config );
		$config = str_replace( '?>', '', $config );
		ob_start();
		@eval( $config ); // This eval is OK. Getting site DB connection parameters.
		ob_end_clean();
		if ( defined( 'DB_USER' ) && defined( 'DB_PASSWORD' ) && defined( 'DB_NAME' ) && defined( 'DB_HOST' ) ) {
			require_once( $db_class );

			return new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
		}
	}

	return false;
}

/**
 * Remove comments from a given piece of code
 *
 * @param string $string
 *
 * @return mixed
 */
function cerber_remove_comments( $string = '' ) {
	return preg_replace( '/#.*/', '', preg_replace( '#//.*#', '', preg_replace( '#/\*(?:[^*]*(?:\*(?!/))*)*\*/#', '', ( $string ) ) ) );
}

/**
 * Set Cerber Groove to logged in user browser
 *
 * @param $expire
 */
function cerber_set_groove( $expire ) {
	if ( ! headers_sent() ) {
		setcookie( 'cerber_groove', md5( cerber_get_groove() ), $expire + 1, cerber_get_cookie_path() );

		$groove_x = cerber_get_groove_x();
		setcookie( 'cerber_groove_x_'.$groove_x[0], $groove_x[1], $expire + 1, cerber_get_cookie_path() );
	}
}

/*
	Get the special Cerber Sign for using with cookies
*/
function cerber_get_groove() {
	$groove = cerber_get_site_option( 'cerber-groove', false );

	if ( empty( $groove ) ) {
		//$groove = wp_generate_password( 16, false );
		$groove = substr( str_shuffle( '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' ), 0, 16 );
		update_site_option( 'cerber-groove', $groove );
	}

	return $groove;
}

/*
	Check if the special Cerber Sign valid
*/
function cerber_check_groove( $hash = '' ) {
	if ( ! $hash ) {
		if ( ! isset( $_COOKIE['cerber_groove'] ) ) {
			return false;
		}
		$hash = $_COOKIE['cerber_groove'];
	}
	$groove = cerber_get_groove();
	if ( $hash == md5( $groove ) ) {
		return true;
	}

	return false;
}

/*
	Get the special public Cerber Sign for using with cookies
*/
function cerber_get_groove_x( $regenerate = false ) {
	$groove_x = array();

	if ( ! $regenerate ) {
		$groove_x = cerber_get_site_option( 'cerber-groove-x' );
	}

	if ( $regenerate || empty( $groove_x ) ) {
		$groove_0 = substr( str_shuffle( '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' ), 0, rand( 24, 32 ) );
		$groove_1 = substr( str_shuffle( '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' ), 0, rand( 24, 32 ) );
		$groove_x = array( $groove_0, $groove_1 );
		update_site_option( 'cerber-groove-x', $groove_x );
		add_action('init', function () {
			cerber_htaccess_sync(); // keep the .htaccess rule is up to date
		});
	}

	return $groove_x;
}

function cerber_get_cookie_path(){
	if ( defined( 'COOKIEPATH' ) ) {
		return COOKIEPATH;
	}

	return '/';
}

/**
 * Synchronize plugin settings with rules in the .htaccess file
 *
 * @param array $settings
 *
 * @return bool
 */
function cerber_htaccess_sync( $settings = array() ) {

	if ( ! $settings ) {
		$settings = crb_get_settings();
	}

	$rules = array();

	if ( ! empty( $settings['adminphp'] ) ) {
		// https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2018-6389
		$groove_x = cerber_get_groove_x();
		$cookie = 'cerber_groove_x_'.$groove_x[0];
		$rules [] = '# Protection of admin scripts is enabled (CVE-2018-6389)';
		$rules [] = '<IfModule mod_rewrite.c>';
		$rules [] = 'RewriteEngine On';
		$rules [] = 'RewriteBase /';
		$rules [] = 'RewriteCond %{REQUEST_URI} ^(.*)wp-admin/load-scripts\.php$ [OR,NC]';
		$rules [] = 'RewriteCond %{REQUEST_URI} ^(.*)wp-admin/load-styles\.php$ [NC]';
		$rules [] = 'RewriteCond %{HTTP_COOKIE} !' . $cookie . '=' . $groove_x[1];
		$rules [] = 'RewriteRule (.*) - [R=403,L]';
		$rules [] = '</IfModule>';
	}

	// Update all rules in a section - adding/deleting
	return cerber_update_htaccess( CERBER_MARKER1, $rules );
}

/**
 * Remove Cerber rules from the .htaccess file
 *
 */
function cerber_htaccess_clean_up(){
	cerber_update_htaccess( CERBER_MARKER1, array() );
}

/**
 * Update the .htaccess file
 *
 * @param $marker string A section name
 * @param array $rules A set of rules (array of strings) for the section. If empty, the section will be cleaned.
 *
 * @return bool|string  True on success, string with error message on failure
 */
function cerber_update_htaccess($marker, $rules = array()){
	if ( ! $marker ) {
		return false;
	}
	if ( ! $htaccess_file = cerber_get_htaccess_file() ) {
		return 'ERROR: Unable to modify the .htaccess file';
	}
	require_once( ABSPATH . 'wp-admin/includes/misc.php' );
	if ( ! apache_mod_loaded( 'mod_rewrite', true ) ) {
		return 'ERROR: Apache mod_rewrite is not enabled';
	}

	$result = insert_with_markers( $htaccess_file, CERBER_MARKER1, $rules );

	if ( $result || $result === 0 ) {
		$result = true;
	}
	else {
		return 'ERROR: Unable to modify the .htaccess file';
	}

	return $result;
}

/**
 * Return .htaccess filename with full path
 *
 * @return bool|string full filename if the file can be written, false otherwise
 */
function cerber_get_htaccess_file() {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	$home_path = get_home_path();
	$file  = $home_path . '.htaccess';
	if ( ! is_writable( $file ) ) {
		// should we create the file?
		return false;
	}

	return $file;
}

/**
 * Check if the remote domain match mask
 *
 * @param $domain_mask array|string Mask(s) to check remote domain against
 *
 * @return bool True if hostname match at least one domain from the list
 */
function cerber_check_remote_domain( $domain_mask ) {

	$hostname = @gethostbyaddr( cerber_get_remote_ip() );

	if ( ! $hostname || filter_var( $hostname, FILTER_VALIDATE_IP ) ) {
		return false;
	}

	if ( ! is_array( $domain_mask ) ) {
		$domain_mask = array( $domain_mask );
	}

	foreach ( $domain_mask as $mask ) {

		if ( substr_count( $mask, '.' ) != substr_count( $hostname, '.' ) ) {
			continue;
		}

		$parts = array_reverse( explode( '.', $hostname ) );

		$ok = true;

		foreach ( array_reverse( explode( '.', $mask ) ) as $i => $item ) {
			if ( $item != '*' && $item != $parts[ $i ] ) {
				$ok = false;
				break;
			}
		}

		if ( $ok == true ) {
			return true;
		}

	}

	return false;
}

/**
 * Prepare files (install/deinstall) for different boot modes
 *
 * @param $mode int A plugin boot mode
 * @param $old_mode int
 *
 * @return bool|WP_Error
 * @since 6.3
 */
function cerber_set_boot_mode( $mode = null, $old_mode = null ) {
	if ( $mode === null ) {
		$mode = crb_get_settings( 'boot-mode' );
	}
	$source = dirname( cerber_plugin_file() ) . '/modules/aaa-wp-cerber.php';
	$target = WPMU_PLUGIN_DIR . '/aaa-wp-cerber.php';
	if ( $mode == 1 ) {
		if ( file_exists( $target ) ) {
			if ( sha1_file( $source, true ) == sha1_file( $target, true ) ) {
				return true;
			}
		}
		if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
			if ( ! mkdir( WPMU_PLUGIN_DIR, 0755, true ) ) {
				// TODO: try to set permissions for the parent folder
				return new WP_Error( 'cerber-boot', __( 'Unable to create the directory', 'wp-cerber' ) . ' ' . WPMU_PLUGIN_DIR );
			}
		}
		if ( ! copy( $source, $target ) ) {
			if ( ! wp_is_writable( WPMU_PLUGIN_DIR ) ) {
				return new WP_Error( 'cerber-boot', __( 'Destination folder access denied', 'wp-cerber' ) . ' ' . WPMU_PLUGIN_DIR );
			}
			elseif ( ! file_exists( $source ) ) {
				return new WP_Error( 'cerber-boot', __( 'File not found', 'wp-cerber' ) . ' ' . $source );
			}

			return new WP_Error( 'cerber-boot', __( 'Unable to copy the file', 'wp-cerber' ) . ' ' . $source . ' to the folder ' . WPMU_PLUGIN_DIR );
		}
	}
	else {
		if ( file_exists( $target ) ) {
			if ( ! unlink( $target ) ) {
				return new WP_Error( 'cerber-boot', __( 'Unable to delete the file', 'wp-cerber' ) . ' ' . $target );
			}
		}

		return true;
	}

	return true;
}

/**
 * How the plugin was loaded (initialized)
 *
 * @return int
 * @since 6.3
 */
function cerber_get_mode() {
	if ( function_exists( 'cerber_mode' ) && defined( 'CERBER_MODE' ) ) {
		return cerber_mode();
	}

	return 0;
}

function cerber_is_permalink_enabled() {
	static $ret;
	if ( isset( $ret ) ) {
		return $ret;
	}
	if ( get_option( 'permalink_structure' ) ) {
		$ret = true;
	}
	else {
		$ret = false;
	}

	return $ret;
}

/**
 * Given the path of a file or directory, this function
 * will return the parent directory's path that is given levels up
 *
 * @param string $path
 * @param integer $levels
 *
 * @return string Parent directory's path
 */
function cerber_dirname( $path, $levels = 0 ) {

	if ( PHP_VERSION_ID >= 70000 || $levels == 0 ) {
		return dirname( $path, $levels );
	}

	$ret = '.';

	$path = explode( DIRECTORY_SEPARATOR, str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $path ) );
	if ( 0 < ( count( $path ) - $levels ) ) {
		$path = array_slice( $path, 0, count( $path ) - $levels );
		$ret  = implode( DIRECTORY_SEPARATOR, $path );
	}

	return $ret;

}