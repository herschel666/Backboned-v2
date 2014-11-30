<?php

/*
 * Wordpress Model Class
**/
class WP_Model {


	protected $user;

	protected $supported_errors = array();

	public function __construct() {

		global $current_user;

		get_currentuserinfo();

		$this->_init_error_messages();

		$this->user = $current_user;

	}

	public function date_format($unixtimestamp) {
    return strftime(get_option('date_format'), $unixtimestamp);
	}

	/**
	 * Initialize error messages
	 *
	 * Maybe we would want this to be internationalized too.
	 *
	 * @return null
	 */
	protected function _init_error_messages() {
		$errors[404]['title'] = '404 Not Found';
		$errors[404]['message'] = 'The page you’re looking for doesn’t exist.';
		$errors[403]['title'] = '403 Forbidden';
		$errors[403]['message'] = 'You don’t have authorized access to view this document.';

		$this->supported_errors = $errors;

		return null;
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

		// See also those related functions
		// to get menus. They had useful notes
		// on how WP handle sorting.
		//
		//   - wp_nav_menu()
		//   - wp_get_nav_menu_items()
		//   - get_nav_menu_locations()
		//   - wp_get_nav_menu_object()

		// Assuming we have ONLY ONE menu. Otherwise it breaks.
		$menus = wp_get_nav_menus();
		$menu = $menus[0];
		$menu_items = wp_get_nav_menu_items( $menu->term_id );

	  // ==== /Copy-pasting code is bad, m-kay... =====
		// This is only to sort menu like WordPress is doing it.
		// Sorry about that.
		// See around line 336 of wp-inclucdes/nav-menu-template.php
		$sorted_menu_items = $menu_items_with_children = array();
		foreach ( (array) $menu_items as $menu_item ) {
			$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
			if ( $menu_item->menu_item_parent )
				$menu_items_with_children[ $menu_item->menu_item_parent ] = true;
		}
		if ( $menu_items_with_children ) {
			foreach ( $sorted_menu_items as &$menu_item ) {
				if ( isset( $menu_items_with_children[ $menu_item->ID ] ) )
					$menu_item->classes[] = 'menu-item-has-children';
			}
		}
	  // ==== /Copy-pasting code is bad, m-kay... =====

		//var_dump($sorted_menu_items);

		$out = array();


		foreach($sorted_menu_items as $m) {
			$e = array();
			//$e['tmp'] = $m;
			if(is_category($m->object_id)) {
				$e['current'] = true;
			}
			$e['title'] = __($m->title);
			$e['slug'] = $m->url;
			$e['id'] = $m->ID;
			$out[] = $e;
		}
		//var_dump($out);

		return $out;


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

		// So theme can work with Xili and multilingual site
		if(class_exists('xili_language')) {
			$query .= '&'.QUETAG.'='.xili_curlang();
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
				'slug' => get_option( 'category_base' ) . '/category/'. $category->slug,
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

	/**
	 * Content of the error view
	 *
	 * @return array
   **/
	protected function __get_error($code) {
		return $this->supported_errors[$code];
	}

	/**
	 * Get list of supported errors messages
	 *
	 * @return array of HTTP status codes numbers
	 */
	public function get_supported_errors() {
		return array_keys($this->supported_errors);
	}

}
