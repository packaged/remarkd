<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class HeadingBlock implements BlockInterface, BlockStartCodes
{
  protected $_heading = '';
  protected $_level;

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if(empty($line) || !empty($this->_heading))
    {
      return false;
    }

    $this->_level = substr_count($line, '#', 0);
    $this->_heading = trim(substr(trim($line), $this->_level));
    return null;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    return $ruleEngine->parse('<h' . $this->_level . '>' . $this->_heading . '</h' . $this->_level . '>');
  }

  public function startCodes(): array
  {
    return ['# ', '##',];
  }

}
