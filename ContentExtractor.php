<?php
/*
Copyright Ross Scrivener ross@scrivna.com
*/

class ContentExtractor {
	
	static function removeElements($elements){
		foreach ($elements as $element){ $element->parentNode->removeChild($element); }
	}
	
	static function get_inner_html($node) { 
		$innerHTML= ''; 
		$children = $node->childNodes; 
		foreach ($children as $child) { 
			$innerHTML .= $child->ownerDocument->saveXML( $child ); 
		} 
		return $innerHTML;
	}
	
	static function getTextFromHTML($html){
		
		if (strlen($html) == 0) return false;
		
		// convert tabs and newlines to spaces
		$html = str_ireplace(array("\t","\n","\r"), '    ', $html);
		
		// add spaces after closing html tags so when we strip tags there are still spaces
		$html = str_ireplace('>', '> ', $html);
		
		// xpath occasionally cocks up stripping javascript containing html, so manually remove it
		$html = preg_replace('@<script[^>]*?>.*?</script>@si', ' ', $html);
		
		$dom = new DomDocument();
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		
		$important = array();
		$queries = [
			'//title[1]', 
			'//meta[@name="description"]/@content', 
			'//meta[@name="keywords"]/@content', 
			'//h1', '//h2', '//h3', '//h4', '//h5', '//h6'
		];
		foreach ($queries as $query){
			foreach($xpath->query($query) as $node){ $important[] = self::get_inner_html($node); }
		}
		
		// image alt tags
		/*$tags = $xpath->query('//img');
		foreach ($tags as $tag){
			$important[] = $tag->getAttribute('alt');
			if ($tag->getAttribute('alt') != $tag->getAttribute('title')) $important[] = $tag->getAttribute('title');
		}*/
		
		self::removeElements($xpath->query('//head'));
		self::removeElements($xpath->query('//title'));
		self::removeElements($xpath->query('//meta'));
		self::removeElements($xpath->query('//script'));
		self::removeElements($xpath->query('//noscript'));
		self::removeElements($xpath->query('//link'));
		self::removeElements($xpath->query('//style'));
		self::removeElements($xpath->query('//comment()'));
		self::removeElements($xpath->query('//iframe'));
		self::removeElements($xpath->query('//nav'));
		self::removeElements($xpath->query('//footer'));
		self::removeElements($xpath->query('//header'));
		self::removeElements($xpath->query('//aside'));
		self::removeElements($xpath->query('//input'));
		self::removeElements($xpath->query('//textarea'));
		self::removeElements($xpath->query('//select'));
		self::removeElements($xpath->query('//label'));
		self::removeElements($xpath->query('//img'));
		//self::removeElements($xpath->query('//a')); // links refer to *other* pages, not the content on this page?
		self::removeElements($xpath->query("//*[contains(@style,'display:none')]"));
		self::removeElements($xpath->query("//*[contains(@class,'hidden')]"));
		self::removeElements($xpath->query("//*[contains(@class,'header')]"));
		self::removeElements($xpath->query("//*[contains(@class,'footer')]"));
		self::removeElements($xpath->query("//*[contains(@role,'navigation')]"));
		self::removeElements($xpath->query("//*[contains(@class,'navigation')]"));
		self::removeElements($xpath->query("//*[contains(@class,'nav')]"));
		
		// we already extract these, so ignore
		self::removeElements($xpath->query("//h1"));
		self::removeElements($xpath->query("//h2"));
		self::removeElements($xpath->query("//h3"));
		self::removeElements($xpath->query("//h4"));
		self::removeElements($xpath->query("//h5"));
		self::removeElements($xpath->query("//h6"));
		
		$html = $dom->saveHTML();
		$html = implode('. ', $important).'. '.$html;
		
		// convert to utf-8 if we can
		$encoding = mb_detect_encoding($html, 'ASCII, windows-1251, ISO-8859-1, UTF-8', true);
		$html = mb_convert_encoding($html, $encoding, 'UTF-8');
		
		$str = strip_tags(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
		$str = str_replace('    ', '. ', $str);
		
		while (stristr($str, '. . ')){
			$str = preg_replace('/'.preg_quote('. . ').'/', '. ', $str);
			$str = preg_replace('/(\s)+/', ' ', $str);
			$str = preg_replace('/\.+/', '.', $str);
		}
		$str = trim($str);
		
		if ($str == '.') return false;
		return $str;
	}
}
?>
