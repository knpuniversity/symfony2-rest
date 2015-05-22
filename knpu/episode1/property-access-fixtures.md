# Test Fixtures and the PropertyAccess Component

Hi big error! Now that I can see you, I can fix you! Remember, back in
`ProgrammerController`, we're *always* assuming there's a `weaverryan` user
in the database:

[[[ code('5f63164302') ]]]

We fix this later with some proper authentication, but for now, when we run
our tests, we need to make sure that user is in the database.

## Creating a test User

Create a new `protected function` called `crateUser()` with a required `username`
argument and one for `plainPassword`. Make that one optional: in this case,
we don't care what the user's password will be:

I'll paste in some code for this: it's pretty easy stuff. I'll trigger autocomplete
on the `User` class to get PhpStorm to add that `use` statement for me. This
creates the `User` and gives it the required data. The `getService()` function
we created lets us get out a service to encode that `foo` password:

[[[ code('80e75a4546') ]]]

Let's save this! Since we'll need the `EntityManager` a lot in this class,
let's add a `protected function getEntityManager()`. Use `getService()` with
`doctrine.orm.entity_manager`. And since I *love* autocomplete, let's give
this this PHPDoc:

[[[ code('eda6c315fa') ]]]

Now `$this->getEntityManager()->persist()` and `$this->getEntityManager()->flush()`.
And just in case whoever calls this needs the `User`, let's return it.

[[[ code('1b95aca2b3') ]]]

We could just go to the top of `testPOST` and call this there. But really,
our entire system is kind of dependent on this user. So to really fix this,
let's put it in `setup()`. Don't forget to call `parent::setup()` - we've
got some awesome code there. Then, `$this->createUser('weaverryan')`:

[[[ code('8be151db5a') ]]]

I'd say we've earned a greener test - let's try it!

```bash
phpunit -c app src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Yay!

## Testing GET one Programmer

Next, let's test the GET programmer endpoint:

[[[ code('d189eb9919') ]]]

Hmm, so we have another data problem: before we make a request to fetch a
single programmer, we need to make sure there's one in the database.

To do that, call out to an imaginary function `createProgrammer()` that we'll
write in a second. This will let us pass in an array of whatever fields we
want to set on that `Programmer`:

[[[ code('bd65c51e2b') ]]]

The `Programmer` class has a few other fields and the idea is that if we
don't pass something here, `createProgrammer()` will invent some sane default
for us.

Let's get to work in `ApiTestCase`: `protected function createProgrammer()`
with an array of `$data` as the argument. And as promised, our first job
is to use `array_merge()` to pass in some default values. One is the `powerLevel` -
it's required - and if it's not set, give it a random value from 0 to 10.
Next, create the `Programmer`:

[[[ code('663d8d282d') ]]]

Ok, maybe you're expecting me to iterate over the data, put the string `set`
before each property name, and call that method. But no! There's a better way.

## Getting down with PropertyAccess

Create an `$accessor` variable that's set to `ProperyAccess::createPropertyAccessor()`.
Hello Symfony's PropertyAccess component! *Now* iterate over data. And instead
of the "set" idea, call `$accessor->setValue()`, pass in `$programmer`,
passing `$key` - which is the property name - and pass in the `$value` we
want to set:

[[[ code('9cb42fec0c') ]]]

The `PropertyAccess` component is what works behind the scenes with Symfony's
Form component. So, it's great at calling getters and setters, but it also
has some *really* cool superpowers that we'll need soon.

The `Programmer` has all the data it needs, *except* for this `$user` relationship
property. To set that, we can just add `user` to the defaults and query for
one. I'll paste in a few lines here: I already setup our `UserRepository`
to have a `findAny()` method on it:

[[[ code('75b3e79f63') ]]]

And finally, the easy stuff! Persist and flush that `Programmer`. And return
it too for good measure:

[[[ code('434985a353') ]]]

## Finishing the GET Test

Phew! With that work done, finish the test is easy. Make a `GET` request
to `/api/programmers/UnitTester`. And as always, we want to start by asserting
the status code:

[[[ code('9e03262278') ]]]

Next, I want to assert that we get the properties we expect. If you look
in `ProgrammerController`, we're serializing 4 properties: `nickname`, `avatarNumber`,
`powerLevel` and `tagLine`. Let's assert that those actually exist.

I'll use an `assertEquals()` and put those property names as the first argument
in a second. For the second argument - the *actual* value - we can use `array_keys()`
on the json decoded response body - which I'll call `$data`. Guzzle can decode
the JSON for us if we call `$response->json()`. This gives us the decoded
JSON and `array_keys` gives us the field names in it. Back in the first argument
to `assertEquals()`, we'll fill in the fields: `nickname`, `avatarNumber`,
`powerLevel` and `tagLine` - even if it's empty:

[[[ code('909b49caef') ]]]

Ok, time to test-drive this:

```bash
phpunit -c app src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Great success! Now let's zero in and make our assertions a whole lot more
interesting.
