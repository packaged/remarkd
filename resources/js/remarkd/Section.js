/**
 * Section - represents a document section
 */
export class Section {
  constructor(remarkd, title = null, level = 0) {
    this._remarkd = remarkd;
    this._attributes = null;
    this._blockEngine = remarkd.ctx().blockEngine();
    this.title = title;
    this.level = level;
    this.id = null;
    this.parent = null;
    this.children = [];
  }

  setAttributes(attributes) {
    this._attributes = attributes;
    return this;
  }

  setId(id) {
    this.id = id;
    return this;
  }

  hasChildren() {
    return this.children.length > 0 || this._blockEngine.blocks().length > 0;
  }

  _flushBlocks() {
    for (const block of this._blockEngine.blocks()) {
      this.children.push(block);
    }
    this._blockEngine.clearBlocks();
  }

  addChild(child) {
    this._flushBlocks();
    this.children.push(child);
    child.parent = this;
    return this;
  }

  async addLine(line, title = null, attribute = null) {
    await this._blockEngine.addLine(line, title, attribute);
    return this;
  }

  close() {
    this._flushBlocks();
    for (const block of this.children) {
      if (block.close) {
        block.close();
      }
    }
    return this;
  }

  render() {
    let html = '';
    
    // Render heading
    if (this.title && this.level > 0) {
      const headingTag = `h${Math.min(this.level + 1, 6)}`;
      html += `<${headingTag}>${this._escapeHtml(this.title)}</${headingTag}>`;
    }

    // Render content
    html += '<div class="remarkd-section section--level' + this.level + 
            ' section--' + (this.children.length > 0 ? 'with-content' : 'empty') + '"';
    
    if (this.id) {
      html += ` id="${this._escapeHtml(this.id)}"`;
    }
    
    html += '>';

    for (const child of this.children) {
      if (typeof child === 'string') {
        html += child;
      } else if (child.render) {
        html += child.render();
      } else if (typeof child === 'object' && child.tag) {
        html += this._renderElement(child);
      } else {
        html += String(child);
      }
    }

    html += '</div>';
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
}

