<?php
/*
 	Copyright (C) 2015-18 CERBER TECH INC., http://cerber.tech
    Copyright (C) 2015-18 Gregory Markov, https://wpcerber.com

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



 ▄████▄     ▓█████     ██▀███      ▄▄▄▄      ▓█████     ██▀███
▒██▀ ▀█     ▓█   ▀    ▓██ ▒ ██▒   ▓█████▄    ▓█   ▀    ▓██ ▒ ██▒
▒▓█    ▄    ▒███      ▓██ ░▄█ ▒   ▒██▒ ▄██   ▒███      ▓██ ░▄█ ▒
▒▓▓▄ ▄██▒   ▒▓█  ▄    ▒██▀▀█▄     ▒██░█▀     ▒▓█  ▄    ▒██▀▀█▄
▒ ▓███▀ ░   ░▒████▒   ░██▓ ▒██▒   ░▓█  ▀█▓   ░▒████▒   ░██▓ ▒██▒
░ ░▒ ▒  ░   ░░ ▒░ ░   ░ ▒▓ ░▒▓░   ░▒▓███▀▒   ░░ ▒░ ░   ░ ▒▓ ░▒▓░
  ░  ▒       ░ ░  ░     ░▒ ░ ▒░   ▒░▒   ░     ░ ░  ░     ░▒ ░ ▒░
░              ░        ░░   ░     ░    ░       ░        ░░   ░
░ ░            ░  ░      ░         ░            ░  ░      ░
░                                       ░




*========================================================================*
|                                                                        |
|	       ATTENTION!  Do not change or edit this file!                  |
|                                                                        |
*========================================================================*

*/

// If this file is called directly, abort executing.
if ( ! defined( 'WPINC' ) ) {
	exit;
}

define( 'CERBER_LOG_TABLE', 'cerber_log' );
define( 'CERBER_TRAF_TABLE', 'cerber_traffic' );
define( 'CERBER_ACL_TABLE', 'cerber_acl' );
define( 'CERBER_BLOCKS_TABLE', 'cerber_blocks' );
define( 'CERBER_LAB_TABLE', 'cerber_lab' );
define( 'CERBER_LAB_IP_TABLE', 'cerber_lab_ip' );
define( 'CERBER_LAB_NET_TABLE', 'cerber_lab_net' );
define( 'CERBER_GEO_TABLE', 'cerber_countries' );
define( 'CERBER_SCAN_TABLE', 'cerber_files' );
define( 'CERBER_SETS_TABLE', 'cerber_sets' );

define( 'CERBER_PREFIX', '_cerber_' );
define( 'CERBER_MARKER1', 'WP CERBER GROOVE' );

define( 'WP_LOGIN_SCRIPT', 'wp-login.php' );
define( 'WP_REG_URI', 'wp-register.php' );
define( 'WP_XMLRPC_SCRIPT', 'xmlrpc.php' );
define( 'WP_TRACKBACK_SCRIPT', 'wp-trackback.php' );
define( 'WP_PING_SCRIPT', 'wp-trackback.php' );
define( 'WP_SIGNUP_SCRIPT', 'wp-signup.php' );

define( 'GOO_RECAPTCHA_URL', 'https://www.google.com/recaptcha/api/siteverify' );

define( 'CERBER_REQ_PHP', '5.3.0' );
define( 'CERBER_REQ_WP', '4.5' );
define( 'CERBER_TECH', 'https://cerber.tech/' );

require_once( dirname( __FILE__ ) . '/cerber-pluggable.php' );
require_once( dirname( __FILE__ ) . '/common.php' );
require_once( dirname( __FILE__ ) . '/settings.php' );
require_once( dirname( __FILE__ ) . '/cerber-lab.php' );
require_once( dirname( __FILE__ ) . '/whois.php' );
require_once( dirname( __FILE__ ) . '/jetflow.php' );
require_once( dirname( __FILE__ ) . '/cerber-news.php' );
//require_once( dirname( __FILE__ ) . '/cerber-scanner.php' );

if ( defined( 'WP_ADMIN' ) || defined( 'WP_NETWORK_ADMIN' ) ) {
	// Load dashboard stuff
	require_once( dirname( __FILE__ ) . '/dashboard.php' );
}

// =============================================================================================

class WP_Cerber {
	private $remote_ip;
	private $session_id;
	private $status = null;
	private $options;
	private $locked = null; // IP has been locked out

	private $recaptcha = null; // Can recaptcha be verified with current request
	private $recaptcha_verified = null; // Is recaptcha successfully verified with current request
	public $recaptcha_here = null; // Is recaptcha widget enabled on the currently displayed page

	private $uri_prohibited = null;
	private $deny = null;
	private $acl = null;

    //private $boot_source_file = '';
	//private $boot_target_file = '';

	public $garbage = false; // Garbage has been deleted

	final function __construct() {

		$this->session_id = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 24);

		// Load settings with filling missing (not-set) array keys
		$this->options = crb_get_settings(); // @since 6.3.3
		$keys = array();
		//$defaults = array();
		foreach ( cerber_get_defaults() as $item ) {
			$keys = array_merge( $keys, array_keys( $item ) );
			//$defaults = array_merge( $defaults, $item );
		}
		foreach ( $keys as $key ) {
			if ( ! isset( $this->options[ $key ] ) ) {
				$this->options[ $key ] = null;
			}
		}

		if ( defined( 'CERBER_IP_KEY' ) ) {
			$this->remote_ip = filter_var( $_SERVER[ CERBER_IP_KEY ], FILTER_VALIDATE_IP );
		}
		elseif ( $this->options['proxy'] && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$list = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			foreach ( $list as $maybe_ip ) {
				$this->remote_ip = filter_var( trim( $maybe_ip ), FILTER_VALIDATE_IP );
				if ( $this->remote_ip ) {
					break;
				}
			}
			if ( ! $this->remote_ip && isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
				$this->remote_ip = filter_var( $_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP );
			}
		} else {
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$this->remote_ip = $_SERVER['REMOTE_ADDR'];
			} elseif ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
				$this->remote_ip = $_SERVER['HTTP_X_REAL_IP'];
			} elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$this->remote_ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( isset( $_SERVER['SERVER_ADDR'] ) ) {
				$this->remote_ip = $_SERVER['SERVER_ADDR'];
			}
			$this->remote_ip = filter_var( $this->remote_ip, FILTER_VALIDATE_IP );
		}
		// No IP address was found? Roll back to localhost.
		if ( ! $this->remote_ip ) {
			$this->remote_ip = '127.0.0.1';
		} // including WP-CLI, other way is: if defined('WP_CLI')

		$this->remote_ip = cerber_short_ipv6( $this->remote_ip );

		$this->reCaptchaInit();

		$this->deleteGarbage();

		// Condition to check reCAPTCHA

		add_action( 'login_init', array( $this, 'reCaptchaNow' ) );

	}

	/**
	 * @since 6.3.3
	 */
	final public function isURIProhibited() {
		global $cerber_status;

		if ( isset( $this->uri_prohibited ) ) {
			return $this->uri_prohibited;
		}

		if ( cerber_acl_check() == 'W' ) {
			$this->uri_prohibited = false;
			return false;
		}

		$script = cerber_last_uri();
		if ( substr( $script, - 4 ) != '.php' ) {
			$script .= '.php'; // Apache MultiViews enabled?
		}

		if ( $script ) {
			if ( $script == WP_LOGIN_SCRIPT || $script == WP_SIGNUP_SCRIPT || ( $script == WP_REG_URI && ! get_option( 'users_can_register' ) ) ) { // no direct access
				if ( !empty( $this->options['wplogin'] ) ) {
					cerber_log( 50 );
					cerber_soft_block_add( $this->remote_ip, 2, $script );
					$this->uri_prohibited = true;
					return true;
				}
				if ( $this->isDeny() || !empty( $this->options['loginnowp'] ) ) {
					cerber_log( 50 );
					$this->uri_prohibited = true;
					return true;
				}
			}
            elseif ( $script == WP_XMLRPC_SCRIPT || $script == WP_TRACKBACK_SCRIPT ) { // no direct access
				if ( $this->isDeny() || ! empty( $this->options['xmlrpc'] ) ) {
					cerber_log( 50 );  // @since 5.21
					$this->uri_prohibited = true;
					return true;
				}
                if ( !cerber_geo_allowed( 'geo_xmlrpc' ) ) {
					$cerber_status = 16;
					cerber_log( 71 );
	                $this->uri_prohibited = true;
	                return true;
				}
			}
		}

		$this->uri_prohibited = false;

		return $this->uri_prohibited;
	}

	/**
	 * @since 6.3.3
	 */
	final public function CheckProhibitedURI(){
	    if ($this->isURIProhibited()){
		    if ( $this->options['page404'] ) {
			    cerber_404_page();
		    }

		    return true;
        }

        return false;
    }

	/**
	 * @since 6.3.3
	 */
	final public function InspectRequest(){
		$deny = false;
		$act = 18;
		if ( cerber_is_http_post() ) {
			if ( ! cerber_is_allowed() ) {
				$deny = true;
				$act  = 18;
			}
		}
		if ( !$deny && $_FILES ) {
			$file_names = array();
			foreach ( $_FILES as $file ) {
				if ( is_array( $file['name'] ) ) {
					$file_names = array_merge( $file_names, $file['name'] );
				}
				else {
					$file_names[] = $file['name'];
				}
			}
			foreach ( $file_names as $item ) {
				if ( $reason = $this->isProhibitedFilename( $item ) ) {
					$deny  = true;
					$act = $reason;
					break;
				}
			}
		}
		if ( $deny ) {
			cerber_log( $act );
			cerber_forbidden_page();
		}
    }

	/**
	 * @since 6.3.3
	 */
	final public function isProhibitedFilename( $file_name ) {
		$prohibited = array( '.htaccess' );
		if ( in_array( $file_name, $prohibited ) ) {
			return 57;
		}
		if ( cerber_detect_exec_extension( $file_name, array('js') ) ) {
			return 56;
		}

		return false;
	}

	/**
	 * @since 6.3.3
	 */
	final public function isDeny() {
		if ( ! isset( $this->deny ) ) {
			$this->acl = cerber_acl_check();
			if ( $this->acl == 'B' || ! cerber_is_allowed() ) {
				$this->deny = true;
			}
			else {
				$this->deny = false;
			}
		}

		return $this->deny;
	}

	// TODO: replace it with cerber_get_remote_ip()
	final public function getRemoteIp() {
		return $this->remote_ip;
	}

	final public function getSessionID() {
		return $this->session_id;
	}

	final public function getStatus() {
		if (isset($this->status)) return $this->status;

		$this->status = 0; // Default

		if ( cerber_is_citadel() ) {
			$this->status = 3;
		}
		else {
			//if ( ! cerber_is_allowed( $this->remote_ip ) ) {
			if ( cerber_block_check( $this->remote_ip ) ) {
				$this->status = 2;
			}
			else {
				$tag = cerber_acl_check( $this->remote_ip );
                if ( $tag == 'W' ) {
					//$this->status = 4;
				}
				elseif ( $tag == 'B' || lab_is_blocked($this->remote_ip, false)) {
					$this->status = 1;
				}
			}
		}

		return $this->status;
	}

	/*
		Return Error message in context
	*/
	final public function getErrorMsg() {
		$status = $this->getStatus();
		switch ( $status ) {
			case 1:
			case 3:
				return apply_filters( 'cerber_msg_blocked', __( 'You are not allowed to log in. Ask your administrator for assistance.', 'wp-cerber' ) , $status);
			case 2:
				$block = cerber_get_block();
				$min   = 1 + ( $block->block_until - time() ) / 60;

				return apply_filters( 'cerber_msg_reached',
					sprintf( __( 'You have reached the login attempts limit. Please try again in %d minutes.', 'wp-cerber' ), $min ),
					$min );
				break;
			default:
				return '';
		}
	}

	/*
		Return Remain message in context
	*/
	final public function getRemainMsg() {
		$acl = !$this->options['limitwhite'];
		$remain = cerber_get_remain_count($this->remote_ip, $acl);
		if ( $remain < $this->options['attempts'] ) {
			if ( $remain == 0 ) {
				$remain = 1;  // with some settings or when lockout was manually removed, we need to have 1 attempt.
			}
			return apply_filters( 'cerber_msg_remain',
				sprintf( _n( 'You have only one attempt remaining.', 'You have %d attempts remaining.', $remain, 'wp-cerber' ), $remain ),
				$remain );
		}

		return false;
	}

	final public function getSettings( $name = null ) {
		if ( ! empty( $name ) ) {
			if ( isset( $this->options[ $name ] ) ) {
				return $this->options[ $name ];
			} else {
				return false;
			}
		}

		return $this->options;
	}

	/*
	final public function isProhibited( $username ) {
		if ( empty( $this->options['prohibited'] ) ) {
			return false;
		}

		return in_array( $username, (array) $this->options['prohibited'] );
	}*/

	/**
	 * Adding reCAPTCHA widgets
	 *
	 */
	final public function reCaptchaInit(){

		if ( $this->status == 4 || empty( $this->options['sitekey'] ) || empty( $this->options['secretkey'] )) return;

		// Native WP forms
		add_action( 'login_form', function () {
			global $wp_cerber;
			$wp_cerber->reCaptcha( 'widget', 'recaplogin' );
		} );
		add_filter( 'login_form_middle', function ( $value ) {
			global $wp_cerber;
			$value .= $wp_cerber->reCaptcha( 'widget', 'recaplogin', false );
			return $value;
		});
		add_action( 'lostpassword_form', function () {
			global $wp_cerber;
			$wp_cerber->reCaptcha( 'widget', 'recaplost' );
		} );
		add_action( 'register_form', function () {
			global $wp_cerber;
			if ( !did_action( 'woocommerce_register_form_start' ) ) {
				$wp_cerber->reCaptcha( 'widget', 'recapreg' );
			}
		} );

		// Support for WooCommerce forms: @since 3.8
		add_action( 'woocommerce_login_form', function () {
			global $wp_cerber;
			$wp_cerber->reCaptcha( 'widget', 'recapwoologin' );
		} );
		add_action( 'woocommerce_lostpassword_form', function () {
			global $wp_cerber;
			$wp_cerber->reCaptcha( 'widget', 'recapwoolost' );
		} );
		add_action( 'woocommerce_register_form', function () {
			global $wp_cerber;
			if ( ! did_action( 'woocommerce_register_form_start' ) ) {
				return;
			}
			$wp_cerber->reCaptcha( 'widget', 'recapwooreg' );
		} );
		add_filter( 'woocommerce_process_login_errors', function ( $validation_error ) {
			global $wp_cerber;
			//$wp_cerber->reCaptchaNow();
			if ( ! $wp_cerber->reCaptchaValidate('woologin', true) ) {

				return new WP_Error( 'incorrect_recaptcha', $wp_cerber->reCaptchaMsg('woocommerce-login'));
			}
			return $validation_error;
		});
		add_filter( 'allow_password_reset', function ( $var ) { // Note: 'allow_password_reset' also is fired in WP itself
			global $wp_cerber;
			if ( isset( $_POST['wc_reset_password'] ) && did_action( 'woocommerce_init' )) {
				//$wp_cerber->reCaptchaNow();
				if ( ! $wp_cerber->reCaptchaValidate( 'woolost' , true) ) {

					return new WP_Error( 'incorrect_recaptcha', $wp_cerber->reCaptchaMsg('woocommerce-lost'));
				}
			}
			return $var;
		});
		add_filter( 'woocommerce_process_registration_errors', function ( $validation_error ) {
			global $wp_cerber;
			//$wp_cerber->reCaptchaNow();
			if ( ! $wp_cerber->reCaptchaValidate('wooreg' , true) ) {

				return new WP_Error( 'incorrect_recaptcha', $wp_cerber->reCaptchaMsg('woocommerce-register'));
			}
			return $validation_error;
		});

	}

	/**
	 * Generates reCAPTCHA HTML
	 *
	 * @param string $part  'style' or 'widget'
	 * @param null $option  what plugin setting must be set to show the reCAPTCHA
	 * @param bool $echo    if false, return the code, otherwise show it
	 *
	 * @return null|string
	 */
	final public function reCaptcha( $part = '', $option = null, $echo = true ) {
		if ( $this->status == 4 || empty( $this->options['sitekey'] ) || empty( $this->options['secretkey'] )
		     || ( $option && empty( $this->options[ $option ] ) )
		) {
			return null;
		}

		$sitekey = $this->options['sitekey'];
		$ret     = '';

		switch ( $part ) {
			case 'style': // for default login WP form only - fit it in width nicely.
				?>
				<style type="text/css" media="all">
					#rc-imageselect, .g-recaptcha {
						transform: scale(0.9);
						-webkit-transform: scale(0.9);
						transform-origin: 0 0;
						-webkit-transform-origin: 0 0;
					}

					.g-recaptcha {
						margin: 16px 0 20px 0;
					}
				</style>
				<?php
				break;
			case 'widget':
				if ( ! empty( $this->options[ $option ] ) ) {
					$this->recaptcha_here = true;

					//if ($this->options['invirecap']) $ret = '<div data-size="invisible" class="g-recaptcha" data-sitekey="' . $sitekey . '" data-callback="now_submit_the_form" id="cerber-recaptcha" data-badge="bottomright"></div>';
					if ($this->options['invirecap']) {
					    $ret = '<span class="cerber-form-marker"></span><div data-size="invisible" class="g-recaptcha" data-sitekey="' . $sitekey . '" data-callback="now_submit_the_form" id="cerber-recaptcha" data-badge="bottomright"></div>';
					}
					else $ret = '<span class="cerber-form-marker"></span><div class="g-recaptcha" data-sitekey="' . $sitekey . '" data-callback="form_button_enabler" id="cerber-recaptcha"></div>';

					//$ret = '<span class="cerber-form-marker g-recaptcha"></span>';

				}
				break;
		}
		if ( $echo ) {
			echo $ret;
			$ret = null;
		}

		return $ret;
		/*
			<script type="text/javascript">
				var onloadCallback = function() {
					//document.getElementById("wp-submit").disabled = true;
					grecaptcha.render("c-recaptcha", {"sitekey" : "<?php echo $sitekey; ?>" });
					//document.getElementById("wp-submit").disabled = false;
				};
			</script>
			<script src = "https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=<?php echo $lang; ?>" async defer></script>
			*/
	}

	/**
	 * Validate reCAPTCHA by calling Google service
	 *
	 * @param string $form  Form ID (slug)
	 * @param boolean $force Force validate without pre-checks
	 *
	 * @return bool true on success false on failure
	 */
	final public function reCaptchaValidate($form = null, $force = false) {
		if (!$force) {
			if ( ! $this->recaptcha || $this->status == 4 ) {
				return true;
			}
		}

		if ($this->recaptcha_verified != null) return $this->recaptcha_verified;

		if ( $form == 'comment' && $this->options['recapcomauth'] && is_user_logged_in()) return true;

		if ( ! $form ) {
			$form = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
		}

		$forms = array( // known pairs: form => specific plugin setting
			'lostpassword' => 'recaplost',
			'register'     => 'recapreg',
			'login'        => 'recaplogin',
			'comment'      => 'recapcom',
			'woologin'     => 'recapwoologin',
			'woolost'      => 'recapwoolost',
			'wooreg'       => 'recapwooreg',
		);

		if ( isset( $forms[ $form ] ) ) {
			if ( empty( $this->options[ $forms[ $form ] ] ) ) {
				return true; // no validation is required
			}
		}
		else {
			return true; // we don't know this form
		}

		if ( empty( $_POST['g-recaptcha-response'] ) ) {
			$this->reCaptchaFailed($form);
			return false;
		}

		$result = $this->reCaptchaRequest($_POST['g-recaptcha-response']);
		if ( ! $result ) {
			cerber_log( 42 );
			return false;
		}

		$result  = json_decode( $result );
		$result = obj_to_arr_deep( $result );

		if ( ! empty( $result['success'] ) ) {
			$this->recaptcha_verified = true;
			return true;
		}
		$this->recaptcha_verified = false;

		if ( ! empty( $result['error-codes'] ) ) {
			if ( in_array( 'invalid-input-secret', (array) $result['error-codes'] ) ) {
				cerber_log( 41 );
			}
		}

        $this->reCaptchaFailed($form);

		return false;
	}

	final function reCaptchaFailed($context = '') {
		cerber_log( 40 );
		if ($this->options['recaptcha-period'] && $this->options['recaptcha-number'] && $this->options['recaptcha-within']) {
			$remain = cerber_get_remain_count($this->remote_ip , true, array( 40 ), $this->options['recaptcha-number'], $this->options['recaptcha-within']);
			if ($remain < 1) cerber_block_add( $this->remote_ip, 5 );
		}
    }

	/**
	 * A form with possible reCAPTCHA has been submitted.
	 * Allow to process reCAPTCHA by setting a global flag.
	 * Must be called before reCaptchaValidate();
	 *
	 */
	final public function reCaptchaNow() {
		if ( cerber_is_http_post() && $this->options['sitekey'] && $this->options['secretkey'] ) {
			$this->recaptcha = true;
		}
	}

	/**
	 * Make a request to the Google reCaptcha web service
	 * 
	 * @param string $response Google specific field from the submitted form (widget)
	 *
	 * @return bool|string Response of the Google service or false on failure
	 */
	final public function reCaptchaRequest($response = ''){

		if (!$response) {
			if (!empty($_POST['g-recaptcha-response'])) $response = $_POST['g-recaptcha-response'];
			else return false;
		}

		$curl = @curl_init(); // @since 4.32
		if (!$curl) {
			cerber_admin_notice(__( 'ERROR:', 'wp-cerber' ) .' Unable to initialize cURL');
		    return false;
		}

		$opt = curl_setopt_array($curl, array(
			CURLOPT_URL => GOO_RECAPTCHA_URL,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => array( 'secret' => $this->options['secretkey'], 'response' => $response ),
			CURLOPT_RETURNTRANSFER => true,
		));

		if (!$opt) {
			cerber_admin_notice(__( 'ERROR:', 'wp-cerber' ) .' '. curl_error($curl));
			curl_close($curl);
			return false;
		}

		$result = @curl_exec($curl);
		if (!$result) {
			cerber_admin_notice(__( 'ERROR:', 'wp-cerber' ) .' '. curl_error($curl));
			$result = false;
		}
		curl_close($curl);

		return $result;

	}

	final public function reCaptchaMsg($context = null){
		return apply_filters( 'cerber_msg_recaptcha', __( 'Human verification failed. Please click the square box in the reCAPTCHA block below.', 'wp-cerber' ), $context);
	}

	final public function setLocked() {
		if ( ! isset( $this->locked ) ) {
			$this->locked = 1;
		}
	}

	final public function isLocked() {
		if ( ! empty( $this->locked ) ) {
			return 1;
		}
		return 0;
	}

	final public function deleteGarbage() {
		global $wpdb;
		if ( $this->garbage ) {
			return;
		}
		$wpdb->query( 'DELETE FROM ' . CERBER_BLOCKS_TABLE . ' WHERE block_until < ' . time() );
		$this->garbage = true;
	}
}

