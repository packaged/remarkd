<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class TabsBlock implements BlockInterface, BlockLineMatcher
{
  protected $_lines = [];
  protected $_properties = [];
  protected $_tabs = [];

  public function __construct($configLine = null)
  {
    if($configLine !== null)
    {
      $properties = [];
      if(preg_match_all('/((\w+)(=([^,}]+))?)/', $configLine, $properties))
      {
        foreach($properties[2] as $i => $property)
        {
          if($i === 0 && $property == 'TABGROUP')
          {
            continue;
          }
          $this->_properties[$property] = $properties[4][$i] ?? true;
        }
      }
    }
  }

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if($line == '{ENDTABGROUP}')
    {
      return false;
    }

    //Dont add initial tab lines
    if(empty($this->_lines) && empty($line))
    {
      return true;
    }

    if(substr($line, 0, 9) !== '{TABGROUP')
    {
      $this->_lines[] = $line;
    }
    return true;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    $lines = $blockEngine->parseBlocks($this->_lines, true);
    $content = [];
    $tabHeaders = '<ul class="tab-header">';
    foreach($lines as $block)
    {
      if($block instanceof TabBlock)
      {
        $append = '';
        if(empty($this->_tabs))
        {
          $append .= ' class="active"';
        }
        $this->_tabs[] = $block;
        $tabHeaders .= '<li><a href="#" data-tab-focus-key="' . $block->key() . '"' . $append . '>'
          . $block->name() . '</a></li>';
      }
      $content[] = $block instanceof BlockInterface ? $block->complete($blockEngine, $ruleEngine) : $block;
    }

    if(empty($this->_tabs))
    {
      return implode("\n", $content);
    }

    $tabHeaders .= '</ul>';
    return '<div class="tab-group">' . $tabHeaders . '<div class="tabs">' . implode("\n", $content) . '</div></div>';
  }

  public function match(string $line, bool $nested): ?BlockInterface
  {
    return substr($line, 0, 9) == '{TABGROUP' ? new static($line) : (
    substr($line, 0, 4) == '{TAB' ? new TabBlock($line) : null
    );
  }
}
