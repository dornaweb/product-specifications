# ADR: Default Value Resolution

## Status

Approved

## Context

A specification can carry a default, so that a merchant filling in a product does not
retype the same answer on every product.

v1 has one level and one storage location: `attr_default` on the specification term,
read by `Attribute\AttributeFieldFactory::createFromWpTerm()`. The field templates apply
it in one expression:

```php
$attribute->value() ?? $attribute->default()
```

That makes it a **prefill in the form**. It becomes stored data only when the merchant
saves the product. The frontend never sees it, because v1 renders the product snapshot
and a product that was never saved holds nothing.

There is no set-level default in v1. A `Warranty` specification has one default
everywhere it is used.

This ticket was written proposing two levels, a set item default winning over the
specification default, with the value "written to the product the first time the editor
opens the field". Both parts are rejected below.

## Decision

### One level

The default lives on the specification term, in `attr_default`, exactly as in v1. There
is no set item default, no `default` member on the structure's set item, and therefore
no precedence rule to state or implement.

ADR 0001's premise stands unamended: the set structure stores composition, and the
canonical definition of a specification stays on its term. A default is part of that
definition.

### A default is a prefill, and nothing else

It is shown in the product editor when no value is stored. It never resolves at read, it
never appears on the frontend, and it is not consulted when rendering a table.

A product with no stored value for a specification has no value for it. Whether its row
is hidden or shown with a placeholder is ADR 0004's `empty_rows` setting, not a question
about defaults.

### It is written when the product is saved

A prefilled field is submitted like any other field, sanitized like any other value, and
stored like any other value. There is no separate write path and no special marker
distinguishing a stored default from a typed value.

The ticket's original proposal — writing the value "the first time the editor opens the
field" — is rejected. A read that mutates data is wrong on its own terms, and here it
would mean that opening a product edit screen and navigating away had silently written
values to that product.

Once stored, a value is a value. Changing the specification's default afterwards does not
touch any product that already holds one, which is the property the original proposal was
reaching for and which saving on save already provides.

### It is authored in the type's own control

The specification editor draws the default with the control the type registers, not with a
text box. A free-text default is sanitized through the type on save, so a string the type
cannot read becomes no default at all, silently and after the editor has left the field.
Offering only what the type accepts means the value that appears saved is the value stored.

A type that registers no such control takes no default. The fallback would be the text box
this rule exists to remove, in the one case — a type whose sanitizer is not visible here —
where the mismatch is least discoverable.

### It is applied on the client

The product edit panel's field component shows the default when the value map has no key
for that specification. The server never injects a default into a value map, so REST
returns stored values and nothing else, and no consumer has to work out whether a value
it received was real or filled in.

### Interactions already settled elsewhere

| Case | Rule | Settled by |
| --- | --- | --- |
| A specification whose value comes from elsewhere | No default. There is no input to prefill | ADR 0005 |
| A variation | Merge parent and variation first, then prefill the effective value. One rule, applied once | ADR 0006 |
| A default invalid for the current type | Ignored, exactly as a stored value the type cannot read is | ADR 0002 |
| Changing a specification's type | The default clears with the options. A value authored for one type means nothing under another | ADR 0003 |

## Rationale

The two-level model was proposed because a set is where a default is most useful: a
`Warranty` specification could default to two years in a phone set and one year in a
laptop set.

It was rejected because the cost is larger than it looks. It puts a value into a document
whose entire premise is that it holds no values, it adds a precedence rule to explain in
the interface, and it makes the same specification behave differently depending on which
set is being edited — while the value it produces is stored on the product identically
either way.

Rendering defaults at read was rejected for a different reason. It is a genuinely more
powerful feature — a default becomes "the value unless overridden", and changing it
updates every product that has not overridden it. It also erases the difference between
"nobody filled this in" and "this is the answer", which is exactly the distinction a
merchant needs when auditing a catalogue, and it makes a live catalogue change on a
specification edit with no product save in sight.

v1's behavior is the safe one, and nothing in the v2 feature set depends on more.

## Consequences

### Positive

- The smallest possible change: the storage, the meta key and the behavior are v1's.
- The structure document stays free of values.
- No precedence rule, in the code or in the interface.
- Nothing is written outside a save, so no read has a side effect.
- REST returns stored values only, with no ambiguity about their provenance.

### Negative

- A merchant wanting a different default per set must create two specifications and lose
  the reuse. This is v1's limitation, carried forward deliberately.
- A default changed after products were saved does not propagate. Reaching those products
  means editing them, which is the same as v1.
- A default is applied only where the client applies it. A product created through the
  REST API or WP-CLI, with no editor involved, gets no defaults.

## Effects on other tickets

- **PS-029, per-item set options**, is currently scoped as "per-item visible and
  switch, and the set-level default value". The second item does not exist.
  `visible` is already a member of the structure schema, so what remains
  is interface work.
- **PS-029's dependency on this ticket disappears.** It depended on PS-017 only for the
  set-level default.

## Alternatives Considered

### A set item default winning over the specification default

Rejected. It is the ticket's original proposal. It puts a value into the composition
document, adds a precedence rule, and delivers a result indistinguishable from the
one-level model once the product has been saved.

### Defaults resolved at read, rendering on the frontend

Rejected. More powerful, and it makes a default into a live catalogue value: changing one
would change product pages with no product save involved, and no merchant could tell an
unfilled specification from a deliberately-answered one.

### Defaults on the set item only, dropping the specification default

Rejected. One level with no precedence rule, like this decision, but it requires
migrating every `attr_default` into every set that uses the specification, and a
specification not yet in any set could carry no default at all.

### Writing the default to the product when the editor opens the field

Rejected. A read with a side effect. Opening a product edit screen and closing it would
write values to that product.

## Final Decision

```txt
attr_default    on the specification term, unchanged from v1
```

One level. A prefill in the editor, applied on the client, stored when the product is
saved. Never resolved at read, never rendered. The set structure holds no defaults.