function cerber_init() {
	static $done = false;

	if ( $done ) {
		return;
	}

	if ( ! crb_get_settings() ) {
		cerber_load_defaults(); // Normally works on the plugins activation stage only
	}

	cerber_request_time();
	cerber_upgrade_all();

	global $wp_cerber;
	$wp_cerber = get_wp_cerber();

	cerber_beast();

	$antibot = cerber_antibot_gene();
	if ( $antibot && ! empty( $antibot[1] ) ) {
		foreach ( $antibot[1] as $item ) {
			setcookie( $item[0], $item[1], time() + 3600 * 24, cerber_get_cookie_path() );
		}
	}

	//global $wp_cerber;
	//$wp_cerber = get_wp_cerber();

	// Redirection control: no default aliases for redirections
	if ( crb_get_settings( 'noredirect' ) ) {
		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
	}

	$done = true;
}

/**
 * Returns correct WP_Cerber object
 * Protects and sets global $wp_cerber to the proper object
 *
 * @return WP_Cerber
 * @since 6.0
 */
function get_wp_cerber(){
    global $wp_cerber;
	static $the_wp_cerber = null;

	if ( ! isset( $the_wp_cerber ) ) {
		$the_wp_cerber = new WP_Cerber();
	}

	$wp_cerber = $the_wp_cerber;

	return $the_wp_cerber;
}

/**
 *
 * Initialize Cerber Security
 *
 */
add_action( 'plugins_loaded', function () {

	get_wp_cerber();

	load_plugin_textdomain( 'wp-cerber', false, basename( dirname( __FILE__ ) ) . '/languages' );

	/* @since 5.8.8
	if ( ! cerber_check_groove() && ! cerber_is_allowed() ) {
		wp_clear_auth_cookie();
	}*/

	cerber_init_cron();

	__('> > > Translator of WP Cerber? To get the PRO license for free, drop your contacts here: https://wpcerber.com/contact/','wp-cerber');

}, 1000 );

/**
 * Some additional tasks...
 *
 */
add_action( 'shutdown', function () {
	global $wpdb, $wp_cerber, $cerber_logged, $cerber_status;

	if ( empty( $cerber_logged ) ) {
		return;
	}

	// Multiple different malicious activities
	$black = crb_get_activity_set('black');
	$black_logged = array_intersect( $black, $cerber_logged );
	if ( ! empty( $black_logged ) && cerber_is_allowed() ) {
		$ip    = $wp_cerber->getRemoteIp();
		//$in    = implode( ',', $black );
		//$range = time() - 600;
		$remain = cerber_get_remain_count( $ip , true, $black); // @since 6.7.5
		if ( $remain < 1 ) {
			cerber_soft_block_add( $ip, 7 );
			$cerber_status = 18;
		}
		/*
		$count = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE ip = "' . $ip . '" AND activity IN (' . $in . ') AND stamp > ' . $range );
		if ( $count >= 5 ) {
			cerber_soft_block_add( $ip, 7 );
			$cerber_status = 18;
		}*/
	}

} );

/*
	Display login form if Custom login URL has been requested

*/
add_action( 'init', 'cerber_wp_login_page', 20 );
//add_action( 'setup_theme', 'cerber_wp_login_page' ); // @since 5.05
function cerber_wp_login_page() {
	if ( $path = crb_get_settings( 'loginpath' ) ) {
		if ( cerber_is_login_request() ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );  // @since 5.7.6
			}
			require( ABSPATH . WP_LOGIN_SCRIPT ); // load default wp-login.php form
			exit;
		}
	}
}

/**
 * Check if the current HTTP request is a login/register/lost password page request
 *
 * @return bool
 */
function cerber_is_login_request() {
    static $ret;

    if ( isset( $ret ) ) {
		return $ret;
	}

	$ret = false;

	if ( $path = crb_get_settings( 'loginpath' ) ) {
		$request = $_SERVER['REQUEST_URI'];
		if ( $pos = strpos( $request, '?' ) ) {
			$request = substr( $request, 0, $pos - 1 ); // @since 4.8
		}
		$request = explode( '/', rtrim( $request, '/' ) );
		$request = end( $request ); // @since 4.8
		if ( $path == $request && ! cerber_is_rest_url() ) {
			$ret = true;
		}
	}
    //elseif ( strtolower( cerber_parse_uri( true ) ) == WP_LOGIN_SCRIPT ) {
	elseif ( cerber_get_uri_script() == WP_LOGIN_SCRIPT ) {
		$ret = true;
	}

	return $ret;
}

/*
	Create message to show it above login form for any simply GET
*/
add_action( 'login_head', 'cerber_login_head' );
function cerber_login_head() {
	global $error, $wp_cerber;

	if ( !$allowed = cerber_is_allowed() )  :
        ?>
        <style type="text/css" media="all">
            #logidnform {
                display: none;
            }
        </style>
		<?php
	endif;

	$wp_cerber->reCaptcha( 'style' );

	if ( !cerber_is_http_get() ) {
		return;
	}
	if ( ! cerber_can_msg() ) {
		return;
	}
	if ( ! $allowed ) {
		$error = $wp_cerber->getErrorMsg();
	}
	elseif ( $msg = $wp_cerber->getRemainMsg() ) {
		$error = $msg;
	}
}

/**
 * Control the process of authentication
 *
 * @since 2.9
 *
 */
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
add_filter( 'authenticate', 'cerber_auth_control', 20, 3 );
function cerber_auth_control( $user, $username, $password ) {
	global $wp_cerber;

	if ( ! $wp_cerber->reCaptchaValidate() ) {

		return new WP_Error( 'incorrect_recaptcha',
			'<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' .
			$wp_cerber->reCaptchaMsg('login'));
	}

	// Check for prohibited username
	//if ( $wp_cerber->isProhibited( $username ) ) {
	if ( cerber_is_prohibited( $username ) ) {
		cerber_log( 52, $username );
		cerber_block_add( null, 4, $username );

		// Create with message that is identical default WP
		return new WP_Error( 'incorrect_password', sprintf(
			__( '<strong>ERROR</strong>: The password you entered for the username %s is incorrect.' ),
			'<strong>' . $username . '</strong>'
		) );
	}
/*
	if ( lab_is_blocked($wp_cerber->getRemoteIp()) ) {

		// Create with message which is identical default WP
		return new WP_Error( 'incorrect_password', sprintf(
			__( '<strong>ERROR</strong>: The password you entered for the username %s is incorrect.' ),
			'<strong>' . $username . '</strong>'
		) );
	}*/

	$user = wp_authenticate_username_password( $user, $username, $password );
	$user = wp_authenticate_email_password( $user, $username, $password );

	// @since 4.18 it is replacement for 'wp_login_failed' action hook
	// see WP function wp_authenticate()
	$ignore_codes = array( 'empty_username', 'empty_password', 'cerber_denied' );
	if ( is_wp_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes ) ) {
		cerber_login_failed( $username );
	}

	return $user;
}

/*
	Block authentication for an existing user if the remote IP is not allowed.
    Invoking in the 'wp_authenticate_username_password()'
*/
add_filter( 'wp_authenticate_user', 'cerber_stop_authentication', 9999, 2 ); // fires after user found, with 'authenticate' filter
function cerber_stop_authentication( $user, $password ) {
	global $wp_cerber, $cerber_status;

	$deny = false;

	if ( ! cerber_is_allowed() ) {
		$deny = true;
	}
	elseif ( ! cerber_geo_allowed( 'geo_login' ) ) {
	    $cerber_status = 16;
		$deny = true;
	}
    elseif ( lab_is_blocked( $wp_cerber->getRemoteIp() ) ) {
		$cerber_status = 15;
		$deny = true;
	}

	if ( $deny ) {
		status_header( 403 );
		$error = new WP_Error();
		$error->add( 'cerber_wp_error', $wp_cerber->getErrorMsg() );

		return $error;
	}

	return $user;
}

/*
 * Handler for failed login attempts
 *
 */
//add_action( 'wp_login_failed', 'cerber_login_failed' ); // @since 4.18
function cerber_login_failed( $user_login ) {
	global $wpdb, $wp_cerber, $cerber_status;
	static $is_processed = false;

	if ( $is_processed ) return;
	$is_processed = true;

	$ip      = $wp_cerber->getRemoteIp();
	$acl     = cerber_acl_check( $ip );
	if ( ! cerber_get_user( $user_login ) ) {
		$no_user = true;
	}
	else {
		$no_user = false;
    }

	//cerber_failed_work($ip, $acl, $no_user, $user_login);

	//if ( ! $wp_cerber->isProcessed() ) {
		//if ( ! cerber_get_user( $user_login ) ) {
		//	$no_user = true;
		//}

	$ac = 7;

	if ( $no_user ) {
		$ac = 51;
	}
	elseif ( ! cerber_is_allowed( $ip ) || $cerber_status == 15 || $cerber_status == 16 ) { // TODO should be refactored together with cerber_stop_authentication
		$ac = 53;
	}
	/*
	elseif ( $acl == 'B' ) {
		$ac = 14;
	}
	elseif ( lab_is_blocked($ip, false) ) {
		$ac = 53;
	}
	else {
		$ac = 53;
	}*/

	cerber_log( $ac, $user_login );

	//}


	// White? Stop further actions.
	if ( $acl == 'W' && !crb_get_settings( 'limitwhite' )) {
		return;
	}

	if ( crb_get_settings( 'usefile' ) ) {
		cerber_file_log( $user_login, $ip );
	}

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) { // Needs additional researching and, maybe, refactoring
		status_header( 403 );
	}

	// Blacklisted? No more actions are needed.
	if ( $acl == 'B' ) {
		return;
	}

	// Must the Citadel mode be activated?
	if ( $wp_cerber->getSettings( 'ciperiod' ) && ! cerber_is_citadel() ) {
		$range    = time() - $wp_cerber->getSettings( 'ciperiod' ) * 60;
		$lockouts = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE activity IN (7,51,52) AND stamp > ' . $range );
		if ( $lockouts >= $wp_cerber->getSettings( 'cilimit' ) ) {
			cerber_enable_citadel();
		}
	}

    if ( $no_user && $wp_cerber->getSettings( 'nonusers' ) ) {
		cerber_block_add( $ip, 3, $user_login);  // @since 5.7
	}
	elseif ( cerber_get_remain_count($ip, false) < 1 ) { //Limit on the number of login attempts is reached
		cerber_block_add( $ip, 1, '', null, false);
	}

}

/**
 * Do the work with failed/blocked attempt
 *
 * @param $ip
 * @param $acl
 * @param $user_login
 */
function cerber_failed_work($ip, $acl, $no_user, $user_login){
	global $wpdb, $wp_cerber;

	// White? Stop further actions.
	if ( $acl == 'W' && !$wp_cerber->getSettings( 'limitwhite' )) {
		return;
	}

	if ( $wp_cerber->getSettings( 'usefile' ) ) {
		cerber_file_log( $user_login, $ip );
	}

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) { // Needs additional researching and, maybe, refactoring
		status_header( 403 );
	}

	// Blacklisted? No more actions are needed.
	if ( $acl == 'B' ) {
		return;
	}

	// Must the Citadel mode be activated?
	if ( $wp_cerber->getSettings( 'ciperiod' ) && ! cerber_is_citadel() ) {
		$range    = time() - $wp_cerber->getSettings( 'ciperiod' ) * 60;
		$lockouts = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE activity IN (7,51,52) AND stamp > ' . $range );
		if ( $lockouts >= $wp_cerber->getSettings( 'cilimit' ) ) {
			cerber_enable_citadel();
		}
	}

	if ( $no_user && $wp_cerber->getSettings( 'nonusers' ) ) {
		cerber_block_add( $ip, 3, $user_login );
	}
    elseif ( cerber_get_remain_count($ip, false) <= 1 ) { //Limit on the number of login attempts is reached
		cerber_block_add( $ip, 1, '', null, false);
	}
}

// Registration -----------------------------------------------------------------------

function cerber_is_registration_prohibited( $sanitized_user_login ) {
	global $wp_cerber, $cerber_status;

	$code = null;
	$msg  = null;

	if (crb_is_reg_limit_reached()){
		$cerber_status = 17;
		cerber_log(54);
		$code = 'ip_denied';
		$msg = '<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' .
		       apply_filters( 'cerber_msg_denied', __( 'You are not allowed to register.', 'wp-cerber' ), 'register' );
    }
	elseif ( cerber_is_bot('botsreg') ) {
		cerber_log(54); // TODO should be separate code to detect bot activity?
		$code = 'bot_detected';
		$msg = '<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' .
		       apply_filters( 'cerber_msg_denied', __( 'You are not allowed to register.', 'wp-cerber' ), 'register' );
	}
    elseif ( ! $wp_cerber->reCaptchaValidate() ) {
		$code = 'incorrect_recaptcha';
		$msg = '<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' .
		       $wp_cerber->reCaptchaMsg('register');
	}
    elseif ( cerber_is_prohibited( $sanitized_user_login ) ) {
		$code = 'prohibited_login';
		$msg =	'<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' .
		          apply_filters( 'cerber_msg_prohibited', __( 'Username is not allowed. Please choose another one.', 'wp-cerber' ), 'register' );
	}
    elseif ( !cerber_is_allowed() || lab_is_blocked($wp_cerber->getRemoteIp()) ) {
		cerber_log(54);
		$code = 'ip_denied';
		$msg = '<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' .
		       apply_filters( 'cerber_msg_denied', __( 'You are not allowed to register.', 'wp-cerber' ), 'register' );
	}
    elseif ( ! cerber_geo_allowed( 'geo_register' ) ) {
		$cerber_status = 16; // TODO: refactor cerber_log, include this status as a second parameter
		cerber_log(54); // TODO should be separate code?
		$code = 'country_denied';
		$msg = '<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' .
		       apply_filters( 'cerber_msg_denied', __( 'You are not allowed to register.', 'wp-cerber' ), 'register' );
	}

	if ( $code ) {
		return array( $code, $msg );
	}

	return null;
}

/**
 * Limit on user registrations per IP
 *
 * @return bool
 */
function crb_is_reg_limit_reached() {
	global $wpdb, $wp_cerber;

	if ( ! $wp_cerber->getSettings( 'reglimit_min' ) || ! $wp_cerber->getSettings( 'reglimit_num' ) ) {
		return false;
	}

	if ( crb_acl_is_white() ) {
		return false;
	}

	$ip    = $wp_cerber->getRemoteIp();
	$stamp = absint( time() - 60 * $wp_cerber->getSettings( 'reglimit_min' ) );
	$count = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE ip = "' . $ip . '" AND activity = 2 AND stamp > ' . $stamp );
	if ( $count > $wp_cerber->getSettings( 'reglimit_num' ) ) {
		lab_save_push( $ip, 344, '' );
		return true;
	}

	return false;
}

// Fires in register_new_user()
add_filter( 'registration_errors', 'cerber_pre_new_user', 10, 3 );
function cerber_pre_new_user( $errors, $sanitized_user_login, $user_email ) {
	global $wp_cerber, $cerber_status;

	$prohibited = cerber_is_registration_prohibited( $sanitized_user_login );

	if ($prohibited){
		return new WP_Error($prohibited[0], $prohibited[1]);
    }

	return $errors;
}

// @since 5.3
// Fires in wp_insert_user()
add_filter( 'pre_user_login', function ( $sanitized_user_login ) {

	if ( cerber_is_registration_prohibited( $sanitized_user_login ) ) {
		return null;
	}

	/*
		if ( ! cerber_is_allowed() ) {
			cerber_log( 54 );

			return null;
		}
		if ( ! cerber_geo_allowed( 'geo_register' ) ) {
			$cerber_status = 16;
			cerber_log( 54 );

			return null;
		}
	*/
	return $sanitized_user_login;
}, 9999 );

// Filter out prohibited usernames
add_filter( 'illegal_user_logins', function () {
	return (array) crb_get_settings( 'prohibited' );
}, 9999 );

add_filter( 'option_users_can_register', function ( $value ) {
	//if ( ! cerber_is_allowed() || !cerber_geo_allowed( 'geo_register' )) {
	if ( ! cerber_is_allowed() || crb_is_reg_limit_reached() ) {
		return false;
	}

	return $value;
}, 9999 );

// Lost password form --------------------------------------------------------------------

/**
 * Validate reCAPTCHA for the WordPress lost password form
 */
add_action( 'login_form_' . 'lostpassword', 'cerber_lost_captcha' );
function cerber_lost_captcha() {
	global $wp_cerber, $cerber_lost;
	if ( ! $wp_cerber->reCaptchaValidate() ) {
		$_POST['user_login'] = null; // workaround due to lack of any way to control lost password form
		$cerber_lost = '<strong>' . __( 'ERROR:', 'wp-cerber' ) . ' </strong>' . $wp_cerber->reCaptchaMsg('lostpassword');
	}
}
/**
 * Display message on the WordPress lost password form screen
 */
add_action( 'lostpassword_form', 'cerber_lost_show_msg' );
function cerber_lost_show_msg() {
	global $cerber_lost;
	if ( ! $cerber_lost ) {
		return;
	}
	?>
	<script type="text/javascript">
		//document.getElementById('login_error').style.visibility = "hidden";
		document.getElementById('login_error').innerHTML = "<?php echo $cerber_lost; ?>";
	</script>
	<?php
}


// Comments (commenting) section ----------------------------------------------------------

/**
 * If a comment must be marked as spam
 *
 */
add_filter( 'pre_comment_approved', function ( $approved, $commentdata ) {
	if ( 1 == crb_get_settings( 'spamcomm' ) && ! cerber_is_comment_allowed() ) {
		$approved = 'spam';
	}

	return $approved;
}, 10, 2 );

/**
 * If a comment must be denied
 *
 */
add_action( 'pre_comment_on_post', function ( $comment_post_ID ) {
	global $cerber_status;

	$deny = false;

	if ( 1 != crb_get_settings( 'spamcomm' ) && ! cerber_is_comment_allowed() ) {
		$deny = true;
	}
	elseif ( ! cerber_geo_allowed( 'geo_comment' ) ) {
		$cerber_status = 16;
	    cerber_log(19);
		$deny = true;
	}

	if ( $deny ) {
		setcookie( 'cerber-post-id', $comment_post_ID, time() + 60, '/' );
		$comments = get_comments( array( 'number' => '1', 'post_id' => $comment_post_ID ) );
		if ( $comments ) {
			$loc = get_comment_link( $comments[0]->comment_ID );
		} else {
			$loc = get_permalink( $comment_post_ID ) . '#cerber-recaptcha-msg';
		}
		wp_safe_redirect( $loc );
		exit;
	}

} );

/**
 * If submit comments via REST API is not allowed
 *
 */
add_filter( 'rest_allow_anonymous_comments', function ( $allowed, $request ) {
    global $wp_cerber, $cerber_status;

	if ( ! cerber_is_allowed() ) {
		$allowed = false;
	}
    if ( ! cerber_geo_allowed( 'geo_comment' ) ) {
	    ceber_log(19);
	    $cerber_status = 16;
	    $allowed = false;
	}
    elseif ( lab_is_blocked( $wp_cerber->getRemoteIp() ) ) {
	    $allowed = false;
	}

	return $allowed;
}, 10, 2 );

