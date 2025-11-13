/**
 * BasicBlock - base implementation for all blocks
 */
export class BasicBlock {
  constructor() {
    this._closed = false;
    this._closer = null;
    this._attr = null;
    this._class = [];
    this._tag = null;
    this._title = null;
    this._allowChildren = true;
    this._closeOnEmpty = null;
    this._substrim = '';
    this._substrimLen = 0;
    this._contentType = 'simple';
    this._contentContainer = true;
    this._children = [];
  }

  setCloseOnEmptyLine(close) {
    this._closeOnEmpty = close;
    return this;
  }

  setContentType(type) {
    this._contentType = type;
    return this;
  }

  contentType() {
    return this._contentType || 'simple';
  }

  isContainer() {
    return this._contentType === 'compound';
  }

  trimLeftLength() {
    return this._substrimLen || 0;
  }

  trimLeftStr() {
    return this._substrim;
  }

  _setSubstrim(substrim) {
    this._substrim = substrim;
    this._substrimLen = substrim.length;
    return this;
  }

  setAttributes(attributes) {
    this._attr = attributes;
    return this;
  }

  closer() {
    return this._closer;
  }

  isOpen() {
    return !this._closed;
  }

  clearChildren() {
    this._children = [];
    return this;
  }

  setTitle(title) {
    this._title = title;
    return this;
  }

  addClass(className) {
    if (Array.isArray(className)) {
      this._class.push(...className);
    } else {
      this._class.push(className);
    }
    return this;
  }

  setCloser(tag) {
    this._closer = tag;
    return this;
  }

  setTag(tag) {
    this._tag = tag;
    return this;
  }

  tag() {
    return this._tag;
  }

  setAllowChildren(bool) {
    this._allowChildren = bool;
    return this;
  }

  allowChildren() {
    return this._allowChildren && this.contentType() !== 'simple';
  }

  closesOnEmptyLine() {
    if (this._closeOnEmpty !== null) {
      return this._closeOnEmpty;
    }
    return !this.closer();
  }

  close() {
    const blockIDs = [];
    for (const child of this._children) {
      if (child.close) {
        blockIDs.push(...child.close());
      }
    }
    blockIDs.push(this.closer());
    this._closed = true;
    return blockIDs;
  }

  addChild(block) {
    this._children.push(block);
    return this;
  }

  async appendLine(ctx, line) {
    this.addChild(await this._formatLine(ctx, line));
    return true;
  }

  async _formatLine(ctx, line) {
    if (['raw', 'verbatim'].includes(this.contentType())) {
      return line;
    }
    const ruleProcessed = ctx.ruleEngine().parse(line);
    const objectProcessed = await ctx.objectEngine().parse(ruleProcessed);
    return objectProcessed;
  }

  render() {
    return this._produceElement();
  }

  _produceElement() {
    let content = this._children;
    
    // Process children - convert strings to HTML
    content = content.map(child => {
      if (typeof child === 'string') {
        return child;
      } else if (child.render) {
        return child.render();
      } else if (typeof child === 'object' && child.tag) {
        return this._renderElement(child);
      }
      return String(child);
    });

    if (this.allowChildren() && this._contentContainer) {
      content = ['<div class="content">', ...content, '</div>'];
    }

    if (this._title) {
      content = ['<div class="title">', this._escapeHtml(this._title), '</div>', ...content];
    }

    const tag = this._tag || 'div';
    let className = Array.isArray(this._class) ? this._class.join(' ') : this._class || '';

    // Apply attributes
    if (this._attr) {
      const id = this._attr.id();
      const classes = this._attr.classes();
      if (classes.length > 0) {
        className = (className ? className + ' ' : '') + classes.join(' ');
      }
      const firstPos = this._attr.position(0);
      if (firstPos && firstPos[0] === '.') {
        className = (className ? className + ' ' : '') + firstPos.substring(1);
      } else if (firstPos === 'source') {
        className = (className ? className + ' ' : '') + 'source-code';
      }
    }

    let html = `<${tag}`;
    if (className) {
      html += ` class="${this._escapeHtml(className)}"`;
    }
    if (this._attr && this._attr.id()) {
      html += ` id="${this._escapeHtml(this._attr.id())}"`;
    }
    html += '>';
    html += content.join('');
    html += `</${tag}>`;
    return html;
  }

  _renderElement(element) {
    if (typeof element === 'string') {
      return element;
    }
    
    if (element.tag) {
      let html = `<${element.tag}`;
      
      if (element.id) {
        html += ` id="${this._escapeHtml(element.id)}"`;
      }
      
      if (element.className) {
        html += ` class="${this._escapeHtml(element.className)}"`;
      }
      
      html += '>';
      
      if (element.content) {
        for (const child of element.content) {
          if (typeof child === 'string') {
            html += child;
          } else if (child.tag) {
            html += this._renderElement(child);
          } else {
            html += String(child);
          }
        }
      }
      
      html += `</${element.tag}>`;
      return html;
    }
    
    return String(element);
  }

  _escapeHtml(text) {
    if (typeof text !== 'string') return '';
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  children() {
    return this._children;
  }

  allowChild(child) {
    if (this.closer() && child.closer) {
      return this.closer() !== child.closer();
    }
    return this.constructor !== child.constructor;
  }

  allowLine(line) {
    if (line === '' && this.closesOnEmptyLine()) {
      return false;
    }
    return this._substrimLen === 0 || line.substring(0, this._substrimLen) === this._substrim;
  }
}

