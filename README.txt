=== PayPal Easy Digital Shop ===
Contributors: mbjtechnolabs
Tags: download, e-commerce, ebook, ecommerce, file, mp3, music, paypal, pdf, shop, software, commerce, digital downloads, download, downloads, e-commerce, e-downloads, e-store, ecommerce, eshop, orders, paypal, paypal ipn, sell digital products, sell download, sell downloads, sell ebook, sell photos, sell products, sell videos, selling, wp ecommerce, button, donate, email, mailchimp, marketing, multi currency, newsletter, payment, payment history, payment list, payment wp, paypal, paypal button manager, PayPal Donate, PayPal payment, paypal payment accept, paypal payment button, paypal payment buttons, Paypal payment list, paypal payment widget, plugin, shortcode, sidebar, signup, subscribe, widget
Requires at least: 3.8
Tested up to: 4.1
Stable tag: 1.0.2
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

= Usage =
*   Edit or create a Page or Post. Find the paragraph (metabox) Easy Digital Shop at the bottom of the edit page (admin view). Upload a file and enter a price.
*    Type the shortcode [easydigitalshop] into the content of the post/page.

= Advanced Usage =
*   If you have uploaded a file in a post with the id 1. And you want the same file/button in a post with the id 2, you may type the shortcode [easydigitalshop id=1] in the content of post 2.
*    You may also use the template tag <?php easydigitalshop(); ?> in the loop, to retrieve the file/button of the current post.
*   You may also reference the file/button of another post by referencing it over the post id: <?php easydigitalshop(1); ?>, where 1 ist the id, where you uploaded the file.
*   <?php easydigitalshop(1); ?> does also work outside of the loop, anywhere else on the page.
*   easydigitalshop degrades gracefully: If there is no file or no price or you have not entered your paypal email address, there will be no button!

= Customization =
*   Copy the file button.php into the folder /wp-content/easy-digital-shop/. Now you can edit the copied file. It will be used instead of the original button.php file and you will not loose it after an update.
*   You will find the price of the file in the PHP variable $price and the currency in $currency.
*   button.php will be included in the complete paypal form. Therefore it contains only the submit button. You may use a HTML submit button (e.g. <input type="submit" value="Buy now!" />) and style it with CSS or you can use an image submit button (e.g. <input type="image" src="image.gif" alt="Buy now!" />). You may change the value or alt attribute text. It does not need to be 'Buy now!'.
*   If you decide to use the image button, you can use the original paypal buttons: https://developer.paypal.com/docs/classic/archive/buttons/ or any other ecommerce icons or your own image.
*   If you don't need $price or $currency delete them! Only the submit button is really needed. 


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
= 1.0.2 =
*       Resolved button compability 
= 1.0.1 =
*	Compatible with current version 
= 1.0.0 =

* initial commit

 == Upgrade Notice == 
*   Easy Digital Shop plugin for selling digital downloads via PayPal payment gateway. Easy Digital Shop is easy to used and easy to integration.