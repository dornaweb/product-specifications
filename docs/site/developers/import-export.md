# Import and export sections

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_register_package_sections` | action | `SectionRegistry $registry` |

A section implements `ImportExport\Section` and carries its own data in the package.
`ImportPlan` and `EntryOutcome` are part of that contract.

| Method | Does |
| --- | --- |
| `key()` | The section's key in the package |
| `dependsOn()` | The keys of the sections this one reads, which run first |
| `validate($entries)` | The problems of each entry that cannot be imported |
| `export()` | The section's entries |
| `planEntry($entry, $plan)` | What importing the entry would do, without writing anything |
| `importEntry($entry, $plan)` | Imports the entry |

A section the importing store does not know is left out, and the preview names it.

For a specification type whose values refer to something on the site, see
[Values that refer to the site](./types#values-that-refer-to-the-site).
