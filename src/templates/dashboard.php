<section class="dashboard ">
  <nav class="tiles grid-wrapper">
    <ul class="grid-container">

      <li class="tile grid-box-3">
        <a class="btn return" href="<?= $request::$subfolders ?>">
          <i class="fa fa-reply"></i>
          Return to site
        </a>
      </li>
      <li class="activityLog grid-box-6">
        <ul class="grid-inner">
          <? foreach ($activityLog as $row) : ?>
            <li>
              <span class="timestamp"><?=\CloudControl\Cms\cc\StringUtil::timeElapsedString($row->timestamp)?></span> <b><?=$row->user?></b> <?=$row->message?>
            </li>
          <? endforeach ?>
        </ul>
      </li>
        <? if (in_array('documents', $userRights)) : ?>
          <li class="tile grid-box-3">
            <a class="btn documents" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents">
              <i class="fa fa-file-text-o"></i>
              Documents
            </a>
          </li>
        <? endif ?>
        <? if (in_array('sitemap', $userRights)) : ?>
          <li class="tile grid-box-3">
            <a class="btn sitemap" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/sitemap">
              <i class="fa fa-map-signs"></i>
              Sitemap
            </a>
          </li>
        <? endif ?>
        <? if (in_array('images', $userRights)) : ?>
          <li class="tile grid-box-3">
            <a class="btn images" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images">
              <i class="fa fa-picture-o"></i>
              Images
            </a>
          </li>
        <? endif ?>
        <? if (in_array('files', $userRights)) : ?>
          <li class="tile grid-box-3">
            <a class="btn files" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/files">
              <i class="fa fa-files-o"></i>
              Files
            </a>
          </li>
        <? endif ?>
        <? if (in_array('search', $userRights)) : ?>
          <li class="tile grid-box-3">
            <a class="btn search" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search">
              <i class="fa fa-search"></i>
              Search
            </a>
          </li>
        <? endif ?>
        <? if (in_array('configuration', $userRights)) : ?>
          <li class="tile grid-box-3">
            <a class="btn configuration" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">
              <i class="fa fa-cogs"></i>
              Configuration
            </a>
          </li>
        <? endif ?>
    </ul>
  </nav>
</section>