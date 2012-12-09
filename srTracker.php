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
		
		$connection = DBConnection::generateMysqli($this->host, $this->username, $this->password, $this->database);
		if($connection === FALSE) {
			throw new Exception('[User-error]Wrong parameter: please make sure that hostname, username, password and database values are correct.');
		}
		else {
			if(DBConnection::generateTables($connection) === FALSE) {
				throw new Exception('[api-error]Could not generate tables.');
			}
		}
	}	
	
	//Functions
	
	/** Private function for getting wanted itemtype
	* @param string - $item
	* @return array or false
	 */
	private function getItems($item) {
		//DBConnection
		$connection = DBConnection::generateMysqli($this->host, $this->username, $this->password, $this->database);
		//Todays date
		$now = date('Y-m-d');			
		//Firsttime run
		$sql = "SELECT date FROM lastUpdate WHERE item = (?)";
		$stmt = $connection->prepare($sql);
		$stmt->bind_param('s', $item);
		$stmt->execute();
		$result = $stmt->get_result();
		if(mysqli_num_rows($result) < 1) {	
			//Get html with cURL
			$html =	DataHandler::getHtml();
			//Create new domDocument for making it more manageable
			$dom = new DOMDocument();
			@$dom->loadHTML($html);
			$nodes = $dom->getElementsByTagName('*');
			$items = DataHandler::extractData($nodes, $item);	
			if(DBConnection::insertItemsIntoDB($item, $items, $connection) !== FALSE) {
				$query = "INSERT INTO lastUpdate VALUES (?, ?, ?)";
				$statement = $connection->prepare($query);
				$statement->bind_param('iss', $id, $item, $date);
				$id = "";
				$date = (string)$now;
				if(!$statement->execute()) {
				//	$statement->close();			
				//	$connection->close();
					return FALSE;
				}
			}
			else {
				return FALSE;
			}
			//Fetch from DB
			$resultat = DBConnection::getFromDB($item, $connection);
			//$connection->close();
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
			while($row = $result->fetch_assoc()) {
				$oldDate = date('Y-m-d', strtotime($row['date']));

				if($now > $oldDate) {
					$html = DataHandler::getHtml();
					$dom = new DOMDocument();
					@$dom->loadHTML($html);
					$nodes = $dom->getElementsByTagName('*');
					$items = DataHandler::extractData($nodes, $item);	
					//Clear old data
					$sql = "TRUNCATE TABLE ".$item;
					$connection->query($sql);
					//Insert new data
					if(DBConnection::insertItemsIntoDB($item, $items, $connection) !== FALSE) {
						$query = "UPDATE lastUpdate SET date = (?) WHERE item = (?)";
						$statement = $connection->prepare($query);
						$statement->bind_param('ss', $date, $item);
						$date = (string)$now;
						if(!$statement->execute()) {
						//	$statement->close();			
						//	$connection->close();
							return FALSE;
						}
					}
					$resultat = DBConnection::getFromDB($item, $connection);
					//$connection->close();
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
					//$connection->close();
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
				throw new Exception('[api-error]Error getting CDs');
			}
		}
		//LP
		else if (($item == 'LP') || ($item == 'lp')) {
			$result = $this->getItems('lp');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('[api-error]Error getting LPs');
			}
		}
		//TS
		else if (($item == 'TS') || ($item == 'ts')) {
			$result = $this->getItems('ts');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('[api-error]Error getting TSHIRTs');
			}
		}
		//LS
		else if (($item == 'LS') || ($item == 'ls')) {
			$result = $this->getItems('ls');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('[api-error]Error getting LONGSLEEVEs');
			}
		}
		//HD
		else if (($item == 'HD') || ($item == 'hd')) {
			$result = $this->getItems('hood');
			if($result !== FALSE) {
				return $result;
			}
			else {
				throw new Exception('[api-error]Error getting HOODs');
			} 
		}
		else {
			throw new Exception('[User-error]Wrong parameter, getAll-function: Only "cd", "ts", "lp", "ls" and "hd" allowed.');
		}
	}
}
?>