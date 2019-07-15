-- Make a file in the folder db-dump
-- with name like "000-db.sql" where 000 is the order of command execution and "db" is a human-readable name of containing commands

CREATE DATABASE IF NOT EXISTS zusic COLLATE=utf8_general_ci;

USE mysql;
update db set User='root' where Db='zusic';
update user set Host='%' where user='root';

FLUSH PRIVILEGES;

-- CREATE USER "zusic" IDENTIFIED BY 'QWTp2n09jFhV';
-- GRANT ALL PRIVILEGES ON *.* TO 'zusic'@'%' WITH GRANT OPTION;
-- FLUSH PRIVILEGES ;