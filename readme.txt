=== WP Idea Stream ===
Contributors: imath, aglekis
Donate link: https://imathi.eu/donations/
Tags: idea, innovation, management, ideas, ideation, sharing
Requires at least: 4.7
Tested up to: 4.7
Stable tag: 2.4.0
License: GNU/GPL 2

Share ideas, great ones will rise to the top!

== Description ==

WP Idea Stream is a WordPress plugin to power idea management for your site. Your members will be able to easily create, share and rate ideas.

Here's a 3 mins demo of it (sorry for my english) :
http://vimeo.com/107403493

WP Idea Stream is available in French and English.

== Installation ==

You can download and install WP Idea Stream using the built in WordPress plugin installer. If you download WP Idea Stream manually, make sure it is uploaded to "/wp-content/plugins/wp-idea-stream/".

Activate WP Idea Stream in the "Plugins" admin panel using the "Activate" link.

== Frequently Asked Questions ==

= I'm not using WordPress 4.7, is this plugin compatible with an older version ? =
Since 2.4.0, plugin is requiring WordPress 4.7. You can use allways check the [different versions](http://wordpress.org/plugins/wp-idea-stream/developers/ "Other version") to find the one that best matches your config.

= If you have any other questions =

Please add a comment [here](https://imathi.eu/tag/wp-idea-stream/ "my blog") or use this plugin forum.

== Screenshots ==

1. User profile.
2. Moderating comments about ideas
3. Moderating ratings
4. Ideas archive page

== Changelog ==

= 2.4.0 =
* The idea ratings built-in system is now using a custom Rest API endpoint to save your users ratings.
* Readonly Rest API endpoints are now available to get the great ideas your users shared.
* A new status for Ideas: Archive.
* List Ideas on your site's static front page.
* New WP Idea Stream's menu items for your navigation menus.
* A tabbed UI for the WP Idea Stream's Settings screen.
* Improved TwentySeventeen integration.
* Improved user feedbacks.
* Classes are now autoloaded!

= 2.3.4 =
* Fix an issue about comments in BuddyPress private groups.

= 2.3.3 =
* WP Idea Stream style has been optimized for TwentySeventeen.
* Fix an issue with comments count cache.
* Fix displayed comments count on Comments Administration screens views.

= 2.3.2 =
* Adapts to Upstream embed improvements introduced in WordPress 4.5
* Make sure to not interfere with the BuddyPress Emails repair tools
* Avoid split sentences and some inconsistencies in l10n/i18n

= 2.3.1 =
* Makes sure the idea header is output even if a plugin is using the content of the idea inside a meta tag (eg: JetPack)
* Adapt to changes introduced about Post Types comments tracking into the Activity Stream by BuddyPress 2.5
* Improve translation/wording/english grammar

= 2.3.0 =
* Users profiles embeds.
* Featured images for ideas.
* Various embed improvements.
* In users profiles, the issue with the title of commented private ideas is now fixed.
* In users profiles, user rates are now consistent meaning only ideas the displayed user rated will be displayed.
* WP Idea Stream style has been optimized for the TwentySixteen theme.
* Spammed users on multisite configs or configs where BuddyPress is activated, will not be displayed anymore. Unless you unspam them :)
* More links on archive pages are now using the link of the idea.
* The Ideas pagination is now completely independant of the Posts one.

= 2.2.0 =
* BuddyDrive integration: users can now attach files to their ideas.
* The sign-up form is now also available for multisite configs when user registration is activated on the network.
* The way the plugin's specific template parts are loaded has been improved.
* Default slugs are now translatable.
* WP Idea Stream template parts has been optimized for the Twentyfifteen theme.

= 2.1.2 =
* Fixes potential security issues by making sure add_query_arg() urls are escaped.

= 2.1.1 =
* Fixes potential security issues
* Fixes a problem with BP Default based themes (ideas in groups)

= 2.1.0 =
* Sign-up form
* Export ideas in a CSV file from the Ideas administration
* Improves the title tag
* Themes can now use a new template for single ideas : single-ideastream.php
* Now uses the BuddyPress Post type activities

= 2.0.0 =

* The plugin has been completely rewritten
* requires WordPress 4.0
* adds BuddyPress support (2.1 required if BuddyPress is activated)
* uses WP Rewrite API so that you can leave default permalink settings or use pretty urls
* Idea author's archive page is now a user profile with 3 tabs to see the ideas shared/commented/rated by the user
* Improves the way plugin's templates are loaded to try to adapt to the most WordPress themes
* Plugin's templates and css file can be overriden from the theme as soon as there are in a folder name 'wp-idea-stream'
* Separates comments about ideas from other post types comments
* Gives a 5 minutes extra time to the user to edit his idea once submitted
* Adds new sort options to filter ideas by number of comments, average rate or latest
* Adds a new search feature
* Adds a sticky feature to public ideas.
* Admins can now moderate ratings
* All url slugs can be customized
* Adds an option to neutralize comments about ideas
* New widgets : top contributors, most popular ideas (comments/rates), most recent comments about ideas...
* and some other nice enhancements to discover by yourself ;)

