<section class="dashboard search documents">
  <h2>
    <i class="fa fa-search"></i>
    Search
  </h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn<?php if (!$searchNeedsUpdate) : ?> reset<?php endif ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search/update-index" title="Update Index">Update Index</a>
      </li>
    </ul>
  </nav>
    <?php if ($searchNeedsUpdate) : ?>
      <div class="message warning">
        <i class="fa fa-exclamation-triangle"></i>
        Search index is no longer in sync with documents.
      </div>
    <?php else : ?>
      <div class="message valid">
        <i class="fa fa-check"></i>
        Search index is in sync with documents.
      </div>
    <?php endif ?>
  <table class="documents">
    <tr>
      <th>Query</th>
      <th>Conversion</th>
      <th>Last Action</th>
      <th>Results</th>
    </tr>
      <?php foreach ($searchAnalysis as $row) : ?>
        <tr>
          <td>
              <?= $row->query ?>
            <div class="details">
              <table>
                <tr>
                  <td>de rest</td>
                </tr>
              </table>
            </div>
          </td>
          <td>
            <?=$row->conversion?>
          </td>
          <td>
            <?=\CloudControl\Cms\util\StringUtil::timeElapsedString($row->timestamp)?> ago
          </td>
          <td>
              <?php if ($row->resultCount === '0') : ?>
                <i class="fa fa-warning"></i>
              <?php endif ?>
              <?= $row->resultCount ?>
          </td>
          <td class="icon context-menu-container">
            <div class="context-menu">
              <i class="fa fa-ellipsis-v"></i>
              <ul>
                <li>
                  <a href="#" onclick="return showDocumentDetails(this);">
                    <i class="fa fa-list-alt"></i>
                    Details
                  </a>
                </li>
              </ul>
            </div>
          </td>
        </tr>
      <?php endforeach ?>
  </table>
</section>