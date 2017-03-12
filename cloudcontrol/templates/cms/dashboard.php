<section class="dashboard ">
	<nav class="tiles grid-wrapper">
		<ul class="grid-container">
			<li class="tile grid-box-3">
				<a class="btn return" href="<?=\library\cc\Request::$subfolders?>">
					<i class="fa fa-reply"></i>
					Return to site
				</a>
			</li>
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
			<? if (in_array('search', $userRights)) : ?>
                <li class="tile grid-box-3">
                    <a class="btn search" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/search">
                        <i class="fa fa-search"></i>
                        Search
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