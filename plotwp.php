<?php

/**
 * @package ResponsivePlotwp
 * @version 0.5
 */
/*
Plugin Name: ResponsivePlot.wp
Plugin URI: http://github.com/ntrcpt/plotwp
Description: Add JSON-based plots to posts and pages using the <a href="https://plot.ly/javascript/">plotly.js</a> API. Fork of Plot.wp (https://github.com/paleolimbot/plotwp) by Dewey Dunnington 
Author: David Reiner
Version: 0.5
Author URI: https://davidreiner.at/
*/

/*
 * Add plot.ly JS library from cdn + responsive unbranded plot layout responsiveplot.css to header
 */
function plotwp_enqueue_scripts() {
    wp_register_script('plot.ly', 'https://cdn.plot.ly/plotly-latest.min.js');
    wp_enqueue_script( 'plot.ly', false );
    wp_enqueue_style('plotwp_default', plugins_url('responsiveplot.css', __FILE__), false);
}

add_action( 'wp_enqueue_scripts', 'plotwp_enqueue_scripts' );

/*
 * Add the [plotly] shortcode
 */
$plotwp_plotly_div_id = 1;
function plotwp_plotly_shortcode( $atts, $content = null ) {
    global $plotwp_plotly_div_id;

    if(empty($content)) {
        return "";
    }
    $json = json_decode($content);
    if(empty($json)) {
        return "<b>Invalid JSON in plotly shortcode</b>";
    }

    if(!is_array($atts)) {
        $atts = array();
    }
    $divatts = join(' ', array_map(function($key) use ($atts) {
            return $key.'="'. esc_attr($atts[$key]).'"';
        }, array_keys($atts)));

    $plotly_div_id = 'plotwp_plotly_' . $plotwp_plotly_div_id++;
    return '<div ' . $divatts . ' id="' . $plotly_div_id . '"></div>
    <script type="text/javascript">
      (function() {
      var d3 = Plotly.d3;
      var WIDTH_IN_PERCENT_OF_PARENT = 100,
          HEIGHT_IN_PERCENT_OF_PARENT = 66;
      var gd3 = d3.select(\'#' . $plotly_div_id . '\')
          .style({
            width: WIDTH_IN_PERCENT_OF_PARENT + \'%\',
            \'margin-left\': (100 - WIDTH_IN_PERCENT_OF_PARENT) / 2 + \'%\',
            height: HEIGHT_IN_PERCENT_OF_PARENT + \'vh\',
            \'margin-top\': 0
        });
      var gd = gd3.node();
        Plotly.plot( gd, ' . json_encode($json) . ' );
        window.onresize = function() {
            Plotly.Plots.resize(gd);
        };
    })();
    </script>';
}
add_shortcode( 'plotly', 'plotwp_plotly_shortcode' );

function plotwp_shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
    $shortcodes[] = 'plotly';
    return $shortcodes;
}
add_filter( 'no_texturize_shortcodes', 'plotwp_shortcodes_to_exempt_from_wptexturize' );
remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop' , 99);
