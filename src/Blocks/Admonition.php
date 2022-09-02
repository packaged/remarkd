<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\Text\StrongText;
use Packaged\Helpers\Arrays;
use Packaged\SafeHtml\SafeHtml;

class Admonition extends BasicBlock implements BlockMatcher
{
  protected $_style;
  protected $_level;

  public function isContainer(): bool
  {
    return true;
  }

  public function __construct($level = '', $style = null)
  {
    $this->_level = strtolower($level);
    $this->_style = $style;
    $this->_setSubstrim($level . $style);
  }

  public function match($line): ?Block
  {
    $matches = [];
    if(preg_match('/(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])/', $line, $matches))
    {
      return new static($matches[1], $matches[2]);
    }
    return null;
  }

  public function produceSafeHTML(): SafeHtml
  {
    $content = Arrays::interleave(PHP_EOL, $this->_children);
    if($this->_style === ':')
    {
      array_unshift($content, StrongText::create(strtoupper($this->_level) . ': ')->addClass('hint-caption'));
    }
    return Div::create($content)->addClass('hint-' . $this->_level)->produceSafeHTML();
  }

  public function allowLine(string $line): bool
  {
    return !empty($line);
  }

}
