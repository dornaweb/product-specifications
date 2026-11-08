# ADR: Import and Export Package

## Status

Approved

## Context

A merchant needs to copy a store's specification setup to another store, keep it as a
backup, and sometimes take the product values along. v1 had a JSON screen for this, but
it exported ids, and imports often went wrong. v2 drops it, with no compatibility for
v1 files.

Three ways to do this were considered:

| Option | What it carries | What it cannot carry |
| --- | --- | --- |
| WordPress export (WXR) and the WordPress Importer | All content, on a full-site export | A post-type-only export writes no term definitions, so specifications and their settings are missing. Extending it means printing extra XML into a format we do not own, and importing depends on a separate plugin |
| WooCommerce product CSV, extended with our columns | Product values, matched by SKU or id | Specification definitions and sets. It also binds the feature to WooCommerce products |
| A package of our own | Specifications, sets and values, by slug | — |

Building a package of our own and also integrating with the WooCommerce CSV would be
the same work twice. One feature of our own is also not tied to WooCommerce products,
so a later version can carry values for other post types.

The package has to grow with the plugin. A new specification type brings new options
and value formats, and an add-on may store data of its own. Neither should require
changes to the importer.

## Decision

### One JSON file, versioned, with no ids in it

```json
{
  "format": "product-specifications",
  "schema_version": 1,
  "plugin_version": "2.0.0",
  "exported_at": "2026-11-07T22:00:00+00:00",
  "source": "https://example.com",
  "sections": {
    "specifications": [
      {
        "slug": "weight",
        "name": "Weight",
        "description": "",
        "type": "text",
        "options": {},
        "default": null
      }
    ],
    "sets": [
      {
        "slug": "phones",
        "title": "Phones",
        "description": "",
        "status": "publish",
        "groups": [
          {
            "key": "general",
            "label": "General",
            "description": "",
            "visible": true,
            "items": [{ "specification": "weight", "visible": true }]
          }
        ]
      }
    ],
    "values": [
      {
        "object": { "type": "product", "sku": "PH-100", "slug": "phone-100" },
        "set": "phones",
        "values": { "weight": "180 g" }
      }
    ]
  }
}
```

Every reference is a slug: a set item names its specification, and a value entry names
its set and its specifications. No term, post or product id appears anywhere, so a file
means the same thing on any store.

`schema_version` versions the file format, independently of the plugin version, and
follows ADR 0009:

- An older file is upgraded in memory before anything reads it.
- A file newer than the plugin understands is refused with a message that names both
  versions.

### Sections behind one contract

Each kind of data is a **section**, implementing one interface:

```txt
key()          the section's name in the file
dependsOn()    the sections it reads, which run before it
export()       writes its entries
plan()         a dry run: what would be created, updated, skipped or left unmatched, and why
import()       writes one chunk of entries and reports the outcome of each
```

- **Registry.** A registry holds the sections and orders them by `dependsOn()`. Core
  registers `specifications`, `sets` and `values`. An add-on registers its own through
  `product_specifications_register_package_sections`.
- **Unknown sections.** A section in a file that no installed code registers is reported
  in the plan and skipped.
- **Unresolved references.** A reference that matches nothing, neither on the site nor
  in the file, is reported by the section that holds it.

### Types own their options and values

A specification's options and stored values pass through its type:

- **Export** writes options as they are stored, and defaults and values in the form the
  type reads them, so two stores holding the same data export the same file.
- **Import** checks options against the type's `optionsSchema()`, exactly as the REST
  API does, and passes every value through the type's `sanitize()`.

So a new type takes part with no change to the importer. A type whose stored value
refers to something local to the site, such as an attachment id, implements an optional
interface that turns the value into something portable and back. No core type needs it
today.

### Import never deletes, and a type mismatch is a conflict

The merchant is not asked about conflicts.

**Products are never created.** A value entry names its product by SKU and slug.

- The product is found by SKU first, then by slug. If the SKU matches one product and
  the slug another, the SKU wins.
- A product that is not found is skipped and reported.

**Specifications are matched by slug.**

| On this store | Outcome |
| --- | --- |
| No specification with that slug | Created |
| Same slug, same type | Updated. The file is the source of truth: name, description, options (choices included) and default are replaced |
| Same slug, different type | **Conflict.** The specification is left untouched. Every set item and value in the file that names it is skipped, because on this store that slug means something else |

**The preview warns where a same-type update may be a different meaning:**

- the name changes;
- the new choices no longer include values that products on this store use, with the
  number of products affected.

**Sets are matched by slug and merged.** Nothing the merchant arranged is removed or
reordered.

| Part | Outcome |
| --- | --- |
| Title, description, status | Taken from the file |
| Groups | Matched by key. A new group is added at the end. An existing group keeps its place and takes the file's label and description |
| Specifications in a group | Ones not yet in the set are appended to their group. Ones already in the set stay where they are, even if the file places them elsewhere. Conflicted specifications are left out |
| Groups and specifications only on this store | Kept |

**Product values are merged.**

- The file's values overwrite the same specifications. Values for other specifications
  are kept.
- The product's set is replaced by the file's.
- A value the type's `sanitize()` rejects is skipped and reported.

**Accepted limits of merging:**

- A value the source store cleared is not cleared on the target, because an export
  carries only values that exist.
