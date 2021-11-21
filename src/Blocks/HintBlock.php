<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class HintBlock implements BlockInterface, BlockLineMatcher
{
  protected $_lines = [];
  protected $_style;
  protected $_levelLen;
  protected $_level;

  public function __construct($level = '', $style = null)
  {
    $this->_level = $level;
    $this->_style = $style;
    $this->_levelLen = strlen($level);
  }

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if(empty($line) || ($this->_style === '|' && substr($line, 0, $this->_levelLen) !== $this->_level))
    {
      return false;
    }

    $this->_lines[] = trim(substr($line, $this->_levelLen + ($this->_style === ')' ? 2 : 1)));
    return $this->_style === '|';
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    return $ruleEngine->parse(
      '<div class="hint-' . strtolower($this->_level) . '">'
      . ($this->_style === ':' ? '<strong class="hint-caption">' . $this->_level . '</strong>' : '')
      . implode("\n<br/>", $this->_lines) . '</div>'
    );
  }

  public function match(string $line, bool $nested): ?BlockInterface
  {
    $matches = [];
    if(preg_match('/\(?(SUCCESS|WARNING|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])/', $line, $matches))
    {
      return new static($matches[1], $matches[2]);
    }
    return null;
  }

}
