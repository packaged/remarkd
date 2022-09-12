<?php
namespace Packaged\Remarkd;

class Attributes
{
  protected $_position = [];
  protected $_named = [];
  protected $_raw;

  public function __construct($raw = '')
  {
    $this->_raw = trim($raw, '[]');
    $matches = [];
    preg_match_all('/(^|[\s,]+)([^, =}]+)(=((\"([^\"]*)\")|([^\s,}]*)))?/', $this->_raw, $matches);
    if(isset($matches[2]))
    {
      foreach($matches[2] as $pos => $match)
      {
        $this->_position[$pos] = $match;
        $this->_named[$match] = $matches[7][$pos] ?: $matches[6][$pos];
      }
    }
  }

  public function raw(): string
  {
    return $this->_raw;
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

  public function get(string $key, $default = null): ?string
  {
    return $this->_named[$key] ?? $default;
  }

  public function classes(): array
  {
    $classes = [];
    foreach($this->_position as $k)
    {
      if($k[0] === '.')
      {
        $classes[] = substr($k, 1);
      }
    }
    return $classes;
  }
}
