# PHP Text Truncation Utility

This utility truncates plain text to a maximum length. It recognizes paragraphs, sentences and words and uses them to cut the text in a smart way.


#### Example

```PHP

// Setup a truncation for a maximum string length of 370 characters.
// Use the Paragraph-Strategy, which tries to find a paragraph 
// within the first 370 characters, and falls back to a sentence or  
// a word.
$t = new Truncation( 370, Truncation::STRATEGY_PARAGRAPH );

// Apply the truncation 
$txt = $t->truncate( $x );
```
