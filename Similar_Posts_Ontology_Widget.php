<?php
class Similar_Posts_Ontology_Widget extends WP_Widget {

	private static $instance = null;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'similar_posts_ontology_widget', // Base ID
			__( 'Similar Posts Ontology', 'similarpostsontology' ), // Name
			array( 'description' => __( 'Similar Posts Widget', 'similarpostsontology' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if (is_single()) {
			if (function_exists('pk_show_similar_posts')) {
				global $wp_query;
				$post_obj = $wp_query->get_queried_object();
				$post = $post_obj->ID;
				$pk_similar_posts = Similar_Posts_Ontology_Widget::get_instance();
				$defaults = $pk_similar_posts->get_defaults();
				foreach($defaults as $k=>$v) {
					if (!isset($instance[$k])) {
						$instance[$k] = $v;
					}
				}
				echo pk_show_similar_posts($post, $instance);
			}
		}
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$pk_similar_posts = Similar_Posts_Ontology_Widget::get_instance();
		$defaults = $pk_similar_posts->get_defaults();
		foreach($defaults as $k=>$v) {
			if (!isset($instance[$k])) {
				$instance[$k] = $v;
			}
		}
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Similar Posts', 'similarpostsontology' );
		$posts_per_page = ! empty( $instance['posts_per_page'] ) ? $instance['posts_per_page'] : __( '5', 'similarpostsontology' );
		$include_fields_thumbnail 	= ( $instance['include_fields_thumbnail'] == 'true') ? ' checked="checked"' : '';
		$include_fields_post_date 	= (! empty( $instance['include_fields_post_date'] ) && $instance['include_fields_post_date'] == 'true')			? ' checked="checked"' : '';
		$include_fields_author_name	= (! empty( $instance['include_fields_author_name'] ) && $instance['include_fields_author_name'] == 'true')		? ' checked="checked"' : '';
		$include_fields_excerpt		= (! empty( $instance['include_fields_excerpt'] ) && $instance['include_fields_excerpt'] == 'true') 			? ' checked="checked"' : '';
		$thumbnail_size				= (! empty( $instance['thumbnail_size'] ) && in_array($instance['thumbnail_size'], array('thumbnail','medium','large','full')))	? $instance['thumbnail_size'] : 'thumbnail';
		$sort_prefer				= (! empty( $instance['sort_prefer'] ) && $instance['sort_prefer'] == 'closest') 			? 'closest' : 'newer';
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e( 'Number of Posts:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'posts_per_page' ); ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ); ?>" type="text" value="<?php echo esc_attr( $posts_per_page ); ?>">
		</p>
		<p>
		Include the following fields:<br />
		<label><input id="<?php echo $this->get_field_id('include_fields_thumbnail'); ?>" name="<?php echo $this->get_field_name( 'include_fields_thumbnail' ); ?>" type="checkbox" value="true" <?php echo $include_fields_thumbnail; ?> /> Featured Image</label><br />
		<label><input id="<?php echo $this->get_field_id('include_fields_post_date'); ?>" name="<?php echo $this->get_field_name( 'include_fields_post_date' ); ?>" type="checkbox" value="true" <?php echo $include_fields_post_date; ?> /> Post Date</label><br />
		<label><input id="<?php echo $this->get_field_id('include_fields_author_name'); ?>" name="<?php echo $this->get_field_name( 'include_fields_author_name' ); ?>" type="checkbox" value="true" <?php echo $include_fields_author_name; ?> /> Author's Name</label><br />
		<label><input id="<?php echo $this->get_field_id('include_fields_excerpt'); ?>" name="<?php echo $this->get_field_name( 'include_fields_excerpt' ); ?>" type="checkbox" value="true" <?php echo $include_fields_excerpt; ?> /> Excerpt</label><br />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('sort_prefer'); ?>"><?php _e( 'Prefer posts that are:' ); ?></label>
		<select id="<?php echo $this->get_field_id('sort_prefer'); ?>" name="<?php echo $this->get_field_name( 'sort_prefer' ); ?>">
			<option value="newer" <?php if ($sort_prefer == '' || $sort_prefer == 'newest') { ?>selected="selected"<?php } ?>>Newer</option>
			<option value="closest" <?php if ($sort_prefer == 'closest') { ?>selected="selected"<?php } ?>>Closer in date</option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('thumbnail_size'); ?>"><?php _e( 'Image Size' ); ?></label>
		<select id="<?php echo $this->get_field_id('thumbnail_size'); ?>" name="<?php echo $this->get_field_name( 'thumbnail_size' ); ?>">
			<option value="thumbnail" <?php if ($thumbnail_size == 'thumbnail') { ?>selected="selected"<?php } ?>>Thumbnail</option>
			<option value="medium" <?php if ($thumbnail_size == 'medium') { ?>selected="selected"<?php } ?>>Medium</option>
			<option value="large" <?php if ($thumbnail_size == 'large') { ?>selected="selected"<?php } ?>>Large</option>
			<option value="full" <?php if ($thumbnail_size == 'full') { ?>selected="selected"<?php } ?>>Full</option>
		</select>
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']						= ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['posts_per_page']				= ( ! empty( $new_instance['posts_per_page'] ) ) ? intval( $new_instance['posts_per_page'] ) : '5';
		$instance['include_fields_thumbnail']	= ( $new_instance['include_fields_thumbnail'] == 'true' ) ? 'true' : 'false';
		$instance['include_fields_post_date']	= ( $new_instance['include_fields_post_date'] == 'true' ) ? 'true' : 'false';
		$instance['include_fields_author_name']	= ( $new_instance['include_fields_author_name'] == 'true' ) ? 'true' : 'false';
		$instance['include_fields_excerpt']		= ( $new_instance['include_fields_excerpt'] == 'true' ) ? 'true' : 'false';
		$instance['sort_prefer']				= ( $new_instance['sort_prefer'] == 'closest' ) ? 'closest' : 'newest';
		$instance['thumbnail_size']				= ( in_array($new_instance['thumbnail_size'], array('thumbnail','medium','large','full')) ) ? $new_instance['thumbnail_size'] : 'thumbnail';

		return $instance;
	}
	
	/**
	 * Plugin default query parameters
	 * 
	 * @access public
	 * @return array The default options for the widget
	 */
	public function get_defaults() {
		return array(
			'title' => '',
			'posts_per_page' => 5,
			'include_fields_thumbnail' => 'true',
			'include_fields_post_date' => 'false',
			'include_fields_author_name' => 'false',
			'include_fields_excerpt' => 'false',
			'sort_prefer' => 'newest',
			'thumbnail_size' => 'thumbnail'
		);
	}

	/**
	 * Provides access to the class' instance
	 * 
	 * @access public
	 * @return PK_Similar_Posts
	 */
	public static function get_instance() {
		if (!self::$instance instanceof self) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

}