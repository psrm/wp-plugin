<?php

namespace psrm\widgets;

use psrm\utils\Views;
use psrm\PSRM;

add_action( 'widgets_init', function(){
	register_widget( 'psrm\widgets\DailyHours' );
});

class DailyHours extends \WP_Widget {
	private $view;

	function __construct() {
		parent::__construct(
			'daily_hours_widget',
			__( 'Daily Hours (PSRM)', 'psrm' ),
			array(
				'description' => __( 'Display the day\'s hours.', 'psrm' ),
			)
		);

		$this->view = new Views( PSRM::$views );
	}

	function widget( $args, $instance ) {
		if($details = get_transient( 'psrm-details' )) {
			echo $this->view->render( 'daily-hours', [
				'args'     => $args,
				'instance' => $instance,
				'details'  => $details
			] );
		}
	}
}