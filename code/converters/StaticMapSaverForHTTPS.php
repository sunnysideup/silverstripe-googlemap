<?php

class StaticMapSaverForHTTPS extends Object {

	protected static $save_dir = "assets";
		static function set_save_dir($v) { self::$save_dir = $v;}

	protected static $overwrite = false;
		static function set_overwrite($v) { self::$overwrite = $v;}

	function convert_to_local_file($url, $filename) {
		$overwrite = true; //or false if image has to be renamed on duplicate
		$fileFolder = self::$save_dir.'/'.$filename;
		$target = Director::baseFolder().'/'.$fileFolder;
		if(file_exists($target) && !self::$overwrite){
			return $fileFolder;
		}
		$fh = fopen($target,'w');
		$check = fwrite($fh,file_get_contents($url));
		fclose($fh);
		if($check) {
			return $fileFolder;
		}
	}
}

