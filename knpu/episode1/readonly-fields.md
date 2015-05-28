# Read-Only Fields

What if we don't want the `nickname` to be changeable? After all, we're using
it almost like a primary key for the `Programmer`. Yea, I want an API client
to set it on create, but I don't want them to be able to change it afterwards.

What if we sent a new nickname in the body of the PUT request - `CowgirlCoder`.
We want the server to just ignore that. Add a new assert at the end that
`nickname` *still* equals `CowboyCoder`, even though we're trying to mess
with things:

TODO CODE

Run just that test:

```bash
phpunit -c app --filter testPUTProgrammer
```

Womp womp - we're failing: the `nickname` *is* updated on the programmer.
That makes perfect sense: our form will update *any* of the 3 fields we
configured in `PogrammerType`:

TODO CODE

## Using disabled Form Fields

So how can we make `nickname` *only* writeable when we're adding a new programmer.
If you think about the HTML world, this would be like a form that had a
functional `nickname` text box when creating, but a *disabled* `nickname`
text box when editing. We can use this idea in our API by giving the `nickname`
field a `disabled` option that's set to `true`.

TODO CODE

In an API, this will mean that any value submitted to this field will just
be ignored. If we can set this to `true` in edit mode only, that would do
the trick!

To do that, reference a new option called `is_edit`:

TODO CODE

If we're in "edit mode", then the field is disabled. To make this a valid
form option, add a new entry in `setDefaultOptions()` and default it to
`false`:

TODO CODE

Head back to `ProgrammerController::updateAction()` and give `createForm()`
a third array argument. Pass `is_edit => true`.

TODO CODE

Ok, try the test!

```bash
phpunit -c app --filter testPUTProgrammer
```

Yay! That was easy!

## Creating a Separate Form Class

And now that it's working, I need to force one small change on us that'll
help us *way* in the future when we talk about API documentation. Instead
of passing `is_edit` in the controller, we'll create a second form type class.
Copy `ProgrammerType` to `UpdateProgrammerType`. Make this extend `ProgrammerType`
and git rid of `buildForm()`. In `setDefaultOptions()`, we only need to set
`is_edit` to `true` and call the parent function above this. Make sure `getName()`
returns something unique:

TODO CODE

The whole purpose of this class is to act just like `ProgrammerType`, but
set `is_edit` to true instead of us passing that in the controller. Both
approaches are fine - but I'm planning ahead to when we use NelmioApiDocBundle:
it likes the 2 classes better. In the controller, use `new UpdateProgrammerType`
and get rid of the third argument:

TODO CODE

Test out your handy-work:

```bash
phpunit -c app --filter testPUTProgrammer
```

Success!
