<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

abstract class AbstractListBlock implements BlockInterface, BlockStartCodes
{
  protected $_listType = '';

  protected $_items = [];

  public function addNewLine(string $line)
  {
    if(empty(trim($line)))
    {
      return false;
    }
    $prefix = substr($line, 0, 2);
    if(in_array($prefix, $this->startCodes()))
    {
      $this->_addItem(trim(substr($line, 2)));
    }
    else
    {
      $this->_appendItem(trim(substr($line, 2)));
    }
    return true;
  }

  protected function _addItem($text)
  {
    $this->_items[] = [$text];
    return $this;
  }

  protected function _appendItem($text)
  {
    $this->_items[count($this->_items) - 1][] = $text;
    return $this;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    $output = '<' . $this->_listType . '>';
    foreach($this->_items as $li)
    {
      $output .= '<li>' . implode("", $blockEngine->parseLines($li, true)) . '</li>';
    }
    $output .= '</' . $this->_listType . '>';
    return $ruleEngine->parse($output);
  }

}
