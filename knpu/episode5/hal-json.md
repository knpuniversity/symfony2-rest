# The HAL  JSON Standard

Google for "How to remove a mustard stain from a white shirt". I mean, Google for
"HAL JSON" - sorry, it's after lunch.

This is one of a few competing *hypermedia* formats. And remember, *hypermedia* is
one of our favorite buzzwords: it's a media type, or format, - like JSON - plus some
rules about how you should semantically organize things inside that format. In human
speak, HAL JSON says:

> Hi I'm HAL! If you want to embed links in your JSON, you should put them under
  an `_links` key and point to the URL with `href`. Have a lovely day!

If you think about it, this idea is similar to HTML. In HTML, there's the XML-like
format, but then there are rules that say:

> Hi, I'm HTML! If you want a link, put it in an `<a>` tag under an `href` attribute.

The advantage of having standards is that - since the entire Internet follows them -
we can create a browser that understands the significance of the `<a>` tag, and
renders them clickable. In theory, if all API's followed a standard, we could create
clients that easily deal with the data.

## Updating Programmer to use the new Links

So let's also update the `Programmer` entity to use the new system. Copy the whole
`@Relation` from `Battle`:

[[[ code('f55af4496a') ]]]

And replace the `@Link` inside of `Programmer`. Change the `rel` back to `self`
and update the expression to `object.getNickname()`:

[[[ code('5980ec64b3') ]]]

Make sure you've got all your parenthesis in place. Oh, and don't forget to bring
over the `use` statement from `Battle`.

In `ProgrammerControllerTest`, the `testGetProgrammer` method looks for `_links.self`:

[[[ code('310f8b3079') ]]]

Add `.href` to this to match the new format:

[[[ code('') ]]]

Try it out!

```bash
vendor/bin/phpunit --filter testGetProgrammer
```

Yes!

## Should I Use HAL JSON?

So why use a standardized format like Hal? Because now, we can say:

> Hey, our API returns HAL JSON responses!

Then, they can go read its documentation to find out what it looks like. Or better,
they might already be familiar with it!

## Advertising that you're using Hal

So now that we are using Hal, we should advertise it! In fact, that's what this
`application/hal+json` means in their documentation: it's a custom `Content-Type`.
It means that the format is JSON, but there's some extra rules called Hal. If a
client sees this, they can Google for it.

In `ProgrammerControllerTest`, assert that `application/hal+json` is equal to
`$response->getHeader('Content-Type')[0]`:

[[[ code('3055f2e1e3') ]]]

Guzzle returns an array for each header - there's a reason for that, but yea,
I know it looks ugly.

To actually advertise that our API returns HAL, open `BaseController` and search
for `createApiResponse()` - the method we're calling at the bottom of *every* controller.
Change the header to be `application/hal+json`:

[[[ code('ec5a2aa39d') ]]]

Nice! Copy the test name and re-run the test:

```bash
./vendor/bin/phpunit --filter testPOSTProgrammerWorks
```

Congratulations! Your API is no longer an island: welcome to the club.
