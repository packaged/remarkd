import { Document } from './Document.js';
import { DocumentData } from './DocumentData.js';
import { Section } from './Section.js';
import { Attributes } from './Attributes.js';

/**
 * Parser - parses remarkd text into a Document
 */
export class Parser {
  constructor(rawLines, remarkd = null, doc = null) {
    this._document = doc || Parser.createDocument();
    this._remarkd = remarkd;
    this._currentSection = null;
    this._conditionals = [];
    this._currentCondition = Parser.COND_INCLUDE;
    
    // Process line continuations (lines ending with \)
    this._raw = [];
    let appendNext = false;
    for (const line of rawLines) {
      const trimmed = line.trim().replace(/\r\n|\r|\n/g, '');
      if (appendNext) {
        const lastLine = this._raw.pop();
        this._raw.push(lastLine + '\n' + trimmed);
      } else {
        // Remove comment markers {!...!}
        this._raw.push(trimmed.replace(/{!(.*?)!}/g, '$1'));
      }
      appendNext = trimmed.endsWith('\\');
    }
  }

  static createDocument() {
    const document = new Document();
    document.data = new DocumentData();
    document.data.set('plus', '+');
    return document;
  }

  static COND_INCLUDE = 1;
  static COND_EXCLUDE = 2;

  static EXPECT_TITLE = 1;
  static EXPECT_AUTHORS = 2;
  static EXPECT_REVISION = 3;
  static EXPECT_DOCUMENT = 4;

  async parse(detectHeaders = true) {
    let attribute = null;
    let title = null;
    let expectAction = detectHeaders ? Parser.EXPECT_TITLE : Parser.EXPECT_DOCUMENT;
    
    // Create initial section if not already created
    if (this._currentSection === null) {
      const section = new Section(this._remarkd);
      this._setActiveSection(section);
      this._document.sections.push(section);
    }
    
    // Clone block engine for this parse
    const oldBlocks = this._remarkd.ctx().blockEngine();
    const BlockEngine = (await import('./BlockEngine.js')).BlockEngine;
    const newBlocks = new BlockEngine(this._remarkd.ctx());
    newBlocks.setMatchers(oldBlocks.getMatchers());
    this._remarkd.ctx().setBlockEngine(newBlocks);

    for (const line of this._raw) {
      const char1 = line[0] || '';
      
      // Skip comment lines starting with //
      if (line.startsWith('// ')) {
        continue;
      }

      if (line === '' && expectAction === Parser.EXPECT_TITLE) {
        continue;
      }

      const processedLine = this._document.data.replace(line);
      const ifParsed = this._ifParse(processedLine);
      if (ifParsed === null) {
        continue;
      }

      if (this._currentCondition === Parser.COND_EXCLUDE) {
        continue;
      }

      if (char1 === ':') {
        expectAction = Parser.EXPECT_DOCUMENT;
        this._document.data.add(line);
        continue;
      }

      switch (expectAction) {
        case Parser.EXPECT_TITLE:
          if (line.startsWith('= ')) {
            this._document.title = line.substring(2);
            expectAction = Parser.EXPECT_AUTHORS;
            continue;
          } else {
            expectAction = Parser.EXPECT_DOCUMENT;
            break;
          }
        case Parser.EXPECT_AUTHORS:
          if (line !== '') {
            this._setAuthors(line);
          }
          expectAction = Parser.EXPECT_REVISION;
          continue;
        case Parser.EXPECT_REVISION:
          if (line !== '') {
            this._setRevision(line);
          }
          expectAction = Parser.EXPECT_DOCUMENT;
          continue;
      }

      if (char1 === '[' && line.endsWith(']')) {
        attribute = new Attributes(line);
        continue;
      }

      if (line === '|:DUMP:|') {
        const data = JSON.stringify(this._document.data.data(), null, 2);
        await this._addLine(`<code class="remarkd-dump">${data}</code>`, title, attribute);
        attribute = title = null;
        continue;
      }

      if (char1 === '.' && line[1] !== '.' && line[1] !== ' ') {
        title = line.substring(1);
        continue;
      }

      await this._addLine(ifParsed, title, attribute);
      attribute = title = null;
    }

    if (this._currentSection !== null) {
      this._currentSection.close();
    }

    this._remarkd.ctx().setBlockEngine(oldBlocks);
    return this._document;
  }

  _ifParse(line) {
    const match = line.match(/(end)?if(def|ndef|eval|nempty|empty|true|false)?::([^\[]*)\[([^]]*)]/);
    if (!match) {
      return line;
    }

    if (match[1] === 'end') {
      this._conditionals.pop();
      this._currentCondition = this._conditionals[this._conditionals.length - 1] || Parser.COND_INCLUDE;
      return null;
    }

    let validated = false;
    if (this._currentCondition === Parser.COND_INCLUDE) {
      switch (match[2]) {
        case 'def':
        case 'ndef':
        case 'true':
        case 'empty':
        case 'nempty':
        case 'false':
          validated = this._ifdefValidate(match[2], match[3]);
          if (match[4]) {
            return validated ? match[4] : null;
          }
          break;
        case 'eval':
          validated = this._ifevalValidate(match[4]);
          break;
      }
    }

    this._currentCondition = validated ? Parser.COND_INCLUDE : Parser.COND_EXCLUDE;
    this._conditionals.push(this._currentCondition);
    return null;
  }

