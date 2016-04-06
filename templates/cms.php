<!DOCTYPE html>
<html>
<head>
	<title>Cloud Control CMS</title>
	<link rel="stylesheet" href="<?=\library\cc\Request::$subfolders?>/css/cms.css"/>
</head>
<body>
	<header id="header" class="header">
		<h1>Cloud Control CMS</h1>
		<nav id="mainNav" class="mainNav grid-wrapper">
			<ul class="grid-container">
				<li class="grid-box-2">
					<a class="btn documents grid-inner<?=$mainNavClass == 'documents' ? ' active' : ''?>" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents">
						<i class="fa fa-file-text-o"></i>
						Documents
					</a>
				</li>
				<li class="grid-box-2">
					<a class="btn sitemap grid-inner<?=$mainNavClass == 'sitemap' ? ' active' : ''?>" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap">
						<i class="fa fa-map-signs"></i>
						Sitemap
					</a>
				</li>
				<li class="grid-box-2">
					<a class="btn images grid-inner" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/images">
						<i class="fa fa-picture-o"></i>
						Images
					</a>
				</li>
				<li class="grid-box-2">
					<a class="btn files grid-inner" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/files">
						<i class="fa fa-files-o"></i>
						Files
					</a>
				</li>
				<li class="grid-box-2">
					<a class="btn configuration grid-inner<?=$mainNavClass == 'configuration' ? ' active' : ''?>" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration">
						<i class="fa fa-cogs"></i>
						Configuration
					</a>
				</li>
				<li class="grid-box-2">
					<a class="btn log-off grid-inner" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/log-off">
						<i class="fa fa-power-off"></i>
						Log off
					</a>
				</li>
			</ul>
		</nav>
	</header>
	<a class="btn mainNav-toggle" id="mainNav_toggle" title="Toggle Menu">
		<i class="fa fa-bars"></i>
		<span>Toggle Menu</span>
	</a>
	<main class="body">
	<? if (isset($body)) : ?>
		<?=$body?>
	<? else : ?>
		<section class="not-found">
			<h2>Page not found.</h2>
		</section>
	<? endif ?>
	</main>
	<script src="<?=\library\cc\Request::$subfolders?>js/cms.js"></script>
</body>
</html>