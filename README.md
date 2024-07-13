<p align="center">
  <img src="https://github.com/athenarc/bip-services/blob/main/web/img/bip-minimal.png?raw=true" width="200px"/>
  <h1 align="center">BIP! Services</h1>
  <br />
</p>

BIP! Services is a suite of tools for exploring scientific literature and assessing research impact through advanced citation-based indicators applied on top of scholarly knowledge graphs. 
BIP! aggregates citation data from the OpenAIRE Graph, constructing a citation network encompassing over 190 million research works, including articles, datasets, software, and more. 
This network is analyzed to compute a variety of citation-based indicators using scalable technologies like Apache Spark. 
The aforementioned indicators capture different dimensions of scientific impact, such as popularity (current impact), influence (overall impact), and impulse (initial momentum).

## How to cite
```
@inproceedings {Vergoulis2019,
 author = {Vergoulis, Thanasis and Chatzopoulos, Serafeim and Kanellos, Ilias and Deligiannis, Panagiotis and Tryfonopoulos, Christos and Dalamagas, Theodore},
 title = {BIP! Finder: Facilitating Scientific Literature Search by Exploiting Impact-Based Ranking},
 booktitle = {Proceedings of the 28th ACM International Conference on Information and Knowledge Management},
 series = {CIKM '19},
 year = {2019},
 pages = {2937--2940},
 url = {http://doi.acm.org/10.1145/3357384.3357850},
 doi = {10.1145/3357384.3357850},
 publisher = {ACM}
} 
```

Directory structure
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources



Requirements
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.

<!---
INSTALLATION
------------

### Install from an Archive File

Extract the archive file downloaded from [yiiframework.com](http://www.yiiframework.com/download/) to
a directory named `basic` that is directly under the Web root.

Set cookie validation key in `config/web.php` file to some random secret string:

```php
'request' => [
    // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
    'cookieValidationKey' => '<secret random string goes here>',
],
```

You can then access the application through the following URL:

~~~
http://localhost/basic/web/
~~~


### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:

~~~
php composer.phar global require "fxp/composer-asset-plugin:~1.1.1"
php composer.phar create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
~~~

Now you should be able to access the application through the following URL, assuming `basic` is the directory
directly under the Web root.

~~~
http://localhost/basic/web/
~~~


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

**NOTES:**
- Yii won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.
- Refer to the README in the `tests` directory for information specific to basic application tests.

-->
