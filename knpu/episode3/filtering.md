# Filtering / Searching

## Designing how to Filter

Paginated a big collection is a must. But you might also want a client to be able
to search or filter that collection. Ok, so how do we search on the web? Well usually,
you fill in a box, hit submitm and that makes a `GET` request with your search term
as a query parameter like `?q=`. The server reads that and returns the results. 

I have an idea! Let's do the *exact* same thing! First, we will *of course* add a
test. Add a new programmer at the top of the pagination test with
`$this->createProgrammer()`. I want to do a search that will *not* return this new
programmer, but still *will* return the original 25. To do that, give it a totally
different nickname, like `'nickname' => 'willnotmatch'`. Keep the avatar number as
3... because we don't really care.

For the query parameter, use whatever name you want: how about `?filter=programmer`.
If you're feeling fancy, you could have multiple query parameters for different
fields, or some cool search syntax like on GitHub. That's all up to you - the API
will still work exactly the same.

## Filtering the Collection

Great news: it turns out that this is going to be pretty easy. First, get the filter
value: `$filter = $request->query->get('filter');`. Pass that to the "query builder"
function as an argument. Let's update that to handle a filter string.

In `ProgrammerRepository`, add a `$filter` argument, but make it optional. Below,
set the old return value to a new `$qb` variable. Then, `if ($filter)` has some value,
add a where clause: `andWhere('programmer.nickname LIKE :filter OR programmer.tagLine LIKE filter')`.
Then use `setParameter('filter' , '%'.$filter.'%')`. Finish things by returning `$qb`
at the bottom.

If you were using something like Elastic Search, then you wouldn't be making this
query through Doctrine: you'd be doing it through elastic search itself. But the
idea is the same: prepare some search for Elastic, then use an Elastic Search adapter
with Pagerfanta.

And that's all there is to it! Re-run the test:

```bash
./bin/phpunit -c app --filter filterGETProgrammersCollectionPaginated
```

Oooh a 500 error: let's see what we're doing wrong:

> Parse error, unexpected '.' on ProgrammerRepository line 38.

Ah yes, it looks like I tripped over my keyboard. Delete that extra period and run
this again:

```bash
./bin/phpunit -c app --filter filterGETProgrammersCollectionPaginated
```

Hmm, it's *still* failing: this time when it goes to page 2. To debug, let's see what
happens if we comment out the filter logic and try again:

```bash
./bin/phpunit -c app --filter filterGETProgrammersCollectionPaginated
```

Now it fails on page 1: that extra `willnotmatch` programmer is returned and that
makes index 5 Programmer4 instead of Programmer5. When we put the filter logic back,
it has that exact same problem on page 2. Can you guess what's going on here? Yep!
We're losing our filter query parameter when we paginate through the results.
womp womp.

## Don't Lose the Filter Parameter!

In the test, the URL ends in `?page=2` with *no* filter on it. We need to maintain
the filter query parameter *through* our pagination. Since we have everything centralized
in, `PaginationFactory` that's going to be easy. Add `$routeParams = array_merge()`
and merge `$routeParams` with all of the current query parameters, which is
`$request->query->all()`. That should take care of it.

Run the tests one last time:

```bash
./bin/phpunit -c app --filter filterGETProgrammersCollectionPaginated
```

And we're green for filtering!
