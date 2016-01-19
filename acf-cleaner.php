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

add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
	wp_enqueue_script('waypoints', plugins_url().'/acf-cleaner/script.js', array('jquery'), null, true);
	add_submenu_page('tools.php', 'ACF Cleaner', 'ACF Cleaner', 'administrator', 'acf-cleaner', 'acf_cleaner');

}

function acf_cleaner() {
	global $wpdb;

	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>ACF Cleaner</h2>';

		echo '<h3>Post Meta</h3>';
		echo generate_table('post', $wpdb->get_results(postmeta_fields()));

		echo '<h3>User Meta</h3>';
		echo generate_table('user', $wpdb->get_results(postmeta_fields('wp_usermeta')));
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

function generate_table($type, $results){
	if(sizeof($results) > 0){
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
					$output .= '<td><a href="#" data-key="'.substr($result->meta_key, 1).'" data-type="'.$type.'" class="acf-cleaner-delete">Delete</a></td>';
					$output .= '</tr>';
				}
					
			$output .= '</tbody>';
		$output .= '</table>';
	}else{
		$output .= '<p>All custom fields empty.</p>';
	}
	return $output;
}

add_action( 'wp_ajax_remove_meta_tags', 'remove_meta_tags' );
function remove_meta_tags(){
	header('Content-Type: application/json');
	http_response_code(200);
	$errors = array();

	if($_POST['type'] === 'post'){
		delete_post_meta_by_key( $_POST['key'] );
		delete_post_meta_by_key( '_'.$_POST['key'] );

	}else if($_POST['type'] === 'user'){
		$users = get_users();
		foreach ($users as $user) {
			delete_user_meta($user->ID, $_POST['key']);
			delete_user_meta($user->ID, '_'.$_POST['key']);
		}
	}

	echo json_encode(array('status' => 'Finished'));
	die();
}