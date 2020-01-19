=== bbPress - Anonymous Subscriptions ===
Author URI: http://www.thecrowned.org
Plugin URI: http://www.thecrowned.org/bbpress-anonymous-subscriptions
Contributors: Ste_95
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8Y79DM6K78NJG
Tags: bbPress, subscriptions, emails, notifications
Requires at least: 3.2
Tested up to: 5.3
Stable Tag: 1.3.7

A simple plugin to allow anonymous bbPress users to subscribe to topics and get email notifications when a new reply is posted.

== Description ==

This add-on plugin for bbPress will allow anonymous users to subscribe to topics and get email notifications when a new reply is posted. The notification email includes an unsubscribe link.

bbPress notifications will keep to go out to registered users, this plugin only extends the thing to anonymous posters as well!

See the project on [GitHub](https://github.com/TheCrowned/bbPress---Anonymous-Subscriptions).

Available in English, Italian, German, Norwegian, Ebrew, Serbian.

== Installation ==
1. Activate the plugin.
2. A new "Notify me of follow-up replies via email" checkbox will be available in the reply form for anonymous users.

== Changelog ==
= 1.3.7 =
* Fix: notifications not going out anymore with bbPress >= 2.6 if there were no (regular) subscribed users.
* Tweak: revised German localization (Toengel).

= 1.3.6.2 = 
* New: added Serbian and Ebrew localizations (Eran).

= 1.3.6.1 =
* New: added German (maxx203) localization and update pot file.

= 1.3.6 =
* New: added Norwegian (Torbjørn Kristensen) and Italian (Stefano Ottolenghi) localizations.

= 1.3.5 = 
* Tweak: updated email text, warning users NOT to reply to automated emails.

= 1.3.4 =
* Fixed: using same message for all subscribed users, resulting in invalid unsubscribe link.

= 1.3.3 =
* New: added pot file.

= 1.3.2 =
* Tweak: changed localization slug.

= 1.3.1 =
* Fixed: anonymous notifications breaking subscribed users ones.

= 1.3 =
* Fixed: notifications to anonymous users wouldn't be sent if no registered users were subscribed to the topic.
* Fixed: notifications to user who created the new topic not working.

= 1.2 =
* Tweak: subscribe field checked by default.

= 1.1 =
* Fixed: anonymous subscriptions broke regular BBP notifications to registered users.
* Fixed: unsubscribe feature would not work when there was only one subscriber.

= 1.0 =
* First release!
