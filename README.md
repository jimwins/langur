# Langur: a simple database migration tool in PHP

[Langur][langur] is inspired by [dbmate](https://github.com/amacneil/dbmate).

It is a standalone tool and PHP library that can be used to apply migration
scripts to a database.

## Features

* Supports any database accessible via PHP's PDO extension.
* Uses plain SQL for migrations
* It does *not* provide any mechanism for make that SQL portable across
  different database systems
* Individual migrations are run inside a transaction (by default)
* Can create and drop databases
* Can generate a file of SQL to generate the current schema
  * Makes setting up new databases a one-step process instead of running all
    migrations
  * Diff of full schema provides an additional check for code review

## Installation

*TBD*

## Usage

You can run `./vendor/bin/langur --help` to see the full list of commands
options.

## Example

The following example demonstrates:

* Creating a migration that creates a new `users` table with a few columns
  including a `name` column
* Creating the database and applying that migration
* Creating another migration to add new `first_name` and `last_name` columns to
  the `users` table, initializing the data from the `name` column and dropping
  the `name` column
* Running that migration
* Rolling back that migration
* Dumping the schema

```sh
$ export LANGUR_DSN="sqlite3:example.sqlite"
$ ./vendor/bin/langur new create_users <<SQL
-- langur:up
create table users (
  id integer,
  name varchar(255),
  email varchar(255) not null
);
insert into users (id, name, email) values
    (1, 'Jim Winstead', 'jim@example.com'),
    (2, 'Rihanna', 'madonna@example.com');

-- langur:down
SQL
$ ./vendor/bin/langur up
$ ./vendor/bin/langur new split_name_in_users <<SQL
-- langur:up
alter table users add first_name varchar(255);
alter table users add last_name varchar(255);
-- we assume a space with no name is just a first name
update users set
    first_name = case when name like '% %' then substr(name, 1, instr(name, ' ') - 1) else name end,
    last_name = case when name like '% %' then substr(name, instr(name, ' ') + 1) else '' end;
alter table users drop name;

-- langur:down
alter table users add name varchar(255) after id;
update users set name = first_name || case when last_name <> '' then ' ' || last_name else '' end;
alter table users drop first_name, last_name;
SQL
$ ./vendor/bin/langur migrate
$ sqlite3 example.sqlite3 "select * from users"
$ ./vendor/bin/langur rollback
$ sqlite3 example.sqlite3 "select * from users"
$ ./vendor/bin/langur dump
```

## About the langur

Old World monkeys of the genus *[Semnipithecus](https://en.wikipedia.org/wiki/Semnopithecus)*
are more commonly known as *langurs*, and are native to the Indian subcontinent.

[Jim Winstead](mailto:jimw@trainedmonkey.com), September 2024

[langur]: https://trainedmonkey.com/projects/langur/
