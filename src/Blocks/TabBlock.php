<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class TabBlock implements BlockInterface
{
  protected $_lines = [];

  protected $_properties = [];

  public function __construct($configLine = null)
  {
    if($configLine !== null)
    {
      $properties = [];
      if(preg_match_all('/((\w+)(=([^,}]+))?)/', $configLine, $properties))
      {
        foreach($properties[2] as $i => $property)
        {
          if($i === 0 && $property == 'TAB')
          {
            continue;
          }
          $this->_properties[$property] = $properties[4][$i] ?? true;
        }
      }
    }
  }

  public function name()
  {
    return $this->_properties['name'] ?? 'Unnamed';
  }

  public function key()
  {
    return $this->_properties['key'] ?? 'tab' . substr(md5($this->name()), 0, 5);
  }

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if($line == '{ENDTAB}')
    {
      return false;
    }

    //Dont add initial tab lines
    if(empty($this->_lines) && empty($line))
    {
      return true;
    }

    if(substr($line, 0, 4) !== '{TAB')
    {
      $this->_lines[] = $line;
    }
    return true;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    $lines = $blockEngine->parseLines($this->_lines, true);
    return '<div class="tab" data-tab-key="' . $this->key() . '">'
      //. '<h2>' . ($this->_properties['name'] ?? 'NO NAME') . '</h2>'
      . implode("<br/>", $lines)
      . '</div>';
  }

}
