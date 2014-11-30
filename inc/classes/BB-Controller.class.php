<?php

$inc = dirname(dirname(__FILE__));

require_once('BB-Model.class.php');
require_once($inc . '/libs/Mustache/Autoloader.php');
Mustache_Autoloader::register();

/*
 * Backboned Controller
**/
class BackbonedController {

	/**
	 * @var string
	 */
	protected $root;

	/**
	 * @var WP_Model
	 */
	protected $wp_model;

	/**
	 * BackbonedController constructor
	 *
	 * There should be only one instance of this object. The
	 * constructor takes care to add filters it needs to generate the
	 * pages and handle the Request Response cycle.
	 *
	 * Ideally, we should refactor toward Symfony2 http_kernel and specialize
	 * exclusively for the theme.
	 *
	 * http://symfony.com/doc/current/components/http_kernel/introduction.html
	 *
	 * @param [type] $WP_Instance [description]
	 */
	public function __construct(&$WP_Instance) {

		if ( isset($GLOBALS['bb_controller']) && $GLOBALS['bb_controller'] instanceof BackbonedController ) {
			return $GLOBALS['bb_controller'];
		}

		// Note: The X-UA-Compatible HTTP header is specific to IE.
		//       In terms of markup recommendations, it is said better to
		//       send an HTTP header than using a meta http-equiv tag.
		//       http://www.validatethis.co.uk/news/fix-bad-value-x-ua-compatible-once-and-for-all/

		if ( $this->request_type() === 'standard' && get_option('html_type') === 'text/html' ) {
			header('X-UA-Compatible: IE=edge,chrome=1');
		}

		header('X-Request-type: '.$this->request_type());

		// We can support child theme views folder
		$this->root = get_stylesheet_directory();

		$this->wp_model = new WP_Model();

		$this->_initMustache();

		add_filter( 'body_class', array( $this, 'body_class' ) );

		add_filter( 'the_date', array( $this->wp_model, 'date_format' ) );

		add_action( 'init', array( $this, 'init_deregister_unused_scripts' ), 9 );

		add_action( 'wp_head', array( $this, 'wp_head_cleanup' ), 9 );

		add_action( 'wp_footer', array( $this, 'wp_footer_scripts_echo' ), 5 );

		add_action( 'wp_footer', array( $this, 'wp_footer_partials_echo'), 5 );

		add_action( 'wp_head', array( $this, 'wp_head_js_vars_echo' ), 5 );

		return $this;
	}

	/**
	 * If it’s not a search-engine-crawler visiting the site,
	 * the right body-class is set to hide the empty
	 * content-element.
	 **/
	public function body_class($classNames) {

		$classes = (array) $classNames;

		if ( isset($_GET['_escaped_fragment_']) ) {
			$classes[] = 'request-direct-link';
		}

		if ( $this->request_type() == 'search_engine' ) {
			$classes[] = 'content-ready';
		}

		return $classes;
	}

	public function init_deregister_unused_scripts() {
		wp_deregister_script('l10n');
	}

  /**
   * Add our own JavaScript bootstrap hook
   **/
	public function wp_footer_scripts_echo() {

		if ( $this->request_type() != 'standard' ) {
			return;
		}

		echo $this->get('js_scripts');
	}

	/**
	 * Writing the templates to the DOM
	 **/
	function wp_footer_partials_echo() {

		if ( $this->request_type() != 'standard' ) {
			return;
		}

		echo $this->get('partials');
	}

	/**
	 * JS-Variables-Hook
	 **/
	function wp_head_js_vars_echo() {

		if ( $this->request_type() != 'standard' ) {
			return;
		}

		$js_variables = $this->get('js_variables');

		echo '<script>var BB = ' . json_encode($js_variables) . ';</script>';
	}

