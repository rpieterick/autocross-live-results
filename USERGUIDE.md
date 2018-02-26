# Autocross Live Results

Autocross Live Results provides enhanced live timing features such as raw time rankings, PAX rankings and ranking by pre-defined categories.
The responsive design provides optimized viewing and navigation on a variety of mobile devices in both landscape and portrait modes.

This guide will walk you through the application's interface and its features.

## Table of Contents

* [Column Headings](USERGUIDE.md#column-headings)
* [Navigation Bar](USERGUIDE.md#navigation-bar)
  * [Columns](USERGUIDE.md#columns)
  * [Grouping](USERGUIDE.md#grouping)
  * [Options](USERGUIDE.md#options)
  * [Source](USERGUIDE.md#source)
* [Links](USERGUIDE.md#links)
  * [Driver Details](USERGUIDE.md#driver-details)
  * [Filtering by Class or Group](USERGUIDE.md#filtering-by-class-or-group)
  * [Sort Order](USERGUIDE.md#sort-order)
* [URL Format](USERGUIDE.md#url-format)
  * [Filtering](USERGUIDE.md#filtering)
  * [Selections](USERGUIDE.md#selections)

# Column Headings

Abbreviations are used in headings to keep columns as compact as possible.

The following columns are available in all grouping options:

| Heading  | Description |
| -------- | ----------- |
| P  | position in the class or indexed class |
| Cls | class identifier |
| Nbr | number |
| Name | name |
| Car | car |
| Color | color of the car |
| Run1, Run2, etc. | individual run times |
| Total / Raw | see description below |
| Diff | delta between best time and previous driver's best time |
| -1st | delta between best time and 1st place time in the class or category |

Total is used when grouping by class for the best time.  This is a raw time for regular classes and an indexed time for indexed classes.
Raw is used when grouping by Category or Overall to display the best raw time.

When grouping by Category or Overall, these additional columns are available:

| Heading  | Description |
| -------- | ----------- |
| R | rank within the category |
| Pax1, Pax2, etc. | individual PAX times |
| Index | index used to calculate PAX times |
| Pax | best PAX time |
| Pax% | PAX percentile ((best PAX time / top PAX time) * 100) |

When viewing details for a driver in an indexed class, Tot1, Tot2, etc. are the indexed times for their individual runs.

# Navigation Bar

![Navigation Bar](img/Navigation-Bar.png?raw=true)

Drop-down menus in the navigation bar allow you to customize your view of the results data.  Selections you make are stored as preferences using browser cookies.
Preferences are stored separately for each grouping option (Category, Class and Overall).

NOTE: Because they allow multiple selections, changes under Columns and Options do not refresh the table until you close their drop-down.

## Columns

The Columns drop-down allows you to control visibility of table columns for each grouping option.
You can choose to show or hide the following columns:
* Car
* Color
* Runs - show individual runs
* Diff
* -1st

When grouping by Category or Overall, these additional columns can be shown:
* Index
* Pax
* Pax%

## Grouping

The Grouping drop-down allows you to switch between three ranking formats:
* Category - Drivers grouped by pre-defined categories.  A category groups multiple classes such as a Street Touring category for all Street Touring classes.
* Class - Default view with drivers grouped by their class or indexed class.
* Overall - Single grouping of all drivers ranked by best raw or PAX time.

## Options

![Category PAX](img/Category-PAX.png?raw=true)

The Options drop-down provides additional customization for each grouping option:
* Grouping Row - Enable or disable displaying the grouping row that separates each class or category.
* Search - Show the search box.  Enables filtering by text search.

When grouping by Category or Overall, these additional options are available:
* Ladies Categories - Enable or disable separate Ladies categories.
* PAX Runs with PAX Order - Show PAX times for individual run columns when sorting by Pax.

## Source

The Source drop-down allows switching to an alternate live timing source (if configured) such as live timing for a different region.
The caching features of Autocross Live Results support multiple users accessing multiple sources at the same time.

# Links

HTML links in the table headings, grouping rows and Name column give you the ability to control ranking type (raw or PAX), filter by class/group and view details for the driver of your choice.

## Driver Details

![Driver Details](img/Driver-Details.png?raw=true)

Click on a driver's name to view details of their run times and rankings.  You can also open the link in a new tab.

Details for a driver in an indexed class will include both raw and indexed times for each of their runs.

When grouping by Category or Overall, driver details include the driver's rank, delta and delta from 1st within the group based on sort order.
They also include both raw and PAX times for each of their runs.

## Filtering by Class or Group

Clicking on the class or category name in a grouping row limits results to just that class or category.  Clicking on the title in the navigation bar clears the filter.

## Sort Order

When grouping by Category or Overall, you can click on the Raw and Pax column headings to switch between raw and PAX time rankings.
Your last sort order selection is remembered for each grouping option.

# URL Format

Autocross Live Results utilizes an intuitive URL format for sharing page links with others.
Selections you make (columns, grouping, options, sort order, source) are automatically updated in the page URL. 
When you share a URL it will load for others with the same selections.

NOTE: Opening a URL with different selections does not change your preferences.  Your preferences only change when you make selections.

## Filtering
"/class/" or "/group/" are appended to the page's HTTP address when filtering by class, category or driver.

Limit results to a single class or indexed class when grouping by Class:
```
http://timing.local/class/ss
http://timing.local/class/t
```

Limit results to a single category when grouping by Category or Overall:
```
http://timing.local/group/Street%20Touring
http://timing.local/group/Overall%20Ladies
```

Limit results to a single driver:
```
http://timing.local/class/ss/number/11
```

## Selections
Query string parameters are appended to the page's HTTP address to control selections for grouping, source, columns, options and sorting.
```
http://timing.local/?grouping=Category&source=Sample+2&cols=-1st+Car+Color+Diff+Index+Pax+PaxP+Runs&options=GrpRow+Ladies+PaxRuns+Search&sort=Pax
```

Column selections:
```
http://timing.local/?cols=-1st+Car+Color+Diff+Index+Pax+PaxP+Runs
```

Grouping selection:
```
http://timing.local/?grouping=Category
```

Option selections:
```
http://timing.local/?options=GrpRow+Ladies+PaxRuns+Search
```

Sorting selection:
```
http://timing.local/group/Overall%20Ladies?sort=Pax
```

Source selection:
```
http://timing.local/class/ss?source=Sample+2
```

A subset of the query string parameters can be used.  Example for posting a link to live PAX rankings:
```
http://timing.local/?grouping=Overall&source=Sample+2&sort=Pax
```
