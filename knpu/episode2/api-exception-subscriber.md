# Global RESTful Exception Handling

When we throw an `ApiProblemException`, we need our app to automatically turn that
into a nicely-formatted API Problem JSON response and return it. That code will look
like what we have down here for validation, but it'll live in a global spot.

Whenever an exception is thrown in Symfony, it dispatches an event called `kernel.exception`.
If we attach a listener to that event, we can take full control of how an exception
is handled. If listeners are new to you, check out our ..... on it

In AppBundle, create an `EventListener` directory. Next, put a new class in there
called `ApiExceptionSubscriber` and make sure it's in the `AppBundle\EventListener`
namespace:

[[[ code('85e6e9211f') ]]]

There are two ways to hook into an event: via a listener or a subscriber. They're
really the same thing, but I like susbcribers better. To hook one up, make this class
implement `EventSubscriberInterface` - the one from Symfony. Now, hit cmd+n - or go
to the the Code->Generate menu - select "Implement Methods" and select `getSubscribedEvents`.
That's a lazy way to generate the *one* method from `EventSubscriberInterface` that
we need to fill in:

[[[ code('d9226344a4') ]]]

Return an array with one element. The key is the event's name - use `KernelEvents::EXCEPTION` -
that's really just the string `kernel.exception`. Assign that to a string: `onKernelException`.
That'll be the name of our method in this class that should be called when an exception
is thrown. Create that method: `public function onKernelException()`:

[[[ code('0514efdc0e') ]]]

So once we tell Symfony about this class, whenever an exception is thrown, `onKernelException()`
Symfony will call this method. And when Symfony calls that, it'll pass us an `$event`
object. But what type of object is that? Hold cmd - or control for Windows and Linux - 
and click the `EXCEPTION` constant. The documentation *above* it tells us that we'll
be passed a `GetResponseForExceptionEvent` object. Close that class and type-hint
the event argument with this. Don't forget your `use` statement:

[[[ code('94732f9e34') ]]]

## The Subscriber Logic

Listeners to `kernel.exception` have one big responsibility: to *try* to understand
what went wrong and return a Response for the error. The big exception page we see
in `dev` mode is caused by a core Symfony listener to this event. We throw an exception
and it gives us the pretty exception page.

So our job is clear: detect ApiProblemException's and create our nice ApiProblem
JSON response.

First, to get access to the exception that was just thrown, call `getException()`
on the `$event`. So far, we *only* want our listener to act if this is an `ApiProblemException`
object. Add an if statment: if `!$e instanceof ApiProblemException`, then just return
immediately:

[[[ code('c927fe0ff2') ]]]

For now, that'll mean that normal exceptions will still be handled via Symfony's
core listener.

Now that we know this is an `ApiProblemException`, let's turn it into a Response.
Go steal all the last few lines of validation response code from `ProgrammerController`.
Put this inside `onKernelException()`. You'll need to add the `use` statement for
`JsonResponse` manually:

[[[ code('ffa82f8516') ]]]

But we don't have an `$apiProblem` yet, where is that? It's inside the `ApiProblemException`
as a property, but we don't have a way to access it yet. Go back to the Generate
menu - select Getters - and choose the `apiProblem` property:

[[[ code('04cbf25121') ]]]

In the subscriber, `$e` is an ApiProblemException, so we can say `$apiProblem = $e->getApiProblem()`:

[[[ code('6130558c35') ]]]

That's *exactly* the Response we want to send back. To tell Symfony about this, call
`$event->setResponse()` and pass it the `$response`:

[[[ code('89686d7748') ]]]

## Registering the Event Subscriber

There's just one more step left: telling Symfony about this. Go to `app/config/services.yml`.
Give the service a name - how about `api_problem_subscriber`. Then fill in the `class`
with `ApiExceptionSubscriber` and give it an empty arguments key. The secret to telling
Symfony that this service is an event subscriber is with a tag whose name is 
`kernel.event_subscriber`:

[[[ code('e167439828') ]]]

That tag is enough to tell Symfony about our subscriber - it'll take care of the
rest.

Head back to our test where we send invalid JSON and expect the 400 status code.
This already worked, but the response was HTML, so the next assert - for a JSON response
with a `type` property - has been failing hard. Actually, I totally messed up that
assert earlier - make sure you're asserting a `type` key, not `test`:

[[[ code('eb924d2079') ]]]

So, `type` should be set to `invalid_body_format` because the `ApiProblem` has that
type set via the constant:

[[[ code('da489f50f8') ]]]

But with the exception subscriber in place, we *should* now get a JSON response.
Moment of truth:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

Green! I've got some debug code that's still printing out the response, and I'm
glad, because I feel so awesome about that response and how it's being created. Take
out the `debugResponse()` call.

Celebrate by throwing an `ApiProblemException` for validations errors too. Replace
all the Response-creation logic in `createValidationErrorResponse()` with a simple
`throw new ApiProblemException()` and pass it the `$apiProblem`:

[[[ code('5563d58598') ]]]

That's all the code we need now, no matter where we are. And now, the method name -
`createValidationErrorResponse()` isn't really accurate. Change it to
`throwApiProblemValidationException()`:

[[[ code('cd7b84ce6d') ]]]

Search for the 2 spots that use that and update the name. And we don't need to have
a `return` statement anymore: just call the function and it'll throw the exception
for us:

[[[ code('b380efc706') ]]]

Re-test *everything*:

```bash
./bin/phpunit -c app
```

Now we're green and we can send back awesome error responses from anywhere in our
code. But what about other exceptions, like 404 exceptions?
