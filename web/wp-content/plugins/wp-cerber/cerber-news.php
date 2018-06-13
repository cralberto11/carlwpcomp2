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
if ( ! defined( 'WPINC' ) ) {
	exit;
}


function cerber_push_the_news( $version ) {

	$news['3.0'] =
		'<h3>Welcome a new version with reCAPTCHA and WordPress filters</h3>
<ul>
 	<li>Now you can use Google reCAPTCHA to protect WordPress registration form from spam registrations. Also reCAPTCHA available for lost password and login forms. <a href="https://wpcerber.com/how-to-setup-recaptcha/">How to setup reCAPTCHA</a>.</li>
 	<li>The registration process, WordPress registration form, XML-RPC, WP REST API are controlled by <a href="http://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">IP Access Lists</a>.</li>
 	<li>Registration is impossible if a particular IP address is locked out.</li>
 	<li>Registration with a prohibited username is impossible.</li>
 	<li><a href="http://wpcerber.com/wp-cerber-hooks/">A set of filters and actions</a>. They are useful if you want to customize some aspects of the plugin as you want.</li>
 	<li>A new action <strong>Get WHOIS info</strong> that obtains detailed WHOIS information about given IP address. You can use it in vary <a href="http://jetflow.io">jetFlow.io automation scenarios</a>. For instance, you can monitor countries from what your users are logged in on the website or you <a href="http://wpcerber.com/notifications-on-wordpress-user-logs-in/">monitor user logins with notifications</a>.</li>
 	<li>A new trigger <strong>IP locked out</strong> that starts automation scenario after a suspicious IP address has been locked out by the WP Cerber plugin.</li>
</ul>

';

	$news['4.0'] =
		'<h3>Welcome a new version with extended Access Lists and reCAPTCHA for WooCommerce</h3>
<ul>
 	<li>reCAPTCHA for WooCommerce forms. <a href="https://wpcerber.com/how-to-setup-recaptcha/">How to set up reCAPTCHA</a>.</li>
 	<li>IP Access Lists has got support for IP networks in three forms: ability to restrict access with IPv4 ranges, IPv4 CIDR notation and IPv4 subnets: A,B,C has been added. Read more: <a href="http://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">Access Lists for WordPress</a>.</li>
 	<li>Cerber can automatically detect an IP network of an intruder and suggest you to block the entire network right from the Activity screen.</li>
 	<!-- <li>reCAPTCHA will not be shown and processed for IP addresses from the White IP Access List.</li> -->
</ul>

 <p><a href="https://wpcerber.com/wp-cerber-security-4-0/" target="_blank">Read a full list of changes and improvements</a></p> 
';

	$news['4.3'] =
		'<h3>What\'s new in version 4.3</h3>
<ul>
 	<li>Do you want to keep eye on specific activity on your website? I have good news for you! Track them like a PRO. Use powerful subscriptions to get email notifications according to filters for events you have set. Filter out activities that you are interested to monitor and then click Subscribe. <a href="https://wpcerber.com/wordpress-notifications-made-easy/">Read more</a></li>
 	<li>Search and/or filter activity by IP address, username (login), specific event and a user. You can use any combination of them. </li>
 	<li>Now you can export activity from your WordPress website to a CSV file. You can export all activities or a set of filtered activities only as it described above. When you will import the CSV file in your spreadsheet editor, don\'t forget to select UTF-8 charset.</li>
 	<li>You can use multiple email addresses for notifications (Main Settings -> Notifications -> Email Address). Use a comma to specify several addresses.</li>
</ul>
';

	$news['4.5'][] = 'Instant mobile and browser notifications with Pushbullet. Get notified instantly via push notifications when an important event happens on your WordPress.';
	$news['4.5'][] = 'Ability to choose a 404 page template. The plugin will try to use the 404 template from the active theme, and this is a default behavior or a generate simple 404 page like Apache web server does.';
	$news['4.5'][] = 'Events on the Activity tab are displaying with user roles and avatars.';

	$news['4.7.7'][] = 'Welcome invisible reCAPTCHA. You can choose what type you want to use: classic visible or new invisible reCAPTCHA. <a target="_blank" href="http://wpcerber.com/how-to-setup-recaptcha/">How to setup</a>.';
	$news['4.7.7'][] = 'reCAPTCHA for comment forms is available now. Stop spam comments – use reCAPTCHA as an anti-spam tool for WordPress comment forms.';
	$news['4.7.7'][] = 'Dates on the plugin dashboard now are displayed according to the Site Language.';
	$news['4.7.7'][] = '<a target="_blank" href="https://www.facebook.com/pg/wpcerber/reviews/">You can leave a review on Facebook now.</a>';

	$news['5.0'][] = 'Cerber has got a new antispam and bot detection engine that protects comment and user registration forms from bot attacks. No reCAPTCHA is needed anymore! After several attempts bot IP will be locked out.';
	$news['5.0'][] = 'Now you can tell Cerber either to mark detected spam comments as spam or deny them completely.';
	$news['5.0'][] = 'Optionally Cerber can automatically move spam comments older than the specified amount of days to trash.';
	$news['5.0'][] = 'Added code to avoid possible conflict between Custom login URL and REST API.';
	$news['5.0'][] = 'Added the <i>cerber_404_template</i> filter for specifying an alternative to the default 404 page not found template.';

	$news['5.1'][] = 'Antispam and anti-bot engine for contact and other forms. Cerber antispam and bot detection engine now protects all forms on a website. It’s compatible with virtually any form. Tested with Caldera Forms, Gravity Forms, Contact Form 7, Ninja Forms, Formidable Forms, Fast Secure Contact Form, Contact Form by WPForms.';
	$news['5.1'][] = 'Portuguese of Portugal translation has been added, thanks to Helderk.';

	$news['5.5'][] = 'White list for the <a href="http://wpcerber.com/antispam-for-wordpress-contact-forms/">WordPress anti-spam engine</a>. It allows creating a list of exceptions by specifying a string to search it in a request URI.';
	$news['5.5'][] = 'White list for REST API requests. It allows creating a list of namespace exceptions if REST API is disabled.';
	$news['5.5'][] = 'Disable access to user data via REST API and stop REST API user enumeration.';

	$news['5.8'][] = 'Weekly reports. Now the plugin will send a brief performance report (activity for past seven days) to specified email addresses. Weekly reports are sent once a week. Set desired reporting time on the Notification admin page.';
	$news['5.8'][] = 'Plugin admin interface pages: compatibility with screen readers has been improved.';
	$news['5.8'][] = 'Compatibility with caching plugins is improved: define( ‘DONOTCACHEPAGE’, true ) is added for Custom login page.';
	$news['5.8.2'][] = 'REST API: the deprecated rest_enabled filter is used for WordPress older than 4.7.';

	$news['5.8.6'][] = 'Regular expressions (REGEX) in the list of prohibited usernames.';
	$news['5.8.6'][] = 'Ability to enable/disable weekly reports, a new field to specify email addresses for weekly reports.';
	$news['5.8.6'][] = 'Your last login date, IP address, and country will be shown in a report email.';
	$news['5.8.6'][] = 'Improved compatibility with non-standard authentication processes, WooCommerce and exotic/outdated hosting environments.';
	$news['5.8.6'][] = 'Bug fixed: Some interface elements of WordPress Customizer might not work, depending on the theme you use.';

	$news['5.9'][] = 'You can add comments for new entries in the access lists';
	$news['5.9'][] = 'Improved compatibility with exotic hosting environments: now the plugin handles URLs with the MultiViews server option enabled.';
	$news['5.9'][] = 'Improved compatibility with caching plugins';
	$news['5.9'][] = 'The plugin doesn’t send cookies if anti-spam is completely disabled';
	$news['5.9'][] = 'Bug fixed: The plugin logs a logout event if the actual logout doesn’t happen';

	$news['6.0'][] = 'Traffic Inspector. It’s a specialized request inspection algorithm that acts as additional protection layer. Since v 6.0 WP Cerber Security performs inspection all suspicious requests and block them before they can harm a website. This security algorithm is enabled by default and requires no configuration.';
	$news['6.0'][] = 'Traffic Inspector optionally logs all or just suspicious and malicious requests so you can inspect them.';
	$news['6.0'][] = 'Added ability to clean up Cerber’s DB tables. Now you can manually delete all rows in a Cerber’s DB table on the Tools / Diagnostic admin page. Note: this operation cannot be rolled back.';
	$news['6.0'][] = 'If your hosting environment (web server) has some issues and those issues can affect plugin functionality, they are shown on the Tools / Diagnostic page.';
	$news['6.0'][] = 'On the Access Lists admin page there are new links for each entry to check HTTP requests from a particular network or an IP address.';

	$news['6.1'][] = 'Traffic Inspector has got a Request White List setting. To exclude a particular request from inspection specify a request string without the website domain and query string (GET parameters).';
	$news['6.1'][] = 'An Activity filter has been added to the Advanced search form on the Traffic Inspector page.';
	$news['6.1'][] = 'Bug fixed: Two reCAPTCHA widgets on login/registration forms.';
	$news['6.1'][] = 'Bug fixed: A legitimate IP address can be locked out by Traffic Inspector on a Windows hosting (server).';

	$news['6.2'][] = 'Protection against (DoS) attacks that exploit recently discovered vulnerability (CVE-2018-6389). The protection mechanism is disabled by default. To enable it navigate to the Hardening tab and enable Block unauthorized access to load-scripts.php and load-styles.php. After enabling this setting a set of security rules will be added to the .htaccess file.';
	$news['6.2'][] = 'The Traffic Inspector algorithm detects malformed and double extensions like .php.jpg more precisely';
	$news['6.2'][] = 'The access Lists now accept IPv6 addresses in any form. You can enter a shortened IPv6 (short form) or a full IPv6. A full IPv6 will be shortened to the short IPv6 address representation. All existing in access lists IP addresses are converted to the short form.';
	$news['6.2'][] = 'Bug fixed: If the WP REST API is blocked, a request with specially malformed URL can bypass protection. Thanks to Tomasz Wasiak.';
	$news['6.2'][] = 'Bug fixed: An IPv4 range in the Access Lists might not work as expected, depending on server/site settings.';

	$news['6.5'][] = 'This version brings a new, advanced and more effective way of loading plugin modules and the plugin security engine. This allows Cerber Security to intercept and inspect suspicious requests more effectively and protect WordPress against attacks that try to exploit a vulnerability in a plugin or a theme. It’s advised to enable loading in Standard mode. To enable this new mode go to the Main Settings plugin admin page and set the Load security engine setting to Standard mode.';
	$news['6.5'][] = 'Traffic Inspector’s algorithms detect and deny any attempt to upload executable files (including PHP and JavaScript) or .htaccess file via any public form or POST request.';
	$news['6.5'][] = 'A new setting to disable email notifications about new versions of the plugin. It’s located on the Notification admin page in the Email notifications section.';
	$news['6.5'][] = 'Search in the traffic log. Now it’s possible to search in the User agent string and filter out the HTTP method (GET/POST).';
	$news['6.5'][] = 'In the Smart mode if a user is not logged in, all requests to the admin dashboard are logged.';
	$news['6.5'][] = 'Performance of the logging subsystem is improved: the DB query has been updated.';

	$news['6.7'][] = 'Antispam engine algorithms have been updated to improve AJAX requests handling and reduce false positives.';
	$news['6.7'][] = 'Improved compatibility with WooCommerce, Formidable Forms, Gravity Forms and AJAX file upload.';
	$news['6.7'][] = 'Regular expressions are now available for the Traffic Inspector Request whitelist and Antispam Query whitelist.';
	$news['6.7'][] = 'Any symbols other than letters, numbers, dashes and underscores are not permitted for Custom login URL anymore. Previously configured URLs will work fine.';
	$news['6.7'][] = 'Bug fixed: the Safe antispam mode doesn\'t work correctly on some website configurations. That may leads to false positives and erroneous spam form submission detection.';
	$news['6.7'][] = 'Norwegian Bokmål and Dutch language translations have updated. Thanks to Jos Knippen and Eirik Vorland.';


	if ( ! empty( $news[ $version ] ) ) {
		//$text = '<h3>What\'s new in WP Cerber '.$version.'</h3>';

		$text = '<h3>Highlights from WP Cerber Security '.$version.'</h3>';

		$text .= '<ul><li>'.implode('</li><li>', $news[ $version ]).'</li></ul>';

		$text .= '	<p style="margin-top: 18px; font-weight: bold;"><a href="https://wpcerber.com/?plugin_version='.$version.'" target="_blank">Read more on wpcerber.com</a></p>';

		$text .= '	<p style="margin-top: 24px;"><span class="dashicons-before dashicons-email-alt"></span> &nbsp; <a href="https://wpcerber.com/subscribe-newsletter/">Subscribe to Cerber\'s newsletter</a></p>
					<p><span class="dashicons-before dashicons-twitter"></span> &nbsp; <a href="https://twitter.com/wpcerber">Follow Cerber on Twitter</a></p>
					<p><span class="dashicons-before dashicons-facebook"></span> &nbsp; <a href="https://www.facebook.com/wpcerber/">Follow Cerber on Facebook</a></p>
				';
		cerber_admin_info( $text );
	}
}


function cerber_admin_info($msg, $type = 'normal'){

	$crb_assets_url = cerber_plugin_dir_url() . 'assets/';

	update_site_option('cerber_admin_info',
		'<table><tr><td><img style="float:left; margin-left:-10px;" src="'.$crb_assets_url.'icon-128x128.png"></td>'.
		'<td>'.$msg.
		'<p style="text-align:right;">
		<input type="button" class="button button-primary cerber-dismiss" value=" &nbsp; '.__('Cool!','wp-cerber').' &nbsp; "/></p></td></tr></table>');
}


