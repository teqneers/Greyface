-- Use this script to fill the newly created user table!
-- A new admin user - with password 'admin' will be created.
-- Do not forget to change the admin password in Greyface after the installation!
--
-- Database: sqlgrey
-- ------------------------------------------------------

INSERT INTO tq_user VALUES ( 1, 'admin', 'root@localhost', sha1('admin'), 1,'','','' );
