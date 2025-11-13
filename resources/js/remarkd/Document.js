/**
 * Document - represents a parsed remarkd document
 */
export class Document {
  constructor() {
    this.title = null;
    this.authors = [];
    this.revisionNumber = null;
    this.revisionDate = null;
    this.revisionRemark = null;
    this.data = null;
    this.sections = [];
  }

  render() {
    let html = '';
    for (const section of this.sections) {
      html += section.render();
    }
    return html;
  }
}

