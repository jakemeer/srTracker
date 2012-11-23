<?php
//Helper class
class Item {
	private $title;
	private $url;
	private $img;
	
	function __construct($titles, $urls, $imgs) {
		$this->title = $titles;
		$this->url = $urls;
		$this->img = $imgs;
	}
	
	public function Title() {
		return $this->title;
	}
	public function Url() {
		return $this->url;
	}
	public function Img() {
		return $this->img;
	}
}
//srTracker class
class srTracker {
	private $host;
	private $username;
	private $password;
	private $database;

	function __construct($host, $username, $password, $database) {
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		
		$connection = $this->generateMysql($this->host, $this->username, $this->password, $this->database);
		if($connection === FALSE) {
			throw new Exception('Wrong parameter');
		}
		else {
			$this->generateTables($connection);
		}
	}
	
	//Methods
	function generateMysql($host, $username, $password, $database) {

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
	function insertItemsIntoDB($table, $items, $connection) {
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
	function generateTables($connection) {
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
	function getHtml() {
		$url = 'http://www.swedrock.se/';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 2);
		$html = curl_exec($curl);
		//converts html-entiteter to utf-8
		$html = @mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8'); 
		curl_close($curl);
		return $html;		
	}
	function getFromDB($table) {
		$resultat = array();
		
		$connection = $this->generateMysql($this->host, $this->username, $this->password, $this->database);
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
	function extractData($nodes, $item) {
		//Variables
		$titles = array();
		$urls = array();
		$images = array();
		$result = array();
		
		//Get information about title and url
		foreach($nodes as $node) {
			//a-tag
			if($node->nodeName == 'a') {
				//If a-tag has class with name gridArticleName
				if(($node->hasAttribute('class')) && ($node->getAttribute('class') == 'gridArticleName')) {
					//Get type (cd/lp/ts/ls/hd)
					$explode = explode('[', $node->nodeValue);
					//Remove unwanted letter
					$typ = substr_replace($explode[1] ,"",-1);											
					//Check if its the item we wanted
					if(strpos(strtolower($typ), $item) !== false) {
						//Save all titles to array
						array_push($titles, $node->nodeValue);
						//Save all urls to array
						array_push($urls, "http://www.swedrock.se".$node->getAttribute('href'));
					}
				}
			}							 						
		}
		//Get information about img
		foreach($nodes as $node) {
			//img-tag
			if($node->nodeName == 'img') {
				//If img-tag has class with name gridArticleImage
				if(($node->hasAttribute('class')) && ($node->getAttribute('class') == 'gridArticleImage')) {
					//Store correct img-url depending on titles array
					foreach($titles as $title) {
						//If title is the same as the alt-attribute
						if($title == $node->getAttribute('alt')) {
							//Save to array
							array_push($images, $node->getAttribute('src'));
						}
					}
				}
			}							 						
		}
		//If result is given return it as array
		if(!empty($titles)) {
			for($i = 0; $i < count($titles); $i++) {
				$item = new Item($titles[$i], $urls[$i], $images[$i]);
				array_push($result, $item);
			}
			return $result;
		}
		else {
			return FALSE;
		}
	}
	function getItems($item) {
		//DBConnection
		$connection = $this->generateMysql($this->host, $this->username, $this->password, $this->database);
		//Todays date
		$now = date('Y-m-d');			
		//Firsttime run
		$sql = "SELECT date FROM lastUpdate WHERE item='".$item."'";
		$result = mysql_query($sql, $connection);
		
		if(mysql_num_rows($result) < 1) {	
			//Get html with cURL
			$html = $this->getHtml();
			//Create new domDocument for making it more manageable
			$dom = new DOMDocument();
			@$dom->loadHTML($html);
			$nodes = $dom->getElementsByTagName('*');
			$items = $this->extractData($nodes, $item);	
			if($this->insertItemsIntoDB($item, $items, $connection) !== FALSE) {
				$sql = "INSERT INTO lastUpdate VALUES ('', '".$item."', '".(string)$now."')";
				mysql_query($sql, $connection);
			}
			else {
				return FALSE;
			}
			//Fetch from DB
			$resultat = $this->getFromDB($item);
			mysql_close($connection);
			//Errorhandling
			if($resultat!== FALSE) {
				return $resultat;
			}
			else {
				return FALSE;
			}
		}
		//If its not firsttime run
		else {
			//Check if its another date than todays (in that case update DB)
			while($row = mysql_fetch_array($result)) {
				$oldDate = date('Y-m-d', strtotime($row['date']));

				if($now > $oldDate) {
					$html = $this->getHtml();
					$dom = new DOMDocument();
					@$dom->loadHTML($html);
					$nodes = $dom->getElementsByTagName('*');
					$items = $this->extractData($nodes, $item);	
					//Clear old data
					$sql = "TRUNCATE TABLE ".$item;
					mysql_query($sql, $connection);
					//Insert new data
					if($this->insertItemsIntoDB($item, $items, $connection) !== FALSE) {
						$sql = "UPDATE lastUpdate SET date='".(string)$now."' WHERE item='".$item."'";
						mysql_query($sql, $connection);
					}

					$resultat = $this->getFromDB($item);
					mysql_close($connection);

					if($resultat !== FALSE) {
						return $resultat;
					}
					else {
						return FALSE;
					}									
				}
				//If its the same day (just fetch data from DB)
				else {					

					$resultat = $this->getFromDB($item);
					mysql_close($connection);

					if($resultat !== FALSE) {
						return $resultat;
					}
					else {
						return FALSE;
					}
				}
			}
		}
	}
	function getAll($item) {
		//CD
		if(($item == 'CD') || ($item == 'cd')) {
			$result = $this->getItems('cd');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('Error getting CDs');
			}
		}
		//LP
		else if (($item == 'LP') || ($item == 'lp')) {
			$result = $this->getItems('lp');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('Error getting LPs');
			}
		}
		//TS
		else if (($item == 'TS') || ($item == 'ts')) {
			$result = $this->getItems('ts');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('Error getting TSHIRTs');
			}
		}
		//LS
		else if (($item == 'LS') || ($item == 'ls')) {
			$result = $this->getItems('ls');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('Error getting LONGSLEEVEs');
			}
		}
		//HD
		else if (($item == 'HD') || ($item == 'hd')) {
			$result = $this->getItems('hood');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('Error getting HOODs');
			} 
		}
		else {
			throw new Exception('Wrong parameter');
		}
	
	}
}
?>