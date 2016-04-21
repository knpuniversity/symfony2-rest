#Adding Battle Validation

It doesn't make *any* sense to create a battle without a Programmer or a Project.
But guess what - we don't have validation to prevent that yet!

The validation system we created in earlier courses is air-tight: as long as we
add the constraint annotations, it'll just work. So normally, I might *not* write
a test for validation. But I will now... because we're going to add a twist.

## Testing for Validation

Add a new `public function testPostBattleValidationErrors`. Copy the first bits from
the previous function that create the data and make the request. But, *don't* actually
create a project! Instead, send `null` for the `projectId`. Since starting a battle
with *nothing* is nonsense, assert that 400 is the response status code. We did this
all before in `ProgrammerControllerTest`.

Actually, the test in that class shows off the validation errors response format:
there should be an `errors` key with the field names for the errors below that. Each
field could technically have multiple errors, so its an array.

Check for the error in our code with `$this->asserter()->assertResponsePropertyExists()`:
the field should be `errors.projectId`. Next, check for the exact message:
`assertResponsePropertyEquals()` with `errors.projectId[0]` - so the *first* and
only error - set to `This value should not be blank.`? Why that exactly? That's the
default message for Symfony's `NotBlank` constraint.

Before we implement this, copy the method name and run the test:

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

Ah, it explodes with a 500 error! This is what happens with no validation: the
`BattleManager` panicks because there is no `Project`. We do *not* want 500 errors,
they are not hipster.

## Adding Basic Validation

We know how to fix this! Go to `BattleModel`. Remember, this is the class that's
bound to the form: so the annotations should go here. First, we need the `use` statement.
Type `use NotBlank` and let it auto-complete. Now, delete the last part and add
the normal `as Assert`. That's a shortcut to getting that `use` statement.

Now, above `project`, add `@Assert\NotBlank()`. do the same above `programmer`:
`@Assert\NotBlank()`. 

Done! Run the test now:

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

We're awesome! Or are we... there's a deeper problem! What prevents an API client
from starting a battle with a `Programmer` that they do *not* own? Right now - *nothing*,
besides karma and trusting people to not be jerks. But unfortunately, that's not
enough. Let's be heros and fix this security hole!
