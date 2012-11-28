<?php
class DBConnection {
	/** Public static function for generating mysql
	* @param string - $hostname
	* @param string - $username
	* @param string - $password
	* @param string - $database
	* @return MySQL link identifier or false
	 */
	public static function generateMysql($host, $username, $password, $database) {

		@$connection = mysql_connect($host, $username, $password);
		if (!$connection) {
			return FALSE;
		}
		else {
			mysql_select_db($database, $connection);
			mysql_set_charset("UTF8", $connection);
			return $connection;
		}
	}
	/** Public static function for inserting items into database
	* @param string - $table
	* @param array - $items
	* @param MySQL link identifier - $connection
	* @return void or false
	 */
	public static function insertItemsIntoDB($table, $items, $connection) {
		if(empty($items) || !isset($items)) {
			return FALSE;
		}
		//Insert rows in table
		foreach($items as $item) {
			$title = $item->Title();
			$url = $item->Url();
			$img = $item->Img();

			$sql = "INSERT INTO ".$table." VALUES ('', '".$title."', '".$url."', '".$img."')";
			$result = mysql_query($sql, $connection) or die ("Error in query: $query. ".mysql_error());
			if($result === FALSE) {
				return FALSE;
			}		
		}	
	}
	/** Public static function for creating necessary tables
	* @param MySQL link identifier - $connection
	* @return void or false
	 */
	public static function generateTables($connection) {
		$sql = "SELECT * FROM cd";
		$result = mysql_query($sql, $connection);
		if(!$result) {
			//Create tables
			//cd
			$sqlcd = "CREATE TABLE cd
			(
				id int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(id),
				title varchar(150),
				url varchar(250),
				img varchar(250)
			)
			CHARSET=utf8
			";
			//lp
			$sqllp = "CREATE TABLE lp
			(
				id int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(id),
				title varchar(150),
				url varchar(250),
				img varchar(250)
			)
			CHARSET=utf8
			";
			//ls
			$sqlls = "CREATE TABLE ls
			(
				id int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(id),
				title varchar(150),
				url varchar(250),
				img varchar(250)
			)
			CHARSET=utf8
			";
			//ts
			$sqlts = "CREATE TABLE ts
			(
				id int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(id),
				title varchar(150),
				url varchar(250),
				img varchar(250)
			)
			CHARSET=utf8
			";
			//hd
			$sqlhd = "CREATE TABLE hood
			(
				id int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(id),
				title varchar(150),
				url varchar(250),
				img varchar(250)
			)
			CHARSET=utf8
			";
			//lastUpdate
			$sqllu = "CREATE TABLE lastUpdate
			(
				id int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(id),
				item varchar(15),
				date varchar(30)
			)
			CHARSET=utf8
			";
			mysql_query($sqlcd, $connection);
			mysql_query($sqllp, $connection);
			mysql_query($sqlls, $connection);
			mysql_query($sqlts, $connection);
			mysql_query($sqlhd, $connection);
			mysql_query($sqllu, $connection);
			mysql_close($connection);
		}
		//If table already exists
		else {
			mysql_close($connection);
			return FALSE;
		}
	}
	/** public static function for fetching from database
	* @param string - $table
	* @param MySQL link identifier - $connection
	* @return array or false
	 */
	public static function getFromDB($table, $connection) {
		$resultat = array();
	
		$sql = "SELECT * FROM ".$table;
		$result = mysql_query($sql, $connection);
		while($row = mysql_fetch_array($result)) {
			$item = array(
				"title" => $row['title'],
				"url" => $row['url'],
				"img" => $row['img']
			);
			array_push($resultat, $item);
		}
		if(!empty($resultat)) {
			return $resultat;
		}
		else {
			return FALSE;
		}
	}
}
?>