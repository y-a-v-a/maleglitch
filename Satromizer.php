<?php
/**
 * Create glitched images
 * @author Vincent Bruijn <vebruijn@gmail.com>
 */
class Satromizer {
	private $chunks = 7;
	
	private $chunk_size_min = 32;
	
	private $chunk_size_max = 64;
	
	private $orig_img = null;
	
	private $tmp_img = null;
	
	private $def_img = null;
	
	public function __construct($file = '') {
		$this->orig_img = @file_get_contents($file);
		if ($this->orig_img == false) {
			throw new Exception('Cannot get file contents.');
		}
		return $this;
	}
	
	public function show() {
		// trick to strip all kinds of headers from a jpeg file
		$img = @imagecreatefromstring($this->orig_img);
		ob_start();
		// header('Content-type: image/jpeg');
	    imagejpeg($img, null, 80);
		// die;
	    $i = ob_get_clean();

		$this->tmp_img = $i;
		$img_size = strlen($this->tmp_img);
		
		$parts = $this->chunkValues($img_size);
		$parts_tot = count($parts);
		
		// chop original image in pieces
		$tmp = array();
		while(!is_null($key = key($parts))) {
			$curr = current($parts);
			$next = next($parts);
			$tmp[] = substr($this->tmp_img, $curr, $next - $curr);
			if ($next == $parts[$parts_tot - 1]) {
				break;
			}
		}

		// create some addition / repetitive image data
		$additions = array();
		for ($i = 1; $i < $this->chunks; $i++) {
			$rs = array();
			for($j = 0; $j < 2; $j++) {
				$rs[] = rand($this->chunk_size_min, $this->chunk_size_max);
			}
			sort($rs);
			$h = $img_size / rand(2,4);
			$add = substr($this->tmp_img,$h - $rs[0], $h - $rs[1]);
			$additions[] = $add;
		}

		// merge arrays
		$arr = array();
		foreach($tmp as $k=>$t) {
			$arr[] = $t;
			if (isset($additions[$k])) {
				$arr[] = $additions[$k];
			}
		}
		// create one big string
		$tmp = implode('',$arr);

		$this->def_img = @imagecreatefromstring($tmp);
		// header('Content-type: image/jpeg');
		
		ob_start();
		imagejpeg($this->def_img, null, 50);
		$this->def_img = ob_get_clean();
		// exit;
		return $this->def_img;
	}
	
	/**
	 * Export satromized image to jpg file
	 * @param string directory to write to.
	 */
	public function export($target = '') {
		if ($this->def_img != null) {
			$saved = @file_put_contents($target . 'img' . time() .'.jpg', $this->def_img);
			if ($saved == false) {
				throw new Exception('Cannot write image. Permissions issue?');
			}
		}
	}
	
	/**
	 * Create array of x elements with random values
	 * @param int
	 * @return array
	 */
	private function chunkValues($strlen) {
		$parts = array();
		for ($i = 1; $i < $this->chunks; $i++) {
			$parts[] = rand(0,$strlen);
		}
		sort($parts);
		array_unshift($parts,0);
		array_push($parts,$strlen);
		return $parts;
	}
}
