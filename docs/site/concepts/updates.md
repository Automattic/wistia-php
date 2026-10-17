# Omission, null and replacement

An update is not always a merge. The meaning of an omitted argument, null or an empty value belongs to the operation you are calling.

## Omit a field to leave it unchanged

Many partial-update types use an `Omitted::Value` default internally. You normally do not need to pass that marker: leave the constructor argument out.

For example, a playback update containing only `autoPlay: false` changes that setting without including unrelated settings in the request.

## Null does not mean the same thing everywhere

| Input | Meaning |
| --- | --- |
| Customization partial-update field set to null | Clear that setting; resulting defaults/inheritance are chosen remotely |
| Nullable brand attribute set to null | Clear that attribute |
| Subfolder description set to null | Explicit nullable update, distinct from omission |
| `UpdateMediaRequest(name: null)` | Omit the name; it does not clear it |
| A media custom-metadata key set to null inside its object | Clear that metadata value |
| Webinar update argument set to null | Omit the argument; no null-reset contract is exposed |

Consult the request type and relevant [how-to guide](../index.md#find-the-right-guide) before copying this pattern to another endpoint.

## Empty values can be meaningful

False, zero, an empty string and an empty list are not interchangeable with omission. A request may deliberately send any of them where its type permits.

For media, `tags: []` replaces the tag list with an empty list. For custom metadata, `(object) []` is an empty JSON object, not a list. In account brand preload settings, an empty logo ID clears the logo, while an empty player-color string leaves the color unchanged.

## Replacement needs the whole intended value

`createCustomizations()` replaces explicitly set customizations; it is not a patch. Partial updates use `updateCustomizations()` or the concern-specific update methods.

List-valued settings may replace an entire list even within a partial update. When updating sharing channels, include every channel you want to retain. To add tags without replacing the existing list, use the dedicated bulk-tag operation rather than a media update's `tags` field.

## Response values may have a different type

Customization results preserve documented string values such as `'false'` and `'0'`. PHP considers `'false'` truthy. Compare the documented value explicitly instead of treating it as a boolean.

Typed requests/results prevent many accidental shape errors, but cannot decide whether a replacement or reset matches your application's intent.
