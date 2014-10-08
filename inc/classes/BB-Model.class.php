<?php

/*
 * Wordpress Model Class
**/
class WP_Model {

	protected $user;

	public function __construct() {

		global $current_user;
		get_currentuserinfo();
		$this->user = $current_user;

	}

	/*
	 * Global Getter-function.
	 *
	 * @return function
	**/
	public function get() {

		$args = func_get_args();
		$method = array_shift($args);

		return call_user_func_array(array($this, '__get_' . $method), $args);

	}

	/*
	 * Header info
	 *
	 * @return array
	**/
	protected function __get_site_header() {

		return array(
			'sitename' => get_bloginfo('name'),
			'description' => get_bloginfo('description'),
			'home' => get_bloginfo('url')
		);

	}

	/*
	 * Main navigation
	 *
	 * @return array
	**/
	protected function __get_main_nav() {

		$pages = get_pages('parent=0');
		$page_array = array();
		$count = 0;

		if ( empty($pages) ) {
			return $page_array;
		}

		foreach( $pages as $page ) {

			array_push($page_array, array(
				'title' => $page->post_title,
				'slug' => get_permalink($page->ID)
			));

			if ( is_page($page->ID) ) {
				$page_array[$count]['current'] = true;
			}

			$count++;

		}

		return $page_array;

	}

	/*
	 * Category navigation
	 *
	 * @return array
	**/
	protected function __get_categories() {

		$categories = get_categories('hierarchical=0');
		$category_array = array();

		if ( empty($categories) ) {
			return $category_array;
		}

		foreach( $categories as $category ) {

			$category_array[] = array(
				'title' => $category->name,
				'slug' => '/category/' . $category->slug . '/',
				'cat_id' => $category->term_id,
				'count' => $category->count
			);

		}

		return $category_array;

	}

	/*
	 * Archive navigation
	 *
	 * @return array
	**/
	protected function __get_archives() {

		$archive = wp_get_archives('format=custom&echo=0&show_post_count=1&before=&after=');
		$archive = str_replace('&nbsp;', " ", $archive);
		$archive = explode("\n", $archive);
		$archive_array = array();

		array_splice($archive, -1);

		if ( empty($archive) ) {
			return $archive_array;
		}

		foreach( $archive as $archive_item ) {

			preg_match('/\'([^\']*)\'>([^<]*)[^(]*\((\d*)/i', $archive_item, $archive_data);

			if ( !$archive_data || count($archive_data) < 4 ) {
				continue;
			}

			$archive_array[] = array(
				'slug' => $archive_data[1],
				'title' => $archive_data[2],
				'count' => $archive_data[3]
			);
		}

		return $archive_array;

	}

	/*
	 * The site footer
	 *
	 * @return array
	**/
	protected function __get_footer() {

		return array(
			'year' => date('Y'),
			'title' => get_bloginfo('title')
		);

	}

	/*
	 * Total post count
	 *
	 * @return number
	**/
	protected function __get_post_count() {

		$count_posts = wp_count_posts();

		return $count_posts->publish;

	}

	/*
	 * Get name of currently logged in user
	**/
	protected function __get_username() {

		return $this->user->display_name;

	}

	/*
	 * The comment form
	 *
	 * @param  number [$id] Post-/Page-ID
	 * @return array
	**/
	protected function __get_commentform($id = null) {

		$ret = array();

		// Comments are closed,
		// do not show the form
		if ( !comments_open() ) {
			return $ret;
		}

		if ( !$id ) {
			return $ret;
		}

		$permalink = get_permalink($id);

		if ( $this->get('username') ) {
			$ret['user_name'] = $this->get('username');
		}
		$ret['logout_url'] = wp_logout_url($permalink);
		$ret['post_id'] = $id;
		$ret['post_url'] = $permalink;

		return $ret;

	}

