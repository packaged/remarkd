/**
 * LinkObject - handles {{link:key}} objects
 */
export class LinkObject {
  getIdentifier() {
    return 'link';
  }

  create(context, config, key) {
    this._context = context;
    this._config = config;
    this._key = key;
    return this;
  }

  render() {
    const text = this._config.get('text', this._titleize(this._key));
    const href = this._config.get('href', this._key);
    
    let html = `<a href="${this._escapeHtml(href)}"`;
    
    const target = this._config.get('target');
    if (target !== null) {
      html += ` target="${this._escapeHtml(target)}"`;
    }
    
    const hrefLang = this._config.get('hreflang');
    if (hrefLang !== null) {
      html += ` hreflang="${this._escapeHtml(hrefLang)}"`;
    }
    
    html += `>${this._escapeHtml(text)}</a>`;
    return html;
  }

  _titleize(str) {
    if (!str) return '';
    return str.replace(/[-_]/g, ' ')
      .replace(/\b\w/g, l => l.toUpperCase());
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

