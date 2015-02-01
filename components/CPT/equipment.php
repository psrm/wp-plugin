<?php

namespace psrm\CPT\equipment;
use psrm\PSRM;

class CPT
{
	static $trains;
	function __construct()
	{
		self::$trains = array(
			'steam' => array(
				'singular' => 'Steam Locomotive',
				'plural' => 'Steam Locomotives',
			),
			'diesel' => array(
				'singular' => 'Diesel Locomotive',
				'plural' => 'Diesel Locomotives',
			),
			'passenger' => array(
				'singular' => 'Passenger Car',
				'plural' => 'Passenger Cars'
			),
			'freight' => array(
				'singular' => 'Freight Car',
				'plural' => 'Freight Cars'
			),
			'cabooses' => array(
				'singular' => 'Caboose',
				'plural' => 'Cabooses'
			),
			'equipment' => array(
				'singular' => 'Support Equipment',
				'plural' => 'Support Equipment'
			),
		);
		add_action('init', array($this, 'register_roster_post_types'));
		//add_action('admin_menu', array($this, 'register_submenu'));
		add_filter('post_updated_messages', array($this, 'post_roster_updated_messages'));
		add_filter('bulk_post_updated_messages', array($this, 'bulk_post_roster_updated_messages'), 10, 2);
	}

	function register_roster_post_types()
	{
		$i = 1;
		foreach(self::$trains as $slug => $nouns)
		{
			$this->register_roster_post_type($i, $slug, $nouns['singular'], $nouns['plural']);
			$i++;
		}
	}

	static function roster_post_type($slug)
	{
		return 'psrm_' . $slug;
	}

	function register_roster_post_type($i, $slug, $singular, $plural)
	{
		$labels = array(
			'name' => sprintf('Our %s', $plural),
			'singular_name' => sprintf('%s', $singular),
			'add_new' => sprintf('Add New %s', $singular),
			'add_new_item' => sprintf('Add New %s', $singular),
			'edit_item' => sprintf('Edit %s', $singular),
			'new_item' => sprintf('New %s', $singular),
			'view_item' => sprintf('View %s', $singular),
			'search_items' => sprintf('Search %s', $plural),
			'not_found' => sprintf('No %s found.', $plural),
			'not_found_in_trash' => sprintf('No %s found in Trash.', $plural),
			'parent_item_colon' => sprintf('Parent %s:', $singular),
			'all_items' => sprintf('All %s', $plural),
			'menu_name' => $i == 1 ? 'Roster' : false,

		);
		reset(self::$trains);
		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'description' => sprintf('Our %s', $plural),
			'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => $i == 1 ? true : 'edit.php?post_type=' . self::roster_post_type(key(self::$trains)),
			'menu_position' => 20,
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => array(
				'slug' => 'trains/' . $slug,
				'with_front' => true,
				'feeds' => false,
				'pages' => false
			),
			'capability_type' => 'post'
		);

		register_post_type( self::roster_post_type($slug), $args );
	}

	function register_submenu()
	{
		//Remove Equipment Roster
		remove_submenu_page('edit.php?post_type=' . PSRM::$people_post_type, 'edit.php?post_type=' . PSRM::$roster_post_type);

		add_submenu_page('edit.php?post_type=' . PSRM::$people_post_type, 'All Equipment', 'All Equipment', 'edit_posts', 'edit.php?post_type=' . PSRM::$roster_post_type);
		add_submenu_page('edit.php?post_type=' . PSRM::$people_post_type, 'Add New Equipment', 'Add New Equipment', 'edit_posts', 'post-new.php?post_type=' . PSRM::$roster_post_type);
	}

	function post_roster_updated_messages($messages)
	{
		global $post;
		foreach(self::$trains as $slug => $nouns)
		{
			$singular = $nouns['singular'];
			$messages[ self::roster_post_type($slug) ] = array(
				0  => '',
				1  => sprintf( '%s updated. <a href="%s">View %s</a>', $singular,
					esc_url( get_permalink( $post->ID ) ), strtolower($singular) ),
				2  => 'Custom field updated.',
				3  => 'Custom field deleted.',
				4  => sprintf( '%s updated.', $singular ),
				5  => isset( $_GET['revision'] ) ? sprintf( '%s restored to revision from %s', $singular,
					wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => sprintf( '%s published. <a href="%s">View %s</a>', $singular,
					esc_url( get_permalink( $post->ID ) ), strtolower($singular) ),
				7  => sprintf( '%s saved.', 'Equipment' ),
				8  => sprintf( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', $singular,
					esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ), strtolower($singular) ),
				9  => sprintf( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>',
					$singular,
					date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ),
					strtolower($singular) ),
				10 => sprintf( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>',
					$singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ),
					strtolower($singular) ),
			);
		}

		return $messages;
	}

	function bulk_post_roster_updated_messages( $bulk_messages, $bulk_counts ) {

		foreach(self::$trains as $slug => $nouns)
		{
			$singular = $nouns['singular'];
			$plural = $nouns['plural'];
			
			$bulk_messages[ self::roster_post_type($slug) ] = array(
				'updated'   => _n( '%s ' . strtolower($singular) . ' updated.', '%s ' . strtolower($plural) . ' updated.', $bulk_counts['updated'] ),
				'locked'    => _n( '%s ' . strtolower($singular) . ' not updated, somebody is editing it.', '%s ' . strtolower($plural) . ' not updated, somebody is editing them.', $bulk_counts['locked'] ),
				'deleted'   => _n( '%s ' . strtolower($singular) . ' permanently deleted.', '%s ' . strtolower($plural) . ' permanently deleted.', $bulk_counts['deleted'] ),
				'trashed'   => _n( '%s ' . strtolower($singular) . ' moved to the Trash.', '%s ' . strtolower($plural) . ' moved to the Trash.', $bulk_counts['trashed'] ),
				'untrashed' => _n( '%s ' . strtolower($singular) . ' restored from the Trash.', '%s ' . strtolower($plural) . ' restored from the Trash.', $bulk_counts['untrashed'] ),
			);
		}

		return $bulk_messages;

	}
}