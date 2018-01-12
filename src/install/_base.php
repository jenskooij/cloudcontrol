<!DOCTYPE html>
<html>
  <head>
    <!-- This is where the title of the current document is displayed in the title of the browser -->
    <title>
        <?php if (isset($document) && $document !== false) : ?>
            <?= $document->title ?>
        <?php else : ?>
          Cloud Control
        <?php endif ?>
    </title>
    <link rel="stylesheet" href="<?= $request::$subfolders ?>css/site.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.blue-pink.min.css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
  </head>
  <body>
    <!-- Always shows a header, even in smaller screens. -->
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
      <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
          <span class="mdl-layout-title">
            <!-- This is where the title of the current document is displayed -->
              <?php if (isset($document) && $document !== false) : ?>
                  <?= $document->title ?>
              <?php else : ?>
                Cloud Control
              <?php endif ?>
          </span>
          <!-- Add spacer, to align navigation to the right -->
          <div class="mdl-layout-spacer"></div>
          <!-- Navigation. We hide it in small screens. -->
          <nav class="mdl-navigation mdl-layout--large-screen-only">
            <a class="mdl-navigation__link" href="<?= \CloudControl\Cms\services\LinkService::get('/') ?>">Home</a>
            <a class="mdl-navigation__link" href="<?= \CloudControl\Cms\services\LinkService::get('/cms') ?>">Cms</a>
          </nav>
        </div>
      </header>
      <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">
          <!-- This is the title of the menu -->
          Cloud Control
        </span>
        <nav class="mdl-navigation">
          <!-- This is where we loop through available documents and fill the navigation -->
            <?php if (isset($folder) && $folder !== false) : ?>
                <?php foreach ($folder->getContent() as $doc) : ?>
                    <? if ($doc->state === 'published') : ?>
                  <a class="mdl-navigation__link" href="<?= \CloudControl\Cms\services\LinkService::get($doc->path) ?>"><?= $doc->title ?></a>
                    <? endif ?>
                <? endforeach ?>
            <?php endif ?>
          <div>
              <?= \CloudControl\Cms\util\Cms::newDocument() ?>
          </div>
        </nav>
      </div>
      <main class="mdl-layout__content">
        <div class="demo-container mdl-grid">
          <div class="mdl-cell mdl-cell--2-col mdl-cell--hide-tablet mdl-cell--hide-phone"></div>
          <div class=" mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--8-col" style="padding:2em;">
            <!-- This is where the main content of the found document is shown -->
              <?php if (isset($document) && $document !== false) : ?>
                <h1>
                    <?= \CloudControl\Cms\util\Cms::editDocument($document->path) ?>
                    <?= $document->title ?>
                </h1>
                <p><?= $document->fields->text[0] ?></p>
              <?php else : ?>
                <h1>Welcome to Cloud Control!</h1>
                <p>This is the default home page.</p>
                  <?= \CloudControl\Cms\util\Cms::newDocument() ?>
              <?php endif ?>
          </div>
        </div>
      </main>
    </div>

    <script src="<?= $request::$subfolders ?>js/site.js"></script>
  </body>
</html>