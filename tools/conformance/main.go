// Command conformance reports, as JSON, whether the Go parser reproduces each
// fixture's expected.html. Used by the docs build step (build:conformance) to
// bake Go results into docs/assets/conformance-status.json.
//
//	go run ./tools/conformance        # prints {"accordion":true,...}
package main

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strings"

	remarkd "github.com/packaged/remarkd/go"
)

func main() {
	root := filepath.Join("requirements", "features")
	entries, err := os.ReadDir(root)
	if err != nil {
		fmt.Fprintln(os.Stderr, err)
		os.Exit(1)
	}

	result := map[string]bool{}
	for _, entry := range entries {
		if !entry.IsDir() {
			continue
		}
		dir := filepath.Join(root, entry.Name())
		input, err1 := os.ReadFile(filepath.Join(dir, "input.remarkd"))
		expected, err2 := os.ReadFile(filepath.Join(dir, "expected.html"))
		if err1 != nil || err2 != nil {
			continue
		}
		got := remarkd.Parse(strings.TrimRight(string(input), "\r\n"))
		result[entry.Name()] = strings.TrimSpace(got) == strings.TrimSpace(string(expected))
	}

	out, err := json.Marshal(result)
	if err != nil {
		fmt.Fprintln(os.Stderr, err)
		os.Exit(1)
	}
	fmt.Println(string(out))
}
