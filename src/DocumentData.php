<?php
namespace Packaged\Remarkd;

class DocumentData
{
  protected $_data = [];

  public function get($key, $default = null)
  {
    return $this->_data[$this->_key($key)] ?? $default;
  }

  public function has($key)
  {
    return array_key_exists($this->_key($key), $this->_data);
  }

  public function set($key, $value)
  {
    $this->_data[$this->_key($key)] = $value;
    return $this;
  }

  public function keys()
  {
    return array_keys($this->_data);
  }

  public function data()
  {
    return $this->_data;
  }

  public function add($attr): bool
  {
    if(!preg_match("/:(\!?[\w\-]+):(.*)?/s", $attr, $matches))
    {
      return false;
    }
    $key = $matches[1];
    $val = trim($matches[2]);
    if(empty($matches[2]))
    {
      $isFalse = substr($key, -1) == '!';
      $val = !$isFalse;
      if($isFalse)
      {
        $key = substr($key, 1);
      }
    }
    $this->_data[$this->_key($key)] = $val;

    return true;
  }

  protected function _key($key)
  {
    return '{' . $key . '}';
  }

  public function replace($in)
  {
    return str_replace(array_keys($this->data()), array_values($this->data()), $in);
  }
}
