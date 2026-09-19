# Taste
- Avoids Filament's Repeater field because it consumes too much space; prefers compact alternatives (e.g. a multi-select plus a read-only table rendered via a View component) for repeatable data. Confidence: 0.6
- Prefers staying within framework-native components instead of writing custom Laravel Blade views/templates (e.g. explicitly asks not to create a new Blade file for a table). Confidence: 0.7
- Builds Laravel admin UIs with Filament (currently v5). Confidence: 0.65
- Wants Select fields for multi/pivot selection to act as append-only "add" pickers: once an item is picked it should disappear from the Select and be removed from its options, with the chosen items surfaced only in the accompanying summary table. Confidence: 0.7
- Wants row-level actions (e.g. a delete/trash button) rendered inline inside the summary table rows, using native framework actions. Confidence: 0.65
- Expects API price/money fields to serialize consistently — formatted as decimal strings (e.g. `"1.00"`) like the underlying `decimal(10,2)` column, not as raw JSON numbers with dropped trailing zeros. Confidence: 0.55
