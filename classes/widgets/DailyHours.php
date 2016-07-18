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
            $hours = $details->opening_hours;
            $open_hours = '';

            if ($hours->open_now) {
                $open_hours = 'currently <p class="museum-status museum-open" > OPEN</p> until ';

                $today = date('w');
                foreach ($hours->periods as $day) {
                    if ($today == $day->open->day) {
                        $open_hours .= date('g:i a', strtotime($day->close->time));
                    }
                }
            } else {
                $day_of_week = date('w');
                $current_hour = date('Hi');

                $found_on_first_check = false;

                foreach ($hours->periods as $day) {
                    if ($day_of_week == $day->open->day && $current_hour <= $day->close->time) {
                        $open_hours = '<p class="museum-status museum-open">OPEN TODAY</p> from ' . date('g:i a', strtotime($day->open->time)) . ' - ' . date('g:i a', strtotime($day->close->time));
                        $found_on_first_check = true;
                        break;
                    } else {
                        if ($day_of_week == 6 && $current_hour > $day->close->time) {
                            $today = -1;
                        } else {
                            $today = $day_of_week;
                        }

                        if ($current_hour > $day->close->time) {
                            $today++;
                        }

                        if ($today <= $day->open->day) {
                            $open_hours = 'currently <p class="museum-status museum-closed">CLOSED</p> until ' . \psrm\controllers\GooglePlaces::$dowMap[$day->open->day] . ': ' . date('g:i a', strtotime($day->open->time)) . ' - ' . date('g:i a', strtotime($day->close->time));
                            $found_on_first_check = true;
                            break;
                        }
                    }
                }

                if (!$found_on_first_check) {
                    $day = $hours->periods[0];
                    $open_hours = 'currently <p class="museum-status museum-closed">CLOSED</p> until ' . \psrm\controllers\GooglePlaces::$dowMap[$day->open->day] . ': ' . date('g:i a', strtotime($day->open->time)) . ' - ' . date('g:i a', strtotime($day->close->time));
                }
            }
			echo $this->view->render( 'daily-hours', [
				'args'     => $args,
				'instance' => $instance,
				'open_hours'  => $open_hours
			] );
		}
	}
}