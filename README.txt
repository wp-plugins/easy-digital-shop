=== PayPal Easy Digital Shop ===
Contributors: jigneshkailam
Tags: download, e-commerce, ebook, ecommerce, file, mp3, music, paypal, pdf, shop, software, commerce, digital downloads, download, downloads, e-commerce, e-downloads, e-store, ecommerce, eshop, orders, paypal, paypal ipn, sell digital products, sell download, sell downloads, sell ebook, sell photos, sell products, sell videos, selling, wp ecommerce
Requires at least: 3.8
Tested up to: 4.1
Stable tag: 1.0.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy Digital Shop plugin for selling digital downloads via PayPal payment gateway. Easy Digital Shop is easy to used and easy to integration.

== Description ==

= Introduction =
Easy Digital Shop enables you to sell files as downloads. Easy Digital Shop concentrates on what is really needed for selling files as downloads. For each post or page you may upload a file and set a price. Easy Digital Shop creates a fully customizable PayPal checkout button for you. The files are stored in a secure access protected folder on your server. After the payment is verified the buyer receives a customizable email with a personal download link.

*   Who could use Easy Digital Shop?
*   everybody who wants to sell downloadable products
== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Easy Digital Shop" and click Search Plugins. Once you've found our plugin you can view details about it such as the the rating and description. Most importantly, of course, you can install it by simply clicking Install Now.

= Manual Installation =

1. Unzip the files and upload the folder into your plugins folder (/wp-content/plugins/) overwriting previous versions if they exist
2. Activate the plugin in your WordPress admin area.

=   Usage   =

*   Edit or create a Page or Post. Find the paragraph (Metabox) easydigitalshop at the bottom of the edit page (admin view). Upload a file and enter a price. 
*   Type the shortcut [easydigitalshop] into the content of the post/page. 


== Screenshots ==

1. PayPal and confirmation email setting.
2. Add Product.
3. Display product.
4. Order Listing.

== Frequently Asked Questions ==

=   Can I create a page with multiple products? =
*   Yes, see Installation tab.

=   Can the button be customized by CSS? =
*   Yes, see Installation tab.

=   When I try to buy an item, plugin connect me directly to PayPal. The plugin never asks me anything (email, name). Is this correct?  =
*   Yes, it is not needed. PayPal has all the payers' data and will handle them to you after the checkout process. This is easy.

=   Can I sell more files than one in one checkout process? =
*   The plugin does not have a cart functionality. It is for cases, where you only want to sell one file at once. If you want to sell more files at once, you need to chose another plugin.

=   How often can the buyer download the file? How long is the download link valid? =
*   Easy Digital Shop allows 3 download attempts in the first 30 days after the purchase. Then the download link is not valid anymore.

=   I don't get an email with the download link and the payment does not appear on the sales page.  =
*   We have a serious problem. Maybe your WordPress installation is not reachable from outside because you are in a testing environment? Around the "Buy Now!"-button you'll find a hidden field named notify-url. The url (ends with... ipn.php) in the value attribute has to be available for PayPal. If you open it in your browser, you should see an empty white page without any error message.

== Changelog ==
= 1.0.1 =
*	Compatible with current version 
= 1.0.0 =

* initial commit

 == Upgrade Notice == 
*   Easy Digital Shop plugin for selling digital downloads via PayPal payment gateway. Easy Digital Shop is easy to used and easy to integration.