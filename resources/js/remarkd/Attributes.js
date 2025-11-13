/**
 * Attributes class - parses attribute strings like [id, class, key=value]
 */
export class Attributes {
  constructor(raw = '') {
    this._raw = raw.trim().replace(/^\[|\]$/g, '');
    this._position = [];
    this._named = {};
    
    // Parse attributes: key=value, key="value", positional attributes
    const matches = this._raw.matchAll(/(^|[\s,]+)([^, =}]+)(=((\"([^\"]*)\")|([^\s,}]*)))?/g);
    let pos = 0;
    for (const match of matches) {
      const key = match[2];
      const value = match[7] || match[6] || null;
      this._position[pos] = key;
      this._named[key] = value;
      pos++;
    }
  }

  raw() {
    return this._raw;
  }

  position(pos, getValue = false) {
    if (getValue) {
      const key = this._position[pos];
      return key ? this._named[key] : null;
    }
    return this._position[pos] || null;
  }

  has(key) {
    return key in this._named;
  }

  get(key, defaultValue = null) {
    return this._named[key] !== undefined ? this._named[key] : defaultValue;
  }

  id() {
    for (const key of this._position) {
      if (key && key[0] === '#') {
        return key.substring(1);
      }
    }
    return null;
  }

  classes() {
    const classes = [];
    for (const key of this._position) {
      if (key && key[0] === '.') {
        classes.push(key.substring(1));
      }
    }
    return classes;
  }
}