/**
 * Check if a submitted comment is allowed
 *
 * @return bool
 */
function cerber_is_comment_allowed(){
	global $wp_cerber;

	if (is_admin()) return true;

	$deny = null;

	if ( ! cerber_is_allowed() ) {
		$deny = 19;
	}
    elseif ( cerber_is_bot('botscomm') ) {
	    $remain = cerber_get_remain_count( null, true, array( 16 ), 3, 60 );
	    if ($remain < 1) cerber_block_add( null, 6, '', 60 );
	    $deny = 16;
	}
    elseif ( ! $wp_cerber->reCaptchaValidate( 'comment' , true ) ) {
		$deny = 16;
	}
    elseif ( lab_is_blocked( $wp_cerber->getRemoteIp() ) ) {
		$deny = 19;
	}

	if ( $deny ) {
		cerber_log( $deny );
		$ret = false;
	}
	else {
		$ret = true;
	}

	return $ret;
}

/**
 * Showing reCAPTCHA widget.
 * Displaying error message on the comment form for a human.
 *
 */
add_filter( 'comment_form_submit_field', function ( $value ) {
	global $wp_cerber, $post;

	if ( ! empty( $_COOKIE["cerber-post-id"] ) && absint( $_COOKIE["cerber-post-id"] ) == $post->ID ) {
		//echo '<div id="cerber-recaptcha-msg">' . __( 'ERROR:', 'wp-cerber' ) . ' ' . $wp_cerber->reCaptchaMsg( 'comment' ) . '</div>';
		echo '<div id="cerber-recaptcha-msg">' . __( 'ERROR:', 'wp-cerber' ) . ' ' . __('Sorry, human verification failed.') . '</div>';
		echo '<script type="text/javascript">document.cookie = "cerber-post-id=0;path=/";</script>';
	}

	$au = $wp_cerber->getSettings( 'recapcomauth' );
	if ( ! $au || ( $au && ! is_user_logged_in() ) ) {
		$wp_cerber->reCaptcha( 'widget', 'recapcom' );
	}

	return $value;
} );


// Messages ----------------------------------------------------------------------

/**
 * Replace ANY system messages or add notify message above login form if IP is not allowed (blocked or locked out)
 */
add_filter( 'login_errors', 'cerber_login_form_msg' ); // hook on POST if credentials was wrong
function cerber_login_form_msg( $errors ) {
	global $error, $wp_cerber;
	if ( cerber_can_msg() ) {
		if ( ! cerber_is_allowed() ) {
			$errors = $wp_cerber->getErrorMsg();
		}
        elseif ( ! $error && ( $msg = $wp_cerber->getRemainMsg() ) ) {
			$errors .= '<p>' . $msg;
		}
	}

	return $errors;
}

add_filter( 'shake_error_codes', 'cerber_login_failure_shake' ); // Shake it, baby!
function cerber_login_failure_shake( $shake_error_codes ) {
	$shake_error_codes[] = 'cerber_wp_error';

	return $shake_error_codes;
}

/*
	Replace default login/logout URL with Custom login page URL
*/
add_filter( 'site_url', 'cerber_login_logout', 9999, 4 );
add_filter( 'network_site_url', 'cerber_login_logout', 9999, 3 );
function cerber_login_logout( $url, $path, $scheme, $blog_id = 0 ) { // $blog_id only for 'site_url'
	global $wp_cerber;
	if ( $login_path = $wp_cerber->getSettings( 'loginpath' ) ) {
		$url = str_replace( WP_LOGIN_SCRIPT, $login_path . '/', $url );
	}

	return $url;
}

/*
	Replace default logout redirect URL with Custom login page URL 
*/
add_filter( 'wp_redirect', 'cerber_login_redirect', 9999, 2 );
function cerber_login_redirect( $location, $status ) {
	global $wp_cerber;
	if ( ($path = $wp_cerber->getSettings( 'loginpath' )) && ( 0 === strpos( $location, WP_LOGIN_SCRIPT . '?' ) ) ) {
		$loc      = explode( '?', $location );
		$location = get_home_url() . '/' . $path . '/?' . $loc[1];
	}

	return $location;
}

// Access control ========================================================================================

add_action( 'init', function () {
	if ( crb_get_settings( 'adminphp' ) && ! is_user_logged_in() ) {
		define( 'CONCATENATE_SCRIPTS', false );
	}
	cerber_access_control();
	cerber_antibot_control();
}, 0 );

/**
 * Restrict access to vital parts of WP
 *
 */
function cerber_access_control() {
	global $wp_cerber, $cerber_status;

	if ( is_admin() ) {
		return;
	}

	// IPs from the White List are allowed
	$acl = cerber_acl_check();
	if ( $acl == 'W' ) {
		return;
	}

	if ( $acl == 'B' || !cerber_is_allowed() ) {
		$deny = true;
	}
	else {
		$deny = false;
	}

	//$opt    = $wp_cerber->getSettings();
	$opt = crb_get_settings();
/*
	$script = cerber_last_uri();
	if ( substr( $script, - 4 ) != '.php' ) {
		$script .= '.php'; // Apache MultiViews enabled?
	}

	if ( $script ) {
		if ( $script == WP_LOGIN_SCRIPT || $script == WP_SIGNUP_SCRIPT || ( $script == WP_REG_URI && ! get_option( 'users_can_register' ) ) ) { // no direct access
			if ( !empty( $opt['wplogin'] ) ) {
				cerber_log( 50 );
				cerber_soft_block_add( $wp_cerber->getRemoteIp(), 2, $script );
				cerber_404_page();
			}
			if ( $deny || !empty( $opt['loginnowp'] ) ) {
				cerber_log( 50 );
				cerber_404_page();
			}
		}
		elseif ( $script == WP_XMLRPC_SCRIPT || $script == WP_TRACKBACK_SCRIPT ) { // no direct access
			if ( $deny || ! empty( $opt['xmlrpc'] ) ) {
				cerber_log( 50 );  // @since 5.21
				cerber_404_page();
			}
			elseif ( !cerber_geo_allowed( 'geo_xmlrpc' ) ) {
				$cerber_status = 16;
				cerber_log( 71 );
				cerber_404_page();
			}
		}
	}
*/
	$wp_cerber = get_wp_cerber();
	if ( $wp_cerber->isURIProhibited() ) {
		cerber_404_page();
	}

	// REST API
	if ( $deny ) {
		cerber_block_rest();
	}
    elseif ( cerber_is_rest_url() ) {
	    $rest_allowed = true;
	    if ( ! empty( $opt['norest'] ) ) {
		    $rest_allowed = false;
		    if ( $opt['restauth'] && is_user_logged_in() ) {
			    $rest_allowed = true;
		    }
            elseif ( cerber_is_route_allowed() ) {
	            $rest_allowed = true;
		    }
	    }
	    /*
		$rest_allowed = true;
		if ( ! empty( $opt['norest'] ) && ! cerber_is_route_allowed() ) {
			$rest_allowed = false;
		}*/
		if ( $rest_allowed && cerber_is_route_blocked() ) {
			$rest_allowed = false;
		}
		if ( $rest_allowed && ! cerber_geo_allowed( 'geo_restapi' ) ) {
			$rest_allowed  = false;
			$cerber_status = 16;
		}
		if ( ! $rest_allowed ) {
			cerber_block_rest();
		}
	}

    // Some XML-RPC stuff
	if ( $deny || ! empty( $opt['xmlrpc'] ) ) {
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'pings_open', '__return_false' );
		add_filter( 'bloginfo_url', 'cerber_pingback_url', 10, 2 );
		remove_action( 'wp_head', 'rsd_link', 10 );
		remove_action( 'wp_head', 'wlwmanifest_link', 10 );
	}

	// Feeds
	if ( $deny || ! empty( $opt['nofeeds'] ) ) {
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );

		remove_action( 'do_feed_rdf', 'do_feed_rdf', 10 );
		remove_action( 'do_feed_rss', 'do_feed_rss', 10 );
		remove_action( 'do_feed_rss2', 'do_feed_rss2', 10 );
		remove_action( 'do_feed_atom', 'do_feed_atom', 10 );
		remove_action( 'do_pings', 'do_all_pings', 10 );

		add_action( 'do_feed_rdf', 'cerber_404_page', 1 );
		add_action( 'do_feed_rss', 'cerber_404_page', 1 );
		add_action( 'do_feed_rss2', 'cerber_404_page', 1 );
		add_action( 'do_feed_atom', 'cerber_404_page', 1 );
		add_action( 'do_feed_rss2_comments', 'cerber_404_page', 1 );
		add_action( 'do_feed_atom_comments', 'cerber_404_page', 1 );
	}
}

/**
 * Antispam & Antibot for forms
 *
 */
function cerber_antibot_control(){
    global  $cerber_status;

	if ( !cerber_is_http_post() || cerber_acl_check( null, 'W' ) ) {
		return;
	}

	if ( !cerber_antibot_enabled('botsany') && !cerber_geo_rules('geo_submit')) {
		return;
	}

	// Exceptions -----------------------------------------------------------------------

	if ( cerber_is_antibot_exception() ) {
		return;
	}

	// Let's make the checks

	$deny = false;

	if ( ! cerber_is_allowed() ) {
		$deny = true;
		cerber_log(18);
	}
	elseif ( cerber_is_bot( 'botsany' ) ) {
		$deny = true;
		cerber_log( 17 );
	}
	elseif ( !cerber_geo_allowed( 'geo_submit' ) ) {
		$deny = true;
		$cerber_status = 16; // TODO: refactor cerber_log, include this status as a second parameter
		cerber_log( 18 );
	}
	elseif ( lab_is_blocked( null, true ) ) {
		$deny = true;
		$cerber_status = 18;
		cerber_log( 18 );
	}

	if ($deny){
		cerber_forbidden_page();
	}

}

/**
 * Exception for POST request control
 *
 * @return bool
 */
