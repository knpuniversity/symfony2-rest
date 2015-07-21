# Handling 404's + other Errors

What should the structure of a 404 response from our API look like? It's obvious:
we'll want to return that same API Problem JSON response format. We want to return
this whenever *anything* goes wrong.

## Planning the Response

Start by planning out how the 404 should look with a new test method - `test404Exception`.
Let's make a GET request to `/api/programmers/fake` and assert the easy part: that
the status code is 404. We also know that we want the nice `application/problem+json`
`Content-Type` header, so assert that too:

[[[ code('c7abf5548f') ]]]

We know the JSON will at least have `type` and `title` properties. So what would be
good values for those? This is a weird situation. Usually, `type` conveys *what*
happened. But in this case, the 404 status code already says exactly what happened.
Using some `type` value like `not_found` is fine, but totally redundant.

Look back at the [Problem Details Spec](https://tools.ietf.org/html/draft-ietf-appsawg-http-problem-00).
Under "Pre-Defined Problem Types", it says that if the status code is enough, you
can set `type` to `about:blank`. And when you do this, it says that we should set
`title` to whatever the standard text is for that status code. For a 404, that's
"Not Found".

Add this to the test: use `$this->asserter()->assertResponsePropertyEquals()` to
assert that `type` is `about:blank`. And do this all again to assert that `title`
is `Not Found`:

[[[ code('383ee7437b') ]]]

## How 404's Work

A 404 happens whenever we call `$this->createNotFoundException()` in a controller.
If you hold cmd or ctrl and click that method, you'll see that this is just a shortcut
to throw a special `NotFoundHttpException`. And *all* of the other errors that might
happen will ultimately be just different exceptions being thrown from different parts
of the code.

The only thing that makes *this* exception special is that it extends that very-important
[HttpException](httpexception-invalid-json) class. That's why throwing this causes
a 404 exception. But otherwise, it's just a normal exception.

## Handling *all* Errors

In `ApiExceptionSubscriber`, we're only handling ApiException's so far. But if we
handled *all* exceptions, we could turn *everything* into the nice format we want.

Reverse the logic on the `if` statement and set the `$apiProblem` inside:

[[[ code('19eba6836a') ]]]

Add an `else`. In all other cases, we'll need to create the `ApiProblem` ourselves.
The first thing we need to figure out is what status code this exception should have.
Create a `$statusCode` variable. Here, check if `$e` is an `instanceof` `HttpExceptionInterface`:
that special interface that lets an exception control its status code. So if it is,
set the status code to `$e->getStatusCode()`. Otherwise, we have to assume that it's
500:

[[[ code('d7c01d960c') ]]]

Now use this to create an `ApiProblem`: `$apiProblem = new ApiProblem()` and pass
it the `$statusCode`:

[[[ code('4a9dd6543a') ]]]

For the `type` argument, we *could* pass `about:blank` - that *is* what we want.
But then in `ApiProblem`, we'll need a constant for this, and that constant will
need to be mapped to a title. But we actually want the title to be dynamically based
on whatever the status code is: 404 is "Not Found", 403 is "Forbidden", etc. So,
don't pass *anything* for the `type` argument. Let's handle all of this inside
`ApiProblem` itself.

In there, make the `$type` argument optional:

[[[ code('3f001f9d29') ]]]

And *if* `$type` is exactly `null`, then set it to `about:blank`. Make sure the
`$this->type = $type` assignment happens *after* all of this:

[[[ code('027b00861c') ]]]

For `$title`, we just need a map from the status code to its official description.
Go to Navigate->Class - that's cmd+o on a Mac. Look for `Response` and open the one
inside `HttpFoundation`. It has a really handy public `$statusTexts` map that's exactly
what we want:

[[[ code('f8ab580a34') ]]]

Set the `$title` variable - but use some `if` logic in case we have some weird status
code for some reason. If it *is* in the `$statusTexts` array, use it. Otherwise,
well, this is kind of a weird situation. Use `Unknown Status Code` with a frowny face:

[[[ code('f3181afebf') ]]]

If the `$type` *is* set - we're in the normal case. Move the check up there and set
add `$title = self::$titles[$type]`. After everything, assign `$this->title = $title`:

[[[ code('c8a89a4f16') ]]]

Now the code we wrote in `ApiExceptionSubscriber` should work: a missing `$type`
tells `ApiProblem` to use this `about:blank` stuff. Time to try this: copy the
test method name:

```bash
./bin/phpunit -c app --filter test404Exception
```

Aaaand that's green. It's so nice when things work. 

What we just did is *huge*. If a 404 exception is thrown *anywhere* in the system,
it'll map to the nice Api Problem format we want. In fact, if *any* exception is
thrown it ends up with that format. So if your database blows up for some reason,
an exception is thrown. Sure, that'll map to a 500 status code, but the JSON format
will be just like every other error.
