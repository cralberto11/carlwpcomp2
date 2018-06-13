=== Cerber Security & Antispam ===
Contributors: gioni
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SR8RJXFU35EW8
Tags: security, login, custom login url, antispam, limit login attempts, firewall, recaptcha, captcha, activity, log, logging, whitelist, blacklist, access list
Requires at least: 4.5
Requires PHP: 5.4
Tested up to: 4.9
Stable tag: 6.7.5
License: GPLv2

Protection against hacker attacks and bots. Restrict access with IP access lists, track user and bot activity. reCAPTCHA. Limit login attempts.

== Description ==

Defends WordPress against brute force attacks by limiting the number of login attempts through the login form, XML-RPC / REST API requests or using auth cookies.
Restricts access with the Black IP Access List and the White IP Access List.
Tracks user and intruder activity with powerful email, mobile and desktop notifications.
Stop spam: activates Cerber antispam engine and Google reCAPTCHA for protecting registration, contact and comments forms.
Hardening WordPress with a set of security rules and comprehensive algorithms.

**Features you will love**

* Limit login attempts when logging in by IP address or entire subnet.
* Monitors logins made by login forms, XML-RPC requests or auth cookies.
* Permit or restrict access by [White IP Access list and Black IP Access List](https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/) with a single IP, IP range or subnet.
* Create **Custom login URL** ([rename wp-login.php](https://wpcerber.com/how-to-rename-wp-login-php/)).
* Cerber antispam engine for protecting any contact form. Automatically detects and moves spam comments to trash or deny it completely.
* Log users, bots, hacker and other suspicious activities.
* Cool notifications with powerful event filters.
* Hide wp-login.php, wp-signup.php and wp-register.php from possible attacks.
* Hide wp-admin (dashboard) when a user isn't logged in.
* Immediately block an IP or a subnet when attempting to log in with non-existent or prohibited username.
* Restrict user registration or login with a username matching REGEX patterns.
* Disable WP REST API or restrict access with your own security rules
* Disable XML-RPC (block access to the XML-RPC interface including Pingbacks and Trackbacks)
* Disable feeds (block access to the RSS, Atom and RDF feeds)
* Restrict access to XML-RPC, REST API and feeds by **White IP Access list** by an IP address or an IP range.
* Disable automatic redirection to the login page.
* **Stop user enumeration** (block access to pages like /?author=n and user REST API)
* Proactively **blocks IP subnet class C** for intruder's IP.
* Anti-spam: **reCAPTCHA** to protect WordPress login, register and comment forms.
* [reCAPTCHA for WooCommerce & WordPress forms](https://wpcerber.com/how-to-setup-recaptcha/).
* Invisible reCAPTCHA for WordPress comments forms
* Citadel mode for **massive brute force attack**.
* [Play nice with **fail2ban**](https://wpcerber.com/how-to-protect-wordpress-with-fail2ban/): write failed attempts to the syslog or a custom log file.
* Filter out and inspect activities by IP address, user, username or a particular activity.
* Filter out activities and export them to a CSV file.
* Reporting: get weekly reports to specified email addresses.
* Limit login attempts works on a site/server behind a reverse proxy.
* [Get notifications by email or via mobile push notifications](https://wpcerber.com/wordpress-notifications-made-easy/).
* Trigger and action for the [jetFlow.io automation plugin](http://jetflow.io).
* Protection against (DoS) attacks (CVE-2018-6389).

= Limit login attempts done right =

By default, WordPress allows unlimited login attempts through the login form, XML-RPC or by sending special cookies. This allows passwords to be cracked with relative ease via brute force attack.

WP Cerber blocks intruders by IP or subnet from making further attempts after a specified limit on retries is reached, making brute force attacks or distributed brute force attacks from botnets impossible.

You will be able to create a **Black IP Access List** or **White IP Access List** to block or allow logins from a particular IP address, IP address range or a subnet any class (A,B,C).

Moreover, you can create your Custom login page and forget about automatic attacks to the default wp-login.php, which takes your attention and consumes a lot of server resources. If an attacker tries to access wp-login.php they will be blocked and get a 404 Error response.

= Log, filter out and export activities =

WP Cerber tracks time, IP addresses and usernames for successful and failed login attempts, logins, logouts, password changes, blocked IP and actions taken by itself. You can export them to a CSV file.

= Limit login attempts reinvented =

You can **hide WordPress dashboard** (/wp-admin/) when a user isn't logged in. If a user isn't logged in and they attempt to access the dashboard by requesting /wp-admin/, WP Cerber will return a 404 Error.

Massive botnet brute force attack? That's no longer a problem. **Citadel mode** will automatically be activated for awhile and prevent your site from making further attempts to log in with any username.

= Cerber antispam engine =

Anti-spam and anti-bot protection for contact, registration, comments and other forms.
Cerber antispam and bot detection engine now protects all forms on a website. No reCAPTCHA is needed.
It’s compatible with virtually any form you have. Tested with Caldera Forms, Gravity Forms, Contact Form 7, Ninja Forms, Formidable Forms, Fast Secure Contact Form, Contact Form by WPForms.

= Anti-spam protection: invisible reCAPTCHA for WooCommerce =

* WooCommerce login form
* WooCommerce register form
* WooCommerce lost password form

= Anti-spam protection: invisible reCAPTCHA for WordPress =

* WordPress login form
* WordPress register form
* WordPress lost password form
* WordPress comment form

**Documentation & Tutorials**

* [How to set up notifications](https://wpcerber.com/wordpress-notifications-made-easy/)
* [Push notifications with Pushbullet](https://wpcerber.com/wordpress-mobile-and-browser-notifications-pushbullet/)
* [How to set up invisible reCAPTCHA for WooCommerce](https://wpcerber.com/how-to-setup-recaptcha/)
* [Changing default plugin messages](https://wpcerber.com/wordpress-hooks/)
* [Best alternatives to the Clef plugin](https://wpcerber.com/two-factor-authentication-plugins-for-wordpress/)
* [Why reCAPTCHA does not protect WordPress from bots and brute-force attacks](https://wpcerber.com/why-recaptcha-does-not-protect-wordpress/)

**Translations**

* Czech, thanks to [Hrohh](https://profiles.wordpress.org/hrohh/)
* Deutsche, thanks to mario, Mike and [Daniel](http://detacu.de)
* Dutch, thanks to Jos Knippen and [Bernardo](https://twitter.com/bernardohulsman)
* Français, thanks to [hardesfred](https://profiles.wordpress.org/hardesfred/)
* Norwegian (Bokmål), thanks to [Eirik Vorland](https://www.facebook.com/KjellDaSensei)
* Portuguese (Portugal), thanks to Helderk
* Portuguese (Brazil), thanks to [Felipe Turcheti](http://felipeturcheti.com)
* Polski, thanks to [Wojciech Górski](https://www.facebook.com/profile.php?id=100010484049780)
* Spanish, thanks to Ismael Murias and [leemon](https://profiles.wordpress.org/leemon/)
* Український, thanks to [Nadia](https://profiles.wordpress.org/webbistro)
* Русский, thanks to [Yui](https://profiles.wordpress.org/fierevere/)
* Italian, thanks to [Francesco Venuti](http://www.algostream.it/)
* Swedish, thanks to Fredrik Näslund

Thanks to [POEditor.com](https://poeditor.com) for helping to translate this project.

There are some semi-similar security plugins you can check out: Login LockDown, Login Security Solution,
BruteProtect, Ajax Login & Register, Lockdown WP Admin, Loginizer,
BulletProof Security, SiteGuard WP Plugin, All In One WP Security & Firewall, Brute Force Login Protection

**Another reliable plugins from the trusted author**

* [Plugin Inspector reveals issues with installed plugins](https://wordpress.org/plugins/plugin-inspector/)

Checks plugins for deprecated WordPress functions, known security vulnerabilities, and some unsafe PHP functions

* [Translate sites with Google Translate Widget](https://wordpress.org/plugins/goo-translate-widget/)

Make your website instantly available in 90+ languages with Google Translate Widget. Add the power of Google automatic translations with one click.

== Installation ==

Installing the WP Cerber Security & Antispam plugin is the same as other WordPress plugins.

1. Install the plugin through Plugins > Add New > Upload or unzip plugin package into wp-content/plugins/.
2. Activate the WP Cerber through the Plugins > Installed Plugins menu in the WordPress admin dashboard.
3. The plugin is now active and has started protecting your WordPress with default settings.
4. Make sure, that you've got a notification letter to your site admin email.
5. It's advised to enable Standard mode for the "Load security engine" setting.
5. Read carefully: [Getting Started Guide](https://wpcerber.com/getting-started/)

**Important notes**

1. Before enabling invisible reCAPTCHA, you must obtain separate keys for the invisible version. [How to enable reCAPTCHA](https://wpcerber.com/how-to-setup-recaptcha/).
2. If you want to test out plugin's features, do this on another computer (or incognito browser window) and remove computer IP address or network from the White Access List. Cerber is smart enough to recognize "the boss".
3. If you've set up the Custom login URL and you use some caching plugin like **W3 Total Cache** or **WP Super Cache**, you have to add the new Custom login URL to the list of pages not to cache.
4. [Read this if your website is under CloudFlare](https://wpcerber.com/cloudflare-and-wordpress-cerber/)
5. If you use the Jetpack plugin or another plugin that needs to connect to wordpress.com, you need to unlock XML-RPC. To do that go to the Hardening tab, uncheck Disable XML-RPC, and click the Save changes button.

The following steps are optional but they allow you to reinforce the protection of your WordPress.

1. Fine tune **Limit login attempts** settings making them more restrictive according to your needs
2. Configure your **Custom login URL** and remember it (the plugin will send you an email with it).
3. Once you have configured Custom login URL, check 'Immediately block IP after any request to wp-login.php' and 'Block direct access to wp-login.php and return HTTP 404 Not Found Error'. Don't use wp-admin to log in to your WordPress dashboard anymore.
4. If your WordPress has a few experienced users, check 'Immediately block IP when attempting to log in with a non-existent username'.
5. Specify the list of prohibited usernames (logins) that legit users will never use. They will not be permitted to log in or register.
6. Configure mobile and browser notifications via Pushbullet.
7. Obtain keys and enable invisible reCAPTCHA for password reset and registration forms (WooCommerce supported too).


== Frequently Asked Questions ==

= Can I use the plugin with CloudFlare? =

Yes.  [WP Cerber settings for CloudFlare](https://wpcerber.com/cloudflare-and-wordpress-cerber/).

= Is WP Cerber Security compatible with WordPress multisite mode? =

Yes. All settings apply to all sites in the network simultaneously. You have to activate the plugin in the Network Admin area on the Plugins page. Just click on the Network Activate link.

= Is WP Cerber Security compatible with bbPress? =

Yes. [Compatibility notes](https://wpcerber.com/compatibility/).

= Is WP Cerber Security compatible with WooCommerce? =

Completely.

= Is reCAPTCHA for WooCommerce free feature? =

Yes. [How to set up reCAPTCHA for WooCommerce](https://wpcerber.com/how-to-setup-recaptcha/).

= Are there any incompatible plugins? =

The following plugins can cause some issues: Ultimate Member, WPBruiser {no- Captcha anti-Spam}, Plugin Organizer, WP-SpamShield.
The Cerber Security plugin won't be updated to fix any issue or conflict related to them, you should decide and stop using one or all of them.
Read more: [https://wpcerber.com/compatibility/](https://wpcerber.com/compatibility/).

= Can I change login URL (rename wp-login.php)? =

Yes, easily. [How to rename wp-login.php](https://wpcerber.com/how-to-rename-wp-login-php/)

= Can I hide the wp-admin folder? =

Yes, easily. [How to hide wp-admin and wp-login.php from possible attacks](https://wpcerber.com/how-to-hide-wp-admin-and-wp-login-php-from-possible-attacks/)

= Can I rename the wp-admin folder? =

Nope. It's not possible and not recommended for compatibility reasons.

= Can I hide the fact I use WordPress? =

No. We strongly encourage you not to use any plugin that renames wp-admin folder to protect a website.
 Beware of all plugins that hide WordPress folders or other parts of a website and claim this as a security feature.
 They are not capable to protect your website. Don't be silly, hiding some stuff doesn't make your site more secure.

= Can WP Cerber Security work together with the Limit Login Attempts plugin? =

Nope. WP Cerber is a drop in replacement for that outdated plugin.

= Can WP Cerber Security protect my site from DDoS attacks? =

Nope. The plugin protects your site from Brute force attacks or distributed Brute force attacks. By default WordPress allows unlimited login attempts either through the login form or by sending special cookies. This allows passwords to be cracked with relative ease via a brute force attack. To prevent from such a bad situation use WP Cerber.

= Is there any WordPress plugin to protect my site from DDoS attacks? =

Nope. This hard task cannot be done by using a plugin. That may be done by using special hardware from your hosting provider.

= What is the goal of Citadel mode? =

Citadel mode is intended to block massive bot (botnet) attacks and also a slow brute force attack. The last type of attack has a large range of intruder IPs with a small number of attempts to login per each.

= How to turn off Citadel mode completely? =

Set Threshold fields to 0 or leave them empty.

= What is the goal of using Fail2Ban? =

With Fail2Ban you can protect site on the OS level with iptables firewall. See details here: [https://wpcerber.com/how-to-protect-wordpress-with-fail2ban/](https://wpcerber.com/how-to-protect-wordpress-with-fail2ban/)

= Do I need to use Fail2Ban to get the plugin working? =

No, you don't. It is optional.

= Is WP Cerber Security compatible with other security plugins like WordFence, iThemes Security, Sucuri, NinjaFirewall, All In One WP Security & Firewall, BulletProof Security? =

The plugin has been tested with WordFence and no issues have been noticed. Other plugins also should be compatible.

= Can I use this plugin on the WP Engine hosting? =

Yes! WP Cerber Security is not on the list of disallowed plugins.

= Is the plugin compatible with Cloudflare? =

Yes, read more: https://wpcerber.com/cloudflare-and-wordpress-cerber/

= Does the plugin works on websites with SSL(HTTPS) =

Absolutely!

= It seems that old activity records are not removing from the activity log =

That means that scheduled tasks are not executed on your site. In other words, WordPress cron is not working the right way.
Try to add the following line to your wp-config.php file:

define( 'ALTERNATE_WP_CRON', true );

= I'm unable to log in / I'm locked out of my site / How to get access (log in) to the dashboard? =

There is a special version of the plugin called **WP Cerber Reset**. This version performs only one task. It resets all WP Cerber settings to their initial values (excluding Access Lists) and then deactivates itself.

To get access to your dashboard you need to copy the WP Cerber Reset folder to the plugins folder. Follow these simple steps.

1. Download the wp-cerber-reset.zip archive to your computer using this link: [https://wpcerber.com/downloads/wp-cerber-reset.zip](https://wpcerber.com/downloads/wp-cerber-reset.zip)
2. Unpack wp-cerber folder from the archive.
3. Upload the wp-cerber folder to the **plugins** folder of your site using any FTP client or a file manager from your hosting control panel. If you see a question about overwriting files, click Yes.
4. Log in to your site as usually. Now WP Cerber is disabled completely.
5. Reinstall the WP Cerber plugin again. You need to do that, because **WP Cerber Reset** cannot work as a normal plugin.

== Screenshots ==

1. The Dashboard: Recently recorded important security events and recently locked out IP addresses.
2. WordPress activity log with filtering, export to CSV and powerful notifications. You can see what's going on right now, when an IP reaches the limit of login attempts and when it was blocked.
3. Activity log filtered by login and specific type of activity. Export it or click Subscribe to be notified with each event.
4. Detailed information about an IP address with WHOIS information.
5. These settings allows you to customize the plugin according to your needs.
6. White and Black IP access lists allow you to restrict access from a particular IP address, network or IP range.
7. Hardening WordPress: disable REST API, XML-RPC and stop user enumeration.
8. Powerful email, mobile and browser notifications for WordPress events.
9. Stop spammer: visible/invisible reCAPTCHA for WooCommerce and WordPress forms - no spam comments anymore.
10. You can export and import security settings and IP Access Lists on the Tools screen.
11. Beautiful widget for the WP dashboard to keep an eye on things. Get quick analytic with trends over last 24 hours.
12. WP Cerber adds four new columns on the WordPress Users screen: Date of registration, Date of last login, Number of failed login attempts and Number of comments. To get more information just click on the appropriate link.


== Changelog ==

= 6.7.5 =
* A new button View Activity has been added to the user edit page in the WordPress dashboard.
* Miscellaneous code optimizations: performance of database routines and SQL queries are improved.
* A new Swedish translation has been added. Thanks to Fredrik Näslund.
* Bug fixed: The wildcard *.*.*.* entry (all IPv4 addresses) to the Black IP Access List, doesn't work as intended.

= 6.7 =
* New: Regular expressions are now available for the Traffic Inspector Request whitelist and Antispam Query whitelist.
* Update: Antispam engine algorithms have been updated to improve AJAX requests handling and reduce false positives.
* Update: Improved compatibility with WooCommerce, Formidable Forms, Gravity Forms and AJAX file upload.
* Update: Any symbols other than letters, numbers, dashes and underscores are not permitted in Custom login URL anymore.
* Bug fixed: The Safe antispam mode doesn’t work correctly on some website configurations. That may lead to false positives and erroneous spam form submission detection.
* [Read more](https://wpcerber.com/wp-cerber-security-6-7/)

= 6.5 =
* New: A new, advanced initialization mode which reinforces overall security performance.
* New: Traffic Inspector's algorithms detect and deny any attempt to upload executable files or an .htaccess file via any POST request.
* New: A new setting to disable email notifications about new versions of the plugin.
* New: Search in the traffic log improved. Search in the User agent string and filter out the HTTP method (GET/POST) are available.
* Update: Performance of the logging subsystem is improved.
* Update: In the Smart mode if a user is not logged in, all requests to the admin dashboard are logged.
* Bug fixed: If a user tries to log in with an email address and an incorrect password, the "Invalid username" message is shown.
* Bug fixed: On a multisite installation with websites in subdirectories a user activation link doesn't work.
* [Read more](https://wpcerber.com/wp-cerber-security-6-5/)

= 6.2 =
* New: Protection against (DoS) attacks that exploit recently discovered vulnerability (CVE-2018-6389).
* New: The Traffic Inspector algorithm detects malformed and double extensions like .php.jpg more precisely.
* New: The Access Lists now accept IPv6 addresses in any form and handle them in a shortened form. All existing IPs will be converted.
* Bug fixed: If the WP REST API is blocked, a request with a specially malformed URL can bypass protection. Thanks to Tomasz Wasiak.
* Bug fixed: An IPv4 range in the Access Lists might not work as expected, depending on server/site settings.

= 6.1 =
* New: Traffic Inspector has got a Request White List setting.
* New: An Activity filter for the Advanced search form on the Traffic Inspector page.
* Bug fixed: Two reCAPTCHA widgets on login/registration forms.
* Bug fixed: A legitimate IP address can be locked out by Traffic Inspector on a Windows hosting (server).

= 6.0 =
* New: Traffic Inspector. It’s a specialized request inspection algorithm that performs inspection all suspicious incoming HTTP requests and block them before they can harm a website.
* New: Traffic Inspector optionally logs all or just suspicious and malicious requests so you can inspect them.
* New: Added ability to clean up Cerber’s DB tables.
* New: If the web server has some issues and those issues can affect plugin functionality, they are shown on the Diagnostic page.
* Added protection to prevent scheduled tasks from being executed multiple times an hour.
* JavaScript antispam code is improved to eliminate excessive fields in GET requests.
* To eliminate possible warning messages, the inet_pton() function has been replaced with filter_var().

= 5.9 =
* New: You can add comments for new entries in the access lists
* Improved compatibility with exotic hosting environments: now the plugin handles URLs with the MultiViews server option enabled.
* Improved compatibility with caching plugins
* Bug fixed: The plugin logs a logout event if the actual logout doesn't happen
* [Read more](https://wpcerber.com/wp-cerber-security-5-9/)

= 5.8.6 =
* New: Regular expressions (REGEX) in the list of prohibited usernames.
* New: Enable/disable weekly reports, a new setting to specify email addresses for weekly reports.
* Improved compatibility with non-standard authentication processes, WooCommerce and exotic/outdated hosting environments.
* Bug fixed: Some interface elements of WordPress Customizer might not work.
* [Read more](https://wpcerber.com/wp-cerber-security-5-8-6/)

= 5.8 =
* New: Now the plugin will send a brief security report (activity for past seven days) to specified email addresses.
* Plugin admin interface pages: compatibility with screen readers has been improved.
* REST API: the deprecated rest_enabled filter is used for WordPress older than 4.7.
* Bug fixed: After updating the plugin to the 5.7 version some disabled checkboxes (and corresponding disabled settings) are set to their default, enabled states.
* Bug fixed: An IP address in the white access list may be locked out as a suspicious IP.
* [Read more](https://wpcerber.com/wp-cerber-security-5-8/)

= 5.7 =
* New: Limit access to WordPress REST API for logged in users only.
* New: For new users the plugin records the date of registration, the IP address and a user who has added a new user.
* New: Sorting users on the Users admin page by date of registration.
* New: User registration monitoring and activity logging functions has been improved.
* Translations has been updated, thanks to Jon Knippen, Wojciech Górski and Francesco.
* Bug fixed: Stop user enumeration via REST API doesn’t work on a multisite WordPress installation.
* [Read more](https://wpcerber.com/wp-cerber-security-5-7/)

= 5.5 =
* New: White list for the WordPress anti-spam engine.
* New: White list for REST API requests.
* New: Disable access to user data via REST API and stop REST API user enumeration.
* [Read more](https://wpcerber.com/wp-cerber-security-5-5/)

= 5.2 =
* Bug fixed: Hidden custom login URL may be discovered by using specially formatted URL.
* Bug fixed: Customized CSS styles don’t work on the Custom login page.

= 5.1 =
* New: Anti-spam and anti-bot for contact and other forms. Cerber antispam and bot detection engine now protects all forms on a website. It’s compatible with virtually any form. Tested with Caldera Forms, Gravity Forms, Contact Form 7, Ninja Forms, Formidable Forms, Fast Secure Contact Form, Contact Form by WPForms.
* New: Portuguese of Portugal translation has been added, thanks to Helderk.
* Bug fixed: A user with admin account is unable to approve comments with pending status in the WordPress Dashboard.

= 5.0 =
* New: A new antispam and bot detection engine that protects comment and user registration forms from bot attacks. After several attempts bot IP will be locked out.
* New: You can tell Cerber either to mark detected spam comments as spam or deny them completely.
* New: Cerber can automatically move spam comments older than the specified amount of days to trash.
* New: Added the cerber_404_template filter for specifying an alternative to the default 404 page not found template.
* New: Added code to avoid possible conflict between Custom login URL and REST API.
* New: Italian translation has been added, thanks to Francesco Venuti.
* Bug fixed: WordPress database error: Table '...cerber_lab_net' doesn't exist.

= 4.9 =
* New: Additional details will be logged and displayed on the Activity page: the URL of a request and decision the plugin engine had made.
* New: Added a nice panel with performance indicators showing key events and plugin performance in the last 24 hours.
* New: To improve reliability self check-up code has been added.
* New: Polish translation has been added, thanks to Wojciech Górski.
* New: On a multisite WP installation scheduled tasks will be executed once per hour for the entire network: there will no excess SQL queries when the plugin executes hourly cron tasks.
* Bug fixed: The language for visible reCAPTCHA doesn't set according to the site language setting. It's always English.

= 4.8.2 =
* New: Starting with this version all database tables will be created with a default database engine. It should be InnoDB.
* New: To improve compatibility with some plugins the email notification function has been updated and now uses the comma-separated list of email addresses instead of an array.
* Bug fixed: An IP address from a range might not be allowed to log in if you have overlapping IP ranges in the both IP Access List.
* Bug fixed: A reason of blocking an IP address is not shown in notification emails if Always block entire subnet Class C of intruders IP is selected in the settings.

= 4.8 =
* New: You can enable/disable applying limit login rules to IP addresses in the White IP Access List.
* New: Block malicious IP addresses after a specified number of failed attempts to solve visible or invisible reCAPTCHA.
* New: Track password reset requests with username entered.

= 4.7.7 =
* New: invisible reCAPTCHA (classic, visible also available).
* New: reCAPTCHA for comment forms. Works well as anti-spam tool.
* Fixed bug: "Add network to the Black List" and "Add IP to the Black List" buttons on the Activity tab doesn't work in the Safari web browser.

= 4.5 =
* New: Instant mobile and browser notifications with Pushbullet.
* New: Ability to choose a 404 page template.
* New: Events on the Activity tab are displaying with user roles and avatars.
* Update: PHP function file_get_contents() has been replaced with cURL to improve compatibilty with restrictive hostings.
* Fixed bug: Password reset link that is generated by the WooCommerce reset password form can be corrupted if reCAPTCHA is enabled for the form.
* Fixed bug: The plugin doesn’t block IPv6 addresses from the Black IP Access List (versions affected: 4.0 – 4.3).

= 4.3 =
* New: Use powerful subscriptions to get email notifications according to filters for events you have set.
* New: Search and/or filter activity by IP address, username (login), specific event and a user. You may use any combination of them.
* New: Now you can export activity from your WordPress website to a CSV file. You may export all activities or just a set of filtered out activities.
* Update: Now you can specify multiple email boxes for notifications.
* Update: The Spanish translation has been updated, thanks to [leemon](https://profiles.wordpress.org/leemon/).

= 4.1 =
* New: Date format field allows you to specify a desirable format for displaying dates and time.
* Updated code for registration_errors filter to handle errors right way.
* The French translation has been updated.
* Fixed issue: Loading settings from a file with reCAPTCHA key and secret on a different website overwrite existing reCAPTCHA key and secret with values from the file.
* Fixed bug: The plugin tries to validate reCAPTCHA on WooCommerce login form if the validation enabled for the default WordPress login form only.

= 4.0 =
* New: reCAPTCHA for WooCommerce forms. [How to set up reCAPTCHA](https://wpcerber.com/how-to-setup-recaptcha/).
* New: IP Access Lists has got support for IP networks in three forms: ability to restrict access with IPv4 ranges, IPv4 CIDR notation and IPv4 subnets: A,B,C has been added. [Access Lists for WordPress](https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/).
* New: Cerber can automatically detect an IP network of an intruder and suggest you to block entire network right from the Activity screen.
* New: Norwegian translation added, thanks to [Eirik Vorland](https://www.facebook.com/KjellDaSensei).
* Update: WP REST API is controlled by Access Lists. While REST API is blocked for the rest of the world, IP addresses from the White Access List can use WP REST API.
* Update: The WP Cerber admin menu is moved from Settings to the main admin menu.
* Update: To make Cerber more compatible with other plugins, the order of the init hook on the Custom login page (Custom login URL) has been changed.
* Update: Several languages and translations has been updated.
* Update: Large amount of code has been rewritten to improve performance and stability.
* Fixed bug: If a hacker or a bot uses login from the list of prohibited usernames or non-existent username, Citadel mode is unable to be automatically activated.
* Fixed bug: reCAPTCHA for an ordinary WordPress login form is incompatible with a WooCommerce login form.
* Fixed issue: In some cases the plugin log first digits of an IP address as an ID of existing user.

= 3.0 =
* New: [reCAPTCHA to protect WordPress forms spam registrations. Also available for lost password and login forms.](https://wpcerber.com/how-to-setup-recaptcha/)
* New: Registration, XML RCP, WP REST API are controlled by IP Access Lists now. If a particular IP address is locked out or blacklisted registration is impossible.
* New: Action Get WHOIS info and trigger IP locked out to create automation scenarios with the [jetFlow.io automation plugin](http://jetflow.io).
* New: Notification emails will contain Reason of a lockout.
* New: The activity DB table will be optimized after removing old records daily.
* Update: Column Username on the Activity tab now shows real value that submitted with WordPress login form.
* Update: Text domain is updated to 'wp-cerber'
* Fixed issue: If a user enter correct email address and wrong password to log in, IP address is locked immediately.

= 2.9 =
* New: Checking for a prohibited username (login). You can specify list of logins manually on the new settings page (Users).
* New: Rate limiting for notification letters. Set it on the main settings page.
* New: If new user registration disabled, automatic redirection from wp-register.php to the login page is blocked (404 error). Remote IP will be locked out.
* New: You can set user session expiration timeout.
* New: Define constant CERBER_IP_KEY if you want the plugin to use it as a key to get IP address from $_SERVER variable.
* Update: Improved WP-CLI compatibility.
* Update: All dates are displayed in a localized format with date_i18n function.
* Fixed bugs: incorrect admin URL in notification letters for multisite with multiple domains configuration, lack of error message on the login form if IP is blocked, CSRF vulnerability on the import settings page
* Removed calls of deprecated function get_currentuserinfo().

= 2.7.2 =
* Fixed bug for non-English WordPress configuration: the plugin is unable to block IP in some server environment. If you have configured language other than English you have to install this release.

= 2.7.1 =
* Fixed two small bugs related to 1) unable to remove IP subnet from the Access Lists and 2) getting IP address in case of reverse proxy doesn't work properly.

= 2.7 =

* New: Now you can view extra WHOIS information for IP addresses in the activity log including country, network info, abuse contact, etc.
* New: Added ability to disable WP REST API, see [Hardening WordPress](https://wpcerber.com/hardening-wordpress/)
* New: Added ability to add IP address to the Black List from the Activity tab. Nail it!
* New: Added Spanish translation, thanks to Ismael.
* New: Added ability to set numbers of displayed rows (lines) on the Activity and Lockout tabs. Click Screen Options on the top-right.
* Fixed minor security issue: Actions to remove IP on the Access Lists tab were not protected against CSRF attacks. Thanks to Gerard.
* Update: Small changes on the dashboard widget.
* Update: Action taken by the plugin (plugin makes a decision) now marked with dark vertical bar on the right side of the labels (Activity tab).

= 2.0.1.6 =
* New: Added Reason column on the Lockouts screen which will display cause of blocking particular IP.
* New: Added Hardening WP with options: disable XML-RPC completely, disable user enumeration, disable feeds (RSS, Atom, RSD).
* New: Added Custom email address for notifications.
* New: Added Dutch and Czech translations.
* New: Added Quick info about IP on Activity tab.
* Update: Removed option 'Allow whitelist in Citadel mode'. Now this whitelist is enabled by default all the time.
* Update: For notifications on the multisite installation the admin email address from the Network Settings will be used.
* Fixed Bug: Disable wp-login.php doesn't work for subfolder installation.
* Fixed Bug: Custom login URL doesn't work without trailing slash.
* Fixed Bug: Any request to wp-signup.php reveal hidden Custom login URL.

= 1.9 =
* Code refactoring and cleaning up.
* Unlocalized strings was localized.

= 1.8.1 =
* Fixed minor bug: no content (empty cells) in the custom colums added by other plugins on the Users screen in the Dashboard.

= 1.8 =
* New! added Hostname column for the Activity and Lockouts tabs.
* New! added ability to write failed login attempts to the specified file or to the syslog file. Use it to protect site with fail2ban.
* Added Ukrainian translation (Український переклад).

= 1.7 =
* Added ability to remove old records from the user activity log. Log will be cleaned up automatically. Check out new Keep records for field on the settings page.
* Added pagination for the Activity and Lockouts tabs.
* Added German (Deutsch) translation, thanks to mario.
* Added ability to reset settings to the recommended defaults at any time.

= 1.6 =
* New: beautiful widget for the dashboard to keep an eye on things. Get quick analytic with trends over 24 hours and ability to manually deactivate Citadel mode.
* French translation added, thanks to hardesfred.
* Hardening WordPress. Removed automatically redirection from /login/ to the login page, from /admin/ and /dashboard/ to the dashboard.
* Fixed issue with lost password link in the multisite mode.
* Now compatible with User Switching plugin.
* Added ability to manually deactivate Citadel mode, once it automatically switches on.

= 1.5 =
* New feature: importing and exporting settings and access lists from/to the file.
* Limited notifications in the dashboard.

= 1.4 =
* Added support Multisite mode for limit login attempts.
* Added Number of comments column on the Users screen in dashboard.
* Updated notification settings.
* Updated languages files.

= 1.3 =
* Fixed issue with hanging up during redirect to /wp-admin/ on some circumstance.
* Fixed minor issue with limit login attempts for non-admin users.
* Added Date of registration column on the Users screen in dashboard.
* Some UI improvements on access-list screen.
* Performance optimization & code refactoring.

= 1.2 =
* Added localization & internationalization files. You can use Loco Translate plugin to make your own translation.
* Added Russian translation.
* Added headers for failed attempts to use such headers with [fail2ban](http://www.fail2ban.org).

= 1.1 =
* Added ability to filter out Activity List by IP, username or particular event. You can see what happens and when it happened with particular IP or username. When IP reaches limit login attempts and when it was blocked.
* Added protection from adding to the Black IP Access List subnet belongs to current user's session IP.
* Added option to work with site/server behind reverse proxy.
* Update installation instruction.

= 1.0 =
* Initial version

== Other Notes ==

1. If you want to test out plugin's features, do this from another computer and remove that computer's network from the White Access List. Cerber is smart enough to recognize "the boss".
2. If you've set up the Custom login URL and you use some caching plugin like **W3 Total Cache** or **WP Super Cache**, you have to add a new Custom login URL to the list of pages not to cache.
3. [Read this if your website is under CloudFlare](https://wpcerber.com/cloudflare-and-wordpress-cerber/)

**Deutsche**
Schützt vor Ort gegen Brute-Force-Attacken. Umfassende Kontrolle der Benutzeraktivität. Beschränken Sie die Anzahl der Anmeldeversuche durch die Login-Formular, XML-RPC-Anfragen oder mit Auth-Cookies. Beschränken Sie den Zugriff mit Schwarz-Weiß-Zugriffsliste Zugriffsliste. Track Benutzer und Einbruch Aktivität.

**Français**
Protège site contre les attaques par force brute. Un contrôle complet de l'activité de l'utilisateur. Limiter le nombre de tentatives de connexion à travers les demandes formulaire de connexion, XML-RPC ou en utilisant auth cookies. Restreindre l'accès à la liste noire accès et blanc Liste d'accès. L'utilisateur de la piste et l'activité anti-intrusion.

**Український**
Захищає сайт від атак перебором. Обмежте кількість спроб входу через запити ввійти форми, XML-RPC або за допомогою авторизації в печиво. Обмежити доступ з чорний список доступу і список білий доступу. Користувач трек і охоронної діяльності.

**What does "Cerber" mean?**

Cerber is derived from the name Cerberus. In Greek and Roman mythology, Cerberus is a multi-headed dog with a serpent's tail, a mane of snakes, and a lion's claws. Nobody can bypass this angry dog. Now you can order WP Cerber to guard the entrance to your site too.


