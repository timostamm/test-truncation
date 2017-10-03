<?php


namespace TS\Text;

use PHPUnit\Framework\TestCase;


/**
 * @author Timo Stamm <ts@timostamm.de>
 * @license AGPLv3.0 https://www.gnu.org/licenses/agpl-3.0.txt
 */
class TempTruncationTest extends TestCase {
	
	
	private $txt = <<<EOD
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. 
Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. 

At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
EOD;

	

	public function testRick(){
		$x = 'Rick ist ein leicht gestörter Alkoholiker mit einer großen wissenschaftlichen Begabung, der erst seit kurzer Zeit wieder mit seiner Familie vereint ist. Die meiste Zeit verbringt Rick damit, seinen Enkel Morty auf absurde Ausflüge in den Weltraum und in parallele Universen mitzunehmen, was weder zu einem entspannten Familienleben beiträgt noch vorteilhaft für Mortys Schulkarriere ist.';
		$t = new Truncation( 370, Truncation::STRATEGY_PARAGRAPH );
		$txt = $t->truncate( $x );
		$txt_len = mb_strlen($txt, 'UTF-8');
		$this->assertStringEndsWith('vereint ist.', $txt);
	}
	
	public function testTruncateParagraphFallback(){
		$t = new Truncation( 100, Truncation::STRATEGY_PARAGRAPH );
		$txt = $t->truncate( $this->txt );
		$this->assertStringEndsWith('tempor invidunt ut …', $txt);
	}
	
	
	public function testTruncatePrecise(){
		$t = new Truncation( 575, Truncation::STRATEGY_CHARACTER );
		$txt = $t->truncate( $this->txt );
		$txt_len = mb_strlen($txt, 'UTF-8');
		$this->assertStringEndsWith('…', $txt);
		$this->assertEquals(575, $txt_len);
	}
	
	public function testTruncateParagraph(){
		$t = new Truncation( 575, Truncation::STRATEGY_PARAGRAPH );
		$txt = $t->truncate( $this->txt );
		$this->assertStringEndsWith('erat, sed diam voluptua.', $txt);
	}
	
	public function testTruncateParagraphWithMinLength(){
		$t = new Truncation( 575, Truncation::STRATEGY_PARAGRAPH );
		$t->setMinLength( 490 );
		$txt = $t->truncate( $this->txt );
		$this->assertStringEndsWith('dolores et ea rebum.', $txt);
	}
	
	
	public function testFindParagraph(){
		$i = Truncation::indexOfRegexBefore( $this->txt, 590, Truncation::RE_PARAGRAPH_BREAK);
		$this->assertEquals(452, $i);
	}
	
	public function testFindParagraphNotFound(){
		$i = Truncation::indexOfRegexBefore( $this->txt, 450, Truncation::RE_PARAGRAPH_BREAK);
		$this->assertEquals(-1, $i);
	}

	public function testFindLinebreak(){
		$i = Truncation::indexOfRegexBefore( $this->txt, 590, Truncation::RE_LINE_BREAK);
		$this->assertEquals(453, $i);
	}
	
	public function testFindOtherLinebreak(){
		$i = Truncation::indexOfRegexBefore( $this->txt, 350, Truncation::RE_LINE_BREAK);
		$this->assertEquals(213, $i);
	}
	
	public function testFindSentence(){
		$i = Truncation::indexOfRegexBefore( $this->txt, 520, Truncation::RE_SENTENCE_BREAK);
		$this->assertEquals(511, $i);
	}
	
	public function testFindWord(){
		$i = Truncation::indexOfRegexBefore( $this->txt, 33, Truncation::RE_WORD_BREAK);
		$this->assertEquals(26, $i);
	}
	
	
	
}