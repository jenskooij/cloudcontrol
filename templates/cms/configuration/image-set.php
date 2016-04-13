<section class="dashboard configuration">
	<h2><i class="fa fa-cogs"></i> <a href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration">Configuration</a> &raquo; Image Set</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration/image-set/new" title="New">+</a>
			</li>
		</ul>
	</nav>
	<? if (isset($imageSet)) : ?>
		<ul class="configuration grid-wrapper">
			<? foreach ($imageSet as $imageSet) : ?>
				<li class="grid-container">
					<div class="grid-box-8">
						<h3>
							<a class="btn documentTitle" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration/image-set/edit?slug=<?=$imageSet->slug?>" title="Edit">
								<i class="fa fa-file-image-o"></i> <?=$imageSet->title?>
							</a>
							<small class="small">
								<span class="label">Size:</span>
								<?=$imageSet->width?>x<?=$imageSet->height?>
							</small> -
							<small class="small">
								<span class="label">Method:</span>
								<?=ucfirst($imageSet->method)?>
							</small>
						</h3>
					</div>
					<div class="documentActions grid-box-4">
						<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration/image-set/edit?slug=<?=$imageSet->slug?>" title="Edit"><i class="fa fa-pencil"></i></a>
						<a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration/image-set/delete?slug=<?=$imageSet->slug?>" title="Delete"><i class="fa fa-times"></i></a>
					</div>
				</li>
			<? endforeach ?>
		</ul>
	<? endif ?>
</section>