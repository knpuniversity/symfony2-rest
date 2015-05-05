# GET a Collection of Programmers

Our API client will need a way to fetch a collection of programmers. Piece
of cake. Start with `public function listAction()`. For the URI, use
`/api/programmers` and add an `@Method("GET")`:

[[[ code('0ed0a4d8dc') ]]]

So, the URI you POST to when creating a resource - `/api/programmers` -
will be the same as that you'll GET to fetch a collection of programmer
resources. And yes, you can filter and paginate this list - all stuff we'll
do later on.

Inside `listAction()`, start like we always do: with a query.
`$programmers = $this->getDoctrine()->getRepository('AppBundle:Programmer')`
and for now, we'll find everything with `findAll()`:

[[[ code('ddefedb47b') ]]]

Next, we need to transform this array of Programmers into JSON. We'll want
to re-use some logic from before, so let's create a new private function
called `serializeProgrammer()` and add a `Programmer` argument. Inside, we
can steal the manual logic that turns a Programmer into an array and just
return it:

[[[ code('25700e652d') ]]]

That's a small improvement - at least we can re-use this stuff from inside
this controller. In `showAction()`, use `$this->serializeProgrammer()` and
pass it the variable.

[[[ code('a8b61796f6') ]]]

Back in `listAction()`, we'll need to loop over the Programmers and serialize
them one-by-one. So start by creating a `$data` array with a `programmers`
key that's also set to an empty array. We're going to put the programmers
there:

[[[ code('964ccb08d2') ]]]

## Avoid JSON Hijacking

Why? You can structure your JSON however you want, but by putting the collection
inside a key, we have room for more root keys later like maybe `count` or
`offset` for pagination. Second, your out JSON should always be an object,
not an array. So, curly braces instead of square braces. If you have square
braces, you're vulnerable to something called JSON Hijacking.

## Turning the Programmers into JSON

Loop through `$programmers`, and one-by-one, say `$data['programmers'][]`
and push on `$this->serializeProgrammer()`. The end is the same as `showAction()`,
so just copy that:

[[[ code('d7f7cb0a15') ]]]

That ought to do it. Update `testing.php` to another call - out to
`/api/programmers`. Let's see what that looks like:

```bash
php testing.php
```

Woh, ok! We've got a database full of programmers. Creating a new endpoint
is getting easier - that trend will continue.

## Returning JSON on Create

Remember how we're returning a super-reassuring text message from our POST
endpoint? Well, you *can* do this, but usually, you'll return the resource
you just created. That's easy now - so let's do it. Just, `$data = $this->serializeProgrammer()`.
Then just `json_encode()` that in the Response. And don't forget to set the
`Content-Type` header:

[[[ code('d7feec0fcd') ]]]

To see if this is working, dump again right after the first request:

[[[ code('7ac8735e18') ]]]

Hit it!

```bash
php testing.php
```

That's a helpful response: it has a `Location` header *and* shows you the
resource immediately. Because, why not?

## JsonResponse

We're about to move onto testing - which makes this all *so* much fun. But
first, we can shorten things. Each endpoint `json_encode`s the data *and*
sets the `Content-Type` header. Use a class called `JsonResponse` instead.
And instead of passing it the JSON string, just pass it the data array. The
other nice thing is that you don't need to set the `Content-Type` header,
it does that for you:

[[[ code('cd1f608d3d') ]]]

API consistency is king, and this is just one less spot for me to mess up
and forget to set that header. Let's go down and update the other spots,
which is pretty much a copy-and-paste operation - just make sure you keep
the right status code.

Take out the extra `die()` statement in `testing.php` and let's try this
*whole* thing out:

```bash
php testing.php
```

It's lovely. To make sure it doesn't break, we need to add tests! And that
will be a whole lot more interesting than you think.
