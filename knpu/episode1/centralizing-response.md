# Centralize that Response!

Check out the response - it's got a `Content-Type` of `text/html`. I thought
we fixed that! Well, that's no surprise - when we switched from `JsonResponse`
to `Response`, we lost that header. But more importantly, this mistake is
too easy to make: we're calling serialize() and then creating the `Response`
by hand in *every* controller. That means we'd need to set this header everywhere. 
That sucks. Let's centralize this across our entire project.

First, move `serialize()` out of `ProgrammerController` and into a class
called `BaseController`. This is something I created and all controllers
extend this. Paste this at the bottom and make it `protected`:

[[[ code('14e07ef9df') ]]]

And while we're here - make another function: `protected function createApiResponse()`.
Give it two arguments: `$data` and `$statusCode` that defaults to 200:

[[[ code('43028655bc') ]]]

Instead of creating the `Response` ourselves, we can just call this and it'll
take care of the details. Inside, first serialize the `$data` - whatever
that is. And then return a `new Response()` with that `$json`, that `$statusCode`
and - most importantly - that `Content-Type` header of `application/json`
so we don't forget to set that:

[[[ code('7c9884aa13') ]]]

I love it! Let's use this everywhere! Search for `new Response`. Call
`$response = $this->createApiResponse()` and pass the `$programmer`. Copy
that line and make sure it's status code is 201. Remove the other stuff,
but *keep* the line that sets the `Location` header:

[[[ code('8784b950f3')]]]

Ok, *much* easier. Find the rest of the `new Response` spots and update them.
It's all pretty much the same - `listAction()` has a different variable name,
but that's it. For `deleteAction()`, well, it's returning a `null` Response,
so we can leave that one alone.

[[[ code('f4bf96e2c8') ]]]

Let's re-run the tests!

```bash
phpunit -c app
```

They still fail, but the responses have the right `Content-Type` header.

Time to fix these failures, *and* see how we can control the serializer.
