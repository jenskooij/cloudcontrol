<section class="dashboard images">
	<h2><i class="fa fa-image"></i> Images</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/images/new" title="New">+</a>
			</li>
		</ul>
	</nav>
	<div class="grid-wrapper">
		<ul class="images grid-container">
			<? if (isset($images)) : ?>
				<? foreach ($images as $image) : ?>
					<li class="grid-box-2">
						<div class="grid-inner">
							<a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/images/delete?file=<?=$image->file?>" title="Delete"><i class="fa fa-times"></i></a>
							<a class="image-link" href="<?=$request::$subfolders?><?=$cmsPrefix?>/images/show?file=<?=$image->file?>" title="Show">
								<img src="<?=$request::$subfolders?>images/<?=isset($image->set->$smallestImage) ? $image->set->$smallestImage : current($image->set)?>" />
							</a>
							<small class="small filename">
								<span class="label">Name:</span>
								<?=$image->file?>
							</small>
							<small class="small fileType">
								<span class="label">Type:</span>
								<?=$image->type?>
							</small>
							<small class="small fileSize">
								<span class="label">Size:</span>
								<?=humanFileSize($image->size)?>
							</small>
						</div>
					</li>
				<? endforeach ?>
			<? endif ?>
		</ul>
	</div>
</section>