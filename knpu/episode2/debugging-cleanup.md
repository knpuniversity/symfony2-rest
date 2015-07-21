# Debugging Cleanup

We're finally to the exciting conclusion, just a few more small cleanup
items that we need to take care of. 

Starting with debugging, let's look inside `ProgrammerController`. What
happens if we mess something up? Like some exception gets thrown inside
of `newAction`. To find out let's run just `testPOST`. As you can see
we get a really nice response, but it contains absolutely no details about
what went wrong inside of there, that's fine for clients but for debugging 
it's going to be a nightmare.

If we are in debug mode and the status code is 500 I would like Symfony's normal
exception controller to take over so we can figure out what the heck's going on!

In `ApiExceptionSubscriber` we'll need to figure out if we're in debug mode. The 
way to do that in Symfony is to pass a `$debug` flag through the `__construct` method,
and setting that up on the property. 

I just hit a short cut called `alt+enter`, go to initialize fields, select debug and hit
ok. That's just a litte shortcut for PhpStorm to set that flag for me. Before we use that
go into `services.yml` and pass that value in.

The way to figure out if we're in debug mode is to type `%kernel.debug%`as an argument. 

Now, if we're in debug mode and the status code is 500 we want to do nothing so let's move
the status code line up a little bit further, making sure it's after the line where we get
the exception. It's as simple as `if ($statusCode == 500 && $this->debug)` then just `return;`
and not let our normal stuff take over.

Let's rerun `testPOST` and it should fail, but I'm hoping I can get some extra details. We get
the `JSON` response still because I changed the request format but we also get the full long stack
trace. That is looking really nice, so let's just go ahead and remove the exception. 

Onto the second thing we need to clean up! Inside the spec, under title it says that title should
be an absolute URI, and if we put it in our browser it would take us to the documentation for that. 
Right now, our types are just strings. We'll fix that in a future episode when we talk properly about
documentation, but I at least want to make us kind of follow this. It will give you an idea of how
we will document these different types. 

In `$ApiExceptionSubscriber`, instead of calling `$apiProblem->toArray(),` directly in the JSON response
I'm going to put `$data` there and create a new `$data` variable that's set to that. I'm doing that 
because now we can now prefix the type key with a URL, except when it's in the case of `about:blank`. 

So let's add our if statement
