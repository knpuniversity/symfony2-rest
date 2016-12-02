# Mad Test Debugging

When we mess up in a web app, we see Symfony's giant exception page. I want
that same experience when I'm building an API.

At the root of the project there's a `resources/` directory with an `ApiTestCase.php`
file. This has all the same stuff as *our* `ApiTestCase` plus some pretty
sweet new debugging stuff.

Copy this and paste it over our class.

First, check out `onNotSuccessfulTest()`:

[[[ code('7741babcf3') ]]]

If you have a method with this name, PHPUnit calls it whenever a test fails.
I'm using it to print out the last response so we can see what just happened.

I also added a few other nice things, like `printLastRequestUrl()`.

[[[ code('ac1a060c5a') ]]]

Next up is `debugResponse()` use it if you want to see what a Response looks like:

[[[ code('d2abc9dad1') ]]]

This crazy function is something I wrote - it knows what Symfony's error
page looks like and tries to extract the important parts... so you don't
have to stare at a giant HTML page in your terminal. I hate that. It's probably
not perfect - and if you find an improvement and want to share it, you'll
be my best friend.

And finally, whenever this class prints something, it's calling `printDebug()`.
And right now, it's about as dull as you can get:

[[[ code('eb465e1a79') ]]]

I think we can make that way cooler. But first, with this in place, it *should*
print out the last response so we can see the error:

```bash
php bin/phpunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Ah hah!

    Catchable Fatal Error: Argument 1 passed to Programmer::setUser() must
    be an instance of AppBundle\Entity\User, null given in ProgrammerController.php
    on line 29.

So the problem is that when we delete our database, we're also deleting our
hacked-in `weaverryan` user:

[[[ code('b30cc3d049') ]]]

Let's deal with that in a second - and do something cool first. So, remember
how some of the `app/console` commands have really pretty colored text when
they print? Well, we're not inside a console command in PHPUnit, but I'd
*love* to be able to print out with colors.

Good news! It turns out, this is really easy. The class that handles the
styling is called `ConsoleOutput`, and you can use it directly from anywhere.

Start by adding a `private $output` property that we'll use to avoid creating
a bunch of these objects. Then down in `printDebug()`, say
`if ($this->output === null)` then `$this->output = new ConsoleOutput();`.
This is the `$output` variable you're passed in a normal Symfony command.
This means we can say `$this->output->writeln()` and pass it the `$string`:

[[[ code('6ab4843e77') ]]]

I'm coloring some things already, so let's see this beautiful art! Re-run the test:

```bash
php bin/phpunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Hey! That error is hard to miss!

## Seeing the Exception Stacktrace!

Ok, *one* more debugging trick. What if we really need to see the full stacktrace?
The response headers are printed on top - and one of those actually holds
the profiler URL for this request. And to be even nicer, my debug code is
printing that at the bottom too.

Pop that into the browser. This is the profiler for that API request. It
has cool stuff like the database queries, but most importantly, there's
an `Exception` tab - you can see the full, beautiful exception with stacktrace.
This is huge.
