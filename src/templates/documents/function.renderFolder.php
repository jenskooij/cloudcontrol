<?php
/**
 * @param \CloudControl\Cms\storage\entities\Document $document
 * @param string $path
 * @param CloudControl\Cms\cc\Request $request
 * @param string $cmsPrefix
 */
function renderFolder($document, $path, $request, $cmsPrefix)
{
    $folderPath = $path . ($path === '/' ? '' : '/') . $document->slug;
    $folderSlug = substr($path, 1);
    $openFolderLink = '?path=' . $folderPath;

    $deleteFolderLink = $request::$subfolders . $cmsPrefix . '/documents/delete-folder?slug=' . $folderSlug . ($path === '/' ? '' : '/') . $document->slug;
    ?>
  <td class="icon" title="<?= $document->type ?>">
    <i class="fa fa-folder-o"></i>
  </td>
  <td class="icon"></td>
  <td>
    <a href="<?= $openFolderLink ?>"><?= $document->title ?></a>
  </td>
  <td class="icon context-menu-container">
    <div class="context-menu">
      <i class="fa fa-ellipsis-v"></i>
      <ul>
        <li>
          <a href="<?= $deleteFolderLink ?>" onclick="return confirm('Are you sure you want to delete this folder?');">
            <i class="fa fa-trash"></i>
            Delete
          </a>
        </li>
      </ul>
    </div>
  </td>
<?php } ?>