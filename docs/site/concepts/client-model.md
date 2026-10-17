# Services, requests and results

The client separates application configuration from the operation you want to perform. Understanding that separation makes it easier to find an API method and read its response.

## One client, several services

A `Client` uses a `Configuration` and an HTTP transport. Pass the same client to the services your application needs. Services group related operations: `DataMediaService` manages media, `UploadService` uploads files, and `AnalyticsMediaService` reads media analytics.

Creating these objects sends no request. An operation runs only when you invoke a service method.

There is no magic method dispatch or legacy compatibility layer. Choose a service from the [service directory](../reference/services.md), then use its named request type. The low-level `Client::execute()` primitive is not an arbitrary-endpoint API catalog.

## Typed input and output

A request object makes the supported fields explicit. For example, `ListMediaRequest(per_page: 25)` selects one media page; it does not read the page when constructed.

Most operation results have `data` and `status`, but the shape of `data` depends on the method. It may be a record, a list, text or null. Job-status/bulk results expose `backgroundJobStatus`; account Analytics media lookup exposes `media_hashed_ids`. Check the generated result type rather than assuming every operation returns the same envelope.

The client checks documented shapes and types. Malformed successful responses throw `DecodeException` rather than silently returning an empty result.

## Field names are deliberate

Results generally retain Wistia's field names: `hashed_id`, `embedCode` and `pageLoads` are not automatically converted to one naming style. Some specialized helper results use their own typed names.

Do not cast every record to an array or expect a universal media object. Upload media, Data media and Analytics records are different types.

## Missing is different from null

Many response records have `presentFields`. An optional absent field may appear as PHP null, while `presentFields` tells you it was not sent. A field explicitly returned as null is present in that list when its schema allows null.

This matters when copying values into your own storage or forming an update. An absent response value is not necessarily an instruction to clear it. [Update semantics](updates.md) explains the input side of this distinction.

## The application still owns the workflow

The library does not store tokens, log requests, schedule work, process webhooks or manage application transactions. Ordinary debug views hide payloads, but reading a property or serializing data can expose names, transcripts, URLs and other sensitive information.

Keep persistence, logging and access controls at your application boundary. A typed result describes the response; it does not prove account entitlement or remote processing completion.
