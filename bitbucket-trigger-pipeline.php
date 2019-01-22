<?php
/**
 * Bitbucket Trigger Pipeline.
 *
 * @package Bitbucket Trigger Pipeline
 */

/*
Plugin Name: Bitbucket Trigger Pipeline
Plugin URI: https://github.com/kmturley/wordpress-bitbucket-trigger-pipeline
Description: WordPress Plugin triggers a Bitbucket Pipeline when user publishes a post (using environment variables BITBUCKET_PROJECT, BITBUCKET_USERNAME and BITBUCKET_PASSWORD)
Version: 0.2.0
Author: Kim T
Author URI: https://github.com/kmturley
License: GPL
Copyright: Kim T
*/

/**
 * Hook called after content is published.
 *
 * @param integer $id Post ID published.
 * @param object  $post Post object.
 */
function bitbucket_trigger_pipeline_publish_static_hook( $id, $post ) {
	// Do nothing.
}

add_action( 'publish_page', 'bitbucket_trigger_pipeline_publish_static_hook' );
add_action( 'publish_news_ideas', 'bitbucket_trigger_pipeline_publish_static_hook' );
add_action( 'publish_work', 'bitbucket_trigger_pipeline_publish_static_hook' );

/**
 * Trigger Bitbucket Pipeline to deploy changes.
 */
function bitbucket_trigger_pipeline_deploy() {

	// Use config from wp-config.php first and the from settings.
	$bitbucket_project      = defined( 'BITBUCKET_PROJECT' ) ? BITBUCKET_PROJECT : get_option( 'option_project' );
	$bitbucket_branch       = defined( 'BITBUCKET_BRANCH' ) ? BITBUCKET_BRANCH : get_option( 'option_branch' );
	$bitbucket_username     = defined( 'BITBUCKET_USERNAME' ) ? BITBUCKET_USERNAME : get_option( 'option_username' );
	$bitbucket_app_password = defined( 'BITBUCKET_PASSWORD' ) ? BITBUCKET_PASSWORD : get_option( 'option_password' );

	// If variables are set, then trigger static build.
	if ( $bitbucket_project && $bitbucket_branch && $bitbucket_username && $bitbucket_app_password ) {
		$data = array(
			'target' => array(
				'ref_type' => 'branch',
				'type'     => 'pipeline_ref_target',
				'ref_name' => $bitbucket_branch,
			),
		);

		$response = wp_remote_post(
			'https://api.bitbucket.org/2.0/repositories/' . $bitbucket_project . '/pipelines/',
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $bitbucket_username . ':' . $bitbucket_app_password ),
					'Content-Type'  => 'application/json',
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			error_log( 'Failed to start deployment:' . $response->get_error_message() );
			// phpcs:enable
			echo '<pre>' . esc_html_e( 'Failed to start deployment: unknown error with the deployment pipeline' ) . '</pre>';
		} else {
			$response_code = $response['response']['code'];
			if ( 201 === $response_code ) {
				echo '<pre>' . esc_html_e( 'Starting deploy to' ) . ' ' . esc_attr( $bitbucket_project ) . '</pre>';
			} elseif ( 400 === $response_code ) {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log( 'Failed to start deployment: response code ' . $response['response']['code'] );
				// phpcs:enable
				echo '<pre>' . esc_html_e( 'Failed to start deployment: invalid request' ) . '</pre>';
			} elseif ( 404 === $response_code ) {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log( 'Failed to start deployment: response code ' . $response['response']['code'] );
				// phpcs:enable
				echo '<pre>' . esc_html_e( 'Failed to start deployment: the account or repository was not found' ) . '</pre>';
			} else {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log( 'Failed to start deployment: response code ' . $response['response']['code'] );
				// phpcs:enable
				echo '<pre>' . esc_html_e( 'Failed to start deployment: unexpected response from the deployment pipeline.' ) . '</pre>';
			}
		}
	} else {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions
		error_log( 'Failed to start deployment: invalid configuration' );
		// phpcs:enable
		echo '<pre>' . esc_html_e( 'Failed to start deployment: invalid configuration' ) . '</pre>';
	}
}

add_action( 'admin_init', 'my_general_section' );

/**
 * Register admin settings for the plugin.
 */
function my_general_section() {
	add_settings_section(
		'my_settings_section',
		'Bitbucket Settings',
		'my_section_options_callback',
		'general'
	);

	add_settings_field(
		'option_project',
		'BITBUCKET PROJECT',
		'my_textbox_callback',
		'general',
		'my_settings_section',
		array(
			'option_project',
		)
	);

	add_settings_field(
		'option_branch',
		'BITBUCKET BRANCH',
		'my_textbox_callback',
		'general',
		'my_settings_section',
		array(
			'option_branch',
		)
	);

	add_settings_field(
		'option_username',
		'BITBUCKET USERNAME',
		'my_textbox_callback',
		'general',
		'my_settings_section',
		array(
			'option_username',
		)
	);

	add_settings_field(
		'option_password',
		'BITBUCKET APP PASSWORD',
		'my_password_callback',
		'general',
		'my_settings_section',
		array(
			'option_password',
		)
	);

	register_setting( 'general', 'option_project', 'esc_attr' );
	register_setting( 'general', 'option_branch', 'esc_attr' );
	register_setting( 'general', 'option_username', 'esc_attr' );
	register_setting( 'general', 'option_password', 'esc_attr' );
}

/**
 * Print section title.
 */
function my_section_options_callback() {
	echo '<p>Settings for Bitbucket</p>';
}

/**
 * Print username input field.
 *
 * @param array $args Input field arguments.
 */
function my_textbox_callback( $args ) {
	$option = get_option( $args[0] );
	echo '<input type="text" id="' . esc_attr( $args[0] ) . '" name="' . esc_attr( $args[0] ) . '" value="' . esc_attr( $option ) . '" />';
}

/**
 * Print password input field.
 *
 * @param array $args Input field arguments.
 */
function my_password_callback( $args ) {
	$option = get_option( $args[0] );
	echo '<input type="password" id="' . esc_attr( $args[0] ) . '" name="' . esc_attr( $args[0] ) . '" value="' . esc_attr( $option ) . '" />';
}

add_action( 'admin_menu', 'test_button_menu' );

/**
 * Test button menu item.
 */
function test_button_menu() {
	add_menu_page( 'Deploy Button Page', 'Deploy', 'manage_options', 'test-button-slug', 'test_button_admin_page' );
}

/**
 * Test button admin page.
 */
function test_button_admin_page() {
	// General check for user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient pilchards to access this page.' ) );
	}

	// Start building the page.
	echo '<div class="wrap">';

	echo '<h2>Deploy</h2>';

	// Check whether the button has been pressed AND also check the nonce.
	if ( isset( $_POST['test_button'] ) && check_admin_referer( 'test_button_clicked' ) ) {
		// The button has been pressed AND we've passed the security check.
		test_button_action();
	}

	echo '<form action="options-general.php?page=test-button-slug" method="post">';

	/**
	 * This is a WordPress security feature.
	 *
	 * @see https://codex.wordpress.org/WordPress_Nonces
	 */
	wp_nonce_field( 'test_button_clicked' );
	echo '<input type="hidden" value="true" name="test_button" />';
	submit_button( 'Deploy to Production' );
	echo '</form>';

	echo '</div>';
}

/**
 * Action called when 'test_button' is clicked.
 */
function test_button_action() {
	bitbucket_trigger_pipeline_deploy();
	echo '<div id="message" class="updated fade"><p>The "Deploy to Production" button was clicked.</p></div>';
}
