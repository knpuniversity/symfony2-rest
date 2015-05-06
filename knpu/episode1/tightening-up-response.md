## Tightening up the Response

This endpoint is missing two teeny-tiny details.

## Setting Content-Type: application/json

First, we're returning JSON, but the Response `Content-Type` is still advertising
that we're returning `text/html`. That's a bummer, and will probably confuse some
clients, like jQuery's AJAX function.

It's easy to fix anyways: set `new Response` to a `$response` variable
like we did earlier and call `$response->headers->set()` with `Content-Type`
and `application/json`:

[[[ code('41d08fe64b') ]]]

Check out the new Content-Type header:

```bash
php testing.php
```

## 404'ing

The second teeny-tiny thing we're missing is a 404 on a bad `$nickname`.
Just treat this like a normal controller - so `if (!$programmer)`, then
`throw $this->createNotFoundException()`. And we might as well give ourselves
a nice message:

[[[ code('b1b79d3367') ]]]

Use a fake nickname in `testing.php` temporarily to try this:

[[[ code('f678e03161') ]]]

Then re-run:

```bash
php testing.php
```

Woh! That exploded! This is Symfony's HTML exception page. It *is* our 404
error, but it's in HTML instead of JSON. Why? Internally, Symfony has a
request format, which defaults to `html`. If you change that to `json`, you'll
get JSON errors. If you're curious about this, google for `Symfony request _format`.

But I'll show you this later in the series. And we'll go one step further
to *completely* control the format of our errors. And it will be awesome.

Change the URL in `testing.php` back to the real nickname.

## Setting the Location Header

Ok, remember that fake `Location` header on the POST endpoint? Good news!
We can get rid of that fake URL.

First, give the GET endpoint route a name - `api_programmers_show`:

[[[ code('658aea2c07') ]]]

Copy that, call `$this->generateUrl()`, pass it `api_programmers_show`
and the array with the `nickname` key set to the nickname of this new
Programmer. Then just set this on the `Location` header... instead of our
invented URL:

[[[ code('5184bd4170') ]]]

Why are we doing this again? Just because it might be helpful to your client
to have the address to the new resource. That would be especially true if
you used an auto-increment id that the server just determined.

To try this in `testing.php`, copy the `echo $response` stuff, put it below
the first `$response`, then let's die:

[[[ code('0a26e9f157') ]]]

Now, try `php testing.php`:

```bash
php testing.php
```

Now we have a *really* clean `Location` header we could use to fetch or edit
that Programmer.

## Use the Location Header

Heck, we can even use this and get rid of the hardcoded URL in `testing.php`.
Set `$programmerUrl` to `$response->getHeader('Location')`. Pop that in to
the next `get()` call:

[[[ code('ebb991bf97') ]]]

I like that! When you're testing your API, you're really eating your own
dog food. And that's a perfect time to think about the user-experience of
getting work done with it.

Try it one last time:

```bash
php testing.php
```

That looks great!