function cerber_is_antibot_exception(){

    if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
	    return true;
    }

	// Admin || AJAX requests by unauthorized users
	if ( is_admin() ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if (is_user_logged_in()) {
				return true;
			}
		}
		else {
			return true;
		}
	}

    // Standard WordPress Comments
	if ( 0 === strpos( trim( $_SERVER['REQUEST_URI'], '/' ), 'wp-comments-post.php' ) ) {
		return true;
	}

	// XML-RPC
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		return true;
	}

	// Trackback
	if ( is_trackback() ) {
		return true;
	}

	// Login page
	if ( cerber_is_login_request() ) {
		return true;
	}

	// REST API (except Contact Form 7 submission)
	if ( cerber_is_rest_url() ) {
		if ( false === strpos( $_SERVER['REQUEST_URI'], 'contact-form-7' ) ) {
			return true;
		}
	}

	if ( class_exists( 'WooCommerce' ) ) {
		if ( cerber_is_permalink_enabled() ) {
			if ( 0 === strpos( cerber_get_site_root() . cerber_purify_uri(), get_home_url() . '/wc-api/' ) ) {
				return true;
			}
		}
        elseif ( ! empty( $_GET['wc-api'] ) ) {
			if ( cerber_check_remote_domain( array( '*.paypal.com', '*.stripe.com' ) ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * What antibot mode must be used.
 *
 * @return int 1 = Cookies + Fields, 2 = Cookies only
 */
function cerber_antibot_mode() {
    global $wp;
	//static $list;

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		if ( crb_get_settings( 'botssafe' ) ) {
			return 2;
		}
		if ( ! empty( $_POST['action'] ) ) {
			if ( $_POST['action'] == 'heartbeat' ) { // WP heartbeat
				//$nonce_state = wp_verify_nonce( $_POST['_nonce'], 'heartbeat-nonce' );
				return 2;
			}
		}

	}

	// Check for third party exceptions

	if ( class_exists( 'WooCommerce' ) ) {

		if ( !empty( $_GET['wc-ajax'] ) &&
		     $_GET['wc-ajax'] == 'get_refreshed_fragments' &&
		     count( $_GET ) == 1 &&
		     empty( $_POST )
		) {

			return 2;
		}

		if ( cerber_is_permalink_enabled() ) {
			if ( function_exists( 'wc_get_page_id' ) && 0 === strpos( cerber_get_site_root() . cerber_purify_uri(), get_permalink( wc_get_page_id( 'checkout' ) ) ) ) {
				return 2;
			}
		}
		else {
			if ( ! empty( $_GET['order-received'] ) && ! empty( $_GET['key'] ) ) {
				return 2;
			}
		}
	}

	if ( class_exists( 'GFForms' ) ) {
		if ( !empty( $_GET['gf_page'] ) &&
		     count( $_POST ) == 4 &&
		     count( $_FILES ) == 1 &&
		     ! empty( $_POST['name'] ) &&
		     ! empty( $_POST['form_id'] ) &&
		     ! empty( $_POST['field_id'] ) &&
		     ! empty( $_POST['gform_unique_id'] )
		) {

			return 2;
		}
	}

	return 1;
}

/*
 * Disable pingback URL (hide from HEAD)
 */
function cerber_pingback_url( $output, $show ) {
	if ( $show == 'pingback_url' ) {
		$output = '';
	}

	return $output;
}

/**
 * Disable REST API
 *
 */
function cerber_block_rest() {
	global $wp_version;
	// OLD WP
	add_filter( 'json_enabled', '__return_false' );
	add_filter( 'json_jsonp_enabled', '__return_false' );
	// WP 4.4, deprecated since 4.7
	if ( version_compare( $wp_version, '4.7', '<' ) ) {
		add_filter( 'rest_enabled', '__return_false', 9999 );
	}
	// WP 4.7
	add_filter( 'rest_jsonp_enabled', '__return_false' );
	// Links
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	// Default REST API hooks from default-filters.php
	remove_action( 'init', 'rest_api_init' );
	remove_action( 'rest_api_init', 'rest_api_default_filters', 10 );
	remove_action( 'rest_api_init', 'register_initial_settings', 10 );
	remove_action( 'rest_api_init', 'create_initial_rest_routes', 99 );
	remove_action( 'parse_request', 'rest_api_loaded' );

	if ( cerber_is_rest_url() ) {
		cerber_log( 70 );
		//cerber_404_page();
        cerber_forbidden_page(); // @since 6.0
	}
}

/*
 * Redirection control: standard admin/login redirections
 *
 */
add_filter( 'wp_redirect', 'cerber_no_redirect', 10, 2 );
function cerber_no_redirect( $location, $status ) {
	global $current_user, $wp_cerber;
	if ( (!$current_user || $current_user->ID == 0) && $wp_cerber->getSettings( 'noredirect' ) ) {
		$str = urlencode( '/wp-admin/' );
		$rdr = explode('redirect_to=',$location);
		if ( isset($rdr[1]) && strpos( $rdr[1], $str ) ) {
			cerber_404_page();
		}
	}

	return $location;
}

/*
 * Stop user enumeration
 *
 */
add_action( 'template_redirect', 'cerber_canonical', 1 );
function cerber_canonical() {
	if ( crb_get_settings( 'stopenum' ) ) {
		if ( ! is_admin() && ! empty( $_GET['author'] ) ) {
			cerber_404_page();
		}
	}
}
/*
if ( $wp_cerber->getSettings( 'hashauthor' ) ) {
	add_filter( 'request',
		function ( $vars ) {
			if (isset($vars['author_name']) && !is_admin()) {
				$vars['author_name'] = '><';
			}

			return $vars;
		} );
}
*/

/*
	Can login form message be shown?
*/
function cerber_can_msg() {
	if ( ! isset( $_REQUEST['action'] ) ) {
		return true;
	}
	if ( $_REQUEST['action'] == 'login' ) {
		return true;
	}

	return false;
	//if ( !in_array( $action, array( 'postpass', 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', 'login' );
}


// Cookies ---------------------------------------------------------------------------------
/*
	Mark user with Cerber Groove
	@since 1.3
*/
add_action( 'auth_cookie_valid', 'cerber_cookie1', 10, 2 );
function cerber_cookie1( $cookie_elements = null, $user = null ) {
	global $current_user;
	if ( ! $user ) {
		$user = wp_get_current_user();
	}
	$expire = time() + apply_filters( 'auth_cookie_expiration', 14 * 24 * 3600, $user->ID, true ) + ( 24 * 3600 );
	cerber_set_groove( $expire );
}

/*
	Mark switched user with Cerber Groove
	@since 1.6
*/
add_action( 'set_logged_in_cookie', 'cerber_cookie2', 10, 5 );
function cerber_cookie2( $logged_in_cookie, $expire, $expiration, $user_id, $logged_in ) {
	cerber_set_groove( $expire );
}

/*
	Track BAD cookies with non-existence user or bad password (hash)
*/
add_action( 'auth_cookie_bad_username', 'cerber_cookie_bad' );
add_action( 'auth_cookie_bad_hash', 'cerber_cookie_bad' );
function cerber_cookie_bad( $cookie_elements ) {
	cerber_login_failed( $cookie_elements['username'] );
}

/**
 * Is bot detection engine enabled in a given rule_id
 *
 * @param $location string|array  ID of the location
 *
 * @return bool true if enabled
 */
function cerber_antibot_enabled( $location ) {

	if ( crb_get_settings( 'botsnoauth' ) && is_user_logged_in() ) {
		return false;
	}

	if ( is_array( $location ) ) {
		foreach ( $location as $loc ) {
			if ( crb_get_settings( $loc ) ) {
				return true;
			}
		}
	}
	else {
		if ( crb_get_settings( $location ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Print out the antibot/antispam jQuery code
 *
 * @param $location string|array Location (setting)
 */
function cerber_antibot_code($location) {
	if ( ! cerber_antibot_enabled( $location ) ) {
		return;
	}

	$values = cerber_antibot_gene();

	if ( empty( $values ) || !is_array( $values ) ) {
		return;
	}

	?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            //$( document ).ajaxStart(function() {
            //});

	        <?php // Append form fields directly to the all forms ?>

            for (var i = 0; i < document.forms.length; ++i) {
                var form = document.forms[i];
	            <?php
	            foreach ( $values[0] as $value ) {
		            echo 'if ($(form).attr("method") != "get") { $(form).append(\'<input type="hidden" name="' . $value[0] . '" value="' . $value[1] . '" />\'); }'."\n";
	            }
	            ?>
            }

            <?php // Ordinary submit ?>

            $(document).on('submit', 'form', function () {
		        <?php
                foreach ( $values[0] as $value ) {
			        echo 'if ($(this).attr("method") != "get") { $(this).append(\'<input type="hidden" name="' . $value[0] . '" value="' . $value[1] . '" />\'); }'."\n";
		        }
		        ?>
                return true;
            });

	        <?php // Pure AJAX submit with two different types of form data (object and string) ?>

            jQuery.ajaxSetup({
                beforeSend: function (e, data) {

                    //console.log(Object.getOwnPropertyNames(data).sort());
                    //console.log(data.type);

                    if (data.type !== 'POST') return;

                    if (typeof data.data === 'object' && data.data !== null) {
	                    <?php
	                    foreach ( $values[0] as $value ) {
		                    echo 'data.data.append("' . $value[0] . '", "' . $value[1] . '");'."\n";
	                    }
	                    ?>
                    }
                    else {
                        data.data =  data.data + '<?php
		                        foreach ( $values[0] as $value ) {
			                        echo '&' . $value[0] . '=' . $value[1];
		                        }
                                ?>';
                    }
                }
            });

        });
    </script>
	<?php

}

/**
 * Generates and saves antibot markers
 *
 * @return array|bool
 */
function cerber_antibot_gene($recreate = false) {

	if ( !crb_get_settings('botsany') && !crb_get_settings('botscomm') && !crb_get_settings('botsreg') ) {
		return false;
	}

	$ret = array();

	if (!$recreate) {
	    $ret = cerber_get_site_option( 'cerber-antibot' );
	}

	if ( $recreate || !$ret ) {

	    $ret = array();

		$max = rand( 2, 4 );
		for ( $i = 1; $i <= $max; $i ++ ) {
			$length   = rand( 6, 16 );
			$string1  = str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_-' );
			$string2  = str_shuffle( '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.@*_[]' );
			$ret[0][] = array( substr( $string1, 0, $length ), substr( $string2, 0, $length ) );
		}

		$max = rand( 2, 4 );
		for ( $i = 1; $i <= $max; $i ++ ) {
			$length   = rand( 6, 16 );
			$string1  = str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_-' );
			$string2  = str_shuffle( '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.@*_[]' );
			$ret[1][] = array( substr( $string1, 0, $length ), substr( $string2, 0, $length ) );
		}

		update_site_option( 'cerber-antibot', $ret );
	}

	return $ret;
}

/**
 * Is a POST request (a form) submitted by a bot?
 *
 * @param $location string identificator of a place where we are check for a bot
 *
 * @return bool
 */
function cerber_is_bot( $location = '' ) {
	global $wp_cerber, $cerber_status;
	static $ret = null;

	if ( isset( $ret ) ) {
		return $ret;
	}

	if ( ! $location || ! cerber_is_http_post() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		$ret = false;

		return $ret;
	}

	// Admin || AJAX requests by unauthorized users
	if ( is_admin() ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            if ( is_user_logged_in() ) {
				$ret = false;
			}
            elseif ( ! empty( $_POST['action'] ) ) {
				if ( $_POST['action'] == 'heartbeat' ) { // WP heartbeat
					//$nonce_state = wp_verify_nonce( $_POST['_nonce'], 'heartbeat-nonce' );
					$ret = false;
				}
			}
		}
		else {
			$ret = false;
		}
	}

	if ($ret !== null) {
	    return $ret;
    }

	if ( ! cerber_antibot_enabled( $location ) ) {
		$ret = false;

		return $ret;
	}

	// Antibot whitelist
	if ( ( $list = crb_get_settings( 'botswhite' ) ) && is_array( $list ) ) {
		foreach ( $list as $item ) {
			if ( substr( $item, 0, 1 ) == '{' && substr( $item, - 1 ) == '}' ) {
				$pattern = '/' . substr( $item, 1, - 1 ) . '/i';
				if ( @preg_match( $pattern, '/' . trim( $_SERVER['REQUEST_URI'], '/' ) ) ) {
					$ret = false;
					return $ret;
				}
			}
			elseif ( false !== strpos( '/' . trim( $_SERVER['REQUEST_URI'], '/' ), $item ) ) {
				$ret = false;
				return $ret;
			}
		}
	}

	$antibot = cerber_antibot_gene();

	$ret = false;

	if ( ! empty( $antibot ) ) {

		$mode = cerber_antibot_mode();

		if ( $mode == 1 ) {
			foreach ( $antibot[0] as $fields ) {
				if ( empty( $_POST[ $fields[0] ] ) || $_POST[ $fields[0] ] != $fields[1] ) {
					$ret = true;
					break;
				}
			}
		}

		if ( ! $ret ) {
			foreach ( $antibot[1] as $fields ) {
				if ( empty( $_COOKIE[ $fields[0] ] ) || $_COOKIE[ $fields[0] ] != $fields[1] ) {
					$ret = true;
					break;
				}
			}
		}

		if ( $ret ) {
			$cerber_status = 11;
			lab_save_push( $wp_cerber->getRemoteIp(), 333, '' );
		}
	}

	return $ret;
}

function cerber_geo_allowed( $rule_id = '' ) {

	if ( !$rule_id || ( defined( 'DOING_CRON' ) && DOING_CRON ) || !lab_lab() ) {
		return true;
	}

	$tag = cerber_acl_check();
	if ( $tag == 'W' ) {
		return true;
	}

	$rule = cerber_geo_rules( $rule_id );
	$wp_cerber = get_wp_cerber();
	if ( $rule && $country = lab_get_country( $wp_cerber->getRemoteIp(), false ) ) {
		if ( in_array( $country, $rule['list'] ) ) {
			if ( $rule['type'] == 'W' ) {
				return true;
			}

			return false;
		}
		if ( $rule['type'] == 'W' ) {
			return false;
		}

		return true;
	}

	return true;
}

/**
 * Retrieve and return GEO rule(s) from the DB
 *
 * @param string $rule_id   Slug of a rule
 *
 * @return bool|array If a rule exists return array of countries
 */
function cerber_geo_rules( $rule_id = '' ) {
	global $wpdb;

	if (is_multisite()) {
		$geo = $wpdb->get_var( 'SELECT meta_value FROM ' . $wpdb->sitemeta . ' WHERE meta_key = "geo_rule_set"' );
    }
    else {
	    $geo = $wpdb->get_var( 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name = "geo_rule_set"' );
    }

	if ( $geo ) {
		$rules = unserialize( $geo );
		if ( $rule_id ) {
			if ( ! empty( $rules[ $rule_id ] ) ) {
				$ret = $rules[ $rule_id ];
			}
			else {
				$ret = false;
			}
		}
		else {
			$ret = $rules;
		}
	}
	else {
		$ret = false;
	}

	return $ret;
}

/**
 * Set user session expiration
 *
 */
add_filter( 'auth_cookie_expiration', function ( $expire ) {
	$time = crb_get_settings( 'auth_expire' );
	if ( $time ) {
		$expire = 60 * $time;
	}

	return $expire;
} );

//  Track various activity -------------------------------------------------------------------------

add_action( 'wp_login', function ( $login, $user ) {
	global $wp_cerber_user_id;
	if ( ! empty( $_POST['log'] ) ) { // default WP form
		$user_login = htmlspecialchars( $_POST['log'] );
	}
	else {
		$user_login = $login;
	}
	$wp_cerber_user_id = $user->ID;
	cerber_log( 5, $user_login, $user->ID );
}, 10, 2 );

/**
 * Catching some cases of authentication without using login form or not a default user login process
 */
add_action( 'set_auth_cookie', function () {
	add_action( 'shutdown', function () { // deferred to allow the possible 'wp_login' action be logged first
		if ($user_id = get_current_user_id()) {
		    cerber_log( 5, '', $user_id );
		}
	} );
} );

/*add_action( 'wp_logout', function() {
	global $user_ID;
	if ( ! $user_ID ) {
		$user    = wp_get_current_user();
		$user_ID = $user->ID;
	}
	cerber_log( 6, '', $user_ID );
});*/

add_action( 'clear_auth_cookie', function () {
	$uid = get_current_user_id();
	if ( $uid ) {
		cerber_log( 6, '', $uid );
	}
} );

//add_action( 'lostpassword_post', 'cerber_password_post' );
add_action( 'retrieve_password', function ( $user_login ) {
	cerber_log( 21, $user_login );
} );

add_action( 'password_reset', 'crb_pass_reset' );
add_action( 'crb_after_reset', 'crb_pass_reset', 10, 2);

function crb_pass_reset( $user, $user_id = null) {
	if ( ! $user && $user_id ) {
		$user = get_user_by( 'id', $user_id );
	}
	if ( ! $user ) {
		return;
	}
	cerber_log( 20, $user->user_login, $user->ID );
}

//add_action( 'register_new_user', function ( $user_id ) { // OLD
// Fires in wp_insert_user()
add_action( 'user_register', function ( $user_id ) { // @since 5.6
    $cid = get_current_user_id();
	if ($user = get_user_by( 'ID', $user_id )) {
		if ( $cid && $cid != $user_id ) {
			$ac = 1;
		}
		else {
			$ac = 2;
		}
		cerber_log( $ac, $user->user_login, $user_id );
		crb_log_user_ip( $user_id, $cid );
	}
});

// Fires after a new user has been created in WP dashboard.
add_action( 'edit_user_created_user', function ( $user_id, $notify = null ) {
	if ( $user_id && $user = get_user_by( 'ID', $user_id ) ) {
		cerber_log( 1, $user->user_login, $user_id );
		crb_log_user_ip( $user_id );
	}
}, 10, 2 );

// Log IP address of user registration independently
function crb_log_user_ip( $user_id, $by_user = null ) {
	global $wp_cerber;
	if ( ! $user_id ) {
		return;
	}
	if ( ! $by_user ) {
		$by_user = get_current_user_id();
	}
	add_user_meta( $user_id, '_crb_reg_', array( 'IP' => $wp_cerber->getRemoteIp(), 'user' => $by_user ) );
}

// Lockouts routines ---------------------------------------------------------------------

/**
 * Lock out IP address if it is an alien IP only (browser does not have valid Cerber groove)
 *
 * @param $ip string IP address to block
 * @param integer $reason_id ID of reason of blocking
 * @param string $details Reason of blocking
 * @param null $duration Duration of blocking
 *
 * @return bool|false|int
 */
function cerber_soft_block_add( $ip, $reason_id, $details = '', $duration = null ) {
	if ( cerber_check_groove() ) {
		return false;
	}

	return cerber_block_add( $ip, $reason_id, $details, $duration );
}

/**
 * Lock out IP address
 *
 * @param $ip string IP address to block
 * @param integer $reason_id ID of reason of blocking
 * @param string $details Reason of blocking
 * @param int $duration Duration of blocking
 *
 * @return bool|false|int
 */
function cerber_block_add( $ip = '', $reason_id = 1, $details = '', $duration = null ) {
	global $wpdb;

	$wp_cerber = get_wp_cerber();

	//$wp_cerber->setProcessed();

	if ( empty($ip) ) {
		$ip = $wp_cerber->getRemoteIp();
	}

	if ( cerber_get_block( $ip ) ) {
		return false;
	}

	if ( cerber_acl_check( $ip ) ) {
        return false;
	}

	$ip_address = $ip;

	lab_save_push( $ip, $reason_id, $details );

	if ( $wp_cerber->getSettings( 'subnet' ) ) {
		$ip       = cerber_get_subnet( $ip );
		$activity = 11;
	}
	else {
		$activity = 10;
	}

	/*if ( $wpdb->get_var( $wpdb->prepare( 'SELECT count(ip) FROM ' . CERBER_BLOCKS_TABLE . ' WHERE ip = %s', $ip ) ) ) {
		return false;
	}*/

	$reason = cerber_get_reason( $reason_id );
	if ( $details ) {
		$reason .= ': <b>' . $details . '</b>';
	}

	if ( ! $duration ) {
		$duration = cerber_calc_duration( $ip );
	}
	$until = time() + $duration;

	//$result = $wpdb->query($wpdb->prepare('INSERT INTO '. CERBER_BLOCKS_TABLE . ' (ip,block_until,reason) VALUES (%s,%d,%s)',$ip,$until,$reason));
	$result = $wpdb->insert( CERBER_BLOCKS_TABLE, array(
		'ip'          => $ip,
		'block_until' => $until,
		'reason'      => $reason
	), array( '%s', '%d', '%s' ) );

	if ( $result ) {
		cerber_log( $activity, null, null, $ip_address );
        $wp_cerber->setLocked();
		do_action( 'cerber_ip_locked', array( 'IP' => $ip_address, 'reason' => $reason ) );
		$result = true;
	}
	else {
		cerber_db_error_log();
		$result = false;
	}

	if ( $wp_cerber->getSettings( 'notify' ) ) {
		//$count = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_BLOCKS_TABLE );
		$count = cerber_blocked_num();
		if ( $count > $wp_cerber->getSettings( 'above' ) ) {
			cerber_send_notify( 'lockout', '', $ip_address );
		}
	}

	return $result;
}

function cerber_block_delete( $ip ) {
	global $wpdb;

	return $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CERBER_BLOCKS_TABLE . ' WHERE ip = %s', $ip ) );
}


/**
 *
 * Check if an IP address is currently blocked. With C subnet also.
 *
 * @param string $ip an IP address
 *
 * @return bool true if IP is locked out
 */
function cerber_block_check( $ip = '' ) {

	// @since 4.8
	if (cerber_get_block($ip)) return true;

	return false;
}

/**
 *
 * Return the lockout row for an IP if it is blocked. With C subnet also.
 *
 * @param string $ip an IP address
 *
 * @return object|bool object if IP is locked out, false otherwise
 */
function cerber_get_block( $ip = '' ) {
	global $wpdb, $wp_cerber;

	if ( ! $ip ) {
		$wp_cerber = get_wp_cerber();
		$ip = $wp_cerber->getRemoteIp();
	}

	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return false;
	}

	$where = ' WHERE ip = "' . $ip . '"';
	if ( cerber_is_ipv4( $ip ) ) {
		$subnet = cerber_get_subnet( $ip );
		$where  .= ' OR ip = "' . $subnet . '"';
	}
	if ( $ret = $wpdb->get_row( 'SELECT * FROM ' . CERBER_BLOCKS_TABLE . $where ) ) {
		return $ret;
	}

	return false;
}

/**
 * Return the number of currently locked out IPs
 *
 * @return int the number of currently locked out IPs
 * @since 3.0
 */
function cerber_blocked_num() {
	global $wpdb;
	$count = cerber_db_get_var( 'SELECT count(ip) FROM ' . CERBER_BLOCKS_TABLE );

	return absint( $count );
}

/**
 * Calculate duration for a lockout of an IP address based on settings
 *
 * @param string $ip
 *
 * @return integer Duration in seconds
 */
function cerber_calc_duration( $ip ) {
	global $wpdb;
	$range = time() - crb_get_settings( 'aglast' ) * 3600;
	//$lockouts = $wpdb->get_var( $wpdb->prepare( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE ip = %s AND activity IN (10,11) AND stamp > %d', $ip, $range ) );
	$lockouts = $wpdb->get_var( 'SELECT COUNT(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE ip = "' . $ip . '" AND activity IN (10,11) AND stamp > ' . $range );
	if ( $lockouts >= crb_get_settings( 'aglocks' ) ) {
		$ret = crb_get_settings( 'agperiod' ) * 3600;
	}
	else {
		$ret = crb_get_settings( 'lockout' ) * 60;
	}
	$ret = absint( $ret );
	if ( $ret < 60 ) {
		$ret = 60;
	}

	return $ret;
}

/**
 * Calculation of remaining attempts
 *
 * @param $ip string an IP address
 * @param $check_acl bool if true will check the White IP ACL first
 * @param $activity array List of activity IDs to calculate for
 * @param $allowed int  Allowed attempts within $period
 * @param $period int  Period for count attempts
 *
 * @return int Allowed attempts for present moment
 */
function cerber_get_remain_count( $ip = '', $check_acl = true, $activity = array( '7,51,52' ), $allowed = null, $period = null ) {
	global $wpdb, $wp_cerber;
	if ( ! $ip ) {
		$ip = $wp_cerber->getRemoteIp();
	}
	else {
		if ( ! $ip = filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return 0;
		}
	}

	if ( ! $allowed ) {
		$allowed = absint( crb_get_settings( 'attempts' ) );
	}

	if ( $check_acl && cerber_acl_check( $ip, 'W' ) ) {
		return $allowed; // whitelist = infinity attempts
	}

	if ( ! $period ) {
		$period = absint( crb_get_settings( 'period' ) );
	}

	$range    = time() - $period * 60;
	$in       = implode( ',', array_filter( array_map( 'absint', $activity ) ) );
	$attempts = cerber_db_get_var( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE . ' WHERE ip = "' . $ip . '" AND activity IN (' . $in . ') AND stamp > ' . $range );

	if ( ! $attempts ) {
		return $allowed;
	}
	else {
		$ret = $allowed - $attempts;
	}
	$ret = $ret < 0 ? 0 : $ret;

	return $ret;
}

/**
 * Is a given IP is allowed to do restricted things?
 * Here Cerber makes its decision.
 *
 * @param $ip string IP address
 *
 * @return bool
 */
function cerber_is_allowed( $ip = '' ) {

	if ( ! $ip ) {
		$wp_cerber = get_wp_cerber();
		$ip = $wp_cerber->getRemoteIp();
	}
	elseif ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return false;
	}

	// @since 4.7.9
	if ( cerber_block_check( $ip ) ) {
		return false;
	}

	$tag = cerber_acl_check( $ip );
	if ( $tag == 'W' ) {
		return true;
	}
	if ( $tag == 'B' ) {
		return false;
	}

	/* @since 4.7.9
	if ( cerber_block_check( $ip ) ) {
		return false;
	}*/

	if ( cerber_is_citadel() ) {
		return false;
	}

	if ( lab_is_blocked( $ip, false ) ) {
		return false;
	}

	return true;
}

/**
 * Check if a given username is not permitted to login or register
 *
 * @param $username string
 *
 * @return bool true if username is prohibited
 */
function cerber_is_prohibited( $username ) {
	if ( $list = (array) crb_get_settings( 'prohibited' ) ) {
		foreach ( $list as $item ) {
			if ( mb_substr( $item, 0, 1 ) == '/' && mb_substr( $item, - 1 ) == '/' ) {
				$pattern = trim( $item, '/' );
				if ( @mb_ereg_match( $pattern, $username, 'i' ) ) {
			        return true;
                }
			}
			elseif ($username === $item){
				return true;
            }
		}
	}

	return false;
}

// TODO: Merge with $wp_cerber->getStatus();
function cerber_get_status( $ip ) {
	global $cerber_status;

	if ( ! empty( $cerber_status ) ) {
		return absint($cerber_status);
	}

	if ( cerber_block_check( $ip ) ) {
		return 13;
	}

	$tag = cerber_acl_check( $ip );
	if ( $tag == 'W' ) {
		return 0;
	}
	if ( $tag == 'B' ) {
		return 14;
	}

	if ( cerber_is_citadel() ) {
		return 12;
	}

	if ( lab_is_blocked( $ip, false ) ) {
		return 15;
	}


	return 0;
}

// Access lists (ACL) routines --------------------------------------------------------------------------------

// TODO: move to dashboard.php
/**
 * Add IP to specified access list
 *
 * @param $ip string|array single IP address, string with IP network, range or associative range array
 * @param $tag string 'B'|'W'
 *
 * @return bool|int|object Result of operation
 */
function cerber_acl_add( $ip, $tag, $comment = '') {
	global $wpdb;
	if ( is_string( $ip ) ) {
		if ( ! cerber_is_ipv4( $ip ) ) {
			$ip = cerber_short_ipv6( $ip );
		}
		if ( $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ip) FROM ' . CERBER_ACL_TABLE . ' WHERE ip = %s', $ip ) ) ) {
			return false; //__( 'Element is already in list', 'wp-cerber' );
		}
		$range = cerber_any2range( $ip );
		if ( is_array( $range ) ) {
			$begin = $range['begin'];
			$end   = $range['end'];
		}
		else {
		    if (cerber_is_ipv4($ip)) {
			    $begin = ip2long( $ip );
			    $end   = ip2long( $ip );
		    }
		    else{
			    $begin = 0;
			    $end = 0;
            }
		}

		$result = $wpdb->insert( CERBER_ACL_TABLE, array( 'ip'            => $ip,
		                                        'ip_long_begin' => $begin,
		                                        'ip_long_end'   => $end,
		                                        'tag'           => $tag,
		                                        'comments'      => $comment
		), array( '%s', '%d', '%d', '%s', '%s' ) );
		return $result;
	}
	elseif ( is_array( $ip ) ) {
		$range = $ip['range'];
		$begin = $ip['begin'];
		$end   = $ip['end'];
		if ( $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ip) FROM ' . CERBER_ACL_TABLE . ' WHERE ip_long_begin = %d AND ip_long_end = %d', $begin, $end ) ) ) {
			return false;
		}

		$result = $wpdb->insert( CERBER_ACL_TABLE, array( 'ip'            => $range,
		                                                  'ip_long_begin' => $begin,
		                                                  'ip_long_end'   => $end,
		                                                  'tag'           => $tag,
		                                                  'comments'      => $comment
		), array( '%s', '%d', '%d', '%s', '%s' ) );

		return $result;

		//return $wpdb->query( $wpdb->prepare( 'INSERT INTO ' . CERBER_ACL_TABLE . ' (ip, ip_long_begin, ip_long_end, tag) VALUES (%s,%d,%d,%s)', $range, $begin, $end, $tag ) );
	}

	return false;
}
// TODO: move to dashboard.php
function cerber_add_white( $ip, $comment = '' ) {
	return cerber_acl_add( $ip, 'W', $comment );
}
// TODO: move to dashboard.php
function cerber_add_black( $ip, $comment = '') {
	return cerber_acl_add( $ip, 'B', $comment );
}
// TODO: move to dashboard.php
function cerber_acl_remove( $ip ) {
	global $wpdb;
	if ( is_string( $ip ) ) {
		return $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CERBER_ACL_TABLE . ' WHERE ip = %s ', $ip ) );
	} elseif ( is_array( $ip ) ) {
		return $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CERBER_ACL_TABLE . ' WHERE ip_long_begin = %d AND ip_long_end = %d', $ip['begin'], $ip['end'] ) );
	}

	return false;
}

// TODO: move to dashboard.php
/**
 * Can a given IP (user input) be added to the blacklist
 *
 * @param $ip
 * @param string $list
 *
 * @return bool
 */
function cerber_can_be_listed( $ip, $list = 'B' ) {
    global $wp_cerber;
	if ( $list == 'B' ) {
		if ( crb_acl_is_white( $wp_cerber->getRemoteIp() ) ) {
			return true;
		}
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			if ( cerber_is_myip( $ip ) ) {
				return false;
			}
		}
		$range = cerber_any2range( $ip );
		if ( cerber_is_ip_in_range( $range ) ) {
			return false;
		}

		return true;
	}

	return true;
}

function cerber_is_acl_permit( $ip = '' ) {
	$tag = cerber_acl_check( $ip );
	if ( $tag == 'W' ) {
		return true;
	}
	if ( $tag == 'B' ) {
		return false;
	}

	return true;
}

/**
 * Check ACLs for given IP. Some extra lines for performance reason.
 *
 * @param string $ip
 * @param string $tag
 *
 * @return bool|string
 */
