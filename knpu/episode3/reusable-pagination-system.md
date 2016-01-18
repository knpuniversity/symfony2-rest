# Reusable Pagination System

Since pagination always looks the same, no matter what you're listing, I *really*
want to organize my code so that pagination is *effortless* in the future. This took
*way* too many lines of code.

Inside of the `Pagination/` directory, create a new PHP class called `PaginationFactory`.
There, add a new `public function createCollection()` method: this will create the
*entire* final `PaginatedCollection` object for some collection resource. To do this,
we'll need to pass it a few things, starting with the `$qb` and the `$request` -
we'll use that to find the *current* page. The method will also need to know the route
for the links and any `$routeParams` it needs.

Go back to `ProgrammerController`, copy the logic, remove it and put it into `PaginationFactory`.
Add the missing `use` statements: by auto-completing the classes `DoctrineORMAdapter`
and `Pagerfanta`. Now, delete `$route` and `$routeParams` since those are passed as
arguments. Remove the `$qb` variable for the same reason. In fact, move that back
to `ProgrammerController`: we'll want it in a minute. 

The only other problem here is `$this->generateUrl`: that method does *not* exist
outside of the controller. That's ok: since we *do* need to generate URLs, this just
means we need the `router`. Add a `__construct()` function at the top with
`RouterInterface` as an argument. I'll use the alt+enter [PHPStorm shortcut](http://knpuniversity.com/screencast/phpstorm/service-shortcuts#generating-constructor-properties)
to create and set that property.

Back inside `createCollection()`, change `$this->generateUrl()` to `$this->router->generate()`.
Our work in this class is done! Next, register it as as service in
`app/config/services.yml` - let's call it `pagination_factory`. How creative! Set
the class to `PaginationFactory` and pass one key for `arguments`: `@router`.

Copy the service name and open `ProgrammerController` to hook this all up. Now, just
use `$paginatedCollection = $this->get('pagination_factory')->createCollection()`
and pass it the 4 arguments: `$qb`, `$request`, the route name - `api_programmers_collection` -
and the route params. Actually, most of the time you won't have route params. So
head back into `PaginationFactory` and make that argument optional. Much better.

Now, PhpStorm *should* be happy... but it's still not! It looks more like someone
stole it's ice cream. Ah, I forgot to `return $paginatedCollection` in `PaginationFactory`.
PhpStorm was complaining that `createCollection()` didn't look like it returned
anything... and it was right! The robots are definitely taking over.

Run the test to see if we broke anything:

```bash
./bin/phpunit -c app --filter filterGETProgrammersCollectionPaginated
```

We didn't! What a delightful surprise.

Now, if you want some sweet pagination, just create a `QueryBuilder`, pass it into
the `PaginationFactory`, pass that to `createApiResponse` and then go find some ice
cream.
