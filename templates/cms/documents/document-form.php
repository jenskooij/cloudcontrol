<section class="documents">
	<h2><i class="fa fa-file-text-o"></i> Documents</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents" title="Back">
					Back
				</a>
			</li>
		</ul>
	</nav>
	<ul class="documents grid-wrapper">
		<li class="grid-container">
			<div class="grid-box-12">
				<i class="fa fa-terminal" title="Path"></i>
				<i id="pathHolder"><?=$request::$get['path']?></i>
			</div>
		</li>
	</ul>
	<form method="<?= isset($request::$get['documentType']) ? 'post' : 'get' ?>">
		<input type="hidden" name="path" value="<?=$request::$get['path']?>" />
		<? if (isset($request::$get['documentType'])) : ?>
			<? foreach ($documentType->fields as $field) : ?>
				<div class="form-element">
					<? include(__DIR__ . '/fieldTypes/' . str_replace(' ', '-', $field->type) . '.php') ?>
				</div>
			<? endforeach ?>
		<? else : ?>
		<div class="form-element">
			<label for="documentType">Document Type</label>
			<select id="documentType" name="documentType">
				<? foreach ($documentTypes as $documentType) : ?>
				<option value="<?=$documentType->slug?>"><?=$documentType->title?></option>
				<? endforeach ?>
			</select>
		</div>
		<? endif ?>
		<div class="form-element">
			<input class="btn" type="submit" value="Save" />
		</div>
	</form>
</section>