	/*
	 * The current Loop
	 *
	 * @return array
	**/
	protected function __get_loop() {

		$paged = get_query_var('paged')
			? get_query_var('paged')
			: 1;
		$catId = get_query_var('cat');
		$archiveDate = strtotime(trim(single_month_title('-', false), '-'));

		$query = 'numberposts=10&paged=' . $paged;

		if ( $catId ) {
			$query .= '&cat=' . $catId;
		}

		if ( $archiveDate ) {
			$query .= '&year=' . date('Y', $archiveDate);
			$query .= '&monthnum=' . date('m', $archiveDate);
		}

		$posts = query_posts($query);
		$result = array();

		foreach ( $posts as &$post ) {

			$result[] = array(
				'ID' => $post->ID,
				'post_title' => $post->post_title,
				'permalink' => get_permalink($post->ID),
				'post_content' => apply_filters('the_content', wpautop($post->post_content)),
				'nice_date' => apply_filters('the_date', strtotime($post->post_date)),
				'comment_count' => $post->comment_count
			);

		}

		return $result;

	}

	/*
	 * Current Post or by specified ID
	 *
	 * @param  number [$id] Post-ID
	 * @return array
	**/
	protected function __get_post($id = null) {

		global $post;

		if ( $id ) {
			$post = query_posts('p=' . $id);
		}

		return array(
			'ID' => $post->ID,
			'post_title' => $post->post_title,
			'post_content' => apply_filters('the_content', wpautop($post->post_content)),
			'nice_date' => apply_filters('the_date', strtotime($post->post_date)),
			'post_author' => $post->post_author
		);

	}

	/*
	 * Comments for the current post or for a post specified by ID
	 *
	 * @param  number [$id] Post-ID
	 * @return array
	**/
	protected function __get_comments($id = null) {

		global $post;

		if ( !$id ) {
			$id = $post->ID;
		}

		$comments = get_comments('post_id=' . $id);
		$result = array();

		foreach ( $comments as $comment ) {
			$result[] = array(
				'comment_ID' => $comment->comment_ID,
				'nice_date' => apply_filters('the_date', strtotime($comment->comment_date)),
				'comment_author' => $comment->comment_author,
				'comment_author_url' => $comment->comment_author_url == 'http://'
					? ''
					: $comment->comment_author_url,
				'comment_content' => wpautop($comment->comment_content)
			);
		}

		return array_reverse($result);

	}

	/*
	 * Categories for the current post or for a post specified by ID
	 *
	 * @param  number [$id] Post-ID
	 * @return array
	**/
	protected function __get_post_cats($id = null) {

		global $post;

		if ( !$id ) {
			$id = $post->ID;
		}

		$categories = array();

		foreach ( wp_get_object_terms($id, 'category') as $category ) {
			$categories[] = array(
				'term_id' => $category->term_id,
				'slug' => $category->slug,
				'name' => $category->name
			);
		}

		return $categories;

	}

	/*
	 * Current page or by specified ID
	 *
	 * @param  number [$id] Page-ID
	 * @return array
	**/
	protected function __get_page($id = null) {

		global $post;

		if ( $id ) {
			$post = query_posts('page_id=' . (int) $id);
		}

		$post->post_content = wpautop($post->post_content);
		$post->nice_date = apply_filters('the_date', strtotime($post->post_date));

		return array(
			'ID' => $post->ID,
			'post_title' => $post->post_title,
			'post_content' => apply_filters('the_content', wpautop($post->post_content)),
			'nice_date' => apply_filters('the_date', strtotime($post->post_date)),
			'post_author' => $post->post_author
		);

	}

	/*
	 * Get page/post author
	 *
	 * @param  number [$id] Post-/Page-ID
	 * @return array
	**/
	protected function __get_author($id = null) {

		if ( is_null($id) ) {
			return array();
		}

		return array(
			'display_name' => get_the_author_meta('display_name', (int) $id),
			'first_name' => get_the_author_meta('first_name', (int) $id),
			'last_name' => get_the_author_meta('last_name', (int) $id)
		);

	}

	/*
	 * Content of the 404-page
	 *
	 * @return array
	**/
	protected function __get_404() {

		return array(
			'title' => 'Error 404',
			'message' => 'The page you\'re looking for doesn\'t exist.'
		);

	}

}