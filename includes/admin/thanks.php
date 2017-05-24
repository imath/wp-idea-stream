<?php
/**
 * WP Idea Stream Administration Thanks screens.
 *
 * About WP Idea Stream & credits screens
 *
 * @package WP Idea Stream\admin
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * About screen
 *
 * @since 2.0.0
 *
 * @return string HTML output
 */
function wp_idea_stream_admin_about() {
	$display_version = wp_idea_stream_get_version();
	$settings_url = add_query_arg( 'page', 'ideastream', admin_url( 'options-general.php' ) );
	$has_upgraded = false;

	if ( ! empty( $_GET['is_upgrade'] ) ) {
		$has_upgraded = true;
	}
	$thanks_for = esc_html__( 'Thank you for activating the latest version of WP Idea Stream! %s brings some really cool new features!', 'wp-idea-stream' );

	if ( ! empty( $has_upgraded ) ) {
		$thanks_for = esc_html__( 'Thank you for upgrading to the latest version of WP Idea Stream! %s brings some really cool new features!', 'wp-idea-stream' );
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

				<?php if ( function_exists( 'buddypress' ) ) : ?>
					<h4 style="text-align:center"><?php esc_html_e( 'What about BuddyPress Integration?', 'wp-idea-stream' );?></h4>
					<p>
						<?php printf(
							esc_html__( 'WP Idea Stream is no longer including this integration into its codebase. Don\'t panic, you can always enjoy BuddyPress with WP Idea Stream by downloading and activating this addon: %s.', 'wp-idea-stream' ),
							'<a href="https://wordpress.org/plugins/bp-idea-stream/">BP Idea Stream</a>'
						); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<hr />

		<div class="headline-feature">
			<h2 style="text-align:center"><?php esc_html_e( 'What you see is closer to what you\'ll get!', 'wp-idea-stream' ); ?></h2>

			<div class="feature-section" style="margin-top:1em">
				<p><?php esc_html_e( 'The WP Idea Stream Editor is now including the editor styles of your active theme to give you a better idea of how ideas will be displayed once published.', 'wp-idea-stream' ); ?></p>
				<p><?php esc_html_e( 'It will - also - display the embeds you added.', 'wp-idea-stream' ); ?></p>

				<picture>
					<img src="https://cldup.com/0-mROXgsis.png" alt="" style="display:block; margin: 0 auto;"/>
				</picture>

			</div>

			<div class="clear"></div>
		</div>

		<hr />

		<div class="headline-feature">
			<h2 style="text-align:center"><?php esc_html_e( 'Ready for MediaThèque!', 'wp-idea-stream' ); ?></h2>

			<div class="feature-section" style="margin-top:1em">
				<p><?php esc_html_e( 'Many users asked for idea uploads, this will be possible very soon thanks to MediaThèque, an alternative Media Library for your WordPress.', 'wp-idea-stream' ); ?></p>
				<p><?php printf( esc_html__( 'Read more about %s.', 'wp-idea-stream' ), sprintf( '<a href="%s">MediaThèque</a>',
					/* Translators URL to the MediaThèque Github wiki (en) or my blog (fr) */
					esc_url( __( 'https://github.com/imath/mediatheque/wiki', 'wp-idea-stream' ) )
				) ); ?></p>

				<picture>
					<img src="https://cldup.com/5Eci6QtW3S.gif" alt="" style="display:block; margin: 0 auto;"/>
				</picture>

			</div>

			<div class="clear"></div>
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
				<a href="https://github.com/imath"><img src="//0.gravatar.com/avatar/8b208ca408dad63888253ee1800d6a03?s=60" class="gravatar" alt="Mathieu Viet" /></a>
				<a class="web" href="https://github.com/imath">imath</a>
				<span class="title"><?php _e( 'Creator', 'wp-idea-stream' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-aglekis">
				<a href="http://profiles.wordpress.org/aglekis"><img src="//0.gravatar.com/avatar/9aed4c3373374032e4ecdde02894d5fb?s=60" class="gravatar" alt="Grégoire Noyelle" /></a>
				<a class="web" href="https://profiles.wordpress.org/aglekis">Grégoire Noyelle</a>
				<span class="title"><?php _e( 'Developer', 'wp-idea-stream' ); ?></span>
			</li>
		</ul>

		<h3 class="wp-people-group"><?php _e( 'Rock Stars', 'wp-idea-stream' ); ?></h3>
		<ul class="wp-people-group" id="wp-people-group-rock-stars">
			<li class="wp-person" id="wp-person-jennybeaumont">
				<a href="https://profiles.wordpress.org/jennybeaumont"><img src="//0.gravatar.com/avatar/c5b883c76357aa309642c255edd51ee1?s=60" class="gravatar" alt="Jenny Beaumont" /></a>
				<a class="web" href="http://profiles.wordpress.org/jennybeaumont">Jenny Beaumont</a>
			</li>
		</ul>

		<h3 class="wp-people-group"><?php printf( esc_html__( 'Contributors to %s', 'wp-idea-stream' ), $display_version ); ?></h3>
		<p class="wp-credits-list">
			<a href="https://github.com/imath">imath</a>, <a href="https://github.com/hubwoop">hubwoop</a>.
		</p>

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
