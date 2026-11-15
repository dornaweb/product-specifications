# Specification types

A specification's type decides how its value is entered on the product screen and how it
is shown on your storefront.

| Type | Records |
| --- | --- |
| [Text](#text) | A short free-form answer, optionally with suggestions or several values |
| [Paragraph](#paragraph) | Several lines of free-form text |
| [Single select](#single-select) | One answer from a fixed list you define |
| [Yes or no](#yes-or-no) | A single yes or no |
| [Number](#number) | A number or a range, with an optional unit such as mm, kg or GB |
| [WooCommerce product data](#woocommerce-product-data) | Something the product already has, such as its weight, SKU or brand |
| [Document](#document) | Files from the media library, such as datasheets, manuals or certificates |
| [Link](#link) | A web address, email address or phone number, with an optional label |
| [Date](#date) | A day, a month or a year, such as a release date |

## Text

A short answer, such as a processor model.

- **Suggest values while typing** offers values as you type: a list you write, plus
  values already used on your products, the most used first. Variants such as `blue `
  and `Blue` count as one value. Any other value can still be typed.
- **Allow several values per product** turns the input into tags, such as the ports of a
  laptop. They are shown on your storefront separated by commas. Turning this on or off
  rewrites no product.

## Paragraph

Several lines of text, such as care instructions. Line breaks are kept on your storefront.

## Single select

One answer from a list you define, one option per line. A product can only hold one of
the options, so the same answer is always spelled the same way.

## Yes or no

A yes-or-no answer, such as *Touchscreen*. **Show on the store as** chooses between check
and cross icons and text. With text, **Wording** sets the words for yes and no, separated
by `|`, such as `Included|Not included`. Leave it empty to use the translated *Yes* and
*No*.

## Number

A number, or a range such as *100–240 V*.

| Option | What it does |
| --- | --- |
| **Unit** | No unit, one of 17 kinds (length, weight, volume, area, temperature, data size, data rate, frequency, power, voltage, current, battery, energy, duration, speed, light, sound), or units you define under *Something else*. |
| **Units** | Which units of the kind a product can be given. Values are shown as entered, never converted. |
| **Default unit** | The unit a new value starts with. |
| **Decimal places** | 0 for whole numbers, up to 4. |
| **Range** | Allow a range, such as 100–240 V. |
| **Thousands** | Separate thousands, such as 1,000. Turn it off for years and codes. |
| **Minimum** and **Maximum** | Optional bounds for the number, whatever unit is chosen. |

## WooCommerce product data

Shows something the product already has, so nothing is typed twice and the table never
goes stale. Choose the product data:

- Weight, Dimensions (L × W × H), Length, Width or Height
- SKU, or GTIN, UPC, EAN or ISBN
- Product brands, categories or tags
- A product attribute, by name

The value is read when the table is shown. On the product screen it is shown as read-only,
with where it comes from, such as *Read from the product's Weight*.

## Document

Attach files from the media library, each with an optional label. Your storefront shows
one download link per file with its type and size, such as *Datasheet (PDF, 1.2 MB)*.

## Link

A URL with an optional label: a web page, a `mailto:` address or a `tel:` number. It is
shown as the label, or as the host, address or number when there is no label. Every link
opens in a new tab with `rel="nofollow noopener"`.

## Date

A day, a month or a year, chosen per specification with **Precision**: choose a month or
a year when the day is not known, such as a release in March 2026. Dates are entered
with a picker and shown in your site's language; a full date follows your site's date
format.
