# Phabricator Wiki (Phriction) Exporter to Markdown Files

This is PHP command line tool which connects to the [Phriction](https://www.phacility.com/phabricator/phriction/) 
database and exports wiki pages to .md files.

Tested with Phabricator version d5a7d4081daa Jun 16 2020



## Remarkup to Mardown conversion

Phrictions uses a markup language called [Remarkup](https://secure.phabricator.com/book/phabricator/article/remarkup/). 
Remarkup is similar to [Markdown](https://www.markdownguide.org/cheat-sheet/)
so some of the markup remains unchanged during conversion.

Phrictions markup which will be converted to wikitext:

- headings
- italic
- monospaced
- deleted
- underlined
- highlighted
- literals
- lists
- links

Images and files used in Phriction content are not supported.



## Install

`git clone https://github.com/bmauser/phriction-to-md.git`

`cd phriction-to-md/config`

`cp config-db.php.example config-db.php`


Edit `config-db.php` and enter the connection parameters for Phriction database.



## **Examples**

Export all Phriction wiki pages to .md files:

`php phriction-to-md.php -o export-dir`

Convert Remarkup content from file to Markdown

`php phriction-to-md.php -f test/phriction-test-content.txt`



## Configuration

In addition to the `config-db.php` file which holds the database connection parameters,
the `config-markup.php` file contains markup settings.  
You can edit `config-markup.php` directly or create a `config-markup.local.php` file with values 
that will override those in `config-markup.php`.



## Disclaimer

This script was written for a one-time export job, so it hasn't been
thoroughly tested. It worked for the specific case I had.