function cerber_acl_check( $ip = null, $tag = '' ) {
	global $wpdb, $wp_cerber;
	static $cache; // @since 5.26

	if ( ! $ip ) {
		$wp_cerber = get_wp_cerber();
		$ip = $wp_cerber->getRemoteIp();
		//$ip = cerber_get_remote_ip();
	}

	$key = cerber_get_id_ip( $ip ) . (string)$tag;

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	if ( ! cerber_is_ipv4( $ip ) ) {
		$ret = cerber_acl_checkV6( $ip, $tag );
		$cache[ $key ] = $ret;
		return $ret;
	}

	$long = ip2long( $ip );

	if ( $tag ) {
		if ( $tag != 'W' && $tag != 'B' ) {
			$ret = false;
		}
		elseif ( cerber_db_get_var( 'SELECT ip FROM ' . CERBER_ACL_TABLE . ' WHERE ip_long_begin <= '.$long.' AND '.$long.' <= ip_long_end AND tag = "'.$tag.'" LIMIT 1' )  ) {
			$ret = true;
		}
		else {
			$ret = false;
		}

		$cache[ $key ] = $ret;
		return $ret;
	}
	else {
	    // We use two queries because of possible overlapping an IP and its network
		if ( $ret = cerber_db_get_var( 'SELECT tag FROM ' . CERBER_ACL_TABLE . ' WHERE ip_long_begin <= ' . $long . ' AND ' . $long . ' <= ip_long_end AND tag = "W" LIMIT 1' ) ) {
			$cache[ $key ] = $ret;
			return $ret;
		}
		if ( $ret = cerber_db_get_var( 'SELECT tag FROM ' . CERBER_ACL_TABLE . ' WHERE ip_long_begin <= ' . $long . ' AND ' . $long . ' <= ip_long_end AND tag = "B" LIMIT 1' ) ) {
			$cache[ $key ] = $ret;
			return $ret;
		}

		$cache[ $key ] = false;
		return false;
	}
}

/**
 * IPv6 version of cerber_acl_check() without subnets and ranges
 *
 * @param null $ip
 * @param string $tag
 *
 * @return bool|null|string
 */
function cerber_acl_checkV6( $ip = null, $tag = '' ) {
	global $wpdb, $wp_cerber;
	if ( ! $ip ) {
		$ip = $wp_cerber->getRemoteIp();
	}
	if ( $tag ) {
		if ( $wpdb->get_var( $wpdb->prepare( 'SELECT count(ip) FROM ' . CERBER_ACL_TABLE . ' WHERE ip = %s AND tag = %s', $ip, $tag ) ) ) {
			return true;
		}

		return false;
	} else {
		if ( $ret = $wpdb->get_var( $wpdb->prepare( 'SELECT tag FROM ' . CERBER_ACL_TABLE . ' WHERE ip = %s', $ip ) ) ) {
			return $ret;
		}

		return false;
	}
}


function crb_acl_is_white( $ip = null ){

    $acl = cerber_acl_check( $ip );
	if ( $acl == 'W' ) {
		return true;
	}

	return false;
}

/*
 * Logging directly to the file
 *
 * CERBER_FAIL_LOG optional, full path including filename to the log file
 * CERBER_LOG_FACILITY optional, use to specify what type of program is logging the messages
 *
 * */
function cerber_file_log( $user_login, $ip ) {
	if ( defined( 'CERBER_FAIL_LOG' ) ) {
		if ( $log = @fopen( CERBER_FAIL_LOG, 'a' ) ) {
			$pid = absint( @posix_getpid() );
			@fwrite( $log, date( 'M j H:i:s ' ) . $_SERVER['SERVER_NAME'] . ' Cerber(' . $_SERVER['HTTP_HOST'] . ')[' . $pid . ']: Authentication failure for ' . $user_login . ' from ' . $ip . "\n" );
			@fclose( $log );
		}
	} else {
		@openlog( 'Cerber(' . $_SERVER['HTTP_HOST'] . ')', LOG_NDELAY | LOG_PID, defined( 'CERBER_LOG_FACILITY' ) ? CERBER_LOG_FACILITY : LOG_AUTH );
		@syslog( LOG_NOTICE, 'Authentication failure for ' . $user_login . ' from ' . $ip );
		@closelog();
	}
}

/*
	Return wildcard - string like subnet Class C
*/
function cerber_get_subnet( $ip ) {
	return preg_replace( '/\.\d{1,3}$/', '.*', $ip );
}

/*
	Check if given IP address or wildcard or CIDR is valid
*/
function cerber_is_ip_or_net( $ip ) {
	if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return true;
	}
	// WILDCARD: 192.168.1.*
	$ip = str_replace( '*', '0', $ip );
	//if ( @inet_pton( $ip ) ) {
	if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return true;
	}
	// CIDR: 192.168.1/24
	if ( strpos( $ip, '/' ) ) {
		$cidr = explode( '/', $ip );
		$net  = $cidr[0];
		$mask = absint( $cidr[1] );
		$dots = substr_count( $net, '.' );
		if ( $dots < 3 ) {
			if ( $dots == 1 ) {
				$net .= '.0.0';
			} elseif ( $dots == 2 ) {
				$net .= '.0';
			}
		}
		if ( ! cerber_is_ipv4( $net ) ) {
			return false;
		}
		if ( ! is_numeric( $mask ) ) {
			return false;
		}

		return true;
	}

	return false;
}

/**
 * Tries to recognize single IP address or IP v4 range (with dash) in a given string.
 *
 * @param string $string String to recognize IP address in
 *
 * @return array|bool|string Return single IP address or wildcard or CIDR as a string, and IP range as an array.
 */
function cerber_parse_ip( $string = '' ) {
	$string = trim( $string );
	if ( cerber_is_ip_or_net( $string ) ) {
		return $string;
	}
	$explode = explode( '-', $string );
	if ( ! is_array( $explode ) || ! $explode ) {
		return false;
	}
	$range = array();
	$count = 0;
	foreach ( $explode as $ip ) {
		$ip = trim( $ip );
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$range[] = $ip;
			$count ++;
			if ( $count >= 2 ) {
				break;
			}
		}
	}
	if ( count( $range ) < 2 ) {
		return false;
	}
	if ( ip2long( $range[1] ) <= ip2long( $range[0] ) ) {
		return false;
	}

	return array(
		'range'    => $range[0] . ' - ' . $range[1],
		'begin_ip' => $range[0],
		'end_ip'   => $range[1],
		'begin'    => ip2long( $range[0] ),
		'end'      => ip2long( $range[1] ),
	);
}

/**
 * Convert a network wildcard string like x.x.x.* to an IP v4 range
 *
 * @param $wildcard string
 *
 * @return array|bool|string False if no wildcard found, otherwise result of cerber_parse_ip()
 */
function cerber_wildcard2range( $wildcard = '' ) {
	$begin = str_replace( '*', '0', $wildcard );
	$end   = str_replace( '*', '255', $wildcard );
	if ( ! cerber_is_ipv4( $begin ) ) {
		return false;
	}
	if ( ! cerber_is_ipv4( $end ) ) {
		return false;
	}

	return cerber_parse_ip( $begin . ' - ' . $end );
}

/**
 * Convert a CIDR to an IP v4 range
 *
 * @param $cidr string
 *
 * @return array|bool|string
 */
function cerber_cidr2range( $cidr = '' ) {
	if ( ! strpos( $cidr, '/' ) ) {
		return false;
	}
	$cidr = explode( '/', $cidr );
	$net  = $cidr[0];
	$mask = absint( $cidr[1] );
	$dots = substr_count( $net, '.' );
	if ( $dots < 3 ) { // not completed CIDR
		if ( $dots == 1 ) {
			$net .= '.0.0';
		} elseif ( $dots == 2 ) {
			$net .= '.0';
		}
	}
	if ( ! cerber_is_ipv4( $net ) ) {
		return false;
	}
	if ( ! is_numeric( $mask ) ) {
		return false;
	}
	$begin = long2ip( ( ip2long( $net ) ) & ( ( - 1 << ( 32 - (int) $mask ) ) ) );
	$end   = long2ip( ( ip2long( $net ) ) + pow( 2, ( 32 - (int) $mask ) ) - 1 );

	return cerber_parse_ip( $begin . ' - ' . $end );
}

/**
 * Try to recognize an IP range or a single IP in a string.
 *
 * @param $string string  Network wildcard, CIDR or IP range.
 *
 * @return array|bool|string
 */
function cerber_any2range( $string = '' ) {
	// Do not change the order!
	$ret = cerber_wildcard2range( $string );
	if ( ! $ret ) {
		$ret = cerber_cidr2range( $string );
	}
	if ( ! $ret ) {
		$ret = cerber_parse_ip( $string ); // must be last due to checking for cidr and wildcard
	}

	return $ret;
}

/*
	Check for given IP address or subnet belong to this session.
*/
function cerber_is_myip( $ip ) {
	global $wp_cerber;
	if ( ! is_string( $ip ) ) {
		return false;
	}
	$remote_ip = $wp_cerber->getRemoteIp();
	if ( $ip == $remote_ip ) {
		return true;
	}
	if ( $ip == cerber_get_subnet( $remote_ip ) ) {
		return true;
	}

	return false;
}

function cerber_is_ip_in_range( $range, $ip = null ) {
	global $wp_cerber;
	if ( ! is_array( $range ) ) {
		return false;
	}
	if ( ! $ip ) {
		$ip = $wp_cerber->getRemoteIp();
	}
	$long = ip2long( $ip );
	if ( $range['begin'] <= $long && $long <= $range['end'] ) {
		return true;
	}

	return false;
}

/**
 * Display 404 page to bump bots and bad guys
 *
 * @param bool $simple If true force displaying basic 404 page
 */
function cerber_404_page($simple = false) {
	global $wp_query;

	if ( !$simple ) {
		if ( function_exists( 'status_header' ) ) {
			status_header( '404' );
		}
		if ( isset( $wp_query ) && is_object( $wp_query ) ) {
			$wp_query->set_404();
		}
		if ( 0 == crb_get_settings( 'page404' ) ) {
			$template = null;
			if ( function_exists( 'get_404_template' ) ) {
				$template = get_404_template();
			}
			if ( function_exists( 'apply_filters' ) ) {
				$template = apply_filters( 'cerber_404_template', $template );
			}
			if ( $template && @file_exists( $template ) ) {
				include( $template );
				exit;
			}
		}
	}

	header( 'HTTP/1.0 404 Not Found', true, 404 );
	echo '<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL ' . esc_url( $_SERVER['REQUEST_URI'] ) . ' was not found on this server.</p></body></html>';
	cerber_traffic_log(); // do not remove!
	exit;
}
/*
	Display Forbidden page
*/
function cerber_forbidden_page() {
	status_header( '403' );
	header( 'HTTP/1.0 403 Access Forbidden', true, 403 );
	?>
    <!DOCTYPE html>
    <html style="height: 100%;">
    <meta charset="UTF-8">
    <head><title>403 Access Forbidden</title></head>
    <body style="height: 100%;">
    <div style="display: flex; align-items: center; justify-content: center; height: 70%;">
        <div style="background-color: #eee; width: 70%; border: solid 3px #ddd; padding: 1.5em 3em 3em 3em; font-family: Arial, Helvetica, sans-serif;">
            <div style="display: table-row;">
                <div style="display: table-cell; font-size: 150px; color: red; vertical-align: middle; padding-right: 50px;">
                    &#9995;
                </div>
                <div style="display: table-cell; vertical-align: middle;">
                    <h1><?php _e("We're sorry, you are not allowed to proceed", 'wp-cerber'); ?></h1>
                    <p>Our server stopped processing your request. Your request looks suspicious or similar to automated
                        requests from spam posting software.</p>
                    <p>If you believe you should be able to perform this request, please let us know.</p>
                </div>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
	cerber_traffic_log();  // do not remove!
	exit;
}

// Citadel mode -------------------------------------------------------------------------------------

function cerber_enable_citadel() {
	global $wp_cerber;
	if ( get_transient( 'cerber_citadel' ) ) {
		return;
	}
	set_transient( 'cerber_citadel', true, crb_get_settings( 'ciduration' ) * 60 );
	cerber_log( 12 );

	// Notify admin
	if ( $wp_cerber->getSettings( 'cinotify' ) ) {
		cerber_send_notify( 'citadel' );
	}
}

function cerber_disable_citadel() {
	delete_transient( 'cerber_citadel' );
}

function cerber_is_citadel() {
	if ( get_transient( 'cerber_citadel' ) ) {
		return true;
	}

	return false;
}

// Hardening -------------------------------------------------------------------------------------

//if (!cerber_acl_check(cerber_get_ip(),'W') && false) {

/*
	if ($hardening['ping']) {
		add_filter( 'xmlrpc_methods', 'remove_xmlrpc_pingback' );
		function remove_xmlrpc_pingback( $methods ) {
			unset($methods['pingback.ping']);
			unset($methods['pingback.extensions.getPingbacks']);
			return $methods;
		}
		add_filter( 'wp_headers', 'remove_pingback_header' );
		function remove_pingback_header( $headers ) {
			unset( $headers['X-Pingback'] );
			return $headers;
		}
	}
*/
//pingback_ping();


/*
// Remove shortlink from HEAD <link rel='shortlink' href='http://адрес-сайта/?p=45' />
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );
*/

/**
 *
 * Send notification letter
 *
 * @param string $type Notification type
 * @param string|array $msg Additional message
 * @param string $ip Remote IP address, if applicable
 *
 * @return bool
 */
function cerber_send_notify( $type = '', $msg = '', $ip = '' ) {
	global $wpdb, $wp_cerber;
	if ( ! $type ) {
		return false;
	}

	/*
	$super = false;
	if ( function_exists( 'is_super_admin' ) ) {
		$super = is_super_admin();
	}
	if ( $type == 'lockout' && !$usper()) {
	*/

	if ( $type == 'lockout' && !is_admin()) {
		$rate = absint( $wp_cerber->getSettings( 'emailrate' ) );
		if ( $rate ) {
			$last   = get_transient( 'cerber_last' );
			$period = 60 * 60;  // per hour
			if ( $last ) {
				if ( $last > ( time() - $period / $rate ) ) {
					return false;
				}
			}
			set_transient( 'cerber_last', time(), $period );
		}
	}

	$html_mode = false;

	$subj = '[' . get_option( 'blogname' ) . '] ' . __( 'WP Cerber notify', 'wp-cerber' ) . ': ';
	$body = '';

	if ( is_array( $msg ) ) {
		$msg = implode( "\n\n", $msg );
	}

	switch ( $type ) {
		case 'citadel':
			$max = $wpdb->get_var( 'SELECT MAX(stamp) FROM ' . CERBER_LOG_TABLE . ' WHERE  activity = 7' );
			if ($max) {
				$last_date = cerber_date( $max );
				$last      = $wpdb->get_row( 'SELECT * FROM ' . CERBER_LOG_TABLE . ' WHERE stamp = ' . $max . ' AND activity = 7' );
			}
			else $last = null;

			if ( ! $last ) { // workaround for the empty log table
				$last             = new stdClass();
				$last->ip         = '127.0.0.1';
				$last->user_login = 'test';
			}

			$subj .= __( 'Citadel mode is activated', 'wp-cerber' );

			$body = sprintf( __( 'Citadel mode is activated after %d failed login attempts in %d minutes.', 'wp-cerber' ), $wp_cerber->getSettings( 'cilimit' ), $wp_cerber->getSettings( 'ciperiod' ) ) . "\n\n";
			$body .= sprintf( __( 'Last failed attempt was at %s from IP %s with user login: %s.', 'wp-cerber' ), $last_date, $last->ip, $last->user_login ) . "\n\n";
			$body .= __( 'View activity in dashboard', 'wp-cerber' ) . ': ' . cerber_admin_link( 'activity' ) . "\n\n";
			//$body .= __('Change notification settings','wp-cerber').': '.cerber_admin_link();
			break;
		case 'lockout':
			$max = $wpdb->get_var( 'SELECT MAX(stamp) FROM ' . CERBER_LOG_TABLE . ' WHERE  activity IN (10,11)' );
			if ($max){
				$last_date = cerber_date( $max );
				$last      = $wpdb->get_row( 'SELECT * FROM ' . CERBER_LOG_TABLE . ' WHERE stamp = ' . $max . ' AND activity IN (10,11)' );
			}
			else $last = null;
			if ( ! $last ) { // workaround for the empty log table
				$last             = new stdClass();
				$last->ip         = '127.0.0.1';
				$last->user_login = 'test';
			}

			//if ( ! $active = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_BLOCKS_TABLE ) ) {
			if ( ! $active = cerber_blocked_num() ) {
				$active = 0;
			}
			//if ( $ip && ( $block = cerber_get_block( $ip ) ) ) {
			if ( $last->ip && ( $block = cerber_get_block( $last->ip ) ) ) {
				$reason = $block->reason;
			}
			else {
				$reason = __( 'unspecified', 'wp-cerber' );
			}

			$subj .= __( 'Number of lockouts is increasing', 'wp-cerber' ) . ' (' . $active . ')';

			$body = __( 'Number of active lockouts', 'wp-cerber' ) . ': ' . $active . "\n\n";
			$body .= sprintf( __( 'Last lockout was added: %s for IP %s', 'wp-cerber' ), $last_date, $last->ip . ' (' . @gethostbyaddr( $last->ip ) . ')' ) . "\n\n";
			$body .= __( 'Reason', 'wp-cerber' ) . ': ' . strip_tags($reason) . "\n\n";
			$body .= __( 'View activity for this IP', 'wp-cerber' ) . ': ' . cerber_admin_link( 'activity' ) . '&filter_ip=' . $last->ip . "\n\n";
			$body .= __( 'View lockouts in dashboard', 'wp-cerber' ) . ': ' . cerber_admin_link( 'lockouts' ) . "\n\n";
			break;
		case 'new_version':
			$subj = __( 'A new version of WP Cerber is available to install', 'wp-cerber' );
			$body = __( 'Hi!', 'wp-cerber' ) . "\n\n";
			$body .= __( 'A new version of WP Cerber is available to install', 'wp-cerber' ) . "\n\n";
			$body .= $msg . "\n\n";
			$body .= __( 'Website', 'wp-cerber' ) . ': ' . get_bloginfo( 'name' );
			break;
		case 'shutdown':
			$subj = '[' . get_option( 'blogname' ) . '] ' . __( 'The WP Cerber security plugin has been deactivated', 'wp-cerber' );
			$body .= __( 'The WP Cerber security plugin has been deactivated', 'wp-cerber' ) . "\n\n";
			if ( ! is_user_logged_in() ) {
				$u = __( 'Not logged in', 'wp-cerber' );
			} else {
				$user = wp_get_current_user();
				$u    = $user->display_name;
			}
			$body .= __( 'Website', 'wp-cerber' ) . ': ' . get_bloginfo( 'name' ) . "\n";
			$body .= __( 'By user', 'wp-cerber' ) . ': ' . $u . "\n";
			$body .= __( 'From IP address', 'wp-cerber' ) . ': ' . $wp_cerber->getRemoteIp() . "\n";
			$whois = cerber_ip_whois_info( $wp_cerber->getRemoteIp() );
			if ( ! empty( $whois['data']['country'] ) ) {
				$body .= __( 'From country', 'wp-cerber' ) . ': ' . cerber_country_name( $whois['data']['country'] );
			}
			break;
		case 'activated':
			$subj = '[' . get_option( 'blogname' ) . '] ' . __( 'The WP Cerber security plugin is now active', 'wp-cerber' );
			$body .= __( 'WP Cerber is now active and has started protecting your site', 'wp-cerber' ) . "\n\n";
			//$body .= __( 'Change notification settings', 'wp-cerber' ) . ': ' . cerber_admin_link('notifications') . "\n\n";
			$body .= __( 'Getting Started Guide', 'wp-cerber' ) . "\n\n";
			$body .= 'https://wpcerber.com/getting-started/' . "\n\n";
			$body .= 'Be in touch with the developer. Subscribe to Cerber\'s newsletter: http://wpcerber.com/subscribe-newsletter/';
			//$body .= get_bloginfo( 'name' );
			break;
		case 'newlurl':
			$subj .= __( 'New Custom login URL', 'wp-cerber' );
			$body .= $msg;
			break;
		case 'subs':
			$subj .= __( 'A new activity has been recorded', 'wp-cerber' );
			$body  = __( 'A new activity has been recorded', 'wp-cerber' ) . "\n\n";
			$body .= $msg;
			break;
		case 'report':
		    $html_mode = true;
			$subj = '[' . get_option( 'blogname' ) . '] WP Cerber Security: ' . __( 'Weekly report', 'wp-cerber' );
			$body = cerber_generate_report();
			$link = cerber_admin_link( 'notifications' );
			$body .= '<br/>' . __( 'To change reporting settings visit', 'wp-cerber' ) . ' <a href="' . $link . '">' . $link . '</a>';
			if ($msg) {
			    $body .= nl2br($msg);
			}
			break;
	}

	$to_list = cerber_get_email( $type, true );
	$to      = implode( ', ', $to_list );

	$body_filtered = apply_filters( 'cerber_notify_body', $body, array( 'type'    => $type,
	                                                                    'IP'      => $ip,
	                                                                    'to'      => $to,
	                                                                    'subject' => $subj
	) );
	if ( $body_filtered && is_string( $body_filtered ) ) {
		$body = $body_filtered;
	}

	$footer = '';

	if ( $lolink = cerber_get_login_url() ) {
		$lourl = urldecode( $lolink );
		if ( $html_mode ) {
			$lourl = '<a href="' . $lolink . '">' . $lourl . '</a>';
		}
		$footer .= "\n\n" . __( 'Your login page:', 'wp-cerber' ) . ' ' . $lourl;
	}

	if ( $type == 'report' && $date = lab_lab(true) ) {
		$footer .= "\n\n" . __( 'Your license is valid until', 'wp-cerber' ) . ' ' . $date;
	}

	$footer .= "\n\n\n" . __( 'This message was sent by', 'wp-cerber' ) . ' WP Cerber Security ' . CERBER_VER . "\n";
	$footer .= 'https://wpcerber.com';

	if ( $html_mode ) {
		add_filter( 'wp_mail_content_type', 'cerber_enable_html' );
		$footer = str_replace( "\n", '<br/>', $footer );
	}

	// Everything is prepared, let's send it out

	$result = null;
	if ( $to && $subj && $body ) {
		if ( ! $html_mode ) {
			cerber_pb_send( $subj, $body.$footer );
		}

		if ( $type == 'report') {
			$result = true;
			foreach ( $to_list as $email ) {
				$lastus = '';
				if ( $rec = cerber_get_last_login( null, $email ) ) {
					$lastus = sprintf( __( 'Your last sign-in was %s from %s', 'wp-cerber' ), cerber_date( $rec->stamp ), $rec->ip . ' (' . cerber_country_name( $rec->country ) . ')' );
					if ( $html_mode ) {
						$lastus = '<br/><br/>' . $lastus;
					}
					else {
						$lastus = "\n\n" . $lastus;
					}
				}

				if ( ! wp_mail( $email, $subj, '<html>' . $body . $lastus . $footer . '</html>' ) ) {
					$result = false;
				}
			}
		}
		else {
			if ( function_exists( 'wp_mail' ) ) {
				$body = $body . $footer;
				if ( $html_mode ) {
					$body = '<html>' . $body . '</html>';
				}
				$result = wp_mail( $to, $subj, $body );
			}
		}
	}

	remove_filter('wp_mail_content_type', 'cerber_enable_html');

	$params = array( 'type' => $type, 'IP' => $ip, 'to' => $to, 'subject' => $subj );
	if ( $result ) {
		do_action( 'cerber_notify_sent', $body, $params );
	}
	else {
		do_action( 'cerber_notify_fail', $body, $params );
	}

	return $result;
}

