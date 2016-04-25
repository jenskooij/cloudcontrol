<script>window.onload=function(){History.init();History.replaceState(null, 'Cloud Control CMS', '/<?=$request::$subfolders . $cmsPrefix?>/documents?path=/');};</script>
<section class="documents">
	<h2><i class="fa fa-file-text-o"></i> Documents</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" onmousedown="this.setAttribute('href', '<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/new-document?path=' + getParameterByName('path'));" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/new-document" title="New Document">
					+ <i class="fa fa-file-text-o"></i>
				</a>
				<a class="btn" onmousedown="this.setAttribute('href', '<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/new-folder?path=' + getParameterByName('path'));" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/new-folder" title="New Folder">
					+ <i class="fa fa-folder-o"></i>
				</a>
			</li>
		</ul>
	</nav>
	<? if (isset($documents)) : ?>
		<ul class="documents grid-wrapper">
			<li class="grid-container">
				<div class="grid-box-12">
					<i class="fa fa-terminal" title="Path"></i>
					<i id="pathHolder">/</i>
				</div>
			</li>
			<? foreach ($documents as $document) : ?>
				<li class="grid-container">
					<? if ($document->type == 'document') : ?>
						<?renderDocument($document, $cmsPrefix);?>
					<? elseif ($document->type == 'folder') : ?>
						<?renderFolder($document, $cmsPrefix, '', true);?>
					<? endif ?>
				</li>

			<? endforeach ?>
		</ul>
	<? endif ?>
</section>
<? function renderDocument($document, $cmsPrefix, $slugPrefix = '') {?>
<div class="grid-box-10">
	<h3>
		<a class="btn documentTitle" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/edit-document?slug=<?=$slugPrefix . $document->slug?>" title="Edit">
			<i class="fa fa-file-text-o"></i> <?=$document->title?>
		</a>
		<small class="small state <?=strtolower($document->state)?>"><?=ucfirst($document->state)?></small>
		<small class="small documentType"><?=$document->documentType?></small>
		<small class="small lastModified" title="<?=date('r', $document->lastModificationDate)?>">
			<span class="label">Modified:</span>
			<?=timeElapsedString($document->lastModificationDate)?>
		</small>
		<small class="small lastModifiedBy">
			<span class="label">By:</span>
			<?=$document->lastModifiedBy?>
		</small>
	</h3>
</div>
<div class="documentActions grid-box-2">
	<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/edit-document?slug=<?=$slugPrefix . $document->slug?>" title="Edit"><i class="fa fa-pencil"></i></a>
	<a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/delete?slug=<?=$slugPrefix . $document->slug?>" title="Delete"><i class="fa fa-times"></i></a>
</div>
<?}?>
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
	<a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents/delete-folder?slug=<?=$slugPrefix . $document->slug?>" title="Delete"><i class="fa fa-times"></i></a>
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
