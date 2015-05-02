<?php

namespace psrm\controllers;

use psrm\PSRM;
use psrm\utils\Views;

class ServiceAlerts
{
	private $view;
	function __construct()
	{
		$this->view = new Views(PSRM::$views);
		add_shortcode(PSRM::$slug . '-service-alerts', [$this, 'display_service_alerts']);
		add_action('init', array($this, 'register_service_alert_post_type'));
		add_filter('post_updated_messages', array($this, 'post_service_alert_updated_messages'));
		add_filter('bulk_post_updated_messages', array($this, 'bulk_post_service_alert_updated_messages'), 10, 2);
		add_action('post_submitbox_misc_actions', [$this, 'service_alert_auto_delete_submitbox']);
		add_action('save_post_service_alerts', [$this, 'service_alert_auto_delete_update']);
		add_action('psrm-daily-cron', [$this, 'service_alert_auto_delete']);
	}

	function display_service_alerts()
	{
		echo $this->view->render('service-alert');
	}
	
	function register_service_alert_post_type()
	{
		$labels = array(
			'name'               => sprintf( '%s', 'Service Alerts' ),
			'singular_name'      => sprintf( '%s', 'Service Alert' ),
			'add_new'            => sprintf( 'Add New %s', 'Service Alert' ),
			'add_new_item'       => sprintf( 'Add New %s', 'Service Alert' ),
			'edit_item'          => sprintf( 'Edit %s', 'Service Alert' ),
			'new_item'           => sprintf( 'New %s', 'Service Alert' ),
			'view_item'          => sprintf( 'View %s', 'Service Alert' ),
			'search_items'       => sprintf( 'Search %s', 'Service Alerts' ),
			'not_found'          => sprintf( 'No %s found.', 'Service Alerts' ),
			'not_found_in_trash' => sprintf( 'No %s found in Trash.', 'Service Alerts' ),
			'parent_item_colon'  => sprintf( 'Parent %s:', 'Service Alert' ),
			'all_items'          => sprintf( 'All %s', 'Service Alerts' ),
			'menu_name'          => 'Service Alerts',

		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'description'         => 'Service Alerts',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 21,
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => array(
				'slug'       => 'alert',
				'with_front' => true,
				'feeds'      => false,
				'pages'      => false
			),
			'capability_type'     => 'post'
		);

		register_post_type( 'service_alerts', $args );
	}
	
	function post_service_alert_updated_messages($messages)
	{
		global $post;
		$messages['service_alerts'] = array(
			0  => '',
			1  => sprintf( '%s updated. <a href="%s">View %s</a>', 'Alert',
				esc_url( get_permalink( $post->ID ) ), strtolower('Alert') ),
			2  => 'Custom field updated.',
			3  => 'Custom field deleted.',
			4  => sprintf( '%s updated.', 'Alert' ),
			5  => isset( $_GET['revision'] ) ? sprintf( '%s restored to revision from %s', 'Alert',
				wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( '%s published. <a href="%s">View %s</a>', 'Alert',
				esc_url( get_permalink( $post->ID ) ), strtolower('Alert') ),
			7  => sprintf( '%s saved.', 'Equipment' ),
			8  => sprintf( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'Alert',
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ), strtolower('Alert') ),
			9  => sprintf( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>',
				'Alert',
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ),
				strtolower('Alert') ),
			10 => sprintf( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>',
				'Alert', esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ),
				strtolower('Alert') ),
		);

		return $messages;
	}

	function bulk_post_service_alert_updated_messages( $bulk_messages, $bulk_counts ) {

		$bulk_messages['service_alerts'] = array(
			'updated'   => _n( '%s ' . strtolower('alert') . ' updated.', '%s ' . strtolower('alert') . ' updated.', $bulk_counts['updated'] ),
			'locked'    => _n( '%s ' . strtolower('alert') . ' not updated, somebody is editing it.', '%s ' . strtolower('alert') . ' not updated, somebody is editing them.', $bulk_counts['locked'] ),
			'deleted'   => _n( '%s ' . strtolower('alert') . ' permanently deleted.', '%s ' . strtolower('alert') . ' permanently deleted.', $bulk_counts['deleted'] ),
			'trashed'   => _n( '%s ' . strtolower('alert') . ' moved to the Trash.', '%s ' . strtolower('alert') . ' moved to the Trash.', $bulk_counts['trashed'] ),
			'untrashed' => _n( '%s ' . strtolower('alert') . ' restored from the Trash.', '%s ' . strtolower('alert') . ' restored from the Trash.', $bulk_counts['untrashed'] ),
		);

		return $bulk_messages;

	}

	function service_alert_auto_delete_submitbox()
	{
		global $post;
		if($post->post_type == 'service_alerts' && $post->post_status == 'publish') {
			echo $this->view->render( 'service-alert-auto-delete' );
		}
	}

	function service_alert_auto_delete_update($post_id)
	{
		global $post;
		if(isset($_REQUEST['service_alert_delete_after']) && $post->post_date_gmt && $post->post_status == 'publish') {
			$num_days = sanitize_text_field($_REQUEST['service_alert_delete_after']);
			update_post_meta($post_id, 'service_alert_delete_after', $num_days);
			update_post_meta($post_id, 'service_alert_delete_timestamp', strtotime($post->post_date_gmt) + (DAY_IN_SECONDS * $num_days));
		}
	}

	function service_alert_auto_delete()
	{
		global $wpdb;

		$posts_to_delete = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'service_alert_delete_timestamp' AND meta_value < '%d'", time()), ARRAY_A);

		foreach ((array) $posts_to_delete as $post)
		{
			$post_id = (int) $post['post_id'];
			if (!$post_id) {
				continue;
			}
			wp_delete_post($post_id, true);
		}
	}
}