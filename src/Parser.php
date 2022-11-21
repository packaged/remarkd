<?php
namespace Packaged\Remarkd;

use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\HorizontalRule;
use Packaged\Helpers\Strings;
use Packaged\Remarkd\Blocks\BlockEngine;

class Parser
{
  protected $_raw;
  /**
   * @var Document
   */
  protected $_document;

  /**
   * @var Section
   */
  protected $_currentSection;

  /**
   * @var \Packaged\Remarkd\Remarkd
   */
  protected $_remarkd;

  const COND_INCLUDE = 1;
  const COND_EXCLUDE = 2;

  protected $_conditionals = [];
  protected $_currentCondition = self::COND_INCLUDE;

  public function __construct(array $rawLines, ?Remarkd $remarkd = null, ?Document $doc = null)
  {
    $this->_document = $doc ?? static::createDocument();
    $this->_remarkd = $remarkd ?? new Remarkd();

    $section = new Section($this->_remarkd);
    $this->_setActiveSection($section);
    $this->_document->sections[] = $section;

    $this->_raw = [];
    $appendNext = false;
    foreach($rawLines as $line)
    {
      $line = trim($line, "\r\n\0\x0B ");
      // If the line ends with a backslash, then it's a continuation of the previous line

      if($appendNext)
      {
        $line = rtrim(array_pop($this->_raw), '\\ ') . "\n" . $line;
      }

      $appendNext = substr($line, -1) == '\\';

      $this->_raw[] = preg_replace('/{!(.*)!}/mUi', '$1', $line);
    }
  }

  public static function createDocument(): Document
  {
    $document = new Document();
    $document->data = new DocumentData();
    $document->data->set('plus', '+');
    return $document;
  }

  const EXPECT_TITLE = 1;
  const EXPECT_AUTHORS = 2;
  const EXPECT_REVISION = 3;
  const EXPECT_DOCUMENT = 4;

  public function parse($detectHeaders = true): Document
  {
    $attribute = $title = null;
    $expectAction = $detectHeaders ? self::EXPECT_TITLE : self::EXPECT_DOCUMENT;
    $oldBlocks = $this->_remarkd->ctx()->blockEngine();
    $newBlocks = new BlockEngine($this->_remarkd->ctx());
    $newBlocks->setMatchers($oldBlocks->getMatchers());
    $this->_remarkd->ctx()->setBlockEngine($newBlocks);
    foreach($this->_raw as $line)
    {
      $char1 = $line[0] ?? '';
      //Skip Comment Lines starting with //
      if(substr($line, 0, 3) == '// ')
      {
        continue;
      }

      if(empty($line) && $expectAction == self::EXPECT_TITLE)
      {
        continue;
      }

      $line = $this->_document->data->replace($line);

      if($char1 === ':')
      {
        $expectAction = self::EXPECT_DOCUMENT;
        $this->_document->data->add($line);
        continue;
      }

      $line = $this->_ifParse($line);
      if($line === null)
      {
        continue;
      }

      if($this->_currentCondition == self::COND_EXCLUDE)
      {
        continue;
      }

      switch($expectAction)
      {
        case self::EXPECT_TITLE:
          if(substr($line, 0, 2) == '= ')
          {
            $this->_document->title = substr($line, 2);
            $expectAction = self::EXPECT_AUTHORS;
            continue 2;
          }
          else
          {
            $expectAction = self::EXPECT_DOCUMENT;
            break;
          }
        case self::EXPECT_AUTHORS:
          if(!empty($line))
          {
            $this->_setAuthors($line);
          }
          $expectAction = self::EXPECT_REVISION;
          continue 2;
        case self::EXPECT_REVISION:
          if(!empty($line))
          {
            $this->_setRevision($line);
          }
          $expectAction = self::EXPECT_DOCUMENT;
          continue 2;
        case $char1 == '[' && substr($line, -1) == ']':
          $attribute = new Attributes($line);
          continue 2;
        case $line == '|:DUMP:|':
          $line = '<code class="remarkd-dump">'
            . json_encode($this->_document->data->data(), JSON_PRETTY_PRINT) . '</code>';
          break;
        case $char1 == '.' && !in_array($line[1] ?? ' ', ['.', ' ']):
          $title = substr($line, 1);
          continue 2;
      }

      $this->_addLine($line, $title, $attribute);
      $attribute = $title = null;
    }
    if($this->_currentSection !== null)
    {
      $this->_currentSection->close();
    }

    $this->_remarkd->ctx()->setBlockEngine($oldBlocks);

    return $this->_document;
  }

  protected function _ifParse($line)
  {
    if(preg_match('/(end)?if(def|ndef|eval|nempty|empty|true|false)?::([^\[]*)\[([^\]]*)\]/', $line, $matches))
    {
      if($matches[1] == 'end')
      {
        array_pop($this->_conditionals);
        $this->_currentCondition = end($this->_conditionals) ?: self::COND_INCLUDE;
        return null;
      }

      $validated = false;
      if($this->_currentCondition === self::COND_INCLUDE)
      {
        switch($matches[2])
        {
          case self::VALIDATOR_DEF:
          case self::VALIDATOR_NDEF:
          case self::VALIDATOR_TRUE:
          case self::VALIDATOR_EMPTY:
          case self::VALIDATOR_NOT_EMPTY:
          case self::VALIDATOR_FALSE:
            $validated = $this->_ifdefValidate($matches[2], $matches[3]);

            //validate
            if(!empty($matches[4]))
            {
              return $validated ? $matches[4] : null;
            }

            break;
          case 'eval':
            $validated = $this->_ifevalValidate($matches[4]);
            break;
        }
      }

      $this->_currentCondition = $validated ? self::COND_INCLUDE : self::COND_EXCLUDE;
      $this->_conditionals[] = $this->_currentCondition;

      return null;
    }
    return $line;
  }

