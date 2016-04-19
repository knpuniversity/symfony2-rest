# VirtualProperty: Add Crazy JSON Fields

The test passes, but let's see what the response looks like. Add `$this->debugResponse()`
and re-run the test:

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

Check it out! It has the fields we expect, but it's *also* embedding the entire programmer
and project resources. That's what the serializer does when a property is an object.
This might be cool with you, or maybe not. For me, this looks like overkill. Instead
of having the `Programmer` and `Project` data right here, it's probably enough to
just have the programmer's *nickname* and the project's id.

But hold on: I want to mention something *really* important. Whenever you need to
make a decision about *how* your API should work, the *right* decision should always
depend on *who* you're making the API for. If you're building your API for an iPhone
app, will having these extra fields be helpful? Or, if you're API is for a JavaScript
frontend like ReactJS, then build your API to make React happy.

## Adding an ExlcusionPolicy

Let's assume that we do *not* want those embedded objects. First, hide them! In
the `Battle` entity, we need to add some serialization exclusion rules. Since we
do this via annotations, we need a `use` statement. Here's an easy way to get the
correct `use` statement without reading the docs. I know that one of the annotations
is called `ExclusionPolicy`. Add `use ExlusionPolicy` and let it autocomplete. Now,
remove the `ExclusionPolicy` ending and add `as Serializer`.

Now, above the class, add `@Serializer\ExclusionPolicy("all")`: now *no* properties will
be used in the JSON, until we expose them. Expose `id`, skip `programmer` and `project`,
and expose `didProgrammerWin`, `foughtAt` and `notes`.

Run the same test

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

Ok, awesome - the JSON has *just* these 4 fields.

## Adding Fake Properties

Let's go to the next level. Now, I *do* want to have a `programmer`, but set to
the username instead of the whole object. And I also *do* want a `project` field,
set to its id.

Update the test to look for these. Use `$this->asserter()->assertResponsePropertyEquals()`
and pass it `$response`. Look for a `project` field that's set to `$project->getId()`.

Copy that line and do the same thing for `programmer`: it should equal `Fred`. We could
also have this return the `id` - it's up to you and what's best for your client.

But, how can we bring this to life? We're in a weird spot, because these fields *do*
exist on `Battle`, but they have the wrong values. How can we do something custom?

By using something called a virtual property. First, create a new `public function`
called `getProgrammerNickname()`. It should return `$this->programmer->getNickname()`.

## VirtualProperty

Simple. But that will *not* be used by the serializer yet. To make that happen, add
`@Serializer\VirtualProperty` above the method. As soon as you do this, it will be
exposed in your API. But it will be called `programmerNickname`: the serializer generates
the field name by taking the method name and removing `get`.

## SerializedName

Since we want this to be called `programmer` add another annotation:
`@Serializer\SerializedName()` and pass it `programmer`. *Now* we have a `programmer`
field set to the return value of this method.

Do the same thing for project: `public function getProjectId()`. This will return
`$this->project->getId()`. Above this, add the `@Serializer\VirtualProperty` to activate
the new field and `@Serializer\SerializedName("project")` to control its name.

Head to the terminal and try the test:

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

We've got it! This trick is a *wonderful* way to take control of exactly how you
want your representation to look.
