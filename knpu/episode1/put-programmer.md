# PUT is for Updating

Suppose now that someone using our API needs to *edit* a programmer: maybe
they want to change the avatar of a programmer. What HTTP method should we
use? And what should the endpoint return? Answering those questions is one
of the reasons we always start by writing a test - it's like the design phase
of a feature.

Create a `public function testPUTProgrammer()` method:

[[[ code('a7fd55ca52') ]]]

*Usually*, if you want to edit a resource, you'll use the PUT HTTP method.
And so far, we've seen POST for creating and PUT for updating. But it's more
complicated than that, and involves PUT being idempotent. We have a full 5
minute video on this in our original REST screencast (see
[PUT versus POST](http://knpuniversity.com/screencast/rest/put-versus-post)),
and if you don't know the difference between PUT and POST, you should geek
out on this.

Inside the test, copy the `createProgrammer()` for CowboyCoder from earlier.
Yep, this programmer definitely needs his avatar changed. Next copy the request
and assert stuff from `testGETProgrammer()` and add that. Ok, what needs
to be updated. Change the request from `get()` to `put()`. And like earlier,
we need to send a JSON string `body` in the request. Grab one of the `$data`
arrays from earlier, add it here, then `json_encode()` it for the body. This
is a combination of stuff we've already done:

[[[ code('2ea197b91b') ]]]

For a PUT request, you're supposed to send the *entire* resource in the body,
even if you only want to update one field. So we need to send `nickname`,
`avatarNumber` *and* `tagLine`. Update the `$data` array so the `nickname`
matches `CowboyCoder`, but change the `avatarNumber` to 2. We won't update
the `tagLine`, so send `foo` and add that to `createProgrammer()` to make
sure this is CowboyCoder's starting `tagLine`:

[[[ code('25591ea067') ]]]

This will create the Programmer in the database then send a PUT request where
only the `tagLine` has changed. Assering a 200 status code is perfect, and
like most endpoints, we'll return the JSON programmer. But, we're already
testing the JSON pretty well earlier. So here, just do a sanity check: assert
that the `avatarNumber` has in fact changed to 2:

[[[ code('f0fe136d49') ]]]

Ready? Try it out, with a `--filter testPUTProgrammer` to only run *this*
one:

```bash
phpunit -c app --filter testPUTProgrammer
```

Hey, a 405 error! Method not allowed. That makes perfect sense: we haven't
added this endpoint yet. Test check! Let's code!

## Adding the PUT Controller

Add a `public function updateAction()`. The start of this will look a lot
like `showAction()`, so copy its Route stuff, but change the method to `PUT`,
and change the name so it's unique. For arguments, add `$nickname` and also
`$request`, because we'll need that in a second:

[[[ code('945fee7f14') ]]]

Ok, we have two easy jobs: query for the `Programmer` then update it from
the JSON. Steal the query logic from `showAction()`:

[[[ code('2c414fe811') ]]]

The updating part is something we did in the original POST endpoint. Steal
*everything* from `newAction()`, though we don't need all of it. Yes yes,
we *will* have some code duplication for a bit. Just trust me - we'll reorganize
things over time. Get rid of the `new Programmer()` line - we're querying
for one. And take out the `setUser()` code too: that's just needed on create.
And because we're not creating a resource, we don't need the `Location` header
and the status code should be 200, not 201:

[[[ code('4d8ec4b0c5') ]]]

Done! And if you look at the function, it's really simple. Most of the duplication
is for pretty mundane code, like creating a form and saving the `Programmer`.
Creating endpoints is already really easy.

Before I congratulate is any more, let's give this a try:

```bash
phpunit -c app --filter testPUTProgrammer
```

Uh oh! 404! But check out that really clear error message from the response:

    No programmer found for username UnitTester

Well yea! Because we should be editing CowboyCoder. In `ProgrammerControllerTest`,
I made a copy-pasta error! Update the PUT URL to be `/api/programmers/CowboyCoder`,
not `UnitTester`:

[[[ code('3242209be5') ]]]

Now we're ready again:

```bash
phpunit -c app --filter testPUTProgrammer
```

We're passing!

## Centralizing Form Data Processing

Let's clean up some of the controller duplication. It's small, but each write
endpoint is processing the request body in the same way: by fetching the
content from the request, calling `json_decode()` on that, then passing it
to `$form->submit()`.

Create a new private function called `processForm()`. This will have two
argument - `$request` and the form object, which is a `FormInterface` instance,
not that that's too important:

[[[ code('cc047a0d41') ]]]

We'll move two things here: the two lines that read and decode the request
body and the `$form->submit()` line:

[[[ code('8ac1472c14') ]]]

If this looks small to you, it is! But centralizing the `json_decode()` means
we'll be able to handle invalid JSON in one spot, really easily in the next
episode.

In `updateAction()`, call `$this->processForm()` passing it the `$request`
and the `$form`. Celebrate by removing the `json_decode` lines. Do the same
thing up in `newAction`:

[[[ code('8928a1e494') ]]]

Yay! We're just a little cleaner. Try the whole test suite:

```bash
phpunit -c app
```
