<?php
/**
 * Create glitched images
 * @author Vincent Bruijn <vebruijn@gmail.com>
 */
class Satromizer {
	private $chunks = 12;
	
	private $chunk_size_min = 8;
	
	private $chunk_size_max = 32;
	
	private $orig_img = null;
	
	private $tmp_img = null;
	
	private $def_img = null;
	
	private $parts;
	
	private $debug = array();
	
	public function __construct($file = '') {
		$this->time = time();
		$this->debug['file'] = $file;
		$this->debug['time'] = $this->time;
		
		$this->orig_img = @file_get_contents($file);
		if ($this->orig_img == false) {
			throw new Exception('Cannot get file contents.');
		}
		return $this;
	}
	
	/**
	 * @return resource image
	 */
	public function show() {
		header('Content-type: image/jpeg');
		return $this->def_img;
	}
	
	/**
	 * Build a glitched image
	 * @return object $this
	 */
	public function build() {
		$this->tmp_img = $this->stripHeaders();
		$this->parts = $this->chunkValues();

		$image_parts = $this->getImageInParts();
		$additions = $this->getAdditions();

		// merge arrays
		$arr = array();$i = 0;
		foreach($image_parts as $k => $t) {
			$arr[$i++] = $t;
			if (isset($additions[$k])) {
				$arr[$i++] = $additions[$k];
			}
		}
		// create one big string
		$tmp = implode('',$arr);

		$this->def_img = @imagecreatefromstring($tmp);
		$this->debug['success'] = 'true';
		unset($tmp);
		if ($this->def_img === false) {
			header('Content-type: text/html');
			$this->debug['success'] = 'false';
			// var_dump($this->debug);
			$this->def_img = @imagecreatetruecolor(300,300);
		}
		// header('Content-type: image/jpeg');
		
		ob_start();
		$is_img = @imagejpeg($this->def_img, null, 50);
		if ($is_img == false) {
			throw new Exception('Cannot create an image.');
		}
		$this->def_img = ob_get_clean();
		// exit;
		$this->doDebug();
		return $this;
	}
	
	/**
	 * Export satromized image to jpg file
	 * @param string directory to write to.
	 * @return object $this
	 */
	public function export($target = '') {
		if ($this->def_img != null) {
			$saved = @file_put_contents($target . 'img' . $this->time .'.jpg', $this->def_img);
			if ($saved == false) {
				throw new Exception('Cannot write image. Permissions issue?');
			}
		}
		return $this;
	}
	
	/**
	 * @return resource image
	 */
	private function stripHeaders() {
		// trick to strip all kinds of headers from a jpeg file
		$img = @imagecreatefromstring($this->orig_img);
		ob_start();
	    $is_img = @imagejpeg($img, null, 80);
		if ($is_img === false) {
			throw new Exception('Cannot strip headers from image.');
		}
	    $img_buffer = ob_get_clean();
		imagedestroy($img);
		return $img_buffer;
	}
	
	/**
	 * Chop original image in pieces
	 * @return array
	 */
	private function getImageInParts() {
		$parts_tot = count($this->parts);
		$tmp = array();
		while(!is_null($key = key($this->parts))) {
			$curr = current($this->parts);
			$next = next($this->parts);
			$tmp[] = substr($this->tmp_img, $curr, $next - $curr);
			if ($next == $this->parts[$parts_tot - 1]) {
				break;
			}
		}
		return $tmp;
	}
	
	private function getAdditions() {
		// create some additional / repetitive image data
		$additions = array();
		$k = 1;
		$this->debug['strlen'] = strlen($this->tmp_img);
		for ($i = 1; $i < $this->chunks; $i++) {
			$rs = array();
			for($j = 0; $j < 2; $j++) {
				$rs[] = rand($this->chunk_size_min, $this->chunk_size_max);
			}
			sort($rs);
			$start = $this->parts[$k++] - ($rs[0] + $rs[1]);
			$start = $start < 0 ? 0 : $start;
			$end = $rs[0] + $rs[1];
			$end = $start + $end > strlen($this->tmp_img) ? 0 : $end;
			$add = substr($this->tmp_img, $start, $end);
			$this->debug[$start] = $end;

			$this->parts;
			$additions[] = $add;
		}
		return $additions;
	}
	
	/**
	 * Create array of x elements with random values
	 * @param int
	 * @return array
	 */
	private function chunkValues() {
		$strlen = strlen($this->tmp_img);
		$parts = array();
		for ($i = 1; $i < $this->chunks; $i++) {
			$parts[] = rand(0,$strlen);
		}
		sort($parts);
		array_unshift($parts,0); // add 0 as minimum
		array_push($parts,$strlen); // add strlen as max
		return $parts;
	}
	
	private function log($msg) {
		@file_put_contents('imgs/log.txt',$msg,FILE_APPEND);
	}
	
	public function __toString() {
		return "Cannot convert image to string";
	}
	
	private function doDebug() {
		ob_start();
		print_r($this->debug);
		$msg = ob_get_clean();
		$this->log($msg);
	}
	
	public function __destruct() {
		@imagedestroy($this->orig_img);
		@imagedestroy($this->tmp_img);
		@imagedestroy($this->def_img);
	}
}