function cerber_enable_html() {
	return 'text/html';
}

/**
 * Generates a performance report
 *
 * @param int $period   Days to look back
 *
 * @return string
 */
function cerber_generate_report($period = 7){
    global $wpdb;

	$period = absint( $period );

	if ( ! $period ) {
		$period = 7;
	}

	$ret = '';
    $rows = array();
	$stamp = time() - $period * 24 * 3600;
	//$in = implode( ',', crb_get_activity_set( 'malicious' ) );
	//$link_base = '<a href="' . cerber_activity_link( array( 2 ) ) . '">';
	$base_url = cerber_admin_link( 'activity' );
	$css_table = 'width: 95%; max-width: 1000px; margin:0 auto; margin-bottom: 10px; background-color: #f5f5f5; text-align: center;';
	$css_td = 'padding: 0.5em 0.5em 0.5em 1em; text-align: left;';
	$css_boder = 'border-bottom: solid 2px #f9f9f9;';

	if (is_multisite()) {
	    $site_name = get_site_option( 'site_name' );
    }
    else {
	    $site_name = get_option( 'blogname' );
    }

	$ret .= '<div style="' . $css_table . '"><div style="margin:0 auto; text-align: center;"><p style="font-size: 130%; padding-top: 0.5em;">' . $site_name .'</p><p style="padding-bottom: 1em;">'. __('Weekly Report','wp-cerber'). '</p></div></div>';

	$kpi_list = cerber_calculate_kpi( $period );

	foreach ($kpi_list as $kpi){
		$rows[] = '<td style="'.$css_td.' text-align: right;">'.$kpi[1].'</td><td style="padding: 0.5em; text-align: left;">'.$kpi[0].'</td>';
	}

	$ret .= '<div style="text-align: center; '.$css_table.'"><table style="font-size: 130%; margin:0 auto;"><tr>' . implode( '</tr><tr>', $rows ) . '</tr></table></div>';

	// Activities counters
	$rows = array();
	$rows[] = '<td style="'.$css_td.$css_boder.'" colspan="2"><p style="line-height: 1.5em; font-weight: bold;">'.__('Activity details','wp-cerber').'</p></td>';
	$activites = $wpdb->get_results( 'SELECT activity, COUNT(activity) cnt FROM ' . CERBER_LOG_TABLE . ' WHERE stamp > ' . $stamp . ' GROUP by activity ORDER BY cnt DESC' );
	if ( $activites ) {
		$lables = cerber_get_labels();
		foreach ( $activites as $a ) {
			$rows[] = '<td style="'.$css_boder.$css_td.'">' . $lables[ $a->activity ] . '</td><td style="padding: 0.5em; text-align: center; width:10%;'.$css_boder.'"><a href="'.$base_url.'&filter_activity='.$a->activity.'">' . $a->cnt . '</a></td>';
		}
	}
	$ret .= '<table style="border-collapse: collapse; '.$css_table.'"><tr>' . implode( '</tr><tr>', $rows ) . '</tr></table>';

	// Activities counters
	$activites = $wpdb->get_results( 'SELECT user_login, COUNT(user_login) cnt FROM ' . CERBER_LOG_TABLE . ' WHERE activity = 51 AND stamp > ' . $stamp . ' GROUP by user_login ORDER BY cnt DESC LIMIT 10' );
	if ( $activites ) {
		$rows = array();
		$rows[] = '<td style="'.$css_td.$css_boder.'" colspan="2"><p style="line-height: 1.5em; font-weight: bold;">'.__('Attempts to log in with non-existent username','wp-cerber').'</p></td>';
		foreach ( $activites as $a ) {
			$rows[] = '<td style="'.$css_boder.$css_td.'">' . htmlspecialchars($a->user_login) . '</td><td style="padding: 0.5em; text-align: center; width:10%;'.$css_boder.'"><a href="'.$base_url.'&filter_login='.$a->user_login.'">' . $a->cnt . '</a></td>';
		}
		$ret .= '<table style="border-collapse: collapse; '.$css_table.'"><tr>' . implode( '</tr><tr>', $rows ) . '</tr></table>';
	}

	$ret = '<div style="width:100%; padding: 1em; text-align: center; background-color: #f9f9f9;">' . $ret . '</div>';

    return $ret;
}


// Maintenance routines ----------------------------------------------------------------

function cerber_init_cron(){
	$next_hour = floor( ( time() + 3600 ) / 3600 ) * 3600;

	if ( ! wp_next_scheduled( 'cerber_hourly_1' ) ) {
		wp_schedule_event( $next_hour + 600, 'hourly', 'cerber_hourly_1' );
	}

	if ( ! wp_next_scheduled( 'cerber_hourly_2' ) ) {
		wp_schedule_event( $next_hour , 'hourly', 'cerber_hourly_2' );
	}

	if ( ! wp_next_scheduled( 'cerber_daily' ) ) {
		wp_schedule_event( $next_hour + 3600, 'daily', 'cerber_daily' );
	}
}

add_action( 'cerber_hourly_1', 'cerber_do_hourly' );
function cerber_do_hourly($force = false) {
	global $wpdb, $wp_cerber;

	if (is_multisite()) {
		if ( ! $force && get_site_transient( 'cerber_multisite' ) ) {
			return;
		}
		set_site_transient( 'cerber_multisite', 'executed', 3600 );
	}

	if ( crb_get_settings( 'cerberlab' ) || lab_lab() ) {
		lab_check_nodes(true, true);
	}

	$time = time();
	$days = absint( crb_get_settings( 'keeplog' ) );
	if ( $days > 0 ) {
		$wpdb->query( 'DELETE FROM ' . CERBER_LOG_TABLE . ' WHERE stamp < ' . ( $time - $days * 24 * 3600 ) );
	}
	$days = absint( crb_get_settings( 'tikeeprec' ) );
	if ( $days > 0 ) {
		$wpdb->query( 'DELETE FROM ' . CERBER_TRAF_TABLE . ' WHERE stamp < ' . ( $time - $days * 24 * 3600 ) );
	}

	if ( crb_get_settings( 'cerberlab' ) ) {
		cerber_push_lab();
	}

	$wpdb->query( 'DELETE FROM ' . CERBER_LAB_IP_TABLE . ' WHERE expires < ' . $time );

	if ( $wp_cerber->getSettings( 'trashafter-enabled') && absint($wp_cerber->getSettings('trashafter'))) {
		$list = get_comments( array( 'status' => 'spam' ) );
		if ( $list ) {
			$time = time() - DAY_IN_SECONDS * absint($wp_cerber->getSettings( 'trashafter' ));
			foreach ( $list as $item ) {
				if ( $time > strtotime( $item->comment_date_gmt ) ) {
					wp_trash_comment( $item->comment_ID );
				}
			}
		}
	}

	cerber_up_data();

	set_site_transient( 'crb_hourly_1', date( 'G' ) , 3600 );
}

add_action( 'cerber_hourly_2', function () {

    // Avoid multiple executions on a weird hosting
	if ( ( $hour = get_site_transient( 'crb_hourly_2' ) ) && $hour == date( 'G' ) ) {
		return;
	}

	$gmt_offset = get_option( 'gmt_offset' ) * 3600;

	if ( crb_get_settings( 'enable-report' )
         && date( 'w', time() + $gmt_offset ) == crb_get_settings( 'wreports-day' )
	     && date( 'G', time() + $gmt_offset ) == crb_get_settings( 'wreports-time' )
	     //&& ! get_site_transient( 'cerber_wreport' )
	) {
		$result = cerber_send_notify( 'report' );
		//set_site_transient( 'cerber_wreport', 'sent', 7200 );
		update_site_option( '_cerber_report', array( time(), $result ) );
	}

	cerber_watchdog( true );

	if ( function_exists( 'cerber_delete_expired_set' ) ) {
		cerber_delete_expired_set();
	}

	set_site_transient( 'crb_hourly_2', date( 'G' ) , 3600 );
});

add_action( 'cerber_daily', 'cerber_do_daily' );
function cerber_do_daily() {
	global $wpdb, $wp_cerber;

	cerber_do_hourly( true );

	$time = time();

    lab_trunc_push();

	lab_validate_lic();

	$wpdb->query( 'DELETE FROM ' . CERBER_LAB_NET_TABLE . ' WHERE expires < ' . $time );

	$wpdb->query( 'OPTIMIZE TABLE ' . CERBER_LOG_TABLE );
	$wpdb->query( 'OPTIMIZE TABLE ' . CERBER_TRAF_TABLE );
	$wpdb->query( 'OPTIMIZE TABLE ' . CERBER_ACL_TABLE );
	$wpdb->query( 'OPTIMIZE TABLE ' . CERBER_BLOCKS_TABLE );
	$wpdb->query( 'OPTIMIZE TABLE ' . CERBER_LAB_TABLE );
	$wpdb->query( 'OPTIMIZE TABLE ' . CERBER_LAB_IP_TABLE );
	$wpdb->query( 'OPTIMIZE TABLE ' . CERBER_LAB_NET_TABLE );

	if ( $new = cerber_check_version() ) {
		$history = get_site_option( '_cerber_notify_new' );
		if ( ! $history || ! is_array( $history ) ) {
			$history = array();
		}
		if ( ! in_array( $new['ver'], $history ) ) {
			if ( crb_get_settings( 'notify-new-ver' ) ) {
				cerber_send_notify( 'new_version', 'Read more: https://wpcerber.com/?plugin_version=' . $new['ver'] );
			}
			$history[] = $new['ver'];
			update_site_option( '_cerber_notify_new', $history );
		}
	}

	// TODO: implement holding previous values for a while
	// cerber_antibot_gene();
}

/**
 * Log activity
 *
 * @param int $activity Activity ID
 * @param string $login Login used or any additional information
 * @param int $user_id  User ID
 * @param null $ip  IP Address
 *
 * @return false|int
 * @since 3.0
 */
function cerber_log( $activity, $login = '', $user_id = 0, $ip = null ) {
	global $wpdb, $user_ID, $cerber_logged;
	static $logged = array();
	$wp_cerber = get_wp_cerber();

	$activity = absint( $activity );

	if ( isset( $logged[ $activity ] ) ) {
		return false;
	}
	else {
		$logged[ $activity ] = true;
	}

	$cerber_logged[ $activity ] = $activity;

	//$wp_cerber->setProcessed();

	if ( empty( $ip ) ) {
		$ip = $wp_cerber->getRemoteIp();
	}
    elseif ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return false;
	}

    if ( cerber_is_ipv4( $ip ) ) {
		$ip_long = ip2long( $ip );
	}
	else {
		$ip_long = 1;
	}

	if ( empty( $user_id ) ) {
		if ( $user_ID ) {
			$user_id = $user_ID;
		}
		else {
			$user_ID = 0;
		}
	}

	$user_id = absint( $user_id );

	$stamp = cerber_request_time();

	$pos = strpos($_SERVER['REQUEST_URI'],'?');
	if ($pos) {
		$path = substr( $_SERVER['REQUEST_URI'], 0, $pos );
	}
	else {
		$path = $_SERVER['REQUEST_URI'];
    }
	$url = strip_tags($_SERVER['HTTP_HOST'] . $path);

	$why = 0;
	if ($activity != 10 && $activity != 11) {
	    $why = cerber_get_status($ip);
	}

    $details = $why .'|0|0|0|'. $url;

	$country = lab_get_country( $ip );

	/*
	$ret   = $wpdb->query( $wpdb->prepare( 'INSERT INTO ' . CERBER_LOG_TABLE . ' (ip, ip_long, user_login, user_id, stamp, activity, session_id, country, details) VALUES (%s,%d,%s,%d,%f,%d,%s,%s,%s)', $ip, $ip_long, $login, $user_id, $stamp, $activity, $wp_cerber->getSessionID(), $country, $details ) );
	if ( ! $ret ) {
		// workaround for a WP bugs like this: WP silently doesn't not insert a row into a table
		// https://core.trac.wordpress.org/ticket/32315
		$ret = $wpdb->insert( CERBER_LOG_TABLE, array(
			'ip'         => $ip,
			'ip_long'    => $ip_long,
			'user_login' => $login,
			'user_id'    => $user_id,
			'stamp'      => $stamp,
			'activity'   => $activity,
            'session_id' => $wp_cerber->getSessionID(),
            'country'   => $country,
            'details'   => $details,
		), array( '%s', '%d', '%s', '%d', '%f', '%d' ) );
	}*/

	// @since 6.3
	$login   = cerber_real_escape( $login );
	$details = cerber_real_escape( $details );
	$ret = cerber_db_query('INSERT INTO ' . CERBER_LOG_TABLE . ' (ip, ip_long, user_login, user_id, stamp, activity, session_id, country, details) 
	    VALUES ("'.$ip.'",'.$ip_long.',"'.$login.'",'.$user_id.',"'.$stamp.'",'.$activity.',"'.$wp_cerber->getSessionID().'","'.$country.'","'.$details.'")' );

	if ( ! $ret ) {
	    cerber_watchdog();
    }

	// Subscriptions - notifications for admin ---------------------------------------------------

	$subs = cerber_get_site_option( '_cerber_subs' );

	if (!empty($subs)) {
		foreach ( $subs as $hash => $sub ) {

		    // Loop through subscription parameters
			if ( ! empty( $sub[0] )) {
				if ( ! in_array( $activity, $sub[0] ) ) {
					continue;
				}
			}
			if ( ! empty( $sub[1] ) && $sub[1] != $user_id ) {
				continue;
			}
			if ( ! empty( $sub[3] ) && ( $ip_long < $sub[2] || $sub[3] < $ip_long ) ) {
				continue;
			}
			if ( ! empty( $sub[4] ) && $sub[4] != $ip ) {
				continue;
			}
			if ( ! empty( $sub[5] ) && $sub[5] != $login ) {
				continue;
			}
			if ( ! empty( $sub[6] ) && (false === strpos( $ip, $sub[6] )) && (false === mb_strpos( $login, $sub[6] )) ) {
				continue;
			}

			// Some parameter(s) matched, prepare and send notification

			$labels = cerber_get_labels( 'activity' );

			$msg = __( 'Activity', 'wp-cerber' ) . ': ' . $labels[$activity] . "\n\n";

			if ( $country ) {
				$coname = ' ('.cerber_country_name( $country ).')';
			}
			else {
				$coname = '';
            }

            $msg .= __( 'IP', 'wp-cerber' ) . ': ' . $ip . $coname. "\n\n";

			if ( $user_id && function_exists('get_userdata')) {
				$u = get_userdata( $user_id );
				$msg .= __( 'User', 'wp-cerber' ) . ': ' . $u->display_name . "\n\n";
			}

			if ( $login ) {
				$msg .= __( 'Username used', 'wp-cerber' ) . ': ' . $login . "\n\n";
			}

			if ( ! empty( $sub['6'] ) ) {
				$msg .= __( 'Search string', 'wp-cerber' ) . ': ' . $sub['6'] . "\n\n";
			}

			// Link to the Activity admin page
			$args = cerber_subscribe_params();
			$i = 0;
			$link_params = '';
			foreach ($args as $arg => $val){
			    if (is_array($sub[$i])){
				    foreach ( $sub[ $i ] as $item ) {
					    $link_params .= '&' . $arg . '[]=' . $item;
			        }
                }
                else {
	                $link_params .= '&' . $arg . '=' . $sub[ $i ];
                }
				$i++;
			}
			$link = cerber_admin_link( 'activity' ) . $link_params;

			$msg .= __( 'View activity in dashboard', 'wp-cerber' ) . ': ' . $link;
			$msg .= "\n\n" . __( 'To unsubscribe click here', 'wp-cerber' ) . ': ' . cerber_admin_link( 'activity' ) . '&unsubscribeme=' . $hash;

			cerber_send_notify( 'subs', $msg, $ip );

			break; // Just one notification letter per event
		}
	}

	if ( in_array( $activity, array( 16, 17, 40, 56 ) ) ) {
		lab_save_push( $ip, $activity, '' );
	}

	return $ret;
}

/**
 * Get records from the log
 *
 * @param array $activity
 * @param array $user
 * @param array $order
 * @param string $limit
 *
 * @return array|null
 */
function cerber_get_log($activity = array(), $user = array(), $order = array(), $limit = ''){
	global $wpdb;

	$where = array();
	if ($activity){
		$activity = array_map( 'absint', $activity );
	    $where[] = 'activity IN ('.implode(', ',$activity).')';
    }

	if ($user['email']){
		$user    = get_user_by( 'email', $user['email'] );
		$where[] = 'user_id = ' . absint( $user->ID );
	}
	elseif ($user['id']){
		$where[] = 'user_id = '.absint($user['id']);
	}

	$where_sql = '';
	if ( $where ) {
		$where_sql = ' WHERE ' . implode( ' AND ', $where );
	}

	$order_sql = '';
	if ( $order ) {
		if ( ! empty( $order['DESC'] ) ) {
			$order_sql = ' ORDER BY ' . preg_replace( '/[^\w]/', '', $order['DESC'] ) . ' DESC ';
		}
        elseif ( ! empty( $order['ASC'] ) ) {
			$order_sql = ' ORDER BY ' . preg_replace( '/[^\w]/', '', $order['ASC'] ) . ' ASC ';
		}
	}

	$limit_sql = '';
	if ( $limit ) {
		$limit_sql = ' LIMIT ' . preg_replace( '/[^0-9\.]/', '', $limit );
	}

	// $row = $wpdb->get_row('SELECT * FROM '.CERBER_LOG_TABLE.' WHERE activity = 5 AND user_id = '.absint($user_id) . ' ORDER BY stamp DESC LIMIT 1');
	return $wpdb->get_results( 'SELECT * FROM ' . CERBER_LOG_TABLE . ' ' . $where_sql . $order_sql . $limit_sql );

}

/**
 * Return the last login record for a given user
 *
 * @param $user_id int|null
 * @param $user_email string
 *
 * @return array|null|object|string
 */
function cerber_get_last_login( $user_id, $user_email = '' ) {
	if ( $user_id ) {
		$u = array( 'id' => $user_id );
	}
    elseif ( $user_email ) {
		$u = array( 'email' => $user_email );
	}

	if ($u) {
		$recs = cerber_get_log( array( 5 ), $u, array( 'DESC' => 'stamp' ), 1 );
		return $recs[0];
	}

	return '';
}

