<?php
namespace Packaged\Remarkd;

class Attributes
{
  protected $_position = [];
  protected $_named = [];

  public function __construct($raw = '')
  {
    $raw = trim($raw, '[]');
    $matches = [];
    preg_match_all('/(^|[\s,]+)([^, =}]+)(=((\"([^\"]*)\")|([^\s,}]*)))?/', $raw, $matches);
    if(isset($matches[2]))
    {
      foreach($matches[2] as $pos => $match)
      {
        $this->_position[$pos] = $match;
        $this->_named[$match] = $matches[7][$pos] ?: $matches[6][$pos];
      }
    }
  }

  public function position(int $pos, bool $getValue = false): ?string
  {
    if($getValue)
    {
      return $this->_named[$this->_position[$pos]] ?? null;
    }
    return $this->_position[$pos] ?? null;
  }

  public function has(string $key): bool
  {
    return array_key_exists($key, $this->_named);
  }

  public function named(string $key): ?string
  {
    return $this->_named[$key] ?? null;
  }
}
