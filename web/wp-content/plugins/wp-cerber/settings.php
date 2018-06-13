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
if ( ! defined( 'WPINC' ) ) { exit; }

define('CERBER_OPT','cerber-main');
define('CERBER_OPT_H','cerber-hardening');
define('CERBER_OPT_U','cerber-users');
define('CERBER_OPT_C','cerber-recaptcha');
define('CERBER_OPT_N','cerber-notifications');
define('CERBER_OPT_T','cerber-traffic');

/**
 * A set of Cerber setting (WP options)
 *
 * @return array
 */

function cerber_get_setting_list() {
	return array( CERBER_OPT, CERBER_OPT_H, CERBER_OPT_U, CERBER_OPT_C, CERBER_OPT_N, CERBER_OPT_T );
}

/*
	WP Settings API
*/
add_action('admin_init', 'cerber_settings_init');
function cerber_settings_init(){

	if ( ! cerber_is_admin_page( false ) && ! strpos( $_SERVER['REQUEST_URI'], '/options.php' ) ) {
		return;
	}

	// Main Settings tab ---------------------------------------------------------------------

	$tab='main'; // 'cerber-main' settings
	register_setting( 'cerberus-'.$tab, 'cerber-'.$tab );

	add_settings_section( 'boot', __( 'Plugin initialization', 'wp-cerber' ), 'cerber_sapi_section', 'cerber-' . $tab );
	add_settings_field('boot-mode',__('Load security engine','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'boot',array('group'=>$tab, 'option'=>'boot-mode', 'type'=>'select', 'set' => array(__('Legacy mode','wp-cerber'), __('Standard mode','wp-cerber'))));

	add_settings_section('cerber', __('Limit login attempts','wp-cerber'), 'cerber_sapi_section', 'cerber-' . $tab);
	add_settings_field('attempts',__('Attempts','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'cerber',array('group'=>$tab,'option'=>'attempts','type'=>'attempts'));
	add_settings_field('lockout',__('Lockout duration','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'cerber',array('group'=>$tab,'option'=>'lockout','type'=>'text','label'=>__('minutes','wp-cerber'),'size'=>3));
	add_settings_field('aggressive',__('Aggressive lockout','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'cerber',array('group'=>$tab,'option'=>'aggressive','type'=>'aggressive'));
	add_settings_field('limitwhite',__('White IP Access List','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'cerber',array('group'=>$tab,'option'=>'limitwhite','type'=>'checkbox','label'=>__('Apply limit login rules to IP addresses in the White IP Access List','wp-cerber')));
	add_settings_field('notify',__('Notifications','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'cerber',array('group'=>$tab,'type'=>'notify','option'=>'notify'));
	add_settings_field('proxy',__('Site connection','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'cerber',array('group'=>$tab,'option'=>'proxy','type'=>'checkbox','label'=>__('My site is behind a reverse proxy','wp-cerber')));

	add_settings_section('proactive', __('Proactive security rules','wp-cerber'), 'cerber_sapi_section', 'cerber-' . $tab);
	add_settings_field('subnet',__('Block subnet','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'proactive',array('group'=>$tab,'option'=>'subnet','type'=>'checkbox','label'=>__('Always block entire subnet Class C of intruders IP','wp-cerber')));
	add_settings_field('nonusers',__('Non-existent users','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'proactive',array('group'=>$tab,'option'=>'nonusers','type'=>'checkbox','label'=>__('Immediately block IP when attempting to login with a non-existent username','wp-cerber')));
	add_settings_field('noredirect',__('Redirect dashboard requests','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'proactive',array('group'=>$tab,'option'=>'noredirect','type'=>'checkbox','label'=>__('Disable automatic redirecting to the login page when /wp-admin/ is requested by an unauthorized request','wp-cerber')));
	add_settings_field('wplogin',__('Request wp-login.php','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'proactive',array('group'=>$tab,'option'=>'wplogin','type'=>'checkbox','label'=>__('Immediately block IP after any request to wp-login.php','wp-cerber')));
	add_settings_field('page404',__('Display 404 page','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'proactive',array('group'=>$tab, 'option'=>'page404', 'type'=>'select', 'set' => array(__('Use 404 template from the active theme','wp-cerber'), __('Display simple 404 page','wp-cerber'))));

	add_settings_section('custom', __('Custom login page','wp-cerber'), 'cerber_sapi_section', 'cerber-' . $tab);
	add_settings_field( 'loginpath', __( 'Custom login URL', 'wp-cerber' ), 'cerber_field_show', 'cerber-' . $tab, 'custom', array(
		'group'   => $tab,
		'setting' => 'loginpath',
		'type'    => 'text',
		'pattern' => '[a-zA-Z0-9\-_]{1,100}',
		'title'   => __( 'Custom login URL may contain only letters, numbers, dashes and underscores', 'wp-cerber' ),
		'label'   => __( 'must not overlap with the existing pages or posts slug', 'wp-cerber' )
	) );
	add_settings_field('loginnowp',__('Disable wp-login.php','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'custom',array('group'=>$tab,'option'=>'loginnowp','type'=>'checkbox','label'=>__('Block direct access to wp-login.php and return HTTP 404 Not Found Error','wp-cerber')));

	add_settings_section('citadel', __('Citadel mode','wp-cerber'), 'cerber_sapi_section', 'cerber-' . $tab);
	add_settings_field('citadel',__('Threshold','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'citadel',array('group'=>$tab,'option'=>'citadel','type'=>'citadel'));
	add_settings_field('ciduration',__('Duration','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'citadel',array('group'=>$tab,'option'=>'ciduration','type'=>'text','label'=>__('minutes','wp-cerber'),'size'=>3));
	add_settings_field('cinotify',__('Notifications','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'citadel',array('group'=>$tab,'option'=>'cinotify','type'=>'checkbox','label'=>__('Send notification to admin email','wp-cerber').' [ <a href="'.wp_nonce_url(add_query_arg(array('testnotify'=>'citadel', 'settings-updated' => 0)),'control','cerber_nonce').'">'.__('Click to send test','wp-cerber').'</a> ]'));

	add_settings_section('activity', __('Activity','wp-cerber'), 'cerber_sapi_section', 'cerber-' . $tab);
	add_settings_field('keeplog',__('Keep records for','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'activity',array('group'=>$tab,'option'=>'keeplog','type'=>'text','label'=>__('days','wp-cerber'),'size'=>3));
	add_settings_field('cerberlab',__('Cerber Lab connection','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'activity',array('group'=>$tab,'option'=>'cerberlab','type'=>'checkbox','label'=>__('Send malicious IP addresses to the Cerber Lab','wp-cerber').' <a target="_blank" href="http://wpcerber.com/cerber-laboratory/">Know more</a>'));
	add_settings_field('cerberproto',__('Cerber Lab protocol','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'activity',array('group'=>$tab,'option'=>'cerberproto','type'=>'select','set'=> array('HTTP', 'HTTPS')));
	add_settings_field('usefile',__('Use file','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'activity',array('group'=>$tab,'option'=>'usefile','type'=>'checkbox','label'=>__('Write failed login attempts to the file','wp-cerber')));

	add_settings_section('prefs', __('Preferences','wp-cerber'), 'cerber_sapi_section', 'cerber-' . $tab);
	add_settings_field('ip_extra',__('Drill down IP','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'prefs',array('group'=>$tab,'option'=>'ip_extra','type'=>'checkbox','label'=>__('Retrieve extra WHOIS information for IP','wp-cerber').' <a href="' . cerber_admin_link('help') . '">Know more</a>'));
	add_settings_field( 'dateformat', __( 'Date format', 'wp-cerber' ), 'cerberus_field_show', 'cerber-' . $tab, 'prefs', array( 'group'  => $tab, 'option' => 'dateformat', 'type'   => 'text', 'label'  => sprintf(__('if empty, the default format %s will be used','wp-cerber'),'<b>'.cerber_date(time()).'</b>') . ' <a target="_blank" href="http://wpcerber.com/date-format-setting/">Know more</a>'
	) );

	// Hardening tab --------------------------------------------------------------------------

	$tab='hardening'; // 'cerber-hardening' settings
	register_setting( 'cerberus-'.$tab, CERBER_OPT_H);
	add_settings_section('hwp', __('Hardening WordPress','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_H);
	add_settings_field('stopenum',__('Stop user enumeration','wp-cerber'),'cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'stopenum','type'=>'checkbox','label'=>__('Block access to user pages like /?author=n and user data via REST API','wp-cerber')));
	add_settings_field('adminphp',__('Protect admin scripts','wp-cerber'),'cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'adminphp','type'=>'checkbox','label'=>__('Block unauthorized access to load-scripts.php and load-styles.php','wp-cerber')));
	add_settings_field('xmlrpc',__('Disable XML-RPC','wp-cerber'),'cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'xmlrpc','type'=>'checkbox','label'=>__('Block access to the XML-RPC server (including Pingbacks and Trackbacks)','wp-cerber')));
	add_settings_field('nofeeds',__('Disable feeds','wp-cerber'),'cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'nofeeds','type'=>'checkbox','label'=>__('Block access to the RSS, Atom and RDF feeds','wp-cerber')));
	add_settings_field('norest',__('Disable REST API','wp-cerber'),'cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'norest','type'=>'checkbox','label'=>__('Block access to the WordPress REST API except the following','wp-cerber')));
	add_settings_field('restauth', '','cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'restauth','type'=>'checkbox','label'=>__('Allow REST API for logged in users','wp-cerber')));
	add_settings_field( 'restwhite', '', 'cerber_field_show', CERBER_OPT_H, 'hwp',
		array( 'group'  => $tab,
		       'setting' => 'restwhite',
		       'type'   => 'textarea',
		       'delimiter'   => "\n",
		       'list'        => true,
		       'label'  => __( 'Specify REST API namespaces to be allowed if REST API is disabled. One string per line.', 'wp-cerber' ) . ' <a target="_blank" href="https://wpcerber.com/restrict-access-to-wordpress-rest-api/">Read more</a>',
		) );
	//add_settings_field('hashauthor',__('Hide author usernames','wp-cerber'),'cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'hashauthor','type'=>'checkbox','label'=>__('Replace author username with hash for author pages and URLs','wp-cerber')));
	//add_settings_field('cleanhead',__('Clean up HEAD','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'hwp',array('group'=>$tab,'option'=>'cleanhead','type'=>'checkbox','label'=>__('Remove generator and version tags from HEAD section','wp-cerber')));
	//add_settings_field('ping',__('Disable Pingback','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'hwp',array('group'=>$tab,'option'=>'ping','type'=>'checkbox','label'=>__('Block access to ping functional','wp-cerber')));

	// Users tab -----------------------------------------------------------------------------

	$tab='users'; // 'cerber-users' settings
	register_setting( 'cerberus-'.$tab, CERBER_OPT_U);
	add_settings_section('us', __('User related settings','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_U);
	if (lab_lab()) add_settings_field('reglimit',__('Registration limit','wp-cerber'),'cerberus_field_show', CERBER_OPT_U,'us',array('group'=>$tab,'option'=>'reglimit','type'=>'reglimit'));
	add_settings_field( 'prohibited', __( 'Prohibited usernames', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_U, 'us',
		array( 'group'  => $tab,
		       'setting' => 'prohibited',
		       'type'   => 'textarea',
		       'delimiter'   => ',',
		       'list'        => true,
		       'label'  => __( 'Usernames from this list are not allowed to log in or register. Any IP address, have tried to use any of these usernames, will be immediately blocked. Use comma to separate logins.', 'wp-cerber' ) . ' ' . __( 'To specify a REGEX pattern wrap a pattern in two forward slashes.', 'wp-cerber' )
		) );
	add_settings_field('auth_expire',__('User session expire','wp-cerber'),'cerberus_field_show',CERBER_OPT_U,'us',array('group'=>$tab,'option'=>'auth_expire','type'=>'text','label'=>__('in minutes (leave empty to use default WP value)','wp-cerber'),'size' => 6));
	add_settings_field('usersort',__('Sort users in dashboard','wp-cerber'),'cerberus_field_show',CERBER_OPT_U,'us',array('group'=>$tab,'option'=>'usersort','type'=>'checkbox','label'=>__('by date of registration','wp-cerber')));

	// Antibot & reCAPTCHA -----------------------------------------------------------------------------

	$tab='recaptcha';  // 'cerber-recaptcha' settings
	register_setting( 'cerberus-'.$tab, CERBER_OPT_C);

	add_settings_section('antibot', __('Cerber antispam engine','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_C);
	add_settings_field('botscomm',__('Comment form','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'antibot',array('group'=>$tab,'option'=>'botscomm','type'=>'checkbox','label'=>__('Protect comment form with bot detection engine','wp-cerber') ));
	add_settings_field('botsreg',__('Registration form','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'antibot',array('group'=>$tab,'option'=>'botsreg','type'=>'checkbox','label'=>__('Protect registration form with bot detection engine','wp-cerber') ));
	add_settings_field('botsany',__('Other forms','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'antibot',array('group'=>$tab,'option'=>'botsany','type'=>'checkbox','label'=>__('Protect all forms on the website with bot detection engine','wp-cerber') ));

	add_settings_section('antibot_more', __('Adjust antispam engine','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_C);
	add_settings_field('botssafe',__('Safe mode','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'antibot_more',array('group'=>$tab,'option'=>'botssafe','type'=>'checkbox','label'=>__('Use less restrictive policies (allow AJAX)','wp-cerber') ));
	add_settings_field('botsnoauth',__('Logged in users','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'antibot_more',array('group'=>$tab,'option'=>'botsnoauth','type'=>'checkbox','label'=>__('Disable bot detection engine for logged in users','wp-cerber') ));
	add_settings_field( 'botswhite', __( 'Query whitelist', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_C, 'antibot_more',
		array( 'group'  => $tab,
		       'setting' => 'botswhite',
		       'type'   => 'textarea',
		       'delimiter'   => "\n",
		       'list'        => true,
		       'label' => __( 'Enter a part of query string or query path to exclude a request from inspection by the engine. One item per line.', 'wp-cerber' ) . ' ' . __( 'To specify a REGEX pattern, enclose a whole line in two braces.', 'wp-cerber' ) . ' <a href="https://wpcerber.com/antispam-for-wordpress-contact-forms/" target="_blank">Read more</a>',
		) );

	add_settings_section('commproc', __('Comment processing','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_C);
	add_settings_field('spamcomm',__('If a spam comment detected','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'commproc',array('group'=>$tab, 'option'=>'spamcomm', 'type'=>'select', 'set' => array(__('Deny it completely','wp-cerber'),__('Mark it as spam','wp-cerber'))));
	add_settings_field('trashafter',__('Trash spam comments','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'commproc',array('group'=>$tab,'option'=>'trashafter','type'=>'text','enabled'=>__('Move spam comments to trash after'),'label'=>__('days','wp-cerber'),'size'=>3));
	//add_settings_field('deleteafter',__('Delete comments from trash after','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'commproc',array('group'=>$tab,'option'=>'deleteafter','type'=>'text','label'=>__('days','wp-cerber'),'size'=>3));

	add_settings_section('recap', __('reCAPTCHA settings','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_C);
	add_settings_field('sitekey',__('Site key','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'sitekey','type'=>'text','size' => 60));
	add_settings_field('secretkey',__('Secret key','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'secretkey','type'=>'text','size' => 60));
	add_settings_field('invirecap',__('Invisible reCAPTCHA','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'invirecap','type'=>'checkbox','label'=>__('Enable invisible reCAPTCHA','wp-cerber') .' '. __('(do not enable it unless you get and enter the Site and Secret keys for the invisible version)','wp-cerber')));

	add_settings_field('recapreg',__('Registration form','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recapreg','type'=>'checkbox','label'=>__('Enable reCAPTCHA for WordPress registration form','wp-cerber')));
	add_settings_field('recapwooreg', '' ,'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recapwooreg','type'=>'checkbox','label'=>__('Enable reCAPTCHA for WooCommerce registration form','wp-cerber')));

	add_settings_field('recaplost',__('Lost password form','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recaplost','type'=>'checkbox','label'=>__('Enable reCAPTCHA for WordPress lost password form','wp-cerber')));
	add_settings_field('recapwoolost', '' ,'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recapwoolost','type'=>'checkbox','label'=>__('Enable reCAPTCHA for WooCommerce lost password form','wp-cerber')));

	add_settings_field('recaplogin',__('Login form','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recaplogin','type'=>'checkbox','label'=>__('Enable reCAPTCHA for WordPress login form','wp-cerber')));
	add_settings_field('recapwoologin', '' ,'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recapwoologin','type'=>'checkbox','label'=>__('Enable reCAPTCHA for WooCommerce login form','wp-cerber')));

	add_settings_field('recapcom',__('Antispam','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recapcom','type'=>'checkbox','label'=>__('Enable reCAPTCHA for WordPress comment form','wp-cerber')));
	add_settings_field('recapcomauth', '' ,'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recapcomauth','type'=>'checkbox','label'=>__('Disable reCAPTCHA for logged in users','wp-cerber')));

	add_settings_field('recaplimit',__('Limit attempts','wp-cerber'),'cerberus_field_show',CERBER_OPT_C,'recap',array('group'=>$tab,'option'=>'recaplimit','type'=>'limitz','label' => __('Lock out IP address for %s minutes after %s failed attempts within %s minutes','wp-cerber') ));

	// Notifications -----------------------------------------------------------------------------

	$group = 'notifications'; // 'cerber-notifications' settings
	register_setting( 'cerberus-'.$group, CERBER_OPT_N);
	add_settings_section('notify', __('Email notifications','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_N);

	$def_email = '<b>'.get_site_option('admin_email').'</b>';
	add_settings_field( 'email', __( 'Email Address', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_N, 'notify',
		array( 'group'       => $group,
		       'setting'     => 'email',
		       'type'        => 'text',
		       'placeholder' => __( 'Use comma to specify multiple values', 'wp-cerber' ),
		       'delimiter'   => ',',
		       'list'        => true,
		       'size'        => 60,
		       'maxlength'   => 1000,
		       'label'       => sprintf( __( 'if empty, the admin email %s will be used', 'wp-cerber' ), $def_email )
		) );

	add_settings_field('emailrate',__('Notification limit','wp-cerber'),'cerber_field_show',CERBER_OPT_N,'notify',array('group'=>$group,'setting'=>'emailrate','type'=>'text','label'=>__('notification letters allowed per hour (0 means unlimited)','wp-cerber'),'size'=>3));
	add_settings_field( 'notify-new-ver', __( 'New version is available', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_N, 'notify', array( 'group'   => $group,
	                                                                                                                                              'setting' => 'notify-new-ver',
	                                                                                                                                              'type'    => 'checkbox'
	) );

	add_settings_section('pushit', __('Push notifications','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_N);
	add_settings_field('pbtoken','Pushbullet access token','cerber_field_show',CERBER_OPT_N,'pushit',array('group'=>$group,'setting'=>'pbtoken','type'=>'text','size'=>60));

	$set = array();
	if (cerber_is_admin_page(false, array('tab'=>'notifications'))){
		$set = cerber_pb_get_devices();
		if (is_array($set)){
			if (!empty($set)) $set = array('all' => __('All connected devices','wp-cerber')) + $set;
			else $set = array('N' => __('No devices found','wp-cerber'));
		}
		else $set = array('N' => __('Not available','wp-cerber'));
	}
	add_settings_field('pbdevice','Pushbullet device','cerber_field_show',CERBER_OPT_N,'pushit',array('group'=>$group,'setting'=>'pbdevice','type'=>'select','set'=>$set));

	add_settings_section('reports', __('Weekly reports','wp-cerber'), 'cerber_sapi_section', CERBER_OPT_N);
	add_settings_field('enable-report',__('Enable reporting','wp-cerber'),'cerber_field_show',CERBER_OPT_N,'reports',array('group'=>$group,'setting'=>'enable-report','type'=>'checkbox'));
	add_settings_field('wreports','Send reports on','cerber_field_show',CERBER_OPT_N,'reports',array('group'=>$group,'setting'=>'wreports','type'=>'reptime'));
	add_settings_field( 'email-report', __( 'Email Address', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_N, 'reports',
		array(
			'group'       => $group,
			'setting'     => 'email-report',
			'type'        => 'text',
			'placeholder' => __( 'Use comma to specify multiple values', 'wp-cerber' ),
			'delimiter'      => ',',
			'list'      => true,
			'size'        => 60,
			'maxlength'   => 1000,
			'label'       => __( 'if empty, email from notification settings will be used', 'wp-cerber' )
		) );

	// Traffic Inspector -----------------------------------------------------------------------------

	$group = 'traffic'; // 'cerber-traffic' settings
	register_setting( 'cerberus-' . $group, CERBER_OPT_T );

	add_settings_section( 'tmain', __( 'Inspection', 'wp-cerber' ), 'cerber_sapi_section', CERBER_OPT_T );
	add_settings_field( 'tienabled', __( 'Enable traffic inspection', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tmain',
		array( 'group'  => $group,
		       'setting' => 'tienabled',
		       'type'   => 'checkbox',
		) );
	add_settings_field( 'tiwhite', __( 'Request whitelist', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tmain',
		array( 'group'  => $group,
		       'setting' => 'tiwhite',
		       'type'   => 'textarea',
		       'delimiter'   => "\n",
		       'list'        => true,
		       'label' => __( 'Enter a request URI to exclude the request from inspection. One item per line.', 'wp-cerber' ) . ' ' . __( 'To specify a REGEX pattern, enclose a whole line in two braces.', 'wp-cerber' ) . ' <a target="_blank" href="https://wpcerber.com/traffic-inspector-in-a-nutshell/">Know more</a>',
		) );

	add_settings_section( 'tlog', __( 'Logging', 'wp-cerber' ), 'cerber_sapi_section', CERBER_OPT_T );
	add_settings_field( 'timode', __( 'Logging mode', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
		array(
			'group'   => $group,
			'setting' => 'timode',
			'type'    => 'select',
			'set'     => array(
				__( 'Logging disabled', 'wp-cerber' ),
				__( 'Smart', 'wp-cerber' ),
				__( 'All traffic', 'wp-cerber' )
			)
		) );

	add_settings_field( 'tinocrabs', __( 'Ignore crawlers', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
		array( 'group'  => $group,
		       'setting' => 'tinocrabs',
		       'type'   => 'checkbox',
		) );
	add_settings_field( 'tifields', __( 'Save request fields', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
		array( 'group'  => $group,
		       'setting' => 'tifields',
		       'type'   => 'checkbox',
		) );
	add_settings_field( 'timask', __( 'Mask these form fields', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
		array( 'group'  => $group,
		       'setting' => 'timask',
		       'type'   => 'text',
		       'size' => 60,
               'maxlength' => 1000,
		       'placeholder'=>__('Use comma to specify multiple values','wp-cerber'),
		       'delimiter'      => ',',
		       'list'      => true,
		) );
	if (lab_lab()) {
		add_settings_field( 'tihdrs', __( 'Save request headers', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
			array(
				'group'   => $group,
				'setting' => 'tihdrs',
				'type'    => 'checkbox',
			) );
		add_settings_field( 'tisenv', __( 'Save $_SERVER', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
			array(
				'group'   => $group,
				'setting' => 'tisenv',
				'type'    => 'checkbox',
			) );
		add_settings_field( 'ticandy', __( 'Save request cookies', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
			array(
				'group'   => $group,
				'setting' => 'ticandy',
				'type'    => 'checkbox',
			) );
	}
	add_settings_field( 'tithreshold', __( 'Page generation time threshold', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
		array(
			'group'   => $group,
			'setting' => 'tithreshold',
			'type'    => 'text',
			'label'   => __( 'milliseconds', 'wp-cerber' ),
			'size'    => 4
		) );
	add_settings_field( 'tikeeprec', __( 'Keep records for', 'wp-cerber' ), 'cerber_field_show', CERBER_OPT_T, 'tlog',
		array( 'group'  => $group,
		       'setting' => 'tikeeprec',
		       'type'   => 'text',
		       'label'  => __( 'days', 'wp-cerber' ),
		       'size'   => 4
		) );
}
/*
	Generate HTML for each sections on a settings page
*/
function cerber_sapi_section($args){
    switch ($args['id']){ // a section id
        case 'proactive':
	        _e('Make your protection smarter!','wp-cerber');
            break;
	    case 'custom':
		    if ( ! cerber_is_permalink_enabled() ) {
			    echo '<span style="color:#DF0000;">' . __( 'Please enable Permalinks to use this feature. Set Permalink Settings to something other than Default.', 'wp-cerber' ) . '</span>';
		    }
		    else {
			    echo __( 'Be careful about enabling these options.', 'wp-cerber' ) . ' ' . __( 'If you forget your Custom login URL, you will be unable to log in.', 'wp-cerber' );
		    }
		    break;
	    case 'citadel':
		    _e("In the Citadel mode nobody is able to log in except IPs from the White IP Access List. Active user sessions will not be affected.",'wp-cerber');
	        break;
	    case 'hwp':
		    echo __('These settings do not affect hosts from the ','wp-cerber').' '.__('White IP Access List','wp-cerber');
	        break;
	    case 'recap':
		    _e('Before you can start using reCAPTCHA, you have to obtain Site key and Secret key on the Google website','wp-cerber');
		    echo ' <a href="https://wpcerber.com/how-to-setup-recaptcha/">'.__('Know more','wp-cerber').'</a>';
		    break;
    }
}

/*
 *
 * Generate HTML for admin page with tabs
 * @since 1.0
 *
 */
function cerber_settings_page(){
	global $wpdb;

	$tab = cerber_get_tab('dashboard', array('main','acl','activity','lockouts','messages','help','hardening','users','notifications'));

	?>
	<div class="wrap crb-admin">

		<h2><?php _e('WP Cerber Security','wp-cerber') ?></h2>

		<h2 class="nav-tab-wrapper cerber-tabs">
			<?php

			echo '<a href="' . cerber_admin_link() . '" class="nav-tab ' . ( $tab == 'dashboard' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-dashboard"></span> ' . __('Dashboard') . '</a>';

			echo '<a href="' . cerber_admin_link('activity') . '" class="nav-tab ' . ( $tab == 'activity' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . __('Activity','wp-cerber') . '</a>';

            $total = cerber_blocked_num();

			echo '<a href="' . cerber_admin_link('lockouts') . '" class="nav-tab ' . ( $tab == 'lockouts' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-shield"></span> ' . __('Lockouts','wp-cerber') . ' <sup class="loctotal">' . $total . '</sup></a>';

			echo '<a href="' . cerber_admin_link('main') . '" class="nav-tab ' . ( $tab == 'main' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-settings"></span> ' . __('Main Settings','wp-cerber') . '</a>';

			$total = cerber_db_get_var('SELECT count(ip) FROM '. CERBER_ACL_TABLE);
			echo '<a href="' . cerber_admin_link('acl') . '" class="nav-tab ' . ( $tab == 'acl' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-network"></span> ' . __('Access Lists','wp-cerber') . ' <sup class="acltotal">' . $total . '</sup></a>';

			echo '<a href="' . cerber_admin_link('hardening') . '" class="nav-tab ' . ( $tab == 'hardening' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-shield-alt"></span> ' . __('Hardening','wp-cerber') . '</a>';

			echo '<a href="' . cerber_admin_link('users') . '" class="nav-tab ' . ( $tab == 'users' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-users"></span> ' . __('Users') . '</a>';
			//echo '<a href="'.cerber_admin_link('messages').'" class="nav-tab '. ($tab == 'messages' ? 'nav-tab-active' : '') .'">'. __('Messages','wp-cerber').'</a>';

			echo '<a href="' . cerber_admin_link('notifications') . '" class="nav-tab ' . ( $tab == 'notifications' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-controls-volumeon"></span> ' . __('Notifications','wp-cerber') . '</a>';

			echo '<a href="' . cerber_admin_link('help') . '" class="nav-tab ' . ( $tab == 'help' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-editor-help"></span> ' . __('Help','wp-cerber') . '</a>';

			echo lab_indicator();
			?>
		</h2>
		<?php

		cerber_show_aside($tab);

		echo '<div class="crb-main">';

		switch ($tab){
			case 'acl':
				cerber_acl_form();
				break;
			case 'activity':
				cerber_show_activity();
				break;
			case 'lockouts':
				cerber_show_lockouts();
				break;
			case 'help':
				cerber_show_help();
				break;
			case 'dashboard':
				cerber_show_dashboard();
				break;
			default: cerber_show_settings_page($tab);
		}

		echo '</div>';

		$pi ['Version'] = CERBER_VER;
		$pi ['time'] = time();
		$pi ['user'] = get_current_user_id();
		update_site_option( '_cp_tabs_' . $tab, serialize( $pi ) );

		?>
	</div>
	<?php
}
/*
 * Display settings screen (one tab)
 *
 */
function cerber_show_settings_page($group = null){
	if (is_multisite()) $action =  ''; // Settings API doesn't work in multisite. Post data will be handled in the cerber_ms_update()
	else $action ='options.php';
	// Display form with settings fields via Settings API
	echo '<form method="post" action="'.$action.'">';

	settings_fields( 'cerberus-'.$group ); // option group name, the same as used in register_setting().
	do_settings_sections( 'cerber-'.$group ); // the same as used in add_settings_section()	$page
    echo '<div style="padding-left: 220px">';
	submit_button();
	echo '</div>';
	echo '</form>';
}
/*
 * Prepare values to display.
 * Generate HTML for one input field on the settings page.
 *
 *
 */
function cerberus_field_show($args){

	$settings = get_site_option('cerber-'.$args['group']);
	if ( is_array( $settings ) ) {
		array_walk_recursive( $settings, 'esc_html' );
	}
	$pre   = '';
	$value = '';
	$disabled = '';
	if ( ! empty( $args['label'] ) ) {
		$label = $args['label'];
	} else {
		$label = '';
	}
	if ( isset( $args['option'] ) ) {
		if ( isset( $settings[ $args['option'] ] ) ) {
			$value = $settings[ $args['option'] ];
		}
		if ( ( $args['option'] == 'loginnowp' || $args['option'] == 'loginpath' ) && ! cerber_is_permalink_enabled() ) {
			$disabled = ' disabled="disabled" ';
		}
		if ( $args['option'] == 'loginpath' ) {
			$pre   = rtrim( get_home_url(), '/' ) . '/';
			$value = urldecode( $value );
		}
	}

	$name = 'cerber-'.$args['group'].'['.$args['option'].']';

	switch ($args['type']) {

		case 'limitz':
			$s1 = $args['group'].'-period';
			$s2 = $args['group'].'-number';
			$s3 = $args['group'].'-within';

			$html=sprintf( $args['label'] ,
				'<input type="text" name="cerber-'.$args['group'].'['.$s1.']" value="'.$settings[$s1].'" size="3" maxlength="3" />',
				'<input type="text" name="cerber-'.$args['group'].'['.$s2.']" value="'.$settings[$s2].'" size="3" maxlength="3" />',
				'<input type="text" name="cerber-'.$args['group'].'['.$s3.']" value="'.$settings[$s3].'" size="3" maxlength="3" />');
			break;

		case 'attempts':
			$html=sprintf(__('%s allowed retries in %s minutes','wp-cerber'),
				'<input type="text" id="attempts" name="cerber-'.$args['group'].'[attempts]" value="'.$settings['attempts'].'" size="3" maxlength="3" />',
				'<input type="text" id="period" name="cerber-'.$args['group'].'[period]" value="'.$settings['period'].'" size="3" maxlength="3" />');
			break;
		case 'reglimit':
			$html=sprintf(__('%s allowed registrations in %s minutes from one IP','wp-cerber'),
				'<input type="text" id="reglimit-num" name="cerber-'.$args['group'].'[reglimit_num]" value="'.$settings['reglimit_num'].'" size="3" maxlength="3" />',
				'<input type="text" id="reglimit-min" name="cerber-'.$args['group'].'[reglimit_min]" value="'.$settings['reglimit_min'].'" size="3" maxlength="3" />');
			break;
		case 'aggressive':
			$html=sprintf(__('Increase lockout duration to %s hours after %s lockouts in the last %s hours','wp-cerber'),
				'<input type="text" id="agperiod" name="cerber-'.$args['group'].'[agperiod]" value="'.$settings['agperiod'].'" size="3" maxlength="3" />',
				'<input type="text" id="aglocks" name="cerber-'.$args['group'].'[aglocks]" value="'.$settings['aglocks'].'" size="3" maxlength="3" />',
				'<input type="text" id="aglast" name="cerber-'.$args['group'].'[aglast]" value="'.$settings['aglast'].'" size="3" maxlength="3" />');
			break;
		case 'notify':
			$html= '<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="'.$args['option'].'" name="cerber-'.$args['group'].'['.$args['option'].']" value="1" '.checked(1,$value,false).$disabled.' /><span class="crb-slider round"></span></label>'
			       .__('Notify admin if the number of active lockouts above','wp-cerber').
			       ' <input type="text" id="above" name="cerber-'.$args['group'].'[above]" value="'.$settings['above'].'" size="3" maxlength="3" />'.
			       ' [ <a href="' . wp_nonce_url( add_query_arg( array( 'testnotify'       => 'lockout', 'settings-updated' => 0	) ), 'control', 'cerber_nonce' ) . '">' . __( 'Click to send test', 'wp-cerber' ) . '</a> ]';
			break;
		case 'citadel':
			$html=sprintf(__('Enable after %s failed login attempts in last %s minutes','wp-cerber'),
				'<input type="text" id="cilimit" name="cerber-'.$args['group'].'[cilimit]" value="'.$settings['cilimit'].'" size="3" maxlength="3" />',
				'<input type="text" id="ciperiod" name="cerber-'.$args['group'].'[ciperiod]" value="'.$settings['ciperiod'].'" size="3" maxlength="3" />');
			break;
		case 'checkbox':
            $html='<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="'.$args['option'].'" name="'.$name.'" value="1" '.checked(1,$value,false).$disabled.' /><span class="crb-slider round"></span></label>';
			//$html.= $args['label'];
			$html.= '<label for="'.$args['option'].'">'.$args['label'].'</label>';
			break;
		case 'textarea':
			//$name = 'cerber-'.$args['group'].'['.$args['option'].']';
			$html='<textarea class="large-text code" id="'.$args['option'].'" name="'.$name.'" '.$disabled.' />'.$value.'</textarea>';
			$html.= '<br><label for="'.$args['option'].'">'.$args['label'].'</label>';
			break;
		case 'select':
			//$name = 'cerber-'.$args['group'].'['.$args['option'].']';
			$html=cerber_select($name,$args['set'],$value);
			break;
		case 'text':
		default:
		    //$name = 'cerber-'.$args['group'].'['.$args['option'].']';
			if ( isset( $args['size'] ) ) {
				$size = ' size="' . $args['size'] . '" maxlength="' . $args['size'] . '" ';
			} else {
				$size = '';
			}
			if ( isset( $args['placeholder'] ) ) {
				$plh = ' placeholder="' . $args['placeholder'] . '"';
			} else {
				$plh = '';
			}
			$html = $pre . '<input type="text" id="' . $args['option'] . '" name="'.$name.'" value="' . $value . '"' . $disabled . $size . $plh. '/>';
			$html .= ' <label for="' . $args['option'] . '">' . $label . '</label>';
			break;
	}

	if (!empty($args['enabled'])){
		$name = 'cerber-'.$args['group'].'['.$args['option'].'-enabled]';
		$value = 0;
		if ( isset( $settings[ $args['option'] . '-enabled' ] ) ) {
			$value = $settings[ $args['option'] . '-enabled' ];
		}
		$checkbox = '<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="' . $args['option'] . '-enabled" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . ' /><span class="crb-slider round"></span></label>' . $args['enabled'];
        $html = $checkbox . $html;
    }

	echo $html."\n";
}

/**
 * A new version of cerberus_field_show()
 *
 * @param $args
 */
function cerber_field_show($args){

	$settings = get_site_option('cerber-'.$args['group']);
	if ( is_array( $settings ) ) {
		array_walk_recursive( $settings, 'esc_html' );
	}
	$pre   = '';
	$value = '';
	$disabled = '';

	$label = empty( $args['label'] ) ? '' : $args['label'];

	if ( isset( $args['setting'] ) ) {
		if ( isset( $settings[ $args['setting'] ] ) ) {
			$value = $settings[ $args['setting'] ];
		}

		if ( ( $args['setting'] == 'loginnowp' || $args['setting'] == 'loginpath' ) && ! cerber_is_permalink_enabled() ) {
			$disabled = ' disabled="disabled" ';
		}
		if ( $args['setting'] == 'loginpath' ) {
			$pre   = rtrim( get_home_url(), '/' ) . '/';
			$value = urldecode( $value );
		}
	}

	if ( isset( $args['list'] ) ) {
		$value = cerber_array2text($value, $args['delimiter']);
	}

	$name = 'cerber-'.$args['group'].'['.$args['setting'].']';

	switch ($args['type']) {

		case 'limitz':
			$s1 = $args['group'].'-period';
			$s2 = $args['group'].'-number';
			$s3 = $args['group'].'-within';

			$html=sprintf( $label ,
				'<input type="text" name="cerber-'.$args['group'].'['.$s1.']" value="'.$settings[$s1].'" size="3" maxlength="3" />',
				'<input type="text" name="cerber-'.$args['group'].'['.$s2.']" value="'.$settings[$s2].'" size="3" maxlength="3" />',
				'<input type="text" name="cerber-'.$args['group'].'['.$s3.']" value="'.$settings[$s3].'" size="3" maxlength="3" />');
			break;

		case 'attempts':
			$html=sprintf(__('%s allowed retries in %s minutes','wp-cerber'),
				'<input type="text" id="attempts" name="cerber-'.$args['group'].'[attempts]" value="'.$settings['attempts'].'" size="3" maxlength="3" />',
				'<input type="text" id="period" name="cerber-'.$args['group'].'[period]" value="'.$settings['period'].'" size="3" maxlength="3" />');
			break;
		case 'reglimit':
			$html=sprintf(__('%s allowed registrations in %s minutes from one IP','wp-cerber'),
				'<input type="text" id="reglimit-num" name="cerber-'.$args['group'].'[reglimit_num]" value="'.$settings['reglimit_num'].'" size="3" maxlength="3" />',
				'<input type="text" id="reglimit-min" name="cerber-'.$args['group'].'[reglimit_min]" value="'.$settings['reglimit_min'].'" size="3" maxlength="3" />');
			break;
		case 'aggressive':
			$html=sprintf(__('Increase lockout duration to %s hours after %s lockouts in the last %s hours','wp-cerber'),
				'<input type="text" id="agperiod" name="cerber-'.$args['group'].'[agperiod]" value="'.$settings['agperiod'].'" size="3" maxlength="3" />',
				'<input type="text" id="aglocks" name="cerber-'.$args['group'].'[aglocks]" value="'.$settings['aglocks'].'" size="3" maxlength="3" />',
				'<input type="text" id="aglast" name="cerber-'.$args['group'].'[aglast]" value="'.$settings['aglast'].'" size="3" maxlength="3" />');
			break;
		case 'notify':
			$html= '<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="'.$args['setting'].'" name="cerber-'.$args['group'].'['.$args['setting'].']" value="1" '.checked(1,$value,false).$disabled.' /><span class="crb-slider round"></span></label>'
			       .__('Notify admin if the number of active lockouts above','wp-cerber').
			       ' <input type="text" id="above" name="cerber-'.$args['group'].'[above]" value="'.$settings['above'].'" size="3" maxlength="3" />'.
			       ' [  <a href="' . wp_nonce_url( add_query_arg( array( 'testnotify'       => 'lockout', 'settings-updated' => 0	) ), 'control', 'cerber_nonce' ) . '">' . __( 'Click to send test', 'wp-cerber' ) . '</a> ]';
			break;
		case 'citadel':
			$html=sprintf(__('Enable after %s failed login attempts in last %s minutes','wp-cerber'),
				'<input type="text" id="cilimit" name="cerber-'.$args['group'].'[cilimit]" value="'.$settings['cilimit'].'" size="3" maxlength="3" />',
				'<input type="text" id="ciperiod" name="cerber-'.$args['group'].'[ciperiod]" value="'.$settings['ciperiod'].'" size="3" maxlength="3" />');
			break;
		case 'checkbox':
			$html='<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="'.$args['setting'].'" name="'.$name.'" value="1" '.checked(1,$value,false).$disabled.' /><span class="crb-slider round"></span></label>';
			$html.= '<label for="'.$args['setting'].'">'.$label.'</label>';
			break;
		case 'textarea':
			$html='<textarea class="large-text code" id="'.$args['setting'].'" name="'.$name.'" '.$disabled.' />'.$value.'</textarea>';
			$html.= '<br><label for="'.$args['setting'].'">'.$label.'</label>';
			break;
		case 'select':
			$html=cerber_select($name,$args['set'],$value);
			break;
		case 'reptime':
			$html = cerber_time_select( $args, $settings );
			break;
		case 'text':
		default:
		    $size = '';
		    $maxlength = '';
		    $attrs = '';
            if ( isset( $args['size'] ) ) {
                //$size = ' size="' . $args['size'] . '" maxlength="' . $args['size'] . '" ';
	            $size = ' size="' . $args['size'] . '"';
            }
            if ( isset( $args['maxlength'] ) ) {
	            $maxlength = ' maxlength="' . $args['maxlength'] . '" ';
            }
		    elseif ( isset( $args['size'] ) ) {
			    $maxlength = ' maxlength="' . $args['size'] . '" ';
            }
            if ( isset( $args['placeholder'] ) ) {
                $attrs .= ' placeholder="' . $args['placeholder'] . '"';
            }
		    if ( isset( $args['pattern'] ) ) {
			    $attrs .= ' pattern="' . $args['pattern'] . '"';
		    }
		    if ( isset( $args['title'] ) ) {
			    $attrs .= ' title="' . $args['title'] . '"';
		    }
            $html = $pre . '<input type="text" id="' . $args['setting'] . '" name="' . $name . '" value="' . $value . '"' . $disabled . $size . $maxlength . $attrs . '/>';
            $html .= ' <label for="' . $args['setting'] . '">' . $label . '</label>';
		break;
	}

	if (!empty($args['enabled'])){
		$name = 'cerber-'.$args['group'].'['.$args['setting'].'-enabled]';
		$value = 0;
		if ( isset( $settings[ $args['setting'] . '-enabled' ] ) ) {
			$value = $settings[ $args['setting'] . '-enabled' ];
		}
		$checkbox = '<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="' . $args['setting'] . '-enabled" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . ' /><span class="crb-slider round"></span></label>' . $args['enabled'];
		$html = $checkbox . $html;
	}

	echo $html."\n";
}

/**
 * @param $name string HTML input name
 * @param $list array   List of elements
 * @param null $selected Index of selected element in the list 
 * @param string $class HTML class
 * @param string $multiple
 *
 * @return string   HTML for select element
 */
function cerber_select($name, $list, $selected = null, $class = '' , $multiple = ''){
	$options = array();
	foreach ($list as $key => $value ) {
		if ($selected == (string)$key) {
			$s = 'selected';
		}
		else $s = '';
		$options[]= '<option value="'.$key.'" '.$s.'>'.htmlspecialchars($value).'</option>';
	}
	if ($multiple) $m = 'multiple="multiple"'; else $m = '';
	return ' <select name="'.$name.'" class="crb-select '.$class.'" '.$m.'>'.implode("\n",$options).'</select>';
}

function cerber_time_select($args, $settings){

    // Week
	$php_week = array(
		__( 'Sunday' ),
		__( 'Monday' ),
		__( 'Tuesday' ),
		__( 'Wednesday' ),
		__( 'Thursday' ),
		__( 'Friday' ),
		__( 'Saturday' ),
	);
	$field = $args['setting'].'-day';
	if (isset($settings[ $field ])) {
	    $selected = $settings[ $field ];
    }
    else {
	    $selected = '';
    }
	$ret = cerber_select( 'cerber-' . $args['group'] . '[' . $field . ']', $php_week, $selected );
	$ret .= ' &nbsp; ' . /* translators: preposition of time */ _x( 'at', 'preposition of time', 'wp-cerber' ) . ' &nbsp; ';

	// Hours
	$hours = array();
	for($i = 0; $i <= 23; $i++) {
		$hours[] = str_pad( $i, 2, '0', STR_PAD_LEFT ) . ':00';
    }
	$field = $args['setting'].'-time';
	if (isset($settings[ $field ])) {
		$selected = $settings[ $field ];
	}
	else {
		$selected = '';
	}
	$ret .= cerber_select( 'cerber-' . $args['group'] . '[' . $field . ']', $hours, $selected );

	return $ret.' &nbsp; [ <a href="'.wp_nonce_url(add_query_arg(array('testnotify'=>'report', 'settings-updated' => 0)),'control','cerber_nonce').'">'.__('Click to send now','wp-cerber').'</a> ]';
}

/*
	Sanitizing users input for Main Settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT, 'cerber_sanitize_m', 10, 3 );
function cerber_sanitize_m($new, $old, $option) {

	$ret = cerber_set_boot_mode( $new['boot-mode'], $old['boot-mode'] );
	if ( is_wp_error( $ret ) ) {
		cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) . ' ' . $ret->get_error_message() );
		cerber_admin_notice( __( 'Plugin initialization mode has not been changed', 'wp-cerber' ) );
		$new['boot-mode'] = $old['boot-mode'];
	}

	$new['attempts'] = absint( $new['attempts'] );
	$new['period']   = absint( $new['period'] );
	$new['lockout']  = absint( $new['lockout'] );

	$new['agperiod'] = absint( $new['agperiod'] );
	$new['aglocks']  = absint( $new['aglocks'] );
	$new['aglast']   = absint( $new['aglast'] );

	if ( cerber_is_permalink_enabled() ) {
		$new['loginpath'] = urlencode( str_replace( '/', '', $new['loginpath'] ) );
		$new['loginpath'] = sanitize_text_field($new['loginpath']);
		if ( $new['loginpath'] && $new['loginpath'] != $old['loginpath'] ) {
			$href = get_home_url() . '/' . $new['loginpath'] . '/';
			$url  = urldecode( $href );
			$msg = array();
			$msg_e = array();
			$msg[]  = __( 'Attention! You have changed the login URL! The new login URL is', 'wp-cerber' ) . ': <a href="' . $href . '">' . $url . '</a>';
			$msg_e[]  = __( 'Attention! You have changed the login URL! The new login URL is', 'wp-cerber' ) . ': ' . $url;
			$msg[]  = __( 'If you use a caching plugin, you have to add your new login URL to the list of pages not to cache.', 'wp-cerber' );
			$msg_e[]  = __( 'If you use a caching plugin, you have to add your new login URL to the list of pages not to cache.', 'wp-cerber' );
			cerber_admin_notice( $msg );
			cerber_send_notify( 'newlurl', $msg_e );
		}
	} else {
		$new['loginpath'] = '';
		$new['loginnowp'] = 0;
	}

	$new['ciduration'] = absint( $new['ciduration'] );
	$new['cilimit']    = absint( $new['cilimit'] );
	$new['cilimit']    = $new['cilimit'] == 0 ? '' : $new['cilimit'];
	$new['ciperiod']   = absint( $new['ciperiod'] );
	$new['ciperiod']   = $new['ciperiod'] == 0 ? '' : $new['ciperiod'];
	if ( ! $new['cilimit'] ) {
		$new['ciperiod'] = '';
	}
	if ( ! $new['ciperiod'] ) {
		$new['cilimit'] = '';
	}

	if ( absint( $new['keeplog'] ) == 0 ) {
		$new['keeplog'] = '';
	}

	return $new;
}
/*
	Sanitizing/checking user input for User tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_U, 'cerber_sanitize_u', 10, 3 );
function cerber_sanitize_u($new, $old, $option) {

	$new['prohibited'] = cerber_text2array($new['prohibited'], ',', 'strtolower');

	return $new;
}
/*
	Sanitizing/checking user input for reCAPTCHA tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_C, 'cerber_sanitize_c', 10, 3 );
function cerber_sanitize_c($new, $old, $option) {
	global $wp_cerber;
	// Check ability to make external HTTP requests
	if ($wp_cerber && !empty($new['sitekey']) && !empty($new['secretkey'])) {
		if (!$goo = $wp_cerber->reCaptchaRequest('1')) {
			$labels = cerber_get_labels( 'activity' );
			cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) . ' ' . $labels[42] );
			cerber_log( 42 );
		}
	}

	$new['recaptcha-period'] = absint( $new['recaptcha-period'] );
	$new['recaptcha-number'] = absint( $new['recaptcha-number'] );
	$new['recaptcha-within'] = absint( $new['recaptcha-within'] );

	$new['botswhite'] = cerber_text2array($new['botswhite'], "\n");

	if ( ! $new['botsany'] && ! $new['botscomm'] && ! $new['botsreg'] ) {
		update_site_option( 'cerber-antibot', '' );
	}

	return $new;
}
/*
	Sanitizing/checking user input for Notifications tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_N, 'cerber_sanitize_n', 10, 3 );
function cerber_sanitize_n($new, $old, $option) {

	$emails = cerber_text2array( $new['email'], ',' );

	$new['email'] = array();
	foreach ( $emails as $item ) {
		if ( is_email( $item ) ) {
			$new['email'][] = $item;
		}
		else {
			cerber_admin_notice( __( '<strong>ERROR</strong>: please enter a valid email address.' ) );
		}
	}

	$emails = cerber_text2array( $new['email-report'], ',' );

	$new['email-report'] = array();
	foreach ( $emails as $item ) {
		if ( is_email( $item ) ) {
			$new['email-report'][] = $item;
		}
		else {
			cerber_admin_notice( __( '<strong>ERROR</strong>: please enter a valid email address.' ) );
		}
	}


	$new['emailrate'] = absint( $new['emailrate'] );

	// set 'default' value for the device setting if a new token has been entered
	if ( $new['pbtoken'] != $old['pbtoken'] ) {
		$list = cerber_pb_get_devices($new['pbtoken']);
		if (is_array($list) && !empty($list)) $new['pbdevice'] = 'all';
		else $new['pbdevice'] = '';
	}

	return $new;
}

/*
    Sanitizing/checking user input for Hardening tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_H, 'cerber_sanitize_h', 10, 3 );
function cerber_sanitize_h($new, $old, $option) {

    $new['restwhite'] = cerber_text2array($new['restwhite'], "\n");

	$new['restwhite'] = array_map( function ( $v ) {
		return trim( $v, '/' );
	}, $new['restwhite'] );

	if ( empty( $new['adminphp'] ) ) {
		$new['adminphp'] = 0;
	}

	$sync = cerber_htaccess_sync( $new );
    if ( $new['adminphp'] != $old['adminphp'] ) {
		if ( $sync === true ) {
			cerber_admin_message( 'The .htaccess file has been modified' );
		}
		else {
			$new['adminphp'] = $old['adminphp'];
			cerber_admin_notice( $sync );
		}
	}

    return $new;
}
/*
    Sanitizing/checking user input for Traffic Inspector tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_T, 'cerber_sanitize_t', 10, 3 );
function cerber_sanitize_t($new, $old, $option) {

	$new['tiwhite'] = cerber_text2array( $new['tiwhite'], "\n" );
	foreach ( $new['tiwhite'] as $item ) {
		if ( strrpos( $item, '?' ) ) {
			cerber_admin_notice( 'You may not specify the query string with question mark: ' . htmlspecialchars( $item ) );
		}
		if ( strrpos( $item, '://' ) ) {
			cerber_admin_notice( 'You may not specify the full URL: ' . htmlspecialchars( $item ) );
		}
	}

	$new['timask'] = cerber_text2array( $new['timask'], "," );
	if ( $new['tithreshold'] ) {
		$new['tithreshold'] = absint( $new['tithreshold'] );
	}
	$new['tikeeprec'] = absint($new['tikeeprec']);
	if ( $new['tikeeprec'] < 1 ) {
		$new['tikeeprec'] = $old['tikeeprec'];
		cerber_admin_notice( 'You may not set <b>Keep records for</b> to 0 days. To completely disable logging set <b>Logging mode</b> to Logging disabled.' );
	}

	return $new;
}
/**
 * Let's sanitize them all
 * @since 4.1
 *
 */
add_filter( 'pre_update_option','cerber_o_o_sanitizer', 10 , 3);
function cerber_o_o_sanitizer($value, $option, $old_value) {
	if (in_array($option, cerber_get_setting_list())){
		if (is_array($value)){
			array_walk_recursive($value, function (&$element, $key) {
				if (!is_array($element)) $element = sanitize_text_field($element);
			});
		}
		else {
			$value = sanitize_text_field($value);
		}
		$value = cerber_normalize($value, $option);
	}

	return $value;
}

/**
 * Fill missed settings (array keys) with empty values
 * @since 5.8.2
 *
 * @param $values
 * @param $group
 *
 * @return array
 */
function cerber_normalize( $values, $group ) {
	$def = cerber_get_defaults();
	if ( isset( $def[ $group ] ) ) {
		$keys  = array_keys( $def[ $group ] );
		$empty = array_fill_keys( $keys, '' );
		$values   = array_merge( $empty, $values );
	}

	return $values;
}

/**
 * Convert an array to text string by using a given delimiter
 *
 * @param array $array
 * @param string $delimiter
 *
 * @return array|string
 */
function cerber_array2text( $array = array(), $delimiter = '') {
	if ( empty( $array ) ) {
		return '';
	}

	if ( is_array( $array ) ) {
	    if ($delimiter == ',') $delimiter .= ' ';
		$ret = implode( $delimiter , $array );
	}
	else {
		$ret = $array;
    }

    return $ret;
}

/**
 * Convert text to array by using a given element delimiter, remove empty and duplicate elements
 * Optionally a callback function may be applied to resulting array elements.
 *
 * @param string $text
 * @param string $delimiter
 * @param string $callback
 *
 * @return array|string
 */
function cerber_text2array( $text = '', $delimiter = '', $callback = '') {

	if ( empty( $text ) ) {
		return array();
	}

	if ( ! is_array( $text ) ) {
		$list = explode( $delimiter, $text );
	}
	else {
		$list = $text;
	}
	$list = array_map( 'trim', $list );

	if ( $callback ) {
		$list = array_map( $callback, $list );
	}

	$list = array_filter( $list );
	$list = array_unique( $list );

	return $list;
}

/*
 * Save settings on the multisite WP.
 * Process POST Form for settings screens.
 * Because Settings API doesn't work in multisite mode!
 *
 */
if (is_multisite())  {
    add_action('admin_init', 'cerber_ms_update');
}
function cerber_ms_update() {
	if ( !cerber_is_http_post() || ! isset( $_POST['action'] ) || $_POST['action'] != 'update' ) {
		return;
	}
	if ( ! isset( $_POST['option_page'] ) || false === strpos( $_POST['option_page'], 'cerberus-' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// See wp_nonce_field() in the settings_fields() function
	check_admin_referer($_POST['option_page'].'-options');

	$opt_name = 'cerber-' . substr( $_POST['option_page'], 9 ); // 8 = length of 'cerberus-'

	$old = (array) get_site_option( $opt_name );
	$new = $_POST[ $opt_name ];
	$new = apply_filters( 'pre_update_option_' . $opt_name, $new, $old, $opt_name );
	update_site_option( $opt_name, $new );
}

/*
 * 	Default settings
 *
 */
function cerber_get_defaults($field = null) {
	$all_defaults = array(
		CERBER_OPT   => array(
			'boot-mode'   => 0,
			'attempts'   => 3,
			'period'     => 60,
			'lockout'    => 60,
			'agperiod'   => 24,
			'aglocks'    => 2,
			'aglast'     => 4,
			'limitwhite' => 0,
			'notify'     => 1,
			'above'      => 3,

			'proxy' => 0,

			'subnet'     => 0,
			'nonusers'   => 0,
			'wplogin'    => 0,
			'noredirect' => 0,
			'page404'    => 1,

			'loginpath' => '',
			'loginnowp' => 0,

			'cilimit'    => 200,
			'ciperiod'   => 30,
			'ciduration' => 60,
			'cinotify'   => 1,

			'keeplog' => 30,
			'ip_extra' => 1,
			'cerberlab' => 0,
			'cerberproto' => 0,
			'usefile' => 0,
			'dateformat' => ''

		),
		CERBER_OPT_H => array(
			'stopenum' => 1,
			'adminphp' => 0,
			'xmlrpc'   => 0,
			'nofeeds'  => 0,
			'norest'  => 0,
			'restauth'  => 0,
			'restwhite' => '',
			'hashauthor'  => 0,
			'cleanhead'  => 1,
		),
		CERBER_OPT_U => array(
			'reglimit_num' => 3,
			'reglimit_min' => 60,
			'prohibited'   => array(),
			'auth_expire'  => '',
			'usersort'     => '',
		),
		CERBER_OPT_C => array(
			'botscomm'  => 1,
			'botsreg'  => 0,
			'botsany'  => 0,
			'botssafe'  => 0,
			'botsnoauth'  => 1,
			'botswhite' => '',

			'spamcomm' => 0,
			'trashafter'  => 7,
			'trashafter-enabled'  => 0,
			'sitekey' => '',
			'secretkey' => '',
			'invirecap'  => 0,
			'recaplogin' => 0,
			'recaplost' => 0,
			'recapreg' => 0,
			'recapwoologin' => 0,
			'recapwoolost' => 0,
			'recapwooreg' => 0,
			'recapcom' => 0,
			'recapcomauth' => 0,
            'recaptcha-period' => 60,
			'recaptcha-number' => 3,
			'recaptcha-within' => 30,
		),
		CERBER_OPT_N => array(
			'email'        => '',
			'emailrate'    => 12,
			'notify-new-ver' => '1',
			'pbtoken'      => '',
			'pbdevice'     => '',
			'wreports-day'  => '1', // workaround, see cerber_upgrade_options()
			'wreports-time' => 9,
			'email-report'  => '',
			'enable-report'  => '1',  // workaround, see cerber_upgrade_options()
		),
		CERBER_OPT_T => array(
			'tienabled' => '1',
			'tiwhite'   => '',
			'timode'    => '1',
			'tinocrabs'  => '1',
			'tifields'  => 0,
			'timask'    => '',
			'tihdrs'  => 0,
			'tisenv'  => 0,
			'ticandy'  => 0,
			'tithreshold'  => '',
			'tikeeprec' => 7,
        )
		);
	if ( $field ) {
		foreach ( $all_defaults as $option ) {
			if ( isset( $option[ $field ] ) ) {
				return $option[ $field ];
			}
		}
		return false;
	}
	else {
		return $all_defaults;
	}
}

/**
 * Upgrade plugin options
 *
 */
function cerber_upgrade_options() {
	// @since 4.4, migrating fields to a new option
	if ($main = get_site_option( CERBER_OPT )) {
		if ( ! empty( $main['email'] ) || ! empty( $main['emailrate'] ) ) {
			$new              = get_site_option( CERBER_OPT_N, array() );
			$new['email']     = $main['email'];
			$new['emailrate'] = $main['emailrate'];
			update_site_option( CERBER_OPT_N, $new );
			// clean up old values
			$main['email']     = '';
			$main['emailrate'] = '';
			update_site_option( CERBER_OPT, $main );
		}
	}
	// @since 5.7
    // Upgrade options: add new settings (fields) with their default values
	foreach ( cerber_get_defaults() as $option_name => $fields ) {
		$values = get_site_option( $option_name );
		if ( ! $values ) {
			$values = array();
		}
		foreach ( $fields as $field_name => $default ) {
			if ( ! isset( $values[ $field_name ] ) && $default !== 1) { // @since 5.7.2 TODO refactor $default !== 1 to more obvious
				$values[ $field_name ] = $default;
			}
		}


		// Must be at the end of all operations above
		$values = cerber_normalize($values, $option_name); // @since 5.8.2

		update_site_option( $option_name, $values );
	}
}


/*
 *
 * Right way to save Cerber settings outside of wp-admin settings page
 * @since 2.0
 *
 */
function cerber_save_options($options){
	foreach ( cerber_get_defaults() as $option_name => $fields ) {
		$save=array();
		foreach ( $fields as $field_name => $def ) {
			if (isset($options[$field_name])) $save[$field_name]=$options[$field_name];
		}
		if (!empty($save)) {
			$result = update_site_option($option_name,$save);
		}
	}
}

/**
 *
 * @deprecated since 4.0 use $wp_cerber->getSettings() instead.
 * @param string $option
 *
 * @return array|bool|mixed
 */
function cerber_get_options( $option = '' ) {
	$options = cerber_get_setting_list();
	$united  = array();
	foreach ( $options as $opt ) {
		$o = get_site_option( $opt );
		if ( ! is_array( $o ) ) {
			continue;
		}
		$united = array_merge( $united, $o );
	}
	$options = $united;
	if ( ! empty( $option ) ) {
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}
		else {
			return false;
		}
	}

	return $options;
}

/**
 * The replacement for cerber_get_options()
 *
 * @param string $option
 *
 * @return array|bool|mixed
 */
function crb_get_settings( $option = '' ) {
	global $wpdb;
	static $united;

	/**
	 * For some hosting environments it might be faster, e.g. Redis enabled
	 */
	if ( defined( 'CERBER_WP_OPTIONS' ) && CERBER_WP_OPTIONS ) {
		return cerber_get_options( $option );
	}

	if ( ! isset( $united ) ) {

	    $options = cerber_get_setting_list();
	    $in = 'IN ("' . implode( '","', $options ) . '")';
	    $united  = array();

	    if ( is_multisite() ) {
		    $set = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->sitemeta . ' WHERE meta_key ' . $in );
	    }
	    else {
		    $set = $wpdb->get_col( 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name ' . $in );
	    }

	    foreach ( $set as $item ) {
		    if ( empty( $item ) ) {
			    continue;
		    }

		    $value = unserialize( $item );

		    if ( ! $value || ! is_array( $value ) ) {
			    continue;
		    }

		    $united = array_merge( $united, $value );
	    }

    }

	if ( ! empty( $option ) ) {
		if ( isset( $united[ $option ] ) ) {
			return $united[ $option ];
		}
		else {
			return false;
		}
	}

	return $united;
}

/**
 * @param string $option Name of site option
 * @param boolean $unserialize If true the value of the option must be unserialized
 *
 * @return null|array|string
 * @since 5.8.7
 */
function cerber_get_site_option($option = '', $unserialize = true){
    global $wpdb;
	static $values = array();

	if ( ! $option ) {
		return null;
	}

	/**
	 * For some hosting environments it might be faster, e.g. Redis enabled
	 */
	if ( defined( 'CERBER_WP_OPTIONS' ) && CERBER_WP_OPTIONS ) {
		return get_site_option( $option, null );
	}

	if (isset($values[$option])){
		return $values[$option];
    }

    if ( is_multisite() ) {
		$value = $wpdb->get_var( 'SELECT meta_value FROM ' . $wpdb->sitemeta . ' WHERE meta_key = "' . $option . '"' );
	}
	else {
		$value = $wpdb->get_var( 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name = "' . $option . '"' );
	}

	if ( $value ) {
		if ( $unserialize ) {
			$value = @unserialize( $value );
			if ( ! is_array( $value ) ) {
				$value = null;
			}
		}
	}
	else {
		$value = null;
	}

	$values[$option] = $value;
	return $value;
}

/*
	Load default settings, except Custom Login URL
*/
function cerber_load_defaults() {
	$save = array();
	foreach ( cerber_get_defaults() as $option_name => $fields ) {
		foreach ( $fields as $field_name => $def ) {
			$save[ $field_name ] = $def;
		}
	}
	$old = cerber_get_options();
	if ( ! empty( $old['loginpath'] ) ) {
		$save['loginpath'] = $old['loginpath'];
	}
	cerber_save_options( $save );
}

/**
 * @param string $type Type of notification email
 * @param bool $array  Return as an array
 *
 * @return array|string Email address(es) for notifications
 */
function cerber_get_email($type = '', $array = false) {
	$email = '';

    if ( $type == 'report' ) {
		$email = crb_get_settings( 'email-' . $type );
	}

	if ( ! $email ) {
		$email = crb_get_settings( 'email' );
	}

	if ( !$array && is_array( $email ) ) {  // @since 4.9
		$email = implode( ', ', $email );
	}

	if ( empty( $email ) ) {
		$email = get_site_option( 'admin_email' );
		if ( $array ) {
			$email = array( $email );
		}
	}

	return $email;
}
