# Coding the Subordinate Resource Endpoint

Before we code up the endpoint, start with the test. But wait! This test is going
to be *pretty* cool: we'll make a request for a programmer resource and *follow*
that link to its battles.

## Following the Link  in a Test

In `ProgrammerControllerTest`, add a new `public function testFollowProgrammerBattlesLink()`:

[[[ code('3adab868cb') ]]]

Copy the first 2 parts from `testGETProgrammer()` that create the programmer and make
the request. Add those here:

[[[ code('6023727b10') ]]]

Okay: before the request, we need to add some battles to the database so we have
something results to check out. Create a project first with `$this->createProject('cool_project')`:

[[[ code('f5c61c3bf8') ]]]

Now, let's add 3 battles. And remember! To do that, we need the `BattleManager`
service. Set that up with `$battleManager = $this->getService()` - that's a helper
method in `ApiTestCase` - and look up `battle.battle_manager`:

[[[ code('8f7466c991') ]]]

Let's add some inline PHPDoc so PhpStorm auto-completes the next lines.

Love it!

Now, life is easy. Add, `$battleManager->battle()` and pass it `$programmer`:

[[[ code('381e842daa') ]]]

And, whoops - make sure you have a `$programmer` variable set above. Now, add `$project`.
Copy that and paste it 2 more times:

[[[ code('eadb7039ff') ]]]

And we *are* setup! After we make the request for the programmer, we *should* get back
a link we can follow. Get that link with `$uri = $this->asserter()->readResponseProperty()`.
Read `_links.battles`:

[[[ code('e5bdc0f5ae') ]]]

Make sure you pass `$response` as the first argument.

Now, follow that link! Be lazy and copy the `$response = ` code from above,
because we still need that `Authorization` header. But change the url to be our
dynamic `$uri`:

[[[ code('3309e1d7aa') ]]]

Before we assert anything, let's dump the response and decide later how this should
all *exactly* look:

[[[ code('765e65d844') ]]]

## Coding the Subordinate Collection

Test, check! Let's hook this up. Open `ProgrammerController`. At first, it's pretty
easy. Exchange the `nickname` for a `Programmer` object. I'll use a magic param
converter for this: just type-hint the argument with `Programmer`, and it will
magically make the query for us:

[[[ code('6934deb204') ]]]

Next, get battles the way you always do: `$this->getDoctrine()->getRepository('AppBundle:Battle')`:

[[[ code('bc1852adac') ]]]

Use `findBy()` to return an array that match `programmer => $programmer`:

[[[ code('c46d229074') ]]]

What now? Why not a simple return? `return $this->createApiResponse()` and pass it `$battles`:

[[[ code('1ab45ce8c1') ]]]

Right? Is it really that simple?

Well, let's find out! Go back to `ProgrammerControllerTest`, copy the new method
name and run:

```bash
./vendor/bin/phpunit --filter testFollowProgrammerBattlesLink
```

## Consistency Anyone?

OK, cool - check out how this looks: it's a big JSON array that holds a bunch of
JSON battle objects. At first glance, it's great! But there's a problem? It's totally
inconsistent with our other endpoint that returns a collection of programmers.

Scroll down a little to `testProgrammersCollection()`. Here: we expect an `items` key
with the resources inside of it:

[[[ code('687709c207') ]]]

We're also missing the pagination fields, making it harder for our API clients to guess
how our responses will look.

Nope, we can do better, guys.
