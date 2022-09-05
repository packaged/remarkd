<?php
namespace Packaged\Remarkd\Objects;

class ProgressMeterObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'meter';
  }

  public function render(): string
  {
    $output = '';
    $id = $this->_config['id'] ?? 'remarkd-meter-' . rand(100, 200);
    if(isset($this->_config['label']))
    {
      $output .= '<label for="' . $id . '" class="remarkd-meter-label">' . $this->_config['label'] . '</label>';
    }

    $min = ' min="' . ($this->_config['min'] ?? 0) . '"';
    $max = ' max="' . ($this->_config['max'] ?? 100) . '"';
    $value = ' value="' . ($this->_config['value'] ?? 0) . '"';
    $text = $this->_config['text'] ?? '';
    $output .= '<meter id="' . $id . '" class="remarkd-meter" ' . $min . $max . $value . '">' . $text . '</meter>';
    return $output;
  }

}
