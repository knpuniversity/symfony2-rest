# Sending back Validation Errors

Time to add validation errors and get this test passing. First, add the validation.
Open the `Programmer` class. There's no validation stuff here yet, so we need the
`use` statement for it. I'll `use` the `NotBlank` class directly, let PhpStorm auto-complete
that for me, then remove the last part and add `as Assert`:

[[[ code('cfda552043') ]]]

That's a little shortcut to get that `use` statement you always need for validation.

Now, above `username`, add `@Assert\NotBlank` with a `message` option. Go back and
copy the clever message and paste it here:

[[[ code('c0149566a5') ]]]

## Handling Validation in the Controller

Ok! That was step 1. Step 2 is to go into the controller and send the validation
errors back to the user. We're using forms, so handling validation is going to look
pretty much identical to how it looks in traditional web forms.

Check out that `processForm()` function we created in episode 1:

[[[ code('cd77c21b43') ]]]

All this does is `json_decode` the request body and call `$form->submit()`. That
does the same thing as `$form->handleRequest()`, which you're probably familiar with.
So after this function is called, the form processing has happened. After it, add
an `if` statement with the normal `if (!$form->isValid())`:

[[[ code('669057db31') ]]]

***TIP
Calling just `$form->isValid()` before submitting the form is deprecated and will raise
an exception in Symfony 4.0. Use `$form->isSubmitted() && $form->isValid()` instead
to avoid the exception.
***

If we find ourselves here, it means we have validation errors. Let's see if this
is working. Use the `dump()` function and the `$form->getErrors()` function, passing
it `true` and `false` as arguments. That'll give us all the errors in a big tree.
Cast this to a string - `getErrors()` returns a `FormErrorIterator` object, which
has a nice `__toString()` method. Add a `die` at the end:

[[[ code('1c540ed6a5') ]]]

Let's run our test to see what this looks like. Copy the `testValidationErrors` method
name, then run:

```bash
php bin/phpunit -c app --filter testValidationErrors
```

Ok, there's our printed dump. Woh, that is ugly. That's the nice HTML formatting
that comes from the `dump()` function. But it's unreadable here. I'll show you a
trick to clean that up.

It's dumping HTML because it detects that something is accessing it via the web
interface. But we kinda want it to print nicely for the terminal. Above the `dump()`
function, add `header('Content-Type: cli')`:

[[[ code('9ff908d690') ]]]

That's a hack - but try the test now:

```bash
bin/phpunit -c app --filter testValidationErrors
```

Ok, that's a *sweet* looking dump. We've got the validation error for the `nickname`
field and another for a missing CSRF token - we'll fix that soon. But, validation
*is* working.

## Collecting the Validation Errors

So now we just need to collect those errors and put them into a JSON response. To
help with that, I'm going to paste a new private function into the bottom of
`ProgrammerController`:

[[[ code('098708daf2') ]]]

If you're coding with me, you'll find this in a code block on this page - copy it
from there. Actually, I adapted this from some code in FOSRestBundle.

A `Form` object is a collection of other `Form` objects - one for each field. And
sometimes, fields have sub-fields, which are yet *another* level of `Form` objects.
It's a tree. And when validation runs, it attaches the errors to the `Form` object
of the right field. That's the treky, I mean techy, explanation of this function:
it recursively loops through that tree, fetching the errors off of each field to
create an associative array of those errors.

Head back to `newAction()` and use this: `$errors = $this->getErrorsFromForm()` and
pass it the `$form` object. Now, create a `$data` array that will eventually be our
JSON response.

[[[ code('0ad36fdfdb') ]]]

Remember, we want `type`, `title` and `errors` keys. Add a `type` key: this is the
machine name of what went wrong. How about `validation_error` - I'm making that up.
For `title` - we'll have the human-readable version of what went wrong. Let's use:
"There was a validation error". And for `errors` pass it the `$errors` array.

Finish it off! Return a `new JsonResponse()` with `$data` *and* the 400 status code:

[[[ code('4a4c3874f0') ]]]

Phew! Let's give it a try:

```bash
bin/phpunit -c app --filter testValidationErrors
```

Oof! That's not passing! That's a huge error. The dumped response looks perfect.
The error started on `ProgrammerControllerTest` where we use `assertResponsePropertiesExist()`.
Whoops! And there's the problem - I had `assertResponsePropertyExists()` - what you
use to check for a single field. Make sure your's says `assertResponsePropertiesExist()`.

Try it again:

```bash
bin/phpunit -c app --filter testValidationErrors
```

It's passing! Let's pretend I made that mistake on purpose - it was nice because we
could see that the dumped response looks exactly like we wanted.
