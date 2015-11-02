# Filtering

If you have a collection large enough to paginate then you'll probably also want to
be able to search or filter on that collection. Think, how do we search on the web?
The answer is: you fill in a box, hit submit and it makes a `GET` request where the
search is a query parameter like `?q=` on the URL and then we read that and it updates
the results. 

We're going to do the exact same thing!  First, we will of course update our pagination
test. Add a new programmer to the top of this test, `$this->createProgrammer` and we'll
do a search that will not return this, it will only return the other 25 with `'nickname' => 'willnotmatch'`.
And we'll keep the avatar number as 3. 

For the query parameter name use whatever you want, I'll go with `?filter=programmer`.
You can get as fancy as you want here with multiple fields or smart logic -- that's up to you!
From a REST point of view it's just one or more query parameters to filter things.

It turns out getting this to work is really easy. Get the filter `$filter = $request->query->get('filter');`
and let's pass it into our `queryBuilder` function and update it to handle that. 
Add a `$filter` argument and make it optional. Down here we'll set a new `$qb` variable and
we'll say `if ($filter)` then we'll apply it. In our case we'll say `andWhere('programmer.nickname LIKE :filter OR programmer.tagLine LIKE filter')` then `setParameter('filter' , '%'.$filter.'%')` and at the
bottom we'll `return $qb;`. If you were using something like elastic search then you wouldn't be
making this query through Doctrine, you would be doing it through elastic search itself. You would
just use a different adapter with the pagination adapter. Ultimately you'd still be building a query
that's going to be passed into the pagination. 

That's all there is to it! Let's rerun the test to see where we ended up. Ooo a 500 error, let's see
what we're doing wrong here. "Parse error, unexpected '.' on programmer repository line 38." Ah yes,
well it looks like I tripped over my keyboard there. Let's delete that extra period and rerun this again.

It's still failing, let's look at why. It's failing on the response that's on page 2, it didn not fail on
page 1 even though it should have. If we comment the filter logic out and run the test again, it fails on
page 1. This is because there is an extra result returned first so that the 5 index is actually programmer
4 instead of programmer 5. With the filter logic it's having that exact same problem on page 2. Can you guess
what's going on here? Yep! We're losing our filter as we paginate through our results. womp womp.

In our test results we can see that the URL ends in `?page=2` with no filter on it. We need to make sure that
our query parameters are maintained through our pagination. Since we have everything centralized in `PaginationFactory`
doing this couldn't be easier. Add `$routeParams = array_merge()` of `$routeParams` and all of the query parameters
which is `$request->query->all()` and that should maintain those across the request. You do have to be a little bit
careful if you have a query parameter and a curly brace wildcard in your route with the same name because you
might end up with some collisions here but that is pretty rare.

Run our tests one last time, and green! Things are working and filtering collections turns out is pretty simple.

