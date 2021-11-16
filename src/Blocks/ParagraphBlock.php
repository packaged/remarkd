<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class ParagraphBlock implements BlockInterface, BlockLineMatcher
{
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if(empty($line))
    {
      return false;
    }
    $this->_lines[] = $line;
    return true;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    return '<p>' . $ruleEngine->parse(implode("<br/>", $this->_lines)) . '</p>';
  }

  public function match(string $line, bool $nested): ?BlockInterface
  {
    if(!$nested && preg_match('/[a-zA-Z0-9_*`!~\[:(]/', $line[0]))
    {
      return new ParagraphBlock();
    }
    return null;
  }

}
