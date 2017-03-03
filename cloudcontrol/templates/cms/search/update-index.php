<section class="dashboard search">
	<h2><i class="fa fa-search"></i> <a href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/search" title="Search">Search</a> &raquo; Update Index</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/search" title="Back">Back</a>
			</li>
		</ul>
	</nav>
	<h3>Index Log</h3>
	<textarea disabled="disabled" rows="8" class="search-log"><?=$searchLog?></textarea>
</section>