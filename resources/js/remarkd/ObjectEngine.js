/**
 * ObjectEngine - processes object macros like {{link:key}} or {{image:url}}
 */
export class ObjectEngine {
  constructor(context) {
    this._context = context;
    this._objects = {};
    this._selector = null;
  }

  registerObject(obj) {
    this._objects[obj.getIdentifier()] = obj;
    // Sort by key length (longest first) to match longest identifiers first
    const sortedKeys = Object.keys(this._objects).sort((a, b) => b.length - a.length);
    const types = sortedKeys.join('|');
    this._selector = new RegExp(
      '\\{\\{(' + types + ')(:([^ }]+))?([^}]*|.*?"(.|\\n)*?".*?)\\}\\}',
      'gmi'
    );
    return this;
  }

  async parse(text) {
    if (!this._selector) {
      return text;
    }

    let result = text;
    const matches = [];
    let match;
    
    // Reset regex lastIndex
    this._selector.lastIndex = 0;
    
    while ((match = this._selector.exec(text)) !== null) {
      matches.push(match);
    }

    // Process matches in reverse order to maintain string positions
    for (let i = matches.length - 1; i >= 0; i--) {
      const match = matches[i];
      const identifier = match[1];
      const key = match[3] || null;
      const configStr = match[4] || '';

      if (this._objects[identifier]) {
        const { Attributes } = await import('./Attributes.js');
        const attrs = new Attributes(configStr.trim());
        const obj = this._objects[identifier].create(this._context, attrs, key);
        const rendered = obj.render();
        result = result.substring(0, match.index) + rendered + result.substring(match.index + match[0].length);
      }
    }

    return result;
  }
}