  const VALIDATOR_DEF = 'def';
  const VALIDATOR_NDEF = 'ndef';
  const VALIDATOR_TRUE = 'true';
  const VALIDATOR_EMPTY = 'empty';
  const VALIDATOR_NOT_EMPTY = 'nempty';
  const VALIDATOR_FALSE = 'false';

  protected function _ifdefValidate($validator, $conditions)
  {
    $validated = false;
    $props = explode(',', $conditions);
    foreach($props as $prop)
    {
      $ands = explode('+', $prop);
      $andValid = true;
      foreach($ands as $propReq)
      {
        $matched = false;
        switch($validator)
        {
          case self::VALIDATOR_DEF:
            $matched = $this->_document->data->has($propReq);
            break;
          case self::VALIDATOR_NDEF:
            $matched = !$this->_document->data->has($propReq);
            break;
          case self::VALIDATOR_TRUE:
            $matched = $this->_document->data->get($propReq) === true;
            break;
          case self::VALIDATOR_EMPTY:
            $matched = empty($this->_document->data->get($propReq));
            break;
          case self::VALIDATOR_NOT_EMPTY:
            $matched = !empty($this->_document->data->get($propReq));
            break;
          case self::VALIDATOR_FALSE:
            $matched = $this->_document->data->get($propReq) === false;
            break;
        }
        if(!$matched)
        {
          $andValid = false;
          break;
        }
      }
      if($andValid)
      {
        $validated = true;
        break;
      }
    }

    return $validated;
  }

  protected function _ifevalValidate($condition)
  {
    $matched = preg_match('/(.+)(\=\=\=|\=\=|\!\=|\<\=|\<|\>\=|\>)(.+)/', $condition, $matches);
    if(!$matched)
    {
      return false;
    }

    $matches[1] = trim($this->_document->data->replace($matches[1]));
    $matches[3] = trim($this->_document->data->replace($matches[3]));

    switch($matches[2])
    {
      case '===':
        return $matches[1] === $matches[3];
      case '==':
        return $matches[1] == $matches[3];
      case '!=':
        return $matches[1] != $matches[3];
      case '<=':
        return $matches[1] <= $matches[3];
      case '<':
        return $matches[1] < $matches[3];
      case '>=':
        return $matches[1] >= $matches[3];
      case '>':
        return $matches[1] > $matches[3];
    }
    return false;
  }

  protected function _setAuthors($authorsLine)
  {
    foreach(explode(';', $authorsLine) as $author)
    {
      $this->_document->authors[] = trim($author);
    }
  }

  protected function _setRevision($revision)
  {
    $doc = $this->_document;
    //revision number, revision date: revision remark
    [$doc->revisionNumber, $revisionData] = Strings::explode(',', $revision, null, 2);
    if($revisionData !== null)
    {
      [$doc->revisionDate, $doc->revisionRemark] = Strings::explode(':', trim($revisionData), null, 2);
      if($doc->revisionRemark !== null)
      {
        $doc->revisionRemark = trim($doc->revisionRemark);
      }
    }
  }

  protected function _addLine($line, $title = null, ?Attributes $attribute = null)
  {
    switch(trim($line))
    {
      // Line Break
      case '---':
      case '- - -':
      case '***':
      case '* * *':
        $line = HorizontalRule::create();
        break;
      // Page Break
      case '<<<':
        $line = Div::create()->setAttribute('style', 'break-after:page');
        break;
      case ($line[0] ?? '') == '=':
        if(preg_match('/^([=]{2,6}) (.*)/', $line, $matches))
        {
          $level = strlen($matches[1]) - 1;
          if(!$this->_currentSection->hasChildren() && empty($this->_currentSection->title))
          {
            array_pop($this->_document->sections);
          }

          $newSection = new Section($this->_remarkd, $matches[2], $level);
          $newSection->setId(Strings::hyphenate(Strings::stringToUnderScore($matches[2])));
          if($attribute !== null)
          {
            $newSection->setAttributes($attribute);
            $id = $attribute->id();
            if($id !== null)
            {
              $newSection->setId($id);
            }
          }

          //Always add Level 0 and 1 to the doc root
          if($level < 2)
          {
            $this->_document->sections[] = $newSection;
          }
          else if($level > $this->_currentSection->level + 1)
          {
            //Error - cannot nest further
            return $this;
          }
          else if($level > $this->_currentSection->level)
          {
            $this->_currentSection->addChild($newSection);
          }
          else if($this->_currentSection->level == $level)
          {
            $this->_currentSection->parent->addChild($newSection);
          }
          else
          {
            $useSection = $this->_currentSection;
            do
            {
              $useSection = $useSection->parent;
            }
            while($useSection && $useSection->level >= $level);
            $useSection->addChild($newSection);
          }

          $this->_setActiveSection($newSection);
          return $this;
        }
    }

    $this->_addSectionLine($line, $title, $attribute);
    return $this;
  }

  protected function _setActiveSection(Section $section)
  {
    if($this->_currentSection !== null)
    {
      $this->_currentSection->close();
    }
    $this->_currentSection = $section;
    return $this;
  }

  public function _addSectionLine($line, $title = null, $attribute = null)
  {
    $this->_currentSection->addLine($line, $title, $attribute);
    return $this;
  }
}
