# Saving Related Resources in a Form

In the `Controller/Api` directory, create a new `BattleController`. Make it extend
the same `BaseController` as before: we've put a lot of shortcuts in this:

[[[ code('ff68936a3e') ]]]

Then, add `public function newAction()`. Set the route above it with `@Route` - make sure
you hit tab to autocomplete this: it adds the necessary `use` statement. Finish the
URL: `/api/battles`. Do the same thing with `@Method` to restrict this to `POST`:

[[[ code('8632fc018d') ]]]

Awesome! Our API processes input through a form - you can see that in `ProgrammerController`:

[[[ code('c973c6e136') ]]]

This form modifies the `Programmer` entity directly and we save it. Simple.

## BattleManager Complicates Things...

Well, not so simple in this case. What? I know, I like to make things as difficult
as possible.

To create battles on the frontend, our controller uses a service class called `BattleManager`.
It's kind of nice: it has a `battle()` function:

[[[ code('a9cea4cb5c') ]]]

We pass it a `Programmer` and `Project` and it takes care of all of the logic for
creating a `Battle`, figuring out who won, and saving it to the database. We even
gave `Battle` a `__construct()` function with two required arguments:

[[[ code('a20548b2c4') ]]]

This is a really nice setup, so I don't want to change it. But, it doesn't work well
with the form system: it prefers to instantiate the object and use setter functions.

***TIP
Actually, it *is* possible to use the form system with the `Battle` entity by taking
advantage of [data mappers][1].
***

But that's ok! In fact, I like this complication: it shows off a very nice workaround.
Just create a *new* model class for the form. In fact, I recommend this whenever
you have a form that stops looking like or working nicely with your entity class.

## Adding the BattleModel

In the `Form` directory, create a `Model` directory to keep things organized. Inside,
add a new class called `BattleModel`:

[[[ code('6f2fb45d1c') ]]]

Give *it* the two properties we're expecting as API input: `$project` and `$programmer`.
Hit `command`+`N` - or go to the "Code"->"Generate" menu - and generate the getter and
setter methods for both properties:

[[[ code('5ad9384c5d') ]]]

To be extra safe and make your code more hipster, type-hint `setProgrammer()` with
the `Programmer` class and `setProject()` with `Project`. The form system will *love*
this class.

## Designing the Form

In the `Form` directory, create a new class for the form: `BattleType`. Make this
extend the normal `AbstractType` and then hit `command`+`N` - or "Code"->"Generate" - and
go to "Override Methods". Select the two we need: `buildForm` and `configureOptions`:

[[[ code('ca58bf5e3a') ]]]

Take out the parent calls - the parent methods are empty.

## EntityType to the Rescue!

Okay, let's think about this. The API client will send `programmer` and `project`
fields and both will be ids. Ultimately, we want to turn those into the *entity*
objects corresponding to those ids before setting the data on the `BattleModel` object.

Well, this is *exactly* what the `Entity` type does. Use `$builder->add()` with
`project` set to `EntityType::class`. To tell it *what* entity to use, add a `class`
option set to `AppBundle\Entity\Project`:

[[[ code('22985f9844') ]]]

Do the same for `programmer` and set its class to `AppBundle\Entity\Programmer`:

[[[ code('b15c1ccc7a') ]]]

In a web form, the entity type renders as a drop-down of projects or programmers.
But it's *perfect* for an API: it transforms the project id into a Project object
by querying for it. That's *exactly* what we want.

In `configureOptions()`, add `$resolver->setDefaults()` and pass it two things: first
the `data_class` set to `BattleModel::class`:

[[[ code('56cd48beb3') ]]]

Make sure PhpStorm adds the `use` statement for that class. Then, set `csrf_protection`
to `false` because we can't use normal CSRF protection in an API:

[[[ code('ef438a2950') ]]]

Form, ready! Now let's hit the controller.


[1]: https://webmozart.io/blog/2015/09/09/value-objects-in-symfony-forms/
