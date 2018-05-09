<?php
/**
 * @param string $path
 */
function renderDocumentBreadcrumb($path)
{
    ?>
    <th colspan="4">
        <?php
        $pathParts = explode('/', $path);
        array_shift($pathParts);
        $pathPartsReconstruction = '';
        ?>
        <a href="?path=/">
            Documents
        </a>
        <?php foreach ($pathParts as $part) : ?>
            <?php if (!empty($part)) : ?>
                <?php $pathPartsReconstruction .= (substr($pathPartsReconstruction, -1) === '/' ? '' : '/') . $part ?>
                &raquo;
                <a href="?path=<?= $pathPartsReconstruction ?>">
                    <?= $part ?>
                </a>
            <?php endif ?>
        <?php endforeach ?>
    </th>
    <?php
}

?>