# Debugging and Cleanup

We're finally to the exciting conclusion, just a few more small cleanup
items that we need to take care of. 

Starting with debugging. Let's look inside `ProgrammerController`. What
happens if we mess something up? Like some exception gets thrown inside
of `newAction`. To find out let's run just `testPOST`. As you can see
we get a really nice response, but it contains absolutely no details about
what went wrong inside of there. That's fine for clients but for debugging 
it's going to be a nightmare.

If we *are* in debug mode and the status code is 500, I would *love* for Symfony's
normal exception handling to take over so we can see that big beautiful stacktrace.

In `ApiExceptionSubscriber` we'll need to figure out if we're in debug mode. The 
way to do that is to pass a `$debug` flag through the `__construct` method and
create a property for it.

I just hit a shortcut called `alt+enter`. Go to initialize fields, select debug and hit
ok. That's just a litte shortcut for PhpStorm to set that flag for me. Before we use that,
go into `services.yml` and pass that value in.

The way to figure out if we're in debug mode is to use `%kernel.debug%` as an argument. 

And if we *are* in debug mode and the status code is 500 we don't want our exception
subscriber to do anything. So let's move the status code line up a little bit further,
making sure it's after the line where we get the exception. The logic is as simple as
`if ($statusCode == 500 && $this->debug)` then just `return`. Symfony's normal exception
handling will take over from here.

Let's rerun `testPOST` and it should fail, but I'm hoping I can get some extra details. We get
the `JSON` response still because I changed the request format but we also get the full long stack
trace. That is looking really nice, so let's just go ahead and remove the exception. 

***TIP
There is one thing missing from our listener: logging! In your application, you should
inject the `logger` service and log that an exception occurred. This is important
so that you are aware of errors on production. The "finish" download code contains
this change.

Thanks to Sylvain for pointing this out in the comments!
***

## type is a URL

Onto the second thing we need to clean up! Inside the spec, under `type` it says that `type` should
be an absolute URI, and if we put it in our browser, it should take us to the documentation for that. 
Right now, our types are just strings. We'll fix this in a future episode when we talk properly about
documentation, but I at least want to make us kind of follow this rule.

In `ApiExceptionSubscriber`, instead of calling `$apiProblem->toArray(),` directly in the JSON response,
let's put `$data` here and create a new `$data` variable that's set to that.
We want to prefix the `type` key with a URL, except in the case of `about:blank` - because
that's already a URL.

So let's add our if statement, `if ($data['type'] != 'about:blank')` then,
`$data['type'] = 'http://localhost:8000/docs/errors#'.$data['type'];` which is just
a fake URL for now. But you can get the idea of how we'll eventually put a real
URL here to a page where people can look up what those error types actually mean.
So that'll be kinda nice.

This stuff may have broken some tests, so let's rerun all of them! Ah yep, and one
of them did fail. `invalid_body_format` failed because we're looking for this exact string
and now it's at the end of a URL.

In your test, change `assertResponsePropertyEquals` to `assertResponsePropertyContains`
which saves me from hardcoding my host name in there:

[[[ code('96991ea4be') ]]]

Copy just that test to our terminal and run it:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

Perfect, back to green!

## Fixing Web Errors

Okay, last thing we need to clean up. This site does have a web interface to it and
right now, if I just invent a URL, on the web interface I get a JSON response. This
makes sense because the subscriber has completely taken over the error handling for
our site, even though, in realitym we only want this to handle errors for our API. 

There are a couple of different ways to do this, but at least in our API, everything is
under the URL /api. So fixing this is as simple as making our subscriber only do
its magic when our URL starts with this. Let's do that!

First get the `$request` by saying `$event->getRequest()`. Then let's get our if
statement in there.  `if (strpos())` and we'll look in the haystack which is
`$request->getPathInfo()`, this is the full URL. For the needle use `/api` and if
all of this `!== 0`, in other words, if the URL doesn't start exactly with `/api`,
then let's just `return`:

[[[ code('101669030b') ]]]

Head back to the browser and refresh the page. Web interface errors restored!

Let's run the entire test suite to make sure we're done:

```bash
./bin/phpunit -c app
```

Look at that, this is a setup to be proud of.

In the next episode we're going to get back to work with pagination, filtering, and
other tough but important things with API's.

Alright guys, see ya next time!
