<?php

$inc = dirname(dirname(__FILE__));

require_once('BB-Model.class.php');
require_once($inc . '/libs/Mustache/Autoloader.php');
Mustache_Autoloader::register();

/*
 * Backboned Controller
**/
class Backboned {

	/**
	 * @var string
	 */
	protected $root;

	/**
	 * @var WP_Model
	 */
	protected $wp_model;

	public function __construct() {

		$this->root = dirname(dirname(dirname(__FILE__)));
		$this->wp_model = new WP_Model();

		$this->mustache = new Mustache_Engine(array(
			'cache' => $this->root . '/cache/mustache',
			'cache_file_mode' => 0666,
			'loader' => new Mustache_Loader_FilesystemLoader($this->root . '/views'),
			'partials_loader' => new Mustache_Loader_FilesystemLoader($this->root . '/views/partials'),
			'escape' => function($value) {
				return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
			},
			'charset' => 'utf-8'
		));

	}

	/*
	 * Global Getter-function.
	 *
	 * @param *
	 * @return function
	**/
	public function get() {

		$args = func_get_args();
		$method = array_shift($args);

		return call_user_func_array(array($this, '__get_' . $method), $args);

	}

	/*
	 * Check, wether it's a normal request, an async
	 * request or an request by a search engine crawler.
	 *
	 * @return string
	**/
	public function request_type() {

		if ( isset($_GET['_escaped_fragment_']) ) {
			return 'search_engine';
		}

		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
			return 'async';
		}

