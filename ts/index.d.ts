export type RemarkdOptions = {
  projectRoot?: string;
  partials?: Record<string, string>;
};

export declare class Remarkd {
  parse(markdown: string, detectHeaders?: boolean, options?: RemarkdOptions): string;
  static parse(markdown: string, detectHeaders?: boolean, options?: RemarkdOptions): string;
}

export declare function parse(markdown: string, detectHeaders?: boolean, options?: RemarkdOptions): string;

export default Remarkd;
