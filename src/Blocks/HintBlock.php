<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class HintBlock implements BlockInterface, BlockLineMatcher
{
  protected $_lines = [];
  protected $_hitNewLine = false;
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

    if(empty($line))
    {
      $this->_hitNewLine = true;
      return null;
    }

    if($this->_hitNewLine && !empty($this->_lines))
    {
      return false;
    }

    $multiLine = $this->_style === '|';
    if($multiLine && substr($line, 0, $this->_levelLen) !== $this->_level)
    {
      return false;
    }

    if(!$multiLine && !empty($this->_lines))
    {
      $this->_lines[] = $line;
    }
    else
    {
      $this->_lines[] = trim(substr($line, $this->_levelLen + ($this->_style === ')' ? 2 : 1)));
    }

    return true;
  }

  public function complete(RemarkdContext $ctx): string
  {
    return $ctx->ruleEngine()->parse(
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