		return 'standard';

	}

	/**
	 * Gets the content by type
	 *
	 * @param  string [$type]
	 * @return function
	 */
	public function content($type = 'loop') {

		if ( is_404() ) {
			return $this->__generate_404();
		}

		return $this->{'__generate_' . $type}();

	}

	/*
	 * Getting the site-frame-data
	 *
	 * @return array
	**/
	protected function __get_frame() {

		$content = array_merge(
			$this->wp_model->get('footer'),
			$this->wp_model->get('site_header')
		);

		$content['nav-items'] = $this->wp_model->get('main_nav');
		$content['categories'] = $this->wp_model->get('categories');
		$content['archives'] = $this->wp_model->get('archives');

		return $content;

	}

	/*
	 * Loop pagination
	 *
	 * @return array
	**/
	protected function __get_pagelinks() {

		$paged = get_query_var('paged')
			? get_query_var('paged')
			: 1;
		$max = ceil($this->wp_model->get('post_count') / get_option('posts_per_page'));
		$pagelinks = array(
			'prev_link' => null,
			'next_link' => null
		);

		if ( $paged > 1 ) {
			preg_match('/href=[\'"]?([^\'" >]+)/', get_previous_posts_link(''), $slug);
			$prev_link = preg_replace('/(\??_escaped_fragment_)/', '', $slug[1]);
			$pagelinks['prev_link'] = $prev_link;
		}

		if ( $paged < $max ) {
			preg_match('/href=[\'"]?([^\'" >]+)/', get_next_posts_link(''), $slug);
			$next_link = preg_replace('/(\??_escaped_fragment_)/', '', $slug[1]);
			$pagelinks['next_link'] = $next_link;
		}

		return $pagelinks;

	}

	/*
	 * The Error page
	**/
	protected function __generate_404() {

		$request_type = $this->request_type();

		if ( $request_type == 'standard' ) {
			echo file_get_contents($this->root . '/views/layout.mustache');
			return;
		}

		$error = $this->wp_model->get('404');

		if ( $request_type == 'async' ) {

			header('Content-type: application/json');
			echo json_encode(array(
				'type' => 'error',
				'content' => $error
			));
			exit;

		}

		$content = array_merge($this->get('frame'), $error);

		$layout = $this->mustache->loadTemplate('404');
		echo $layout->render($content);

	}

	/*
	 * Output for the loop due to request type
	**/
	protected function __generate_loop() {

		$request_type = $this->request_type();

		if ( $request_type == 'standard' ) {
			echo file_get_contents($this->root . '/views/layout.mustache');
			return;
		}

		$posts = $this->wp_model->get('loop');
		$pagelinks = $this->get('pagelinks');

		if ( $request_type == 'async' ) {

			header('Content-type: application/json');
			echo json_encode(array(
				'type' => 'loop',
				'content' => $posts,
				'pagelinks' => $pagelinks
			));
			exit;

		}

		$content = array_merge($this->get('frame'), $pagelinks);
		$content['posts'] = $posts;

		$layout = $this->mustache->loadTemplate('loop');
		echo $layout->render($content);

	}

	/*
	 * Generating the content for a single post.
	**/
	protected function __generate_post() {

		$request_type = $this->request_type();

		if ( $request_type == 'standard' ) {
			echo file_get_contents($this->root . '/views/layout.mustache');
			return;
		}

		$post = $this->wp_model->get('post');
		$cats = $this->wp_model->get('post_cats', $post['ID']);
		$comments = $this->wp_model->get('comments');
		$content = array_merge($post, array(
			'post_cats' => $cats,
			'author' => $this->wp_model->get('author', $post['post_author'])
		));

		if ( $request_type == 'async' ) {

			header('Content-type: application/json');
			echo json_encode(array(
				'type' => 'single',
				'content' => $content,
				'comments' => $comments,
				'commentform' => $this->wp_model->get('commentform', $post['ID'])
			));
			exit;

		}

		$content = array_merge($content, $this->get('frame'));
		$content['comments'] = $comments;

		$layout = $this->mustache->loadTemplate('single');
		echo $layout->render($content);

	}

	/*
	 * Generating the content for a static page.
	**/
	protected function __generate_page() {

		$request_type = $this->request_type();

		if ( $request_type == 'standard' ) {
			echo file_get_contents($this->root . '/views/layout.mustache');
			return;
		}

		$page = $this->wp_model->get('page');
		$comments = $this->wp_model->get('comments');
		$commentform = $this->wp_model->get('commentform', $page['ID']);
		$content = array_merge($page, array(
			'author' => $this->wp_model->get('author', $page['post_author'])
		));

		if ( $request_type == 'async' ) {

			header('Content-type: application/json');
			echo json_encode(array(
				'type' => 'page',
				'content' => $content,
				'comments' => $comments,
				'commentform' => $commentform
			));
			exit;

		}

		$content = array_merge($content, $this->get('frame'), $commentform);
		$content['comments'] = $comments;

		$layout = $this->mustache->loadTemplate('page');
		echo $layout->render($content);

	}

	/*
	 * Create a JS-Object with all relevant data
	 *
	 * @return array
	**/
	protected function __get_js_variables() {

		$variables = array(
			'dev_mode' => $_SERVER['HTTP_HOST'] === 'wp.dev',
			'base_url' => get_option('home'),
			'template_url' => get_bloginfo('template_url'),
			'logged_in' => is_user_logged_in(),
			'site_header' => $this->wp_model->get('site_header'),
			'main_nav' => $this->wp_model->get('main_nav'),
			'aside' => array(
				'categories' => $this->wp_model->get('categories'),
				'archives' => $this->wp_model->get('archives')
			),
			'footer' => $this->wp_model->get('footer'),
			'post_count' => $this->wp_model->get('post_count')
		);

		return $variables;

	}

	/*
	 * Create the JS-script-embedding
	 *
	 * @return string
	**/
	protected function __get_js_scripts() {

		$str = '<script src="' . get_bloginfo('template_url');
		$str .= '/assets/scripts/vendor/require-2.1.5.min.js"';
		$str .= ' data-main="' . get_bloginfo('template_url');
		$str .= '/assets/scripts/config"';
		$str .= '></script>';

		return $str;

	}

	/*
	 * Create Template-Strings for the DOM
	 *
	 * @return string
	**/
	protected function __get_partials() {

		$templates = array(
			'loop' => 'posts',
			'page' => 'page',
			'single' => 'post',
			'error' => 'error',
			'comments' => 'comments',
			'commentform' => 'commentform',
			'page-links' => 'page-links',
			'header' => 'main-header',
			'navigation' => 'main-nav',
			'aside' => 'main-aside',
			'footer' => 'main-footer'
		);
		$str = '';

		foreach ( $templates as $id => $path ) {
			$str .= '<script type="text/x-template" id="' . $id . '-tmpl">';
			$str .= str_replace(array("\n", "\t", "\r"), '', file_get_contents($this->root . '/views/partials/' . $path . '.mustache'));
			$str .= '</script>';
		}

		return $str;

	}

}