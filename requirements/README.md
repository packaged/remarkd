# Remarkd Requirements

This directory is the shared conformance suite for every Remarkd implementation.

Each feature lives in `features/<feature-name>/` and contains:

- `input.remarkd`: the Remarkd source for the feature.
- `expected.html`: the exact HTML string the parser must return.

Language implementations should discover these folders and assert that parsing
`input.remarkd` produces `expected.html` byte-for-byte. Keep fixtures small and
focused so a failing feature points to one behavior.
