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
?>