<?php

namespace psrm\utils;

class Cron
{
	function __construct()
	{
		$this->create_daily_cron_schedule();
	}

	function create_daily_cron_schedule()
	{
		if(!wp_next_scheduled('psrm-daily-cron')) {
			wp_schedule_event(strtotime('today 08:00'), 'daily', 'psrm-daily-cron');
		}

		if(!wp_next_scheduled('psrm-hourly-cron')) {
			wp_schedule_event(strtotime('today 09:03'), 'hourly', 'psrm-hourly-cron');
		}
	}
}