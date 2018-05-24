<td class="icon" title="<?= $document->type ?>">
  <i class="fa fa-file-text-o"></i>
</td>

<?php if ($document->state === 'published') : ?>
  <td class="icon" title="<?= $document->publicationDate >= time() ? 'scheduled for publication' : 'published' ?>">
    <i class="fa <?= $document->publicationDate >= time() ? 'fa-clock-o' : 'fa-check-circle-o' ?>"></i>
  </td>
<?php else : ?>
  <td class="icon" title="<?= $document->state ?>">
    <i class="fa fa-times-circle-o"></i>
  </td>
<?php endif ?>

<td>
  <a href="<?= getEditDocumentLink($request, $cmsPrefix, $path, $document) ?>"><?= $document->title ?></a>
    <?php if ($document->unpublishedChanges) : ?>
      <small class="small unpublished-changes">Unpublished Changes</small>
    <?php endif ?>
    <?php if ($document->publicationDate >= time()) : ?>
      <small class="small scheduled-for-publication">
        <i class="fa fa-clock-o"></i><?= date('d-m-Y H:i', $document->publicationDate) ?></small>
    <?php endif ?>
  <small class="small document-type"><?= $document->documentType ?></small>
  <div class="details">
    <table>
      <tr>
        <th>Document Type</th>
        <td><?= $document->documentType ?></td>
        <th>Last Modified By</th>
        <td><?= $document->lastModifiedBy ?></td>
      </tr>
      <tr>
        <th>Created</th>
        <td title="<?= date('d-m-Y H:i:s',
            $document->creationDate) ?>"><?= \CloudControl\Cms\util\StringUtil::timeElapsedString($document->creationDate) ?></td>
        <th>Last Modified</th>
        <td title="<?= date('d-m-Y H:i:s',
            $document->lastModificationDate) ?>"><?= \CloudControl\Cms\util\StringUtil::timeElapsedString($document->lastModificationDate) ?></td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
        <th>Published</th>
          <?php if ($document->state === 'published' && $document->publicationDate <= time()) : ?>
            <td title="<?= date('d-m-Y H:i:s',
                $document->publicationDate) ?>"><?= \CloudControl\Cms\util\StringUtil::timeElapsedString($document->publicationDate) ?></td>
          <?php else : ?>
            <td>Not yet</td>
          <?php endif ?>
      </tr>
    </table>
  </div>
</td>
<td class="icon context-menu-container">
  <div class="context-menu">
    <i class="fa fa-ellipsis-v"></i>
    <ul>
      <li>
        <a href="<?= getEditDocumentLink($request, $cmsPrefix, $path, $document) ?>">
          <i class="fa fa-pencil"></i>
          Edit
        </a>
      </li>
        <?php if ($document->state === 'unpublished' || $document->unpublishedChanges) : ?>
          <li>
            <a href="<?= getPublishDocumentLink($request, $cmsPrefix, $path, $document) ?>">
              <i class="fa fa-check"></i>
              Publish now
            </a>
          </li>
          <li>
            <a href="<?= getPublishDocumentLink($request, $cmsPrefix, $path, $document) ?>">
              <i class="fa fa-clock-o"></i>
              Schedule publication
            </a>
          </li>
        <?php endif ?>
        <?php if ($document->state === 'published') : ?>
          <li>
            <a href="<?= getUnpublishDocumentLink($request, $cmsPrefix, $path, $document) ?>">
              <i class="fa fa-times"></i>
              <?=$document->publicationDate <= time() ? 'Unpublish now' : 'Cancel publication' ?>
            </a>
          </li>
        <?php endif ?>
        <?php if ($document->state === 'unpublished') : ?>
          <li>
            <a href="<?= getDeleteDocumentLink($request, $cmsPrefix, $path, $document) ?>"
               onclick="return confirm('Are you sure you want to delete this document?');">
              <i class="fa fa-trash"></i>
              Delete
            </a>
          </li>
        <?php endif ?>
      <li>
        <a href="#" onclick="return showDocumentDetails(this);">
          <i class="fa fa-list-alt"></i>
          Details
        </a>
      </li>
    </ul>
  </div>
</td>