# Autocross Live Results

This application enhances your existing live timing with raw time rankings, PAX rankings and ranking by pre-defined categories such as Street and Street Touring.

![Overall PAX](img/Overall-PAX.png?raw=true)

## Features

* Responsive design for optimized viewing on a variety of mobile devices.
* Live PAX and raw time rankings by Category and Overall.
* Driver details in the context of the selected grouping - For Category and Overall, they include the driver's rank, delta and delta from 1st within the group based on sort order.
* Viewing the details for a driver in an indexed class shows both raw times and indexed times.
* Show/hide columns.
* Option to show PAX run times when sorting by best PAX time.
* Option to enable/disable separate Ladies groups for Category and Overall.
* Filter by text search of category, class, number, name, car and car color.
* Designed to be minimal load on the live timing source:
  * Data is cached for a configurable amount of time - The timing source is only accessed when the cache is empty or expired.
  * Data from the last successful read of the live timing source is cached for a configurable amount of time so that it can be viewed even when there are issues accessing the source page.
  * HTTP headers returned by the source URL are used so that data only needs to be transferred if it was modified since the previous request.
  * Also supports reading results from a local file using a cached sha256 hash to determine if the contents have changed.
* Preferences are saved separately for each grouping option.
* The Category and Overall groupings remember how you last sorted the results (best raw time or best PAX time).
* Selections you make (columns, grouping, options, sort order, source) are reflected in the page URL - When you share a URL it will load for others with the same selections.
* Limit results by class, category or driver:
  * Your last filtering selection is remembered for each grouping.
  * If you switch sources and there is no data for the filtering selection, the filter will be cleared.

## Getting Started


### Prerequisites

The server-side PHP code uses the Laravel framework.  Your web server or hosting environment must meet the [Laravel 5.4 requirements](https://laravel.com/docs/5.4/installation#server-requirements).

```
PHP >= 5.6.4
OpenSSL PHP Extension
PDO PHP Extension
Mbstring PHP Extension
Tokenizer PHP Extension
XML PHP Extension
```
PHP 7 with opcache enabled is recommended for optimal performance.

You will also need console access during the installation to run php commands.

The web server or hosting environment will require network access to the HTTP address and port of the timing source unless using a local file as your live timing source.

### Installing

Clone or copy this repository via downloaded zip to your web server or hosting environment.
On shared hosting a typical configuration would be to create a new subdomain and set document root to the "public" folder of this application.

The steps below can be used when cloning into a non-empty directory:

```
$ cd /path/to/subdomain
$ git init
$ git remote add origin git@github.com:rpieterick/tbd.git
$ git fetch
$ git checkout master
```

Run the following commands to complete the installation of Laravel after cloning this repo.
The first command checks if your PHP installation meets the requirements for [Composer Dependency Manager](https://getcomposer.org/).

```
$ php composer-setup.php --check
$ php composer.phar install --no-dev
$ cp .env.example .env
```
Edit the .env file and set APP_URL to the HTTP address that will be used for the application.
Run the following command to generate a new encryption key for Laravel to use.
```
$ php artisan key:generate
```

### Initial Configuration

Copy the provided example configuration:
```
$ cp config/timing.php.example config/timing.php
```
You should now be able to open the app in a browser using the address you set for APP_URL in the .env file.

__REMINDER:__ Document root for the APP_URL address needs to be the "public" folder under the directory where you cloned or copied this repository.

The source "Sample 1" is accessed as a local file.  The other sources are accessed via HTTP using the address set for APP_URL.

NOTE: The example configuration contains PAX indexes for the current season.  The sample sources use indexes from the 2017 season.
Therefore calculated values such as indexed or PAX times for individual runs in an indexed class will differ from those in the Total column of the sample source.

After verifying the installation and initial configuration were successful, you can customize the settings in config/timing.php including timing sources, categories and indexed classes.

Open the sample html files in the "public/samples" directory for examples of supported live timing formats.  Formats with links to separate pages for each class are not supported.

### File and Folder Permissions

Files can be restricted to read access and directories to read + execute except for the "bootstrap/cache" and "storage" folders.
Laravel 5 requires that the user the web server/PHP process is running as have write access to those folders and their contents.

## User Guide

[USERGUIDE.md](USERGUIDE.md) contains additional screen shots and a tutorial for users.

## Built With

* [Laravel 5](https://laravel.com/) - PHP framework
* [Laravel Datatables](https://github.com/yajra/laravel-datatables) - package for DataTables server-side processing
* [jQuery](https://jquery.com/) - JavaScript library
* [Bootstrap 3](http://getbootstrap.com/docs/3.3/) - HTML, CSS, and JS framework
* [DataTables](https://datatables.net/) - Table plug-in for jQuery
* [JavaScript Cookie](https://github.com/js-cookie/js-cookie) - JavaScript API for handling cookies

## Authors

* **Raymond Pieterick** - [rpieterick](https://github.com/rpieterick)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details