# Form Voodoo: property_path

Remember, we want to design our API to work well with whomever is using it: whether
that's a third-party API client, a JavaScript front end, or another PHP app that's
talking to us. That's why we just changed how our Battle output looks.

But you might also want to control how the input looks: what the client needs to
send to your API. For example, right now, to create a new battle, you send a `project`
field and a `programmer` field: each set to their id.

But what if we wanted to call these fields `projectId` and `programmerId`? After all,
those are *ids* that are being sent. If we change this in the test, everything will
explode. Prove it by running things:

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

Yep, a big validation error: the form should not contain extra fields: these two
new fields are *not* in the form we built.

The easiest fix is to simply rename these fields in the form to `projectId` and
`programmerId`. But then, we would *also* need to change the property names in
`BattleModel` to match these. And that sucks: because these properties do *not*
hold id's: they hold objects. I'd rather *not* need to make my class ugly and confusing
to help out the API.

## Using property_path

Here is the very simple, elegant, amazing solution. In the form, you *do* need to
update your fields to `projectId` and `programmerId` so they match what the client
is sending. But then, add a `property_path` option to `projectId` set to `project`.
Do the same thing to the `programmerId` field: `'property_path' => 'programmer'`.

That's the key! The form now expects the client to send `projectId` and `programmerId`.
But when it sets the final data on `BattleModel`, it will call `setProject` and
`setProgrammer`.

This is a little known way to have a field name that's different than the property
name on your class. Bring on the test!

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

Awesome! Another useful option I want you to know about is called `mapped`. You can
use this to allow an *extra* field in your input, without needing to add a corresponding
property to your class.
