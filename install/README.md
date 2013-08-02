This Symfony project is designed to have an independent backend for editing content on different pages.

Pages are still required to be built and designed by hand, however any "thing" which may be editable can be inserted into the database and loaded on page generation. By using the provided mysql schema, it is possible to use this general content management system. It simply loads all the "pages" and all of the "things", allowing for users to edit whatever value is given to these things.

Symfony expects sha1 to encode user's passwords. HTTPS is recommended.
