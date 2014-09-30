=== WP Idea Stream ===
Contributors: imath
Donate link: http://imathi.eu/donations/
Tags: buddypress, idea, innovation, management, ideas, ideation, sharing, post-type, rating
Requires at least: 4.0
Tested up to: 4.0
Stable tag: 2.0.0
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

= I'm not using BuddyPress, will it work without it ? =
Of course! But try BuddyPress, it's awesome :)

= I'm not using WordPress 4.0, is this plugin compatible with an older version ? =
Since 2.0.0, plugin is requiring 4.0.

= I'm not using WordPress 3.9, is this plugin compatible with an older version ? =
Version 1.2 of the plugin requires WordPress 3.9, if you want to use this plugin with an earlier version of WordPress, you'll need to download a previous version of the plugin.
I advise you to browse the [different versions](http://wordpress.org/extend/plugins/wp-idea-stream/developers/ "Other version") available and choose version 1.0.3 if you run a WordPress from 3.1 to 3.4.2 and 1.1 if you run a WordPress from 3.5.

= I'm still using the twentyeleven or twentyten theme with WordPress 3.5, how can i make the different templates go along with it ? =
You can download from my dorpbox a [zip file](https://dl.dropbox.com/u/2322874/templates-2010-11.zip "my dropbox") containing the idea templates optimized for this 2 themes. Once you've downloaded them, simply copy and paste them
in your twentyeleven or twentyten (child) theme directory.

= When on front end, idea category or tag are displaying a 404 ? =

To fix this, you can go to your permalinks settings and simply click on the "save changes" button to update your permalinks settings

= If you have any other questions =

Please add a comment [here](http://imathi.eu/tag/wp-idea-stream/ "my blog") or use this plugin forum.

== Screenshots ==

1. User profile.
2. Moderating comments about ideas
3. Moderating ratings
4. Submit form in a BuddyPress group.
5. Ideas archive page

== Changelog ==

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
