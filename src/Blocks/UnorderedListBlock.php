<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class UnorderedListBlock implements BlockInterface, BlockStartCodes
{
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if(empty($line))
    {
      return false;
    }
    $this->_lines[] = substr($line, 2);
    return true;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    $lines = $blockEngine->parseLines($this->_lines, true);
    return $ruleEngine->parse('<ul><li>' . implode("</li><li>", $lines) . '</li></ul>');
  }

  public function startCodes(): array
  {
    return ['- ', '* ', '+ '];
  }

}
