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

The way to figure out if we're in debug mode is to type `%kernel.debug%`
