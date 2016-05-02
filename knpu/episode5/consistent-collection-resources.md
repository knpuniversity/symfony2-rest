# Rock-Solid, Consistent Collection Endpoints

Go back to the test we're working on right now. First, every collection should have an
`items` key for consistency. Assert that with `$this->asserter()->assertResponsePropertyExists()`
for `items`.

## Pagination in the Past

Next, open `ProgrammerController`. The *whole* reason the other endpoint had an
`items` key was because - in `listAction` - we went through our fancy pagination
system. Click into the `pagination_factory`. The *key* part is that this method
eventually creates a `PaginatedCollection` object. *This* is what we feed to the
serializer.

The `PaginatedCollection` object is something *we* created. And hey! It has an `$items`
property! So this isn't rocket science. It also has a few other properties: `total`,
`count` and the pagination links.

So if we want *every* collection endpoint to be identical, every endpoint should
return a `PaginatedCollection`. 

## Creating a PaginatedCollection

We could do this the simple way: `$collection = new PaginatedCollection()`
and pass it `$battles` and the total items - which right now is `count($battles)`.
There's not *actually* any pagination going on.

At the bottom, pass that `$collection` to `createApiResponse`.

Done! Run that test:

```bash
./vendor/bin/phpunit --filter testFollowProgrammerBattlesLink
```

Yes! *Now* we have an `items` key, *and* `total`, `count` and `_links`... which is
empty.

## Adding Real Pagination

And really: if we're going to all of this trouble to use the `PaginatedCollection`,
shouldn't we go one extra half-step and actually add pagination? After all, it'll
make this endpoint even more consistent by having those pagination links.

Change the `$collection =` line to `$this->get('pagination_factory')->createCollection()`.
This needs a few arguments. The first is a query builder. So instead of making this
full query for battles, we need to *just* return the query builder. Rename this to
a new method called - `createQueryBuilderForProgrammer()` and pass it the `$programmer`
object.

I'll hold command and I'll click `Battle` to jump into `BattleRepository`. Add that
method: `public function createQueryBuilderForProgrammer` with a `Programmer $programmer`
argument.

Fortunately, the query is easy: `return $this->createQueryBuilder('battle')`, then
`->andWhere('battle.programmer = :programmer')` with `setParameter('programmer', $programmer)`.

Perfect! Back in `ProgrammerController`, rename the variable to `battlesQb` and pass
it to `createCollection`. The second argument is the request object. You guys know
what to do: type-hint a new argument with `Request` and pass that in.

The third argument is the name of the route the pagination links should point to.
That's *this* route: `api_programmers_battles_list`. Finally, the last argument is
any route parameters that need to be passed to the route. This route has a `nickname`,
so pass `nickname => $programmer->getNickname()`.

Done. We basically changed one line to create a *real* paginated collection. And
now, we celebrate. Run the test:

```bash
./vendor/bin/phpunit --filter testFollowProgrammerBattlesLink
```

That is *real* pagination pretty much out of the box. Yea, this only has three results
and only one page: but if this programmer keeps having battles, we're covered.

We've really perfected a lot of traditional REST endpoints. Now, let's talk about
what happens when endpoints get weird...
