<?
/**
 * @param CloudControl\Cms\storage\Document $document
 * @param string $cmsPrefix
 * @param string $slugPrefix
 * @param bool $root
 * @param \CloudControl\Cms\cc\Request $request
 */
function renderFolder($document, $cmsPrefix, $slugPrefix = '', $root = false, $request)
{ ?>
  <div class="grid-box-8">
    <h3>
      <a class="btn documentTitle openFolder" data-slug="<?= $slugPrefix . $document->slug ?>" title="Open">
        <i class="fa fa-folder-o "></i> <?= $document->title ?>
      </a>
    </h3>
  </div>
  <div class="documentActions grid-box-4">
      <? renderAction('Edit',
          '',
          $request::$subfolders . $cmsPrefix . '/documents/edit-folder?slug=' . $slugPrefix . $document->slug,
          'pencil'); ?>
      <? renderAction('Delete',
          'error',
          $request::$subfolders . $cmsPrefix . '/documents/delete-folder?slug=' . $slugPrefix . $document->slug,
          'trash',
          'return confirm(\'Are you sure you want to delete this document?\');'); ?>
  </div>
  <ul class="documents grid-wrapper nested<?= $root ? ' root' : '' ?>">
      <? foreach ($document->getContent() as $subDocument) : ?>
        <li class="grid-container">
            <? if ($subDocument->type == 'document') : ?>
                <? renderDocument($subDocument, $cmsPrefix, $slugPrefix . $document->slug . '/', $request); ?>
            <? elseif ($subDocument->type == 'folder') : ?>
                <? renderFolder($subDocument, $cmsPrefix, $slugPrefix . $document->slug . '/', false, $request); ?>
            <? endif ?>
        </li>
      <? endforeach ?>
      <? if (count($document->getContent()) == 0) : ?>
        <li class="grid-container">
          <div class="grid-box-12">
            <i class="fa fa-ellipsis-h empty"></i>
            <i>Empty</i>
          </div>
        </li>
      <? endif ?>
  </ul>
<? } ?>