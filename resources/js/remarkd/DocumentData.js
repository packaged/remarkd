/**
 * DocumentData - manages document-level attributes and replacements
 */
export class DocumentData {
  constructor() {
    this._data = {};
  }

  get(key, defaultValue = null) {
    const normalizedKey = this._key(key);
    return this._data[normalizedKey] !== undefined ? this._data[normalizedKey] : defaultValue;
  }

  has(key) {
    return this._key(key) in this._data;
  }

  set(key, value) {
    this._data[this._key(key)] = value;
    return this;
  }

  keys() {
    return Object.keys(this._data);
  }

  data() {
    return this._data;
  }

  add(attr) {
    const match = attr.match(/^:(\!?[\w\-\.]+)(\!)?:(.*)?$/m);
    if (!match) {
      return false;
    }
    const key = match[1];
    let val = (match[3] || '').trim();
    const isFalse = match[2] === '!';
    if (val === '') {
      val = !isFalse;
    }
    this._data[this._key(key)] = val;
    return true;
  }

  _key(key) {
    return '{' + key + '}';
  }

  replace(text) {
    const keys = Object.keys(this._data);
    const values = keys.map(k => this._data[k]);
    let result = text;
    for (let i = 0; i < keys.length; i++) {
      result = result.replace(new RegExp(keys[i].replace(/[{}]/g, '\\$&'), 'g'), values[i]);
    }
    return result;
  }
}

