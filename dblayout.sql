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

CREATE INDEX `paste_expire_index`
ON `paste` (`expires`);

CREATE TABLE `clearpaste`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `pid`       integer         NOT NULL,
    `title`     varchar(128)    NOT NULL,
    `author`    varchar(20)     NOT NULL,

    PRIMARY KEY (`id`)
);

CREATE index `clearpaste_pid_index`
ON `clearpaste` (`pid`);

CREATE TABLE `clearfile`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `pid`       integer         NOT NULL,
    `lid`       integer         NOT NULL,
    `filename`  varchar(64)     NOT NULL,
    `contents`  text            NOT NULL,

    PRIMARY KEY (`id`)
);

CREATE index `clearfile_pid_index`
ON `clearfile` (`pid`);

CREATE TABLE `cryptpaste`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `pid`       integer         NOT NULL,
    `iv`        binary(32)      NOT NULL,
    `contents`  blob            NOT NULL,

    PRIMARY KEY (`id`)
);

CREATE index `cryptpaste_pid_index`
ON `cryptpaste` (`pid`);

CREATE TABLE `language`
(
    `id`        integer         NOT NULL AUTO_INCREMENT,
    `name`      varchar(64)     NOT NULL,
    
    PRIMARY KEY (`id`)
);

INSERT INTO `language` (`id`, `name`) VALUES (1, '@Formula/@Command');
INSERT INTO `language` (`id`, `name`) VALUES (2, 'ABAP');
INSERT INTO `language` (`id`, `name`) VALUES (3, 'Actionscript');
INSERT INTO `language` (`id`, `name`) VALUES (4, 'ActionScript3');
INSERT INTO `language` (`id`, `name`) VALUES (5, 'Ada');
INSERT INTO `language` (`id`, `name`) VALUES (6, 'ALGOL 68');
INSERT INTO `language` (`id`, `name`) VALUES (7, 'Apache');
INSERT INTO `language` (`id`, `name`) VALUES (8, 'AppleScript');
INSERT INTO `language` (`id`, `name`) VALUES (9, 'Apt sources.list');
INSERT INTO `language` (`id`, `name`) VALUES (10, 'ASP');
INSERT INTO `language` (`id`, `name`) VALUES (11, 'AutoCAD/IntelliCAD Lisp');
INSERT INTO `language` (`id`, `name`) VALUES (12, 'autoconf');
INSERT INTO `language` (`id`, `name`) VALUES (13, 'Autohotkey');
INSERT INTO `language` (`id`, `name`) VALUES (14, 'AutoIT');
INSERT INTO `language` (`id`, `name`) VALUES (15, 'AviSynth');
INSERT INTO `language` (`id`, `name`) VALUES (16, 'Awk');
INSERT INTO `language` (`id`, `name`) VALUES (17, 'Axapta/Dynamics Ax X++');
INSERT INTO `language` (`id`, `name`) VALUES (18, 'BASCOM AVR');
INSERT INTO `language` (`id`, `name`) VALUES (19, 'BASH');
INSERT INTO `language` (`id`, `name`) VALUES (20, 'Basic4GL');
INSERT INTO `language` (`id`, `name`) VALUES (21, 'BibTeX');
INSERT INTO `language` (`id`, `name`) VALUES (22, 'BlitzBasic');
INSERT INTO `language` (`id`, `name`) VALUES (23, 'BNF (Backus-Naur form)');
INSERT INTO `language` (`id`, `name`) VALUES (24, 'Boo');
INSERT INTO `language` (`id`, `name`) VALUES (25, 'Brainfuck');
INSERT INTO `language` (`id`, `name`) VALUES (26, 'C');
INSERT INTO `language` (`id`, `name`) VALUES (27, 'C (for LoadRunner)');
INSERT INTO `language` (`id`, `name`) VALUES (28, 'C for Macs');
INSERT INTO `language` (`id`, `name`) VALUES (29, 'C#');
INSERT INTO `language` (`id`, `name`) VALUES (30, 'C++');
INSERT INTO `language` (`id`, `name`) VALUES (31, 'C++ (with QT extensions)');
INSERT INTO `language` (`id`, `name`) VALUES (32, 'CAD DCL (Dialog Control Language)');
INSERT INTO `language` (`id`, `name`) VALUES (33, 'CFDG');
INSERT INTO `language` (`id`, `name`) VALUES (34, 'ChaiScript');
INSERT INTO `language` (`id`, `name`) VALUES (35, 'CIL / MSIL');
INSERT INTO `language` (`id`, `name`) VALUES (36, 'Clojure');
INSERT INTO `language` (`id`, `name`) VALUES (37, 'CMake');
INSERT INTO `language` (`id`, `name`) VALUES (38, 'COBOL');
INSERT INTO `language` (`id`, `name`) VALUES (39, 'CoffeeScript');
INSERT INTO `language` (`id`, `name`) VALUES (40, 'ColdFusion');
INSERT INTO `language` (`id`, `name`) VALUES (41, 'CSS');
INSERT INTO `language` (`id`, `name`) VALUES (42, 'Cuesheet');
INSERT INTO `language` (`id`, `name`) VALUES (43, 'D');
INSERT INTO `language` (`id`, `name`) VALUES (44, 'DCS');
INSERT INTO `language` (`id`, `name`) VALUES (45, 'Delphi');
INSERT INTO `language` (`id`, `name`) VALUES (46, 'Delphi Prism (Oxygene)');
INSERT INTO `language` (`id`, `name`) VALUES (47, 'Diff-output');
INSERT INTO `language` (`id`, `name`) VALUES (48, 'DIV');
INSERT INTO `language` (`id`, `name`) VALUES (49, 'DOS');
INSERT INTO `language` (`id`, `name`) VALUES (50, 'dot');
INSERT INTO `language` (`id`, `name`) VALUES (51, 'E');
INSERT INTO `language` (`id`, `name`) VALUES (52, 'ECMAScript');
INSERT INTO `language` (`id`, `name`) VALUES (53, 'Eiffel');
INSERT INTO `language` (`id`, `name`) VALUES (54, 'Email (mbox \ eml \ RFC format)');
INSERT INTO `language` (`id`, `name`) VALUES (55, 'Enerscript');
INSERT INTO `language` (`id`, `name`) VALUES (56, 'Erlang');
INSERT INTO `language` (`id`, `name`) VALUES (57, 'Euphoria');
INSERT INTO `language` (`id`, `name`) VALUES (58, 'F#');
INSERT INTO `language` (`id`, `name`) VALUES (59, 'Falcon');
INSERT INTO `language` (`id`, `name`) VALUES (60, 'fo');
INSERT INTO `language` (`id`, `name`) VALUES (61, 'Formula One');
INSERT INTO `language` (`id`, `name`) VALUES (62, 'Fortran');
INSERT INTO `language` (`id`, `name`) VALUES (63, 'FreeBasic');
INSERT INTO `language` (`id`, `name`) VALUES (64, 'GAMBAS');
INSERT INTO `language` (`id`, `name`) VALUES (65, 'GDB');
INSERT INTO `language` (`id`, `name`) VALUES (66, 'Generic Lisp');
INSERT INTO `language` (`id`, `name`) VALUES (67, 'Genero (FOURJ\'s Genero 4GL)');
INSERT INTO `language` (`id`, `name`) VALUES (68, 'Genie');
INSERT INTO `language` (`id`, `name`) VALUES (69, 'glSlang');
INSERT INTO `language` (`id`, `name`) VALUES (70, 'GML');
INSERT INTO `language` (`id`, `name`) VALUES (71, 'GNU Gettext .po/.pot');
INSERT INTO `language` (`id`, `name`) VALUES (72, 'Gnuplot script');
INSERT INTO `language` (`id`, `name`) VALUES (73, 'Go');
INSERT INTO `language` (`id`, `name`) VALUES (74, 'Groovy');
INSERT INTO `language` (`id`, `name`) VALUES (75, 'GwBasic');
INSERT INTO `language` (`id`, `name`) VALUES (76, 'Haskell');
INSERT INTO `language` (`id`, `name`) VALUES (77, 'HicEst');
INSERT INTO `language` (`id`, `name`) VALUES (78, 'HQ9+');
INSERT INTO `language` (`id`, `name`) VALUES (79, 'HTML 4.01 strict');
INSERT INTO `language` (`id`, `name`) VALUES (80, 'HTML 4.01 strict');
INSERT INTO `language` (`id`, `name`) VALUES (81, 'Icon');
INSERT INTO `language` (`id`, `name`) VALUES (82, 'INI');
INSERT INTO `language` (`id`, `name`) VALUES (83, 'Inno Script');
INSERT INTO `language` (`id`, `name`) VALUES (84, 'INTERCAL');
INSERT INTO `language` (`id`, `name`) VALUES (85, 'Io');
INSERT INTO `language` (`id`, `name`) VALUES (86, 'J');
INSERT INTO `language` (`id`, `name`) VALUES (87, 'Java');
INSERT INTO `language` (`id`, `name`) VALUES (88, 'Java');
INSERT INTO `language` (`id`, `name`) VALUES (89, 'JavaScript');
INSERT INTO `language` (`id`, `name`) VALUES (90, 'jQuery 1.3');
INSERT INTO `language` (`id`, `name`) VALUES (91, 'KLone with C');
INSERT INTO `language` (`id`, `name`) VALUES (92, 'KLone with C++');
INSERT INTO `language` (`id`, `name`) VALUES (93, 'LaTeX');
INSERT INTO `language` (`id`, `name`) VALUES (94, 'Liberty BASIC');
INSERT INTO `language` (`id`, `name`) VALUES (95, 'Lightwave Script');
INSERT INTO `language` (`id`, `name`) VALUES (96, 'Linden Scripting Language (LSL2)');
INSERT INTO `language` (`id`, `name`) VALUES (97, 'LLVM');
INSERT INTO `language` (`id`, `name`) VALUES (98, 'Locomotive Basic');
INSERT INTO `language` (`id`, `name`) VALUES (99, 'Logtalk');
INSERT INTO `language` (`id`, `name`) VALUES (100, 'LOLcode');
INSERT INTO `language` (`id`, `name`) VALUES (101, 'LotusScript');
INSERT INTO `language` (`id`, `name`) VALUES (102, 'LUA');
INSERT INTO `language` (`id`, `name`) VALUES (103, 'MagikSF');
INSERT INTO `language` (`id`, `name`) VALUES (104, 'make');
INSERT INTO `language` (`id`, `name`) VALUES (105, 'MapBasic');
INSERT INTO `language` (`id`, `name`) VALUES (106, 'Matlab M-file');
INSERT INTO `language` (`id`, `name`) VALUES (107, 'Microchip Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (108, 'Microsoft Registry Editor');
INSERT INTO `language` (`id`, `name`) VALUES (109, 'mIRC Scripting');
INSERT INTO `language` (`id`, `name`) VALUES (110, 'MMIX Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (111, 'Modula-2');
INSERT INTO `language` (`id`, `name`) VALUES (112, 'Modula-3');
INSERT INTO `language` (`id`, `name`) VALUES (113, 'MOS 6502 (6510) ACME Cross Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (114, 'MOS 6502 (6510) Kick Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (115, 'MOS 6502 (6510) TASM/64TASS');
INSERT INTO `language` (`id`, `name`) VALUES (116, 'Motorola 68000 - HiSoft Devpac ST 2 Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (117, 'Motorola 68000 Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (118, 'MXML');
INSERT INTO `language` (`id`, `name`) VALUES (119, 'MySQL');
INSERT INTO `language` (`id`, `name`) VALUES (120, 'newLISP');
INSERT INTO `language` (`id`, `name`) VALUES (121, 'Nullsoft Scriptable Install System');
INSERT INTO `language` (`id`, `name`) VALUES (122, 'Oberon-2');
INSERT INTO `language` (`id`, `name`) VALUES (123, 'Objeck Programming Language');
INSERT INTO `language` (`id`, `name`) VALUES (124, 'Objective-C');
INSERT INTO `language` (`id`, `name`) VALUES (125, 'OCaml (Objective Caml)');
INSERT INTO `language` (`id`, `name`) VALUES (126, 'OCaml (Objective Caml)');
INSERT INTO `language` (`id`, `name`) VALUES (127, 'OpenBSD PACKET FILTER');
INSERT INTO `language` (`id`, `name`) VALUES (128, 'OpenOffice.org Basic');
INSERT INTO `language` (`id`, `name`) VALUES (129, 'Oracle 11i');
INSERT INTO `language` (`id`, `name`) VALUES (130, 'Oracle 8');
INSERT INTO `language` (`id`, `name`) VALUES (131, 'Oracle 9.2 PL/SQL');
INSERT INTO `language` (`id`, `name`) VALUES (132, 'Oz');
INSERT INTO `language` (`id`, `name`) VALUES (133, 'Pascal');
INSERT INTO `language` (`id`, `name`) VALUES (134, 'PCRE');
INSERT INTO `language` (`id`, `name`) VALUES (135, 'Per');
INSERT INTO `language` (`id`, `name`) VALUES (136, 'Perl');
INSERT INTO `language` (`id`, `name`) VALUES (137, 'Perl 6');
INSERT INTO `language` (`id`, `name`) VALUES (138, 'PHP');
INSERT INTO `language` (`id`, `name`) VALUES (139, 'PHP (brief version)');
INSERT INTO `language` (`id`, `name`) VALUES (140, 'PIC16 Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (141, 'Pike');
INSERT INTO `language` (`id`, `name`) VALUES (142, 'Pixel Bender 1.0');
INSERT INTO `language` (`id`, `name`) VALUES (143, 'PL/I');
INSERT INTO `language` (`id`, `name`) VALUES (144, 'PostgreSQL');
INSERT INTO `language` (`id`, `name`) VALUES (145, 'Povray');
INSERT INTO `language` (`id`, `name`) VALUES (146, 'PowerBuilder (PowerScript)');
INSERT INTO `language` (`id`, `name`) VALUES (147, 'PowerShell');
INSERT INTO `language` (`id`, `name`) VALUES (148, 'ProFTPd');
INSERT INTO `language` (`id`, `name`) VALUES (149, 'Progress');
INSERT INTO `language` (`id`, `name`) VALUES (150, 'Prolog');
INSERT INTO `language` (`id`, `name`) VALUES (151, 'Property');
INSERT INTO `language` (`id`, `name`) VALUES (152, 'ProvideX');
INSERT INTO `language` (`id`, `name`) VALUES (153, 'PureBasic');
INSERT INTO `language` (`id`, `name`) VALUES (154, 'Plaintext');
INSERT INTO `language` (`id`, `name`) VALUES (155, 'Python');
INSERT INTO `language` (`id`, `name`) VALUES (156, 'q/kdb+');
INSERT INTO `language` (`id`, `name`) VALUES (157, 'QBasic/QuickBASIC');
INSERT INTO `language` (`id`, `name`) VALUES (158, 'R');
INSERT INTO `language` (`id`, `name`) VALUES (159, 'Rebol');
INSERT INTO `language` (`id`, `name`) VALUES (160, 'robots.txt');
INSERT INTO `language` (`id`, `name`) VALUES (161, 'RPM Spec');
INSERT INTO `language` (`id`, `name`) VALUES (162, 'Ruby');
INSERT INTO `language` (`id`, `name`) VALUES (163, 'Ruby on Rails');
INSERT INTO `language` (`id`, `name`) VALUES (164, 'SAS');
INSERT INTO `language` (`id`, `name`) VALUES (165, 'Scala');
INSERT INTO `language` (`id`, `name`) VALUES (166, 'Scheme');
INSERT INTO `language` (`id`, `name`) VALUES (167, 'SciLab');
INSERT INTO `language` (`id`, `name`) VALUES (168, 'sdlBasic');
INSERT INTO `language` (`id`, `name`) VALUES (169, 'Smalltalk');
INSERT INTO `language` (`id`, `name`) VALUES (170, 'Smarty template');
INSERT INTO `language` (`id`, `name`) VALUES (171, 'SQL');
INSERT INTO `language` (`id`, `name`) VALUES (172, 'SystemVerilog');
INSERT INTO `language` (`id`, `name`) VALUES (173, 'T-SQL');
INSERT INTO `language` (`id`, `name`) VALUES (174, 'TCL/iTCL');
INSERT INTO `language` (`id`, `name`) VALUES (175, 'Tera Term Macro');
INSERT INTO `language` (`id`, `name`) VALUES (176, 'thinBasic');
INSERT INTO `language` (`id`, `name`) VALUES (177, 'TypoScript');
INSERT INTO `language` (`id`, `name`) VALUES (178, 'Unicon');
INSERT INTO `language` (`id`, `name`) VALUES (179, 'Unoidl');
INSERT INTO `language` (`id`, `name`) VALUES (180, 'UnrealScript');
INSERT INTO `language` (`id`, `name`) VALUES (181, 'Vala');
INSERT INTO `language` (`id`, `name`) VALUES (182, 'VB.NET');
INSERT INTO `language` (`id`, `name`) VALUES (183, 'Verilog');
INSERT INTO `language` (`id`, `name`) VALUES (184, 'VHDL');
INSERT INTO `language` (`id`, `name`) VALUES (185, 'Vim scripting');
INSERT INTO `language` (`id`, `name`) VALUES (186, 'Visual Basic');
INSERT INTO `language` (`id`, `name`) VALUES (187, 'Visual FoxPro');
INSERT INTO `language` (`id`, `name`) VALUES (188, 'Visual Prolog');
INSERT INTO `language` (`id`, `name`) VALUES (189, 'Whitespace');
INSERT INTO `language` (`id`, `name`) VALUES (190, 'Whois response (RPSL format)');
INSERT INTO `language` (`id`, `name`) VALUES (191, 'WinBatch');
INSERT INTO `language` (`id`, `name`) VALUES (192, 'x86 Assembler (NASM)');
INSERT INTO `language` (`id`, `name`) VALUES (193, 'XBasic');
INSERT INTO `language` (`id`, `name`) VALUES (194, 'XML');
INSERT INTO `language` (`id`, `name`) VALUES (195, 'xorg.conf');
INSERT INTO `language` (`id`, `name`) VALUES (196, 'YAML');
INSERT INTO `language` (`id`, `name`) VALUES (197, 'ZiLOG Z80 Assembler');
INSERT INTO `language` (`id`, `name`) VALUES (198, 'ZXBasic');
INSERT INTO `language` (`id`, `name`) VALUES (199, '4CS');
