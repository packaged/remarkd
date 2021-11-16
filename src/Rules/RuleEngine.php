<?php
namespace Packaged\Remarkd\Rules;

class RuleEngine
{
  protected $_rules = [];

  public function registerRule(RemarkdownRule $rule)
  {
    $this->_rules[] = $rule;
    return $this;
  }

  public function parse(string $text): string
  {
    foreach($this->_rules as $rule)
    {
      $text = $rule->apply($text);
    }
    return $text;
  }
}
