<?php

namespace psrm\controllers;

use psrm\utils\Views;
use psrm\PSRM;

class Widgets
{
    private $view;

    function __construct()
    {
        $this->view = new Views(PSRM::$views);
        add_action('in_widget_form', [$this, 'in_widget_form'], 10, 3);
        add_filter('widget_update_callback', [$this, 'in_widget_form_update'], 10, 3);
        add_filter('dynamic_sidebar_params', [$this, 'dynamic_sidebar_params']);
    }

    function in_widget_form($t, $return, $instance)
    {
        echo $this->view->render('in-widget-form', ['t' => $t, 'return' => $return, 'instance' => $instance]);
        $return = null;
        return array($t, $return, $instance);
    }

    function in_widget_form_update($instance, $new_instance, $old_instance)
    {
        $instance['widgetwidth'] = sanitize_text_field($new_instance['widgetwidth']);
        $instance['customcss'] = sanitize_text_field($new_instance['customcss']);
        return $instance;
    }


    function dynamic_sidebar_params($params)
    {
        global $wp_registered_widgets;
        $widget_id = $params[0]['widget_id'];
        $widget_obj = $wp_registered_widgets[$widget_id];
        $widget_opt = get_option($widget_obj['callback'][0]->option_name);
        $widget_num = $widget_obj['params'][0]['number'];

        // Set $widgetwidth to the option chosen
        if (isset($widget_opt[$widget_num]['widgetwidth']) && ($widget_opt[$widget_num]['widgetwidth'] != '')) {
            $widgetwidth = $widget_opt[$widget_num]['widgetwidth'] . ' ';
        } else {
            $widgetwidth = '';
        }

        // Set $customcss to the custom option entered
        if (isset($widget_opt[$widget_num]['customcss']) && ($widget_opt[$widget_num]['customcss']) != '') {
            $customcss = $widget_opt[$widget_num]['customcss'] . ' ';
        } else {
            $customcss = '';
        }

        // Add the chosen widget options to the css class
        $params[0]['before_widget'] = preg_replace('/class="/', 'class="' . $widgetwidth . $customcss, $params[0]['before_widget'], 1);

        return $params;
    }
}
