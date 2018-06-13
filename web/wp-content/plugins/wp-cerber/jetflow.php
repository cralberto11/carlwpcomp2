<?php
/*

 Integration with the jetFlow.io automation and customization plugin, http://jetflow.io
 Actions and triggers definitions.

 Copyright (C) 2015-18 CERBER TECH INC., Gregory Markov, https://wpcerber.com

 Licenced under the GNU GPL.

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


add_action('jetflow_register','cerber_jetflow');
function cerber_jetflow() {
	if (!class_exists('WF_Repository')) return;
	class TR_IP_Locked extends WF_Action {
		static $trigger = true;
		static $wp_hook = 'cerber_ip_locked';
		static $name = 'IP locked out';
		static $description = 'Start after IP address has been blocked by Cerber';
		static $form_help = '
	Start the workflow after an IP address has been locked out by the WP Cerber plugin and conditions match the specified criteria. 
	<p>To get blocked IP address in actions use pattern <code>{TRIGGER[IP]}</code>, for a reason why the IP has been locked out use pattern <code>{TRIGGER[reason]}</code>.
	';
		public static $fields  = array(
			'filter' => array(
				'type'      => 'group',
				'fields'	=> array(
					'locks'    => array(
						'type'     => 'text',
						'label'    => 'Start if an IP address has been locked out more than times',
						'default'  => '0',
						'autocomplete' => 0
					),
					'period'    => array(
						'type'     => 'text',
						'label'    => 'in the last minutes',
						'default'  => '60',
						'autocomplete' => 0
					),
				)),
			'limit'    => array(
				'type'     => 'text',
				'label'    => 'Start if the number of currently locked out IP addresses is greater than',
				'default'  => '0',
				'required' => 1,
				'autocomplete' => 0
			),
		);
		function execute($fields) {
			global $wpdb;
			list ($fields, $previous, $env, $wp_arguments) = func_get_args();
			if ( cerber_blocked_num() <= absint($fields['limit'])) return new WF_Stop( __CLASS__ );
			if (!empty($fields['filter']['locks'])) $locks = absint($fields['filter']['locks']);
			else $locks = 0;
			if ($locks > 0) {
				$ip       = $wp_arguments[0]['IP'];
				$stamp    = time() - absint( $fields['filter']['period'] ) * 60;
				$lockouts = $wpdb->get_var( $wpdb->prepare( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE ip = %s AND activity IN (10,11) AND stamp > %d', $ip, $stamp ) );
				$lockouts = absint($lockouts);
				if ( !$lockouts || $lockouts <= $locks) {
					return new WF_Stop( __CLASS__ );
				}
			}
			return $wp_arguments[0];
		}
		static function getStarterInfo($config, $context) {
			return 'After IP address has been locked out by Cerber';
		}
	}
	class WF_WHOIS extends WF_Action {
		public static $section = 'network';
		public static $name = 'Get WHOIS info';
		public static $description = 'Get extended information about IP address';
		public static $form_help = '
		Sends request to a WHOIS server and obtains details about given IP address like country, abuse email address, owner of network, etc. The WHOIS information is publicly available and provided for free. 
		There are no reasons for security concerns, because a list of WHOIS servers are maintained by <a target="_blank" href="https://en.wikipedia.org/wiki/ICANN">ICANN</a>.
		<p>Bear in mind that each WHOIS request takes some time to retrieve data from remote WHOIS server. One request can take up to 300 ms approximately, so workflow will wait that time for a response with each request. 
		<p>A result will be a list. To get a country name in the next action use pattern <code>{PREVIOUS[country-name]}</code>, for two letter country code: <code>{PREVIOUS[country]}</code>, for abuse email address: <code>{PREVIOUS[abuse-mailbox]}</code>, for network as IP range: <code>{PREVIOUS[inetnum]}</code>. 
		The full list of available fields depends on network owner.
		You can request WHOIS data manually to find out what kind of field are available on this page: <a target="_blank" href="http://wq.apnic.net/apnic-bin/whois.pl">http://wq.apnic.net/apnic-bin/whois.pl</a>.
    ';
		public static $fields  = array(
			'ip'    => array(
				'type'        => 'text',
				'label'       => 'IP address',
				'default'   => '{TRIGGER[IP]}',
				'required' => 1,
			),
		);
		function execute($fields) {
			list ($fields, $previous, $env, $wp_arguments) = func_get_args();
			$ip = filter_var($fields['ip'],FILTER_VALIDATE_IP);
			if (!$ip) return false;
			$whois = cerber_ip_whois_info($ip);
			if (!empty($whois['error'])) return new WF_Error (__CLASS__, 'Unable to obtain IP info');
			$ret = $whois['data'];

			if (empty($ret['abuse-mailbox']) && !empty($ret['OrgAbuseEmail'])){
				$ret['abuse-mailbox'] = $ret['OrgAbuseEmail'];
			}

			if (!is_email($ret['abuse-mailbox'])) $ret['abuse-mailbox'] = '';

			$ret['country-name'] = cerber_country_name($ret['country']);
			return $ret;
		}
	}
	wof_register(array('TR_IP_Locked','WF_WHOIS'));
}

