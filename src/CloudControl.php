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
    public static function run()
    {
        new Application();
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
        $event->getIO()->write("*** Checking installation of Cloud Control framework ***");

        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $rootDir = realpath($vendorDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

        $baseConfigTargetPath = $rootDir . DIRECTORY_SEPARATOR . 'config.json';
        $configObject = self::getConfig($baseConfigTargetPath);

        $configObject->{'vendorDir'} = realpath($vendorDir);
        $configObject->{'rootDir'} = $rootDir;
        $configObject->{'templateDir'} = self::createDir($event, $rootDir, 'templates');
        $configObject->{'storageDir'} = self::createDir($event, $rootDir, $configObject->{'storageDir'});
        $configObject->{'publicDir'} = self::createDir($event, $rootDir, 'public');
        $configObject->{'jsDir'} = self::createDir($event, $configObject->{'publicDir'}, 'js');
        $configObject->{'cssDir'} = self::createDir($event, $configObject->{'publicDir'}, 'css');
        $configObject->{'imagesDir'} = self::createDir($event, $configObject->{'publicDir'}, 'images');
        $configObject->{'filesDir'} = self::createDir($event, $configObject->{'publicDir'}, 'files');

        $baseStorageDefaultPath = __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . '_storage.json';
        $baseStorageSqlPath = __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . '_storage.sql';

        self::createStorage($configObject->{'storageDir'}, $baseStorageDefaultPath, $baseStorageSqlPath);
        self::saveConfig($event, $baseConfigTargetPath, $configObject);
        self::copyInstallFile($event, 'public.htaccess', $configObject->{'publicDir'}, '.htaccess');
        self::copyInstallFile($event, 'root.htaccess', $configObject->{'rootDir'}, '.htaccess');
        self::copyInstallFile($event, 'base.php', $configObject->{'templateDir'});
        self::copyInstallFile($event, 'cms.css', $configObject->{'cssDir'});
        self::copyInstallFile($event, 'cms.js', $configObject->{'jsDir'});
        self::copyInstallFile($event, 'index.php', $configObject->{'publicDir'});
    }

    /**
     * @param Event $event
     * @param $baseConfigTargetPath
     * @param $configObject
     * @internal param $rootDir
     * @internal param $vendorDir
     * @internal param $templateDir
     * @internal param $storageDir
     * @internal param $baseConfigDefaultPath
     * @internal param $baseConfigTargetPath
     * @internal param $storageDir
     */
    private static function saveConfig(Event $event, $baseConfigTargetPath, $configObject)
    {
        file_put_contents($baseConfigTargetPath, json_encode($configObject));
        $event->getIO()->write("Saved config to: " . $baseConfigTargetPath);
    }

    private static function copyInstallFile(Event $event, $sourceFileName, $destinationPath, $destinationFileName = null)
    {
        $sourceFilePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'install/_' . $sourceFileName);

        if (file_exists($sourceFilePath) && realpath($destinationPath) !== false) {
            if ($destinationFileName !== null) {
                copy($sourceFilePath, realpath($destinationPath) . DIRECTORY_SEPARATOR . $destinationFileName);
            } else {
                copy($sourceFilePath, realpath($destinationPath) . DIRECTORY_SEPARATOR . $sourceFileName);
            }

            $event->getIO()->write("Copied file: " . $sourceFileName . ' to ' . $destinationPath);
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
            $event->getIO()->write("Created dir: " . $dir);
        }
        return realpath($dir);
    }

    /**
     * @param $configTargetPath
     * @return mixed
     */
    private static function getConfig($configTargetPath)
    {
        $baseConfigDefaultPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'install/_config.json');

        if (file_exists($configTargetPath)) {
            $config = json_decode(file_get_contents($configTargetPath));
        } else {
            $config = json_decode(file_get_contents($baseConfigDefaultPath));
        }
        return $config;
    }

}