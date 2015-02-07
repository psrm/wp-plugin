<?php

namespace psrm\google_analytics;

class GA
{
	public $cookie_name;
	function __construct()
	{
		$this->cookie_name = 'psrm_ga_exclude';
		add_action('admin_init', array($this, 'tracking_exclusion_cookie'));
		if(!isset($_COOKIE[$this->cookie_name]) && (strpos($_SERVER['HTTP_HOST'], 'dev') === false || $_SERVER['SERVER_NAME'] == 'staging.psrm.org')) {
			add_action( 'wp_head', array( $this, 'tracking_code' ) );
		}
	}

	function tracking_exclusion_cookie()
	{
		if ( !isset($_COOKIE[$this->cookie_name])) {
			setcookie( $this->cookie_name, 1, time() + 3600 * 24 * 365, COOKIEPATH, COOKIE_DOMAIN, false );
		}
	}

	function tracking_code()
	{
		echo <<<HTML
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-21545268-1', 'auto', {'allowLinker': true});
  ga('require', 'linker');
  ga('linker:autoLink', ['dynamicticketsolutions.com']);
  ga('send', 'pageview');

</script>
HTML;
	}
}