<?php
/**
 * Plugin Name: Similar Posts Ontology
 * Plugin URI: http://www.planetkodiak.com/blog/similar-posts
 * Description: Returns a list of similar posts using ontological philosophies based on your taxonomies (tags, categories, and custom taxonomies). The stronger your taxonomies, the better the results! The widget only works on "single" posts, but you can use the pk_related_return($post->ID); function to grab them programmatically.
 * Version: 1.0.1
 * Author: Cory Fischer
 * Author URI: http://www.planetkodiak.com
 * Text Domain: similarpostsontology
 * License: GPL2
 */
/*  Copyright 2015  Cory Fischer (email : cfischer83@yahoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
defined('ABSPATH') or die("Nope");

// Example of programatic call: pk_related_return(353, array('posts_per_page'=>1))


define('PK_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once(PK_DIR . 'Similar_Posts_Ontology_Widget.php');


/**
 * Get all posts related to the specified post ID
 * 
 * @param int $ID the post ID of the post for which you want to find similar content
 *
 * @retun array of posts
 */
function pk_get_similar_posts($ID, $args) {
    global $wpdb,$table_prefix;
	$ID = intval($ID);
	$limit = (isset($args['posts_per_page'])) ? intval($args['posts_per_page']) : 5;

	if ($ID == 0) {
		return null;
	}

	$sql = "SELECT ".$table_prefix."posts.*, COUNT(`".$table_prefix."posts`.`post_name`) AS `pk_connections` 
		FROM ".$table_prefix."posts 
		INNER JOIN `".$table_prefix."term_relationships` AS `pk_term_rel` ON (`pk_term_rel`.`object_id` = `".$table_prefix."posts`.`ID`) 
		INNER JOIN `".$table_prefix."term_taxonomy` AS `pk_term_tax` ON (`pk_term_tax`.`term_taxonomy_id` = `pk_term_rel`.`term_taxonomy_id`) 
		WHERE 1=1 
		AND ".$table_prefix."posts.post_type = 'post' 
		AND (".$table_prefix."posts.post_status = 'publish' OR ".$table_prefix."posts.post_status = 'private') 
		AND `pk_term_rel`.`term_taxonomy_id` IN 
			( 
				SELECT `term_rel`.`term_taxonomy_id` FROM `".$table_prefix."term_relationships` AS `term_rel` WHERE `term_rel`.`object_id` = ".$ID."  
			) 
		AND `".$table_prefix."posts`.`ID` != ".$ID." 
		GROUP BY `".$table_prefix."posts`.`post_name` 
		ORDER BY `pk_connections` DESC, post_date DESC 
		LIMIT 0, ".$limit;
	$results = $wpdb->get_results($sql);

	foreach($results as $r=>$v) {
		// remove this as it's not needed
		unset($v->pk_connections);

		// attach the thumbnail URL to each post
		$thumbnail_size = (isset($args['thumbnail_size']) && in_array($args['thumbnail_size'], array('thumbnail','medium','large','full'))) ? $args['thumbnail_size'] : 'thumbnail';
		$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($v->ID), $thumbnail_size);
		if ($thumbnail && isset($thumbnail[0]) && is_string($thumbnail[0])) {
			$v->thumbnail = $thumbnail[0];
		}

		// attach the permalink
		$permalink = get_permalink($v->ID);
		if ($permalink) {
			$v->permalink = $permalink;
		}
	}
	return $results;
}


/**
 * Assemble HTML for widget
 * 
 * @param int $ID the post ID of the post for which you want to find similar content
 *
 * @retun string of HTML
 */
function pk_show_similar_posts($ID, $args) {
	$results = pk_get_similar_posts($ID, $args);
	$html = '<aside class="widget widget_archive">';

	if (isset($args['title'])) {
		$html .= '<h2 class="widget-title">'.$args['title'].'</h2>';
	}

	$html .= '<ul class="similar-posts">';
	foreach($results as $r=>$v) {
		// start tag and title
		$html .= '<li>
			<div>
			<span class="similar-title"><a href="'.$v->permalink.'">'.$v->post_title.'</a></span>
			';
		
		// If time, show time.
		if (isset($args['include_fields_post_date']) && $args['include_fields_post_date'] == 'true') {
			$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	
			if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
				$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
			}
	
			$time_string = sprintf( $time_string,
				esc_attr( get_the_date( 'c' ) ),
				get_the_date(),
				esc_attr( get_the_modified_date( 'c' ) ),
				get_the_modified_date()
			);

			$html .= sprintf( '<span class="posted-on">%1$s</span>',
				get_the_date(get_option('date_format'), $v->ID )
			);
		}
		
		// If author, show author name
		if (isset($args['include_fields_author_name']) && $args['include_fields_author_name'] == 'true') {
			$author = get_the_author_meta('nickname', $v->ID);
			$separator = (isset($args['include_fields_post_date']) && $args['include_fields_post_date'] == 'true') ? ' | ' : '';
			$author = get_the_author_meta('display_name', $v->post_author);
			$html .= sprintf( '<span class="byline"> %1$s <span class="author vcard">Author: <a class="url fn n" href="%2$s">%3$s</a></span></span>',
				$separator,
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				$author
			);
		}

		// If thumbnail, show thumbnail
		if ($v->thumbnail && isset($args['include_fields_thumbnail']) && $args['include_fields_thumbnail'] == 'true') {
			$html .= '<a href="'.$v->permalink.'"><img src="'.$v->thumbnail.'" alt="'.htmlentities($v->post_title,ENT_QUOTES).'" /></a>';
		}

		// If excerpt, show excerpt
		if (isset($args['include_fields_excerpt']) && $args['include_fields_excerpt'] == 'true') {
			$excerpt = (isset($v->post_excerpt)) ? $v->post_excerpt : '';
			if ($excerpt == '') {
				$excerpt = strip_tags($v->post_content);
				$excerpt_length = apply_filters('excerpt_length', 55);
				$excerpt = substr($excerpt, 0, $excerpt_length);
			}
			$html .= '<p>'.$excerpt.'</p>';
		}
		$html .= '</div></li>';
	}
	$html .= '</ul></aside>';

	return $html;
}




/**
 * Assemble HTML for widget
 * 
 * @param int $ID the post ID of the post for which you want to find similar content
 * @param array $args containing parameters for use
 *		int posts_per_page
 *
 * @retun array return an array with a list of objects containing objects of each post type.
 */
function pk_related_return($ID, $args) {
	$defaults = Similar_Posts_Ontology_Widget::get_defaults();
	foreach($defaults as $k=>$v) {
		if (!isset($args[$k])) {
			$args[$k] = $v;
		}
	}
	return pk_get_similar_posts($ID, $args);
}




add_action( 'widgets_init', function() {
     register_widget( 'Similar_Posts_Ontology_Widget' );
});

