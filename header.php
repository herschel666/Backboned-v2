<?php if ( empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest' ) : ?>
<!doctype html>
<!--[if lt IE 8]>  <html class="no-js lt-ie9 lt-ie8 old" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>     <html class="no-js lt-ie9 ie8 old" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9]>     <html class="no-js ie9 old" <?php language_attributes(); ?>> <![endif]-->
<!--[if !IE]><!--> <html class="no-js modern" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<!-- make sure google et al. opts in to the hashbang-party -->
	<meta name="fragment" content="!">
	
	<base href="<?php bloginfo('url'); ?>/">
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" media="all" href="<?php bloginfo('stylesheet_url'); ?>">
	<title><?php wp_title( ' | ', true, 'right' ); ?><?php bloginfo('name'); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	
	<?php wp_head(); ?>
</head>
<body>
<?php endif; ?>