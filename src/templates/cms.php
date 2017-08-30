<!DOCTYPE html>
<html>
  <head>
    <title>Cloud Control CMS</title>
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto|Ubuntu+Mono:400italic,700italic,400,700"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="<?= $request::$subfolders ?>css/cms.css"/>
    <link rel="shortcut icon" type="image/png" href="<?= $request::$subfolders ?>favicon.ico"/>
    <meta name="viewport" content="initial-scale=1, maximum-scale=2">
    <meta charset="UTF-8">
  </head>
  <body>
    <header id="header" class="header">
      <h1>Cloud Control CMS</h1>
      <nav id="mainNav" class="mainNav grid-wrapper">
        <ul class="grid-container">
          <li class="grid-box-1">
            <a class="btn grid-inner<?= $mainNavClass == 'dashboard' ? ' active' : '' ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/">
              <i class="fa fa-th"></i>
              <span>Dashboard</span>
            </a>
          </li>
            <? $nrOfMenuItems = 0 ?>
            <? if (in_array('documents', $userRights)) : ?>
              <li class="grid-box-1">
                <a class="btn documents grid-inner<?= $mainNavClass == 'documents' ? ' active' : '' ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents">
                  <i class="fa fa-file-text-o"></i>
                  <span>Documents</span>
                </a>
              </li>
                <? $nrOfMenuItems += 1 ?>
            <? endif ?>
            <? if (in_array('sitemap', $userRights)) : ?>
              <li class="grid-box-1">
                <a class="btn sitemap grid-inner<?= $mainNavClass == 'sitemap' ? ' active' : '' ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/sitemap">
                  <i class="fa fa-map-signs"></i>
                  <span>Sitemap</span>
                </a>
              </li>
                <? $nrOfMenuItems += 1 ?>
            <? endif ?>
            <? if (in_array('images', $userRights)) : ?>
              <li class="grid-box-1">
                <a class="btn images grid-inner<?= $mainNavClass == 'images' ? ' active' : '' ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images">
                  <i class="fa fa-picture-o"></i>
                  <span>Images</span>
                </a>
              </li>
                <? $nrOfMenuItems += 1 ?>
            <? endif ?>
            <? if (in_array('files', $userRights)) : ?>
              <li class="grid-box-1">
                <a class="btn files grid-inner<?= $mainNavClass == 'files' ? ' active' : '' ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/files">
                  <i class="fa fa-files-o"></i>
                  <span>Files</span>
                </a>
              </li>
                <? $nrOfMenuItems += 1 ?>
            <? endif ?>
            <? if (in_array('search', $userRights)) : ?>
              <li class="grid-box-1">
                <a class="btn search grid-inner<?= $mainNavClass == 'search' ? ' active' : '' ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search">
                  <i class="fa fa-search"></i>
                  <span>Search</span>
                </a>
              </li>
                <? $nrOfMenuItems += 1 ?>
            <? endif ?>
            <? if (in_array('configuration', $userRights)) : ?>
              <li class="grid-box-1">
                <a class="btn configuration grid-inner<?= $mainNavClass == 'configuration' ? ' active' : '' ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">
                  <i class="fa fa-cogs"></i>
                  <span>Configuration</span>
                </a>
              </li>
                <? $nrOfMenuItems += 1 ?>
            <? endif ?>
          <li class="grid-box-<?= 6 + (5 - $nrOfMenuItems) ?> log-off-box">
            <a class="btn log-off grid-inner" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/log-off">
              <i class="fa fa-power-off"></i>
              <span>Log off</span>
            </a>
          </li>
        </ul>
      </nav>
    </header>
    <a class="btn mainNav-toggle" id="mainNav_toggle" title="Toggle Menu">
      <i class="fa fa-bars"></i>
      <span>Toggle Menu</span>
    </a>
    <main class="body">
        <? if (isset($body)) : ?>
            <?= $body ?>
        <? else : ?>
          <section class="not-found">
            <h2>Page not found.</h2>
          </section>
        <? endif ?>
    </main>
    <script>
      var subfolders = '<?=$request::$subfolders?>',
        cmsSubfolders = '<?=$request::$subfolders . $cmsPrefix?>';
    </script>
    <script src="<?= $request::$subfolders ?>js/cms.js"></script>
  </body>
</html>