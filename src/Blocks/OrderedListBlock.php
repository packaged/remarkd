<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class OrderedListBlock implements BlockInterface, BlockStartCodes
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
    return $ruleEngine->parse('<ol><li>' . implode("</li><li>", $lines) . '</li></ol>');
  }

  public function startCodes(): array
  {
    return [
      "0 ",
      "1 ",
      "2 ",
      "3 ",
      "4 ",
      "5 ",
      "6 ",
      "7 ",
      "8 ",
      "9 ",
      "0.",
      "1.",
      "2.",
      "3.",
      "4.",
      "5.",
      "6.",
      "7.",
      "8.",
      "9.",
    ];
  }

}
