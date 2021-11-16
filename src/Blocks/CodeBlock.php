<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class CodeBlock implements BlockInterface, BlockStartCodes
{
  protected $_openStyle;
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    if(substr($line, 0, 3) === '```')
    {
      if($this->_openStyle === null)
      {
        $this->_openStyle = '```';
        return true;
      }
      return false;
    }
    if($this->_openStyle === null && ($line[0] === "\t" || substr($line, 0, 4) === '    '))
    {
      $this->_openStyle = '\t';
    }

    if($this->_openStyle === '\t' && empty($line))
    {
      return false;
    }

    $this->_lines[] = $line;
    return true;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    return '<code>' . htmlentities(implode("\n", $this->_lines)) . '</code>';
  }

  public function startCodes(): array
  {
    return ['``'];
  }
}
