# Subordinate Resource Endpoint

Before we code up the endpoint, start with the test. But wait! This test is going
to be *pretty* cool: we'll make a request for a programmer resource and *follow*
that link to its battles.

## Following the Link  in a Test

In `ProgrammerControllerTest`, ad a new `public function testFollowProgrammerBattlesLink()`.
Copy the first 2 parts from `testGETProgrammer` that create the programmer and make
the request. Add those here.

Okay: before the request, we need to add some battles to the database so we have
something results to check out. Create a project first with `$this->createProject('cool_project')`.
Now, let's add 3 battles. And remember! To do that, we need the `BattleManager`
service. Set that up with `$battleManager = $this->getService()` - that's a helper
method in `ApiTestCase` - and look up `battle.battle_manager`. Let's add some inline
PHPDoc so PhpStorm auto-completes the next lines.

Love it!

Now, life is easy. Add, `$battleManager->battle()` and pass it `$programmer`. And,
whoops - make sure you have a `$programmer` variable set above. Now, add `$project`.
Copy that and paste it 2 more times.

And we *are* setup! After we make the request for the programmer, we *should* get back
a link we can follow. Get that link with `$uri = $this->asserter()->readResponseProperty`.
Read `_links.battles`. Make sure you pass `$response` as the first argument.

Now, follow that link! Be lazy and copy the `$response = ` code from above,
because we still need that `Authorization` header. But change the url to be our
dynamic `$uri`.

Before we assert anything, let's dump the response and decide later how this should
all *exactly* look.

## Coding the Subordinate Collection

Test, check! Let's hook this up. Open `ProgrammerController`. At first, it's pretty
easy. Exchange the `nickname` for a `Programmer` object. I'll use a magic param
converter for this: just type-hint the argument with `Programmer`, and it will
magically make the query for us.

Next, get battles the way you always do: `$this->getDoctrine()->getRepository('AppBundle:Battle')`.
Use `findBy` to return an array that match `programmer => $programmer`. What now?
Why not a simple return? `return $this->createApiResponse()` and pass it `$battles`.

Right? Is it really that simple?

Well, let's find out! Go back to `ProgrammerControllerTest`, copy the new method
name and run:

```bash
./vendor/bin/phpunit --filter testFollowProgrammerBattlesLink
```

## Consistency Anyone?

Ok cool - check out how this looks: it's a big JSON array that holds a bunch of
JSON battle objects. At first glance, it's great! But there's a problem? It's totally
inconsistent with our other endpoint that returns a collection of programmers.

Scroll down a little to `testProgrammersCollection`. Here: we expect an `items` key
with the resources inside of it. We're also missing the pagination fields, making
it harder for our API clients to guess how our responses will look.

Nope, we can do better.
