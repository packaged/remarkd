<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class WellBlock implements BlockInterface, BlockStartCodes
{
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    if(substr($line, 0, 2) !== '||')
    {
      return false;
    }
    $this->_lines[] = substr($line, 2);
    return true;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    return '<div class="well">' . $ruleEngine->parse(implode("<br/>", $this->_lines)) . '</div>';
  }

  public function startCodes(): array
  {
    return ['||'];
  }

}
