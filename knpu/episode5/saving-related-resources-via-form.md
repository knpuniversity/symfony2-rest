# Saving Related Resources in a Form

In the `Controller\Api` directory, create a new `BattleController`. Make it extend
the same `BaseController` as before: we've put a lot of shortcuts in this. Then,
add `public function newAction()`. Set the route above it with `@Route` - make sure
you hit tab to autocomplete this: it adds the necessary `use` statement. Finish the
URL: `/api/battles`. Do the same thing with `@Method` to restrict this to `POST`

Awesome! Our API processes input through a form - you can see that in `ProgrammerController`.
This form modifies the `Programmer` entity directly and we save it. Simple.

## BattleManager Complicates Things...

Well, not so simple in this case. What? It's always more interesting if we take on
the tough stuff!

To create battles on the frontend, our controller uses a service class called `BattleManager`.
It's kind of nice: it has a `battle()` function: we pass it a `Programmer` and `Project`
and it takes care of all of the logic for creating a `Battle`, figuring out who won,
and saving it to the database. We even gave `Battle` a `__construct()` function with
two required arguments.

This is a really nice setup, so I don't want to change it. But, it doesn't work well
with the form system: it prefers to instantiate the object and use setter functions.

***TIP
Actually, it *is* possible to use the form system with the `Battle` entity by taking
advantage of [data mappers](https://webmozart.io/blog/2015/09/09/value-objects-in-symfony-forms/).
***

But that's ok! In fact, I like this complication: it shows off a very nice workaround.
Just create a *new* model class for the form. In fact, I recommend this whenever
you have a form that stops looking like or working nicely with your entity class.

## Adding the BattleModel

In the `Form` directory, create a `Model` directory to keep things organized. Inside,
add a new class called `BattleModel`. Give *it* the two properties we're expecting
as API input: `$project` and `$programmer`. Now hit command+N - or go to the`Code->Generate`
menu option - and generate the getter and setter methods for both properties.

To be extra safe and make your code more hipster, type-hint `setProgrammer` with
the `Programmer` class and `setProject` with `Project`. The form system will *love*
this class.

## Designing the Form

In the `Form` directory, create a new class for the form: `BattleType`. Make this
extend the normal `AbstractType` and then hit Command+N - or Code->Generate - and
go to "Override Methods". Select the two we need: `buildForm` and `configureOptions`.

Take out the parent calls - the parent methods are empty.

## EntityType to the Rescue!

Okay, let's think about this. The API client will send `programmer` and `project`
fields and both will be ids. Ultimately, we want to turn those into the *entity*
objects corresponding to those ids before setting the data on the `BattleModel` object.

Well, this is *exactly* what the `Entity` type does. Use `$builder->add()` with
`project` set to `EntityType::class`. To tell it *what* entity to use, add a `class`
option set to `AppBundle\Entity\Project`.

Do the same for `programmer` and set its class to `AppBundle\Entity\Programmer`.

In a web form, the entity type renders as a drop-down of projects or programmers.
But it's *perfect* for an API: it transforms the project id into a Project object
by querying for it. That's *exactly* what we want.

In `configureOptions()`, add `$resolver->setDefaults()` and pass it two things: first
the `data_class` set to `BattleModel::class`. Make sure PhpStorm adds the `use` statement
for that class. Then, add `csrf_protection` to `false` because we can't use normal
CSRF protection in an API.






Alright. Form is ready. So finally, let's hop into our controller and do the
same flow that we always do. Step one, create a battle model object. Battle
model equals new battle model – and then create a form object. The form equals
this arrow, create form – and we will use battle type::class. And the same
thing. I'm going to go back and retype the E and autocomplete that so it adds
the use statement on top. That's not super smooth right now. The second
argument to create form, give it the actual battle model object.

Second, this arrow process form, we need to pass up the request object and the
form object. As a reminder, if you hold command and go into process form, this
is something that lives in our base controller. It decodes the body off of the
request and it submits those fields into the form. So it's just a nice little
shortcut. Type in the request object from HTB [07:19] foundation to get that as
an argument and then pass request and then pass it the form object.

So at this point, the form is bound. If it's not valid, then we need to send it
back in read errors, and we also have a method for doing that already. We're
going to say this arrow, throw API problem validation exception. And if you
pass it the form object – we've already done the work for this in previous
episodes – that will return a nice JSON response with the form errors.

Okay, we're killing it. Let's finish this up. We now have a battle model object
which is populated with the programmer object and the project object that's
related to it. Now we can finally use that battle manager I was showing you
before, to have it create and save the battle entity for us. So, say this arrow
get battle manager – again, a hold command – just a shortcut to get that
service out of the container, arrow, battle. And pass it battle model arrow get
programmer and battle model arrow get project – and that's it. Put a little
battle equals in the beginning of this because that is our saved battle object.

And that is what we want to return from our API response. So return this arrow
create API response – yet another shortcut method we've created in previous
episodes, and pass that battle in the 201 status code. We'll even add a little
note above this that says to do set the location header once we have an end
point that shows a single battle. And that is it. Controller model form.
Everything else is reusing logic from before. So let's try our test. Run it –
it passes. Awesome.

So now let's start playing around with this end point and the relations and see
some other cool stuff we can do.
