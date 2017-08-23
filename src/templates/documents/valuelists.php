<section class="documents">
  <h2><i class="fa fa-file-text-o"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents">Documents</a> &raquo;
    Valuelists</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/valuelists/new"
           title="New valuelist">
          + <i class="fa fa-list-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <ul class="valuelists grid-wrapper">
      <? if (isset($valuelists)) : ?>
          <? foreach ($valuelists as $valuelist) : ?>
          <li class="grid-container">
            <div class="grid-box-10">
              <h3>
                <a class="btn documentTitle" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/valuelists/edit?slug=<?= $valuelist->slug ?>" title="Edit">
                  <i class="fa fa-list-alt"></i> <?= $valuelist->title ?>
                </a>
              </h3>
            </div>
            <div class="documentActions grid-box-2">
              <a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/valuelists/delete?slug=<?= $valuelist->slug ?>" title="Delete"><i class="fa fa-trash"></i></a>
            </div>
          </li>
          <? endforeach ?>
      <? endif ?>
  </ul>
</section>