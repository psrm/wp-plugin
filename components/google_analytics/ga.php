<?php

namespace psrm\google_analytics;

class GA
{
	function __construct()
	{
		add_action('wp_head', array($this, 'tracking_code'));
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