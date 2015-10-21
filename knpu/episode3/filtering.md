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
we'll say `if ($filter)` then we'll apply it. In our case we'll say `andWhere('programmer.nickname LIKE :filter OR programmer.tagLine LIKE filter')`
