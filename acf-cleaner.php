<?php
/**
 * Plugin Name: ACF Meta Cleanup
 * Plugin URI: http://jameswburke.com
 * Description: Get rid of unused and outdated ACF plugin fields.
 * Version: 0.1.0
 * Author: James W Burke
 * Author URI: http://jameswburke.com
 * License: GPL2
 */

add_action( 'admin_init', 'my_plugin_admin_init' );
add_action('admin_menu', 'my_plugin_menu');

function my_plugin_admin_init() {
	wp_register_script( 'my-plugin-script', plugins_url( '/script.js', __FILE__ ) );
}

function my_plugin_admin_scripts() {
        /* Link our already registered script to a page */
        wp_enqueue_script( 'my-plugin-script' );
    }

function my_plugin_menu() {
	add_submenu_page('tools.php', 'ACF Cleaner', 'ACF Cleaner', 'administrator', 'acf-cleaner', 'acf_cleaner');

}

function acf_cleaner() {
	global $wpdb;
	wp_enqueue_script( 'acf-cleaner-script', plugins_url().'/acf-cleaner/script.js', array('jQuery'), false, true );

	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>ACF Cleaner</h2>';

		echo '<h3>Post Meta</h3>';
		echo generate_table($wpdb->get_results(postmeta_fields()));

		echo '<h3>User Meta</h3>';
		echo generate_table($wpdb->get_results(postmeta_fields('wp_usermeta')));
	echo '</div>';
}


function postmeta_fields($table = 'wp_postmeta'){
	return 'SELECT meta_key,count(*) as total, meta_value, wp_posts.post_type
	FROM '.$table.'
	LEFT JOIN wp_posts ON '.$table.'.meta_value = wp_posts.post_name
	WHERE LEFT(meta_value, 6) = \'field_\'
	AND wp_posts.post_name IS NULL
	GROUP BY meta_key
	ORDER BY meta_key';

}

function generate_table($results){
	$output .= '<table>';
		$output .= '<thead>';
			$output .= '<tr>';
				$output .= '<th>Meta Key</th>';
				$output .= '<th></th>';
			$output .= '</tr>';
		$output .= '</thead>';
		$output .= '<tbody>';
			foreach($results as $result){
				$output .= '<tr>';
				$output .= '<td>'.substr($result->meta_key, 1).'</td>';
				$output .= '<td><a href="#">Delete</a></td>';
				$output .= '</tr>';
			}
		$output .= '</tbody>';
	$output .= '</table>';
	return $output;
}