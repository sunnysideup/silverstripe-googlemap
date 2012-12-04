<?php

class BatchGeoAddressConverter extends Object {
	static function convert($tableName, $fieldName, $idFieldName) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$data = self::getDataFromTable($tableName, $fieldName, $idFieldName);
		foreach($data as $row) {
			print_r($row);
			$address = str_replace('?', " ", $row[$fieldName]);
			if($address) {
				echo "checking ".$address."<hr />";
				$newAddress = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address, true);
				if($newAddress["1"] || $newAddress[0] || $newAddress["address"]) {
					$sql = 'UPDATE {$bt}'.$tableName.'{$bt} SET {$bt}'.$fieldName.'{$bt} = "'.addslashes($newAddress["address"]).'" WHERE {$bt}'.$idFieldName.'{$bt} = "'.$row[$idFieldName].'" LIMIT 1;';
					mysql_query($sql);
					echo "<hr />".$sql."<hr />";
				}
				echo "**********************<BR />";
				$sql = "UPDATE {$bt}".$tableName."{$bt} SET {$bt}DONE{$bt} = 1 WHERE {$bt}".$idFieldName."{$bt} = '".$row[$idFieldName]."' LIMIT 1;";
				mysql_query($sql);
			}
		}
		die("test");
	}

	static function getDataFromTable($tableName, $fieldName, $idFieldName) {
		$sqlQuery = new SQLQuery();
		$sqlQuery->select = array($fieldName, $idFieldName);
		$sqlQuery->where = array("DONE <> 1");
		$sqlQuery->from = Array($tableName);
		$result = $sqlQuery->execute();
		return $result;
	}

}
