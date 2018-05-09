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
        $parentPath = substr($path, 0, strrpos( $path, '/'));
        if ($path !== '/' && substr_count($path, '/') === 1) {
            $parentPath = '/';
        }
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