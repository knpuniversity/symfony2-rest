# Pagerfanta Pagination

## Installing Pagerfanta

To handle pagination, we're going to install the WhiteoctoberPagerfantaBundle.
To install the bundle, run:

```bash
composer require white-october/pagerfanta-bundle
```

Pageranta is a great library for pagination, whether you're doing things on the web
or building an API. While we're waiting, enable the bundle in `AppKernel`. And that's
it for setup: no configuration needed. Now just wait for Composer, and we're ready!

## Setting up the Query Builder

Open up `ProgrammerController` and find the `listAction` that we need to work on.
Pagination is pretty easy: you basically need to tell the pagination library what
page you're on and give it a query builder. Then, you can use it to fetch the correct
results for that page.

To read the page query parameter, type-hint the `Request` argument and say 
`$page = $request->query->get('page', 1);`. The `1` is the default value in case
there is *no* query parameter.

Next, replace `$programmers` with `$qb`, standing for query builder. And instead of
calling `findAll()`, use a new method called `findAllQueryBuilder()`. That doesn't
exist yet, so let's go add it!

I'll hold `cmd` and click to go into the `ProgrammerRepository`. Add the new method:
`public function findAllQueryBuilder()`. For now, just return
`$this->createQueryBuilder();` with an alias of `programmer`. Perfect!

## Creating the Pagerfanta Objects

This is *all* we need to use Pagerfanta. In the controller, start with
`$adapter = new DoctrineORMAdapter()` - since we're using Doctrine - and pass it
the query builder. Next, create a `$pagerfanta` variable set to `new Pagerfanta()`
and bass *it* the adapter.

On the Pagerfanta object, call `setMaxPerPage()` and pass it 10. And then call
`$pagerfanta->setCurrentPage()` and pass it `$page`.

## Using Pagerfanta to Fetch Results

Ultimately, we need Pagerfanta to return the programmers that should be showing
*right now* based on whatever page is being requested. To get that, use
`$pagerfanta->getCurrentPageResults()`. But there's a problem: instead of returning
an array of `Programmer` objects, this returns a type of traversable object with
those programmes inside. This confuses the serializer. To fix that, create a new
programmers array: `$programmers = array()`.

Next, loop over that traversable object from Pagerfanta and push each `Programmer`
object into our simple array. This gives us a simple array of `Programmer` objects.

And that means we're dangerous. In `createApiResponse`, we *still* need to pass in
the `programmers` key, but we also need to add `count` and `total`, Add the `total`
key and set it to `$pagerfanta->getNbResults()`

For `count`, that's easy: that's the current number of results that are shown on
*this* page. Just use `count($programmers)`.

We're definitely not done, but this should be enough to return a valid response on
page 1 at least. Test it out. Copy the method name and use `--filter` to *just* run
that test:


```bash
./bin/phpunit -c app --filter testGETProgrammersCollectionPaginated
```

This fails. But looks closely: we *do* have programmers 0 through 9 in the response
for page 1. It fails when trying to read the `_links.next` property because we haven't
added those yet.

## The PaginatedCollection

Before we add those, there's one improvement I want to make. Since we'll use pagination
in a lot of places, we're going to need to duplicate this JSON structure. Why not
create an object with these properties, and then let the serializer turn *that* object
into JSON?

Create a new directory called Pagination. And inside of that, a new class to model
this called `PaginatedCollection`. Make sure it's in the page `AppBundle\Pagination`
namespace. Very simply: give this 3 properties: `items`, `total` and `count`.

Generate the constructor and allow `items` and `total` to be passed. We don't need
the `count` because again we can set it with `$this->count = count($items)`. That
should do it!

But something *did* just change: this object has an `items` property instead of
`programmers`. That will change the JSON response. I made this change because I
want to re-use this class for other resources. With a little serializer magic, you
*could* make this dynamic: `programmers` in this case and something else like `battles`
in other situations. But instead, I'm going to stay with `items`. This is something
you often see with APIs: if they have their collection results under a `key`, they
often use the same key - like `items` - for all responses.

But this means that I just changed our API. In the test, search for `programmers`:
all of these keys need to change to `items`, so make sure you find them all.

Using the new class is easy: `$paginatedCollection = new PaginatedCollection()`.
Pass it `$programmers` and `$pagerfanta->getNbResults()`.

To create the `ApiResponse` pass it the `$paginatedCollection` variable directly:
`$response = $this->createApiResponse($paginatedCollection)`.

Try this test! It still fails, but only once it looks for the links. The first response
looks exactly how we want it to. Okay, that's awesome - so now let's add some links.
