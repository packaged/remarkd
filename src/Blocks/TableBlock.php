<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class TableBlock implements BlockInterface, BlockStartCodes
{
  protected $_headerSet = false;
  protected $_rows = ['th' => [], 'td' => []];
  protected $_styles = [];

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if(empty($line) || $line[0] !== '|')
    {
      return false;
    }

    if(!$this->_headerSet && !empty($this->_rows) && preg_match('/^(\|[\s\-:|]+)+$/', $line))
    {
      $this->_headerSet = true;
      $this->_rows['th'] = $this->_rows['td'];
      $this->_rows['td'] = [];
      $styles = explode('|', trim($line, '| '));
      foreach($styles as $style)
      {
        $this->_styles[] = $style[0] === ':' ? 'text-align: left;' :
          (substr($style, -1) === ':' ? 'text-align: right;' :
            (strpos($style, ':') > 0 ? 'text-align: center;' : null));
      }
    }
    else
    {
      $this->_rows['td'][] = explode('|', trim($line, '|'));
    }
    return true;
  }

  public function complete(RemarkdContext $ctx): string
  {
    $table = '<table>';
    foreach(['th', 'td'] as $type)
    {
      foreach($this->_rows[$type] ?? [] as $row)
      {
        $table .= '<tr>';
        foreach($row as $i => $cell)
        {
          $style = $this->_styles[$i] ?? null;
          $table .= '<' . $type . ($style ? ' style="' . $style . '"' : '') . '>' . $cell . '</' . $type . '>';
        }
        $table .= '</tr>';
      }
    }
    $table .= '</table>';
    return $ctx->ruleEngine()->parse($table);
  }

  public function startCodes(): array
  {
    return ['| '];
  }

}