	/**
	 * Removing annoying stuff from the HTML-head
	 **/
	public function wp_head_cleanup() {
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'start_post_rel_link', 9, 0);
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 9, 0);
	}

	/**
	 * Initialize Mustache
	 *
	 * @return void
	 */
	private function _initMustache() {
		$mustache_options['cache'] = '/tmp/m'; //$this->root . '/cache/mustache';
		$mustache_options['cache_file_mode'] = 0666;
		$mustache_options['loader'] = new Mustache_Loader_FilesystemLoader($this->root . '/views');
		$mustache_options['partials_loader'] = new Mustache_Loader_FilesystemLoader($this->root . '/views/partials');
		$mustache_options['escape'] = function($value) {
			return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		};
		$mustache_options['charset'] = 'utf-8';
		$mustache_options['helpers'] = array('foo' => function() { return (isset($_GET['_escaped_fragment_']))? true : false; });

		$this->mustache = new Mustache_Engine($mustache_options);
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

	/**
	 * Check, wether it's a normal request, an async
	 * request or an request by a search engine crawler.
	 *
	 * Also adds an HTTP response header (X-Request-type: ) so we can traceback.
	 *
	 * @return string
	 **/
	public function request_type() {

		$type = 'standard';

		if ( isset($_GET['_escaped_fragment_']) ) {
			$type = 'search_engine';
		}

		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
			$type = 'async';
		}

		return $type;

	}

	/**
	 * Gets the content by type
	 *
	 * @param  string [$type]
	 * @return function
	 */
	public function content($type = 'loop') {

		if ( is_404() ) {
			return $this->__generate_error(404);
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

	/**
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

	/**
	 * The Error view
	 **/
	protected function __generate_error($http_code=404) {

		$request_type = $this->request_type();

		/**
		 * If it happens that this view has been
		 * called directly for the first time, just
		 * send the Mustache. JavaScript will take
		 * care to serve the content
		 */
		if ( $request_type == 'standard' ) {
			echo file_get_contents($this->root . '/views/layout.mustache');
			return;
		}

		$code = 404;

		if ( in_array($http_code, $this->wp_model->get_supported_errors()) ) {
			$code = (int) $http_code;
		}

		// Make sure we have each equivalent codes
		// in there too.
		$error = $this->wp_model->get('error', $code);

		if ( $request_type == 'async' ) {

			header('Content-type: application/json');
			echo json_encode(array(
				'type' => 'error',
				'content' => $error
			));
			exit;

		}

		/**
		 * from this point on, the request URL should have 
		 * ?_escaped_fragment_=foo and therefore be a 
		 * $request_type === 'search_engine'
		 **/

		$content = array_merge($this->get('frame'), $error);

		// Change views/error.mustache for generic-er error document #TODO

		$layout = $this->mustache->loadTemplate('error');

		// If we wanted i18n on PHP side, that’s where we’d inject it #TODO

		echo $layout->render($content);
	}

	/**
	 * Output for the loop due to request type
	 **/
	protected function __generate_loop() {

		$request_type = $this->request_type();

		/**
		 * If it happens that this view has been
		 * called directly for the first time, just
		 * send the Mustache. JavaScript will take
		 * care to serve the content
		 */
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

		/**
		 * from this point on, the request URL should have 
		 * ?_escaped_fragment_=foo and therefore be a 
		 * $request_type === 'search_engine'
		 **/

		$content = array_merge($this->get('frame'), $pagelinks);
		$content['posts'] = $posts;

		$layout = $this->mustache->loadTemplate('posts');

		// If we wanted i18n on PHP side, that’s where we’d inject it #TODO
		echo $layout->render($content);

	}

	/*
	 * Generating the content for a single post.
	**/
	protected function __generate_post() {

		$request_type = $this->request_type();

		/**
		 * If it happens that this view has been
		 * called directly for the first time, just
		 * send the Mustache. JavaScript will take
		 * care to serve the content
		 */
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

		/**
		 * from this point on, the request URL should have 
		 * ?_escaped_fragment_=foo and therefore be a 
		 * $request_type === 'search_engine'
		 **/

		$content = array_merge($content, $this->get('frame'));
		$content['comments'] = $comments;

		$layout = $this->mustache->loadTemplate('layout');
		// If we wanted i18n on PHP side, that’s where we’d inject it #TODO
		echo $layout->render($content);

	}

	/*
	 * Generating the content for a static page.
	**/
	protected function __generate_page() {

		$request_type = $this->request_type();

		/**
		 * If it happens that this view has been
		 * called directly for the first time, just
		 * send the Mustache. JavaScript will take
		 * care to serve the content
		 */
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

		/**
		 * from this point on, the request URL should have 
		 * ?_escaped_fragment_=foo and therefore be a 
		 * $request_type === 'search_engine'
		 **/

		$content = array_merge($content, $this->get('frame'), $commentform);
		$content['comments'] = $comments;

		$layout = $this->mustache->loadTemplate('layout');
		// If we wanted i18n on PHP side, that’s where we’d inject it #TODO
		echo $layout->render($content);

	}

	/*
	 * Create a JS-Object with all relevant data
	 *
	 * @return array
	**/
	protected function __get_js_variables() {

		$variables = array(
			'dev_mode' => (bool) preg_match("/localhost/", $_SERVER['HTTP_HOST']),
			'base_url' => get_option('home'),
			'template_url' => get_stylesheet_directory_uri(),
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

		// So theme can work with Xili and multilingual site
		if(class_exists('xili_language')) {
			$variables['lang'] = xili_curlang();
		}

		return $variables;

	}

	/*
	 * Create the JS-script-embedding
	 *
	 * @return string
	**/
	protected function __get_js_scripts() {

		// get_stylesheet_directory_uri() will also work with child themes
		$str = '<script src="' . get_stylesheet_directory_uri();
		$str .= '/assets/vendor/requirejs/require.js"';
		$str .= ' data-main="' . get_stylesheet_directory_uri();
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

		/*
		 * Let’s use the same names
		 *
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
		*/

		$templates = array();
		$str = '';
		$path = $this->root . '/views/partials';

		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$id = strstr($entry,'.mustache', true);
					$templates[$id] = $path.'/'.$entry;
				}
			}
			closedir($handle);
		}

		foreach ( $templates as $id => $path ) {
			$str .= '<script type="text/x-template" id="' . $id . '__tmpl">';
			$str .= str_replace(array(PHP_EOL, "\t", "\r", "  "), '', file_get_contents($path));
			$str .= '</script>';
		}

		return $str;
	}

}