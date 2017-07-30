# Change Log

## 2.5.2

_Requires WordPress 4.7_

### Bug Fixes

- Adds a Change log.

### Features

- Adds the dutch translation.
- Use Git archive to package the plugin.

## 2.5.1

_Requires WordPress 4.7_

### Features

- Adds a Github Plugin URI header tag for easier upgrades using the Github Updater plugin
- Adds the missing GNU/GPL2 license to repository
- Adds an icon for the plugin.
- The plugin can now be installed and updated automatically thanks to the Entrepôt plugin.

## 2.5.0

_Requires WordPress 4.7_

### Bug Fixes

- The plugin is no more hosted on the WordPress.org directory to prevent sad behaviors from some users.

### Features

- The WP Idea Stream Editor is now including the editor styles of your active theme to give you a better idea of how ideas will be displayed once published.
- It will - also - display the embeds you added.
- Many users asked for idea uploads, this will be possible very soon thanks to MediaThèque, an alternative Media Library for your WordPress.

---

## 2.4.0

_Requires WordPress 4.7_

### Features

- The idea ratings built-in system is now using a custom Rest API endpoint to save your users ratings.
- Readonly Rest API endpoints are now available to get the great ideas your users shared.
- A new status for Ideas: Archive.
- List Ideas on your site's static front page.
- New WP Idea Stream's menu items for your navigation menus.
- Featured images are finally prettifying loops.
- A tabbed UI for the WP Idea Stream's Settings screen.
- Improved TwentySeventeen integration.
- Improved user feedbacks.
- Classes are now autoloaded!

---

## 2.3.4

_Requires WordPress 4.4_

### Bug fixes

- Fix an issue about comments in BuddyPress private groups.

## 2.3.3

_Requires WordPress 4.4_

### Features

- WP Idea Stream style has been optimized for TwentySeventeen.

### Bug fixes

- Fix an issue with comments count cache.
- Fix displayed comments count on Comments Administration screens views.

## 2.3.2

_Requires WordPress 4.4_

### Bug fixes

- Adapts to Upstream embed improvements introduced in WordPress 4.5
- Make sure to not interfere with the BuddyPress Emails repair tools
- Avoid split sentences and some inconsistencies in l10n/i18n

## 2.3.1

_Requires WordPress 4.4_

### Bug fixes

- Makes sure the idea header is output even if a plugin is using the content of the idea inside a meta tag (eg: JetPack)
- Adapt to changes introduced about Post Types comments tracking into the Activity Stream by BuddyPress 2.5
- Improve translation/wording/english grammar

## 2.3.0

_Requires WordPress 4.4_

### Bug fixes

- In users profiles, the issue with the title of commented private ideas is now fixed.
- In users profiles, user rates are now consistent meaning only ideas the displayed user rated will be displayed.
- Spammed users on multisite configs or configs where BuddyPress is activated, will not be displayed anymore. Unless you unspam them :)

### Features

- Users profiles embeds.
- Featured images for ideas.
- Various embed improvements.
- WP Idea Stream style has been optimized for the TwentySixteen theme.
- More links on archive pages are now using the link of the idea.
- The Ideas pagination is now completely independant of the Posts one.

---

## 2.2.0

_Requires WordPress 4.3_

### Bug fixes

- The way the plugin's specific template parts are loaded has been improved.

### Features

- BuddyDrive integration: users can now attach files to their ideas.
- The sign-up form is now also available for multisite configs when user registration is activated on the network.
- Default slugs are now translatable.
- WP Idea Stream template parts has been optimized for the Twentyfifteen theme.

---

## 2.1.2

_Requires WordPress 4.1_

### Bug fixes

- Fixes potential security issues by making sure add_query_arg() urls are escaped.

## 2.1.1

_Requires WordPress 4.1_

### Bug fixes

- Fixes potential security issues
- Fixes a problem with BP Default based themes (ideas in groups)

## 2.1.0

_Requires WordPress 4.1_

### Features

- Sign-up form
- Export ideas in a CSV file from the Ideas administration
- Improves the title tag
- Themes can now use a new template for single ideas : single-ideastream.php
- Now uses the BuddyPress Post type activities

---

## 2.0.0

_Requires WordPress 4.0_

### Features

- The plugin has been completely rewritten.
- Adds BuddyPress support (2.1 required if BuddyPress is activated)
- Uses WP Rewrite API so that you can leave default permalink settings or use pretty urls.
- Idea author's archive page is now a user profile with 3 tabs to see the ideas shared/commented/rated by the user.
- Improves the way plugin's templates are loaded to try to adapt to the most WordPress themes.
- Plugin's templates and css file can be overriden from the theme as soon as there are in a folder name 'wp-idea-stream'.
- Separates comments about ideas from other post types comments.
- Gives a 5 minutes extra time to the user to edit his idea once submitted.
- Adds new sort options to filter ideas by number of comments, average rate or latest
- Adds a new search feature
- Adds a sticky feature to public ideas.
- Admins can now moderate ratings
- All url slugs can be customized
- Adds an option to neutralize comments about ideas
- New widgets : top contributors, most popular ideas (comments/rates), most recent comments about ideas...

---

## 1.2.0

_Requires WordPress 3.9_

### Features

- It gives up custom tinyMCE plugin previously used to add links or image in favor of the WordPress built-in ones.
- It adds a link on the rating stars when not viewing the idea in its single template. When on an archive template, clicking on the stars will open the idea so that the user can rate it.

### Bug Fixes

- It fixes the slashes bug when defining custom captions for stars

---

## 1.1.0

_Requires WordPress 3.5_

### Features

- adds 2 tinyMCE plugins to improve image and link management when adding an idea.
- templates are now optimized for twentytwelve theme.

### Bug Fixes

- now uses wp_editor to fix some ugly javascript warnings.

---

## 1.0.3

_Requires WordPress 3.1_

### Bug Fixes

- fixes a trouble appeared in WP 3.4 on the wysiwyg editor
- fixes a redirect trouble once the idea is posted if plugin is activated in a child blog
- adds status header to avoid 404 in several templates

## 1.0.2

_Requires WordPress 3.1_

### Bug Fixes

- adds a filter on comment notification text when post author can't edit comment in order to avoid displaying the trash and spam link in the author's mail.

## 1.0.1

_Requires WordPress 3.1_

### Bug Fixes

- fixes the 'edit description link' bug on author idea template.
- adds titles on browser header by filtering wp_title and bp_page_title (BuddyPress).
- it's now possible to feature ideas from BuddyPress comment template.

## 1.0.0

_Requires WordPress 3.1_

### Features

- Registered users can post their ideas from the front-end of the website.
- A rating system is included
- Each registered users can find their posted ideas in a specific archive page.
- From this archive page they can also customize the description of their profile.
- An administration area can help you customize the settings of the plugin.
