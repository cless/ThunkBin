/* Set database to use */
USE `thunkbin`;

/* Drop all tables to patch in our new additions */
DROP TABLE IF EXISTS `paste`;
DROP TABLE IF EXISTS `clearpaste`;
DROP TABLE IF EXISTS `clearfile`;
DROP TABLE IF EXISTS `cryptpaste`;
DROP TABLE IF EXISTS `language`;


CREATE TABLE `paste`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `link`      varchar(12)     NOT NULL,
    `state`     tinyint         NOT NULL,
    `created`   integer         NOT NULL,
    `expires`   integer         NOT NULL,

    UNIQUE(`link`),
    PRIMARY KEY (`id`)
);

CREATE TABLE `clearpaste`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `pid`       integer         NOT NULL,
    `title`     varchar(128)    NOT NULL,
    `author`    varchar(20)     NOT NULL,

    PRIMARY KEY (`id`)
);

CREATE TABLE `clearfile`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `pid`       integer         NOT NULL,
    `lid`       integer         NOT NULL,
    `filename`  varchar(64)     NOT NULL,
    `contents`  text            NOT NULL,

    PRIMARY KEY (`id`)
);

CREATE TABLE `cryptpaste`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `pid`       integer         NOT NULL,
    `contents`  blob            NOT NULL,

    PRIMARY KEY (`id`)
);

CREATE TABLE `language`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `name`      varchar(20)     NOT NULL,
    
    PRIMARY KEY (`id`)
);



INSERT INTO `language` (`name`) VALUES ('plaintext');
INSERT INTO `language` (`name`) VALUES ('C');
INSERT INTO `language` (`name`) VALUES ('C++');
/**/
