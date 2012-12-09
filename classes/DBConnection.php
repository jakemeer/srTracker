<?php
class DBConnection {
	/** Public static function for generating mysqli
	* @param string - $hostname
	* @param string - $username
	* @param string - $password
	* @param string - $database
	* @return MySQLi link identifier or false
	 */
	public static function generateMysqli($host, $username, $password, $database) {
		$connection = new mysqli($host, $username, $password, $database);
		if(mysqli_connect_errno()) {
			return FALSE;
		}
		$connection->set_charset("utf8");
		return $connection;
	}
	/** Public static function for inserting items into database
	* @param string - $table
	* @param array - $items
	* @param MySQLi link identifier - $connection
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
			//Fix for potential singlequotes in the title
			if(strpos($title, "'") !== false) {
				$title = str_replace("'", "''", $title);
			}
			$sql = "INSERT INTO ".$table." VALUES (?, ?, ?, ?)";
			$stmt = $connection->prepare($sql);
			$stmt->bind_param('isss', $id, $title, $url, $img);
			$id = "";
			if(!$stmt->execute()) {
				//$stmt->close();			
				//$connection->close();
				return FALSE;
			}
			$stmt->free_result();
		}	
	}
	/** Public static function for creating necessary tables
	* @param MySQLi link identifier - $connection
	* @return void or false
	 */
	public static function generateTables($connection) {
		//Tables exists
		if($result = $connection->query("SELECT * FROM cd")) {
			return TRUE;
		}
		//Tables doesnt exist
		else {
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
			$connection->query($sqlcd);
			$connection->query($sqllp);
			$connection->query($sqlls);
			$connection->query($sqlts);
			$connection->query($sqlhd);
			$connection->query($sqllu);
		}
	}
	/** public static function for fetching from database
	* @param string - $table
	* @param MySQLi link identifier - $connection
	* @return array or false
	 */
	public static function getFromDB($table, $connection) {
		$resultat = array();
	
		$sql = "SELECT * FROM ".$table;
		$result = $connection->query($sql);
		while($row = $result->fetch_array(MYSQLI_ASSOC)) {
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