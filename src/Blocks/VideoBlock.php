<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class VideoBlock implements BlockInterface, BlockLineMatcher
{
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
    return false;
  }

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string
  {
    $content = $containerAppend = '';

    if(isset($this->_properties['aspect']))
    {
      switch($this->_properties['aspect'])
      {
        case '1:1';
          $padding = '100';
          break;
        case '4:3';
          $padding = '75';
          break;
        case '3:2';
          $padding = '66.66';
          break;
        case '8:5';
          $padding = '62.5';
          break;
        case '16:9':
        default:
          $padding = '56.25';
          break;
      }
      $containerAppend = ' style="padding-top: ' . $padding . '%"';
    }

    switch($this->_properties['source'] ?? '')
    {
      case 'youtube':
        if(isset($this->_properties['id']))
        {
          $content = $this->_completeYoutube($this->_properties['id']);
          break;
        }
    }
    return '<div class="video-container"' . $containerAppend . '>' . $content . '</div>';
  }

  protected function _completeYoutube($id)
  {
    return '<iframe '
      . 'src="https://www.youtube.com/embed/' . $id . '" '
      . 'title="YouTube video player" frameborder="0" '
      . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" '
      . 'allowfullscreen></iframe>';
  }

  public function match(string $line, bool $nested): ?BlockInterface
  {
    return substr($line, 0, 6) == '{VIDEO' ? new static($line) : null;
  }

}
