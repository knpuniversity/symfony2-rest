# Weird Endpoint: The tagline as a Resource?

Most of our endpoints are pretty straightforward: We create a programmer, we update
a programmer, we create a battle, we get a collection of battles.

Reality check! In the wild: endpoints get weird. Learning how to handle these was
one of the most *frustrating* parts of REST for me. So let's code through two examples.

## Updating *just* the Tagline?

Here's the first: suppose you decide that it would be really nice to have an endpoint
where your client can edit the `tagline` of a programmer directly.

Now, technically, that's already possible: send a `PATCH` request to the programmer
endpoint and only send the `tagline`.

But remember: we're building the API for our API clients, and if they want an endpoint
*specifically* for updating a `tagline`, give it to them.

Open `ProgrammerControllerTest`: let's design the endpoint first. Make a
`public function testEditTagline()`:

[[[ code('1c867612f2') ]]]

Scroll to the top and copy the `$this->createProgrammer()` line that we've been using.
Give this a specific tag line: `The original UnitTester`:

[[[ code('f831cf257b') ]]]

## The URL Structure

Now, if we want an endpoint where the *only* thing you can do is edit the `tagLine`,
how should that look?

One way to think about this is that the `tagLine` is a *subordinate* *string* resource
of the programmer. Remember also that every URI is supposed to represent a different
resource. If you put those 2 ideas together, a great URI becomes obvious:
`/api/programmers/UnitTester/tagline`. In fact, if you think of this as its own
resource, then all of a sudden, you could imagine creating a `GET` endpoint to fetch
*only* the `tagline` or a `PUT` endpoint to update *just* the `tagline`. It's a cool
idea!

And that's what we'll do: make an update request with `$this->client->put()` to this
URL: `/api/programmers/UnitTester/tagline`:

[[[ code('80e4acfad8') ]]]

## How to send the Data?

Send the normal `Authorization` header:

[[[ code('ba303d4862') ]]]

But how should we pass the new `tagline` data? Normally, we send a json-encoded array
of fields. But this resource isn't a collection of fields: it's just *one* string.
There's nothing wrong with sending some JSON data up like before, but you could also
set the `body` to the plain-text `New Tag Line` itself:

[[[ code('9f79ac11d5') ]]]

And I think this is pretty cool.

Finish this off with `$this->assertEquals()` 200 for the status code:

[[[ code('f213059a3e') ]]]

But what should be returned? Well, whenever we edit or create a resource, we return the
resource that we just edited or created. In this context, the `tagline` *is* its own
resource... even though it's just a string. So instead of expecting JSON, let's look
for the literal text: `$this->assertEquals()` that `New Tag Line` is equal to the
string representation of `$response->getBody()`:

[[[ code('53a5e5f8c5') ]]]

But you don't *need* to do it this way: you might say:

> Look, we all know that you're *really* editing the `UnitTester` programmer
  resource, so I'm going to return that.

And that's fine! This is an interesting *option* for how to think about things.
Just as long as you don't spend your days dreaming philosophically about your API,
you'll be fine. Make a decision and feel good about it. In fact, that's good life
advice.

## Adding the String Resource Endpoint

Let's finish this endpoint. At the bottom of `ProgrammerController`, add a new
`public function editTagline()`:

[[[ code('cf7e202da6') ]]]

We already know that the route should be `/api/programmers/{nickname}/tagline`.
To be super hip, add an `@Method` annotation: we know this should only match `PUT` requests:

[[[ code('64161c3c8d') ]]]

Like before, type-hint the `Programmer` argument so that Doctrine will query for
it *for* us, using the `nickname` value. And, we'll also need the `Request` argument:

[[[ code('d8b53f450b') ]]]

I *could* use a form like before... but this is just *so* simple:
`$programmer->setTagLine($request->getContent())`:

[[[ code('bf4e360c2b') ]]]

Literally: read the text from the request body and set that on the programmer.

Now, save: `$em = $this->getDoctrine()->getManager()`, `$em->persist($programmer)`
and `$em->flush()`:

[[[ code('16b03773d5') ]]]

For the return, it's not JSON! Return a plain `new Response()` with `$programmer->getTagLine()`,
a 200 status code, and a `Content-Type` header of `text/plain`:

[[[ code('d984d37d9e') ]]]

Now, this is a good-looking, super-weird endpoint. Copy the test method name and try
it out:

```bash
./vendor/bin/phpunit --filter testEditTagLine
```

We're green! Next, let's look at a *weirder* endpoint.