  _ifdefValidate(validator, conditions) {
    const props = conditions.split(',');
    for (const prop of props) {
      const ands = prop.split('+');
      let andValid = true;
      for (const propReq of ands) {
        let matched = false;
        switch (validator) {
          case 'def':
            matched = this._document.data.has(propReq);
            break;
          case 'ndef':
            matched = !this._document.data.has(propReq);
            break;
          case 'true':
            matched = this._document.data.get(propReq) === true;
            break;
          case 'empty':
            matched = !this._document.data.get(propReq);
            break;
          case 'nempty':
            matched = !!this._document.data.get(propReq);
            break;
          case 'false':
            matched = this._document.data.get(propReq) === false;
            break;
        }
        if (!matched) {
          andValid = false;
          break;
        }
      }
      if (andValid) {
        return true;
      }
    }
    return false;
  }

  _ifevalValidate(condition) {
    const match = condition.match(/(.+)\s(===|==|!=|<=|<|>=|>|&&|\|\||in|nin)(.+)/);
    if (!match) {
      return false;
    }

    let left = this._document.data.replace(match[1].trim());
    let right = this._document.data.replace(match[3].trim());

    if (left.includes('int')) {
      left = parseInt(left.replace(/"/g, '').replace('int', ''));
    }
    if (right.includes('int')) {
      right = parseInt(right.replace(/"/g, '').replace('int', ''));
    }

    switch (match[2]) {
      case '===':
        return left === right;
      case '==':
        return left == right;
      case '!=':
        return left != right;
      case '<=':
        return left <= right;
      case '<':
        return left < right;
      case '>=':
        return left >= right;
      case '>':
        return left > right;
      case '&&':
        return left && right;
      case '||':
        return left || right;
      case 'in':
        return right.split(',').includes(left);
      case 'nin':
        return !right.split(',').includes(left);
    }
    return false;
  }

  _setAuthors(authorsLine) {
    for (const author of authorsLine.split(';')) {
      this._document.authors.push(author.trim());
    }
  }

  _setRevision(revision) {
    const parts = revision.split(',');
    this._document.revisionNumber = parts[0] || null;
    if (parts[1]) {
      const revisionParts = parts[1].split(':');
      this._document.revisionDate = revisionParts[0] ? revisionParts[0].trim() : null;
      this._document.revisionRemark = revisionParts[1] ? revisionParts[1].trim() : null;
    }
  }

  async _addLine(line, title = null, attribute = null) {
    switch (line.trim()) {
      case '---':
      case '- - -':
      case '***':
      case '* * *':
        line = '<hr>';
        break;
      case '<<<':
        line = '<div style="break-after:page"></div>';
        break;
    }

    if (line[0] === '=') {
      const match = line.match(/^([=]{2,6}) (.*)/);
      if (match) {
        const level = match[1].length - 1;
        if (!this._currentSection.hasChildren() && !this._currentSection.title) {
          this._document.sections.pop();
        }

        const Section = (await import('./Section.js')).Section;
        const newSection = new Section(this._remarkd, match[2], level);
        const hyphenated = this._hyphenate(this._stringToUnderscore(match[2]));
        newSection.setId(hyphenated);
        
        if (attribute !== null) {
          newSection.setAttributes(attribute);
          const id = attribute.id();
          if (id !== null) {
            newSection.setId(id);
          }
        }

        if (level < 2) {
          this._document.sections.push(newSection);
        } else if (level > this._currentSection.level + 1) {
          return this;
        } else if (level > this._currentSection.level) {
          this._currentSection.addChild(newSection);
        } else if (this._currentSection.level === level) {
          this._currentSection.parent.addChild(newSection);
        } else {
          let useSection = this._currentSection;
          while (useSection && useSection.level >= level) {
            useSection = useSection.parent;
          }
          useSection.addChild(newSection);
        }

        this._setActiveSection(newSection);
        return this;
      }
    }

    await this._addSectionLine(line, title, attribute);
    return this;
  }

  _setActiveSection(section) {
    if (this._currentSection !== null) {
      this._currentSection.close();
    }
    this._currentSection = section;
    return this;
  }

  async _addSectionLine(line, title = null, attribute = null) {
    if (this._currentSection === null) {
      // Create initial section if it doesn't exist
      const section = new Section(this._remarkd);
      this._setActiveSection(section);
      this._document.sections.push(section);
    }
    await this._currentSection.addLine(line, title, attribute);
    return this;
  }

  _hyphenate(str) {
    return str.toLowerCase()
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim();
  }

  _stringToUnderscore(str) {
    return str.toLowerCase()
      .replace(/[^\w\s]/g, '')
      .replace(/\s+/g, '_');
  }
}

