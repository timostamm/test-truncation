<?php 


namespace TS\Text;


/**
 * @author Timo Stamm <ts@timostamm.de>
 * @license AGPLv3.0 https://www.gnu.org/licenses/agpl-3.0.txt
 */
class Truncation {
	
	
	const STRATEGY_PARAGRAPH = 'paragraph';
	const STRATEGY_LINE      = 'line';
	const STRATEGY_SENTENCE  = 'sentence';
	const STRATEGY_WORD      = 'word';
	const STRATEGY_CHARACTER = 'character';
	
	const RE_PARAGRAPH_BREAK = '/(?:[\s]*\\n\\n|[\s]*\\r\\n\\r\\n|[\s]*\\r\\r)+/u';
	const RE_LINE_BREAK = '/(?:\\n|\\r\\n|\\r)+/u';
	const RE_SENTENCE_BREAK = '/[^\.\!\?]*[\.\!\?]/u';
	const RE_WORD_BREAK = '/[^\w]+/u';

	const NBSP = ' ';
	
	public $spaceAfterWordTruncation = true;
	
	private $max_length;
	private $max_length_wo_truncation_str;
	private $min_length;
	private $strategy;
	private $truncation_string;
	private $encoding;
	
	
	public function __construct( $max_length, $strategy = self::STRATEGY_WORD, $truncation_string = '…', $encoding = 'UTF-8', $min_length = 0 ) {
		$this->max_length = $max_length;
		$this->truncation_string = $truncation_string;
		$this->max_length_wo_truncation_str = $max_length - mb_strlen($truncation_string, $encoding);
		if ( ! in_array($strategy, [ self::STRATEGY_CHARACTER, self::STRATEGY_WORD, self::STRATEGY_SENTENCE, self::STRATEGY_PARAGRAPH ]) ) {
			throw new \Exception();
		}
		$this->strategy = $strategy;
		$this->encoding = $encoding;
		$this->setMinLength( $min_length );
	}
	
	
	public function setMinLength( $min_length ) {
		if ( $min_length < 0 ) {
			throw new \Exception();
		}
		if ( $min_length > $this->max_length ) {
			throw new \Exception();
		}
		$this->min_length = $min_length;
	}
	
	
	public function truncate( $str ) {
		$len = mb_strlen($str, $this->encoding);
		if ( $len <= $this->max_length ) {
			return $str;
		}
		
		if ( $this->strategy == self::STRATEGY_PARAGRAPH ) {
			$index = self::indexOfRegexBefore( $str, $this->max_length, self::RE_PARAGRAPH_BREAK, $this->encoding );
			if ( $index >= $this->min_length ) {
				return mb_substr($str, 0, $index, $this->encoding);
			}
		}
		
		if ( $this->strategy == self::STRATEGY_LINE || $this->strategy == self::STRATEGY_PARAGRAPH ) {
			$index = self::indexOfRegexBefore( $str, $this->max_length, self::RE_LINE_BREAK, $this->encoding );
			if ( $index >= $this->min_length ) {
				return mb_substr($str, 0, $index, $this->encoding);
			}
		}
		
		if ( $this->strategy == self::STRATEGY_SENTENCE || $this->strategy == self::STRATEGY_LINE || $this->strategy == self::STRATEGY_PARAGRAPH ) {
			$index = self::endOfRegexBefore( $str, $this->max_length, self::RE_SENTENCE_BREAK, $this->encoding );
			if ( $index >= $this->min_length ) {
				return mb_substr($str, 0, $index, $this->encoding);
			}
		}
		
		if ( $this->strategy == self::STRATEGY_WORD || $this->strategy == self::STRATEGY_SENTENCE || $this->strategy == self::STRATEGY_LINE || $this->strategy == self::STRATEGY_PARAGRAPH ) {
			$index = self::indexOfRegexBefore( $str, $this->max_length_wo_truncation_str, self::RE_WORD_BREAK, $this->encoding );
			if ( $index >= $this->min_length ) {
				return mb_substr($str, 0, $index, $this->encoding) . ($this->spaceAfterWordTruncation ? self::NBSP : '') . $this->truncation_string;
			}
		}
		
		
		return mb_substr($str, 0, $this->max_length_wo_truncation_str, $this->encoding) . $this->truncation_string;
	}
	

	public static function indexOfRegexBefore( $str, $index, $re, $encoding='UTF-8' ) {
		
		$matches = [];
		$r = preg_match_all($re, $str, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
		
		$r = -1;
		foreach ( $matches as $m ) {
			$match = $m[0][0];
			$i = $m[0][1];
			if ( $i > $index ) {
				continue;
			}
			if ( $i > $r ) {
				$r = $i;
			}
		}
		
		if ( $r !== -1 ) {
			// offsets are in bytes, we have to get the mb character position
			$s = substr($str, 0, $r);
			return mb_strlen($s, $encoding);
		}
		
		return $r;
	}
	
	
	public static function endOfRegexBefore( $str, $index, $re, $encoding='UTF-8' ) {
		$matches = [];
		$r = preg_match_all($re, $str, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
		$r = -1;
		foreach ( $matches as $m ) {
			$match = $m[0][0];
			$i = $m[0][1];
			$i += strlen( $match );
			if ( $i > $index ) {
				continue;
			}
			if ( $i > $r ) {
				$r = $i;
			}
		}
		
		if ( $r !== -1 ) {
			// offsets are in bytes, we have to get the mb character position
			$s = substr($str, 0, $r);
			return mb_strlen($s, $encoding);
		}
		
		return $r;
	}
	
	
}