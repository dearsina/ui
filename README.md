# Common UI elements
Common UI elements built on Bootstap.

## Status badges

Use `StatusBadge` for a compact icon-and-text status indicator. Text is escaped
unless the `html` extra is explicitly enabled.

```php
use App\UI\StatusBadge;

StatusBadge::positive("Matched");
StatusBadge::negative("Mismatch", ["tooltip" => "The submitted value differs."]);
StatusBadge::neutral("Not compared");
StatusBadge::generate("Pending", "clock", "orange", ["class" => "my-status"]);
StatusBadge::positive("مطابق", ["rtl" => true]);
```

## Data points

`DataPoint` renders a labelled value with optional comparison context. Matching
remains the caller's responsibility, so the component can be reused with any
comparison methodology.

```php
use App\UI\DataPoint;

DataPoint::generate("Company number", "04366849", ["type" => "code"]);
DataPoint::inline("Status", StatusBadge::positive("Active"), ["inverse" => true]);

DataPoint::compared("Company name", "SHELL PLC", "Shell Limited", false, [
    "comparison" => [
        "title" => "Client submitted",
        "tooltip" => "The names do not match.",
    ],
]);

DataPoint::link("Company profile", "Official company overview", [
    "icon" => "building",
    "url" => "/company/04366849",
    "alt" => "Open the company profile",
]);

DataPoint::matrix([
    ["title" => "Company name", "value" => "SHELL PLC"],
    ["title" => "Company number", "value" => "04366849", "type" => "code"],
    [
        "variant" => "link",
        "title" => "Filing history",
        "subtitle" => "Accounts, statements and other filings",
        "icon" => "file-alt",
        "url" => "/company/04366849/filing-history",
    ],
], [
    "columns" => 2,
    "flow" => DataPoint::FLOW_COLUMN,
]);
```

Values allow trusted HTML by default. Use `type => "text"` to escape a value.
Types are open-ended CSS modifiers, allowing money, percentage, image,
signature, and other renderers to be added without changing the public API.
Linked data points use a right chevron by default, switch it automatically for
RTL layouts, and accept any link settings supported by the common `href` helper.

## Panels

`Panel` is a lightweight content section with an optional padded icon, required
title, unrestricted body, and optional footer. It uses one uninterrupted visual
surface by default; enable `footer_separator` only when the footer needs a
divider.

```php
use App\UI\Panel;

Panel::generate("Next accounts", DataPoint::matrix($data_points), [
    "icon" => "calendar-check",
    "colour" => "primary",
    "stretch" => true,
    "footer" => StatusBadge::positive("Filed"),
]);
```

The body and footer accept trusted HTML, `Stringable` objects, or nested arrays
of rendered fragments. This allows panels to contain DataPoints, forms, tables,
messages, images, or any other UI components without knowing their structure.

## Responsive composition

`Grid::responsive()` lays out arbitrary rendered fragments. It supports one to
twelve columns, row- or column-major flow, named or custom gaps, item spans,
equal-height children, and configurable collapse breakpoints. `DataPoint::matrix()`
uses this same grid internally.

```php
use App\UI\Grid;

Grid::responsive([
    ["html" => $overview, "span" => "all"],
    $left,
    $right,
], [
    "columns" => 2,
    "gap" => "lg",
    "collapse_at" => "lg",
    "stretch" => true,
]);
```

`SplitView` is the two-pane companion for media/details, comparisons, previews,
and other paired content. Each pane accepts arbitrary HTML and independent
padding, surface, alignment, and overflow settings.

```php
use App\UI\SplitView;

SplitView::generate($preview, $details, [
    "ratio" => "equal",
    "height" => "lg",
    "collapse_at" => "lg",
    "second_settings" => ["padding" => "md", "surface" => "soft"],
]);
```

## Media previews and timelines

`MediaPreview` renders an image or arbitrary embedded media, can turn the whole
surface into a link, and supplies a consistent icon/title/subtitle fallback.

```php
use App\UI\MediaPreview;

MediaPreview::generate([
    "src" => $image_url,
    "alt" => "Registered office",
    "url" => $maps_url,
    "height" => "lg",
    "zoom" => true,
]);
```

`Timeline` accepts ordered items with optional title, meta, body, aside, footer,
marker/icon, and colour regions. Those regions can contain DataPoints, badges,
comparison icons, or any other rendered component.

```php
use App\UI\Timeline;

Timeline::generate([
    [
        "title" => "Previous name",
        "meta" => "1 January 2020 to 31 December 2024",
        "aside" => StatusBadge::positive("Matched"),
        "footer" => DataPoint::inline("Client submitted", "Previous name"),
    ],
]);
```

Cards also accept `variant => "soft"`, `"flush"`, or `"emphasis"` (or an
array containing several variants). The emphasis variant combines with the
existing `accent` setting for reusable status-coloured headers and borders;
add `inverse` when that emphasis header should use light foreground content.

## Dense print layouts

Wrap a component-heavy printable report in `ui-print-layout--dense` when its
screen layout should remain responsive but its configured Grid and SplitView
columns should be preserved on A4 paper. The wrapper has no screen effect. In
print media it restores nested column counts and item spans after responsive
breakpoints run, shortens oversized split-view media, reduces component
whitespace, and keeps individual panels together where possible.

```html
<div class="ui-print-layout ui-print-layout--dense">
    <!-- Cards, Panels, Grids, SplitViews, DataPoints, and accordions -->
</div>
```

This is opt-in because some reports intentionally use a single-column print
layout. A dense layout can span multiple pages; accordion cards may therefore
break between their child panels rather than being pushed wholesale to a new
page.

For repeated records that should be separated by pages, add
`ui-print-page-sequence` to their containing section and `ui-print-page` to
each record. The sequence starts on a new page, every record except the last
adds a page break after itself, and an oversized record can still continue onto
an additional page without clipping content.

```html
<section class="ui-print-page-sequence">
    <article class="ui-print-page">First record</article>
    <article class="ui-print-page">Second record</article>
</section>
```
