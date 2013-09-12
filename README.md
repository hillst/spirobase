#Installing#
To install, make sure you install the standard symfony requirements. Once installed, you will need to
build the base mysql database.
The seed.sql provided will build a database with the sample web app included.
hillcms.sql contains just the structure of the database.

Important path:

*/app_dev.php/manage 		Manager page. Base Username and password is: admin password
*/app_dev.php/people		Page using a foreach on groups to give an example.
*/app_dev.php/home		Page that uses two different heading prefixes to organize pagethings.

#Configuring a fresh website#
First you will need to register a new bundle. When that is done, you must remove the registered CCBL bundle.
You must remove CCBL from the app/config/routing.yml file as well.

#Purpose#
The goal of this app is to create some middle ground for a CMS and a framework. This is primarly designed for developers, with the ability to allow user end text and selective feature editing. All styling should be done through straight CSS and HTML using the underlying symfony framework.

#Content Management#
The way the Content Management System works is simple. Pages contain things. Things can be images, text blocks, names, whatever needs to be edited. Additionally, things can be grouped together by being a part of a "group."
Groups allow developers and users to load certain blocks based on their group. On the example page, there is the Bio groups. They contain Bio-Text, Bio-Image, Bio-Title, Bio-Name. All of these things are grouped into one section, for each time they appear.

In some cases you will need to load subsets of things. On the homepage, there are slide shows and the main block. To group these subsets, the pagethings are further broken down based on the prefixto their name. The home page uses Main-* and Slide-*. All thingnames are required to have this kind of prefix. On the People page, all of the things have the name Bio-*. 

To go along with this kind of naming convention, there is a class in ManageBundle called CMSController, which extends the base symfony class controller. By extending it instead of Controller, you inherit a method called buildPageGroups. This method automatically groups pagethings based on their name prefix and their group number.

By using these types, a developer can build a page using Things and Pages. If they are recorded in the database as a thing and page, then they will automatically be editable to the users. The way you edit a page involves simply clicking on a text box, then typing in the change, and hitting enter or save.

The server will automatically update the database and push it live. In future versions there may be a waiting feature where they are pushed to a non-published state (similar to drupal and wordpress).

If you want to upload files to the server, this should be done manually. All files belong in web/includes. They can be accessed from twig using the asset command.

#Important Notes#
parameters.yml is not included or configured. This file is in the app/config directory and is required for connection to the database.

Be sure to use base.sql to seed your database, it contains two default users, admin and user. Both user's passwords are password.
admin
password

user
password

#Adding Users and Security Notes#
The site should also be configured to use https for the manage page and login pages. In app/config/security.yml there is a setting that is commented out requiring for https. Additionally apache may need to be configured.

By adding users from the manage page on the admin account, you can easily create new admins. As of now there is no way to remove users without manually editing the database.
