=== In Stock Mailer for WooCommerce ===
Contributors: frankspress
Donate link: https://paypal.me/frankspress
Tags: in stock, woocommerce, email, products, woocommerce mailchimp
Requires at least: 4.9
Tested up to: 5.6.1
Stable tag: 2.1.1
Requires PHP: 5.6.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

In Stock Mailer for WC sends in stock alert emails to customers with a customizable alert button and content. Now integrates Mailchimp!

== Description ==
<h4>Introduction</h4>
<p>Thank you for trying <i>In Stock Mailer for WooCommerce!</i></p>
This WooCommerce extension allows your online store to have a customizable in stock alert button and email. If a product or product variation is out of stock this plugin will display an animated button with a "Notify when available" option.
Registered users can simply click on the button and they will be automatically notified by email when the product/s is back in stock. In addition, your <i>Mailchimp</i> account can be linked to this plugin and new visitors email can be added for future marketing campaigns.
This is a useful tool to collect email and consent from a visitor.
<p>For optimal customer experience, non-registered users/visitors will be prompted to enter their email address only once since it will be saved in a cookie; Once a product or more products become available, the application will check and group the requests by email address, and send one email per customer, including images and links to the product pages they were initially interested in.</p>
<p>In the admin pages you can see the pending alert requests and email sent to customers, sort by date, users and status or group requests by email address. You can also customize your back in stock email, which will always begin with "Hello {customer name},"; You can choose a custom header image that shows in the top of the email, a custom subject and even modify the body of the email.</p>
<h4>Notes</h4>
<p>It's important to understand that this plugin uses cookies. It's your responsibility to abide any laws and regulations within your country or jurisdiction ( eg. EU Cookie laws ).</p>

== Installation ==
<h4>To install</h4>
<p>
1. Upload the plugin files to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to In Stock Mailer then  screen to configure the plugin
4. Set up your button text and email header image.
5. Test your email functionality at the bottom of the General Settings page.
</p>
<h4>Note</h4>
<p>In order for this plugin to work you need a "transactional emails service". Basically your WordPress installation needs to be set-up in order to send individual emails.</p>

== Frequently Asked Questions ==

= Do I have to configure anything to use it? =
Yes and no. The plugin works out of the box but you should add an header image to your email.

= When are the in stock email alerts sent? =
Normally it runs at intervals of 8 hours. If your website has a low traffic rate consider adding a server based cron job.

= The plugin isn't sending any email, what do I do? =
If emails are not being sent the first step is to send an email test. You can do so from the General Settings page. This plugin requires you to have a transactional email service in place already!

= What is a transactional mail service? =
It's a service offered by a company that allows WordPress to send "individual emails". There are many out there such as SendGrid, mailgun etc.; Some of which have free plans too.

= If I link Mailchimp to this plugin will it send emails?
No. Linking your Mailchimp account simply collects emails adding them to your store list on Mailchimp for campaigns.

= How to configure Mailchimp?
It's super simple. Follow the link from Mailchimp config in General Settings. On mailchimp.com you will be asked to generate an API key if you dont have one yet.
Simply copy and paste it in Mailchimp Config and copy also the server prefix into the second field, which is the code following the https:// in the browser while on mailchimp.com. Pick your store from the list and wait the confiramtion message. At that point your application is set up.
You can also send a test email and the verify the email has been added to your Mailchimp store list, but wait at least 10 minutes before you do so.

= Will I get spam requests? =
The plugin offers some degree of protection against spam! Bots will have a hard time sending fake requests but some may get through. In the future I might implement captcha if needed/requested.

= I have an issue but I can't find a solution. =
Please use the plugin support forum to ask a question or make a suggestion.

== Screenshots ==

1. Notification button.
1. Button settings.
1. Sent alerts with Mailchimp.
1. Sent alerts grouped.
1. General settings menu.

== Changelog ==

= 2.1.1 =
* Removes Woo logo because Automatics got upset I'm promoting their brand and got my plugin shutdown, therefore I will NOT be creating anything for BooCommerce anymore!

= 2.1.0 =
* Fixes a database bug.
* This update will fix a known issue for those pending emails that are not being sent automatically or manually.

= 2.0.2 =
* Fixes a bug on email validation.

= 2.0.1 =
* Fixes multiple email submission on some configuration.

= 2.0.0 =
* Adds Support for Mailchimp and automatic collection of emails.
* Minor database and options upgrade.
* Plugin has now a dedicated menu on the side screen.
* Menu has been restyled and organized.
* Improves server compatibility.
* Minor security update.
* Fully tested on the newer 5.5.1 WP version and the 4.6.0 WC version.

= 1.4.8 =
* API now works with plain permalinks too.
* Minor security update.

= 1.4.7 =
* Adds default banner to emails.

= 1.4.6 =
* Removes email restriction for requests when not logged in.
* Adds FontAwesome local lib.
* Tested with WooCommerce 4.2.2.

= 1.4.5 =
* Email success submit triggers successful confirmation.
* Minor data validation fix.

= 1.4.4 =
* Implements backorder alert.
* Improves alert display logics.

= 1.4.3 =
* Minor style fix for email submit text size.

= 1.4.2 =
* Test Email now fully verifies manual plugin functionality.
* Minor style compatibility update.

= 1.4.1 =
* Adds a send now button for pending requests.
* Sets product variation option off by default for theme compatibility.

= 1.4.0 =
* Implements additional settings for email submit field.
* Improves style compatibility with multiple themes.

= 1.3.0 =
* Improved CSS compatibility and style.
* Adds functionalities: FontAwesome, Button size and email submit button custom background.
* Fixes basic translation. (Please review your .pot file if you are using this plugin in a language other than English).
* Notes: FontAwesome will be enabled by default with this update.

= 1.2.5 =
* Fixes an issue with email delivery for some WP installations.

= 1.2.4 =
* Adds initial support for Internationalization.
* Fixes minor HTML layout issue.

= 1.2.3 =
* Submenu page in WooCommerce is always positioned under Orders.

= 1.2.2 =
* Menu is now shown in its own subpage in WooCommerce main menu.

= 1.2.1 =
* Timezone fix.

= 1.2.0 =
* Implements the menu in a custom tab in WooCommerce setting.
* Pending requests are now grouped by email by default and show request date.

= 1.1.2 =
* DB Character set fix.

= 1.1.1 =
* Minor styling improvement. Security update.

= 1.1.0 =
* Cancel button is now optional.
* Product variation alert is now optional.
* Minor layout fixes.

= 1.0.0 =
* Initial release.
