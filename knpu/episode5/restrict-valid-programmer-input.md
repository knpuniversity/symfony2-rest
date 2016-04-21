# Security: Restrict the programmer Input

In the test, the `Programmer` is *owned* by `weaverryan` and then we authenticate
as `weaverryan`. So, we're starting a battle using a Programmer that *we* own. Time
to mess that up. Create a new user called `someone_else`. There still *is* a user
called `weaverryan`. But now, change the programmer to be owned by this `someone_else`
character.

This means that `weaverryan` will be starting a battle with someone else's programmer.
This should cause a validation error: this is an *invalid* `programmerId` to pass.

## Form Field Sanity Validation

But how do we do that? Is there some annotation we can use for this? Nope! This
validation logic should live in the form. "What!?" you say - "Validation always
goes on the class!". Not true! Every field type has a little bit of built-in validation
logic. For example, the `NumberType` will fail if a mischevious - or confused - user
types in a word. And the `EntityType` will fail if someone passes an `id` that's not
found in the database. I call this sanity validation: the form fields at least make
sure that a sane value is passed to your object.

If we could restrict the valid programmer id's to *only* those owned by our user,
we'd be in business.

But first, add the test: `assertResponsePropertyEquals()` that `programmerId[0]`
should equal some dummy message.

Run the test to see the failure:

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

There's no error for `programmerId` yet.

Let's fix that. Currently, the `EntityType` will allow *any* valid programmer id.
To shrink that to a smaller list, we'll pass it a custom query to use, via the
`query_builder` option.

## Passing the User to the Form

But first, some setup! In `BattleController`, we need to make sure the user is authenticated:
add `$this->denyAccessUnlessGranted('ROLE_USER')`.

Second, because we need to filter the programmers to only those *owned* by me, we
need to pass the currently-authenticated user object into the form. Add a third arugment
to `createForm()`, which is a little-known options array. Invent a new option:
`user` set to `$this->getUser()`. This isn't a core Symfony feature: we're creating
our own option.

To do this, open `BattleType` and find `configureOptions`. Here, you eed to say that
`user` is an allowed option. One way is via `$resolver->setRequired('user')`.
This means that whoever uses this form is allowed to, and in fact *must*, pass a
`user` option.

With that, you can access the user object in `buildForm()`: `$user = $options['user']`.
None of this is unqiue to API's: we're just giving our form more power!

## Passing the query_builder Option

And now we're ready to filter! Add the `query_builder` option set to an anonymous
function with the `ProgrammerRepository` as the only argument. Add a `use` for `$user`
so we can access it.

We could write the query right here, but you guys know I don't like that: keep your
queries in the repository! To do that, call a new method `createQueryBuilderForUser`
and pass it `$user`.

Copy that method name and shortcut-your way to that class by holding command and
clicking `ProgrammerRepository`. Add `public function createQueryBuilderForUser`
wit the `User $user` argument.

Inside, `return $this->createQueryBuilder()` and alias the class to `programmer`.
Then, `andWhere('programmer.user = :user')` and `->setParameter('user', $user)`.

Done! The controller passes the User to the form, and the form calls the repository
to create the custom query builder. Now, if someone passes a programmer id that we
do *not* own, the EntityType will automatically cause a validation error. Security
is built-in.

Head back to the terminal to try it!

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

Awesome! Well, it failed - but look! It's just because we don't have the real message
yet: it returned `This valid is not valid`. That's the standard message if any field
fails the "sanity" validation.

***TIP
You can customize this message via the `invalid_message` form field option.
***

Copy that string and paste it into the test. Run it!

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

So that's "sanity" validation: it's form fields watching your back to make sure
mean users don't start sending crazy things to us. And it happens automatically.
