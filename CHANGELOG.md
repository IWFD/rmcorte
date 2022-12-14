Infinity Squared changelog
==========================

2.0 (March 2015)
----------------
* New, more spaced out design.
  * SVG based Retina graphics, based on Font Awesome.
  * Custom social sharing buttons.
  * Nicer, larger font.
  * Mobile first design.
  * Improved error page. Now it has an actual footer, and the errors can be more easily displayed using the `display_error()` function.
* Decent antispam system. If the user is logged in, antispam protection is ommited. If not, reCAPTCHA keys can be supplied for Google's new tick-CAPTCHA to be used. If both of those are failed, basic fill-box protection is used.
* Shunned jQuery.
* Shunned the dependency on the soon-to-be deprecated Google Charts API, in favour of PHP QR Code.
* Using a different ZeroClipboard script and updating it's settings to more reliably find copy material.
* Dependency system for scripts, so scripts like ZeroClipboard are not loaded where they're not needed, such as on the index page. Dependencies are added to the `dependencies[]` array, and then if the dependency is in the array during loading, the script is loaded.
* Updated POT.
* Corrected all of the settings code in `index.php` and `results.php`. It should now more reliably detect whether settings are enabled or not.

1.6 (January 2014)
------------------
* reCAPTCHA support.
* Single new bookmarklet.
* Custom CSS styling which will not be overwritten on upgrade.

1.5 (August 2013)
-----------------
Big changes:
* Internationalisation! See Wiki for instructions on how to use Infinity Squared in your language and translate it for other people to use.
* A proper CSS mobile interface. When I tried to add internationalisation, it quickly became apparent that having two code bases was stupid.

Bug fixes:
* HTML5 doctype
* Upgraded to qTip2
* Including jQuery and qTip from CDN
* No longer loading G+ code if it's not enabled
* Parallel loading of scripts thanks to Chrome's audits
* Rearranged the CSS so that it's more readable

Many thanks to [Ozh](http://ozh.org) for help with internationalisation

1.4 (February 2013)
-------------------
* Bookmarklets updated to the code relevant to YOURLS 1.6
* Added CSS animation to the main menu
* Updated jQuery to 1.9.1 (included with YOURLS 1.6)
* Updated qTip?? and Formalize.me so they're compatible with the new version of jQuery
* Added a few more comments to the code so it's easier to modify it
* Improved the readability of the documentation by moving over to Markdown
* Usual assortment of bug fixed, cleaned up code etc.

1.3 (August 2012)
-----------------
* Added a mobile version of the theme which can be disabled in the config file
* Discreet gradient in the menu
* New Google+ sharer
* Bookmarklet code updated with code with YOURLS 1.5
* Usual assortment of bug fixes, cleaned up code etc.
