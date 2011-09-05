This code is a proof of concept implementing a microblogging system
like Twitter, but that is completely decentralised. It is designed
to be immune to the ``fail whale'' problem as the failure of any one
node will not take down the whole network. Much like email and the
internet as a whole. There is no central server, instead independent
nodes intercommunicate using a simple XML based protocol.

In order to implement this one major compromise had to be made. There
is no way to implement a ``follow button''. Instead following is
achieved by copying a URL which points to the users XML stream. This
URL is analogous to an email address. 

This application is made available under the terms of the Apache Licence.


=== Installing ===

The code depends on the open_ssl and GD libraries, the code also depends
on the `url_fopen' feature of PHP. All of these need to be enabled in
the php.ini file.

To install the code first rename the file example_config.php to config.php
then fill in the database options. Next the code, including the .htaccess
file(may be hidden on *NIX), needs to be copied onto a server. It will
automatically work regardless of where it is placed within the server
tree. Finally the schema.sql file needs to be imported into the database
using PHPMyAdmin or another MySQL client.


=== Known limitations ===

--  This code WILL NOT work without mod_rewrite enabled and the .htaccess
    file placed on the server. Consequently it will not work with any HTTP
    server that does not support Apache-style .htaccess files.

--  Following users on a different server, when the local server is viewed
    using the localhost does not work. The remote server gets sent URL's
    that reference the localhost, rather than the servers IP/domain.

    To avoid this problem always view the application using the servers
    public IP address or domain.

--  A node running behind a NAT router cannot follow users on a node on
    the internet and vice/versa.


=== Test suite ===

The code has a test suite built using the PHPUnit framework, the test
code can be found in the tests/ directory. In order to run the test
suite it is necessary to have a web server running on the localhost
and a second database. The test suite should NEVER be ran using the
primary database as it truncates all of the tables.

To run the test suite the file example_config_tests.php needs to be
renamed to config_tests.php and filled in with the database settings
info.

NOTE:
The test suite does not auto adapt to the codes location within
the server web root, if the code is installed in a subdirectory the
option ``APP_ROOT'' needs to be set to reflect this.

After this is set up, the tests can be ran by cd'ing into the root
of the application and running:

    phpunit tests/


=== Thanks to ===
    JQuery (http://jquery.com/)
