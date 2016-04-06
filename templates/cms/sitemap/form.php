<section class="sitemap">
	<h2>Sitemap</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap">Back</a>
			</li>
		</ul>
	</nav>
	<form method="post" class="panel" id="sitemapForm">
		<div class="form-element">
			<label for="title">Title</label>
			<input required="required" id="title" type="text" name="title" placeholder="Title" value="<?=isset($sitemapItem) ? $sitemapItem->title : '' ?>" />
		</div>
		<div class="form-element">
			<label for="url">Url</label>
			<input required="required" id="url" type="text" name="url" placeholder="Url" value="<?=isset($sitemapItem) ? $sitemapItem->url : '' ?>" />
		</div>
		<div class="form-element">
			<label for="regex">Regex</label>
			<input id="regex" type="checkbox" name="regex"<?=isset($sitemapItem) && $sitemapItem->regex == true ? ' checked="checked"' : '' ?>/>
		</div>
		<div class="form-element">
			<label for="component">Component</label>
			<input required="required" id="component" type="text" name="component" placeholder="Component" value="<?=isset($sitemapItem) ? $sitemapItem->component : '' ?>" />
		</div>
		<div class="form-element">
			<label for="template">Template</label>
			<input required="required" id="template" type="text" name="template" placeholder="Template" value="<?=isset($sitemapItem) ? $sitemapItem->template : '' ?>" />
		</div>
		<div class="form-element">
			<label for="template">Parameters</label>
			<div id="dropZone">
			<? if (isset($sitemapItem)) : ?>
				<? foreach ($sitemapItem->parameters as $key => $value) : ?>
					<div class="form-element parameters">
						<input type="text" required="required" name="parameterNames[]" placeholder="Parameter Name" value="<?=$key?>" />
						<input type="text" required="required" name="parameterValues[]" placeholder="Parameter Value" value="<?=$value?>" />
						<a class="btn error" id="sitemap_remove_parameter">x</a>
					</div>
				<? endforeach ?>
			<? endif ?>
			</div>
			<a class="btn add-parameter" id="sitemap_add_parameter">+</a>
		</div>
		<div class="form-element">
			<input class="btn" type="submit" value="Save" />
		</div>
	</form>
</section>
<div class="form-element parameters" id="parameterPlaceholder" style="display:none;">
	<input type="text" required="required" name="parameterNames[]" placeholder="Parameter Name" />
	<input type="text" required="required" name="parameterValues[]" placeholder="Parameter Value" />
	<a class="btn error" id="sitemap_remove_parameter">x</a>
</div>