function cerber_count_log($activity = array(), $period = 1) {
	global $wpdb;

	$period = absint( $period );
	if ( ! $period ) {
		$period = 1;
	}

	$stamp = time() - $period * 24 * 3600;

	// TODO: replace with SELECT COUNT(DISTINCT session_id)
	$ret = $wpdb->get_var('SELECT COUNT(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN ('.implode(',',$activity).') AND stamp > '. $stamp);
	if (!$ret) $ret = 0;

	return $ret;
}

/**
 * Create a set of parameters for using it in Subscriptions
 * The keys are used to built an URL. Values to calculate a hash.
 *
 * @return array The set of parameters
 */
function cerber_subscribe_params() {
	$begin = 0;
	$end   = 0;
	$ip    = 0;
	if ( ! empty( $_GET['filter_ip'] ) ) {
		$ip = cerber_any2range( $_GET['filter_ip'] );
		if ( is_array( $ip ) ) {
			$begin = $ip['begin'];
			$end   = $ip['end'];
			$ip    = 0;
		} elseif ( ! $ip ) {
			$ip = 0;
		}
	}

	$filter_activity = ( empty( $_GET['filter_activity'] ) ) ? 0 : $_GET['filter_activity'];
	$filter_user     = ( empty( $_GET['filter_user'] ) ) ? 0 : $_GET['filter_user'];
	$filter_login    = ( empty( $_GET['filter_login'] ) ) ? 0 : $_GET['filter_login'];
	$search_activity = ( empty( $_GET['search_activity'] ) ) ? 0 : $_GET['search_activity'];
	$filter_role = ( empty( $_GET['filter_role'] ) ) ? 0 : $_GET['filter_role'];

	if ( ! is_array( $filter_activity ) ) {
		$filter_activity = array( $filter_activity );
	}
	$filter_activity = array_filter( $filter_activity );

	// 'begin' and 'end' array keys are not used, added for compatibility
	return array( 'filter_activity' => $filter_activity, 'filter_user' => $filter_user, 'being' => $begin, 'end' => $end, 'filter_ip' => $ip, 'filter_login' => $filter_login, 'search_activity' => $search_activity, 'filter_role' => $filter_role );
}

/*
	Plugin activation
*/
//register_activation_hook( __FILE__, 'cerber_activate' );
register_activation_hook( cerber_plugin_file(), 'cerber_set_up' );
function cerber_set_up() {
	global $wp_version;
	$assets_url = cerber_plugin_dir_url() . 'assets';

	//cerber_load_lang();
	load_plugin_textdomain( 'wp-cerber', false, basename( dirname( __FILE__ ) ) . '/languages' );

	if ( version_compare( CERBER_REQ_PHP, phpversion(), '>' ) ) {
		cerber_stop_activating( '<h3>' . sprintf( __( 'The WP Cerber requires PHP %s or higher. You are running', 'wp-cerber' ), CERBER_REQ_PHP ) . ' ' . phpversion() . '</h3>' );
	}

	if ( version_compare( CERBER_REQ_WP, $wp_version, '>' ) ) {
		cerber_stop_activating( '<h3>' . sprintf( __( 'The WP Cerber requires WordPress %s or higher. You are running', 'wp-cerber' ), CERBER_REQ_WP ) . ' ' . $wp_version . '</h3>' );
	}

	$db_errors = cerber_create_db();
	if ( $db_errors ) {
	    $e = '';
		foreach ( $db_errors as $db_error ) {
			$e .= '<p>' . implode( '</p><p>', $db_error ) . '</p>';
	    }
		cerber_stop_activating( '<h3>' . __( "Can't activate WP Cerber due to a database error.", 'wp-cerber' ) . '</h3>'.$e);
	}

	// Check for existing options
	$opt = cerber_get_options();
	$new = true;
	if ( ! empty( $opt ) && is_array( $opt ) ) {
		$opt = array_filter( $opt );
		if ( ! empty( $opt ) ) {
			$new = false;
		}
	}

	if ( $new ) {
		cerber_load_defaults();
	}

	cerber_upgrade_all();

	cerber_cookie1();
	cerber_disable_citadel();

	$wp_cerber = get_wp_cerber();

	cerber_add_white( cerber_get_subnet( $wp_cerber->getRemoteIp() ) , 'My subnet' ); // Protection for non-experienced user

	cerber_htaccess_sync();

	cerber_set_boot_mode();

	cerber_admin_message(
		'<img style="float:left; margin-left:-10px;" src="' . $assets_url . '/icon-128x128.png">' .
		'<p style="font-size:120%;">' . __( 'WP Cerber is now active and has started protecting your site', 'wp-cerber' ) . '</p>' .
		' <p>' . __( 'Your IP address is added to the', 'wp-cerber' ) . ' ' . __( 'White IP Access List', 'wp-cerber' ) .

		' <p style="font-size:120%;"><a href="http://wpcerber.com/getting-started/" target="_blank">' . __( 'Getting Started Guide', 'wp-cerber' ) . '</a></p>' .

		//' <p><b>' . __( "It's important to check security settings.", 'wp-cerber' ) . '</b> &nbsp;<a href="http://wpcerber.com/" target="_blank">Read Cerber\'s blog</a> ' .
		//'&nbsp; <a href="http://wpcerber.com/subscribe-newsletter/" target="_blank">Subscribe to Cerber\'s newsletter</a></p>' .

		' <p> </p><p><span class="dashicons dashicons-admin-settings"></span> <a href="' . cerber_admin_link( 'main' ) . '">' . __( 'Main Settings', 'wp-cerber' ) . '</a>' .
		' <span style="margin-left:20px;" class="dashicons dashicons-admin-network"></span> <a href="' . cerber_admin_link( 'acl' ) . '">' . __( 'Access Lists', 'wp-cerber' ) . '</a>' .
		' <span style="margin-left:20px;" class="dashicons dashicons-forms"></span> <a href="' . cerber_admin_link( 'antispam' ) . '">' . __( 'Antispam', 'wp-cerber' ) . '</a>' .
		' <span style="margin-left:20px;" class="dashicons dashicons-shield-alt"></span> <a href="' . cerber_admin_link( 'hardening' ) . '">' . __( 'Hardening', 'wp-cerber' ) . '</a>' .
		' <span style="margin-left:20px;" class="dashicons dashicons-controls-volumeon"></span> <a href="' . cerber_admin_link( 'notifications' ) . '">' . __( 'Notifications', 'wp-cerber' ) . '</a>' .
		' <span style="margin-left:20px;" class="dashicons dashicons-admin-tools"></span> <a href="' . cerber_admin_link( 'imex' ) . '">' . __( 'Import settings', 'wp-cerber' ) . '</a>' .
		'</p>' );


	if ( ! $new ) {
		return;
	}

	cerber_send_notify( 'activated' );

	$pi          = get_file_data( cerber_plugin_file(), array( 'Version' => 'Version' ), 'plugin' );
	$pi ['time'] = time();
	$pi ['user'] = get_current_user_id();
	update_site_option( '_cerber_activated', $pi );
}

/*
	Abort activating plugin!
*/
function cerber_stop_activating( $msg ) {
	//deactivate_plugins( plugin_basename( __FILE__ ) );
	deactivate_plugins( cerber_plug_in() );
	wp_die( $msg );
}

/**
 * Upgrade database tables, data and plugin settings
 *
 * @since 3.0
 *
 */
function cerber_upgrade_all() {
	$ver = cerber_get_site_option( '_cerber_up' );
	if ( ! $ver || $ver['v'] != CERBER_VER ) {
		cerber_create_db();
		cerber_upgrade_db();
		cerber_acl_fixer( true );
		cerber_antibot_gene( true );
		cerber_upgrade_options();
		wp_clear_scheduled_hook( 'cerber_hourly' ); // @since 5.8
		cerber_push_the_news( CERBER_VER );
		update_site_option( '_cerber_up', array( 'v' => CERBER_VER, 't' => time() ) );
	}
}

/**
 * Creates DB tables if they don't exist
 *
 * @param bool $recreate  If true, recreate some tables completely (with data lost)
 *
 * @return array    Errors
 */
function cerber_create_db($recreate = true) {
	global $wpdb;

	$wpdb->hide_errors();
	$db_errors = array();
	$sql       = array();

	if (!cerber_is_table(CERBER_LOG_TABLE)){
		$sql[] = "
        	CREATE TABLE IF NOT EXISTS " . CERBER_LOG_TABLE . " (
            ip varchar(39) CHARACTER SET ascii NOT NULL COMMENT 'Remote IP',
            user_login varchar(60) NOT NULL COMMENT 'Username from HTTP request',
            user_id bigint(20) unsigned NOT NULL DEFAULT '0',
            stamp bigint(20) unsigned NOT NULL COMMENT 'Unix timestamp',
            activity int(10) unsigned NOT NULL DEFAULT '0',
            KEY ip (ip)
	        ) DEFAULT CHARSET=utf8 COMMENT='Cerber activity log';
				";
	}

	if (!cerber_is_table(CERBER_ACL_TABLE)){
		$sql[] = "
            CREATE TABLE IF NOT EXISTS " . CERBER_ACL_TABLE . " (
            ip varchar(39) CHARACTER SET ascii NOT NULL COMMENT 'IP',
            tag char(1) NOT NULL COMMENT 'Type: B or W',
            comments varchar(250) NOT NULL,
            UNIQUE KEY ip (ip)
	        ) DEFAULT CHARSET=utf8 COMMENT='Cerber IP Access Lists';
				";
	}

	if (!cerber_is_table(CERBER_BLOCKS_TABLE)){
		$sql[] = "
	        CREATE TABLE IF NOT EXISTS " . CERBER_BLOCKS_TABLE . " (
		    ip varchar(39) CHARACTER SET ascii NOT NULL COMMENT 'Remote IP',
		    block_until bigint(20) unsigned NOT NULL COMMENT 'Unix timestamp',
		    reason varchar(250) NOT NULL COMMENT 'Why IP was blocked',
		    UNIQUE KEY ip (ip)
			) DEFAULT CHARSET=utf8 COMMENT='Cerber list of currently blocked IPs';			
				";
	}

	if (!cerber_is_table(CERBER_LAB_TABLE)){
		$sql[] = "
            CREATE TABLE IF NOT EXISTS " . CERBER_LAB_TABLE . " (
            ip varchar(39) CHARACTER SET ascii NOT NULL COMMENT 'Remote IP',
            reason_id int(11) unsigned NOT NULL DEFAULT '0',
            stamp bigint(20) unsigned NOT NULL COMMENT 'Unix timestamp',
            details text NOT NULL
			) DEFAULT CHARSET=utf8 COMMENT='Cerber lab cache';
				";
	}


	if ($recreate || !cerber_is_table(CERBER_LAB_IP_TABLE)){
		if ( $recreate && cerber_is_table( CERBER_LAB_IP_TABLE ) ) {
			$sql[] = 'DROP TABLE IF EXISTS ' . CERBER_LAB_IP_TABLE;
		}
		$sql[] = "
            CREATE TABLE IF NOT EXISTS " . CERBER_LAB_IP_TABLE . " ( 
            ip varchar(39) CHARACTER SET ascii NOT NULL COMMENT 'IP',
            reputation INT(11) UNSIGNED NOT NULL COMMENT 'Reputation of IP',
            expires INT(11) UNSIGNED NOT NULL COMMENT 'Unix timestamp', 
            PRIMARY KEY (ip)
			) DEFAULT CHARSET=utf8 COMMENT='Cerber lab IP cache';
				";
	}

	if ( $recreate || ! cerber_is_table( CERBER_LAB_NET_TABLE ) ) {
		if ( $recreate && cerber_is_table( CERBER_LAB_NET_TABLE ) ) {
			$sql[] = 'DROP TABLE IF EXISTS ' . CERBER_LAB_NET_TABLE;
		}
		$sql[] = '
            CREATE TABLE IF NOT EXISTS ' . CERBER_LAB_NET_TABLE . ' (
            ip varchar(39) CHARACTER SET ascii NOT NULL DEFAULT "" COMMENT "IP address",             
            ip_long_begin BIGINT UNSIGNED NOT NULL DEFAULT "0",
            ip_long_end BIGINT UNSIGNED NOT NULL DEFAULT "0",
            country CHAR(3) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT "" COMMENT "Country code",
            expires INT(11) UNSIGNED NOT NULL DEFAULT "0", 
            PRIMARY KEY (ip),
            UNIQUE KEY begin_end (ip_long_begin, ip_long_end)
			) DEFAULT CHARSET=utf8 COMMENT="Cerber lab network cache";
				';
	}

	if (!cerber_is_table(CERBER_GEO_TABLE)){
		$sql[] = '
            CREATE TABLE IF NOT EXISTS ' . CERBER_GEO_TABLE . ' (
            country CHAR(3) NOT NULL DEFAULT "" COMMENT "Country code",
            locale CHAR(10) NOT NULL DEFAULT "" COMMENT "Locale i18n",
            country_name VARCHAR(250) NOT NULL DEFAULT "" COMMENT "Country name",
            PRIMARY KEY (country, locale)
			) DEFAULT CHARSET=utf8;
				';
	}

	if (!cerber_is_table(CERBER_TRAF_TABLE)){
		$sql[] = '
            CREATE TABLE IF NOT EXISTS ' . CERBER_TRAF_TABLE . ' (            
            ip varchar(39) CHARACTER SET ascii NOT NULL,
            ip_long BIGINT UNSIGNED NOT NULL DEFAULT "0", 
            hostname varchar(250) NOT NULL DEFAULT "",
            uri text NOT NULL,
            request_fields MEDIUMTEXT NOT NULL,
            request_details MEDIUMTEXT NOT NULL,
            session_id char(32) CHARACTER SET ascii NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            stamp decimal(14,4) NOT NULL,
            processing int(10) NOT NULL DEFAULT 0,
            country char(3) CHARACTER SET ascii NOT NULL DEFAULT "",
            request_method char(8) CHARACTER SET ascii NOT NULL,
            http_code int(10) UNSIGNED NOT NULL,
            wp_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            wp_type int(10) UNSIGNED NOT NULL DEFAULT 0,
            is_bot int(10) UNSIGNED NOT NULL DEFAULT 0,
            blog_id int(10) UNSIGNED NOT NULL DEFAULT 0,
            KEY stamp (stamp)
            ) DEFAULT CHARSET=utf8;
			';
	}
/*
	if ( ! cerber_is_table( CERBER_SCAN_TABLE ) ) {
		$sql[] = '
		    CREATE TABLE IF NOT EXISTS ' . CERBER_SCAN_TABLE . ' (
            scan_id INT(10) UNSIGNED NOT NULL,
            scan_type INT(10) UNSIGNED NOT NULL DEFAULT 1,
            scan_status INT(10) UNSIGNED NOT NULL DEFAULT 0,
            file_name_hash VARCHAR(255) CHARACTER SET ascii NOT NULL DEFAULT "",
            file_name TEXT NOT NULL,
            file_type INT(10) UNSIGNED NOT NULL DEFAULT 0,
            file_hash VARCHAR(255) CHARACTER SET ascii NOT NULL DEFAULT "",
            file_md5 VARCHAR(255) CHARACTER SET ascii NOT NULL DEFAULT "",
            file_hash_repo VARCHAR(255) CHARACTER SET ascii NOT NULL DEFAULT "",
            hash_match INT(10) UNSIGNED NOT NULL DEFAULT 0,
            file_size BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            file_perms INT(11) NOT NULL DEFAULT 0,
            file_writable INT(10) UNSIGNED NOT NULL DEFAULT 0,
            file_mtime INT(10) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (scan_id, file_name_hash)
            ) DEFAULT CHARSET=utf8;
        ';
	}

	if ( ! cerber_is_table( CERBER_SETS_TABLE ) ) {
		$sql[] = '
            CREATE TABLE IF NOT EXISTS ' . CERBER_SETS_TABLE . ' (          
            the_key VARCHAR(255) CHARACTER SET ascii NOT NULL,
            the_id BIGINT(20) NOT NULL DEFAULT 0,
            the_value LONGTEXT NOT NULL,
            expires BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (the_key, the_id)
			) DEFAULT CHARSET=utf8;
        ';
	}
*/

	foreach ( $sql as $query ) {
		if ( ! $wpdb->query( $query ) && $wpdb->last_error ) {
			$db_errors[] = array( $wpdb->last_error, $wpdb->last_query );
		}
	}

	return $db_errors;
}

/**
 * Upgrade structure of existing DB tables
 *
 * @return array Errors during upgrading
 * @since 3.0
 */
function cerber_upgrade_db( $force = false ) {
	global $wpdb;
	$wpdb->hide_errors();
	if ($force) $wpdb->suppress_errors();
	$db_errors = array();
	$sql       = array();

	// @since 3.0
	$sql[] = 'ALTER TABLE ' . CERBER_LOG_TABLE . ' CHANGE stamp stamp DECIMAL(14,4) NOT NULL';

	// @since 3.1
	if ( $force || ! cerber_is_column( CERBER_LOG_TABLE, 'ip_long' ) ) {
		$sql[] = 'ALTER TABLE ' . CERBER_LOG_TABLE . ' ADD ip_long BIGINT UNSIGNED NOT NULL DEFAULT "0" COMMENT "IPv4 long" AFTER ip, ADD INDEX (ip_long)';
	}
	if ( $force || ! cerber_is_column( CERBER_ACL_TABLE, 'ip_long_begin' ) ) {
		$sql[] = 'ALTER TABLE ' . CERBER_ACL_TABLE . " ADD ip_long_begin BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'IPv4 range begin' AFTER ip, ADD ip_long_end BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'IPv4 range end' AFTER ip_long_begin";
	}
	if ( $force || !cerber_is_index( CERBER_ACL_TABLE, 'ip_begin_end' ) ) {
		$sql[] = 'ALTER TABLE ' . CERBER_ACL_TABLE . ' ADD UNIQUE ip_begin_end (ip, ip_long_begin, ip_long_end)';
	}
	if ( $force || cerber_is_index( CERBER_ACL_TABLE, 'ip' ) ) {
		$sql[] = 'ALTER TABLE ' . CERBER_ACL_TABLE . ' DROP INDEX ip';
	}

	// @since 4.8.2
	if ( $force || cerber_is_index( CERBER_ACL_TABLE, 'begin_end' ) ) {
		$sql[] = 'ALTER TABLE ' . CERBER_ACL_TABLE . ' DROP INDEX begin_end';
	}
	if ( $force || !cerber_is_index( CERBER_ACL_TABLE, 'begin_end_tag' ) ) {
		$sql[] = 'ALTER TABLE ' . CERBER_ACL_TABLE . ' ADD INDEX begin_end_tag (ip_long_begin, ip_long_end, tag)';
	}

	// @since 4.9
	if ( $force || ! cerber_is_column( CERBER_LOG_TABLE, 'session_id' ) ) {
	    $sql[] = 'ALTER TABLE ' . CERBER_LOG_TABLE . ' 
        ADD session_id CHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT "",
        ADD country CHAR(3) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT "" COMMENT "Country code",
        ADD details VARCHAR(250) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT "" COMMENT "Details about HTTP request";
      ';
	}

	// @since 6.1
	if ( $force || !cerber_is_index( CERBER_LOG_TABLE, 'session_index' ) ) {
		$sql[] = 'ALTER TABLE ' . CERBER_LOG_TABLE . ' ADD INDEX session_index (session_id)';
	}

	if (!empty($sql)) {
		foreach ( $sql as $query ) {
			if ( !$wpdb->query( $query ) && $wpdb->last_error ) {
				$db_errors[] = array( $wpdb->last_error, $wpdb->last_query );
			}
		}
	}

	// Convert existing data into the new format

	cerber_acl_fixer();

	if ( $db_errors ) {
		update_site_option( '_cerber_db_errors', $db_errors );
	}
	else {
		update_site_option( '_cerber_db_errors', '' );
	}

	return $db_errors;
}

/**
 * Updating old activity log records to the new row format (has been introduced in v 3.1)
 *
 * @since 4.0
 *
 */
function cerber_up_data() {
	global $wpdb;
	$ips = $wpdb->get_col( 'SELECT DISTINCT ip FROM ' . CERBER_LOG_TABLE . ' WHERE ip_long = 0 LIMIT 50' );
	if ( ! $ips ) {
		return;
	}
	foreach ( $ips as $ip ) {
		if ( cerber_is_ipv4( $ip ) ) {
			$ip_long = ip2long( $ip );
		} else {
			$ip_long = 1;
		}
		$wpdb->query( 'UPDATE ' . CERBER_LOG_TABLE . ' SET ip_long = ' . $ip_long . ' WHERE ip = "' . $ip .'" AND ip_long = 0');
	}
}

/**
 * Upgrade corrupted / from older versions of the plugin rows in ACL
 *
 * @param bool $ipv6 if true Process IPv6 addresses
 *
 */
