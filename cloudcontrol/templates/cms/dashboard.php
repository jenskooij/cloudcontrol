<section class="dashboard ">
	<nav class="tiles grid-wrapper">
		<ul class="grid-container">
			<? if (in_array('documents', $userRights)) : ?>
			<li class="tile grid-box-3">
				<a class="btn documents" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents">
					<i class="fa fa-file-text-o"></i>
					Documents
				</a>
			</li>
			<? endif ?>
			<? if (in_array('sitemap', $userRights)) : ?>
			<li class="tile grid-box-3">
				<a class="btn sitemap" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap">
					<i class="fa fa-map-signs"></i>
					Sitemap
				</a>
			</li>
			<? endif ?>
			<? if (in_array('images', $userRights)) : ?>
			<li class="tile grid-box-3">
				<a class="btn images" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/images">
					<i class="fa fa-picture-o"></i>
					Images
				</a>
			</li>
			<? endif ?>
			<? if (in_array('files', $userRights)) : ?>
			<li class="tile grid-box-3">
				<a class="btn files" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/files">
					<i class="fa fa-files-o"></i>
					Files
				</a>
			</li>
			<? endif ?>
			<? if (in_array('configuration', $userRights)) : ?>
			<li class="tile grid-box-3">
				<a class="btn configuration" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration">
					<i class="fa fa-cogs"></i>
					Configuration
				</a>
			</li>
			<? endif ?>
		</ul>
	</nav>
</section>