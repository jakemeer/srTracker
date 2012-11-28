<?php
require_once('interface/isrtracker.php');
require_once('classes/Item.php');
require_once('classes/DBConnection.php');
require_once('classes/DataHandler.php');

class srTracker implements iSrTracker{
	//Private members
	private $host;
	private $username;
	private $password;
	private $database;

	function __construct($host, $username, $password, $database) {
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		
		$connection = DBConnection::generateMysql($this->host, $this->username, $this->password, $this->database);
		if($connection === FALSE) {
			throw new Exception('Wrong parameter');
		}
		else {
			DBConnection::generateTables($connection);
		}
	}	
	
	//Functions
	
	/** Private function for getting wanted itemtype
	* @param string - $item
	* @return array or false
	 */
	private function getItems($item) {
		//DBConnection
		$connection = DBConnection::generateMysql($this->host, $this->username, $this->password, $this->database);
		//Todays date
		$now = date('Y-m-d');			
		//Firsttime run
		$sql = "SELECT date FROM lastUpdate WHERE item='".$item."'";
		$result = mysql_query($sql, $connection);
		
		if(mysql_num_rows($result) < 1) {	
			//Get html with cURL
			$html =	DataHandler::getHtml();
			//Create new domDocument for making it more manageable
			$dom = new DOMDocument();
			@$dom->loadHTML($html);
			$nodes = $dom->getElementsByTagName('*');
			$items = DataHandler::extractData($nodes, $item);	
			if(DBConnection::insertItemsIntoDB($item, $items, $connection) !== FALSE) {
				$sql = "INSERT INTO lastUpdate VALUES ('', '".$item."', '".(string)$now."')";
				mysql_query($sql, $connection);
			}
			else {
				return FALSE;
			}
			//Fetch from DB
			$resultat = DBConnection::getFromDB($item, $connection);
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
					$html = DataHandler::getHtml();
					$dom = new DOMDocument();
					@$dom->loadHTML($html);
					$nodes = $dom->getElementsByTagName('*');
					$items = DataHandler::extractData($nodes, $item);	
					//Clear old data
					$sql = "TRUNCATE TABLE ".$item;
					mysql_query($sql, $connection);
					//Insert new data
					if(DBConnection::insertItemsIntoDB($item, $items, $connection) !== FALSE) {
						$sql = "UPDATE lastUpdate SET date='".(string)$now."' WHERE item='".$item."'";
						mysql_query($sql, $connection);
					}

					$resultat = DBConnection::getFromDB($item, $connection);
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

					$resultat = DBConnection::getFromDB($item, $connection);
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
	/** Public function for getting wanted itemtype 
	* @param string - $item
	* @throw Exception
	* @return array 
	 */
	public function getAll($item) {
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