<td class="icon" title="<?= $document->type ?>">
    <i class="fa fa-folder-o"></i>
</td>
<td class="icon"></td>
<td>
    <a href="<?= openFolderLink($path, $document) ?>"><?= $document->title ?></a>
</td>
<td class="icon context-menu-container">
    <div class="context-menu">
        <i class="fa fa-ellipsis-v"></i>
        <ul>
            <li>
                <a href="<?= getDeleteFolderLink($request, $cmsPrefix, $path, $document) ?>"
                   data-confirm="Are you sure you want to delete the folder '<?= $document->title ?>'?"
                   data-confirm-text="Delete"
                   data-decline-text="Cancel">
                    <i class="fa fa-trash"></i>
                    Delete
                </a>
            </li>
        </ul>
    </div>
</td>