<section class="dashboard search">
	<h2><i class="fa fa-search"></i> Search</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn<? if (!$searchNeedsUpdate) : ?> reset<? endif ?>" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/search/update-index" title="Update Index">Update Index</a>
			</li>
		</ul>
	</nav>
    <? if ($searchNeedsUpdate) : ?>
    <div class="message warning">
        <i class="fa fa-exclamation-triangle"></i> Search index is no longer in sync with documents.
    </div>
    <? else : ?>
        <div class="message valid">
            <i class="fa fa-check"></i> Search index is in sync with documents.
        </div>
    <? endif ?>
</section>