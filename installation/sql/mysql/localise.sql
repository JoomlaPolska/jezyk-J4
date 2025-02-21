--
-- Zamania na polskie
--

--
-- Table `#__extensions`
--
UPDATE IGNORE `#__extensions` SET `params` = REPLACE(`params`, 'en-GB', 'pl-PL') WHERE `extension_id` = 11;

INSERT INTO `#__extensions` (`extension_id`, `package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `manifest_cache`, `params`, `custom_data`) VALUES
(602, 604, 'Polski (PL)', 'language', 'pl-PL', '', 0, 1, 1, '{"name":"Polski","type":"language","creationDate":"19.08.2024","author":"Polskie Centrum Joomla","copyright":"Copyright (C) 2005 - 2024 Open Source Matters & Polskie Centrum Joomla. All rights reserved.","authorEmail":"zwiastun@joomla.pl","authorUrl":"http:\/\/joomla.pl","version":"5.1.2.1","group":""}' ,'' ,''),
(603, 604, 'Polski (PL)', 'language', 'pl-PL', '', 1, 1, 1, '{"name":"Polski","type":"language","creationDate":"19.08.2024","author":"Polskie Centrum Joomla","copyright":"Copyright (C) 2005 - 2024 Open Source Matters & Polskie Centrum Joomla. All rights reserved.","authorEmail":"zwiastun@joomla.pl","authorUrl":"http:\/\/www.joomla.pl","version":"5.1.2.1","group":""}' ,'' ,''),
(604, 0, 'Polski pakiet językowy', 'package', 'pkg_pl-PL', '', 0, 1, 1, '{"name":"Polski pakiet językowy","type":"package","creationDate":"19.08.2024","author":"Polskie Centrum Joomla","copyright":"","authorEmail":"zwiastun@joomla.pl","authorUrl":"http:\/\/www.joomla.pl","version":"5.1.2.1","group":"","filename":"pkg_pl-PL"}' ,'' ,'');

--
-- Table `#__languages`
--
INSERT INTO `#__languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `description`, `metadesc`, `published`, `access`, `ordering`) VALUES
(2, 'pl-PL', 'Polski (PL)', 'Polska', 'pl', 'pl_pl', '', '', 1, 1, 2),


--
-- Table `#__update_sites_extensions`
--
INSERT INTO `#__update_sites_extensions` VALUES (3, 604);

--
-- Table `#__usergroups`
--
UPDATE IGNORE `#__usergroups` SET `title` = 'Wszyscy' WHERE `id` = 1;
UPDATE IGNORE `#__usergroups` SET `title` = 'Zarejestrowani' WHERE `id` = 2;
UPDATE IGNORE `#__usergroups` SET `title` = 'Autorzy' WHERE `id` = 3;
UPDATE IGNORE `#__usergroups` SET `title` = 'Superużytkownicy' WHERE `id` = 8;
UPDATE IGNORE `#__usergroups` SET `title` = 'Goście' WHERE `id` = 9;
UPDATE IGNORE `#__usergroups` SET `title` = 'Redaktarzy' WHERE `id` = 4;
UPDATE IGNORE `#__usergroups` SET `title` = 'Wydawcy' WHERE `id` = 5;
UPDATE IGNORE `#__usergroups` SET `title` = 'Operatorzy' WHERE `id` = 6;
UPDATE IGNORE `#__usergroups` SET `title` = 'Administratorzy' WHERE `id` = 7;
--
-- Table `#__viewlevels`
--
UPDATE IGNORE `#__viewlevels` SET `title` = 'Wszyscy' WHERE `id` = 1;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Zalogowani' WHERE `id` = 2;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Specjalny' WHERE `id` = 3;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Goście' WHERE `id` = 5;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Superużytkownicy' WHERE `id` = 6;

--
-- Table `#__modules`
--
UPDATE IGNORE `#__modules` SET `title` = 'Główne menu' WHERE `id` = 1;
UPDATE IGNORE `#__modules` SET `title` = 'Logowanie' WHERE `id` = 2;
UPDATE IGNORE `#__modules` SET `title` = 'Popularne' WHERE `id` = 3;
UPDATE IGNORE `#__modules` SET `title` = 'Nowe artykuły' WHERE `id` = 4;
UPDATE IGNORE `#__modules` SET `title` = 'Przybornik' WHERE `id` = 8;
UPDATE IGNORE `#__modules` SET `title` = 'Powiadomienia' WHERE `id` = 9;
UPDATE IGNORE `#__modules` SET `title` = 'Zalogowani' WHERE `id` = 10;
UPDATE IGNORE `#__modules` SET `title` = 'Menu zaplecza' WHERE `id` = 12;
UPDATE IGNORE `#__modules` SET `title` = 'Podmenu zaplecza' WHERE `id` = 13;
UPDATE IGNORE `#__modules` SET `title` = 'Tytuł' WHERE `id` = 15;
UPDATE IGNORE `#__modules` SET `title` = 'Formularz logowania' WHERE `id` = 16;
UPDATE IGNORE `#__modules` SET `title` = 'Ścieżka powrotu' WHERE `id` = 17;
UPDATE IGNORE `#__modules` SET `title` = 'Wielojezyczność' WHERE `id` = 79;
UPDATE IGNORE `#__modules` SET `title` = 'Wersja Joomla' WHERE `id` = 86;
UPDATE IGNORE `#__modules` SET `title` = 'Przykładowe dane' WHERE `id` = 87;
UPDATE IGNORE `#__modules` SET `title` = 'Ostatnie działania' WHERE `id` = 88;
UPDATE IGNORE `#__modules` SET `title` = 'Pulpit dostępności' WHERE `id` = 89;
