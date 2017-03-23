<? function renderFolder($document, $cmsPrefix, $slugPrefix ='', $root = false) {?>
	<div class="grid-box-8">
		<h3>
			<a class="btn documentTitle openFolder" data-slug="<?=$slugPrefix . $document->slug?>" title="Open">
				<i class="fa fa-folder-o "></i> <?=$document->title?>
			</a>
		</h3>
	</div>
	<div class="documentActions grid-box-4">
		<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/edit-folder?slug=<?=$slugPrefix . $document->slug?>" title="Edit"><i class="fa fa-pencil"></i></a>
		<a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/delete-folder?slug=<?=$slugPrefix . $document->slug?>" title="Delete"><i class="fa fa-trash"></i></a>
	</div>
	<ul class="documents grid-wrapper nested<?=$root ? ' root' : '' ?>">
		<? foreach ($document->content as $subDocument) : ?>
			<li class="grid-container">
				<? if ($subDocument->type == 'document') : ?>
					<?renderDocument($subDocument, $cmsPrefix, $slugPrefix . $document->slug . '/');?>
				<? elseif ($subDocument->type == 'folder') : ?>
					<?renderFolder($subDocument, $cmsPrefix, $slugPrefix . $document->slug . '/');?>
				<? endif ?>
			</li>
		<? endforeach ?>
		<? if (count($document->content) == 0) : ?>
			<li class="grid-container">
				<div class="grid-box-12">
					<i class="fa fa-ellipsis-h empty"></i>
					<i>Empty</i>
				</div>
			</li>
		<? endif ?>
	</ul>
<?}?>