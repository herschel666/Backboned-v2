<?php

/*
 * Wordpress Model Class
**/
class WP_Model {

	private $user;

	function __construct() {

		global $current_user;
		get_currentuserinfo();
		$this->user = $current_user;

	}

	/*
	 * Auto-<p> the passed content.
	**/
	private function autop(&$content, $key, $eference) {

		if ( $key != reference ) {
			return;
		}

		$content->{$reference} = wpautop($content->{$reference});

	}

	/*
	 * Global Getter-function.
	**/
	public function get() {

		$args = func_get_args();
		$method = array_shift($args);

		return call_user_func_array(array($this, '__get_' . $method), $args);

	}

	/*
	 * Header info
	**/
	private function __get_site_header() {

		return array(
			'sitename' => get_bloginfo('name'),
			'description' => get_bloginfo('description'),
			'home' => get_bloginfo('home')
		);

	}

	/*
	 * Main navigation
	**/
	private function __get_main_nav() {

		$pages = get_pages('parent=0');
		$page_array = array();
		$count = 0;

		if ( !empty($pages) ) {

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

		}

		return $page_array;

	}

	/*
	 * Category navigation
	**/
	private function __get_categories($jsObj = false) {

		$categories = get_categories('hierarchical=0');
		$category_array = array();

		if ( !empty($categories) ) {

			foreach( $categories as $category ) {

				array_push($category_array, array(
					'title' => $category->name,
					'slug' => '/' . $category->slug . '/',
					'cat_id' => $category->term_id,
					'count' => $category->count
				));

			}

		}

		return $category_array;

	}

	/*
	 * Archive navigation
	**/
	private function __get_archives() {

		$archive = wp_get_archives('format=custom&echo=0&show_post_count=1&before=&after=');
		$archive = str_replace('&nbsp;', " ", $archive);
		$archive = explode("\n", $archive);
		$archive_array = array();

		array_splice($archive, -1);

		if ( !empty($archive) ) {

			foreach( $archive as $archive_item ) {

				preg_match('/href=[\'"]?([^\'" >]+)/', $archive_item, $slug);
				preg_match('/title=[\'"]?([^\'">]+)/', $archive_item, $title);
				preg_match('/\((\d+)\)/', $archive_item, $count);

				array_push($archive_array, array(
					'title' => $title[1],
					'slug' => $slug[1],
					'count' => $count[1]
				));
			}
		}

		return $archive_array;

	}

	/*
	 * Bookmarks navigation
	**/
	private function __get_bookmarks() {

		$bookmarks = get_bookmarks();
		$bookmark_array = array();

		if ( !empty($bookmarks) ) {

			foreach( $bookmarks as $bookmark ) {

				array_push($bookmark_array, array(
					'title' => $bookmark->link_name,
					'slug' => $bookmark->link_url
				));

			}

		}

		return $bookmark_array;

	}

	/*
	 * The site footer
	**/
	private function __get_footer() {

		return array(
			'year' => date('Y'),
			'title' => get_bloginfo('title')
		);

	}

	/*
	 * Total post count
	**/
	private function __get_post_count() {

		$count_posts = wp_count_posts();

		return $count_posts->publish;

	}

	/*
	 * Get name of currently logged in user
	**/
	private function __get_username() {

		return $this->user->display_name;

	}

	/*
	 * The comment form
	**/
	private function __get_commentform($id = null) {

		$ret = array();

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
	**/
	private function __get_loop() {

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

			array_walk($post, array('WP_Model', 'autop'), 'post_content');

			$result[] = array(
				'ID' => $post->ID,
				'post_title' => $post->post_title,
				'permalink' => get_permalink($post->ID),
				'post_content' => $post->post_content,
				'nice_date' => date(get_option('date_format'), strtotime($post->post_date)),
				'comment_count' => $post->comment_count
			);

		}

		return $result;

	}

	/*
	 * Current Post or by specified ID
	**/
	private function __get_post($id = null) {

		global $post;

		if ( $id ) {
			$post = query_posts('p=' . $id);
		}

		return array(
			'ID' => $post->ID,
			'post_title' => $post->post_title,
			'post_content' => wpautop($post->post_content),
			'nice_date' => date(get_option('date_format'), strtotime($post->post_date)),
			'post_author' => $post->post_author
		);

	}

	/*
	 * Comments for the current post or for a post specified by ID
	**/
	private function __get_comments($id = null) {

		global $post;

		if ( !$id ) {
			$id = $post->ID;
		}

		$comments = get_comments('post_id=' . $id);
		$result = array();

		foreach ( $comments as &$comment ) {
			array_walk($comment, array('WP_Model', 'autop'), 'comment_content');
			$result[] = array(
				'comment_ID' => $comment->comment_ID,
				'nice_date' => date(get_option('date_format'), strtotime($comment->comment_date)),
				'comment_author' => $comment->comment_author,
				'comment_author_url' => $comment->comment_author_url == 'http://'
					? ''
					: $comment->comment_author_url,
				'comment_content' => $comment->comment_content
			);
		}

		return array_reverse($result);

	}

	/*
	 * Categories for the current post or for a post specified by ID
	**/
	private function __get_post_cats($id = null) {

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
	**/
	private function __get_page($id = null) {

		global $post;

		if ( $id ) {
			$post = query_posts('page_id=' . $id);
		}

		$post->post_content = wpautop($post->post_content);
		$post->nice_date = date(get_option('date_format'), strtotime($post->post_date));

		return array(
			'ID' => $post->ID,
			'post_title' => $post->post_title,
			'post_content' => wpautop($post->post_content),
			'nice_date' => date(get_option('date_format'), strtotime($post->post_date)),
			'post_author' => $post->post_author
		);

	}

	/*
	 * Get page/post author
	**/
	private function __get_author($id = null) {

		return array(
			'display_name' => get_the_author_meta('display_name', $id),
			'first_name' => get_the_author_meta('first_name', $id),
			'last_name' => get_the_author_meta('last_name', $id)
		);

	}

	/*
	 * Content of the 404-page
	**/
	private function __get_404() {

		return array(
			'title' => 'Error 404',
			'message' => 'The page you\'re looking for doesn\'t exist.'
		);

	}

}