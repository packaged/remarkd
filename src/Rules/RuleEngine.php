<?php
namespace Packaged\Remarkd\Rules;

use Packaged\Remarkd\RemarkdContext;

class RuleEngine
{
  /**
   * @var \Packaged\Remarkd\RemarkdContext
   */
  protected RemarkdContext $_context;

  public function __construct(RemarkdContext $ctx)
  {
    $this->_context = $ctx;
  }

  protected $_rules = [];

  public function registerRule(RemarkdRule $rule)
  {
    $this->_rules[get_class($rule)] = $rule;
    return $this;
  }

  public function replaceRule($ruleClass, RemarkdRule $rule)
  {
    //TODO: Having rule priorities would help here, as the rule processing order is important
    $this->_rules[$ruleClass] = $rule;
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
