# TeachKloud
Web application based on the [Slim Framework](https://www.slimframework.com/) with a MySQL database.

## Installation
* The production server is running PHP 7.0.x and MySQL 5.7.21  I would encourage you to get the same environment or slightly higher.  You may run into problems with MySQL higher than 5.x.x
* You should have received access to the repository hosted on Bitbucket. Ask the team, if you did not.
* After pulling from the repository using `$ git clone <URL>`, run `$ composer install` to install all dependencies.
* Obtain a sample config.php file from the team.
* Run INSTALL.SQL on your local MySQL database to create all required schema tables.
* Setup virtual host for your local TeachKloud environment ([windows guide](https://www.cloudways.com/blog/configure-virtual-host-on-windows-10-for-wordpress/), [Mac guide](https://davidwalsh.name/create-virtual-host))
* Don't forget that the application entry folder is /public, so your virtual host configuration should reflect that.  Below is a sample of what this might look like.  Note that the sample below uses custom port :8080 for development.  If you are only running a single server on your machine you will more than likely just use port :80 for your localhost config
```
<VirtualHost *:8080>
   ServerName teachkloud.local
   DocumentRoot "/Applications/mampstack-7.1.10-1/apache2/htdocs/teachkloud/public"
   <Directory "/Applications/mampstack-7.1.10-1/apache2/htdocs/teachkloud/public">
           Options FollowSymLinks
           AllowOverride All
   </Directory>
</VirtualHost>
```
* Sign up for a free [Uploadcare account](https://uploadcare.com) to get your public / private keys for testing image upload on your localhost.  Don't forget to insert these values into the config/config.php file.

## Structure
The source code is structured in MVC. The models are actually snippets of individual transactions to and from the database. If you add a new model file, you must run `$ composer update`, so that the new file is recognized in the composer.lock file.

* src/Helpers (frequently used helper functions)
* src/Models
* src/Routes (including controllers)
* src/Views

## Containers
Slim allows you to load 3rd party libraries as containers. These are located in `config/dependencies.php`:

* logger: Monolog text-based logging
* db: PDO database library for MySQL
* view: Twig template engine
* flash: Flash messages stored in the session
* csrf: CSRF protection against vulnerabilities in POST submissions
* uploader: file uploading library by Uploadcare
* mailer: mail handling by PHPMailer

## Cronjobs
Cronjobs are run locally using the crontab. All jobs are combined in `Routes/tasks.php`. The crontab currently looks as such:

```
MAILTO=hostmaster@teachkloud.com
15 2 * * * /usr/bin/certbot renew --quiet
00 9 * * * curl --silent https://dashboard.teachkloud.com/tasks/expiry &>/dev/null
15 9 * * * curl --silent https://dashboard.teachkloud.com/tasks/postexpiry &>/dev/null
```

## How to Contribute
When working on an implementation, always create your own feature branch using Git: `git checkout -b <name-of-new-feature>`.

If you are not familiar with GitFlow 
please read the following guides: [http://nvie.com/posts/a-successful-git-branching-model/](http://nvie.com/posts/a-successful-git-branching-model/) and [https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow)

Always branch off of the develop branch.  When you are ready to have your code reviewed and deployed, pull the latest changes for develop and rebase your new feature unto the develop branch to have the most recent code.

If changes are needed to the database, keep track of all queries in UPGRADE.sql so that the Lead Developer can apply the changes to the staging and production databases. You can clear the content again after deployment. Once you have finished your branch, the changes from UPGRADE.sql need to be transferred over into INSTALL.sql

Upon approval of your work, your feature branch should be merged into the master branch using `$ git checkout master`, followed by `$ git merge <name-of-new-feature> --no-ff`. Only certain developers have write access to the master branch. The `--no-ff` flag causes the merge to always create a new commit object, even if the merge could be performed with a fast-forward. This avoids losing information about the historical existence of a feature branch and groups together all commits that together added the feature.



## Staging
If you are done with the implementation, ask the Lead Developer to pull your changes to the staging environment for testing. Production and staging are on the same server. SSH into the server and switch to the staging path, then pull from the repository:

```
$ cd /var/www/starlight
$ git pull --rebase
```

The team should then test the features at [starlight.teachkloud.com](https://starlight.teachkloud.com/). Test all features thoroughly and test potentially affected modules (if changes were made to the code or the database schemas). Release to production if approved:

```
$ cd /var/www/teachkloud
$ git pull --rebase
```