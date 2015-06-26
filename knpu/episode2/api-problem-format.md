# 99 api/problem+json(s)

Time for me to reveal why I chose this error response format with `type`, `title`
and `errors`. Imagine if every JSON API returned the same format when things went
wrong: always with these keys. That'd be pretty awesome. As API client, we'd always
know what to expect and what things mean. That's a beautiful fairy tale.

In the real world, every API does whatever they want. But there are people out there
working on standards for error responses, with the hope that someday, API's have
some consistency. 

## The API Problem Details Format

One of those is called [api problem details](https://tools.ietf.org/html/draft-nottingham-http-problem-07).
Google for that. Ah, a boring spec document. Go ahead and read this whole thing,
I'll wait... Kidding! I'll show you the good parts.

But first - click the [draft ietf](https://tools.ietf.org/html/draft-ietf-appsawg-http-problem-00)
link. These drafts go through version, and this one has been replaced with a whole
new document. And yea, these *are* just drafts - not official specs. But who cares?
If a spec makes sense to you, why not follow it instead of making up your own format.

Scroll down to the [example response](https://tools.ietf.org/html/draft-ietf-appsawg-http-problem-00#section-3).
Two important things here. First, if you follow this spec, you should return a custom
`Content-type` response header to advertise this: `application/problem+json`. When
a client sees this - they can research it to find out what the fields in the response
*mean* in human terms.

Second, check out the fields: the main ones are `type` and `title`. `type` is a
unique string for *what* went wrong. It's supposed to be a URL - our's is just a
key. We'll revisit that later. Next, this says `title` is a human-readable version
of `type` and there are a bunch of other optional fields. The [Extension Members](https://tools.ietf.org/html/draft-ietf-appsawg-http-problem-00#section-3.2)
section says that you can also add whatever other fields you want. We're adding
an extra `errors` key.

## Adding the application/problem+json Header

So we're already following this format - or at least we're pretty close. So I want
to advertise this to our clients so they can dig into what each key means. Copy the
`application/problem+json` `Content-Type` header so we can use it.

First, check for this in the test: `$this->assertEquals()` with `application/problem+json`
as the expected value and `$response->getHeader('Content-Type')` for the actual value:

[[[ code('7dcb615e79') ]]]

Make sure it fails - run *just* this test:

```bash
bin/phpunit -c app --filter testValidationErrors
```

Yep, it fails. Now go to `ProgrammerController` and find `createValidationErrorResponse`.
Instead of returning `JsonResponse`, set it to a `$response` variable and then call
`$response->headers->set()` with `Content-Type` and `application/problem+json`. Return
this:

[[[ code('dcd9b82b84') ]]]

See if this fixed the test:

```bash
bin/phpunit -c app --filter testValidationErrors
```

Error!

    Attempted to call function JsonResponse from namespace "AppBundle\Controller\Api"

That looks like a "Ryan" mistake - I deleted the `new` keyword before `JsonResponse`.
You probably saw me do that. Put it back and *now* try the tests:

Beautiful green! And now we're advertising our special error format.
