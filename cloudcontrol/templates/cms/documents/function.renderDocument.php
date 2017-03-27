<? function renderDocument($document, $cmsPrefix, $slugPrefix = '') {?>
	<div class="grid-box-10">
		<h3>
			<a class="btn documentTitle" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/edit-document?slug=<?=$slugPrefix . $document->slug?>" title="Edit">
				<i class="fa fa-file-text-o"></i>
				<small class="state <?=strtolower($document->state)?>"><i class="fa <?=$document->state == 'published' ? 'fa-check-circle-o' : 'fa-times-circle-o' ?>"></i></small>
				<?=$document->title?>
			</a>
			<? if ($document->unpublishedChanges) : ?><small class="small unpublished-changes">Unpublished Changes</small><? endif ?>
			<small class="small documentType"><?=$document->documentType?></small>
			<small class="small lastModified" title="<?=date('r', $document->lastModificationDate)?>">
				<span class="label">Modified:</span>
				<?=\library\cc\StringUtil::timeElapsedString($document->lastModificationDate)?>
			</small>
			<small class="small lastModifiedBy">
				<span class="label">By:</span>
				<?=$document->lastModifiedBy?>
			</small>
		</h3>
	</div>
	<div class="documentActions grid-box-2">
		<? if ($document->state == 'unpublished' || $document->unpublishedChanges) : ?>
            <?renderAction('Publish',
                'publish',
                \library\cc\Request::$subfolders . $cmsPrefix . '/documents/publish-document?slug=' . $slugPrefix . $document->slug,
                'check');?>
		<? endif ?>
		<? if ($document->state == 'published') : ?>
			<?renderAction('Unpublish',
				'unpublish',
				\library\cc\Request::$subfolders . $cmsPrefix . '/documents/unpublish-document?slug=' . $slugPrefix . $document->slug,
				'times');?>
		<? endif ?>
		<?renderAction('Edit',
			'',
			\library\cc\Request::$subfolders . $cmsPrefix . '/documents/edit-document?slug=' . $slugPrefix . $document->slug,
			'pencil');?>
		<? if ($document->state == 'unpublished') : ?>
			<?renderAction('Delete',
				'error',
				\library\cc\Request::$subfolders . $cmsPrefix . '/documents/delete-document?slug=' . $slugPrefix . $document->slug,
				'trash',
                'return confirm(\'Are you sure you want to delete this document?\');');?>
		<? endif ?>
	</div>
<?}?>
