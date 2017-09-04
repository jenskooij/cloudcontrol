<?
/** @var \stdClass $activityLog
 * @var bool $searchNeedsUpdate
 * @var \CloudControl\Cms\cc\Request $request
 * @var array $userRights
 * @var string $cmsPrefix
 */
?>
<section class="dashboard ">

  <div class="grid-wrapper">
    <ul class="grid-container">
      <li class="grid-box-6">
        <div class="grid-inner">
          <nav class="tiles grid-wrapper">
            <ul class="grid-container">

              <li class="tile grid-box-6">
                <a class="btn return" href="<?= $request::$subfolders ?>">
                  <i class="fa fa-reply"></i>
                  Return to site
                </a>
              </li>

                <? if (in_array('documents', $userRights)) : ?>
                  <li class="tile grid-box-6">
                    <a class="btn documents" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents">
                      <i class="fa fa-file-text-o"></i>
                      Documents
                    </a>
                  </li>
                <? endif ?>
                <? if (in_array('valuelists', $userRights)) : ?>
                  <li class="tile grid-box-4">
                    <a class="btn valuelists" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/valuelists">
                      <i class="fa fa-tags"></i>
                      Valuelists
                    </a>
                  </li>
                <? endif ?>
                <? if (in_array('sitemap', $userRights)) : ?>
                  <li class="tile grid-box-4">
                    <a class="btn sitemap" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/sitemap">
                      <i class="fa fa-map-signs"></i>
                      Sitemap
                    </a>
                  </li>
                <? endif ?>
                <? if (in_array('redirects', $userRights)) : ?>
                  <li class="tile grid-box-4">
                    <a class="btn redirects" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/redirects">
                      <i class="fa fa-random"></i>
                      Redirects
                    </a>
                  </li>
                <? endif ?>
                <? if (in_array('images', $userRights)) : ?>
                  <li class="tile grid-box-4">
                    <a class="btn images" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images">
                      <i class="fa fa-picture-o"></i>
                      Images
                    </a>
                  </li>
                <? endif ?>
                <? if (in_array('files', $userRights)) : ?>
                  <li class="tile grid-box-4">
                    <a class="btn files" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/files">
                      <i class="fa fa-files-o"></i>
                      Files
                    </a>
                  </li>
                <? endif ?>
                <? if (in_array('configuration', $userRights)) : ?>
                  <li class="tile grid-box-4">
                    <a class="btn configuration" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">
                      <i class="fa fa-cogs"></i>
                      Configuration
                    </a>
                  </li>
                <? endif ?>
            </ul>
          </nav>
        </div>
      </li>
      <li class="grid-box-6">
        <div class="grid-inner">
          <div class="search">
              <? if ($searchNeedsUpdate) : ?>
                <div class="message warning">
                  <i class="fa fa-exclamation-triangle"></i> Search index is no longer in sync with documents.
                    <? if (in_array('search', $userRights)) : ?>
                      <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search/update-index?returnUrl=<?=urlencode($request::$subfolders . $cmsPrefix)?>" title="Update Index">Update Index</a>
                    <? endif ?>
                </div>
              <? else : ?>
                <div class="message valid">
                  <i class="fa fa-check"></i> Search index is in sync with documents.
                </div>
              <? endif ?>
          </div>
          <ul class="activityLog">
              <? foreach ($activityLog as $row) : ?>
                <li class="row">
                    <? if ($row->icon !== null) : ?>
                      <i class="fa fa-<?= $row->icon ?>"></i>
                    <? endif ?>
                  <span class="timestamp"><?= \CloudControl\Cms\cc\StringUtil::timeElapsedString($row->timestamp) ?></span>
                  <b><?= $row->user ?></b>
                  <span class="message"><?= $row->message ?></span>
                </li>
              <? endforeach ?>
          </ul>
        </div>
      </li>
    </ul>
  </div>
</section>