= 1.2 =

* requires WordPress 3.9
* This version fixes some bugs and notice errors
* It gives up custom tinyMCE plugin previously used to add links or image in favor of the WordPress built-in ones.
* It fixes the slashes bug when defining custom captions for stars.
* It adds a link on the rating stars when not viewing the idea in its single template. When on an archive template, clicking on the stars will open the idea so that the user can rate it.

= 1.1 =

* requires WordPress 3.5
* now uses wp_editor to fix some ugly javascript warnings
* adds 2 tinyMCE plugins to improve image and link management when adding an idea.
* templates are now optimized for twentytwelve theme.

= 1.0.3 =

* fixes a trouble appeared in WP 3.4 on the wysiwyg editor
* fixes a redirect trouble once the idea is posted if plugin is activated in a child blog
* adds status header to avoid 404 in several templates

= 1.0.2 =

* adds a filter on comment notification text when post author can't edit comment in order to avoid displaying the trash and spam link in the author's mail

= 1.0.1 =

* fixes the 'edit description link' bug on author idea template
* adds titles on browser header by filtering wp_title and bp_page_title (BuddyPress)
* it's now possible to feature ideas from BuddyPress comment template.

= 1.0 =

* Plugin birth..

== Upgrade Notice ==

= 2.4.0 =
Please be sure to use at least WordPress 4.7. If you are using BuddyPress, required version is 2.5. Back up your database and files.

= 2.3.4 =
Please be sure to use at least WordPress 4.4. If you are using BuddyPress, required version is 2.5. Back up your database and files.

= 2.3.3 =
Please be sure to use at least WordPress 4.4. If you are using BuddyPress, required version is 2.5. Back up your database and files.

= 2.3.2 =
Please be sure to use at least WordPress 4.4. If you are using BuddyPress, required version is 2.5. Back up your database and files.

= 2.3.1 =
Please be sure to use at least WordPress 4.4. If you are using BuddyPress, required version is 2.5. Back up your database and files.

= 2.3.0 =

Please be sure to use at least WordPress 4.4. If you are using BuddyPress, required version is 2.4. Back up your database and files.

= 2.2.0 =

Please be sure to use at least WordPress 4.3. Now that default slugs are translatable, make sure to save the IdeaStream > Settings before upgrading the plugin if you never did. If you are using BuddyPress, required version is 2.3. Back up your database and files.

= 2.1.2 =

Please be sure to use at least WordPress 4.1 before upgrading/downloading this plugin.
If you are using BuddyPress, make sure to upgrade to version 2.2 before upgrading to WP Idea Stream 2.1.2.
Back up your database and files (in case you want to roll back to previous version).

= 2.1.1 =

Please be sure to use at least WordPress 4.1 before upgrading/downloading this plugin.
If you are using BuddyPress, make sure to upgrade to version 2.2 before upgrading to WP Idea Stream 2.1.1.
Back up your database and files (in case you want to roll back to previous version).

= 2.1.0 =
Please be sure to use at least WordPress 4.1 before upgrading/downloading this plugin.
If you are using BuddyPress, make sure to upgrade to version 2.2 before upgrading to WP Idea Stream 2.1.0.
Back up your database and files (in case you want to roll back to previous version).

= 2.0.0 =
Please be sure to use at least WordPress 4.0 before upgrading/downloading this plugin.
Back up your database and files (in case you want to roll back to previous version).

= 1.2 =
Please be sure to use at least WordPress 3.9 before upgrading/downloading this plugin.

= 1.1 =
Please be sure to use at least WordPress 3.5 before upgrading/downloading this plugin.

= 1.0.3 =
no particular notice for this upgrade.

= 1.0.2 =
no particular notice for this upgrade.

= 1.0.1 =
no particular notice for this upgrade.

= 1.0 =
no upgrades, just a beta version.
