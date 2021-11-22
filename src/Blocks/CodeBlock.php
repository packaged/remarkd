<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class CodeBlock implements BlockInterface, BlockStartCodes
{
  private const FENCE = 'f';
  private const TAB = 't';
  private const SPACE = 's';

  protected $_trimLen = 0;
  protected $_openStyle;
  protected $_lines = [];
  protected $_complete = false;

  public function addNewLine(string $line)
  {
    if(substr($line, 0, 3) === '```')
    {
      if($this->_openStyle === null)
      {
        $this->_openStyle = self::FENCE;
        return true;
      }
      else
      {
        return null;
      }
    }
    if($this->_openStyle === null)
    {
      if($line[0] === "\t")
      {
        $this->_openStyle = self::TAB;
        $this->_trimLen = 1;
      }
      else if(substr($line, 0, 4) === '    ')
      {
        $this->_trimLen = 4;
        $this->_openStyle = self::SPACE;
      }
    }

    if($this->_openStyle !== self::FENCE && empty($line))
    {
      return false;
    }

    $this->_lines[] = substr($line, $this->_trimLen);
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
