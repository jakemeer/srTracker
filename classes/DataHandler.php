<?php
class DataHandler {
/** Public static function for scraping html
	* @return result or false
	 */
	public static function getHtml() {
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
	/** Public static function for getting wanted itemtype $item from DOMNodes
	* @param DOMNodeList - $nodes
	* @param string - $item
	* @return array or false
	 */
	public static function extractData($nodes, $item) {
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
				//If img-tag has class with name gridArticleImage or if class is empty
				if((($node->hasAttribute('class')) && ($node->getAttribute('class') == 'gridArticleImage')) || 
				(($node->hasAttribute('class')) && ($node->getAttribute('class') == ''))) {
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
}
?>