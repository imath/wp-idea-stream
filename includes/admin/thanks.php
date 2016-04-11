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
	$thanks_for = esc_html__( 'Thank you for activating the latest version of WP Idea Stream! %s brings some really cool new features!', 'wp-idea-stream' );

	if ( ! empty( $has_upgraded ) ) {
		$thanks_for = esc_html__( 'Thank you for upgrading to the latest version of WP Idea Stream! %s brings some really cool new features!', 'wp-idea-stream' );
		delete_transient( '_ideastream_reactivated_upgrade' );
	}

	if ( wp_idea_stream_is_embed_profile() ) {
		$admin_profile = wp_oembed_get( wp_idea_stream_users_get_user_profile_url( get_current_user_id(), '', true ) );
	}
	?>
	<div class="wrap about-wrap">
		<h1><?php printf( esc_html_x( 'WP Idea Stream %s', 'about screen title', 'wp-idea-stream' ), $display_version ); ?></h1>
		<div class="about-text"><?php printf( $thanks_for, $display_version ); ?></div>
		<div class="wp-idea-stream-badge"></div>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'about-ideastream' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'About', 'wp-idea-stream' ); ?>
			</a>
			<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'credits-ideastream' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'wp-idea-stream' ); ?>
			</a>
		</h2>

		<div class="headline-feature">
			<h2 style="text-align:center"><?php echo esc_html_x( 'Share ideas, great ones will rise to the top!', 'WP Idea Stream Headline', 'wp-idea-stream' ); ?></h2>

			<div class="feature-section">
				<p>
					<?php esc_html_e( 'WP Idea Stream is a WordPress plugin to power idea management for your site. Your members will be able to easily create, share and rate ideas.', 'wp-idea-stream' ); ?>
				</p>

				<?php if ( ! empty( $has_upgraded ) ) : ?>
					<h4><?php esc_html_e( 'Important: these features are not supported anymore.', 'wp-idea-stream' );?></h4>
					<ul>
						<li><?php esc_html_e( 'Set the list of ideas as the front page of the blog.', 'wp-idea-stream' );?></li>
						<li><?php esc_html_e( 'Sharing options (twitter or email).', 'wp-idea-stream' );?></li>
						<li><?php esc_html_e( 'Disabling the built-in rating system.', 'wp-idea-stream' );?></li>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<hr />

		<div class="headline-feature">
			<h2 style="text-align:center"><?php esc_html_e( 'Embed your users profiles', 'wp-idea-stream' ); ?></h2>

			<?php if ( ! empty( $admin_profile ) ) :?>
				<style>blockquote.wp-embedded-content{display:none;}iframe.wp-embedded-content{display:block!important;clip:auto!important;position:relative!important;margin:0 auto;max-height:228px!important;}</style>
				<div class="embed-container" id="#embed-admin-profile">
					<?php echo $admin_profile ; ?>
				</div>
			<?php else : ?>
				<div class="feature-image" style="text-align:center">
					<img src="https://cldup.com/4t2V1bvOMR.png" alt="<?php esc_attr_e( 'Embed profile', 'wp-idea-stream' ); ?>">
				</div>
			<?php endif ; ?>

			<div class="feature-section" style="margin-top:1em">
				<h3 style="text-align:center"><?php esc_html_e( 'Great ideas are found by awesome people!', 'wp-idea-stream' ); ?></h3>
				<p><?php esc_html_e( 'Use the powerful WordPress Embeds provider feature introduced in WordPress 4.4 to let your users show off their lovely profile and statistics about their contributions.', 'wp-idea-stream' ); ?></p>
				<p><?php esc_html_e( 'WP Idea Stream embed profiles are even greater when BuddyPress 2.4 is activated thanks to its new Cover Images feature, check this out!', 'wp-idea-stream' ); ?></p>
			</div>

			<div class="feature-image" style="padding-bottom:1.5em;width:600px;margin:0 auto">
				<img src="https://cldup.com/liERrg_WJJ.png" alt="<?php esc_attr_e( 'Embed profile when BuddyPress is active', 'wp-idea-stream' ); ?>">
			</div>

			<div class="clear"></div>
		</div>

		<hr />

		<div class="feature-section two-col">
			<div class="col">
				<div class="media-container">
					<img src="https://cldup.com/QOitsGKqou.png" alt=""/>
				</div>
			</div>
			<div class="col">
				<h3><?php esc_html_e( 'Featured images for ideas.', 'wp-idea-stream' ); ?></h3>
				<p><?php esc_html_e( 'When a user adds images to an idea, those images will be presented as a list, and can then be selected as the featured image for the idea.', 'wp-idea-stream' ); ?></p>
			</div>
		</div>

		<hr />

		<div class="feature-section two-col">
			<div class="col">
				<div class="media-container">
					<img src="https://cldup.com/GglZMhKiLG.png" alt=""/>
				</div>
			</div>
			<div class="col">
				<h3><?php esc_html_e( 'Embeds, embeds, embeds!', 'wp-idea-stream' ); ?></h3>
				<p>
					<?php esc_html_e( 'You can now share ideas within your WordPress posts, or anyone can embed them anywhere on the internet compatible with embed codes: just like regular posts. Ideas will include one more piece of information: the average rating they have received!', 'wp-idea-stream' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'The way the plugin deal with embeds has been greatly improved and every embed code should be displayed just fine in every single view of ideas (including the one in BuddyPress Groups).', 'wp-idea-stream' ); ?>
				</p>
			</div>
		</div>

		<div class="changelog feature-list">
			<h2 class="about-headline-callout"><?php esc_html_e( 'The finer points..', 'wp-idea-stream' ); ?></h2>
			<div class="feature-section col one-col">
				<div class="col-1">
					<h4><?php esc_html_e( 'TwentySixteen', 'wp-idea-stream' ); ?></h4>
					<p>
						<?php esc_html_e( 'WP Idea Stream will look really nice when used with the new WordPress default theme. Its style has been optimized for it.', 'wp-idea-stream' ); ?>
					</p>
					<h4><?php esc_html_e( 'Bug fixes', 'wp-idea-stream' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'In users profiles, the issue with the title of commented private ideas is now fixed.', 'wp-idea-stream' ); ?></li>
						<li><?php esc_html_e( 'In users profiles, user ratings are now consistent, meaning only ideas the displayed user ranked will be displayed.', 'wp-idea-stream' ); ?></li>
						<li><?php esc_html_e( 'Spammed users on multisite configs or configs where BuddyPress is activated, will not be displayed anymore. Unless you unspam them :)', 'wp-idea-stream' ); ?></li>
						<li><?php esc_html_e( 'More links on archive pages are now using the link of the idea.', 'wp-idea-stream' ); ?></li>
						<li><?php esc_html_e( 'The Ideas pagination is now completely independent of the Posts one.', 'wp-idea-stream' ); ?></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="changelog">
			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( $settings_url );?>" title="<?php _e( 'Configure WP Idea Stream', 'wp-idea-stream' ); ?>"><?php _e( 'Go to the WP Idea Stream Settings page', 'wp-idea-stream' );?></a>
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

		<h3 class="wp-people-group"><?php _e( 'The team!', 'wp-idea-stream' ); ?></h3>
		<ul class="wp-people-group" id="wp-people-group-core-team">
			<li class="wp-person" id="wp-person-imath">
				<a href="http://profiles.wordpress.org/imath"><img src="http://0.gravatar.com/avatar/8b208ca408dad63888253ee1800d6a03?s=60" class="gravatar" alt="Mathieu Viet" /></a>
				<a class="web" href="http://profiles.wordpress.org/imath">imath</a>
				<span class="title"><?php _e( 'Creator', 'wp-idea-stream' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-aglekis">
				<a href="http://profiles.wordpress.org/aglekis"><img src="http://0.gravatar.com/avatar/9aed4c3373374032e4ecdde02894d5fb?s=60" class="gravatar" alt="Grégoire Noyelle" /></a>
				<a class="web" href="http://profiles.wordpress.org/aglekis">Grégoire Noyelle</a>
				<span class="title"><?php _e( 'Developer', 'wp-idea-stream' ); ?></span>
			</li>
		</ul>

		<h3 class="wp-people-group"><?php _e( 'Rock Stars', 'wp-idea-stream' ); ?></h3>
		<ul class="wp-people-group" id="wp-people-group-rock-stars">
			<li class="wp-person" id="wp-person-jennybeaumont">
				<a href="http://profiles.wordpress.org/jennybeaumont"><img src="http://0.gravatar.com/avatar/c5b883c76357aa309642c255edd51ee1?s=60" class="gravatar" alt="Jenny Beaumont" /></a>
				<a class="web" href="http://profiles.wordpress.org/jennybeaumont">Jenny Beaumont</a>
			</li>
		</ul>

		<h3 class="wp-people-group"><?php printf( esc_html__( 'Contributors to %s', 'wp-idea-stream' ), $display_version ); ?></h3>
		<p class="wp-credits-list">
			<a href="https://profiles.wordpress.org/imath">imath</a>,
			<a href="https://github.com/naokomc">Naoko Takano</a>.
		</p>

		<h3 class="wp-people-group"><?php esc_html_e( 'Special thanks.', 'wp-idea-stream' ); ?></h3>
		<div class="ideastream-credits">
			<a href="https://paris.wordcamp.org/2016/"><img src="https://cldup.com/UoFilD4UGh.png" class="gravatar" alt="WordCamp Paris 2016" /></a>
		</div>
		<p><?php printf( esc_html__( 'WP Idea Stream was the choice of the WordCamp Paris 2016 organization team to manage their &quot;Call for Speakers&quot;. Some requested features were very specific to their need and were all added as custom hooks in the %s file.', 'wp-idea-stream' ), '<a href="https://github.com/imath/wc-talk">wp-idea-stream-custom.php</a>' ); ?></p>
		<p><?php esc_html_e( 'The plugin was completely transformed to let the speakers submit their talks privately. The managing team was able to discuss together using private comments and evaluate each talk using the built-in rating system.', 'wp-idea-stream' ); ?></p>
		<p><?php esc_html_e( 'Many thanks to WordCamp Paris organizers and speakers for this great experience and for their contributions to the user rates bug report.', 'wp-idea-stream' ); ?></p>

		<h3 class="wp-people-group"><?php esc_html_e( 'WP Idea Stream&#39;s external libraries and useful code', 'wp-idea-stream' ); ?></h3>
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
				<a href="<?php echo esc_url( $settings_url );?>" title="<?php esc_html_e( 'Configure WP Idea Stream', 'wp-idea-stream' ); ?>"><?php esc_html_e( 'Go to the WP Idea Stream Settings page', 'wp-idea-stream' );?></a>
			</div>
		</div>

	</div>
	<?php
}
