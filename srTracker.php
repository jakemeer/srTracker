<?php
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
class srTracker {
	private $host;
	private $username;
	private $password;
	private $database;

	//Konstruktor
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
	//Metoder

	function generateMysql($host, $username, $password, $database) {
		//Valt mysql eftersom ingen data tas emot från formulär etc.
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
		//Sätta in rader i tabell
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
			//Skapa tabeller
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
		//Om det redan finns tabeller gör vi inget
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
		//konverterar html-entiteter till utf-8
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
		//Variablar
		$titles = array();
		$urls = array();
		$images = array();
		$result = array();
		
		//Hämta information om titel och url
		foreach($nodes as $node) {
			//Om det är en a-tag
			if($node->nodeName == 'a') {
				//Om a tagen har en klass och det är klassen gridArticleName
				if(($node->hasAttribute('class')) && ($node->getAttribute('class') == 'gridArticleName')) {
					//Hämta typen av item (cd/lp/ts/ls/hd)
					$explode = explode('[', $node->nodeValue);
					//Ta bort ]
					$typ = substr_replace($explode[1] ,"",-1);											
					//Kolla om typen stämmer med det vi vill ha 
					if(strpos(strtolower($typ), $item) !== false) {
						//Spara alla titlar till array 'titles'
						array_push($titles, $node->nodeValue);
						//Spara alla urls till array 'urls'
						array_push($urls, "http://www.swedrock.se".$node->getAttribute('href'));
					}
				}
			}							 						
		}
		//Alt attributet i img tagen är identisk med titeln på itemet
		foreach($nodes as $node) {
			//Hämta ut img
			//Om det är en img-tag
			if($node->nodeName == 'img') {
				//Om img tagen har en klass och det är klassen gridArticleImage
				if(($node->hasAttribute('class')) && ($node->getAttribute('class') == 'gridArticleImage')) {
					//Hämta ut rätt img baserat på titles arrayen
					foreach($titles as $title) {
						//Om titeln stämmer med img tagens alt attribut
						if($title == $node->getAttribute('alt')) {
							//Spara alla img-urls till array 'images'
							array_push($images, $node->getAttribute('src'));
						}
					}
				}
			}							 						
		}
		//Returnera resultat som array (om det finns något resultat)
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
		//Dagens datum
		$now = date('Y-m-d');			
		//Om det är första gången funktionen körs
		$sql = "SELECT date FROM lastUpdate WHERE item='".$item."'";
		$result = mysql_query($sql, $connection);
		
		if(mysql_num_rows($result) < 1) {	
			//Hämta HTML med curl
			$html = $this->getHtml();
			//Skapa nytt DOM dokument för att hantera HTML-datat lättare
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
			//Hämta ur DBn
			$resultat = $this->getFromDB($item);
			mysql_close($connection);
			//Felhantering
			if($resultat!== FALSE) {
				return $resultat;
			}
			else {
				return FALSE;
			}
		}
		//Om det inte är första gången
		else {
			//Kolla om datumet är annat än dagens (isåfall vill vi uppdatera DBn)
			while($row = mysql_fetch_array($result)) {
				$oldDate = date('Y-m-d', strtotime($row['date']));

				if($now > $oldDate) {
					//Hämta HTML med curl
					$html = $this->getHtml();
					//Skapa nytt DOM dokument för att hantera HTML-datat lättare
					$dom = new DOMDocument();
					@$dom->loadHTML($html);
					$nodes = $dom->getElementsByTagName('*');
					$items = $this->extractData($nodes, $item);	
					if($this->insertItemsIntoDB($item, $items, $connection) !== FALSE) {
						$sql = "UPDATE lastUpdate SET date='".(string)$now."' WHERE item='".$item."'";
						mysql_query($sql, $connection);
					}
					//Hämta ur DBn
					$resultat = $this->getFromDB($item);
					mysql_close($connection);
					//Felhantering
					if($resultat !== FALSE) {
						return $resultat;
					}
					else {
						return FALSE;
					}									
				}
				//Om det är samma dag vill vi hämta data från DBn
				else {					
					//Hämta ur DBn
					$resultat = $this->getFromDB($item);
					mysql_close($connection);
					//Felhantering
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