function cerber_acl_fixer($ipv6 = false){
	global $wpdb;

	// Repair/update IPs without long values
	$rows = $wpdb->get_col( 'SELECT ip FROM ' . CERBER_ACL_TABLE . ' WHERE ip_long_begin = 0 OR ip_long_end = 0' );
	if ( ! $rows ) {
		return;
	}
	foreach ( $rows as $ip ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			continue;
		}
		$range = cerber_any2range( $ip );
		if ( is_array( $range ) ) {
			$begin = $range['begin'];
			$end   = $range['end'];
		}
		else {
			$begin = ip2long( $ip );
			$end   = ip2long( $ip );
		}

		$wpdb->query( 'UPDATE ' . CERBER_ACL_TABLE . ' SET ip_long_begin = ' . $begin . ', ip_long_end = ' . $end . ' WHERE ip = "' . $ip .'"');
	}

	// Convert old IPv6 to all shortened
	if ( $ipv6 ) {
		$ips = $wpdb->get_col( 'SELECT ip FROM ' . CERBER_ACL_TABLE . ' WHERE ip_long_begin = 0' );
		if ( $ips ) {
			foreach ( $ips as $ip ) {
				if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
					continue;
				}
				$short_ip = cerber_short_ipv6( $ip );
				if ($short_ip != $ip) {
					$wpdb->query( 'UPDATE ' . CERBER_ACL_TABLE . ' SET ip = "' . $short_ip . '" WHERE ip = "' . $ip . '"' );
				}
			}
		}
	}
}

add_action( 'deac' . 'tivate_' . cerber_plug_in(), 'cerber_clean' );
function cerber_clean( $ip ) {
	wp_clear_scheduled_hook( 'cerber_hourly_1' );
	wp_clear_scheduled_hook( 'cerber_hourly_2' );
	wp_clear_scheduled_hook( 'cerber_daily' );

	cerber_htaccess_clean_up();
	cerber_set_boot_mode( 0 );

	$pi       = get_file_data( cerber_plugin_file(), array( 'Version' => 'Version' ), 'plugin' );
	$pi ['v'] = time();
	$pi ['u'] = get_current_user_id();
	update_site_option( '_cerber_o' . 'ff', $pi );
	$f = 'cerb' . 'er_se' . 'nd_not' . 'ify';
	$f( 'sh' . 'utd' . 'own' );
}

/*
	Fix an issue with the empty user_id field in the comments table.
*/
add_filter( 'preprocess_comment', 'cerber_add_uid' );
function cerber_add_uid( $commentdata ) {
	$current_user           = wp_get_current_user();
	$commentdata['user_ID'] = $current_user->ID;

	return $commentdata;
}

/**
 * Load jQuery on the page
 *
 */
add_action( 'login_enqueue_scripts', 'cerber_login_scripts' );
function cerber_login_scripts() {
	if ( cerber_antibot_enabled( array('botsreg', 'botsany') ) ) {
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'wp_enqueue_scripts', 'cerber_scripts' );
function cerber_scripts() {
	global $wp_cerber;
	if ( ( is_singular() && cerber_antibot_enabled( array( 'botscomm', 'botsany' ) ) )
	     || ( $wp_cerber->getSettings( 'sitekey' ) && $wp_cerber->getSettings( 'secretkey' ) )
	) {
		wp_enqueue_script( 'jquery' );
	}
}

/**
 * Footer stuff
 * Explicit rendering reCAPTCHA
 *
 */
add_action( 'login_footer', 'cerber_login_foo', 1000 );
function cerber_login_foo( $ip ) {
    global $wp_cerber;

	cerber_antibot_code(array( 'botsreg', 'botsany' ));

    // Universal JS
	if (!$wp_cerber->recaptcha_here) return;

	$sitekey = $wp_cerber->getSettings('sitekey');

	if (!$wp_cerber->getSettings('invirecap')){
	    // Classic version (visible reCAPTCHA)
		echo '<script src = https://www.google.com/recaptcha/api.js?hl='.cerber_recaptcha_lang().' async defer></script>';
    }
	else {
	    // Pure JS version with explicit rendering
		?>
        <script src="https://www.google.com/recaptcha/api.js?onload=init_recaptcha_widgets&render=explicit&hl=<?php echo cerber_recaptcha_lang(); ?>" async defer></script>
        <script type='text/javascript'>

            document.getElementById("cerber-recaptcha").remove();

            var init_recaptcha_widgets = function () {
                for (var i = 0; i < document.forms.length; ++i) {
                    var form = document.forms[i];
                    var place = form.querySelector('.cerber-form-marker');
                    if (null !== place) render_recaptcha_widget(form, place);
                }
            };

            function render_recaptcha_widget(form, place) {
                var place_id = grecaptcha.render(place, {
                    'callback': function (g_recaptcha_response) {
                        HTMLFormElement.prototype.submit.call(form);
                    },
                    'sitekey': '<?php echo $sitekey; ?>',
                    'size': 'invisible',
                    'badge': 'bottomright'
                });

                form.onsubmit = function (event) {
                    event.preventDefault();
                    grecaptcha.execute(place_id);
                };

            }
        </script>
		<?php
	}
}

/**
 * Inline reCAPTCHA widget
 *
 */
add_action( 'wp_footer', 'cerber_foo', 1000 );
function cerber_foo() {
    global $wp_cerber;

	if (is_singular()) cerber_antibot_code( array( 'botscomm', 'botsany' ) );

    if (!$wp_cerber->recaptcha_here) return;

	// jQuery version with support visible and invisible reCAPTCHA
	// TODO: convert it into pure JS
	?>
    <script type="text/javascript">

        jQuery(document).ready(function ($) {

            var recaptcha_ok = false;
            var the_recaptcha_widget = $("#cerber-recaptcha");
            var is_recaptcha_visible = ($(the_recaptcha_widget).data('size') !== 'invisible');

            var the_form = $(the_recaptcha_widget).closest("form");
            var the_button = $(the_form).find('input[type="submit"]');
            if (!the_button.length) {
                the_button = $(the_form).find(':button');
            }

            // visible
            if (the_button.length && is_recaptcha_visible) {
                the_button.prop("disabled", true);
                the_button.css("opacity", 0.5);
            }

            window.form_button_enabler = function () {
                if (!the_button.length) return;
                the_button.prop("disabled", false);
                the_button.css( "opacity", 1 );
            };

            // invisible
            if (!is_recaptcha_visible) {
                $(the_button).click(function (event) {
                    if (recaptcha_ok) return;
                    event.preventDefault();
                    grecaptcha.execute();
                });
            }

            window.now_submit_the_form = function () {
                recaptcha_ok = true;
                $(the_button).click(); // this is only way to submit a form that contains "submit" inputs
            };
        });
    </script>
    <script src = "https://www.google.com/recaptcha/api.js?hl=<?php echo cerber_recaptcha_lang(); ?>" async defer></script>
	<?php
}


// Traffic Logging  ======================================================================

//add_action( 'wp', 'cerber_traffic_log' );
add_action( 'shutdown', function () {
	cerber_traffic_log();
} );
register_shutdown_function( 'cerber_traffic_log' );
/**
 * Traffic Logging
 *
 * @since 6.0
 */
function cerber_traffic_log(){
    global $wpdb, $wp_query, $wp_cerber_user_id, $wp_cerber_start_stamp, $blog_id;
    static $logged = false;

	if ( $logged ) {
		return;
	}

	$wp_cerber = get_wp_cerber();

	$wp_type = 700;

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
        /*
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'heartbeat' ) {
			return;
		}*/
		$wp_type = 500;
	}
	elseif ( is_admin() ) {
	    $wp_type = 501;
    }
	elseif ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		$wp_type = 502;
	}
	elseif ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
	    $wp_type = 515;
    }
    elseif ( cerber_is_rest_url() ) {
	    $wp_type = 520;
	}
	// Public part starts with 600
    elseif ( $wp_query && is_object( $wp_query ) ) {
	    $wp_type = 600;
	    if ( $wp_query->is_singular ) {
		    $wp_type = 601;
	    }
        elseif ( $wp_query->is_tag ) {
		    $wp_type = 603;
	    }
        elseif ( $wp_query->is_category ) {
		    $wp_type = 604;
	    }
        elseif ( $wp_query->is_search ) {
		    $wp_type = 605;
	    }
    }

	if ( function_exists( 'http_response_code' ) ) {  // PHP >= 5.4.0, PHP 7
		$http_code = http_response_code();
	}
	else {
		//get_status_header_desc()
		// TODO: Add detection for other HTTP codes for PHP 5.3
		$http_code = 200;
		if ( $wp_type > 600 ) {
			if ( $wp_query->is_404 ) {
				$http_code = 404;
			}
		}
	}

	$user_id = 0;
	if ( function_exists( 'get_current_user_id' ) ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id && $wp_cerber_user_id ) {
		$user_id = absint( $wp_cerber_user_id );
	}

	if ( ! cerber_to_log( $wp_type, $http_code, $user_id ) ) {
		return;
	}

	$logged = true;

	$ua = '';
	if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
		$ua = substr ($_SERVER['HTTP_USER_AGENT'], 0, 1000);
	}

	$bot = cerber_is_crawler( $ua );
	if ( $bot && crb_get_settings( 'tinocrabs' ) ) {
		return;
	}

    $ip = $wp_cerber->getRemoteIp();
	$ip_long = 0;
	if ( cerber_is_ipv4( $ip ) ) {
		$ip_long = ip2long( $ip );
	}

	$wp_id = 0;
	if ( $wp_query && is_object( $wp_query ) ) {
		$wp_id = absint( $wp_query->get_queried_object_id() );
	}

	$session_id = $wp_cerber->getSessionID();
	if ( is_ssl() ) {
		$scheme = 'https';
	}
	else {
		$scheme = 'http';
	}
	$uri = $scheme . '://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$method = preg_replace( '/[^\w]/', '', $_SERVER['REQUEST_METHOD'] );

	// Request fields

	$fields = '';
	if ( crb_get_settings( 'tifields' ) ) {
		$fields = array();
		if ( ! empty( $_POST ) ) {
			$fields[1] = cerber_prepare_fields( cerber_mask_fields( (array) $_POST ) );
		}
		if ( ! empty( $_GET ) ) {
			$fields[2] = cerber_prepare_fields( (array) $_GET );
		}
		if ( ! empty( $_FILES ) ) {
			$fields[3] = $_FILES;
		}
		if ( ! empty( $fields ) ) {
			$fields = serialize( $fields );
		}
		else {
			$fields = '';
		}
	}

	// Extra request details

    $details = array();
	if ( $ua ) {
		$details[1] = $ua;
	}
	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		//$ref = mb_substr( $_SERVER['HTTP_REFERER'], 0, 1048576 ); // 1 Mb for ASCII
		$details[2] = filter_var( $_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL );
	}
	/*
	if ( ! empty( $_FILES ) ) {
		$details[3] = $_FILES;
	}*/
	if ( $wp_type == 605 && ! empty( $_GET['s'] ) ) {
		$details[4] = $_GET['s'];
	}
	if ( $wp_type == 515 ) {
	    // TODO: add a setting to enable it because there is a user/password in the php://input
		//$details[5] = file_get_contents('php://input');
	}
	if ( crb_get_settings( 'tihdrs' ) ) {
		$hds = getallheaders();
		unset( $hds['Cookie'] );
		$details[6] = $hds;
	}
	if ( crb_get_settings( 'tisenv' ) ) {
		$srv = $_SERVER;
		unset( $srv['HTTP_COOKIE'] );
		$details[7] = $srv;
	}
	if ( crb_get_settings( 'ticandy' ) && ! empty( $_COOKIE ) ) {
		$details[8] = $_COOKIE;
	}
	if ( !empty( $details ) ) {
	    /*
		foreach ( $details as &$detail ) {
			$detail = mb_substr($detail, 0, 1048576); // 1 Mb for ASCII
	    }*/
		$details = cerber_prepare_fields( $details );
		$details = serialize($details);
	}
	else {
		$details = '';
	}

	// Timestamps
	if ( ! empty( $wp_cerber_start_stamp ) && is_numeric( $wp_cerber_start_stamp ) ) {
		$stamp = (float)$wp_cerber_start_stamp; // define this variable: $wp_cerber_start_stamp = microtime( true ); in wp-config.php
	}
	else {
		$stamp = cerber_request_time();
	}

	$processing = (int) ( 1000 * ( microtime( true ) - $stamp ) );

	$uri = cerber_real_escape($uri);
	$fields = cerber_real_escape($fields);
	$details = cerber_real_escape($details);

    //$uri = $wpdb->remove_placeholder_escape( esc_sql( $uri ) );
	//$fields = $wpdb->remove_placeholder_escape( esc_sql( $fields ) );
	//$details = $wpdb->remove_placeholder_escape( esc_sql( $details ) );

	//$query = $wpdb->prepare( 'INSERT INTO ' . CERBER_TRAF_TABLE . ' (ip, ip_long, uri, request_fields , request_details, session_id, user_id, stamp, processing, request_method, http_code, wp_id, wp_type, is_bot, blog_id ) VALUES ("' .$ip .'", '. $ip_long . ', %s, %s, %s, "' . $session_id . '", ' . $user_id . ', ' . $stamp . ',' . $processing . ', "' . $method . '", ' . $http_code . ',' . $wp_id . ', ' . $wp_type . ', ' . $bot . ', ' . absint($blog_id) . ')', $uri, $fields, $details);

	$query = 'INSERT INTO ' . CERBER_TRAF_TABLE . ' (ip, ip_long, uri, request_fields , request_details, session_id, user_id, stamp, processing, request_method, http_code, wp_id, wp_type, is_bot, blog_id ) VALUES ("' . $ip . '", ' . $ip_long . ',"' . $uri . '","' . $fields . '","' . $details . '", "' . $session_id . '", ' . $user_id . ', ' . $stamp . ',' . $processing . ', "' . $method . '", ' . $http_code . ',' . $wp_id . ', ' . $wp_type . ', ' . $bot . ', ' . absint( $blog_id ) . ')';

	//$ret = $wpdb->query( $query );

	$ret = cerber_db_query( $query );

	if (!$ret){

	    // mysqli_error($wpdb->dbh);

		// TODO: Daily software error report
	    /*
		echo mysqli_sqlstate($wpdb->dbh);
		echo $wpdb->last_error;
		echo "<p>\n";
		echo $uri;
		echo "<p>\n";
		echo '<p>ERR '.$query.$wpdb->last_error;
		echo '<p>'.$wpdb->_real_escape( $uri );
	    */
    }

}

/**
 * To log or not to log current request?
 *
 * @param $wp_type integer
 * @param $http_code integer
 * @param $user_id integer
 *
 * @return bool
 * @since 6.0
 */
function cerber_to_log($wp_type = 0, $http_code, $user_id){
    global $cerber_logged;

	$mode = crb_get_settings( 'timode' );

	if ($mode == 0) {
		return false;
	}
	if ($mode == 2) {
		if ($wp_type < 515) { // Pure admin requests
			if ( $wp_type < 502 && ! $user_id ) { // @since 6.3
				return true;
			}
			return false;
		}
		return true;
	}

	// Smart mode ---------------------------------------------------------

	if ( ! empty( $cerber_logged ) ) {
		$tmp = $cerber_logged;
		unset( $tmp[7] );
		unset( $tmp[51] );
		if ( ! empty( $tmp ) ) {
			return true;
		}
	}

	if ($wp_type < 515) {
		if ( $wp_type < 502 && ! $user_id ) { // @since 6.3
			if ( ! empty( $_GET ) || ! empty( $_POST ) || ! empty( $_FILES ) ) {
	            return true;
            }
		}
		return false;
    }

	if ( $http_code >= 400 ||
         $wp_type < 600 ||
	     $user_id ||
         cerber_is_http_post() ||
	     ! empty( $_POST ) ||
	     ! empty( $_FILES ) ||
         isset($_GET['s']) ||
	     cerber_get_non_wp_fields()){
		return true;
	}

	if ( cerber_get_uri_script() ) {
		return true;
	}

	return false;
}

/**
 * Mask sensitive request fields before saving in DB (avoid information leaks)
 *
 * @param $fields array
 *
 * @return array
 * @since 6.0
 */
function cerber_mask_fields( $fields ) {
	$to_mask = array( 'pwd', 'pass', 'password' );
	if ($list = (array)crb_get_settings( 'timask' )){
		$to_mask = array_merge($to_mask, $list);
    }
	foreach ( $to_mask as $mask_field ) {
		if ( ! empty( $fields[ $mask_field ] ) ) {
			$fields[ $mask_field ] = str_pad( '', mb_strlen( $fields[ $mask_field ] ), '*' );
		}
	}

	return $fields;
}

/**
 * Recursive prepare values in array for inserting into DB
 *
 * @param $list
 *
 * @return mixed
 * @since 6.0
 */
function cerber_prepare_fields( $list ) {
	foreach ( $list as &$field ) {
		if ( is_array( $field ) ) {
			cerber_prepare_fields( @$field );
			/*
			foreach ( $field as &$field_2 ) {
				$field_2 = mb_substr( $field_2, 0, 1048576 );  // 1 Mb for ASCII
			}*/
		}
		else {
			if ( ! $field ) {
				$field = '';
			}
			$field = mb_substr( $field, 0, 1048576 );  // 1 Mb for ASCII
		}
	}

	$list = stripslashes_deep( $list );

	return $list;
}

/**
 * Request time
 *
 * @return mixed
 * @since 6.0
 */
function cerber_request_time() {
	static $stamp;

	if ( ! isset( $stamp ) ) {
		if ( ! empty( $_SERVER['REQUEST_TIME_FLOAT'] ) ) { // PHP >= 5.4
			$stamp = $_SERVER['REQUEST_TIME_FLOAT'];
			$stamp = filter_var( $_SERVER['REQUEST_TIME_FLOAT'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		}
		else {
			$stamp = microtime( true );
		}
	}

	return $stamp;
}

/**
 * Return non WP public query $_GET fields
 *
 * @param array $fields An associative array field => value to check
 *
 * @return array
 * @since 6.0
 */
function cerber_get_non_wp_fields($fields = array()) {
	global $wp_query;

	if ( empty( $fields ) ) {
		$get_keys = array_keys( $_GET );
	}
	else {
		$get_keys = array_keys( $fields );
	}

	if ( empty( $get_keys ) ) {
		return array();
	}

	if ( is_object($wp_query) ) {
		$keys = $wp_query->fill_query_vars( array() );
	}
	else {
	    $tmp = new WP_Query();
		$keys = $tmp->fill_query_vars( array() );
    }

    $wp_keys   = array_keys( $keys );  // WordPress GET fields for frontend

	$wp_keys[] = 'redirect_to';
	$wp_keys[] = 'reauth';
	$wp_keys[] = 'action';
	$wp_keys[] = '_wpnonce';
	$wp_keys[] = 'loggedout';

	// WP Customizer fields
	$wp_keys = array_merge($wp_keys, array( 'nonce', '_method', 'wp_customize', 'changeset_uuid', 'customize_changeset_uuid', 'customize_theme', 'theme', 'customize_messenger_channel', 'customize_autosaved' ));

	$ret = array_diff( $get_keys, $wp_keys );

	if ( ! $ret ) {
		$ret = array();
	}

	return $ret;

}


/**
 *
 * @since 6.0
 */
function cerber_beast(){
	global $cerber_status;

	if ( is_admin()
         || ( defined( 'DOING_CRON' ) && DOING_CRON )
	     || ( defined( 'WP_CLI' ) && WP_CLI )
    ) {
		return;
	}

	$wp_cerber = get_wp_cerber();

	$wp_cerber->CheckProhibitedURI();

	if ( !crb_get_settings( 'tienabled' ) ) {
		return;
	}

	$uri = cerber_purify_uri();

	if ( $tiwhite = crb_get_settings( 'tiwhite' ) ) {
		foreach ( (array) $tiwhite as $item ) {
			if ( substr( $item, 0, 1 ) == '{' && substr( $item, - 1 ) == '}' ) {
				$pattern = '/' . substr( $item, 1, - 1 ) . '/i';
				if ( @preg_match( $pattern, $uri ) ) {
					return;
				}
			}
            elseif ( $item == $uri ) {
				return;
			}
		}
	}

	$wp_cerber->InspectRequest();

	$uri_script = cerber_get_uri_script();

	if ( $uri_script && $script_filename = cerber_script_filename() ) {
		// Scaning for executable scripts?
		//if ( false === strrpos( $script_filename, $uri ) ) {
		if ( ! cerber_script_exists( $uri ) && !cerber_is_login_request() ) {
			$wp_cerber = get_wp_cerber();
			$cerber_status = 19;
			cerber_log( 55 );
			cerber_soft_block_add( $wp_cerber->getRemoteIp(), 8);
			//cerber_404_page( true );
			cerber_forbidden_page();
		}
		// Direct access to a PHP script
		if ( ! in_array( $uri_script, cerber_get_wp_scripts() ) ) {
			$deny = false;
			if ( ! cerber_is_acl_permit() ) {
				$deny = true;
				$cerber_status = 14;
			}
            elseif ( ! cerber_is_allowed() ) {
				$deny = true;
				$cerber_status = 13;
			}
            elseif ( lab_is_blocked( null, true ) ) {
				$deny = true;
				$cerber_status = 15;
			}
			if ( $deny ) {
				cerber_log( 50 );
				//cerber_404_page( true );
				cerber_forbidden_page();
			}
		}
	}
}
