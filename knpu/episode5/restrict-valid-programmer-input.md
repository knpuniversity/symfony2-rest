# EntityType Validation: Restrict Invalid programmerId

In the test, the `Programmer` is *owned* by `weaverryan` and then we authenticate
as `weaverryan`. So, we're starting a battle using a Programmer that *we* own. Time
to mess that up. Create a new user called `someone_else`. There still *is* a user
called `weaverryan`. But now, change the programmer to be owned by this sketchy
`someone_else` character.

With this setup, `weaverryan` will be starting a battle with someone else's programmer.
This should cause a validation error: this is an *invalid* `programmerId` to pass.

## Form Field Sanity Validation

But how do we do that? Is there some annotation we can use for this? Nope! This
validation logic will live in the form. "What!?" you say - "Validation always
goes in the class!". Not true! Every field type has a little bit of built-in validation
logic. For example, the `NumberType` will fail if a mischievous - or confused - user
types in a word. And the `EntityType` will fail if someone passes an `id` that's not
found in the database. I call this sanity validation: the form fields at least make
sure that a sane value is passed to your object.

If we could restrict the valid programmer id's to *only* those owned by our user,
we'd be in business.

But first, add the test: `assertResponsePropertyEquals()` that `errors.programmerId[0]`
should equal some dummy message.

Run the test to see the failure:

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

Yep: there's no error for `programmerId` yet.

Let's fix that. Right now, the client can pass *any* valid programmer id, and the
`EntityType` happily accepts it. To shrink that to a smaller list, we'll pass it
a custom query to use.

## Passing the User to the Form

To do that, the form needs to know who is authenticated. In `BattleController`,
guarantee that first: add `$this->denyAccessUnlessGranted('ROLE_USER')`.

To pass the user to the form, add a third argument to `createForm()`, which is a
little-known options array. Invent a new option: `user` set to `$this->getUser()`.
This isn't a core Symfony thing: we're creating a new option.

To allow this, open `BattleType` and find `configureOptions`. Here, you need to say
that `user` is an allowed option. One way is via `$resolver->setRequired('user')`.
This means that whoever uses this form is allowed to, and in fact *must*, pass a
`user` option.

With that, you can access the user object in `buildForm()`: `$user = $options['user']`.
None of this is unique to API's: we're just giving our form more power!

## Passing the query_builder Option

Let's filter the programmer query: add a `query_builder` option set to an anonymous
function with `ProgrammerRepository` as the only argument. Add a `use` for `$user`
so we can access it.

We could write the query right here, but you guys know I don't like that: keep your
queries in the repository! Call a new method `createQueryBuilderForUser`
and pass it `$user`.

Copy that method name and shortcut-your way to that class by holding command and
clicking `ProgrammerRepository`. Add `public function createQueryBuilderForUser`
with the `User $user` argument.

Inside, `return $this->createQueryBuilder()` and alias the class to `programmer`.
Then, just `andWhere('programmer.user = :user')` with `->setParameter('user', $user)`.

Done! The controller passes the User to the form, and the form calls the repository
to create the custom query builder. Now, if someone passes a programmer id that we
do *not* own, the EntityType will automatically cause a validation error. Security
is built-in.

Head back to the terminal to try it!

```bash
./vendor/bin/phpunit --filter testPostBattleValidationErrors
```

Awesome! Well, it failed - but look! It's just because we don't have the real message
yet: it returned `This value is not valid`. That's the standard message if any field
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
