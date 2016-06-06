# Finishing the Battle

Head to the controller. We've got this form flow mastered! Step 1: create a new
`BattleModel` object: `$battleModel = new BattleModel()`:

[[[ code('a95d1165d3') ]]]

Step 2: create the form: `$form = $this->createForm()` with `BattleType::class`:

[[[ code('346b40bc01') ]]]

On my version of PhpStorm, I need to go back and re-type the `e` to trigger auto-completion
so that the `use` statement is added above.

For the second argument to `createForm`: pass it `$battleModel`.

Step 3: Use `$this->processForm()`:

[[[ code('29defd435a') ]]]

Remember, this is a method we added in `BaseController`:

[[[ code('1b9edbf419') ]]]

it decodes the `Request` body and submits it into the form, which is what we do
on *every* endpoint that processes data.

Type-hint the `Request` argument for the controller and pass this to `processForm()`.

If the form is *not* valid, we need to send back errors. Use another method from
earlier: `$this->throwApiProblemValidationException()` and pass it the `$form` object:

[[[ code('f205ea6417') ]]]

This will grab the validation errors off and create that response.

At this point, we have a `BattleModel` object that's populated with the `Programmer` and
`Project` objects sent in the request. To create the battle, we need to use the
`BattleManager`. Do that with `$this->getBattleManager()` - that's just a shortcut
to get the service - `->battle()` and pass it `$battleModel->getProgrammer()` and
`$battleModel->getProject()`:

[[[ code('9eac953089') ]]]

Put a little `$battle = ` in the beginning of all of this to get the new `Battle` object.
Perfect!

Now that the battle has been heroically fought, let's send back the gory details.
Use `return $this->createApiResponse()` and pass it `$battle` and the 201 status code:

[[[ code('8a4c0ead57') ]]]

We aren't setting a `Location` header yet, so let's at least add a `todo` for that.

We are done! Controller, model and form: these are the *only* pieces we need to create
a robust endpoint. Try the test:

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

We are in prime battling shape.

Now, let's complicate things and learn how to *really* take control of every field
in our endpoint. And, learn more about relations.
