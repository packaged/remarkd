<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class BlockQuote implements BlockInterface, BlockStartCodes
{
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if(empty($line) || $line[0] !== '>')
    {
      return false;
    }
    $this->_lines[] = substr($line, 1);
    return true;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    $lines = $blockEngine->parseLines($this->_lines, true);
    return '<blockquote>' . implode("<br/>", $lines) . '</blockquote>';
  }

  public function startCodes(): array
  {
    return ['> ', '>>'];
  }

}
