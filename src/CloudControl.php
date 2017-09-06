<?php
/**
 * Created by jensk on 14-8-2017.
 */

namespace CloudControl\Cms;

use CloudControl\Cms\cc\Application;
use CloudControl\Cms\storage\Repository;
use Composer\Script\Event;

class CloudControl
{
    /**
     * @param string $rootDir
     * @param string $configPath
     */
    public static function run($rootDir, $configPath)
    {
        new Application($rootDir, $configPath);
    }

    public static function postInstall(Event $event)
    {
        $event->getIO()->write("Post install");
        self::checkInstall($event);
    }

    public static function postUpdate(Event $event)
    {
        $event->getIO()->write("Post update");
        self::checkInstall($event);
    }

    /**
     * @param Event $event
     */
    private static function checkInstall(Event $event)
    {
        $event->getIO()->write("");
        $event->getIO()->write("********************************************************");
        $event->getIO()->write("*** Checking installation of Cloud Control framework ***");
        $event->getIO()->write("********************************************************");
        $event->getIO()->write("");

        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $rootDir = realpath($vendorDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

        $baseConfigTargetPath = $rootDir . DIRECTORY_SEPARATOR . 'config.json';
        $configObject = self::getConfig($event, $baseConfigTargetPath);

        $configObject->{'vendorDir'} = realpath($vendorDir);
        $configObject->{'templateDir'} = self::createDir($event, $rootDir, 'templates');
        $configObject->{'storageDir'} = self::createDir($event, $rootDir, $configObject->{'storageDir'});
        $configObject->{'publicDir'} = self::createDir($event, $rootDir, 'public');
        $configObject->{'jsDir'} = self::createDir($event, $rootDir, $configObject->{'publicDir'} . 'js');
        $configObject->{'cssDir'} = self::createDir($event, $rootDir, $configObject->{'publicDir'} . 'css');
        $configObject->{'imagesDir'} = self::createDir($event, $rootDir, $configObject->{'publicDir'} . 'images');
        $configObject->{'filesDir'} = self::createDir($event, $rootDir, $configObject->{'publicDir'} . 'files');

        $baseStorageDefaultPath = __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . '_storage.json';
        $baseStorageSqlPath = __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . '_storage.sql';

        self::createStorage($configObject->{'storageDir'}, $baseStorageDefaultPath, $baseStorageSqlPath);
        self::saveConfig($event, $baseConfigTargetPath, $configObject);
        self::copyInstallFile($event, 'public.htaccess', $configObject->{'publicDir'}, '.htaccess');
        self::copyInstallFile($event, 'root.htaccess', $rootDir, '.htaccess');
        self::copyInstallFile($event, 'base.php', $configObject->{'templateDir'});
        self::copyInstallFile($event, 'cms.css', $configObject->{'cssDir'});
        self::copyInstallFile($event, 'cms.js', $configObject->{'jsDir'});
        self::copyInstallFile($event, 'index.php', $configObject->{'publicDir'});

        $event->getIO()->write("");
        $event->getIO()->write("[SUCCESS] Installation is complete");
        $event->getIO()->write("");
    }

    /**
     * @param Event $event
     * @param $baseConfigTargetPath
     * @param $configObject
     */
    private static function saveConfig(Event $event, $baseConfigTargetPath, $configObject)
    {
        file_put_contents($baseConfigTargetPath, json_encode($configObject));
        $event->getIO()->write("[INFO] Saved config to: " . $baseConfigTargetPath);
    }

    private static function copyInstallFile(Event $event, $sourceFileName, $destinationPath, $destinationFileName = null)
    {
        $sourceFilePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'install/_' . $sourceFileName);


        if ($destinationFileName === null) {
            $destinationFileName = $sourceFileName;
        }

        if (file_exists($sourceFilePath) && realpath($destinationPath) !== false) {
            $destinationFullPath = realpath($destinationPath) . DIRECTORY_SEPARATOR . $destinationFileName;
            if (file_exists($destinationFullPath)) {
                $event->getIO()->write("[INFO] File already exists: " . $destinationFullPath);
            } else {
                copy($sourceFilePath, $destinationFullPath);
                $event->getIO()->write("[INSTALL] Copied file: " . $sourceFileName . ' to ' . $destinationPath);
            }
        } else {
            $event->getIO()->write("[ERROR] Couldnt copy file: " . $sourceFileName . ' to ' . $destinationPath);
        }
    }

    /**
     * @param $storageDir
     * @param $baseStorageDefaultPath
     * @param $baseStorageSqlPath
     */
    private static function createStorage($storageDir, $baseStorageDefaultPath, $baseStorageSqlPath)
    {
        $repository = new Repository($storageDir);
        $repository->init($baseStorageDefaultPath, $baseStorageSqlPath);
    }

    private static function createDir(Event $event, $rootDir, $dirName)
    {
        $dir = $rootDir . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir);
            $event->getIO()->write("[INSTALL] Created dir: " . $dir);
        } else {
            $event->getIO()->write("[INFO] Dir already exists: " . $dir);
        }
        return self::getRelativePath($rootDir, $dir);
    }

    /**
     * @param $configTargetPath
     * @return mixed
     */
    private static function getConfig(Event $event, $configTargetPath)
    {
        $baseConfigDefaultPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . '_config.json');

        if (file_exists($configTargetPath)) {
            $config = json_decode(file_get_contents($configTargetPath));
            $event->getIO()->write("[INFO] Using existing config");
        } else {
            $config = json_decode(file_get_contents($baseConfigDefaultPath));
            $event->getIO()->write("[INSTALL] Created default config");
        }
        return $config;
    }

    /**
     * Calculate the relative path from $from to $to
     * Derived from https://stackoverflow.com/a/2638272/
     *
     * @param $from
     * @param $to
     * @return string
     */
    private static function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . DIRECTORY_SEPARATOR : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . DIRECTORY_SEPARATOR : $to;
        $from = str_replace('\\', DIRECTORY_SEPARATOR, $from);
        $to = str_replace('\\', DIRECTORY_SEPARATOR, $to);

        $from = explode(DIRECTORY_SEPARATOR, $from);
        $to = explode(DIRECTORY_SEPARATOR, $to);
        $relPath = $to;

        $relPath = self::calculateRelativePath($from, $to, $relPath);
        $relPath = implode(DIRECTORY_SEPARATOR, $relPath);
        $relPath = self::removePointerToCurrentDir($relPath);
        return $relPath;
    }

    /**
     * @param $relPath
     * @return mixed
     */
    private static function removePointerToCurrentDir($relPath)
    {
        while (strpos($relPath, '.' . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR) !== false) {
            $relPath = str_replace('.' . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR, '.' . DIRECTORY_SEPARATOR, $relPath);
        }
        return $relPath;
    }

    /**
     * @param $from
     * @param $to
     * @param $relPath
     * @return array
     */
    private static function calculateRelativePath($from, $to, $relPath)
    {
        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = '.' . DIRECTORY_SEPARATOR . $relPath[0];
                }
            }
        }
        return $relPath;
    }

}