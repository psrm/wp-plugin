<?php

namespace psrm\controllers;

use psrm\utils\View;

class ExtraPostHeadersAndFooters
{
	const Header = 'header';
	const Footer = 'footer';
	const PageSections = [self::Header, self::Footer];

	public function __construct()
	{
		add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
		add_action('save_post', [$this, 'saveMetaBoxes']);
		add_action('wp_head', [$this, 'renderMetaDataInHeader']);
		add_action('wp_footer', [$this, 'renderMetaDataInFooter']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueAce']);
	}

	/**
	 * Add any scripts to the header.
	 */
	public function renderMetaDataInHeader()
	{
		echo $this->getMetaDataOutput(self::Header);
	}

	/**
	 * Add any scripts to the footer.
	 */
	public function renderMetaDataInFooter()
	{
		echo $this->getMetaDataOutput(self::Footer);
	}

	/**
	 * Get the HTML output for each section.
	 *
	 * @param $section
	 * @return null|string
	 */
	private function getMetaDataOutput($section)
	{
		$meta = get_post_meta(get_the_ID(), $this->getMetaBoxValueKey($section), true);

		if (empty($meta)) {
			return null;
		}

		return new View('extra-header-footer-output', [
			'value' => $meta,
		]);
	}

	/**
	 * Enqueue ACE editor
	 * @link https://ace.c9.io
	 */
	public function enqueueAce()
	{
		wp_enqueue_script('ace', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.8/ace.js', [], '1.2.8');
	}

	/**
	 * Adds one meta box for each section.
	 *
	 * @param $postType
	 */
	public function addMetaBoxes($postType)
	{
		foreach (self::PageSections as $section) {
			$CapitalizedSection = ucfirst($section);
			add_meta_box($this->getMetaBoxValueKey($section), "Insert Javascript to $CapitalizedSection", [$this, 'renderMetaBoxes'], $postType, 'normal', 'high', ['section' => $section, 'postType' => $postType]);
		}
	}

	/**
	 * Displays the meta boxes on the page/post admin.
	 *
	 * @param $post
	 * @param $args
	 */
	public function renderMetaBoxes($post, $args)
	{
		/* @var $section string */
		/* @var $type string */
		extract($args['args']);
		$nonceName = $this->getMetaBoxNonceName($section);
		$viewArgs = [
			'nonce' => wp_nonce_field($nonceName, $nonceName, true, false),
			'label' => $this->getMetaBoxFieldName($section),
			'value' => get_post_meta($post->ID, $this->getMetaBoxValueKey($section), true),
		];

		echo new View('extra-header-footer-metaboxes', $viewArgs);
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param int $postID Post ID.
	 * @return int
	 */
	public function saveMetaBoxes($postID)
	{
		if ($_POST['post_type'] == 'page') {
			if (!current_user_can('edit_page', $postID)) {
				return $postID;
			}
		} else if (!current_user_can('edit_post', $postID)) {
			return $postID;
		}

		foreach (self::PageSections as $section) {
			$nonceName = $this->getMetaBoxNonceName($section);

			if (!isset($_POST[$nonceName])) {
				return $postID;
			}

			if (!wp_verify_nonce($_POST[$nonceName], $nonceName)) {
				return $postID;
			}

			$metaKey = $this->getMetaBoxValueKey($section);
			$currentData = get_post_meta($postID, $metaKey, true);
			$newData = $_POST[$this->getMetaBoxFieldName($section)];

			if ($currentData) {
				if (empty($newData)) {
					delete_post_meta($postID, $metaKey);
				} elseif ($currentData != $newData) {
					update_post_meta($postID, $metaKey, $newData);
				}
			} elseif ($newData) {
				add_post_meta($postID, $metaKey, $newData, true);
			}
		}

		return $postID;
	}

	/**
	 * Returns the HTML field name for a given section.
	 *
	 * @param string $section
	 * @return string
	 */
	private function getMetaBoxFieldName($section)
	{
		return "post_meta_$section";
	}

	/**
	 * Returns the meta data key for a given section.
	 *
	 * @param $section
	 * @return string
	 */
	private function getMetaBoxValueKey($section)
	{
		return "psrm_post_meta_$section";
	}

	/**
	 * Returns the meta box nonce name for a given section.
	 *
	 * @param $section
	 * @return string
	 */
	private function getMetaBoxNonceName($section)
	{
		return "psrm_meta_nonce_$section";
	}
}
