<section class="dashboard images">
	<h2><i class="fa fa-image"></i> Images</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/images" title="Back">Back</a>
			</li>
		</ul>
	</nav>
	<div class="show-image">
		<label>File</label>
		<div class="value">
			<?=isset($image)? $image->file : '' ?>
		</div>
		<label>Type</label>
		<div class="value">
			<?=isset($image)? $image->type : '' ?>
		</div>
		<label>Size</label>
		<div class="value">
			<?=isset($image)? \library\cc\StringUtil::humanFileSize($image->size) : '' ?>
		</div>
		<label>Set</label>
		<? if (isset($image)) : ?>
			<? foreach ($image->set as $key => $set) : ?>
				<div class="sets">
					<label><?=$key?></label>
					<img src="<?=\library\cc\Request::$subfolders . 'images/' . $set?>" />
				</div>
			<? endforeach ?>
		<? endif ?>
	</div>
</section>