<?php

/**
 * save a map to a local file.
 *
 *
 */

class StaticMapSaverForHTTPS extends Object
{
    private static $save_dir = "assets";

    private static $overwrite = false;

    /**
     * @param String $url
     * @param String $filename
     */
    public function convert_to_local_file($url, $filename)
    {
        $fileFolder = self::$save_dir.'/'.$filename;
        $target = Director::baseFolder().'/'.$fileFolder;
        if (file_exists($target) && !self::$overwrite) {
            return $fileFolder;
        }
        $fh = fopen($target, 'w');
        $check = fwrite($fh, file_get_contents($url));
        fclose($fh);
        if ($check) {
            return $fileFolder;
        }
    }
}
