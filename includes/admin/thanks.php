<?php
/**
 * WP Idea Stream Administration Thanks screens.
 *
 * About WP Idea Stream & credits screens
 *
 * @package WP Idea Stream
 * @subpackage admin/thanks
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * About screen
 *
 * @package WP Idea Stream
 * @subpackage admin/thanks
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_version() to get plugin's version
 * @uses   add_query_arg() to add query vars to an url
 * @uses   admin_url() to build a link inside the current blog's Administration
 * @uses   get_transient() to get the value of a transient
 * @uses   delete_transient() to delete a transient
 * @uses   wp_oembed_get() to get the vidéo démo of the plugin
 * @return string HTML output
 */
function wp_idea_stream_admin_about() {
	$display_version = wp_idea_stream_get_version();
	$settings_url = add_query_arg( 'page', 'ideastream', admin_url( 'options-general.php' ) );
	$has_upgraded = get_transient( '_ideastream_reactivated_upgrade' );
	$upgraded = __( 'activating', 'wp-idea-stream' );

	if ( ! empty( $has_upgraded ) ) {
		$upgraded = __( 'upgrading to', 'wp-idea-stream' );
		delete_transient( '_ideastream_reactivated_upgrade' );
	}

	$about_video = wp_oembed_get( 'http://vimeo.com/107403493', array( 'height' => 590, 'width' => 1050 ) );
	?>
	<div class="wrap about-wrap">
		<h1><?php printf( esc_html_x( 'WP Idea Stream %s', 'about screen title', 'wp-idea-stream' ), $display_version ); ?></h1>
		<div class="about-text"><?php printf( esc_html__( 'Thank you for %1$s the latest version of WP Idea Stream! I would say %2$s is a kind of "Rebirth" for this plugin.', 'wp-idea-stream' ), $upgraded, $display_version ); ?></div>
		<div class="wp-idea-stream-badge"></div>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab nav-tab-active" href="<?php echo esc_url(  admin_url( add_query_arg( array( 'page' => 'about-ideastream' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'About', 'wp-idea-stream' ); ?>
			</a>
			<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'credits-ideastream' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'wp-idea-stream' ); ?>
			</a>
		</h2>

		<div class="changelog">
			<h2 class="about-headline-callout"><?php echo esc_html_x( 'Share ideas, great ones will rise to the top!', 'IdeaStream Headline', 'wp-idea-stream' ); ?></h2>

			<div class="feature-section">
				<p>
					<?php esc_html_e( 'WP Idea Stream is a WordPress plugin to power idea management for your site. Your members will be able to easily create, share and rate ideas.', 'wp-idea-stream' ); ?>
					<?php esc_html_e( 'This release introduces some major changes: the plugin has been completely redesigned and rewritten!', 'wp-idea-stream' ); ?>
				</p>

				<?php if ( ! empty( $has_upgraded ) ) : ?>
					<h4><?php esc_html_e( 'Important: these features are not supported anymore.', 'wp-idea-stream' );?></h4>
					<ul>
						<li><?php esc_html_e( 'Set the list of ideas as the front page of the blog.', 'wp-idea-stream' );?></li>
						<li><?php esc_html_e( 'Sharing options (twitter or email).', 'wp-idea-stream' );?></li>
						<li><?php esc_html_e( 'Disabling the built-in rating system.', 'wp-idea-stream' );?></li>
					</ul>
					<p>
						<?php esc_html_e( 'About points 2 and 3, see the Advanced customization chapter below. Examples are available on the plugin&#39;s support forum.', 'wp-idea-stream' ); ?>
					</p>
				<?php endif; ?>

				<div class="about-video about-video-focus">
					<?php if ( empty( $about_video ) ) : ?>
						<a href="http://vimeo.com/107403493" target="_blank">
							<img src="<?php echo wp_idea_stream_get_includes_url();?>admin/images/video-fallback.png"/>
						</a>
					<?php else :
					echo $about_video;
					endif;?>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( 'Activate .. and .. it&#39;s ready!', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col one-col">
				<div class="col-1">
					<h4><?php esc_html_e( 'Whatever the structure of your permalinks, or your theme, it just works!', 'wp-idea-stream' ); ?></h4>
					<p><?php printf( esc_html__( 'In previous versions of the plugin, it was necessary to use &#34;pretty URLs&#34; : a different permalink structure than the default one. With %s, you no longer need to care about this setting, the plugin will automatically adapt to your preferences.', 'wp-idea-stream' ), $display_version ); ?></p>
					<p><?php esc_html_e( 'WP Idea Stream now uses a similar mechanism to the Theme Compatibility API of bbPress or BuddyPress. As a result, its integration into your theme is hugely improved.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'After the plugin&#39;s activation, you will only have to choose the navigation menu in which to insert a link to the page listing the ideas or use the Idea Stream built-in navigation widget.', 'wp-idea-stream' ); ?></p>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( 'A richer user profile.', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col two-col">
				<div class="col-1">
					<p><?php esc_html_e( 'The &#34;author&#39;s archive page&#34; has been replaced by an extended profile page displaying 3 tabs:', 'wp-idea-stream' ); ?></p>
					<h4><?php esc_html_e( 'Published', 'wp-idea-stream' );?></h4>
					<p><?php esc_html_e( 'This part shows the list of ideas the user published in your site.', 'wp-idea-stream' ); ?></p>
					<h4><?php esc_html_e( 'Commented', 'wp-idea-stream' );?></h4>
					<p><?php esc_html_e( 'All the comments the user made about ideas are displayed in this tab.', 'wp-idea-stream' ); ?></p>
					<h4><?php esc_html_e( 'Rated', 'wp-idea-stream' );?></h4>
					<p><?php esc_html_e( 'You will find there all the ideas the user rated. Under the idea&#39;s title a mention informs the number of stars he gave to the idea.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'The user viewing his own profile is able to edit his description (at the right of his avatar) to tell a bit more about him.', 'wp-idea-stream' ); ?></p>
				</div>
				<div class="col-2 last-feature">
					<img src="<?php echo wp_idea_stream_get_includes_url();?>admin/images/user-profile.png"/>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( 'It&#39;s easier to moderate comments related to ideas.', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col two-col">
				<div class="col-1">
					<img src="<?php echo wp_idea_stream_get_includes_url();?>admin/images/moderate-comments.png"/>
				</div>
				<div class="col-2 last-feature">
					<p><?php esc_html_e( 'Natively, the WordPress administration does not distinguish comments made on the various post types that are registered on your blog. So you end up managing comments about blog posts, pages or other post types in the same place.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'With WP Idea Stream, comments relating to ideas are separated from the other ones. You will find them in the IdeaStream administration, leaving no doubts comments are about ideas!', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'In &#34;IdeaStream&#34; main Administration menu and in its &#34;Comments&#34; submenu, an awaiting moderation bubble will inform about the numbers of comments you have to check.', 'wp-idea-stream' ); ?></p>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( 'Now you can moderate idea ratings', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col two-col">
				<div class="col-1">
					<p><?php esc_html_e( 'Select the idea that you want to moderate the ratings and scroll down to the &#34;Rates&#34; metabox.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'Based on their choice, user avatars are divided into the various rating levels.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'To delete a rating, just click on the icon trash lying to the right of the user&#39;s avatar.', 'wp-idea-stream' ); ?></p>
				</div>
				<div class="col-2 last-feature">
					<img src="<?php echo wp_idea_stream_get_includes_url();?>admin/images/moderate-ratings.png"/>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( '5 minutes interval to edit an idea', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col two-col">
				<div class="col-1">
					<img src="<?php echo wp_idea_stream_get_includes_url();?>admin/images/5minedit.png"/>
				</div>
				<div class="col-2 last-feature">
					<p><?php esc_html_e( 'Unlike administrators who can edit an idea at any time from the admin interface, users have 5 minutes to possibly edit the content of their idea once submitted.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'But this time interval can be shortened! If in the meantime the idea is rated or commented, then the changes will not be accepted.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'To prevent a collision between the changes made by the administrator and by the user, the idea is locked until the user finished editing his idea.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'Of course, the administrator can take control, in this case, the user will be notified that his changes has been interrupted.', 'wp-idea-stream' ); ?></p>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( 'Some other cool stuff', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col two-col">
				<div class="col-1">
					<h4><?php esc_html_e( 'Stick great ideas to the top!', 'wp-idea-stream' ); ?></h4>
					<p><?php esc_html_e( 'WP Idea Stream is taking inspiration from the &#34;sticky&#34; feature that is available for blog articles to offer Administrators a way to highlight the great ideas on the first page of the list of ideas.', 'wp-idea-stream' ); ?></p>
					<h4><?php esc_html_e( 'Find the most popular ideas.', 'wp-idea-stream' ); ?></h4>
					<p><?php esc_html_e( 'Besides the ideas search form, you will find a drop-down list to rank the ideas based on the number of comments or their average rating. You soon know the ones that are rising to the top of popularity!', 'wp-idea-stream' ); ?></p>
					<h4><?php esc_html_e( 'Plenty of widgets!', 'wp-idea-stream' ); ?></h4>
					<p><?php esc_html_e( 'Navigation, categories, popular ideas, top contributors, recent comments about ideas, you have the choice! The tag cloud has been forgotten? Of course not! Just use the WordPress one and  select the &#34;Idea Tags&#34; taxonomy.', 'wp-idea-stream' ); ?></p>
				</div>
				<div class="col-2 last-feature">
					<img src="<?php echo wp_idea_stream_get_includes_url();?>admin/images/cool-stuff.png"/>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( 'Advanced customization.', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col one-col">
				<div class="col-1">
					<p><?php esc_html_e( 'The settings of the plugin does not allow you to customize the plugin as much as you&#39;d like? WP Idea Stream offers around 80 actions and more than 230 filters to allow you to customize its behavior.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'To organize your customizations, you can use the functions file of your theme or create a specific file: call it &#34;wp-idea-stream-custom.php&#34; and place it into your plugins directory.', 'wp-idea-stream' ); ?></p>
					<p><?php esc_html_e( 'What about changing the look and feel of WP Idea Stream? Create a &#34;wp-idea-stream&#34; folder in your theme and drop in it your style sheet and/or your new templates making sure your filenames match the ones that are included in the plugin’s templates directory and they will automatically replace them.', 'wp-idea-stream' ); ?></p>
				</div>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h2 class="about-headline-callout"><?php esc_html_e( 'One more thing...', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col two-col">
				<div class="col-1">
				    <h4><?php esc_html_e( 'BuddyPress integration!', 'wp-idea-stream' ); ?></h4>
					<p><?php printf( esc_html__( 'Version %s of WP Idea Stream enters a new dimension taking advantage of the BuddyPress community features (version 2.1 required).', 'wp-idea-stream' ), $display_version ); ?></p>
					<p><?php esc_html_e( 'The plugin&#39;s user profile becomes a new navigation in the BuddyPress member page.', 'wp-idea-stream' ); ?></p>
					<h4><?php esc_html_e( 'Groups component is activated?', 'wp-idea-stream' ); ?></h4>
					<p><?php esc_html_e( 'Nice, it’s now possible to share ideas within these micro-communities ensuring their members that the group&#39;s visibility is transposed to the status of their ideas.', 'wp-idea-stream' ); ?></p>
					<h4><?php esc_html_e( 'Site Tracking and Activity components are activated?', 'wp-idea-stream' ); ?></h4>
					<p><?php esc_html_e( 'Great, each time a new idea or a new comment about an idea will be posted, an activity will be generated to inform the members of your community.', 'wp-idea-stream' ); ?></p>
				</div>
				<div class="col-2 last-feature">
					<img src="<?php echo wp_idea_stream_get_includes_url();?>admin/images/buddypress.png"/>
				</div>
			</div>
		</div>

		<div class="changelog">
			<div class="return-to-dashboard">
				<a href="<?php echo $settings_url;?>" title="<?php _e( 'Configure WP Idea Stream', 'wp-idea-stream' ); ?>"><?php _e( 'Go to the IdeaStream Settings page', 'wp-idea-stream' );?></a>
			</div>
		</div>

	</div>
	<?php
}

/**
 * Credits screen
 *
 * @package WP Idea Stream
 * @subpackage admin/thanks
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_version() to get plugin's version
 * @uses   add_query_arg() to add query vars to an url
 * @uses   admin_url() to build a link inside the current blog's Administration
 * @return string HTML output
 */
function wp_idea_stream_admin_credits() {
	$display_version = wp_idea_stream_get_version();
	$settings_url = add_query_arg( 'page', 'ideastream', admin_url( 'options-general.php' ) );
	?>
	<div class="wrap about-wrap">
		<h1><?php printf( esc_html_x( 'WP Idea Stream %s', 'credit screen title', 'wp-idea-stream' ), $display_version ); ?></h1>
		<div class="about-text"><?php printf( esc_html__( '%s version of WP Idea Stream was also successfully released thanks to them!', 'wp-idea-stream' ), $display_version ); ?></div>
		<div class="wp-idea-stream-badge"></div>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'about-ideastream' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'About', 'wp-idea-stream' ); ?>
			</a>
			<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'credits-ideastream' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Credits', 'wp-idea-stream' ); ?>
			</a>
		</h2>

		<div class="changelog">
			<h4 class="wp-people-group"><?php esc_html_e( 'WP Idea Stream&#39;s engine', 'wp-idea-stream' ); ?></h4>
			<a href="http://wordpress.org"><div class="doubleup-badge ideastream-credits"></div></a>
			<p><?php esc_html_e( 'Huge thanks to the WordPress community for building and maintaining this fabulous open source software, and also for sharing his knowledge on WordPress.org sites (trac, codex, make, developer) or during community events (WordCamp, Meet-up).', 'wp-idea-stream' ); ?></p>
			<p><?php esc_html_e( 'Over the past few years, I had the opportunity to meet extraordinary women and men, worldwide, online or in real life. Each of you brought me a lot.', 'wp-idea-stream' ); ?></p>
		</div>

		<div class="changelog">
			<h4 class="wp-people-group"><?php esc_html_e( 'WP Idea Stream&#39;s best friend!', 'wp-idea-stream' ); ?></h4>
			<a href="http://buddypress.org"><div class="buddy-badge ideastream-credits"></div></a>
			<p><?php esc_html_e( 'I have a special thought for the BuddyPress core team. Each and every one of you are fantastic. Every day, I learn more and more to your contact and I am infinitely grateful.', 'wp-idea-stream' ); ?></p>
			<p><?php printf( esc_html__( 'BuddyPress is a fascinating open source project that extends WordPress beautifully. This %s version of WP Idea Stream was an opportunity for me to try to motivate WordPress plugins to include a few extra lines of code in order to take benefit from the BuddyPress community features and APIs.', 'wp-idea-stream' ), $display_version ); ?></p>
		</div>

		<div class="changelog">
			<h4 class="wp-people-group"><?php esc_html_e( 'WP Idea Stream&#39;s source of inspiration!', 'wp-idea-stream' ); ?></h4>
			<a href="http://bbpress.org"><div class="beebee-badge ideastream-credits"></div></a>
			<p><?php esc_html_e( 'IMHO, I think bbPress is one of the best plugins (if not the best) when it comes to rely on custom post types to build a WordPress plugin.', 'wp-idea-stream' ); ?></p>
			<p><?php esc_html_e( 'Beyond the fact that the bbPress forums are gorgeous, its source code is a valuable demonstration that I invite you to browse.', 'wp-idea-stream' ); ?></p>
			<p><?php esc_html_e( 'Many functions of WP Idea Stream are based on those of bbPress. Many Thanks to all the members of its core team.', 'wp-idea-stream' ); ?></p>
		</div>


		<h4 class="wp-people-group"><?php esc_html_e( 'WP Idea Stream&#39;s external libraries and useful code', 'wp-idea-stream' ); ?></h4>
		<ul class="wp-people-group " id="wp-people-group-project-leaders">
			<li class="wp-person" id="wp-person-sniperwolf">
				<a href="https://github.com/sniperwolf"><img src="https://avatars1.githubusercontent.com/u/741938?v=2&s=60" class="gravatar" alt="Fabrizio Fallico" /></a>
				<a class="web" href="https://github.com/sniperwolf">Fabrizio Fallico</a>
				<span class="title"><a href="https://github.com/sniperwolf/taggingJS">taggingJS</a></span>
			</li>
			<li class="wp-person" id="wp-person-wbotelhos">
				<a href="https://github.com/wbotelhos"><img src="https://avatars2.githubusercontent.com/u/116234?v=2&s=60" class="gravatar" alt="Washington Botelho" /></a>
				<a class="web" href="https://github.com/wbotelhos">Washington Botelho</a>
				<span class="title"><a href="https://github.com/wbotelhos/raty">Raty</a></span>
			</li>
			<li class="wp-person" id="wp-person-garyjones">
				<a href="https://github.com/GaryJones"><img src="https://avatars3.githubusercontent.com/u/88371?v=2&s=60" class="gravatar" alt="Gary Jones" /></a>
				<a class="web" href="https://github.com/GaryJones">Gary Jones</a>
				<span class="title"><a href="https://github.com/GaryJones/Gamajo-Template-Loader">Template Loader class</a></span>
			</li>
		</ul>

		<div class="changelog">
			<div class="return-to-dashboard">
				<a href="<?php echo $settings_url;?>" title="<?php esc_html_e( 'Configure WP Idea Stream', 'wp-idea-stream' ); ?>"><?php esc_html_e( 'Go to the IdeaStream Settings page', 'wp-idea-stream' );?></a>
			</div>
		</div>

	</div>
	<?php
}
