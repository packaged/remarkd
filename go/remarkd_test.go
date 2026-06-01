package remarkd

import (
	"os"
	"path/filepath"
	"strings"
	"testing"
)

func TestSharedRequirements(t *testing.T) {
	root := filepath.Join("..", "requirements", "features")
	features, err := os.ReadDir(root)
	if err != nil {
		t.Fatal(err)
	}

	for _, feature := range features {
		if !feature.IsDir() {
			continue
		}
		t.Run(feature.Name(), func(t *testing.T) {
			dir := filepath.Join(root, feature.Name())
			input, err := os.ReadFile(filepath.Join(dir, "input.remarkd"))
			if err != nil {
				t.Fatal(err)
			}
			expected, err := os.ReadFile(filepath.Join(dir, "expected.html"))
			if err != nil {
				t.Fatal(err)
			}

			got := Parse(strings.TrimRight(string(input), "\r\n"))
			want := strings.TrimRight(string(expected), "\r\n")
			if got != want {
				t.Fatalf("unexpected html\nwant: %s\n got: %s", want, got)
			}
		})
	}
}

func TestDocumentHeaderAttributes(t *testing.T) {
	tests := map[string]string{
		"attributes after title":               "= Document Title\n:product: Remarkd\n\nThis is {product}.",
		"attributes after author and revision": "= Document Title\nJane Doe\nv1.0, 2026-06-01: Released\n:product: Remarkd\n\nThis is {product}.",
		"comment before header":                "// banner comment\n= Document Title\n:product: Remarkd\n\nThis is {product}.",
	}
	want := `<div class="remarkd-section section--level0 section--with-content"><p>This is Remarkd.</p></div>`

	for name, input := range tests {
		t.Run(name, func(t *testing.T) {
			if got := Parse(input, true); got != want {
				t.Fatalf("unexpected html\nwant: %s\n got: %s", want, got)
			}
		})
	}
}