- A specification removed from a set, or moved within it, on the source store is not
  removed or moved on the target.

The merchant corrects either by hand.

**Products are matched per object type.** A matcher registered for each object type
finds the target object. Core registers `product`. Supporting another post type later
means adding a matcher, with no change to the sections.

### Specifications first, then sets, then values

Sections run in dependency order, `specifications` then `sets` then `values`, in both
the plan and the run.

The plan covers every section before anything is written, and each section reads the
outcome of the ones before it. That is how a conflicted specification leaves its set
items and values out, and why the preview already shows the full effect of an import.

### Product data is optional

The export screen and the CLI take an option to include product values. Without it, the
file carries only specifications and sets. An import takes every section the file carries,
so product values are imported whenever the file has them.

### Export runs in one request, import runs in the background

**Export** is streamed in one request. Entries are read in chunks of 100 and written as
they are read, so memory stays flat regardless of catalogue size.

**Import** runs in stages:

1. **Upload.** The file is stored under a random name in a private directory in uploads,
   and deleted when the import finishes or is abandoned.
2. **Validate.** The whole file is checked before anything else:
   - JSON that parses, the format marker, a supported schema version, and the size limit,
     and every section being a list. A failure here rejects the file and nothing is
     imported.
   - Every entry's shape, with no duplicate slugs inside a section and every set item
     naming a specification. An entry that fails is skipped, and the rest of the file is
     imported. Its problems name the field, for example
     `[2].groups[0].items[3].specification`.
3. **Plan.** A dry run per section, shown as a preview: new, updated, unchanged, type
   changes, unknown sections, unmatched products and unresolved references.
4. **Run.** Chunks run in the background, using the same model as the migration (ADR
   0011): a stored run record with a cursor per section, resumable after a failure,
   driven by WP-Cron and nudged by the screen while it is open. The run record holds only
   counts and the first 200 entries worth reading, so saving it after every entry stays
   cheap. The plan and the full report are files beside the upload.
5. **Report.** Created, updated, skipped and failed, per section. Downloadable.

Import and export are unavailable until the migration is complete, on the screen and in
the CLI: a package is read and written in the migrated data model.

### WP-CLI

`wp product-specifications export` and `wp product-specifications import` use the same
code as the screen:

- `export`: `--without-values`, `--file`
- `import`: `--dry-run`

The CLI is how a site schedules recurring imports.

### Out of scope

- Plugin settings.
- CSV in any form.
- v1 export files.
- Post types other than products. The design allows them, but none is built.

## Rationale

- **No ids.** Slugs are the only identity that means the same thing on two stores
  (ADR 0002, ADR 0010).
- **Sections.** Every new kind of data is an addition, never a change to existing code.
- **Planning first.** A merchant sees what will happen before anything is written. That
  is what made v1 imports untrustworthy.
- **The migration's model for running.** It is already proven on real stores, including
  sites where cron never runs.

## Consequences

### Positive

- One feature covers copying a setup, backups and moving values, with no dependency on
  WooCommerce's importer.
- New types and add-ons extend the package without touching the importer.
- The CLI covers automation and scheduled imports.

### Negative

- We own product matching, background running and reporting, which WooCommerce's CSV
  importer would otherwise provide.
- Values cannot be edited in a spreadsheet. That stays with PS-076, deferred.
- Merging cannot propagate deletions: cleared values, and specifications removed from a
  set, stay on the target.
- A same-type specification whose meaning differs between stores cannot be detected for
  certain. The preview warns on name changes and on choices that products still use.

## Alternatives Considered

### Extend WordPress export and the WordPress Importer

Rejected. A post-type-only WXR export has no term definitions, and adding them means
printing extra XML from `rss2_head`. Importing depends on a separate plugin and its
filters, and the merchant gets no plan or report. A full-site WXR move gives terms new
ids, which set structures do not survive. That is not addressed: this package moves data
by slug, and database-copy tools keep ids.

### WooCommerce product CSV columns

Rejected. It cannot carry definitions or sets, and building it next to our own package
would be the same work twice.

### Ask the merchant on every conflict

Rejected. Updating by slug is what a merchant who imports a file means. The plan already
shows what will change before it runs.

### A choice between merging and replacing values

Rejected for now, to keep the import simple. Merging never deletes data the file does
not mention. Replacing would cover exact mirroring between stores, and can be added
later as an option without changing the format.

### Overwrite a specification whose type differs

Rejected. On this store the slug names a different specification, and its existing
values would stop being readable.

### Combine the store's choices with the file's

Rejected. The file is the source of truth for a definition. The preview reports
choices that products still use.

### Replace a product's whole value map

Rejected. A file that carries some specifications would silently delete the others on
the target product.

## Final Decision

One versioned JSON package with slug references and a section registry. Specifications
and sets are always included, and product values are optional in the export and imported
whenever the file carries them. A file that is not a package is rejected whole; a
malformed entry is skipped.

- **Specifications:** a same-type match is updated from the file, and a type mismatch is
  a skipped conflict.
- **Sets:** merged without removing or reordering anything.
- **Product values:** merged. Products are never created, and are matched by SKU, then
  slug.

Everything is planned in the order specifications, sets, values before anything is
written.
Export streams in one request. Import validates, plans, then runs in the background
with a report. The screen and the CLI share the code.
