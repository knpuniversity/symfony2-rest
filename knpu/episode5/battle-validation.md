#Adding Battle Validation

It doesn't make *any* sense to create a battle without a Programmer or a Project.
But guess what - you can! Or at least, you kind of can: we don't have validation
to prevent that yet!

The validation system we created in earlier courses is air-tight: as long as we
add the constraint annotations, it just works. So normally, I might *not* write
a test for failing validation. But I will now... because we're going to add a twist.

## Testing for Validation

Add a new `public function testPOSTBattleValidationErrors()`:

[[[ code('9799ddc2ba') ]]]

Copy the first bits from the previous function that create the data and make the request:

[[[ code('bff4f1f2f2') ]]]

But, *don't* actually create a project! Instead, send `null` for the `projectId`.
Since starting a battle against *nothing* is nonsense, assert that 400 is the response
status code. This follows the pattern we did before in `ProgrammerControllerTest`.

And actually, that test shows off the validation errors response format:
there should be an `errors` key with field names for the errors below that. Each
field could technically have multiple errors, so that's an array:

[[[ code('39d629e848') ]]]

Check for the error in our code with `$this->asserter()->assertResponsePropertyExists()`:
the field should be `errors.projectId`:

[[[ code('412463a899') ]]]

Next, check for the exact message: `assertResponsePropertyEquals()` with `errors.projectId[0]` - so
the *first* and only error - set to `This value should not be blank.`:

[[[ code('cd0f11e0ae') ]]]

Why that message? That's the default message for Symfony's `NotBlank` constraint.

Before we code this up, copy the method name and run the test:

```bash
./vendor/bin/phpunit --filter testPOSTBattleValidationErrors
```

It explodes with a 500 error! This is what happens when you're lazy and forget to
add validation: the `BattleManager` panics because there is no `Project`. We do
*not* want 500 errors, they are not hipster.

## Adding Basic Validation

We know how to fix this! Go to `BattleModel`. Remember, this is the class that's
bound to the form: so the annotations should go here. First, add the `use` statement.
Type `use NotBlank`, let it auto-complete, delete the last part and add the normal
`as Assert`:

[[[ code('f4cf579ba8') ]]]

That's my shortcut to get the `use` statement.

Now, above `project`, add `@Assert\NotBlank()`. Do the same above `programmer`:
`@Assert\NotBlank()`:

[[[ code('03624d957f') ]]]

Done! Now run the test:

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

We're awesome! Or are we... there's a deeper problem! What prevents an API client
from starting a battle with a `Programmer` that they do *not* own? Right now - *nothing*,
besides karma and trusting that humankind will do the right thing. Unfortunately,
that doesn't usually pass a security audit. Let's be heros and fix this security hole!
