# WordPress MySQL database migration
#
# Generated: Thursday 23. July 2020 19:24 UTC
# Hostname: localhost
# Database: `fullbrook-floor.vm`
# URL: //fullbrook-floor.vm
# Path: C:\\wamp64\\www\\fullbrook-floor.vm
# Tables: league_actionscheduler_actions, league_actionscheduler_claims, league_actionscheduler_groups, league_actionscheduler_logs, league_aryo_activity_log, league_commentmeta, league_comments, league_links, league_nf3_action_meta, league_nf3_actions, league_nf3_chunks, league_nf3_field_meta, league_nf3_fields, league_nf3_form_meta, league_nf3_forms, league_nf3_object_meta, league_nf3_objects, league_nf3_relationships, league_nf3_upgrades, league_options, league_postmeta, league_posts, league_redirection_404, league_redirection_groups, league_redirection_items, league_redirection_logs, league_term_relationships, league_term_taxonomy, league_termmeta, league_terms, league_usermeta, league_users, league_wpmailsmtp_tasks_meta, league_yoast_indexable, league_yoast_indexable_hierarchy, league_yoast_migrations, league_yoast_primary_term, league_yoast_seo_links, league_yoast_seo_meta
# Table Prefix: league_
# Post Types: revision, acf-field, acf-field-group, attachment, help-advice, nav_menu_item, page, post
# Protocol: http
# Multisite: false
# Subsite Export: false
# --------------------------------------------------------

/*!40101 SET NAMES utf8mb4 */;

SET sql_mode='NO_AUTO_VALUE_ON_ZERO';



#
# Delete any existing table `league_actionscheduler_actions`
#

DROP TABLE IF EXISTS `league_actionscheduler_actions`;


#
# Table structure of table `league_actionscheduler_actions`
#

CREATE TABLE `league_actionscheduler_actions` (
  `action_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hook` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `scheduled_date_local` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `args` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schedule` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_attempt_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_attempt_local` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `claim_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `extended_args` varchar(8000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`action_id`),
  KEY `hook` (`hook`),
  KEY `status` (`status`),
  KEY `scheduled_date_gmt` (`scheduled_date_gmt`),
  KEY `args` (`args`),
  KEY `group_id` (`group_id`),
  KEY `last_attempt_gmt` (`last_attempt_gmt`),
  KEY `claim_id` (`claim_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_actionscheduler_actions`
#
INSERT INTO `league_actionscheduler_actions` ( `action_id`, `hook`, `status`, `scheduled_date_gmt`, `scheduled_date_local`, `args`, `schedule`, `group_id`, `attempts`, `last_attempt_gmt`, `last_attempt_local`, `claim_id`, `extended_args`) VALUES
(7, 'action_scheduler/migration_hook', 'complete', '2020-07-02 17:40:30', '2020-07-02 18:40:30', '[]', 'O:30:"ActionScheduler_SimpleSchedule":4:{s:22:"\0*\0scheduled_timestamp";i:1593711630;s:41:"\0ActionScheduler_SimpleSchedule\0timestamp";i:1593711630;s:19:"scheduled_timestamp";i:1593711630;s:9:"timestamp";i:1593711630;}', 1, 1, '2020-07-02 17:40:47', '2020-07-02 18:40:47', 0, NULL),
(8, 'action_scheduler/migration_hook', 'complete', '2020-07-02 17:41:47', '2020-07-02 18:41:47', '[]', 'O:30:"ActionScheduler_SimpleSchedule":4:{s:22:"\0*\0scheduled_timestamp";i:1593711707;s:41:"\0ActionScheduler_SimpleSchedule\0timestamp";i:1593711707;s:19:"scheduled_timestamp";i:1593711707;s:9:"timestamp";i:1593711707;}', 1, 1, '2020-07-02 17:42:12', '2020-07-02 18:42:12', 0, NULL),
(9, 'action_scheduler/migration_hook', 'complete', '2020-07-02 18:08:20', '2020-07-02 19:08:20', '[]', 'O:30:"ActionScheduler_SimpleSchedule":4:{s:22:"\0*\0scheduled_timestamp";i:1593713300;s:41:"\0ActionScheduler_SimpleSchedule\0timestamp";i:1593713300;s:19:"scheduled_timestamp";i:1593713300;s:9:"timestamp";i:1593713300;}', 1, 1, '2020-07-02 18:08:50', '2020-07-02 19:08:50', 0, NULL),
(10, 'action_scheduler/migration_hook', 'complete', '2020-07-02 18:09:50', '2020-07-02 19:09:50', '[]', 'O:30:"ActionScheduler_SimpleSchedule":4:{s:22:"\0*\0scheduled_timestamp";i:1593713390;s:41:"\0ActionScheduler_SimpleSchedule\0timestamp";i:1593713390;s:19:"scheduled_timestamp";i:1593713390;s:9:"timestamp";i:1593713390;}', 1, 1, '2020-07-02 18:09:56', '2020-07-02 19:09:56', 0, NULL) ;

#
# End of data contents of table `league_actionscheduler_actions`
# --------------------------------------------------------



#
# Delete any existing table `league_actionscheduler_claims`
#

DROP TABLE IF EXISTS `league_actionscheduler_claims`;


#
# Table structure of table `league_actionscheduler_claims`
#

CREATE TABLE `league_actionscheduler_claims` (
  `claim_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date_created_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`claim_id`),
  KEY `date_created_gmt` (`date_created_gmt`)
) ENGINE=MyISAM AUTO_INCREMENT=1456 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_actionscheduler_claims`
#

#
# End of data contents of table `league_actionscheduler_claims`
# --------------------------------------------------------



#
# Delete any existing table `league_actionscheduler_groups`
#

DROP TABLE IF EXISTS `league_actionscheduler_groups`;


#
# Table structure of table `league_actionscheduler_groups`
#

CREATE TABLE `league_actionscheduler_groups` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `slug` (`slug`(191))
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_actionscheduler_groups`
#
INSERT INTO `league_actionscheduler_groups` ( `group_id`, `slug`) VALUES
(1, 'action-scheduler-migration') ;

#
# End of data contents of table `league_actionscheduler_groups`
# --------------------------------------------------------



#
# Delete any existing table `league_actionscheduler_logs`
#

DROP TABLE IF EXISTS `league_actionscheduler_logs`;


#
# Table structure of table `league_actionscheduler_logs`
#

CREATE TABLE `league_actionscheduler_logs` (
  `log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `action_id` bigint(20) unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `log_date_local` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`log_id`),
  KEY `action_id` (`action_id`),
  KEY `log_date_gmt` (`log_date_gmt`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_actionscheduler_logs`
#
INSERT INTO `league_actionscheduler_logs` ( `log_id`, `action_id`, `message`, `log_date_gmt`, `log_date_local`) VALUES
(1, 7, 'action created', '2020-07-02 17:39:30', '2020-07-02 18:39:30'),
(2, 7, 'action started via WP Cron', '2020-07-02 17:40:47', '2020-07-02 18:40:47'),
(3, 7, 'action complete via WP Cron', '2020-07-02 17:40:47', '2020-07-02 18:40:47'),
(4, 8, 'action created', '2020-07-02 17:40:47', '2020-07-02 18:40:47'),
(5, 8, 'action started via WP Cron', '2020-07-02 17:42:12', '2020-07-02 18:42:12'),
(6, 8, 'action complete via WP Cron', '2020-07-02 17:42:12', '2020-07-02 18:42:12'),
(7, 9, 'action created', '2020-07-02 18:07:20', '2020-07-02 19:07:20'),
(8, 9, 'action started via WP Cron', '2020-07-02 18:08:50', '2020-07-02 19:08:50'),
(9, 9, 'action complete via WP Cron', '2020-07-02 18:08:50', '2020-07-02 19:08:50'),
(10, 10, 'action created', '2020-07-02 18:08:50', '2020-07-02 19:08:50'),
(11, 10, 'action started via Async Request', '2020-07-02 18:09:56', '2020-07-02 19:09:56'),
(12, 10, 'action complete via Async Request', '2020-07-02 18:09:56', '2020-07-02 19:09:56') ;

#
# End of data contents of table `league_actionscheduler_logs`
# --------------------------------------------------------



#
# Delete any existing table `league_aryo_activity_log`
#

DROP TABLE IF EXISTS `league_aryo_activity_log`;


#
# Table structure of table `league_aryo_activity_log`
#

CREATE TABLE `league_aryo_activity_log` (
  `histid` int(11) NOT NULL AUTO_INCREMENT,
  `user_caps` varchar(70) NOT NULL DEFAULT 'guest',
  `action` varchar(255) NOT NULL,
  `object_type` varchar(255) NOT NULL,
  `object_subtype` varchar(255) NOT NULL DEFAULT '',
  `object_name` varchar(255) NOT NULL,
  `object_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `hist_ip` varchar(55) NOT NULL DEFAULT '127.0.0.1',
  `hist_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`histid`)
) ENGINE=InnoDB AUTO_INCREMENT=373 DEFAULT CHARSET=utf8;


#
# Data contents of table `league_aryo_activity_log`
#
INSERT INTO `league_aryo_activity_log` ( `histid`, `user_caps`, `action`, `object_type`, `object_subtype`, `object_name`, `object_id`, `user_id`, `hist_ip`, `hist_time`) VALUES
(1, 'administrator', 'activated', 'Plugin', '', 'Activity Log', 0, 1, '::1', 1593714868),
(2, 'administrator', 'activated', 'Plugin', '', 'Adtrak Core', 0, 1, '::1', 1593714869),
(3, 'administrator', 'activated', 'Plugin', '', 'Adtrak Location Dynamics', 0, 1, '::1', 1593714869),
(4, 'administrator', 'activated', 'Plugin', '', 'Advanced Custom Fields PRO', 0, 1, '::1', 1593714869),
(5, 'guest', 'updated', 'Post', 'page', 'Cookie Policy', 5, 0, '::1', 1593714870),
(6, 'administrator', 'activated', 'Plugin', '', 'Ninja Forms', 0, 1, '::1', 1593714871),
(7, 'administrator', 'activated', 'Plugin', '', 'Post to Google My Business', 0, 1, '::1', 1593714872),
(8, 'administrator', 'activated', 'Plugin', '', 'Public Post Preview', 0, 1, '::1', 1593714872),
(9, 'administrator', 'activated', 'Plugin', '', 'Public Post Preview Configurator', 0, 1, '::1', 1593714872),
(10, 'administrator', 'activated', 'Plugin', '', 'Redirection', 0, 1, '::1', 1593714872),
(11, 'administrator', 'activated', 'Plugin', '', 'WP Mail SMTP', 0, 1, '::1', 1593714872),
(12, 'administrator', 'activated', 'Plugin', '', 'WP Migrate DB Pro', 0, 1, '::1', 1593714872),
(13, 'administrator', 'activated', 'Plugin', '', 'WP Migrate DB Pro Media Files', 0, 1, '::1', 1593714872),
(14, 'administrator', 'activated', 'Plugin', '', 'Yoast SEO', 0, 1, '::1', 1593714872),
(15, 'administrator', 'updated', 'Plugin', '3.4.24.3', 'Ninja Forms', 0, 1, '::1', 1593715147),
(16, 'administrator', 'updated', 'Plugin', '2.2.25', 'Post to Google My Business', 0, 1, '::1', 1593715155),
(17, 'administrator', 'updated', 'Plugin', '4.8', 'Redirection', 0, 1, '::1', 1593715160),
(18, 'administrator', 'updated', 'Plugin', '2.1.1', 'WP Mail SMTP', 0, 1, '::1', 1593715167),
(19, 'administrator', 'updated', 'Plugin', '14.4.1', 'Yoast SEO', 0, 1, '::1', 1593715177),
(20, 'administrator', 'activated', 'Theme', 'fullbrook-floor', 'Fullbrook &amp; Floor', 0, 1, '::1', 1593716080),
(21, 'guest', 'updated', 'Options', '', 'sidebars_widgets', 0, 0, '::1', 1593716081),
(22, 'administrator', 'updated', 'Plugin', '5.8.12', 'Advanced Custom Fields PRO', 0, 1, '::1', 1593716097),
(23, 'administrator', 'created', 'Post', 'page', 'Home', 6, 1, '::1', 1593716294),
(24, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '::1', 1593716304),
(25, 'administrator', 'created', 'Post', 'page', 'Buy a home', 8, 1, '::1', 1593716317),
(26, 'administrator', 'created', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1593716327),
(27, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1593716331),
(28, 'administrator', 'created', 'Post', 'page', 'Free sales valuations', 12, 1, '::1', 1593716345),
(29, 'administrator', 'updated', 'Post', 'page', 'Free sales valuations', 12, 1, '::1', 1593716347),
(30, 'administrator', 'created', 'Post', 'page', 'About us', 14, 1, '::1', 1593716366),
(31, 'administrator', 'created', 'Post', 'page', 'Meet the team', 16, 1, '::1', 1593716376),
(32, 'administrator', 'updated', 'Post', 'page', 'Meet the team', 16, 1, '::1', 1593716380),
(33, 'administrator', 'updated', 'Post', 'page', 'Free sales valuations', 12, 1, '::1', 1593716397),
(34, 'administrator', 'created', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1593716409),
(35, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1593716413),
(36, 'administrator', 'created', 'Post', 'page', 'Contact Us', 20, 1, '::1', 1593716424),
(37, 'administrator', 'updated', 'Post', 'page', 'Contact Us', 20, 1, '::1', 1593716427),
(38, 'administrator', 'updated', 'Options', '', 'blogdescription', 0, 1, '::1', 1593716455),
(39, 'administrator', 'updated', 'Options', '', 'show_on_front', 0, 1, '::1', 1593716482),
(40, 'administrator', 'updated', 'Options', '', 'page_on_front', 0, 1, '::1', 1593716482),
(41, 'administrator', 'updated', 'Options', '', 'default_pingback_flag', 0, 1, '::1', 1593716514),
(42, 'administrator', 'updated', 'Options', '', 'default_comment_status', 0, 1, '::1', 1593716515),
(43, 'administrator', 'updated', 'Options', '', 'comment_moderation', 0, 1, '::1', 1593716515),
(44, 'administrator', 'updated', 'Options', '', 'close_comments_for_old_posts', 0, 1, '::1', 1593716515),
(45, 'administrator', 'updated', 'Options', '', 'page_comments', 0, 1, '::1', 1593716515),
(46, 'administrator', 'updated', 'Options', '', 'comment_registration', 0, 1, '::1', 1593716515),
(47, 'administrator', 'updated', 'Options', '', 'permalink_structure', 0, 1, '::1', 1593716525),
(48, 'administrator', 'deactivated', 'Plugin', '', 'Adtrak Location Dynamics', 0, 1, '::1', 1593716839),
(49, 'administrator', 'trashed', 'Post', 'page', 'Sample Page', 2, 1, '::1', 1593717001),
(50, 'administrator', 'created', 'Menu', '', 'Primary Menu', 0, 1, '::1', 1593717021),
(51, 'administrator', 'updated', 'Menu', '', 'Primary Menu', 0, 1, '::1', 1593717041),
(52, 'administrator', 'created', 'Taxonomy', 'resource-categories', 'Buying a home', 3, 1, '::1', 1593728551),
(53, 'administrator', 'created', 'Taxonomy', 'resource-categories', 'Selling your home', 4, 1, '::1', 1593728557),
(54, 'administrator', 'created', 'Taxonomy', 'resource-categories', 'Mortgages', 5, 1, '::1', 1593728562),
(55, 'administrator', 'created', 'Taxonomy', 'resource-categories', 'Lettings', 6, 1, '::1', 1593728566),
(56, 'administrator', 'created', 'Taxonomy', 'resource-categories', 'Landlords', 7, 1, '::1', 1593728571),
(57, 'administrator', 'created', 'Taxonomy', 'resource-categories', 'Finance', 8, 1, '::1', 1593728575),
(58, 'administrator', 'created', 'Taxonomy', 'resource-categories', 'Housing market', 9, 1, '::1', 1593728582),
(59, 'administrator', 'updated', 'Menu', '', 'Primary Menu', 0, 1, '::1', 1593728785),
(60, 'administrator', 'updated', 'Menu', '', 'Primary Menu', 0, 1, '::1', 1593728788),
(61, 'administrator', 'updated', 'Menu', '', 'Primary Menu', 0, 1, '::1', 1593728789),
(62, 'administrator', 'installed', 'Plugin', '3.2.4', 'Duplicate Post', 0, 1, '::1', 1593728830),
(63, 'administrator', 'activated', 'Plugin', '', 'Duplicate Post', 0, 1, '::1', 1593728836),
(64, 'administrator', 'created', 'Taxonomy', 'help-advice-categories', 'Buying a home', 10, 1, '::1', 1593728851),
(65, 'administrator', 'created', 'Taxonomy', 'help-advice-categories', 'Selling your home', 11, 1, '::1', 1593728857),
(66, 'administrator', 'created', 'Taxonomy', 'help-advice-categories', 'Mortgages', 12, 1, '::1', 1593728861),
(67, 'administrator', 'created', 'Taxonomy', 'help-advice-categories', 'Lettings', 13, 1, '::1', 1593728865),
(68, 'administrator', 'created', 'Taxonomy', 'help-advice-categories', 'Landlords', 14, 1, '::1', 1593728869),
(69, 'administrator', 'created', 'Taxonomy', 'help-advice-categories', 'Finance', 15, 1, '::1', 1593728874),
(70, 'administrator', 'created', 'Taxonomy', 'help-advice-categories', 'Housing market', 16, 1, '::1', 1593728881),
(71, 'administrator', 'installed', 'Plugin', '1.5', 'Classic Editor', 0, 1, '::1', 1593728907),
(72, 'administrator', 'activated', 'Plugin', '', 'Classic Editor', 0, 1, '::1', 1593728915),
(73, 'administrator', 'created', 'Post', 'help-advice', 'Help &#038; advice buying a home', 36, 1, '::1', 1593728966),
(74, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 36, 1, '::1', 1593728977),
(75, 'administrator', 'added', 'Attachment', 'attachment', 'placeholder-images-image_large', 37, 1, '::1', 1593729014),
(76, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 36, 1, '::1', 1593729036),
(77, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 38, 1, '::1', 1593729078),
(78, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 39, 1, '::1', 1593729083),
(79, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 40, 1, '::1', 1593729085),
(80, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 44, 1, '::1', 1593729086),
(81, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice finance', 38, 1, '::1', 1593729103),
(82, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice finance', 38, 1, '::1', 1593729107),
(83, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice housing market', 39, 1, '::1', 1593729117),
(84, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice landlords', 43, 1, '::1', 1593729128),
(85, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice lettings', 42, 1, '::1', 1593729138),
(86, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice mortgages', 41, 1, '::1', 1593729147),
(87, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home', 40, 1, '::1', 1593729157),
(88, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 2', 46, 1, '::1', 1593729170),
(89, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 3', 45, 1, '::1', 1593729179),
(90, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 3', 45, 1, '::1', 1593729184),
(91, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 2', 46, 1, '::1', 1593729187),
(92, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling you home 4', 44, 1, '::1', 1593729199),
(93, 'administrator', 'created', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1593786189),
(94, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Options', 48, 1, '::1', 1593786190),
(95, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Horizontal', 49, 1, '::1', 1593786190),
(96, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Vertical', 50, 1, '::1', 1593786190),
(97, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Horizontal White', 51, 1, '::1', 1593786190),
(98, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Vertical White', 52, 1, '::1', 1593786190),
(99, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1593786190),
(100, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1593786225) ;
INSERT INTO `league_aryo_activity_log` ( `histid`, `user_caps`, `action`, `object_type`, `object_subtype`, `object_name`, `object_id`, `user_id`, `hist_ip`, `hist_time`) VALUES
(101, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Horizontal', 49, 1, '::1', 1593786225),
(102, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Vertical', 50, 1, '::1', 1593786225),
(103, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Horizontal White', 51, 1, '::1', 1593786225),
(104, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Vertical White', 52, 1, '::1', 1593786225),
(105, 'administrator', 'added', 'Attachment', 'attachment', 'logo-horizontal', 53, 1, '::1', 1593786364),
(106, 'administrator', 'added', 'Attachment', 'attachment', 'logo-horizontal-white', 54, 1, '::1', 1593786365),
(107, 'administrator', 'added', 'Attachment', 'attachment', 'logo-vertical', 55, 1, '::1', 1593786440),
(108, 'administrator', 'added', 'Attachment', 'attachment', 'logo-vertical-white', 56, 1, '::1', 1593786441),
(109, 'administrator', 'deleted', 'Attachment', 'attachment', 'logo-vertical-white', 56, 1, '::1', 1593786630),
(110, 'administrator', 'deleted', 'Attachment', 'attachment', 'logo-horizontal-white', 54, 1, '::1', 1593786631),
(111, 'administrator', 'deleted', 'Attachment', 'attachment', 'logo-vertical', 55, 1, '::1', 1593786632),
(112, 'administrator', 'deleted', 'Attachment', 'attachment', 'logo-horizontal', 53, 1, '::1', 1593786634),
(113, 'administrator', 'added', 'Attachment', 'attachment', 'logo-vertical', 57, 1, '::1', 1593786638),
(114, 'administrator', 'added', 'Attachment', 'attachment', 'logo-vertical-white', 58, 1, '::1', 1593786664),
(115, 'administrator', 'added', 'Attachment', 'attachment', 'logo-horizontal', 59, 1, '::1', 1593786751),
(116, 'administrator', 'added', 'Attachment', 'attachment', 'logo-horizontal-white', 60, 1, '::1', 1593786752),
(117, 'administrator', 'created', 'Post', 'acf-field-group', 'Homepage Hero', 61, 1, '::1', 1593788299),
(118, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Message', 62, 1, '::1', 1593788299),
(119, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Top Line', 63, 1, '::1', 1593788299),
(120, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Main Line', 64, 1, '::1', 1593788299),
(121, 'administrator', 'updated', 'Post', 'acf-field-group', 'Homepage Hero', 61, 1, '::1', 1593788299),
(122, 'administrator', 'updated', 'Post', 'acf-field-group', 'Homepage Hero', 61, 1, '::1', 1593788304),
(123, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '::1', 1593788331),
(124, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '::1', 1593788373),
(125, 'administrator', 'added', 'Attachment', 'attachment', 'istockphoto-1225367483-1024&#215;1024', 67, 1, '::1', 1593788868),
(126, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '::1', 1593788873),
(127, 'administrator', 'created', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1593792210),
(128, 'administrator', 'updated', 'Post', 'acf-field', 'Hero', 69, 1, '::1', 1593792210),
(129, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Heading', 70, 1, '::1', 1593792210),
(130, 'administrator', 'updated', 'Post', 'acf-field', 'Has Search Bar', 71, 1, '::1', 1593792210),
(131, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1593792210),
(132, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1593792216),
(133, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1593792217),
(134, 'administrator', 'updated', 'Post', 'page', 'Buy a home', 8, 1, '::1', 1593792251),
(135, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1593793015),
(136, 'administrator', 'updated', 'Post', 'page', 'Free sales valuations', 12, 1, '::1', 1593793034),
(137, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '::1', 1593793045),
(138, 'administrator', 'updated', 'Post', 'page', 'Meet the team', 16, 1, '::1', 1593793055),
(139, 'administrator', 'updated', 'Post', 'page', 'Contact Us', 20, 1, '::1', 1593793065),
(140, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1593793080),
(141, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1593793162),
(142, 'administrator', 'created', 'Post', 'acf-field-group', 'Meet the team options', 83, 1, '::1', 1593795007),
(143, 'administrator', 'updated', 'Post', 'acf-field', 'Team Members', 84, 1, '::1', 1593795007),
(144, 'administrator', 'updated', 'Post', 'acf-field', 'Name', 85, 1, '::1', 1593795007),
(145, 'administrator', 'updated', 'Post', 'acf-field', 'Job Title', 86, 1, '::1', 1593795007),
(146, 'administrator', 'updated', 'Post', 'acf-field', 'Phone Number', 87, 1, '::1', 1593795007),
(147, 'administrator', 'updated', 'Post', 'acf-field', 'Email address', 88, 1, '::1', 1593795007),
(148, 'administrator', 'updated', 'Post', 'acf-field', 'Profile Photo', 89, 1, '::1', 1593795007),
(149, 'administrator', 'updated', 'Post', 'acf-field', 'Biography', 90, 1, '::1', 1593795007),
(150, 'administrator', 'updated', 'Post', 'acf-field-group', 'Meet the team options', 83, 1, '::1', 1593795008),
(151, 'administrator', 'added', 'Attachment', 'attachment', 'profile-image', 91, 1, '::1', 1593795072),
(152, 'administrator', 'updated', 'Post', 'page', 'Meet the team', 16, 1, '::1', 1593795109),
(153, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1593802007),
(154, 'administrator', 'updated', 'Post', 'acf-field', 'Buckets', 93, 1, '::1', 1593802007),
(155, 'administrator', 'updated', 'Post', 'acf-field', 'Title', 95, 1, '::1', 1593802007),
(156, 'administrator', 'updated', 'Post', 'acf-field', 'Intro', 96, 1, '::1', 1593802007),
(157, 'administrator', 'updated', 'Post', 'acf-field', 'Background Image', 97, 1, '::1', 1593802007),
(158, 'administrator', 'updated', 'Post', 'acf-field', 'Button Text', 98, 1, '::1', 1593802007),
(159, 'administrator', 'updated', 'Post', 'acf-field', 'Button Link', 99, 1, '::1', 1593802007),
(160, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1593802012),
(161, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1593802039),
(162, 'administrator', 'updated', 'Post', 'acf-field', 'Buckets', 94, 1, '::1', 1593802039),
(163, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '::1', 1594143984),
(164, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '::1', 1594144286),
(165, 'administrator', 'updated', 'Post', 'acf-field-group', 'Homepage Options', 61, 1, '::1', 1594144337),
(166, 'administrator', 'updated', 'Post', 'acf-field', 'H1', 101, 1, '::1', 1594144337),
(167, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '::1', 1594144352),
(168, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1594149082),
(169, 'administrator', 'updated', 'Post', 'acf-field', 'Intro', 96, 1, '::1', 1594149082),
(170, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594158982),
(171, 'administrator', 'updated', 'Post', 'page', 'Free sales valuations', 12, 1, '::1', 1594158985),
(172, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1594158988),
(173, 'administrator', 'updated', 'Post', 'page', 'Buy a home', 8, 1, '::1', 1594158990),
(174, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '::1', 1594158994),
(175, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1594159047),
(176, 'administrator', 'updated', 'Post', 'acf-field', 'H1', 109, 1, '::1', 1594159047),
(177, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594159084),
(178, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594159184),
(179, 'administrator', 'created', 'Post', 'acf-field-group', 'Why Choose Us', 113, 1, '::1', 1594159799),
(180, 'administrator', 'updated', 'Post', 'acf-field', 'Why choose us', 114, 1, '::1', 1594159799),
(181, 'administrator', 'updated', 'Post', 'acf-field', 'Image', 115, 1, '::1', 1594159799),
(182, 'administrator', 'updated', 'Post', 'acf-field', 'TItle', 116, 1, '::1', 1594159799),
(183, 'administrator', 'updated', 'Post', 'acf-field', 'Text', 117, 1, '::1', 1594159800),
(184, 'administrator', 'updated', 'Post', 'acf-field-group', 'Why Choose Us', 113, 1, '::1', 1594159800),
(185, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594159890),
(186, 'administrator', 'updated', 'Attachment', 'attachment', 'logo-vertical', 57, 1, '::1', 1594159890),
(187, 'administrator', 'added', 'Attachment', 'attachment', 'Friends Arriving for Social Gathering', 119, 1, '::1', 1594160322),
(188, 'administrator', 'added', 'Attachment', 'attachment', 'Young Family Collecting Keys To New Home From Realtor', 120, 1, '::1', 1594160375),
(189, 'administrator', 'added', 'Attachment', 'attachment', 'Couple packing together', 121, 1, '::1', 1594160418),
(190, 'administrator', 'added', 'Attachment', 'attachment', '3122736571_f', 122, 1, '::1', 1594160765),
(191, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1594160800),
(192, 'administrator', 'updated', 'Post', 'acf-field', 'Property Placeholder', 123, 1, '::1', 1594160800),
(193, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1594161933),
(194, 'administrator', 'updated', 'Post', 'acf-field', 'Page Options', 125, 1, '::1', 1594161933),
(195, 'administrator', 'updated', 'Post', 'acf-field', 'Show Why Choose Us', 126, 1, '::1', 1594161933),
(196, 'administrator', 'updated', 'Post', 'acf-field', 'Show Buckets', 127, 1, '::1', 1594161933),
(197, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594161947),
(198, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594161955),
(199, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1594162013),
(200, 'administrator', 'updated', 'Post', 'acf-field', 'Show Why Choose Us', 126, 1, '::1', 1594162013) ;
INSERT INTO `league_aryo_activity_log` ( `histid`, `user_caps`, `action`, `object_type`, `object_subtype`, `object_name`, `object_id`, `user_id`, `hist_ip`, `hist_time`) VALUES
(201, 'administrator', 'updated', 'Post', 'acf-field', 'Show Buckets', 127, 1, '::1', 1594162013),
(202, 'administrator', 'updated', 'Post', 'acf-field', 'Show Team', 130, 1, '::1', 1594162013),
(203, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594162062),
(204, 'administrator', 'updated', 'Post', 'acf-field', 'Why choose us', 114, 1, '::1', 1594162080),
(205, 'administrator', 'trashed', 'Post', 'acf-field-group', 'Why Choose Us', 113, 1, '::1', 1594162086),
(206, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1594162115),
(207, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Horizontal', 49, 1, '::1', 1594162116),
(208, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Vertical', 50, 1, '::1', 1594162116),
(209, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Horizontal White', 51, 1, '::1', 1594162116),
(210, 'administrator', 'updated', 'Post', 'acf-field', 'Logo Vertical White', 52, 1, '::1', 1594162116),
(211, 'administrator', 'updated', 'Post', 'acf-field', 'Buckets', 93, 1, '::1', 1594162116),
(212, 'administrator', 'updated', 'Post', 'acf-field', 'Property Placeholder', 123, 1, '::1', 1594162116),
(213, 'administrator', 'updated', 'Post', 'acf-field', 'Why Choose Us', 132, 1, '::1', 1594162116),
(214, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1594162116),
(215, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594162155),
(216, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594162459),
(217, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '::1', 1594162472),
(218, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '::1', 1594386439),
(219, 'administrator', 'added', 'Attachment', 'attachment', 'propertymark_coventry_letting_agents', 136, 1, '::1', 1594389888),
(220, 'administrator', 'added', 'Attachment', 'attachment', 'Property Ombudsman Logo', 137, 1, '::1', 1594389891),
(221, 'administrator', 'deleted', 'Attachment', 'attachment', 'propertymark_coventry_letting_agents', 136, 1, '::1', 1594390115),
(222, 'administrator', 'added', 'Attachment', 'attachment', 'property-mark', 138, 1, '::1', 1594390126),
(223, 'administrator', 'created', 'Post', 'acf-field-group', 'Guide To Selling Fields', 139, 1, '::1', 1594394711),
(224, 'administrator', 'updated', 'Post', 'acf-field', 'Guide Steps', 140, 1, '::1', 1594394711),
(225, 'administrator', 'updated', 'Post', 'acf-field', 'Image', 141, 1, '::1', 1594394711),
(226, 'administrator', 'updated', 'Post', 'acf-field', 'Title', 142, 1, '::1', 1594394711),
(227, 'administrator', 'updated', 'Post', 'acf-field', 'Content', 143, 1, '::1', 1594394711),
(228, 'administrator', 'updated', 'Post', 'acf-field', 'Is Highlighted', 144, 1, '::1', 1594394711),
(229, 'administrator', 'updated', 'Post', 'acf-field-group', 'Guide To Selling Fields', 139, 1, '::1', 1594394711),
(230, 'administrator', 'updated', 'Post', 'acf-field-group', 'Guide To Selling Fields', 139, 1, '::1', 1594394717),
(231, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1594395726),
(232, 'administrator', 'updated', 'Post', 'acf-field-group', 'Guide To Selling Options', 139, 1, '::1', 1594395763),
(233, 'administrator', 'updated', 'Post', 'acf-field', 'Image', 141, 1, '::1', 1594395763),
(234, 'administrator', 'updated', 'Post', 'acf-field', 'Is Highlighted', 144, 1, '::1', 1594395763),
(235, 'administrator', 'updated', 'Post', 'acf-field', 'Title', 142, 1, '::1', 1594395763),
(236, 'administrator', 'updated', 'Post', 'acf-field', 'Content', 143, 1, '::1', 1594395764),
(237, 'administrator', 'updated', 'Post', 'acf-field-group', 'Guide To Selling Options', 139, 1, '::1', 1594395764),
(238, 'administrator', 'updated', 'Post', 'acf-field-group', 'Guide To Selling Options', 139, 1, '::1', 1594395774),
(239, 'administrator', 'updated', 'Post', 'acf-field', 'Guide Steps', 140, 1, '::1', 1594395774),
(240, 'administrator', 'updated', 'Post', 'acf-field-group', 'Guide To Selling Options', 139, 1, '::1', 1594395796),
(241, 'administrator', 'updated', 'Post', 'acf-field', 'Guide Steps', 140, 1, '::1', 1594395796),
(242, 'administrator', 'updated', 'Post', 'acf-field-group', 'Guide To Selling Options', 139, 1, '::1', 1594395823),
(243, 'administrator', 'updated', 'Post', 'acf-field', 'Guide Steps', 140, 1, '::1', 1594395823),
(244, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1594396227),
(245, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1594396713),
(246, 'administrator', 'updated', 'Attachment', 'attachment', '3122736571_f', 122, 1, '::1', 1594396713),
(247, 'administrator', 'updated', 'Attachment', 'attachment', 'Couple packing together', 121, 1, '::1', 1594396713),
(248, 'administrator', 'updated', 'Attachment', 'attachment', 'Friends Arriving for Social Gathering', 119, 1, '::1', 1594396713),
(249, 'administrator', 'updated', 'Attachment', 'attachment', 'Young Family Collecting Keys To New Home From Realtor', 120, 1, '::1', 1594396713),
(250, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1594396896),
(251, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1594397776),
(252, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '::1', 1594397802),
(253, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '::1', 1594397900),
(254, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling you home 4', 44, 1, '::1', 1594405493),
(255, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 3', 45, 1, '::1', 1594405501),
(256, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 2', 46, 1, '::1', 1594405554),
(257, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home', 40, 1, '::1', 1594405561),
(258, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice mortgages', 41, 1, '::1', 1594405574),
(259, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice lettings', 42, 1, '::1', 1594405584),
(260, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice landlords', 43, 1, '::1', 1594405594),
(261, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice housing market', 39, 1, '::1', 1594405601),
(262, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice finance', 38, 1, '::1', 1594405609),
(263, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 36, 1, '::1', 1594405617),
(264, 'administrator', 'updated', 'Post', 'page', 'Contact Us', 20, 1, '::1', 1594472378),
(265, 'administrator', 'updated', 'Post', 'page', 'Contact Us', 20, 1, '::1', 1594472668),
(266, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1594476354),
(267, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling you home 4', 44, 1, '::1', 1594476400),
(268, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling you home 4', 44, 1, '::1', 1594477263),
(269, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling you home 4', 44, 1, '::1', 1594477387),
(270, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling you home 4', 156, 1, '::1', 1594479531),
(271, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 3', 157, 1, '::1', 1594479532),
(272, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 2', 158, 1, '::1', 1594479532),
(273, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home', 159, 1, '::1', 1594479532),
(274, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice mortgages', 160, 1, '::1', 1594479532),
(275, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice lettings', 161, 1, '::1', 1594479532),
(276, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice landlords', 162, 1, '::1', 1594479532),
(277, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice housing market', 163, 1, '::1', 1594479532),
(278, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice finance', 164, 1, '::1', 1594479532),
(279, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 165, 1, '::1', 1594479532),
(280, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 3', 157, 1, '::1', 1594479544),
(281, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home 2', 158, 1, '::1', 1594479544),
(282, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling your home', 159, 1, '::1', 1594479544),
(283, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice mortgages', 160, 1, '::1', 1594479544),
(284, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice lettings', 161, 1, '::1', 1594479545),
(285, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice landlords', 162, 1, '::1', 1594479545),
(286, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice housing market', 163, 1, '::1', 1594479545),
(287, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice finance', 164, 1, '::1', 1594479545),
(288, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice buying a home', 165, 1, '::1', 1594479545),
(289, 'administrator', 'updated', 'Post', 'help-advice', 'Help &#038; advice selling you home 4', 156, 1, '::1', 1594479545),
(290, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1594481591),
(291, 'administrator', 'updated', 'Post', 'acf-field', 'Sidebar Logos', 166, 1, '::1', 1594481592),
(292, 'administrator', 'updated', 'Post', 'acf-field', 'Name', 168, 1, '::1', 1594481592),
(293, 'administrator', 'updated', 'Post', 'acf-field', 'Image', 169, 1, '::1', 1594481592),
(294, 'administrator', 'updated', 'Post', 'acf-field', 'Link', 170, 1, '::1', 1594481592),
(295, 'administrator', 'updated', 'Post', 'acf-field-group', 'Page Options', 68, 1, '::1', 1594481592),
(296, 'administrator', 'added', 'Attachment', 'attachment', 'rightmove', 171, 1, '::1', 1594481908),
(297, 'administrator', 'added', 'Attachment', 'attachment', 'zoopla', 172, 1, '::1', 1594481912),
(298, 'administrator', 'added', 'Attachment', 'attachment', 'nethouseprices', 173, 1, '::1', 1594481917),
(299, 'administrator', 'added', 'Attachment', 'attachment', 'primelocation', 174, 1, '::1', 1594481922),
(300, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '::1', 1594481928) ;
INSERT INTO `league_aryo_activity_log` ( `histid`, `user_caps`, `action`, `object_type`, `object_subtype`, `object_name`, `object_id`, `user_id`, `hist_ip`, `hist_time`) VALUES
(301, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '86.20.174.156', 1594806426),
(302, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '86.20.174.156', 1594806519),
(303, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '86.20.174.156', 1594806536),
(304, 'administrator', 'updated', 'Post', 'page', 'Buy a home', 8, 1, '86.20.174.156', 1594806556),
(305, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '86.20.174.156', 1594806575),
(306, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '86.20.174.156', 1594806684),
(307, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '86.20.174.156', 1594806756),
(308, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '86.20.174.156', 1594807060),
(309, 'administrator', 'updated', 'Post', 'page', 'Meet the team', 16, 1, '86.20.174.156', 1594807300),
(310, 'administrator', 'updated', 'Post', 'page', 'Meet the team', 16, 1, '86.20.174.156', 1594807815),
(311, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '94.119.96.0', 1594814172),
(312, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '::1', 1594834448),
(313, 'administrator', 'updated', 'Post', 'page', 'Buy a home', 8, 1, '::1', 1594834477),
(314, 'administrator', 'updated', 'Post', 'page', 'Meet Rene &#038; Rod', 16, 1, '::1', 1594834525),
(315, 'administrator', 'updated', 'Post', 'acf-field-group', 'Homepage Options', 61, 1, '::1', 1594835073),
(316, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Carousel', 187, 1, '::1', 1594835073),
(317, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Message', 62, 1, '::1', 1594835073),
(318, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Top Line', 63, 1, '::1', 1594835073),
(319, 'administrator', 'updated', 'Post', 'acf-field', 'Hero Main Line', 64, 1, '::1', 1594835073),
(320, 'administrator', 'updated', 'Post', 'acf-field', 'H1', 101, 1, '::1', 1594835073),
(321, 'administrator', 'updated', 'Attachment', 'attachment', 'Young Family Collecting Keys To New Home From Realtor', 120, 1, '::1', 1594835279),
(322, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '::1', 1594835281),
(323, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '::1', 1594835709),
(324, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1594835922),
(325, 'administrator', 'updated', 'Post', 'acf-field', 'Mover Logos', 191, 1, '::1', 1594835922),
(326, 'administrator', 'updated', 'Post', 'acf-field', 'Mover Logo Headline', 192, 1, '::1', 1594835922),
(327, 'administrator', 'updated', 'Post', 'acf-field', 'Mover Logos', 193, 1, '::1', 1594835923),
(328, 'administrator', 'updated', 'Post', 'acf-field', 'Name', 194, 1, '::1', 1594835923),
(329, 'administrator', 'updated', 'Post', 'acf-field', 'Logo', 195, 1, '::1', 1594835923),
(330, 'administrator', 'updated', 'Post', 'acf-field', 'Link', 196, 1, '::1', 1594835923),
(331, 'administrator', 'updated', 'Post', 'acf-field-group', 'Additional Site Options', 47, 1, '::1', 1594835923),
(332, 'administrator', 'deleted', 'Attachment', 'attachment', 'primelocation', 174, 1, '::1', 1594836100),
(333, 'administrator', 'deleted', 'Attachment', 'attachment', 'nethouseprices', 173, 1, '::1', 1594836103),
(334, 'administrator', 'deleted', 'Attachment', 'attachment', 'zoopla', 172, 1, '::1', 1594836106),
(335, 'administrator', 'deleted', 'Attachment', 'attachment', 'rightmove', 171, 1, '::1', 1594836107),
(336, 'administrator', 'added', 'Attachment', 'attachment', 'Rightmove_logo_DEC2016', 197, 1, '::1', 1594836112),
(337, 'administrator', 'added', 'Attachment', 'attachment', 'Zoopla-logo-Purple-RGBPNG', 198, 1, '::1', 1594836113),
(338, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '::1', 1594836131),
(339, 'administrator', 'updated', 'Attachment', 'attachment', 'Rightmove_logo_DEC2016', 197, 1, '::1', 1594836131),
(340, 'administrator', 'updated', 'Attachment', 'attachment', 'Zoopla-logo-Purple-RGBPNG', 198, 1, '::1', 1594836131),
(341, 'administrator', 'updated', 'Menu', '', 'Primary Menu', 0, 1, '::1', 1594836401),
(342, 'administrator', 'updated', 'Options', '', 'blogname', 0, 1, '::1', 1594836536),
(343, 'administrator', 'updated', 'Post', 'acf-field-group', 'Meet the team options', 83, 1, '::1', 1594837108),
(344, 'administrator', 'updated', 'Post', 'acf-field', 'Phone Number', 87, 1, '::1', 1594837108),
(345, 'administrator', 'updated', 'Post', 'acf-field', 'Email address', 88, 1, '::1', 1594837108),
(346, 'administrator', 'updated', 'Post', 'acf-field', 'Profile Photo', 89, 1, '::1', 1594837108),
(347, 'administrator', 'updated', 'Post', 'acf-field', 'Casual Photo', 200, 1, '::1', 1594837108),
(348, 'administrator', 'updated', 'Post', 'acf-field', 'Biography', 90, 1, '::1', 1594837108),
(349, 'administrator', 'updated', 'Post', 'acf-field-group', 'Meet the team options', 83, 1, '::1', 1594837109),
(350, 'administrator', 'updated', 'Post', 'acf-field-group', 'Meet the team options', 83, 1, '::1', 1594837140),
(351, 'administrator', 'updated', 'Post', 'acf-field', 'Name', 85, 1, '::1', 1594837140),
(352, 'administrator', 'updated', 'Post', 'acf-field', 'Job Title', 86, 1, '::1', 1594837140),
(353, 'administrator', 'updated', 'Post', 'acf-field-group', 'Meet the team options', 83, 1, '::1', 1594837141),
(354, 'administrator', 'added', 'Attachment', 'attachment', 'profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D', 201, 1, '::1', 1594837224),
(355, 'administrator', 'updated', 'Post', 'page', 'Meet Rene &#038; Rod', 16, 1, '::1', 1594837240),
(356, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '86.20.174.156', 1595436837),
(357, 'administrator', 'added', 'Attachment', 'attachment', 'logo-update-v8', 203, 1, '86.20.174.156', 1595437086),
(358, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '86.20.174.156', 1595437288),
(359, 'administrator', 'updated', 'Post', 'page', 'About us', 14, 1, '86.20.174.156', 1595437302),
(360, 'administrator', 'updated', 'Post', 'page', 'Meet Rene &#038; Rod', 16, 1, '86.20.174.156', 1595437342),
(361, 'administrator', 'updated', 'Post', 'page', 'Meet Rene &#038; Rod', 16, 1, '86.20.174.156', 1595437356),
(362, 'administrator', 'updated', 'Post', 'page', 'Buy a home', 8, 1, '86.20.174.156', 1595437365),
(363, 'administrator', 'updated', 'Post', 'page', 'Sell your home', 10, 1, '86.20.174.156', 1595437379),
(364, 'administrator', 'updated', 'Post', 'page', 'How to sell a home', 18, 1, '86.20.174.156', 1595437483),
(365, 'administrator', 'added', 'Attachment', 'attachment', 'logo-update-v9', 211, 1, '86.20.174.156', 1595438526),
(366, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '86.20.174.156', 1595438706),
(367, 'administrator', 'updated', 'Post', 'page', 'Home', 6, 1, '86.20.174.156', 1595438761),
(368, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '86.20.174.156', 1595534836),
(369, 'guest', 'logged_in', 'User', '', 'admin-league', 1, 1, '::1', 1595535661),
(370, 'administrator', 'updated', 'Plugin', '3.2.5', 'Yoast Duplicate Post', 0, 1, '::1', 1595535688),
(371, 'administrator', 'updated', 'Plugin', '2.2.1', 'WP Mail SMTP', 0, 1, '::1', 1595535708),
(372, 'administrator', 'updated', 'Plugin', '14.6.1', 'Yoast SEO', 0, 1, '::1', 1595535740) ;

#
# End of data contents of table `league_aryo_activity_log`
# --------------------------------------------------------



#
# Delete any existing table `league_commentmeta`
#

DROP TABLE IF EXISTS `league_commentmeta`;


#
# Table structure of table `league_commentmeta`
#

CREATE TABLE `league_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_commentmeta`
#

#
# End of data contents of table `league_commentmeta`
# --------------------------------------------------------



#
# Delete any existing table `league_comments`
#

DROP TABLE IF EXISTS `league_comments`;


#
# Table structure of table `league_comments`
#

CREATE TABLE `league_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_comments`
#
INSERT INTO `league_comments` ( `comment_ID`, `comment_post_ID`, `comment_author`, `comment_author_email`, `comment_author_url`, `comment_author_IP`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_karma`, `comment_approved`, `comment_agent`, `comment_type`, `comment_parent`, `user_id`) VALUES
(1, 1, 'A WordPress Commenter', 'wapuu@wordpress.example', 'https://wordpress.org/', '', '2020-07-02 18:33:39', '2020-07-02 17:33:39', 'Hi, this is a comment.\nTo get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.\nCommenter avatars come from <a href="https://gravatar.com">Gravatar</a>.', 0, '1', '', '', 0, 0) ;

#
# End of data contents of table `league_comments`
# --------------------------------------------------------



#
# Delete any existing table `league_links`
#

DROP TABLE IF EXISTS `league_links`;


#
# Table structure of table `league_links`
#

CREATE TABLE `league_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT 1,
  `link_rating` int(11) NOT NULL DEFAULT 0,
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_links`
#

#
# End of data contents of table `league_links`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_action_meta`
#

DROP TABLE IF EXISTS `league_nf3_action_meta`;


#
# Table structure of table `league_nf3_action_meta`
#

CREATE TABLE `league_nf3_action_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `key` longtext NOT NULL,
  `value` longtext DEFAULT NULL,
  `meta_key` longtext DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_action_meta`
#
INSERT INTO `league_nf3_action_meta` ( `id`, `parent_id`, `key`, `value`, `meta_key`, `meta_value`) VALUES
(1, 1, 'objectType', 'Action', 'objectType', 'Action'),
(2, 1, 'objectDomain', 'actions', 'objectDomain', 'actions'),
(3, 1, 'editActive', '', 'editActive', ''),
(4, 1, 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}', 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}'),
(5, 1, 'payment_gateways', '', 'payment_gateways', ''),
(6, 1, 'payment_total', '0', 'payment_total', '0'),
(7, 1, 'tag', '', 'tag', ''),
(8, 1, 'to', '{wp:admin_email}', 'to', '{wp:admin_email}'),
(9, 1, 'email_subject', 'Ninja Forms Submission', 'email_subject', 'Ninja Forms Submission'),
(10, 1, 'email_message', '{fields_table}', 'email_message', '{fields_table}'),
(11, 1, 'from_name', '', 'from_name', ''),
(12, 1, 'from_address', '', 'from_address', ''),
(13, 1, 'reply_to', '', 'reply_to', ''),
(14, 1, 'email_format', 'html', 'email_format', 'html'),
(15, 1, 'cc', '', 'cc', ''),
(16, 1, 'bcc', '', 'bcc', ''),
(17, 1, 'attach_csv', '', 'attach_csv', ''),
(18, 1, 'redirect_url', '', 'redirect_url', ''),
(19, 1, 'email_message_plain', '', 'email_message_plain', ''),
(20, 2, 'to', '{field:email}', 'to', '{field:email}'),
(21, 2, 'subject', 'This is an email action.', 'subject', 'This is an email action.'),
(22, 2, 'message', 'Hello, Ninja Forms!', 'message', 'Hello, Ninja Forms!'),
(23, 2, 'objectType', 'Action', 'objectType', 'Action'),
(24, 2, 'objectDomain', 'actions', 'objectDomain', 'actions'),
(25, 2, 'editActive', '', 'editActive', ''),
(26, 2, 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:0:{}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}', 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:0:{}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}'),
(27, 2, 'payment_gateways', '', 'payment_gateways', ''),
(28, 2, 'payment_total', '0', 'payment_total', '0'),
(29, 2, 'tag', '', 'tag', ''),
(30, 2, 'email_subject', 'Submission Confirmation ', 'email_subject', 'Submission Confirmation '),
(31, 2, 'email_message', '<p>{all_fields_table}<br></p>', 'email_message', '<p>{all_fields_table}<br></p>'),
(32, 2, 'from_name', '', 'from_name', ''),
(33, 2, 'from_address', '', 'from_address', ''),
(34, 2, 'reply_to', '', 'reply_to', ''),
(35, 2, 'email_format', 'html', 'email_format', 'html'),
(36, 2, 'cc', '', 'cc', ''),
(37, 2, 'bcc', '', 'bcc', ''),
(38, 2, 'attach_csv', '', 'attach_csv', ''),
(39, 2, 'email_message_plain', '', 'email_message_plain', ''),
(40, 3, 'objectType', 'Action', 'objectType', 'Action'),
(41, 3, 'objectDomain', 'actions', 'objectDomain', 'actions'),
(42, 3, 'editActive', '', 'editActive', ''),
(43, 3, 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}', 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}'),
(44, 3, 'payment_gateways', '', 'payment_gateways', ''),
(45, 3, 'payment_total', '0', 'payment_total', '0'),
(46, 3, 'tag', '', 'tag', ''),
(47, 3, 'to', '{system:admin_email}', 'to', '{system:admin_email}'),
(48, 3, 'email_subject', 'New message from {field:name}', 'email_subject', 'New message from {field:name}'),
(49, 3, 'email_message', '<p>{field:message}</p><p>-{field:name} ( {field:email} )</p>', 'email_message', '<p>{field:message}</p><p>-{field:name} ( {field:email} )</p>'),
(50, 3, 'from_name', '', 'from_name', ''),
(51, 3, 'from_address', '', 'from_address', ''),
(52, 3, 'reply_to', '{field:email}', 'reply_to', '{field:email}'),
(53, 3, 'email_format', 'html', 'email_format', 'html'),
(54, 3, 'cc', '', 'cc', ''),
(55, 3, 'bcc', '', 'bcc', ''),
(56, 3, 'attach_csv', '0', 'attach_csv', '0'),
(57, 3, 'email_message_plain', '', 'email_message_plain', ''),
(58, 4, 'message', 'Thank you {field:name} for filling out my form!', 'message', 'Thank you {field:name} for filling out my form!'),
(59, 4, 'objectType', 'Action', 'objectType', 'Action'),
(60, 4, 'objectDomain', 'actions', 'objectDomain', 'actions'),
(61, 4, 'editActive', '', 'editActive', ''),
(62, 4, 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}', 'conditions', 'a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}'),
(63, 4, 'payment_gateways', '', 'payment_gateways', ''),
(64, 4, 'payment_total', '0', 'payment_total', '0'),
(65, 4, 'tag', '', 'tag', ''),
(66, 4, 'to', '{wp:admin_email}', 'to', '{wp:admin_email}'),
(67, 4, 'email_subject', 'Ninja Forms Submission', 'email_subject', 'Ninja Forms Submission'),
(68, 4, 'email_message', '{fields_table}', 'email_message', '{fields_table}'),
(69, 4, 'from_name', '', 'from_name', ''),
(70, 4, 'from_address', '', 'from_address', ''),
(71, 4, 'reply_to', '', 'reply_to', ''),
(72, 4, 'email_format', 'html', 'email_format', 'html'),
(73, 4, 'cc', '', 'cc', ''),
(74, 4, 'bcc', '', 'bcc', ''),
(75, 4, 'attach_csv', '', 'attach_csv', ''),
(76, 4, 'redirect_url', '', 'redirect_url', ''),
(77, 4, 'success_msg', '<p>Form submitted successfully.</p><p>A confirmation email was sent to {field:email}.</p>', 'success_msg', '<p>Form submitted successfully.</p><p>A confirmation email was sent to {field:email}.</p>'),
(78, 4, 'email_message_plain', '', 'email_message_plain', ''),
(79, 1, 'message', 'This action adds users to WordPress&#039; personal data export tool, allowing admins to comply with the GDPR and other privacy regulations from the site&#039;s front end.', 'message', 'This action adds users to WordPress&#039; personal data export tool, allowing admins to comply with the GDPR and other privacy regulations from the site&#039;s front end.'),
(80, 1, 'submitter_email', '', 'submitter_email', ''),
(81, 1, 'fields-save-toggle', 'save_all', 'fields-save-toggle', 'save_all'),
(82, 1, 'exception_fields', 'a:0:{}', 'exception_fields', 'a:0:{}'),
(83, 1, 'set_subs_to_expire', '0', 'set_subs_to_expire', '0'),
(84, 1, 'subs_expire_time', '90', 'subs_expire_time', '90'),
(85, 3, 'message', 'This action adds users to WordPress&#039; personal data delete tool, allowing admins to comply with the GDPR and other privacy regulations from the site&#039;s front end.', 'message', 'This action adds users to WordPress&#039; personal data delete tool, allowing admins to comply with the GDPR and other privacy regulations from the site&#039;s front end.'),
(86, 4, 'submitter_email', '', 'submitter_email', ''),
(87, 4, 'fields-save-toggle', 'save_all', 'fields-save-toggle', 'save_all'),
(88, 4, 'exception_fields', 'a:0:{}', 'exception_fields', 'a:0:{}'),
(89, 4, 'set_subs_to_expire', '0', 'set_subs_to_expire', '0'),
(90, 4, 'subs_expire_time', '90', 'subs_expire_time', '90') ;

#
# End of data contents of table `league_nf3_action_meta`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_actions`
#

DROP TABLE IF EXISTS `league_nf3_actions`;


#
# Table structure of table `league_nf3_actions`
#

CREATE TABLE `league_nf3_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` longtext DEFAULT NULL,
  `key` longtext DEFAULT NULL,
  `type` longtext DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `parent_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `label` longtext DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_actions`
#
INSERT INTO `league_nf3_actions` ( `id`, `title`, `key`, `type`, `active`, `parent_id`, `created_at`, `updated_at`, `label`) VALUES
(1, '', '', 'save', 1, 1, '2020-07-02 18:34:31', '2020-07-02 18:34:31', 'Store Submission'),
(2, '', '', 'email', 1, 1, '2020-07-02 18:34:31', '2020-07-02 18:34:31', 'Email Confirmation'),
(3, '', '', 'email', 1, 1, '2020-07-02 18:34:31', '2020-07-02 18:34:31', 'Email Notification'),
(4, '', '', 'successmessage', 1, 1, '2020-07-02 18:34:31', '2020-07-02 18:34:31', 'Success Message') ;

#
# End of data contents of table `league_nf3_actions`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_chunks`
#

DROP TABLE IF EXISTS `league_nf3_chunks`;


#
# Table structure of table `league_nf3_chunks`
#

CREATE TABLE `league_nf3_chunks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `value` longtext DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_chunks`
#

#
# End of data contents of table `league_nf3_chunks`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_field_meta`
#

DROP TABLE IF EXISTS `league_nf3_field_meta`;


#
# Table structure of table `league_nf3_field_meta`
#

CREATE TABLE `league_nf3_field_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `key` longtext NOT NULL,
  `value` longtext DEFAULT NULL,
  `meta_key` longtext DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=308 DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_field_meta`
#
INSERT INTO `league_nf3_field_meta` ( `id`, `parent_id`, `key`, `value`, `meta_key`, `meta_value`) VALUES
(1, 1, 'label_pos', 'above', 'label_pos', 'above'),
(2, 1, 'required', '1', 'required', '1'),
(3, 1, 'order', '1', 'order', '1'),
(4, 1, 'placeholder', 'Your name *', 'placeholder', 'Your name *'),
(5, 1, 'default', '', 'default', ''),
(6, 1, 'wrapper_class', '', 'wrapper_class', ''),
(7, 1, 'element_class', '', 'element_class', ''),
(8, 1, 'objectType', 'Field', 'objectType', 'Field'),
(9, 1, 'objectDomain', 'fields', 'objectDomain', 'fields'),
(10, 1, 'editActive', '', 'editActive', ''),
(11, 1, 'container_class', '', 'container_class', ''),
(12, 1, 'input_limit', '', 'input_limit', ''),
(13, 1, 'input_limit_type', 'characters', 'input_limit_type', 'characters'),
(14, 1, 'input_limit_msg', 'Character(s) left', 'input_limit_msg', 'Character(s) left'),
(15, 1, 'manual_key', '', 'manual_key', ''),
(16, 1, 'disable_input', '', 'disable_input', ''),
(17, 1, 'admin_label', '', 'admin_label', ''),
(18, 1, 'help_text', '', 'help_text', ''),
(19, 1, 'desc_text', '', 'desc_text', ''),
(20, 1, 'disable_browser_autocomplete', '', 'disable_browser_autocomplete', ''),
(21, 1, 'mask', '', 'mask', ''),
(22, 1, 'custom_mask', '', 'custom_mask', ''),
(23, 1, 'wrap_styles_background-color', '', 'wrap_styles_background-color', ''),
(24, 1, 'wrap_styles_border', '', 'wrap_styles_border', ''),
(25, 1, 'wrap_styles_border-style', '', 'wrap_styles_border-style', ''),
(26, 1, 'wrap_styles_border-color', '', 'wrap_styles_border-color', ''),
(27, 1, 'wrap_styles_color', '', 'wrap_styles_color', ''),
(28, 1, 'wrap_styles_height', '', 'wrap_styles_height', ''),
(29, 1, 'wrap_styles_width', '', 'wrap_styles_width', ''),
(30, 1, 'wrap_styles_font-size', '', 'wrap_styles_font-size', ''),
(31, 1, 'wrap_styles_margin', '', 'wrap_styles_margin', ''),
(32, 1, 'wrap_styles_padding', '', 'wrap_styles_padding', ''),
(33, 1, 'wrap_styles_display', '', 'wrap_styles_display', ''),
(34, 1, 'wrap_styles_float', '', 'wrap_styles_float', ''),
(35, 1, 'wrap_styles_show_advanced_css', '0', 'wrap_styles_show_advanced_css', '0'),
(36, 1, 'wrap_styles_advanced', '', 'wrap_styles_advanced', ''),
(37, 1, 'label_styles_background-color', '', 'label_styles_background-color', ''),
(38, 1, 'label_styles_border', '', 'label_styles_border', ''),
(39, 1, 'label_styles_border-style', '', 'label_styles_border-style', ''),
(40, 1, 'label_styles_border-color', '', 'label_styles_border-color', ''),
(41, 1, 'label_styles_color', '', 'label_styles_color', ''),
(42, 1, 'label_styles_height', '', 'label_styles_height', ''),
(43, 1, 'label_styles_width', '', 'label_styles_width', ''),
(44, 1, 'label_styles_font-size', '', 'label_styles_font-size', ''),
(45, 1, 'label_styles_margin', '', 'label_styles_margin', ''),
(46, 1, 'label_styles_padding', '', 'label_styles_padding', ''),
(47, 1, 'label_styles_display', '', 'label_styles_display', ''),
(48, 1, 'label_styles_float', '', 'label_styles_float', ''),
(49, 1, 'label_styles_show_advanced_css', '0', 'label_styles_show_advanced_css', '0'),
(50, 1, 'label_styles_advanced', '', 'label_styles_advanced', ''),
(51, 1, 'element_styles_background-color', '', 'element_styles_background-color', ''),
(52, 1, 'element_styles_border', '', 'element_styles_border', ''),
(53, 1, 'element_styles_border-style', '', 'element_styles_border-style', ''),
(54, 1, 'element_styles_border-color', '', 'element_styles_border-color', ''),
(55, 1, 'element_styles_color', '', 'element_styles_color', ''),
(56, 1, 'element_styles_height', '', 'element_styles_height', ''),
(57, 1, 'element_styles_width', '', 'element_styles_width', ''),
(58, 1, 'element_styles_font-size', '', 'element_styles_font-size', ''),
(59, 1, 'element_styles_margin', '', 'element_styles_margin', ''),
(60, 1, 'element_styles_padding', '', 'element_styles_padding', ''),
(61, 1, 'element_styles_display', '', 'element_styles_display', ''),
(62, 1, 'element_styles_float', '', 'element_styles_float', ''),
(63, 1, 'element_styles_show_advanced_css', '0', 'element_styles_show_advanced_css', '0'),
(64, 1, 'element_styles_advanced', '', 'element_styles_advanced', ''),
(65, 1, 'cellcid', 'c3277', 'cellcid', 'c3277'),
(66, 2, 'label_pos', 'above', 'label_pos', 'above'),
(67, 2, 'required', '1', 'required', '1'),
(68, 2, 'order', '3', 'order', '3'),
(69, 2, 'placeholder', 'Your email Address *', 'placeholder', 'Your email Address *'),
(70, 2, 'default', '', 'default', ''),
(71, 2, 'wrapper_class', '', 'wrapper_class', ''),
(72, 2, 'element_class', '', 'element_class', ''),
(73, 2, 'objectType', 'Field', 'objectType', 'Field'),
(74, 2, 'objectDomain', 'fields', 'objectDomain', 'fields'),
(75, 2, 'editActive', '', 'editActive', ''),
(76, 2, 'container_class', '', 'container_class', ''),
(77, 2, 'admin_label', '', 'admin_label', ''),
(78, 2, 'help_text', '', 'help_text', ''),
(79, 2, 'desc_text', '', 'desc_text', ''),
(80, 2, 'wrap_styles_background-color', '', 'wrap_styles_background-color', ''),
(81, 2, 'wrap_styles_border', '', 'wrap_styles_border', ''),
(82, 2, 'wrap_styles_border-style', '', 'wrap_styles_border-style', ''),
(83, 2, 'wrap_styles_border-color', '', 'wrap_styles_border-color', ''),
(84, 2, 'wrap_styles_color', '', 'wrap_styles_color', ''),
(85, 2, 'wrap_styles_height', '', 'wrap_styles_height', ''),
(86, 2, 'wrap_styles_width', '', 'wrap_styles_width', ''),
(87, 2, 'wrap_styles_font-size', '', 'wrap_styles_font-size', ''),
(88, 2, 'wrap_styles_margin', '', 'wrap_styles_margin', ''),
(89, 2, 'wrap_styles_padding', '', 'wrap_styles_padding', ''),
(90, 2, 'wrap_styles_display', '', 'wrap_styles_display', ''),
(91, 2, 'wrap_styles_float', '', 'wrap_styles_float', ''),
(92, 2, 'wrap_styles_show_advanced_css', '0', 'wrap_styles_show_advanced_css', '0'),
(93, 2, 'wrap_styles_advanced', '', 'wrap_styles_advanced', ''),
(94, 2, 'label_styles_background-color', '', 'label_styles_background-color', ''),
(95, 2, 'label_styles_border', '', 'label_styles_border', ''),
(96, 2, 'label_styles_border-style', '', 'label_styles_border-style', ''),
(97, 2, 'label_styles_border-color', '', 'label_styles_border-color', ''),
(98, 2, 'label_styles_color', '', 'label_styles_color', ''),
(99, 2, 'label_styles_height', '', 'label_styles_height', ''),
(100, 2, 'label_styles_width', '', 'label_styles_width', '') ;
INSERT INTO `league_nf3_field_meta` ( `id`, `parent_id`, `key`, `value`, `meta_key`, `meta_value`) VALUES
(101, 2, 'label_styles_font-size', '', 'label_styles_font-size', ''),
(102, 2, 'label_styles_margin', '', 'label_styles_margin', ''),
(103, 2, 'label_styles_padding', '', 'label_styles_padding', ''),
(104, 2, 'label_styles_display', '', 'label_styles_display', ''),
(105, 2, 'label_styles_float', '', 'label_styles_float', ''),
(106, 2, 'label_styles_show_advanced_css', '0', 'label_styles_show_advanced_css', '0'),
(107, 2, 'label_styles_advanced', '', 'label_styles_advanced', ''),
(108, 2, 'element_styles_background-color', '', 'element_styles_background-color', ''),
(109, 2, 'element_styles_border', '', 'element_styles_border', ''),
(110, 2, 'element_styles_border-style', '', 'element_styles_border-style', ''),
(111, 2, 'element_styles_border-color', '', 'element_styles_border-color', ''),
(112, 2, 'element_styles_color', '', 'element_styles_color', ''),
(113, 2, 'element_styles_height', '', 'element_styles_height', ''),
(114, 2, 'element_styles_width', '', 'element_styles_width', ''),
(115, 2, 'element_styles_font-size', '', 'element_styles_font-size', ''),
(116, 2, 'element_styles_margin', '', 'element_styles_margin', ''),
(117, 2, 'element_styles_padding', '', 'element_styles_padding', ''),
(118, 2, 'element_styles_display', '', 'element_styles_display', ''),
(119, 2, 'element_styles_float', '', 'element_styles_float', ''),
(120, 2, 'element_styles_show_advanced_css', '0', 'element_styles_show_advanced_css', '0'),
(121, 2, 'element_styles_advanced', '', 'element_styles_advanced', ''),
(122, 2, 'cellcid', 'c3281', 'cellcid', 'c3281'),
(123, 3, 'label_pos', 'above', 'label_pos', 'above'),
(124, 3, 'required', '1', 'required', '1'),
(125, 3, 'order', '4', 'order', '4'),
(126, 3, 'placeholder', 'Let us know how we can help you', 'placeholder', 'Let us know how we can help you'),
(127, 3, 'default', '', 'default', ''),
(128, 3, 'wrapper_class', '', 'wrapper_class', ''),
(129, 3, 'element_class', '', 'element_class', ''),
(130, 3, 'objectType', 'Field', 'objectType', 'Field'),
(131, 3, 'objectDomain', 'fields', 'objectDomain', 'fields'),
(132, 3, 'editActive', '', 'editActive', ''),
(133, 3, 'container_class', '', 'container_class', ''),
(134, 3, 'input_limit', '', 'input_limit', ''),
(135, 3, 'input_limit_type', 'characters', 'input_limit_type', 'characters'),
(136, 3, 'input_limit_msg', 'Character(s) left', 'input_limit_msg', 'Character(s) left'),
(137, 3, 'manual_key', '', 'manual_key', ''),
(138, 3, 'disable_input', '', 'disable_input', ''),
(139, 3, 'admin_label', '', 'admin_label', ''),
(140, 3, 'help_text', '', 'help_text', ''),
(141, 3, 'desc_text', '', 'desc_text', ''),
(142, 3, 'disable_browser_autocomplete', '', 'disable_browser_autocomplete', ''),
(143, 3, 'textarea_rte', '', 'textarea_rte', ''),
(144, 3, 'disable_rte_mobile', '', 'disable_rte_mobile', ''),
(145, 3, 'textarea_media', '', 'textarea_media', ''),
(146, 3, 'wrap_styles_background-color', '', 'wrap_styles_background-color', ''),
(147, 3, 'wrap_styles_border', '', 'wrap_styles_border', ''),
(148, 3, 'wrap_styles_border-style', '', 'wrap_styles_border-style', ''),
(149, 3, 'wrap_styles_border-color', '', 'wrap_styles_border-color', ''),
(150, 3, 'wrap_styles_color', '', 'wrap_styles_color', ''),
(151, 3, 'wrap_styles_height', '', 'wrap_styles_height', ''),
(152, 3, 'wrap_styles_width', '', 'wrap_styles_width', ''),
(153, 3, 'wrap_styles_font-size', '', 'wrap_styles_font-size', ''),
(154, 3, 'wrap_styles_margin', '', 'wrap_styles_margin', ''),
(155, 3, 'wrap_styles_padding', '', 'wrap_styles_padding', ''),
(156, 3, 'wrap_styles_display', '', 'wrap_styles_display', ''),
(157, 3, 'wrap_styles_float', '', 'wrap_styles_float', ''),
(158, 3, 'wrap_styles_show_advanced_css', '0', 'wrap_styles_show_advanced_css', '0'),
(159, 3, 'wrap_styles_advanced', '', 'wrap_styles_advanced', ''),
(160, 3, 'label_styles_background-color', '', 'label_styles_background-color', ''),
(161, 3, 'label_styles_border', '', 'label_styles_border', ''),
(162, 3, 'label_styles_border-style', '', 'label_styles_border-style', ''),
(163, 3, 'label_styles_border-color', '', 'label_styles_border-color', ''),
(164, 3, 'label_styles_color', '', 'label_styles_color', ''),
(165, 3, 'label_styles_height', '', 'label_styles_height', ''),
(166, 3, 'label_styles_width', '', 'label_styles_width', ''),
(167, 3, 'label_styles_font-size', '', 'label_styles_font-size', ''),
(168, 3, 'label_styles_margin', '', 'label_styles_margin', ''),
(169, 3, 'label_styles_padding', '', 'label_styles_padding', ''),
(170, 3, 'label_styles_display', '', 'label_styles_display', ''),
(171, 3, 'label_styles_float', '', 'label_styles_float', ''),
(172, 3, 'label_styles_show_advanced_css', '0', 'label_styles_show_advanced_css', '0'),
(173, 3, 'label_styles_advanced', '', 'label_styles_advanced', ''),
(174, 3, 'element_styles_background-color', '', 'element_styles_background-color', ''),
(175, 3, 'element_styles_border', '', 'element_styles_border', ''),
(176, 3, 'element_styles_border-style', '', 'element_styles_border-style', ''),
(177, 3, 'element_styles_border-color', '', 'element_styles_border-color', ''),
(178, 3, 'element_styles_color', '', 'element_styles_color', ''),
(179, 3, 'element_styles_height', '', 'element_styles_height', ''),
(180, 3, 'element_styles_width', '', 'element_styles_width', ''),
(181, 3, 'element_styles_font-size', '', 'element_styles_font-size', ''),
(182, 3, 'element_styles_margin', '', 'element_styles_margin', ''),
(183, 3, 'element_styles_padding', '', 'element_styles_padding', ''),
(184, 3, 'element_styles_display', '', 'element_styles_display', ''),
(185, 3, 'element_styles_float', '', 'element_styles_float', ''),
(186, 3, 'element_styles_show_advanced_css', '0', 'element_styles_show_advanced_css', '0'),
(187, 3, 'element_styles_advanced', '', 'element_styles_advanced', ''),
(188, 3, 'cellcid', 'c3284', 'cellcid', 'c3284'),
(189, 4, 'processing_label', 'Processing', 'processing_label', 'Processing'),
(190, 4, 'order', '5', 'order', '5'),
(191, 4, 'objectType', 'Field', 'objectType', 'Field'),
(192, 4, 'objectDomain', 'fields', 'objectDomain', 'fields'),
(193, 4, 'editActive', '', 'editActive', ''),
(194, 4, 'container_class', '', 'container_class', ''),
(195, 4, 'element_class', '', 'element_class', ''),
(196, 4, 'wrap_styles_background-color', '', 'wrap_styles_background-color', ''),
(197, 4, 'wrap_styles_border', '', 'wrap_styles_border', ''),
(198, 4, 'wrap_styles_border-style', '', 'wrap_styles_border-style', ''),
(199, 4, 'wrap_styles_border-color', '', 'wrap_styles_border-color', ''),
(200, 4, 'wrap_styles_color', '', 'wrap_styles_color', '') ;
INSERT INTO `league_nf3_field_meta` ( `id`, `parent_id`, `key`, `value`, `meta_key`, `meta_value`) VALUES
(201, 4, 'wrap_styles_height', '', 'wrap_styles_height', ''),
(202, 4, 'wrap_styles_width', '', 'wrap_styles_width', ''),
(203, 4, 'wrap_styles_font-size', '', 'wrap_styles_font-size', ''),
(204, 4, 'wrap_styles_margin', '', 'wrap_styles_margin', ''),
(205, 4, 'wrap_styles_padding', '', 'wrap_styles_padding', ''),
(206, 4, 'wrap_styles_display', '', 'wrap_styles_display', ''),
(207, 4, 'wrap_styles_float', '', 'wrap_styles_float', ''),
(208, 4, 'wrap_styles_show_advanced_css', '0', 'wrap_styles_show_advanced_css', '0'),
(209, 4, 'wrap_styles_advanced', '', 'wrap_styles_advanced', ''),
(210, 4, 'label_styles_background-color', '', 'label_styles_background-color', ''),
(211, 4, 'label_styles_border', '', 'label_styles_border', ''),
(212, 4, 'label_styles_border-style', '', 'label_styles_border-style', ''),
(213, 4, 'label_styles_border-color', '', 'label_styles_border-color', ''),
(214, 4, 'label_styles_color', '', 'label_styles_color', ''),
(215, 4, 'label_styles_height', '', 'label_styles_height', ''),
(216, 4, 'label_styles_width', '', 'label_styles_width', ''),
(217, 4, 'label_styles_font-size', '', 'label_styles_font-size', ''),
(218, 4, 'label_styles_margin', '', 'label_styles_margin', ''),
(219, 4, 'label_styles_padding', '', 'label_styles_padding', ''),
(220, 4, 'label_styles_display', '', 'label_styles_display', ''),
(221, 4, 'label_styles_float', '', 'label_styles_float', ''),
(222, 4, 'label_styles_show_advanced_css', '0', 'label_styles_show_advanced_css', '0'),
(223, 4, 'label_styles_advanced', '', 'label_styles_advanced', ''),
(224, 4, 'element_styles_background-color', '', 'element_styles_background-color', ''),
(225, 4, 'element_styles_border', '', 'element_styles_border', ''),
(226, 4, 'element_styles_border-style', '', 'element_styles_border-style', ''),
(227, 4, 'element_styles_border-color', '', 'element_styles_border-color', ''),
(228, 4, 'element_styles_color', '', 'element_styles_color', ''),
(229, 4, 'element_styles_height', '', 'element_styles_height', ''),
(230, 4, 'element_styles_width', '', 'element_styles_width', ''),
(231, 4, 'element_styles_font-size', '', 'element_styles_font-size', ''),
(232, 4, 'element_styles_margin', '', 'element_styles_margin', ''),
(233, 4, 'element_styles_padding', '', 'element_styles_padding', ''),
(234, 4, 'element_styles_display', '', 'element_styles_display', ''),
(235, 4, 'element_styles_float', '', 'element_styles_float', ''),
(236, 4, 'element_styles_show_advanced_css', '0', 'element_styles_show_advanced_css', '0'),
(237, 4, 'element_styles_advanced', '', 'element_styles_advanced', ''),
(238, 4, 'submit_element_hover_styles_background-color', '', 'submit_element_hover_styles_background-color', ''),
(239, 4, 'submit_element_hover_styles_border', '', 'submit_element_hover_styles_border', ''),
(240, 4, 'submit_element_hover_styles_border-style', '', 'submit_element_hover_styles_border-style', ''),
(241, 4, 'submit_element_hover_styles_border-color', '', 'submit_element_hover_styles_border-color', ''),
(242, 4, 'submit_element_hover_styles_color', '', 'submit_element_hover_styles_color', ''),
(243, 4, 'submit_element_hover_styles_height', '', 'submit_element_hover_styles_height', ''),
(244, 4, 'submit_element_hover_styles_width', '', 'submit_element_hover_styles_width', ''),
(245, 4, 'submit_element_hover_styles_font-size', '', 'submit_element_hover_styles_font-size', ''),
(246, 4, 'submit_element_hover_styles_margin', '', 'submit_element_hover_styles_margin', ''),
(247, 4, 'submit_element_hover_styles_padding', '', 'submit_element_hover_styles_padding', ''),
(248, 4, 'submit_element_hover_styles_display', '', 'submit_element_hover_styles_display', ''),
(249, 4, 'submit_element_hover_styles_float', '', 'submit_element_hover_styles_float', ''),
(250, 4, 'submit_element_hover_styles_show_advanced_css', '0', 'submit_element_hover_styles_show_advanced_css', '0'),
(251, 4, 'submit_element_hover_styles_advanced', '', 'submit_element_hover_styles_advanced', ''),
(252, 4, 'cellcid', 'c3287', 'cellcid', 'c3287'),
(253, 1, 'field_label', 'Name', 'field_label', 'Name'),
(254, 1, 'field_key', 'name', 'field_key', 'name'),
(255, 2, 'field_label', 'Email', 'field_label', 'Email'),
(256, 2, 'field_key', 'email', 'field_key', 'email'),
(257, 3, 'field_label', 'Message', 'field_label', 'Message'),
(258, 3, 'field_key', 'message', 'field_key', 'message'),
(259, 4, 'field_label', 'Submit', 'field_label', 'Submit'),
(260, 4, 'field_key', 'submit', 'field_key', 'submit'),
(261, 1, 'label', 'Name', 'label', 'Name'),
(262, 1, 'key', 'name', 'key', 'name'),
(263, 1, 'type', 'textbox', 'type', 'textbox'),
(264, 1, 'created_at', '2020-07-02 18:34:30', 'created_at', '2020-07-02 18:34:30'),
(265, 1, 'custom_name_attribute', '', 'custom_name_attribute', ''),
(266, 1, 'personally_identifiable', '', 'personally_identifiable', ''),
(267, 1, 'value', '', 'value', ''),
(268, 1, 'drawerDisabled', '', 'drawerDisabled', ''),
(269, 5, 'editActive', '', 'editActive', ''),
(270, 5, 'order', '2', 'order', '2'),
(271, 5, 'label', 'Phone', 'label', 'Phone'),
(272, 5, 'type', 'phone', 'type', 'phone'),
(273, 5, 'key', 'phone_1594468934779', 'key', 'phone_1594468934779'),
(274, 5, 'label_pos', 'above', 'label_pos', 'above'),
(275, 5, 'required', '1', 'required', '1'),
(276, 5, 'default', '', 'default', ''),
(277, 5, 'placeholder', 'Your phone number *', 'placeholder', 'Your phone number *'),
(278, 5, 'container_class', '', 'container_class', ''),
(279, 5, 'element_class', '', 'element_class', ''),
(280, 5, 'input_limit', '', 'input_limit', ''),
(281, 5, 'input_limit_type', 'characters', 'input_limit_type', 'characters'),
(282, 5, 'input_limit_msg', 'Character(s) left', 'input_limit_msg', 'Character(s) left'),
(283, 5, 'manual_key', '', 'manual_key', ''),
(284, 5, 'admin_label', '', 'admin_label', ''),
(285, 5, 'help_text', '', 'help_text', ''),
(286, 5, 'mask', '', 'mask', ''),
(287, 5, 'custom_mask', '', 'custom_mask', ''),
(288, 5, 'custom_name_attribute', 'phone', 'custom_name_attribute', 'phone'),
(289, 5, 'personally_identifiable', '1', 'personally_identifiable', '1'),
(290, 5, 'value', '', 'value', ''),
(291, 2, 'label', 'Email', 'label', 'Email'),
(292, 2, 'key', 'email', 'key', 'email'),
(293, 2, 'type', 'email', 'type', 'email'),
(294, 2, 'created_at', '2020-07-02 18:34:30', 'created_at', '2020-07-02 18:34:30'),
(295, 2, 'custom_name_attribute', 'email', 'custom_name_attribute', 'email'),
(296, 2, 'personally_identifiable', '1', 'personally_identifiable', '1'),
(297, 2, 'value', '', 'value', ''),
(298, 3, 'label', 'Message', 'label', 'Message'),
(299, 3, 'key', 'message', 'key', 'message'),
(300, 3, 'type', 'textarea', 'type', 'textarea') ;
INSERT INTO `league_nf3_field_meta` ( `id`, `parent_id`, `key`, `value`, `meta_key`, `meta_value`) VALUES
(301, 3, 'created_at', '2020-07-02 18:34:30', 'created_at', '2020-07-02 18:34:30'),
(302, 3, 'value', '', 'value', ''),
(303, 4, 'label', 'Submit', 'label', 'Submit'),
(304, 4, 'key', 'submit', 'key', 'submit'),
(305, 4, 'type', 'submit', 'type', 'submit'),
(306, 4, 'created_at', '2020-07-02 18:34:30', 'created_at', '2020-07-02 18:34:30'),
(307, 3, 'drawerDisabled', '', 'drawerDisabled', '') ;

#
# End of data contents of table `league_nf3_field_meta`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_fields`
#

DROP TABLE IF EXISTS `league_nf3_fields`;


#
# Table structure of table `league_nf3_fields`
#

CREATE TABLE `league_nf3_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` longtext DEFAULT NULL,
  `key` longtext DEFAULT NULL,
  `type` longtext DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `field_label` longtext DEFAULT NULL,
  `field_key` longtext DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `required` bit(1) DEFAULT NULL,
  `default_value` longtext DEFAULT NULL,
  `label_pos` varchar(15) DEFAULT NULL,
  `personally_identifiable` bit(1) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_fields`
#
INSERT INTO `league_nf3_fields` ( `id`, `label`, `key`, `type`, `parent_id`, `created_at`, `updated_at`, `field_label`, `field_key`, `order`, `required`, `default_value`, `label_pos`, `personally_identifiable`) VALUES
(1, 'Name', 'name', 'textbox', 1, '2020-07-02 18:34:30', '2020-07-02 18:34:30', 'Name', 'name', 1, b'1', '', 'above', b'0'),
(2, 'Email', 'email', 'email', 1, '2020-07-02 18:34:30', '2020-07-02 18:34:30', 'Email', 'email', 3, b'1', '', 'above', b'1'),
(3, 'Message', 'message', 'textarea', 1, '2020-07-02 18:34:30', '2020-07-02 18:34:30', 'Message', 'message', 4, b'1', '', 'above', b'0'),
(4, 'Submit', 'submit', 'submit', 1, '2020-07-02 18:34:30', '2020-07-02 18:34:30', 'Submit', 'submit', 5, b'0', '', '', b'0'),
(5, 'Phone', 'phone_1594468934779', 'phone', 1, NULL, NULL, 'Phone', 'phone_1594468934779', 2, b'1', '', 'above', b'1') ;

#
# End of data contents of table `league_nf3_fields`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_form_meta`
#

DROP TABLE IF EXISTS `league_nf3_form_meta`;


#
# Table structure of table `league_nf3_form_meta`
#

CREATE TABLE `league_nf3_form_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `key` longtext NOT NULL,
  `value` longtext DEFAULT NULL,
  `meta_key` longtext DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_form_meta`
#
INSERT INTO `league_nf3_form_meta` ( `id`, `parent_id`, `key`, `value`, `meta_key`, `meta_value`) VALUES
(1, 1, 'key', '', 'key', ''),
(2, 1, 'created_at', '2020-07-02 18:34:30', 'created_at', '2020-07-02 18:34:30'),
(3, 1, 'default_label_pos', 'above', 'default_label_pos', 'above'),
(4, 1, 'conditions', 'a:0:{}', 'conditions', 'a:0:{}'),
(5, 1, 'objectType', 'Form Setting', 'objectType', 'Form Setting'),
(6, 1, 'editActive', '1', 'editActive', '1'),
(7, 1, 'show_title', '0', 'show_title', '0'),
(8, 1, 'clear_complete', '1', 'clear_complete', '1'),
(9, 1, 'hide_complete', '0', 'hide_complete', '0'),
(10, 1, 'wrapper_class', '', 'wrapper_class', ''),
(11, 1, 'element_class', '', 'element_class', ''),
(12, 1, 'add_submit', '1', 'add_submit', '1'),
(13, 1, 'logged_in', '', 'logged_in', ''),
(14, 1, 'not_logged_in_msg', '', 'not_logged_in_msg', ''),
(15, 1, 'sub_limit_number', '', 'sub_limit_number', ''),
(16, 1, 'sub_limit_msg', '', 'sub_limit_msg', ''),
(17, 1, 'calculations', 'a:0:{}', 'calculations', 'a:0:{}'),
(18, 1, 'formContentData', 'a:5:{i:0;s:4:"name";i:1;s:19:"phone_1594468934779";i:2;s:5:"email";i:3;s:7:"message";i:4;s:6:"submit";}', 'formContentData', 'a:5:{i:0;s:4:"name";i:1;s:19:"phone_1594468934779";i:2;s:5:"email";i:3;s:7:"message";i:4;s:6:"submit";}'),
(19, 1, 'container_styles_background-color', '', 'container_styles_background-color', ''),
(20, 1, 'container_styles_border', '', 'container_styles_border', ''),
(21, 1, 'container_styles_border-style', '', 'container_styles_border-style', ''),
(22, 1, 'container_styles_border-color', '', 'container_styles_border-color', ''),
(23, 1, 'container_styles_color', '', 'container_styles_color', ''),
(24, 1, 'container_styles_height', '', 'container_styles_height', ''),
(25, 1, 'container_styles_width', '', 'container_styles_width', ''),
(26, 1, 'container_styles_font-size', '', 'container_styles_font-size', ''),
(27, 1, 'container_styles_margin', '', 'container_styles_margin', ''),
(28, 1, 'container_styles_padding', '', 'container_styles_padding', ''),
(29, 1, 'container_styles_display', '', 'container_styles_display', ''),
(30, 1, 'container_styles_float', '', 'container_styles_float', ''),
(31, 1, 'container_styles_show_advanced_css', '0', 'container_styles_show_advanced_css', '0'),
(32, 1, 'container_styles_advanced', '', 'container_styles_advanced', ''),
(33, 1, 'title_styles_background-color', '', 'title_styles_background-color', ''),
(34, 1, 'title_styles_border', '', 'title_styles_border', ''),
(35, 1, 'title_styles_border-style', '', 'title_styles_border-style', ''),
(36, 1, 'title_styles_border-color', '', 'title_styles_border-color', ''),
(37, 1, 'title_styles_color', '', 'title_styles_color', ''),
(38, 1, 'title_styles_height', '', 'title_styles_height', ''),
(39, 1, 'title_styles_width', '', 'title_styles_width', ''),
(40, 1, 'title_styles_font-size', '', 'title_styles_font-size', ''),
(41, 1, 'title_styles_margin', '', 'title_styles_margin', ''),
(42, 1, 'title_styles_padding', '', 'title_styles_padding', ''),
(43, 1, 'title_styles_display', '', 'title_styles_display', ''),
(44, 1, 'title_styles_float', '', 'title_styles_float', ''),
(45, 1, 'title_styles_show_advanced_css', '0', 'title_styles_show_advanced_css', '0'),
(46, 1, 'title_styles_advanced', '', 'title_styles_advanced', ''),
(47, 1, 'row_styles_background-color', '', 'row_styles_background-color', ''),
(48, 1, 'row_styles_border', '', 'row_styles_border', ''),
(49, 1, 'row_styles_border-style', '', 'row_styles_border-style', ''),
(50, 1, 'row_styles_border-color', '', 'row_styles_border-color', ''),
(51, 1, 'row_styles_color', '', 'row_styles_color', ''),
(52, 1, 'row_styles_height', '', 'row_styles_height', ''),
(53, 1, 'row_styles_width', '', 'row_styles_width', ''),
(54, 1, 'row_styles_font-size', '', 'row_styles_font-size', ''),
(55, 1, 'row_styles_margin', '', 'row_styles_margin', ''),
(56, 1, 'row_styles_padding', '', 'row_styles_padding', ''),
(57, 1, 'row_styles_display', '', 'row_styles_display', ''),
(58, 1, 'row_styles_show_advanced_css', '0', 'row_styles_show_advanced_css', '0'),
(59, 1, 'row_styles_advanced', '', 'row_styles_advanced', ''),
(60, 1, 'row-odd_styles_background-color', '', 'row-odd_styles_background-color', ''),
(61, 1, 'row-odd_styles_border', '', 'row-odd_styles_border', ''),
(62, 1, 'row-odd_styles_border-style', '', 'row-odd_styles_border-style', ''),
(63, 1, 'row-odd_styles_border-color', '', 'row-odd_styles_border-color', ''),
(64, 1, 'row-odd_styles_color', '', 'row-odd_styles_color', ''),
(65, 1, 'row-odd_styles_height', '', 'row-odd_styles_height', ''),
(66, 1, 'row-odd_styles_width', '', 'row-odd_styles_width', ''),
(67, 1, 'row-odd_styles_font-size', '', 'row-odd_styles_font-size', ''),
(68, 1, 'row-odd_styles_margin', '', 'row-odd_styles_margin', ''),
(69, 1, 'row-odd_styles_padding', '', 'row-odd_styles_padding', ''),
(70, 1, 'row-odd_styles_display', '', 'row-odd_styles_display', ''),
(71, 1, 'row-odd_styles_show_advanced_css', '0', 'row-odd_styles_show_advanced_css', '0'),
(72, 1, 'row-odd_styles_advanced', '', 'row-odd_styles_advanced', ''),
(73, 1, 'success-msg_styles_background-color', '', 'success-msg_styles_background-color', ''),
(74, 1, 'success-msg_styles_border', '', 'success-msg_styles_border', ''),
(75, 1, 'success-msg_styles_border-style', '', 'success-msg_styles_border-style', ''),
(76, 1, 'success-msg_styles_border-color', '', 'success-msg_styles_border-color', ''),
(77, 1, 'success-msg_styles_color', '', 'success-msg_styles_color', ''),
(78, 1, 'success-msg_styles_height', '', 'success-msg_styles_height', ''),
(79, 1, 'success-msg_styles_width', '', 'success-msg_styles_width', ''),
(80, 1, 'success-msg_styles_font-size', '', 'success-msg_styles_font-size', ''),
(81, 1, 'success-msg_styles_margin', '', 'success-msg_styles_margin', ''),
(82, 1, 'success-msg_styles_padding', '', 'success-msg_styles_padding', ''),
(83, 1, 'success-msg_styles_display', '', 'success-msg_styles_display', ''),
(84, 1, 'success-msg_styles_show_advanced_css', '0', 'success-msg_styles_show_advanced_css', '0'),
(85, 1, 'success-msg_styles_advanced', '', 'success-msg_styles_advanced', ''),
(86, 1, 'error_msg_styles_background-color', '', 'error_msg_styles_background-color', ''),
(87, 1, 'error_msg_styles_border', '', 'error_msg_styles_border', ''),
(88, 1, 'error_msg_styles_border-style', '', 'error_msg_styles_border-style', ''),
(89, 1, 'error_msg_styles_border-color', '', 'error_msg_styles_border-color', ''),
(90, 1, 'error_msg_styles_color', '', 'error_msg_styles_color', ''),
(91, 1, 'error_msg_styles_height', '', 'error_msg_styles_height', ''),
(92, 1, 'error_msg_styles_width', '', 'error_msg_styles_width', ''),
(93, 1, 'error_msg_styles_font-size', '', 'error_msg_styles_font-size', ''),
(94, 1, 'error_msg_styles_margin', '', 'error_msg_styles_margin', ''),
(95, 1, 'error_msg_styles_padding', '', 'error_msg_styles_padding', ''),
(96, 1, 'error_msg_styles_display', '', 'error_msg_styles_display', ''),
(97, 1, 'error_msg_styles_show_advanced_css', '0', 'error_msg_styles_show_advanced_css', '0'),
(98, 1, 'error_msg_styles_advanced', '', 'error_msg_styles_advanced', ''),
(99, 1, 'allow_public_link', '0', 'allow_public_link', '0'),
(100, 1, 'embed_form', '', 'embed_form', '') ;
INSERT INTO `league_nf3_form_meta` ( `id`, `parent_id`, `key`, `value`, `meta_key`, `meta_value`) VALUES
(101, 1, 'currency', '', 'currency', ''),
(102, 1, 'unique_field_error', 'A form with this value has already been submitted.', 'unique_field_error', 'A form with this value has already been submitted.'),
(103, 1, 'changeEmailErrorMsg', '', 'changeEmailErrorMsg', ''),
(104, 1, 'changeDateErrorMsg', '', 'changeDateErrorMsg', ''),
(105, 1, 'confirmFieldErrorMsg', '', 'confirmFieldErrorMsg', ''),
(106, 1, 'fieldNumberNumMinError', '', 'fieldNumberNumMinError', ''),
(107, 1, 'fieldNumberNumMaxError', '', 'fieldNumberNumMaxError', ''),
(108, 1, 'fieldNumberIncrementBy', '', 'fieldNumberIncrementBy', ''),
(109, 1, 'formErrorsCorrectErrors', '', 'formErrorsCorrectErrors', ''),
(110, 1, 'validateRequiredField', '', 'validateRequiredField', ''),
(111, 1, 'honeypotHoneypotError', '', 'honeypotHoneypotError', ''),
(112, 1, 'fieldsMarkedRequired', '', 'fieldsMarkedRequired', ''),
(113, 1, 'drawerDisabled', '', 'drawerDisabled', '') ;

#
# End of data contents of table `league_nf3_form_meta`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_forms`
#

DROP TABLE IF EXISTS `league_nf3_forms`;


#
# Table structure of table `league_nf3_forms`
#

CREATE TABLE `league_nf3_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` longtext DEFAULT NULL,
  `key` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `views` int(11) DEFAULT NULL,
  `subs` int(11) DEFAULT NULL,
  `form_title` longtext DEFAULT NULL,
  `default_label_pos` varchar(15) DEFAULT NULL,
  `show_title` bit(1) DEFAULT NULL,
  `clear_complete` bit(1) DEFAULT NULL,
  `hide_complete` bit(1) DEFAULT NULL,
  `logged_in` bit(1) DEFAULT NULL,
  `seq_num` int(11) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_forms`
#
INSERT INTO `league_nf3_forms` ( `id`, `title`, `key`, `created_at`, `updated_at`, `views`, `subs`, `form_title`, `default_label_pos`, `show_title`, `clear_complete`, `hide_complete`, `logged_in`, `seq_num`) VALUES
(1, 'Contact Us', NULL, '2020-07-02 18:34:30', '2020-07-02 18:34:30', NULL, NULL, 'Contact Us', 'above', b'0', b'1', b'0', b'0', NULL) ;

#
# End of data contents of table `league_nf3_forms`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_object_meta`
#

DROP TABLE IF EXISTS `league_nf3_object_meta`;


#
# Table structure of table `league_nf3_object_meta`
#

CREATE TABLE `league_nf3_object_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `key` longtext NOT NULL,
  `value` longtext DEFAULT NULL,
  `meta_key` longtext DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_object_meta`
#

#
# End of data contents of table `league_nf3_object_meta`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_objects`
#

DROP TABLE IF EXISTS `league_nf3_objects`;


#
# Table structure of table `league_nf3_objects`
#

CREATE TABLE `league_nf3_objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` longtext DEFAULT NULL,
  `title` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `object_title` longtext DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_objects`
#

#
# End of data contents of table `league_nf3_objects`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_relationships`
#

DROP TABLE IF EXISTS `league_nf3_relationships`;


#
# Table structure of table `league_nf3_relationships`
#

CREATE TABLE `league_nf3_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `child_type` longtext NOT NULL,
  `parent_id` int(11) NOT NULL,
  `parent_type` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_relationships`
#

#
# End of data contents of table `league_nf3_relationships`
# --------------------------------------------------------



#
# Delete any existing table `league_nf3_upgrades`
#

DROP TABLE IF EXISTS `league_nf3_upgrades`;


#
# Table structure of table `league_nf3_upgrades`
#

CREATE TABLE `league_nf3_upgrades` (
  `id` int(11) NOT NULL,
  `cache` longtext DEFAULT NULL,
  `stage` int(11) NOT NULL DEFAULT 0,
  `maintenance` bit(1) DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


#
# Data contents of table `league_nf3_upgrades`
#
INSERT INTO `league_nf3_upgrades` ( `id`, `cache`, `stage`, `maintenance`) VALUES
(1, 'a:7:{s:2:"id";i:1;s:20:"show_publish_options";b:0;s:6:"fields";a:5:{i:0;a:2:{s:8:"settings";a:73:{s:10:"objectType";s:5:"Field";s:12:"objectDomain";s:6:"fields";s:10:"editActive";b:0;s:5:"order";i:1;s:5:"label";s:4:"Name";s:3:"key";s:4:"name";s:4:"type";s:7:"textbox";s:10:"created_at";s:19:"2020-07-02 18:34:30";s:9:"label_pos";s:5:"above";s:8:"required";s:1:"1";s:11:"placeholder";s:11:"Your name *";s:7:"default";s:0:"";s:13:"wrapper_class";s:0:"";s:13:"element_class";s:0:"";s:15:"container_class";s:0:"";s:11:"input_limit";s:0:"";s:16:"input_limit_type";s:10:"characters";s:15:"input_limit_msg";s:17:"Character(s) left";s:10:"manual_key";s:0:"";s:13:"disable_input";s:0:"";s:11:"admin_label";s:0:"";s:9:"help_text";s:0:"";s:9:"desc_text";s:0:"";s:28:"disable_browser_autocomplete";s:0:"";s:4:"mask";s:0:"";s:11:"custom_mask";s:0:"";s:28:"wrap_styles_background-color";s:0:"";s:18:"wrap_styles_border";s:0:"";s:24:"wrap_styles_border-style";s:0:"";s:24:"wrap_styles_border-color";s:0:"";s:17:"wrap_styles_color";s:0:"";s:18:"wrap_styles_height";s:0:"";s:17:"wrap_styles_width";s:0:"";s:21:"wrap_styles_font-size";s:0:"";s:18:"wrap_styles_margin";s:0:"";s:19:"wrap_styles_padding";s:0:"";s:19:"wrap_styles_display";s:0:"";s:17:"wrap_styles_float";s:0:"";s:29:"wrap_styles_show_advanced_css";s:1:"0";s:20:"wrap_styles_advanced";s:0:"";s:29:"label_styles_background-color";s:0:"";s:19:"label_styles_border";s:0:"";s:25:"label_styles_border-style";s:0:"";s:25:"label_styles_border-color";s:0:"";s:18:"label_styles_color";s:0:"";s:19:"label_styles_height";s:0:"";s:18:"label_styles_width";s:0:"";s:22:"label_styles_font-size";s:0:"";s:19:"label_styles_margin";s:0:"";s:20:"label_styles_padding";s:0:"";s:20:"label_styles_display";s:0:"";s:18:"label_styles_float";s:0:"";s:30:"label_styles_show_advanced_css";s:1:"0";s:21:"label_styles_advanced";s:0:"";s:31:"element_styles_background-color";s:0:"";s:21:"element_styles_border";s:0:"";s:27:"element_styles_border-style";s:0:"";s:27:"element_styles_border-color";s:0:"";s:20:"element_styles_color";s:0:"";s:21:"element_styles_height";s:0:"";s:20:"element_styles_width";s:0:"";s:24:"element_styles_font-size";s:0:"";s:21:"element_styles_margin";s:0:"";s:22:"element_styles_padding";s:0:"";s:22:"element_styles_display";s:0:"";s:20:"element_styles_float";s:0:"";s:32:"element_styles_show_advanced_css";s:1:"0";s:23:"element_styles_advanced";s:0:"";s:7:"cellcid";s:5:"c3277";s:21:"custom_name_attribute";s:0:"";s:23:"personally_identifiable";s:0:"";s:5:"value";s:0:"";s:14:"drawerDisabled";b:0;}s:2:"id";i:1;}i:1;a:2:{s:8:"settings";a:24:{s:10:"objectType";s:5:"Field";s:12:"objectDomain";s:6:"fields";s:10:"editActive";b:0;s:5:"order";i:2;s:5:"label";s:5:"Phone";s:4:"type";s:5:"phone";s:3:"key";s:19:"phone_1594468934779";s:9:"label_pos";s:5:"above";s:8:"required";i:1;s:7:"default";s:0:"";s:11:"placeholder";s:19:"Your phone number *";s:15:"container_class";s:0:"";s:13:"element_class";s:0:"";s:11:"input_limit";s:0:"";s:16:"input_limit_type";s:10:"characters";s:15:"input_limit_msg";s:17:"Character(s) left";s:10:"manual_key";b:0;s:11:"admin_label";s:0:"";s:9:"help_text";s:0:"";s:4:"mask";s:0:"";s:11:"custom_mask";s:0:"";s:21:"custom_name_attribute";s:5:"phone";s:23:"personally_identifiable";s:1:"1";s:5:"value";s:0:"";}s:2:"id";s:1:"5";}i:2;a:2:{s:8:"settings";a:64:{s:10:"objectType";s:5:"Field";s:12:"objectDomain";s:6:"fields";s:10:"editActive";b:0;s:5:"order";i:3;s:5:"label";s:5:"Email";s:3:"key";s:5:"email";s:4:"type";s:5:"email";s:10:"created_at";s:19:"2020-07-02 18:34:30";s:9:"label_pos";s:5:"above";s:8:"required";s:1:"1";s:11:"placeholder";s:20:"Your email Address *";s:7:"default";s:0:"";s:13:"wrapper_class";s:0:"";s:13:"element_class";s:0:"";s:15:"container_class";s:0:"";s:11:"admin_label";s:0:"";s:9:"help_text";s:0:"";s:9:"desc_text";s:0:"";s:28:"wrap_styles_background-color";s:0:"";s:18:"wrap_styles_border";s:0:"";s:24:"wrap_styles_border-style";s:0:"";s:24:"wrap_styles_border-color";s:0:"";s:17:"wrap_styles_color";s:0:"";s:18:"wrap_styles_height";s:0:"";s:17:"wrap_styles_width";s:0:"";s:21:"wrap_styles_font-size";s:0:"";s:18:"wrap_styles_margin";s:0:"";s:19:"wrap_styles_padding";s:0:"";s:19:"wrap_styles_display";s:0:"";s:17:"wrap_styles_float";s:0:"";s:29:"wrap_styles_show_advanced_css";s:1:"0";s:20:"wrap_styles_advanced";s:0:"";s:29:"label_styles_background-color";s:0:"";s:19:"label_styles_border";s:0:"";s:25:"label_styles_border-style";s:0:"";s:25:"label_styles_border-color";s:0:"";s:18:"label_styles_color";s:0:"";s:19:"label_styles_height";s:0:"";s:18:"label_styles_width";s:0:"";s:22:"label_styles_font-size";s:0:"";s:19:"label_styles_margin";s:0:"";s:20:"label_styles_padding";s:0:"";s:20:"label_styles_display";s:0:"";s:18:"label_styles_float";s:0:"";s:30:"label_styles_show_advanced_css";s:1:"0";s:21:"label_styles_advanced";s:0:"";s:31:"element_styles_background-color";s:0:"";s:21:"element_styles_border";s:0:"";s:27:"element_styles_border-style";s:0:"";s:27:"element_styles_border-color";s:0:"";s:20:"element_styles_color";s:0:"";s:21:"element_styles_height";s:0:"";s:20:"element_styles_width";s:0:"";s:24:"element_styles_font-size";s:0:"";s:21:"element_styles_margin";s:0:"";s:22:"element_styles_padding";s:0:"";s:22:"element_styles_display";s:0:"";s:20:"element_styles_float";s:0:"";s:32:"element_styles_show_advanced_css";s:1:"0";s:23:"element_styles_advanced";s:0:"";s:7:"cellcid";s:5:"c3281";s:21:"custom_name_attribute";s:5:"email";s:23:"personally_identifiable";s:1:"1";s:5:"value";s:0:"";}s:2:"id";i:2;}i:3;a:2:{s:8:"settings";a:72:{s:10:"objectType";s:5:"Field";s:12:"objectDomain";s:6:"fields";s:10:"editActive";b:0;s:5:"order";i:4;s:5:"label";s:7:"Message";s:3:"key";s:7:"message";s:4:"type";s:8:"textarea";s:10:"created_at";s:19:"2020-07-02 18:34:30";s:9:"label_pos";s:5:"above";s:8:"required";s:1:"1";s:11:"placeholder";s:31:"Let us know how we can help you";s:7:"default";s:0:"";s:13:"wrapper_class";s:0:"";s:13:"element_class";s:0:"";s:15:"container_class";s:0:"";s:11:"input_limit";s:0:"";s:16:"input_limit_type";s:10:"characters";s:15:"input_limit_msg";s:17:"Character(s) left";s:10:"manual_key";s:0:"";s:13:"disable_input";s:0:"";s:11:"admin_label";s:0:"";s:9:"help_text";s:0:"";s:9:"desc_text";s:0:"";s:28:"disable_browser_autocomplete";s:0:"";s:12:"textarea_rte";s:0:"";s:18:"disable_rte_mobile";s:0:"";s:14:"textarea_media";s:0:"";s:28:"wrap_styles_background-color";s:0:"";s:18:"wrap_styles_border";s:0:"";s:24:"wrap_styles_border-style";s:0:"";s:24:"wrap_styles_border-color";s:0:"";s:17:"wrap_styles_color";s:0:"";s:18:"wrap_styles_height";s:0:"";s:17:"wrap_styles_width";s:0:"";s:21:"wrap_styles_font-size";s:0:"";s:18:"wrap_styles_margin";s:0:"";s:19:"wrap_styles_padding";s:0:"";s:19:"wrap_styles_display";s:0:"";s:17:"wrap_styles_float";s:0:"";s:29:"wrap_styles_show_advanced_css";s:1:"0";s:20:"wrap_styles_advanced";s:0:"";s:29:"label_styles_background-color";s:0:"";s:19:"label_styles_border";s:0:"";s:25:"label_styles_border-style";s:0:"";s:25:"label_styles_border-color";s:0:"";s:18:"label_styles_color";s:0:"";s:19:"label_styles_height";s:0:"";s:18:"label_styles_width";s:0:"";s:22:"label_styles_font-size";s:0:"";s:19:"label_styles_margin";s:0:"";s:20:"label_styles_padding";s:0:"";s:20:"label_styles_display";s:0:"";s:18:"label_styles_float";s:0:"";s:30:"label_styles_show_advanced_css";s:1:"0";s:21:"label_styles_advanced";s:0:"";s:31:"element_styles_background-color";s:0:"";s:21:"element_styles_border";s:0:"";s:27:"element_styles_border-style";s:0:"";s:27:"element_styles_border-color";s:0:"";s:20:"element_styles_color";s:0:"";s:21:"element_styles_height";s:0:"";s:20:"element_styles_width";s:0:"";s:24:"element_styles_font-size";s:0:"";s:21:"element_styles_margin";s:0:"";s:22:"element_styles_padding";s:0:"";s:22:"element_styles_display";s:0:"";s:20:"element_styles_float";s:0:"";s:32:"element_styles_show_advanced_css";s:1:"0";s:23:"element_styles_advanced";s:0:"";s:7:"cellcid";s:5:"c3284";s:5:"value";s:0:"";s:14:"drawerDisabled";b:0;}s:2:"id";i:3;}i:4;a:2:{s:8:"settings";a:68:{s:10:"objectType";s:5:"Field";s:12:"objectDomain";s:6:"fields";s:10:"editActive";b:0;s:5:"order";i:5;s:5:"label";s:6:"Submit";s:3:"key";s:6:"submit";s:4:"type";s:6:"submit";s:10:"created_at";s:19:"2020-07-02 18:34:30";s:16:"processing_label";s:10:"Processing";s:15:"container_class";s:0:"";s:13:"element_class";s:0:"";s:28:"wrap_styles_background-color";s:0:"";s:18:"wrap_styles_border";s:0:"";s:24:"wrap_styles_border-style";s:0:"";s:24:"wrap_styles_border-color";s:0:"";s:17:"wrap_styles_color";s:0:"";s:18:"wrap_styles_height";s:0:"";s:17:"wrap_styles_width";s:0:"";s:21:"wrap_styles_font-size";s:0:"";s:18:"wrap_styles_margin";s:0:"";s:19:"wrap_styles_padding";s:0:"";s:19:"wrap_styles_display";s:0:"";s:17:"wrap_styles_float";s:0:"";s:29:"wrap_styles_show_advanced_css";s:1:"0";s:20:"wrap_styles_advanced";s:0:"";s:29:"label_styles_background-color";s:0:"";s:19:"label_styles_border";s:0:"";s:25:"label_styles_border-style";s:0:"";s:25:"label_styles_border-color";s:0:"";s:18:"label_styles_color";s:0:"";s:19:"label_styles_height";s:0:"";s:18:"label_styles_width";s:0:"";s:22:"label_styles_font-size";s:0:"";s:19:"label_styles_margin";s:0:"";s:20:"label_styles_padding";s:0:"";s:20:"label_styles_display";s:0:"";s:18:"label_styles_float";s:0:"";s:30:"label_styles_show_advanced_css";s:1:"0";s:21:"label_styles_advanced";s:0:"";s:31:"element_styles_background-color";s:0:"";s:21:"element_styles_border";s:0:"";s:27:"element_styles_border-style";s:0:"";s:27:"element_styles_border-color";s:0:"";s:20:"element_styles_color";s:0:"";s:21:"element_styles_height";s:0:"";s:20:"element_styles_width";s:0:"";s:24:"element_styles_font-size";s:0:"";s:21:"element_styles_margin";s:0:"";s:22:"element_styles_padding";s:0:"";s:22:"element_styles_display";s:0:"";s:20:"element_styles_float";s:0:"";s:32:"element_styles_show_advanced_css";s:1:"0";s:23:"element_styles_advanced";s:0:"";s:44:"submit_element_hover_styles_background-color";s:0:"";s:34:"submit_element_hover_styles_border";s:0:"";s:40:"submit_element_hover_styles_border-style";s:0:"";s:40:"submit_element_hover_styles_border-color";s:0:"";s:33:"submit_element_hover_styles_color";s:0:"";s:34:"submit_element_hover_styles_height";s:0:"";s:33:"submit_element_hover_styles_width";s:0:"";s:37:"submit_element_hover_styles_font-size";s:0:"";s:34:"submit_element_hover_styles_margin";s:0:"";s:35:"submit_element_hover_styles_padding";s:0:"";s:35:"submit_element_hover_styles_display";s:0:"";s:33:"submit_element_hover_styles_float";s:0:"";s:45:"submit_element_hover_styles_show_advanced_css";s:1:"0";s:36:"submit_element_hover_styles_advanced";s:0:"";s:7:"cellcid";s:5:"c3287";}s:2:"id";i:4;}}s:7:"actions";a:4:{i:0;a:2:{s:8:"settings";a:30:{s:10:"objectType";s:6:"Action";s:12:"objectDomain";s:7:"actions";s:10:"editActive";b:0;s:5:"title";s:0:"";s:3:"key";s:0:"";s:4:"type";s:4:"save";s:6:"active";s:1:"1";s:10:"created_at";s:19:"2020-07-02 18:34:31";s:5:"label";s:16:"Store Submission";s:10:"conditions";a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}s:16:"payment_gateways";s:0:"";s:13:"payment_total";s:1:"0";s:3:"tag";s:0:"";s:2:"to";s:16:"{wp:admin_email}";s:13:"email_subject";s:22:"Ninja Forms Submission";s:13:"email_message";s:14:"{fields_table}";s:9:"from_name";s:0:"";s:12:"from_address";s:0:"";s:8:"reply_to";s:0:"";s:12:"email_format";s:4:"html";s:2:"cc";s:0:"";s:3:"bcc";s:0:"";s:12:"redirect_url";s:0:"";s:19:"email_message_plain";s:0:"";s:7:"message";s:170:"This action adds users to WordPress&#039; personal data export tool, allowing admins to comply with the GDPR and other privacy regulations from the site&#039;s front end.";s:15:"submitter_email";s:0:"";s:18:"fields-save-toggle";s:8:"save_all";s:16:"exception_fields";a:0:{}s:18:"set_subs_to_expire";s:1:"0";s:16:"subs_expire_time";s:2:"90";}s:2:"id";i:1;}i:1;a:2:{s:8:"settings";a:25:{s:10:"objectType";s:6:"Action";s:12:"objectDomain";s:7:"actions";s:10:"editActive";b:0;s:5:"title";s:0:"";s:3:"key";s:0:"";s:4:"type";s:5:"email";s:6:"active";s:1:"1";s:10:"created_at";s:19:"2020-07-02 18:34:31";s:5:"label";s:18:"Email Confirmation";s:2:"to";s:13:"{field:email}";s:7:"subject";s:24:"This is an email action.";s:7:"message";s:19:"Hello, Ninja Forms!";s:10:"conditions";a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:0:{}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}s:16:"payment_gateways";s:0:"";s:13:"payment_total";s:1:"0";s:3:"tag";s:0:"";s:13:"email_subject";s:24:"Submission Confirmation ";s:13:"email_message";s:29:"<p>{all_fields_table}<br></p>";s:9:"from_name";s:0:"";s:12:"from_address";s:0:"";s:8:"reply_to";s:0:"";s:12:"email_format";s:4:"html";s:2:"cc";s:0:"";s:3:"bcc";s:0:"";s:19:"email_message_plain";s:0:"";}s:2:"id";i:2;}i:2;a:2:{s:8:"settings";a:25:{s:10:"objectType";s:6:"Action";s:12:"objectDomain";s:7:"actions";s:10:"editActive";b:0;s:5:"title";s:0:"";s:3:"key";s:0:"";s:4:"type";s:5:"email";s:6:"active";s:1:"1";s:10:"created_at";s:19:"2020-07-02 18:34:31";s:5:"label";s:18:"Email Notification";s:10:"conditions";a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}s:16:"payment_gateways";s:0:"";s:13:"payment_total";s:1:"0";s:3:"tag";s:0:"";s:2:"to";s:20:"{system:admin_email}";s:13:"email_subject";s:29:"New message from {field:name}";s:13:"email_message";s:60:"<p>{field:message}</p><p>-{field:name} ( {field:email} )</p>";s:9:"from_name";s:0:"";s:12:"from_address";s:0:"";s:8:"reply_to";s:13:"{field:email}";s:12:"email_format";s:4:"html";s:2:"cc";s:0:"";s:3:"bcc";s:0:"";s:10:"attach_csv";s:1:"0";s:19:"email_message_plain";s:0:"";s:7:"message";s:170:"This action adds users to WordPress&#039; personal data delete tool, allowing admins to comply with the GDPR and other privacy regulations from the site&#039;s front end.";}s:2:"id";i:3;}i:3;a:2:{s:8:"settings";a:31:{s:10:"objectType";s:6:"Action";s:12:"objectDomain";s:7:"actions";s:10:"editActive";b:0;s:5:"title";s:0:"";s:3:"key";s:0:"";s:4:"type";s:14:"successmessage";s:6:"active";s:1:"1";s:10:"created_at";s:19:"2020-07-02 18:34:31";s:5:"label";s:15:"Success Message";s:7:"message";s:47:"Thank you {field:name} for filling out my form!";s:10:"conditions";a:6:{s:9:"collapsed";s:0:"";s:7:"process";s:1:"1";s:9:"connector";s:3:"all";s:4:"when";a:1:{i:0;a:6:{s:9:"connector";s:3:"AND";s:3:"key";s:0:"";s:10:"comparator";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"when";}}s:4:"then";a:1:{i:0;a:5:{s:3:"key";s:0:"";s:7:"trigger";s:0:"";s:5:"value";s:0:"";s:4:"type";s:5:"field";s:9:"modelType";s:4:"then";}}s:4:"else";a:0:{}}s:16:"payment_gateways";s:0:"";s:13:"payment_total";s:1:"0";s:3:"tag";s:0:"";s:2:"to";s:16:"{wp:admin_email}";s:13:"email_subject";s:22:"Ninja Forms Submission";s:13:"email_message";s:14:"{fields_table}";s:9:"from_name";s:0:"";s:12:"from_address";s:0:"";s:8:"reply_to";s:0:"";s:12:"email_format";s:4:"html";s:2:"cc";s:0:"";s:3:"bcc";s:0:"";s:12:"redirect_url";s:0:"";s:11:"success_msg";s:89:"<p>Form submitted successfully.</p><p>A confirmation email was sent to {field:email}.</p>";s:19:"email_message_plain";s:0:"";s:15:"submitter_email";s:0:"";s:18:"fields-save-toggle";s:8:"save_all";s:16:"exception_fields";a:0:{}s:18:"set_subs_to_expire";s:1:"0";s:16:"subs_expire_time";s:2:"90";}s:2:"id";i:4;}}s:8:"settings";a:114:{s:10:"objectType";s:12:"Form Setting";s:10:"editActive";b:1;s:5:"title";s:10:"Contact Us";s:3:"key";s:0:"";s:10:"created_at";s:19:"2020-07-02 18:34:30";s:17:"default_label_pos";s:5:"above";s:10:"conditions";a:0:{}s:10:"show_title";i:0;s:14:"clear_complete";s:1:"1";s:13:"hide_complete";i:0;s:13:"wrapper_class";s:0:"";s:13:"element_class";s:0:"";s:10:"add_submit";s:1:"1";s:9:"logged_in";s:0:"";s:17:"not_logged_in_msg";s:0:"";s:16:"sub_limit_number";s:0:"";s:13:"sub_limit_msg";s:0:"";s:12:"calculations";a:0:{}s:15:"formContentData";a:5:{i:0;s:4:"name";i:1;s:19:"phone_1594468934779";i:2;s:5:"email";i:3;s:7:"message";i:4;s:6:"submit";}s:33:"container_styles_background-color";s:0:"";s:23:"container_styles_border";s:0:"";s:29:"container_styles_border-style";s:0:"";s:29:"container_styles_border-color";s:0:"";s:22:"container_styles_color";s:0:"";s:23:"container_styles_height";s:0:"";s:22:"container_styles_width";s:0:"";s:26:"container_styles_font-size";s:0:"";s:23:"container_styles_margin";s:0:"";s:24:"container_styles_padding";s:0:"";s:24:"container_styles_display";s:0:"";s:22:"container_styles_float";s:0:"";s:34:"container_styles_show_advanced_css";s:1:"0";s:25:"container_styles_advanced";s:0:"";s:29:"title_styles_background-color";s:0:"";s:19:"title_styles_border";s:0:"";s:25:"title_styles_border-style";s:0:"";s:25:"title_styles_border-color";s:0:"";s:18:"title_styles_color";s:0:"";s:19:"title_styles_height";s:0:"";s:18:"title_styles_width";s:0:"";s:22:"title_styles_font-size";s:0:"";s:19:"title_styles_margin";s:0:"";s:20:"title_styles_padding";s:0:"";s:20:"title_styles_display";s:0:"";s:18:"title_styles_float";s:0:"";s:30:"title_styles_show_advanced_css";s:1:"0";s:21:"title_styles_advanced";s:0:"";s:27:"row_styles_background-color";s:0:"";s:17:"row_styles_border";s:0:"";s:23:"row_styles_border-style";s:0:"";s:23:"row_styles_border-color";s:0:"";s:16:"row_styles_color";s:0:"";s:17:"row_styles_height";s:0:"";s:16:"row_styles_width";s:0:"";s:20:"row_styles_font-size";s:0:"";s:17:"row_styles_margin";s:0:"";s:18:"row_styles_padding";s:0:"";s:18:"row_styles_display";s:0:"";s:28:"row_styles_show_advanced_css";s:1:"0";s:19:"row_styles_advanced";s:0:"";s:31:"row-odd_styles_background-color";s:0:"";s:21:"row-odd_styles_border";s:0:"";s:27:"row-odd_styles_border-style";s:0:"";s:27:"row-odd_styles_border-color";s:0:"";s:20:"row-odd_styles_color";s:0:"";s:21:"row-odd_styles_height";s:0:"";s:20:"row-odd_styles_width";s:0:"";s:24:"row-odd_styles_font-size";s:0:"";s:21:"row-odd_styles_margin";s:0:"";s:22:"row-odd_styles_padding";s:0:"";s:22:"row-odd_styles_display";s:0:"";s:32:"row-odd_styles_show_advanced_css";s:1:"0";s:23:"row-odd_styles_advanced";s:0:"";s:35:"success-msg_styles_background-color";s:0:"";s:25:"success-msg_styles_border";s:0:"";s:31:"success-msg_styles_border-style";s:0:"";s:31:"success-msg_styles_border-color";s:0:"";s:24:"success-msg_styles_color";s:0:"";s:25:"success-msg_styles_height";s:0:"";s:24:"success-msg_styles_width";s:0:"";s:28:"success-msg_styles_font-size";s:0:"";s:25:"success-msg_styles_margin";s:0:"";s:26:"success-msg_styles_padding";s:0:"";s:26:"success-msg_styles_display";s:0:"";s:36:"success-msg_styles_show_advanced_css";s:1:"0";s:27:"success-msg_styles_advanced";s:0:"";s:33:"error_msg_styles_background-color";s:0:"";s:23:"error_msg_styles_border";s:0:"";s:29:"error_msg_styles_border-style";s:0:"";s:29:"error_msg_styles_border-color";s:0:"";s:22:"error_msg_styles_color";s:0:"";s:23:"error_msg_styles_height";s:0:"";s:22:"error_msg_styles_width";s:0:"";s:26:"error_msg_styles_font-size";s:0:"";s:23:"error_msg_styles_margin";s:0:"";s:24:"error_msg_styles_padding";s:0:"";s:24:"error_msg_styles_display";s:0:"";s:34:"error_msg_styles_show_advanced_css";s:1:"0";s:25:"error_msg_styles_advanced";s:0:"";s:17:"allow_public_link";i:0;s:10:"embed_form";s:0:"";s:8:"currency";s:0:"";s:18:"unique_field_error";s:50:"A form with this value has already been submitted.";s:19:"changeEmailErrorMsg";s:0:"";s:18:"changeDateErrorMsg";s:0:"";s:20:"confirmFieldErrorMsg";s:0:"";s:22:"fieldNumberNumMinError";s:0:"";s:22:"fieldNumberNumMaxError";s:0:"";s:22:"fieldNumberIncrementBy";s:0:"";s:23:"formErrorsCorrectErrors";s:0:"";s:21:"validateRequiredField";s:0:"";s:21:"honeypotHoneypotError";s:0:"";s:20:"fieldsMarkedRequired";s:0:"";s:14:"drawerDisabled";b:0;}s:14:"deleted_fields";a:0:{}s:15:"deleted_actions";a:0:{}}', 4, b'0') ;

#
# End of data contents of table `league_nf3_upgrades`
# --------------------------------------------------------



#
# Delete any existing table `league_options`
#

DROP TABLE IF EXISTS `league_options`;


#
# Table structure of table `league_options`
#

CREATE TABLE `league_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=MyISAM AUTO_INCREMENT=2350 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_options`
#
INSERT INTO `league_options` ( `option_id`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'siteurl', 'http://fullbrook-floor.vm', 'yes'),
(2, 'home', 'http://fullbrook-floor.vm', 'yes'),
(3, 'blogname', 'Fullbrook and Floor', 'yes'),
(4, 'blogdescription', 'Estate agents St. Albans', 'yes'),
(5, 'users_can_register', '0', 'yes'),
(6, 'admin_email', 'tom@weareleague.co.uk', 'yes'),
(7, 'start_of_week', '1', 'yes'),
(8, 'use_balanceTags', '0', 'yes'),
(9, 'use_smilies', '1', 'yes'),
(10, 'require_name_email', '1', 'yes'),
(11, 'comments_notify', '1', 'yes'),
(12, 'posts_per_rss', '10', 'yes'),
(13, 'rss_use_excerpt', '0', 'yes'),
(14, 'mailserver_url', 'mail.example.com', 'yes'),
(15, 'mailserver_login', 'login@example.com', 'yes'),
(16, 'mailserver_pass', 'password', 'yes'),
(17, 'mailserver_port', '110', 'yes'),
(18, 'default_category', '1', 'yes'),
(19, 'default_comment_status', 'closed', 'yes'),
(20, 'default_ping_status', 'open', 'yes'),
(21, 'default_pingback_flag', '', 'yes'),
(22, 'posts_per_page', '10', 'yes'),
(23, 'date_format', 'j F Y', 'yes'),
(24, 'time_format', 'g:i a', 'yes'),
(25, 'links_updated_date_format', 'j F Y H:i', 'yes'),
(26, 'comment_moderation', '1', 'yes'),
(27, 'moderation_notify', '1', 'yes'),
(28, 'permalink_structure', '/%postname%/', 'yes'),
(30, 'hack_file', '0', 'yes'),
(31, 'blog_charset', 'UTF-8', 'yes'),
(32, 'moderation_keys', '', 'no'),
(33, 'active_plugins', 'a:14:{i:0;s:27:"adtrak-core/adtrak-core.php";i:1;s:34:"advanced-custom-fields-pro/acf.php";i:2;s:39:"aryo-activity-log/aryo-activity-log.php";i:3;s:33:"classic-editor/classic-editor.php";i:4;s:33:"duplicate-post/duplicate-post.php";i:5;s:27:"ninja-forms/ninja-forms.php";i:6;s:57:"post-to-google-my-business/post-to-google-my-business.php";i:7;s:69:"public-post-preview-configurator/public-post-preview-configurator.php";i:8;s:43:"public-post-preview/public-post-preview.php";i:9;s:27:"redirection/redirection.php";i:10;s:24:"wordpress-seo/wp-seo.php";i:11;s:29:"wp-mail-smtp/wp_mail_smtp.php";i:12;s:63:"wp-migrate-db-pro-media-files/wp-migrate-db-pro-media-files.php";i:13;s:39:"wp-migrate-db-pro/wp-migrate-db-pro.php";}', 'yes'),
(34, 'category_base', '', 'yes'),
(35, 'ping_sites', 'http://rpc.pingomatic.com/', 'yes'),
(36, 'comment_max_links', '2', 'yes'),
(37, 'gmt_offset', '', 'yes'),
(38, 'default_email_category', '1', 'yes'),
(39, 'recently_edited', '', 'no'),
(40, 'template', 'adtrak-parent', 'yes'),
(41, 'stylesheet', 'fullbrook-floor', 'yes'),
(42, 'comment_whitelist', '1', 'yes'),
(43, 'blacklist_keys', '', 'no'),
(44, 'comment_registration', '1', 'yes'),
(45, 'html_type', 'text/html', 'yes'),
(46, 'use_trackback', '0', 'yes'),
(47, 'default_role', 'subscriber', 'yes'),
(48, 'db_version', '47018', 'yes'),
(49, 'uploads_use_yearmonth_folders', '1', 'yes'),
(52, 'default_link_category', '2', 'yes'),
(53, 'show_on_front', 'page', 'yes'),
(54, 'tag_base', '', 'yes'),
(55, 'show_avatars', '1', 'yes'),
(56, 'avatar_rating', 'G', 'yes'),
(58, 'thumbnail_size_w', '150', 'yes'),
(59, 'thumbnail_size_h', '150', 'yes'),
(60, 'thumbnail_crop', '1', 'yes'),
(61, 'medium_size_w', '300', 'yes'),
(62, 'medium_size_h', '300', 'yes'),
(63, 'avatar_default', 'mystery', 'yes'),
(64, 'large_size_w', '1024', 'yes'),
(65, 'large_size_h', '1024', 'yes'),
(66, 'image_default_link_type', 'none', 'yes'),
(67, 'image_default_size', '', 'yes'),
(68, 'image_default_align', '', 'yes'),
(69, 'close_comments_for_old_posts', '', 'yes'),
(70, 'close_comments_days_old', '14', 'yes'),
(71, 'thread_comments', '1', 'yes'),
(72, 'thread_comments_depth', '5', 'yes'),
(73, 'page_comments', '', 'yes'),
(74, 'comments_per_page', '50', 'yes'),
(75, 'default_comments_page', 'newest', 'yes'),
(76, 'comment_order', 'asc', 'yes'),
(77, 'sticky_posts', 'a:0:{}', 'yes'),
(78, 'widget_categories', 'a:2:{i:2;a:4:{s:5:"title";s:0:"";s:5:"count";i:0;s:12:"hierarchical";i:0;s:8:"dropdown";i:0;}s:12:"_multiwidget";i:1;}', 'yes'),
(79, 'widget_text', 'a:0:{}', 'yes'),
(80, 'widget_rss', 'a:0:{}', 'yes'),
(81, 'uninstall_plugins', 'a:5:{s:39:"aryo-activity-log/aryo-activity-log.php";a:2:{i:0;s:15:"AAL_Maintenance";i:1;s:9:"uninstall";}s:27:"ninja-forms/ninja-forms.php";s:21:"ninja_forms_uninstall";s:43:"public-post-preview/public-post-preview.php";a:2:{i:0;s:22:"DS_Public_Post_Preview";i:1;s:9:"uninstall";}s:27:"redirection/redirection.php";a:2:{i:0;s:17:"Redirection_Admin";i:1;s:16:"plugin_uninstall";}s:33:"classic-editor/classic-editor.php";a:2:{i:0;s:14:"Classic_Editor";i:1;s:9:"uninstall";}}', 'no'),
(82, 'timezone_string', 'Europe/London', 'yes'),
(83, 'page_for_posts', '0', 'yes'),
(84, 'page_on_front', '6', 'yes'),
(85, 'default_post_format', '0', 'yes'),
(86, 'link_manager_enabled', '0', 'yes'),
(87, 'finished_splitting_shared_terms', '1', 'yes'),
(88, 'site_icon', '0', 'yes'),
(89, 'medium_large_size_w', '768', 'yes'),
(90, 'medium_large_size_h', '0', 'yes'),
(91, 'wp_page_for_privacy_policy', '3', 'yes'),
(92, 'show_comments_cookies_opt_in', '1', 'yes'),
(93, 'admin_email_lifespan', '1609263218', 'yes'),
(94, 'initial_db_version', '47018', 'yes'),
(95, 'league_user_roles', 'a:7:{s:13:"administrator";a:2:{s:4:"name";s:13:"Administrator";s:12:"capabilities";a:64:{s:13:"switch_themes";b:1;s:11:"edit_themes";b:1;s:16:"activate_plugins";b:1;s:12:"edit_plugins";b:1;s:10:"edit_users";b:1;s:10:"edit_files";b:1;s:14:"manage_options";b:1;s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:6:"import";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:8:"level_10";b:1;s:7:"level_9";b:1;s:7:"level_8";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;s:12:"delete_users";b:1;s:12:"create_users";b:1;s:17:"unfiltered_upload";b:1;s:14:"edit_dashboard";b:1;s:14:"update_plugins";b:1;s:14:"delete_plugins";b:1;s:15:"install_plugins";b:1;s:13:"update_themes";b:1;s:14:"install_themes";b:1;s:11:"update_core";b:1;s:10:"list_users";b:1;s:12:"remove_users";b:1;s:13:"promote_users";b:1;s:18:"edit_theme_options";b:1;s:13:"delete_themes";b:1;s:6:"export";b:1;s:26:"view_all_aryo_activity_log";b:1;s:20:"wpseo_manage_options";b:1;s:10:"copy_posts";b:1;}}s:6:"editor";a:2:{s:4:"name";s:6:"Editor";s:12:"capabilities";a:36:{s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;s:15:"wpseo_bulk_edit";b:1;s:10:"copy_posts";b:1;}}s:6:"author";a:2:{s:4:"name";s:6:"Author";s:12:"capabilities";a:10:{s:12:"upload_files";b:1;s:10:"edit_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:4:"read";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;s:22:"delete_published_posts";b:1;}}s:11:"contributor";a:2:{s:4:"name";s:11:"Contributor";s:12:"capabilities";a:5:{s:10:"edit_posts";b:1;s:4:"read";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;}}s:10:"subscriber";a:2:{s:4:"name";s:10:"Subscriber";s:12:"capabilities";a:2:{s:4:"read";b:1;s:7:"level_0";b:1;}}s:13:"wpseo_manager";a:2:{s:4:"name";s:11:"SEO Manager";s:12:"capabilities";a:38:{s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;s:15:"wpseo_bulk_edit";b:1;s:28:"wpseo_edit_advanced_metadata";b:1;s:20:"wpseo_manage_options";b:1;s:23:"view_site_health_checks";b:1;}}s:12:"wpseo_editor";a:2:{s:4:"name";s:10:"SEO Editor";s:12:"capabilities";a:36:{s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;s:15:"wpseo_bulk_edit";b:1;s:28:"wpseo_edit_advanced_metadata";b:1;}}}', 'yes'),
(96, 'fresh_site', '0', 'yes'),
(97, 'WPLANG', 'en_GB', 'yes'),
(98, 'widget_search', 'a:2:{i:2;a:1:{s:5:"title";s:0:"";}s:12:"_multiwidget";i:1;}', 'yes'),
(99, 'widget_recent-posts', 'a:2:{i:2;a:2:{s:5:"title";s:0:"";s:6:"number";i:5;}s:12:"_multiwidget";i:1;}', 'yes'),
(100, 'widget_recent-comments', 'a:2:{i:2;a:2:{s:5:"title";s:0:"";s:6:"number";i:5;}s:12:"_multiwidget";i:1;}', 'yes'),
(101, 'widget_archives', 'a:2:{i:2;a:3:{s:5:"title";s:0:"";s:5:"count";i:0;s:8:"dropdown";i:0;}s:12:"_multiwidget";i:1;}', 'yes'),
(102, 'widget_meta', 'a:2:{i:2;a:1:{s:5:"title";s:0:"";}s:12:"_multiwidget";i:1;}', 'yes'),
(103, 'sidebars_widgets', 'a:2:{s:19:"wp_inactive_widgets";a:6:{i:0;s:8:"search-2";i:1;s:14:"recent-posts-2";i:2;s:17:"recent-comments-2";i:3;s:10:"archives-2";i:4;s:12:"categories-2";i:5;s:6:"meta-2";}s:13:"array_version";i:3;}', 'yes'),
(104, 'cron', 'a:16:{i:1595532329;a:1:{s:26:"action_scheduler_run_queue";a:1:{s:32:"0d04ed39571b55704c122d726248bbac";a:3:{s:8:"schedule";s:12:"every_minute";s:4:"args";a:1:{i:0;s:7:"WP Cron";}s:8:"interval";i:60;}}}i:1595532819;a:1:{s:34:"wp_privacy_delete_old_export_files";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:6:"hourly";s:4:"args";a:0:{}s:8:"interval";i:3600;}}}i:1595568819;a:3:{s:16:"wp_version_check";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:17:"wp_update_plugins";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:16:"wp_update_themes";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}}i:1595568869;a:1:{s:32:"check_plugin_updates-adtrak-core";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}}i:1595570081;a:1:{s:42:"puc_cron_check_updates_theme-adtrak-parent";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}}i:1595612019;a:2:{s:30:"wp_site_health_scheduled_check";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:6:"weekly";s:4:"args";a:0:{}s:8:"interval";i:604800;}}s:32:"recovery_mode_clean_expired_keys";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}}i:1595612058;a:2:{s:19:"wp_scheduled_delete";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}s:25:"delete_expired_transients";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}}i:1595612059;a:1:{s:30:"wp_scheduled_auto_draft_delete";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}}i:1595612072;a:1:{s:17:"mbp_refresh_token";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}}i:1595612075;a:1:{s:19:"wpseo-reindex-links";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}}i:1595613314;a:1:{s:22:"redirection_log_delete";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}}i:1596130472;a:1:{s:16:"wpseo_ryte_fetch";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:6:"weekly";s:4:"args";a:0:{}s:8:"interval";i:604800;}}}i:1596134076;a:1:{s:26:"nf_weekly_promotion_update";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:9:"nf-weekly";s:4:"args";a:0:{}s:8:"interval";i:604800;}}}i:1596134474;a:1:{s:22:"nf_marketing_feed_cron";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:9:"nf-weekly";s:4:"args";a:0:{}s:8:"interval";i:604800;}}}i:1596393674;a:1:{s:13:"nf_optin_cron";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"nf-monthly";s:4:"args";a:0:{}s:8:"interval";i:2678400;}}}s:7:"version";i:2;}', 'yes') ;
INSERT INTO `league_options` ( `option_id`, `option_name`, `option_value`, `autoload`) VALUES
(105, 'widget_pages', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(106, 'widget_calendar', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(107, 'widget_media_audio', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(108, 'widget_media_image', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(109, 'widget_media_gallery', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(110, 'widget_media_video', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(111, 'widget_tag_cloud', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(112, 'widget_nav_menu', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(113, 'widget_custom_html', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(115, 'recovery_keys', 'a:1:{s:22:"2jc2868xrzceGYIwxN4RXH";a:2:{s:10:"hashed_key";s:34:"$P$BMVjWj.0iQoR0tGiMCtea7SRMpmmq51";s:10:"created_at";i:1595531942;}}', 'yes'),
(137, 'can_compress_scripts', '1', 'no'),
(144, 'recently_activated', 'a:0:{}', 'yes'),
(145, 'activity_log_db_version', '1.0', 'yes'),
(146, 'ninja_forms_oauth_client_secret', 'CjczRWoWU023MMT69wtgM443GXj0PmoD9kNMlNRW', 'yes'),
(147, 'ninja_forms_version', '3.4.24.3', 'yes'),
(148, 'ninja_forms_db_version', '1.4', 'no'),
(149, 'ninja_forms_required_updates', 'a:6:{s:19:"CacheCollateActions";s:19:"2020-07-02 17:34:29";s:17:"CacheCollateForms";s:19:"2020-07-02 17:34:29";s:18:"CacheCollateFields";s:19:"2020-07-02 17:34:29";s:19:"CacheCollateObjects";s:19:"2020-07-02 17:34:29";s:19:"CacheCollateCleanup";s:19:"2020-07-02 17:34:29";s:25:"CacheFieldReconcilliation";s:19:"2020-07-02 17:34:29";}', 'yes'),
(150, 'ninja_forms_settings', 'a:11:{s:11:"date_format";s:5:"m/d/Y";s:8:"currency";s:3:"USD";s:18:"recaptcha_site_key";s:0:"";s:20:"recaptcha_secret_key";s:0:"";s:14:"recaptcha_lang";s:0:"";s:19:"delete_on_uninstall";i:0;s:21:"disable_admin_notices";s:1:"0";s:16:"builder_dev_mode";s:1:"1";s:18:"opinionated_styles";s:5:"light";s:15:"recaptcha_theme";s:5:"light";s:15:"currency_symbol";s:5:"&#36;";}', 'yes'),
(151, 'ninja_forms_zuul', '36', 'no'),
(154, 'external_updates-adtrak-core', 'O:8:"stdClass":3:{s:9:"lastCheck";i:1595532090;s:14:"checkedVersion";s:6:"0.9.27";s:6:"update";O:8:"stdClass":9:{s:2:"id";i:0;s:4:"slug";s:11:"adtrak-core";s:7:"version";s:6:"0.9.27";s:8:"homepage";s:36:"http://github.com/adtrak-core/plugin";s:6:"tested";N;s:12:"download_url";s:68:"https://api.github.com/repos/adtrak-core/adtrak-core/zipball/v0.9.27";s:14:"upgrade_notice";N;s:8:"filename";s:27:"adtrak-core/adtrak-core.php";s:12:"translations";a:0:{}}}', 'no'),
(155, 'fs_active_plugins', 'O:8:"stdClass":3:{s:7:"plugins";a:1:{s:35:"post-to-google-my-business/freemius";O:8:"stdClass":4:{s:7:"version";s:8:"2.3.2.10";s:4:"type";s:6:"plugin";s:9:"timestamp";i:1595531939;s:11:"plugin_path";s:57:"post-to-google-my-business/post-to-google-my-business.php";}}s:7:"abspath";s:33:"C:\\wamp64\\www\\fullbrook-floor.vm/";s:6:"newest";O:8:"stdClass":5:{s:11:"plugin_path";s:57:"post-to-google-my-business/post-to-google-my-business.php";s:8:"sdk_path";s:35:"post-to-google-my-business/freemius";s:7:"version";s:8:"2.3.2.10";s:13:"in_activation";b:0;s:9:"timestamp";i:1595531939;}}', 'yes'),
(156, 'fs_debug_mode', '', 'yes'),
(157, 'fs_accounts', 'a:6:{s:21:"id_slug_type_path_map";a:1:{i:1828;a:3:{s:4:"slug";s:26:"post-to-google-my-business";s:4:"type";s:6:"plugin";s:4:"path";s:57:"post-to-google-my-business/post-to-google-my-business.php";}}s:11:"plugin_data";a:1:{s:26:"post-to-google-my-business";a:16:{s:16:"plugin_main_file";O:8:"stdClass":1:{s:4:"path";s:57:"post-to-google-my-business/post-to-google-my-business.php";}s:20:"is_network_activated";b:0;s:17:"install_timestamp";i:1593711271;s:17:"was_plugin_loaded";b:1;s:21:"is_plugin_new_install";b:0;s:16:"sdk_last_version";N;s:11:"sdk_version";s:8:"2.3.2.10";s:16:"sdk_upgrade_mode";b:1;s:18:"sdk_downgrade_mode";b:0;s:19:"plugin_last_version";s:6:"2.2.18";s:14:"plugin_version";s:6:"2.2.25";s:19:"plugin_upgrade_mode";b:1;s:21:"plugin_downgrade_mode";b:0;s:17:"connectivity_test";a:6:{s:12:"is_connected";b:1;s:4:"host";s:18:"fullbrook-floor.vm";s:9:"server_ip";s:3:"::1";s:9:"is_active";b:1;s:9:"timestamp";i:1593711271;s:7:"version";s:6:"2.2.18";}s:15:"prev_is_premium";b:0;s:18:"sticky_optin_added";b:1;}}s:13:"file_slug_map";a:1:{s:57:"post-to-google-my-business/post-to-google-my-business.php";s:26:"post-to-google-my-business";}s:7:"plugins";a:1:{s:26:"post-to-google-my-business";O:9:"FS_Plugin":24:{s:16:"parent_plugin_id";N;s:5:"title";s:26:"Post to Google My Business";s:4:"slug";s:26:"post-to-google-my-business";s:12:"premium_slug";s:34:"post-to-google-my-business-premium";s:4:"type";s:6:"plugin";s:20:"affiliate_moderation";s:8:"selected";s:19:"is_wp_org_compliant";b:1;s:22:"premium_releases_count";N;s:4:"file";s:57:"post-to-google-my-business/post-to-google-my-business.php";s:7:"version";s:6:"2.2.25";s:11:"auto_update";N;s:4:"info";N;s:10:"is_premium";b:0;s:14:"premium_suffix";s:9:"(Premium)";s:7:"is_live";b:1;s:9:"bundle_id";N;s:17:"bundle_public_key";N;s:10:"public_key";s:32:"pk_8ef8aab9dd4277db6bc9b2441830c";s:10:"secret_key";N;s:2:"id";s:4:"1828";s:7:"updated";N;s:7:"created";N;s:22:"\0FS_Entity\0_is_updated";b:0;s:11:"_is_updated";b:0;}}s:9:"unique_id";s:32:"96a80166e4482cb8e1b7740845a876c6";s:13:"admin_notices";a:1:{s:26:"post-to-google-my-business";a:0:{}}}', 'yes'),
(158, 'activity-log-settings', 'a:1:{s:13:"logs_lifespan";s:2:"30";}', 'yes'),
(159, 'fs_gdpr', 'a:1:{s:2:"u1";a:1:{s:8:"required";b:0;}}', 'yes'),
(160, 'fs_api_cache', 'a:0:{}', 'no'),
(163, 'redirection_options', 'a:24:{s:7:"support";b:0;s:5:"token";s:32:"02443377a9b66ac268d1628542b18add";s:12:"monitor_post";i:1;s:13:"monitor_types";a:2:{i:0;s:4:"post";i:1;s:4:"page";}s:19:"associated_redirect";s:0:"";s:11:"auto_target";s:0:"";s:15:"expire_redirect";i:7;s:10:"expire_404";i:7;s:7:"modules";a:0:{}s:10:"newsletter";b:0;s:14:"redirect_cache";i:1;s:10:"ip_logging";i:0;s:13:"last_group_id";i:1;s:8:"rest_api";i:0;s:5:"https";b:0;s:7:"headers";a:0:{}s:8:"database";s:3:"4.1";s:8:"relocate";s:0:"";s:16:"preferred_domain";s:0:"";s:7:"aliases";a:0:{}s:10:"flag_query";s:5:"exact";s:9:"flag_case";b:0;s:13:"flag_trailing";b:0;s:10:"flag_regex";b:0;}', 'yes'),
(164, 'wp_mail_smtp_initial_version', '2.0.1', 'no'),
(165, 'wp_mail_smtp_version', '2.0.1', 'no'),
(166, 'wp_mail_smtp', 'a:2:{s:4:"mail";a:6:{s:10:"from_email";s:21:"tom@weareleague.co.uk";s:9:"from_name";s:21:"Fullbrook &amp; Floor";s:6:"mailer";s:4:"mail";s:11:"return_path";b:0;s:16:"from_email_force";b:0;s:15:"from_name_force";b:0;}s:4:"smtp";a:2:{s:7:"autotls";b:1;s:4:"auth";b:1;}}', 'no'),
(167, 'wpseo', 'a:28:{s:15:"ms_defaults_set";b:0;s:40:"ignore_search_engines_discouraged_notice";b:0;s:25:"ignore_indexation_warning";b:0;s:29:"indexation_warning_hide_until";b:0;s:18:"indexation_started";b:0;s:31:"indexables_indexation_completed";b:1;s:7:"version";s:6:"14.6.1";s:16:"previous_version";s:6:"14.4.1";s:20:"disableadvanced_meta";b:1;s:30:"enable_headless_rest_endpoints";b:1;s:17:"ryte_indexability";b:1;s:11:"baiduverify";s:0:"";s:12:"googleverify";s:0:"";s:8:"msverify";s:0:"";s:12:"yandexverify";s:0:"";s:9:"site_type";s:0:"";s:20:"has_multiple_authors";s:0:"";s:16:"environment_type";s:0:"";s:23:"content_analysis_active";b:1;s:23:"keyword_analysis_active";b:1;s:21:"enable_admin_bar_menu";b:1;s:26:"enable_cornerstone_content";b:1;s:18:"enable_xml_sitemap";b:1;s:24:"enable_text_link_counter";b:1;s:22:"show_onboarding_notice";b:1;s:18:"first_activated_on";i:1593711272;s:13:"myyoast-oauth";b:0;s:8:"tracking";b:0;}', 'yes'),
(168, 'wpseo_titles', 'a:70:{s:17:"forcerewritetitle";b:0;s:9:"separator";s:7:"sc-dash";s:16:"title-home-wpseo";s:42:"%%sitename%% %%page%% %%sep%% %%sitedesc%%";s:18:"title-author-wpseo";s:41:"%%name%%, Author at %%sitename%% %%page%%";s:19:"title-archive-wpseo";s:38:"%%date%% %%page%% %%sep%% %%sitename%%";s:18:"title-search-wpseo";s:63:"You searched for %%searchphrase%% %%page%% %%sep%% %%sitename%%";s:15:"title-404-wpseo";s:35:"Page not found %%sep%% %%sitename%%";s:19:"metadesc-home-wpseo";s:0:"";s:21:"metadesc-author-wpseo";s:0:"";s:22:"metadesc-archive-wpseo";s:0:"";s:9:"rssbefore";s:0:"";s:8:"rssafter";s:53:"The post %%POSTLINK%% appeared first on %%BLOGLINK%%.";s:20:"noindex-author-wpseo";b:0;s:28:"noindex-author-noposts-wpseo";b:1;s:21:"noindex-archive-wpseo";b:1;s:14:"disable-author";b:0;s:12:"disable-date";b:0;s:19:"disable-post_format";b:0;s:18:"disable-attachment";b:1;s:23:"is-media-purge-relevant";b:0;s:20:"breadcrumbs-404crumb";s:25:"Error 404: Page not found";s:29:"breadcrumbs-display-blog-page";b:1;s:20:"breadcrumbs-boldlast";b:0;s:25:"breadcrumbs-archiveprefix";s:12:"Archives for";s:18:"breadcrumbs-enable";b:0;s:16:"breadcrumbs-home";s:4:"Home";s:18:"breadcrumbs-prefix";s:0:"";s:24:"breadcrumbs-searchprefix";s:16:"You searched for";s:15:"breadcrumbs-sep";s:7:"&raquo;";s:12:"website_name";s:0:"";s:11:"person_name";s:0:"";s:11:"person_logo";s:0:"";s:14:"person_logo_id";i:0;s:22:"alternate_website_name";s:0:"";s:12:"company_logo";s:0:"";s:15:"company_logo_id";i:0;s:12:"company_name";s:0:"";s:17:"company_or_person";s:7:"company";s:25:"company_or_person_user_id";b:0;s:17:"stripcategorybase";b:0;s:10:"title-post";s:39:"%%title%% %%page%% %%sep%% %%sitename%%";s:13:"metadesc-post";s:0:"";s:12:"noindex-post";b:0;s:13:"showdate-post";b:0;s:23:"display-metabox-pt-post";b:1;s:23:"post_types-post-maintax";i:0;s:10:"title-page";s:39:"%%title%% %%page%% %%sep%% %%sitename%%";s:13:"metadesc-page";s:0:"";s:12:"noindex-page";b:0;s:13:"showdate-page";b:0;s:23:"display-metabox-pt-page";b:1;s:23:"post_types-page-maintax";i:0;s:16:"title-attachment";s:39:"%%title%% %%page%% %%sep%% %%sitename%%";s:19:"metadesc-attachment";s:0:"";s:18:"noindex-attachment";b:0;s:19:"showdate-attachment";b:0;s:29:"display-metabox-pt-attachment";b:1;s:29:"post_types-attachment-maintax";i:0;s:18:"title-tax-category";s:53:"%%term_title%% Archives %%page%% %%sep%% %%sitename%%";s:21:"metadesc-tax-category";s:0:"";s:28:"display-metabox-tax-category";b:1;s:20:"noindex-tax-category";b:0;s:18:"title-tax-post_tag";s:53:"%%term_title%% Archives %%page%% %%sep%% %%sitename%%";s:21:"metadesc-tax-post_tag";s:0:"";s:28:"display-metabox-tax-post_tag";b:1;s:20:"noindex-tax-post_tag";b:0;s:21:"title-tax-post_format";s:53:"%%term_title%% Archives %%page%% %%sep%% %%sitename%%";s:24:"metadesc-tax-post_format";s:0:"";s:31:"display-metabox-tax-post_format";b:1;s:23:"noindex-tax-post_format";b:1;}', 'yes'),
(169, 'wpseo_social', 'a:19:{s:13:"facebook_site";s:0:"";s:13:"instagram_url";s:0:"";s:12:"linkedin_url";s:0:"";s:11:"myspace_url";s:0:"";s:16:"og_default_image";s:0:"";s:19:"og_default_image_id";s:0:"";s:18:"og_frontpage_title";s:0:"";s:17:"og_frontpage_desc";s:0:"";s:18:"og_frontpage_image";s:0:"";s:21:"og_frontpage_image_id";s:0:"";s:9:"opengraph";b:1;s:13:"pinterest_url";s:0:"";s:15:"pinterestverify";s:0:"";s:7:"twitter";b:1;s:12:"twitter_site";s:0:"";s:17:"twitter_card_type";s:19:"summary_large_image";s:11:"youtube_url";s:0:"";s:13:"wikipedia_url";s:0:"";s:10:"fbadminapp";s:0:"";}', 'yes'),
(170, 'wpseo_flush_rewrite', '1', 'yes'),
(172, 'yoast_migrations_free', 'a:1:{s:7:"version";s:6:"14.6.1";}', 'yes'),
(177, 'mbp_notifications', 'a:1:{s:23:"dashboard-notifications";a:1:{s:15:"welcome_message";a:4:{s:5:"title";s:47:"Getting started with Post to Google My Business";s:4:"text";s:356:"Hi admin.league,<br />\n<br />\nThanks for installing Post to Google My Business! To get started, connect the plugin to your Google account on the Google settings tab.<br />\n<br />\nNeed help? Check out the <a target="_blank" href="https://tycoonmedia.net/gmb-tutorial-video/">tutorial video</a><br />\n<br />\n<strong>Koen</strong><br /><i>Plugin Developer</i>";s:5:"image";s:12:"img/koen.png";s:3:"alt";s:23:"Developer profile photo";}}}', 'yes'),
(178, 'mbp_ignored_notifications', 'a:0:{}', 'yes'),
(179, 'mbp_welcome_message', '1', 'yes'),
(180, 'mbp_version', '2.2.25', 'yes'),
(181, 'widget_ninja_forms_widget', 'a:1:{s:12:"_multiwidget";i:1;}', 'yes'),
(185, 'acf_version', '5.8.12', 'yes'),
(186, 'ninja_forms_needs_updates', '0', 'yes'),
(187, 'a31ad2cbad919e8a377cdd00048d4574', 'a:2:{s:7:"timeout";i:1593725676;s:5:"value";s:871:"{"new_version":"3.4.3","name":"Location Dynamics","slug":"plugin","url":"https:\\/\\/plugins.adtrakdev.com\\/downloads\\/location-dynamics\\/?changelog=1","last_updated":"2019-07-30 16:39:24","homepage":"https:\\/\\/plugins.adtrakdev.com\\/downloads\\/location-dynamics\\/","package":"https:\\/\\/plugins.adtrakdev.com\\/edd-sl\\/package_download\\/MTU5Mzk3MDQ3NTpBRFRSQUtMT0NBVElPTkRZTkFNSUNTOjc2OmYwMGI5ZGYzMjU1YTE5Y2I3NGE2Mjc1ZDAyYWFkMDA5Omh0dHBALy9mdWxsYnJvb2stZmxvb3Iudm0=","download_link":"https:\\/\\/plugins.adtrakdev.com\\/edd-sl\\/package_download\\/MTU5Mzk3MDQ3NTpBRFRSQUtMT0NBVElPTkRZTkFNSUNTOjc2OmYwMGI5ZGYzMjU1YTE5Y2I3NGE2Mjc1ZDAyYWFkMDA5Omh0dHBALy9mdWxsYnJvb2stZmxvb3Iudm0=","sections":{"description":"<p>A plugin that shows phone numbers based on given location or PPC area. Allow for old JSON importing for easy migration and easy Wordpress editing.<\\/p>\\n","changelog":""}}";}', 'yes'),
(191, 'mbp_dashboard', '', 'yes'),
(192, 'mbp_google_settings', '', 'yes'),
(193, 'mbp_quick_post_settings', '', 'yes'),
(194, 'mbp_post_type_settings', '', 'yes'),
(195, 'mbp_debug_info', '', 'yes'),
(198, 'nf_admin_notice', 'a:1:{s:16:"one_week_support";a:3:{s:5:"start";s:8:"7/9/2020";s:3:"int";i:7;s:9:"dismissed";i:1;}}', 'yes'),
(199, 'wpseo_ryte', 'a:2:{s:6:"status";i:-1;s:10:"last_fetch";i:1595532033;}', 'yes'),
(206, 'action_scheduler_hybrid_store_demarkation', '6', 'yes'),
(207, 'schema-ActionScheduler_StoreSchema', '3.0.1593711569', 'yes'),
(208, 'schema-ActionScheduler_LoggerSchema', '2.0.1593711569', 'yes'),
(213, 'action_scheduler_lock_async-request-runner', '1595532299', 'yes'),
(217, 'wp_mail_smtp_migration_version', '2', 'yes'),
(218, 'wp_mail_smtp_review_notice', 'a:2:{s:4:"time";i:1593711648;s:9:"dismissed";b:0;}', 'yes'),
(227, 'acf_pro_license', 'YToyOntzOjM6ImtleSI7czo3MjoiYjNKa1pYSmZhV1E5TkRnME16QjhkSGx3WlQxa1pYWmxiRzl3WlhKOFpHRjBaVDB5TURFMUxUQXhMVEl6SURFek9qRXpPakkwIjtzOjM6InVybCI7czoyNToiaHR0cDovL2Z1bGxicm9vay1mbG9vci52bSI7fQ==', 'yes'),
(246, 'nf_form_tel_data', '1', 'no'),
(247, 'ninja_forms_do_not_allow_tracking', '1', 'yes'),
(248, 'ninja_forms_optin_reported', '1', 'yes'),
(252, 'theme_mods_twentytwenty', 'a:1:{s:16:"sidebars_widgets";a:2:{s:4:"time";i:1593712480;s:4:"data";a:3:{s:19:"wp_inactive_widgets";a:0:{}s:9:"sidebar-1";a:3:{i:0;s:8:"search-2";i:1;s:14:"recent-posts-2";i:2;s:17:"recent-comments-2";}s:9:"sidebar-2";a:3:{i:0;s:10:"archives-2";i:1;s:12:"categories-2";i:2;s:6:"meta-2";}}}}', 'yes'),
(253, 'current_theme', 'Fullbrook &amp; Floor', 'yes'),
(254, 'theme_mods_fullbrook-floor', 'a:3:{i:0;b:0;s:18:"nav_menu_locations";a:0:{}s:18:"custom_css_post_id";i:-1;}', 'yes'),
(255, 'theme_switched', '', 'yes'),
(257, 'puc_external_updates_theme-adtrak-parent', 'O:8:"stdClass":5:{s:9:"lastCheck";i:1595532090;s:14:"checkedVersion";s:5:"2.1.3";s:6:"update";O:8:"stdClass":5:{s:4:"slug";s:13:"adtrak-parent";s:7:"version";s:5:"2.1.8";s:12:"download_url";s:69:"https://api.github.com/repos/adtrak-core/adtrak-parent/zipball/v2.1.8";s:12:"translations";a:0:{}s:11:"details_url";s:19:"http://adtrak.co.uk";}s:11:"updateClass";s:19:"Puc_v4_Theme_Update";s:15:"updateBaseClass";s:12:"Theme_Update";}', 'no'),
(269, 'new_admin_email', 'tom@weareleague.co.uk', 'yes'),
(285, 'options_site_logo', '', 'no'),
(286, '_options_site_logo', 'field_58457e46ed540', 'no'),
(287, 'options_site_email', 'info@fullbrookandfloor.co.uk', 'no'),
(288, '_options_site_email', 'field_58457e5eed541', 'no'),
(289, 'options_site_address', '3', 'no'),
(290, '_options_site_address', 'field_58457ebaed542', 'no'),
(291, 'options_site_postcode', 'AL1 1LE', 'no'),
(292, '_options_site_postcode', 'field_58482a2a8d01f', 'no'),
(293, 'options_vat_number', '', 'no'),
(294, '_options_vat_number', 'field_58482a8642e0d', 'no'),
(295, 'options_reg_number', '12706556', 'no'),
(296, '_options_reg_number', 'field_58482a9e42e0e', 'no'),
(297, 'options_default_location', 'St. Albans', 'no'),
(298, '_options_default_location', 'field_58457f555f726', 'no'),
(299, 'options_prefix_phone_number', '', 'no'),
(300, '_options_prefix_phone_number', 'field_58457f5d5f727', 'no'),
(301, 'options_default_phone_number', '01727 251 691', 'no'),
(302, '_options_default_phone_number', 'field_58457f705f728', 'no'),
(303, 'options_social_twitter', 'https://www.twitter.com', 'no'),
(304, '_options_social_twitter', 'field_58457fd1dae69', 'no'),
(305, 'options_social_facebook', 'https://www.facebook.com', 'no'),
(306, '_options_social_facebook', 'field_58458028dae6b', 'no'),
(307, 'options_social_google', '', 'no'),
(308, '_options_social_google', 'field_58458033dae6c', 'no'),
(309, 'options_social_instagram', 'https://www.instagram.com', 'no'),
(310, '_options_social_instagram', 'field_5845803edae6d', 'no'),
(311, 'options_social_youtube', '', 'no'),
(312, '_options_social_youtube', 'field_social_youtube', 'no'),
(313, 'options_social_pinterest', '', 'no'),
(314, '_options_social_pinterest', 'field_58458062dae6f', 'no'),
(315, 'options_social_linkedin', '', 'no'),
(316, '_options_social_linkedin', 'field_58458062dae6g', 'no'),
(317, 'options_social_other', '', 'no'),
(318, '_options_social_other', 'social_other', 'no') ;
INSERT INTO `league_options` ( `option_id`, `option_name`, `option_value`, `autoload`) VALUES
(319, 'options_logos_header', 'We\'re fully accredited for your peace of mind', 'no'),
(320, '_options_logos_header', 'field_58482ac942e10', 'no'),
(321, 'options_logos', '2', 'no'),
(322, '_options_logos', 'field_58482bc942e12', 'no'),
(323, 'options_cc_header', '', 'no'),
(324, '_options_cc_header', 'field_58482e29afe4d', 'no'),
(325, 'options_credit_cards', '', 'no'),
(326, '_options_credit_cards', 'field_58482e3bafe4e', 'no'),
(327, 'options_why_header', '', 'no'),
(328, '_options_why_header', 'field_58483296afe51', 'no'),
(329, 'options_why_choose_us', '5', 'no'),
(330, '_options_why_choose_us', 'field_5f04e465f4a3e', 'no'),
(332, 'action_scheduler_migration_status', 'complete', 'yes'),
(335, 'nav_menu_options', 'a:2:{i:0;b:0;s:8:"auto_add";a:0:{}}', 'yes'),
(345, 'nf_active_promotions', '{"dashboard":[{"id":"personal-20","location":"dashboard","type":"personal","content":"<a href=\\"https:\\/\\/ninjaforms.com\\/personal-membership\\/?utm_source=ninja-forms-plugin&utm_medium=dashboard-banner-ad&utm_campaign=personal-banner-ad&utm_content=personal-20\\" target=\\"_blank\\" class=\\"nf-remove-promo-styling\\"><img src=\\"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/plugins\\/ninja-forms\\/assets\\/img\\/promotions\\/dashboard-banner-personal-20.png\\"><\\/a>","script":""},{"id":"personal-50","location":"dashboard","type":"personal","content":"<a href=\\"https:\\/\\/ninjaforms.com\\/personal-membership\\/?utm_source=ninja-forms-plugin&utm_medium=dashboard-banner-ad&utm_campaign=personal-banner-ad&utm_content=personal-50\\" target=\\"_blank\\" class=\\"nf-remove-promo-styling\\"><img src=\\"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/plugins\\/ninja-forms\\/assets\\/img\\/promotions\\/dashboard-banner-personal-50.png\\"><\\/a>","script":""},{"id":"sendwp-banner","location":"dashboard","content":"<span aria-label=\\"SendWP. Getting WordPress email into an inbox shouldn\'t be that hard! Never miss another receipt, form submission, or any WordPress email ever again.\\" style=\\"cursor:pointer;width:800px;height:83px;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;background-image:url(\'http:\\/\\/fullbrook-floor.vm\\/wp-content\\/plugins\\/ninja-forms\\/assets\\/img\\/promotions\\/dashboard-banner-sendwp.png\');display:block;\\"><\\/span>","type":"sendwp","script":"\\r\\n      setTimeout(function(){ \\/* Wait for services to init. *\\/\\r\\n        var data = {\\r\\n          width: 450,\\r\\n          closeOnClick: \'body\',\\r\\n          closeOnEsc: true,\\r\\n          content: \'<p><h2>Frustrated that WordPress email isn\\u2019t being received?<\\/h2><p>Form submission notifications not hitting your inbox? Some of your visitors getting form feedback via email, others not? By default, your WordPress site sends emails through your web host, which can be unreliable. Your host has spent lots of time and money optimizing to serve your pages, not send your emails.<\\/p><h3>Sign up for SendWP today, and never deal with WordPress email issues again!<\\/h3><p>SendWP is an email service that removes your web host from the email equation.<\\/p><ul style=&quot;list-style-type:initial;margin-left: 20px;&quot;><li>Sends email through dedicated email service, increasing email deliverability.<\\/li><li>Keeps form submission emails out of spam by using a trusted email provider.<\\/li><li>On a shared web host? Don\\u2019t worry about emails being rejected because of blocked IP addresses.<\\/li><li><strong>$1 for the first month. $9\\/month after. Cancel anytime!<\\/strong><\\/li><\\/ul><\\/p><br \\/>\',\\r\\n          btnPrimary: {\\r\\n            text: \'Sign me up!\',\\r\\n            callback: function() {\\r\\n              var spinner = document.createElement(\'span\');\\r\\n              spinner.classList.add(\'dashicons\', \'dashicons-update\', \'dashicons-update-spin\');\\r\\n              var w = this.offsetWidth;\\r\\n              this.innerHTML = spinner.outerHTML;\\r\\n              this.style.width = w+\'px\';\\r\\n              ninja_forms_sendwp_remote_install();\\r\\n            }\\r\\n          },\\r\\n          btnSecondary: {\\r\\n            text: \'Cancel\',\\r\\n            callback: function() {\\r\\n              sendwpModal.toggleModal(false);\\r\\n            }\\r\\n          }\\r\\n        }\\r\\n        var sendwpModal = new NinjaModal(data);\\r\\n      }, 500);\\r\\n    "}]}', 'no'),
(346, 'ninja_forms_addons_feed', '[{"title":"Conditional Logic","image":"assets\\/img\\/add-ons\\/conditional-logic.png","content":"Build dynamic forms that can change as a user fills out the form. Show and hide fields. Send certain email, don\'t send others. Redirect to one of many pages. The possibilities are endless!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/conditional-logic\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Conditional+Logic","plugin":"ninja-forms-conditionals\\/conditionals.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/conditional-logic\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Conditional+Logic+Docs","version":"3.0.26.2","categories":[{"name":"Look &amp; Feel","slug":"look-feel"},{"name":"Actions","slug":"actions"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Business","slug":"business"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"Form Function and Design","slug":"form-function-design"}]},{"title":"Multi-Part Forms","image":"assets\\/img\\/add-ons\\/multi-part-forms.png","content":"Give submissions a boost on any longer form by making it a multi-page form. Drag and drop fields between pages, add breadcrumb navigation, a progress bar, and loads more!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/multi-part-forms\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Multi-Part+Forms","plugin":"ninja-forms-multi-part\\/multi-part.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/multi-part-forms\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Multi-Part+Forms+Docs","version":"3.0.26","categories":[{"name":"Look &amp; Feel","slug":"look-feel"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Business","slug":"business"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"Form Function and Design","slug":"form-function-design"}]},{"title":"Front-End Posting","image":"assets\\/img\\/add-ons\\/front-end-posting.png","content":"Let users publish content just by submitting a form! Completely configurable including post type, title, even categories and tags. Set post status, author, and much more!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/post-creation\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Front-End+Posting","plugin":"ninja-forms-post-creation\\/ninja-forms-post-creation.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/post-creation\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Front-End+Posting+Docs","version":"3.0.9","categories":[{"name":"Content Management","slug":"content-management"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"User Management","slug":"user-management"}]},{"title":"File Uploads","image":"assets\\/img\\/add-ons\\/file-uploads.png","content":"Upload files to WordPress, Google Drive, Dropbox, or Amazon S3. Upload documents, images, media, and more. Easily control file type and size. Add an upload field to any form!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/file-uploads\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=File+Uploads","plugin":"ninja-forms-uploads\\/file-uploads.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/file-uploads\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=File+Uploads+Docs","version":"3.3.5","categories":[{"name":"Content Management","slug":"content-management"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Business","slug":"business"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"File Management","slug":"file-management"}]},{"title":"Layout and Styles","image":"assets\\/img\\/add-ons\\/layout-styles.png","content":"Drag and drop fields into columns and rows. Resize fields. Add backgrounds, adjust borders, and more. Design gorgeous forms without being a designer!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/layouts-and-styles\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Layout+and+Styles","plugin":"ninja-forms-style\\/ninja-forms-style.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/layouts-and-styles\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Layout+and+Styles+Docs","version":"3.0.28","categories":[{"name":"Look &amp; Feel","slug":"look-feel"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Business","slug":"business"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"Form Function and Design","slug":"form-function-design"}]},{"title":"Mailchimp","image":"assets\\/img\\/add-ons\\/mail-chimp.png","content":"Bring new life to your lists with upgraded Mailchimp signup forms for WordPress! Easy to build and customize with no code required. Link to lists and interest groups!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/mailchimp\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Mailchimp","plugin":"ninja-forms-mail-chimp\\/ninja-forms-mail-chimp.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/mailchimp\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Mailchimp+Docs","version":"3.1.11","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Business","slug":"business"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"Campaign Monitor","image":"assets\\/img\\/add-ons\\/campaign-monitor.png","content":"Make any form a custom crafted WordPress signup form for Campaign Monitor. Connect to any list, link form fields to list fields, and watch your lists grow!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/campaign-monitor\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Campaign+Monitor","plugin":"ninja-forms-campaign-monitor\\/ninja-forms-campaign-monitor.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/campaign-monitor\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Campaign+Monitor+Docs","version":"3.0.5","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Membership","slug":"membership"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"User Analytics","image":"assets\\/img\\/add-ons\\/user-analytics.png","content":"Get better data on where your form traffic is coming from with every submission. Add 12+ analytics fields including UTM values,  URL referrer, geo data, and more!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/user-analytics\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=User+Analytics","plugin":"ninja-forms-user-analytics\\/ninja-forms-user-analytics.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/user-analytics\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=User+Analytics+Docs","version":"3.0.0","categories":[{"name":"Content Management","slug":"content-management"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"},{"name":"User Management","slug":"user-management"}]},{"title":"Constant Contact","image":"assets\\/img\\/add-ons\\/constant-contact.png","content":"Connect WordPress to Constant Contact with forms that you can build and design just the way you want, no tech skills required! Subscribe users to any list or interest group.","link":"https:\\/\\/ninjaforms.com\\/extensions\\/constant-contact\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Constant+Contact","plugin":"ninja-forms-constant-contact\\/ninja-forms-constant-contact.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/constant-contact\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Constant+Contact+Docs","version":"3.0.4","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Membership","slug":"membership"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"AWeber","image":"assets\\/img\\/add-ons\\/aweber.png","content":"Build your lists faster with easy to design, professional quality WordPress signup forms. No technical skills required. Connect WordPress to AWeber with style!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/aweber\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=AWeber","plugin":"ninja-forms-aweber\\/ninja-forms-aweber.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/aweber\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=AWeber+Docs","version":"3.1.1","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"PayPal Express","image":"assets\\/img\\/add-ons\\/paypal-express.png","content":"Set up any form to accept PayPal payments with PayPal Express for WordPress! Base totals on a fixed amount, user entered amount, or a calculated total.","link":"https:\\/\\/ninjaforms.com\\/extensions\\/paypal-express\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=PayPal+Express","plugin":"ninja-forms-paypal-express\\/ninja-forms-paypal-express.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/paypal-express\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=PayPal+Express+Docs","version":"3.0.15","categories":[{"name":"Payment Gateways","slug":"payment-gateways"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Business","slug":"business"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"MailPoet","image":"assets\\/img\\/add-ons\\/mailpoet.png","content":"Say hello better! Customize your MailPoet signup forms to draw more subscribers than ever before. Connect WordPress to any MailPoet list in minutes!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/mailpoet\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=MailPoet","plugin":"ninja-forms-mailpoet\\/nf-mailpoet.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/mailpoet\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=MailPoet+Docs","version":"3.0.0","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"Zoho CRM","image":"assets\\/img\\/add-ons\\/zoho-crm.png","content":"Customize your forms to get the most out of your connection between WordPress and Zoho. Link form fields directly to Zoho fields, custom fields included, from almost any module.","link":"https:\\/\\/ninjaforms.com\\/extensions\\/zoho-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Zoho+CRM","plugin":"ninja-forms-zoho-crm\\/ninja-forms-zoho-crm.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/zoho-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Zoho+CRM+Docs","version":"3.4","categories":[{"name":"CRM Integrations","slug":"crm-integrations"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"Capsule CRM","image":"assets\\/img\\/add-ons\\/capsule-crm.png","content":"Boost conversions from WordPress to Capsule with forms tailor made to your audience. Link form fields to Capsule fields from a wide range of modules. Custom fields too!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/capsule-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Capsule+CRM","plugin":"ninja-forms-capsule-crm\\/ninja-forms-capsule-crm.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/capsule-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Capsule+CRM+Docs","version":"3.4.0","categories":[{"name":"CRM Integrations","slug":"crm-integrations"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"Stripe","image":"assets\\/img\\/add-ons\\/stripe.png","content":"Set up any WordPress form to accept credit card payments or donations through Stripe. Base totals on a fixed amount, user entered amount, or a calculated total!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/stripe\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Stripe","plugin":"ninja-forms-stripe\\/ninja-forms-stripe.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/stripe\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Stripe+Docs","version":"3.1.3","categories":[{"name":"Payment Gateways","slug":"payment-gateways"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"Insightly CRM","image":"assets\\/img\\/add-ons\\/insightly-crm.png","content":"Your customer\'s journey begins with your WordPress forms. Send Contacts, Leads, Opportunities, Custom fields and more seamlessly from WordPress to Insightly!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/insightly-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Insightly+CRM","plugin":"ninja-forms-insightly-crm\\/ninja-forms-insightly-crm.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/insightly-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Insightly+CRM+Docs","version":"3.2.0","categories":[{"name":"CRM Integrations","slug":"crm-integrations"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"PDF Form Submission","image":"assets\\/img\\/add-ons\\/pdf-form-submission.png","content":"Generate a PDF of any WordPress form submission. Export any submission as a PDF, or attach it to an email and send a copy to whoever needs one!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/pdf\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=PDF+Form+Submission","plugin":"ninja-forms-pdf-submissions\\/nf-pdf-submissions.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/pdf\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=PDF+Form+Submission+Docs","version":"3.1.2","categories":[{"name":"Content Management","slug":"content-management"},{"name":"Membership","slug":"membership"},{"name":"Business","slug":"business"},{"name":"Agency","slug":"agency"},{"name":"File Management","slug":"file-management"}]},{"title":"Trello","image":"assets\\/img\\/add-ons\\/trello.png","content":"Create a new Trello card with data from any WordPress form submission. Map fields to card details, assign members and labels, upload images, embed links.","link":"https:\\/\\/ninjaforms.com\\/extensions\\/trello\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Trello","plugin":"ninja-forms-trello\\/ninja-forms-trello.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/trello\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Trello+Docs","version":"3.0.3","categories":[{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"},{"name":"Notification and Workflow","slug":"notification-workflow"}]},{"title":"Elavon","image":"assets\\/img\\/add-ons\\/elavon.png","content":"Accept credit card payments from any of your WordPress forms. Pass customer and invoice info from any field securely into Elavon with each payment.","link":"https:\\/\\/ninjaforms.com\\/extensions\\/elavon\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Elavon","plugin":"ninja-forms-elavon-payment-gateway\\/ninja-forms-elavon-payment-gateway.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/elavon\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Elavon+Docs","version":"3.1.0","categories":[{"name":"Payment Gateways","slug":"payment-gateways"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"Zapier","image":"assets\\/img\\/add-ons\\/zapier.png","content":"Don\'t see an add-on integration for a service you love? Don\'t worry! Connect WordPress to more than 1,500 different services through Zapier, no code required!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/zapier\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Zapier","plugin":"ninja-forms-zapier\\/ninja-forms-zapier.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/zapier\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Zapier+Docs","version":"3.0.8","categories":[{"name":"Membership","slug":"membership"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"File Management","slug":"file-management"},{"name":"Notification and Workflow","slug":"notification-workflow"},{"name":"Custom Integrations","slug":"custom-integrations"}]},{"title":"Salesforce CRM","image":"assets\\/img\\/add-ons\\/salesforce-crm.png","content":"Easily map any form field to any Salesforce Object or Field. A better connection to your customers begins with a better WordPress form builder!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/salesforce-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Salesforce+CRM","plugin":"ninja-forms-salesforce-crm\\/ninja-forms-salesforce-crm.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/salesforce-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Salesforce+CRM+Docs","version":"3.2.0","categories":[{"name":"CRM Integrations","slug":"crm-integrations"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"Slack","image":"assets\\/img\\/add-ons\\/slack.png","content":"Get realtime Slack notifications in the workspace and channel of your choice with any new WordPress form submission. @Mention any team member!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/slack\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Slack","plugin":"ninja-forms-slack\\/ninja-forms-slack.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/slack\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Slack+Docs","version":"3.0.3","categories":[{"name":"Notifications","slug":"notifications"},{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"},{"name":"Notification and Workflow","slug":"notification-workflow"}]},{"title":"CleverReach","image":"assets\\/img\\/add-ons\\/cleverreach.png","content":"Grow the reach of your email marketing with better CleverReach signup forms. Tailor your forms to your audience with this easy to set up integration!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/cleverreach\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=CleverReach","plugin":"ninja-forms-cleverreach\\/ninja-forms-cleverreach.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/cleverreach\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=CleverReach+Docs","version":"3.1.3","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"Webhooks","image":"assets\\/img\\/add-ons\\/webhooks.png","content":"Can\'t find a WordPress integration for the service you love? Send WordPress forms data to any external URL using a simple GET or POST request!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/webhooks\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Webhooks","plugin":"ninja-forms-webhooks\\/ninja-forms-webhooks.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/webhooks\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Webhooks+Docs","version":"3.0.5","categories":[{"name":"Notifications","slug":"notifications"},{"name":"Actions","slug":"actions"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"Custom Integrations","slug":"custom-integrations"}]},{"title":"Excel Export","image":"assets\\/img\\/add-ons\\/excel-export.png","content":"Export any form\'s submissions as a Microsoft Excel spreadsheet. Choose a date range, the fields you want to include, and export to Excel! ","link":"https:\\/\\/ninjaforms.com\\/extensions\\/excel-export\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Excel+Export","plugin":"ninja-forms-excel-export\\/ninja-forms-excel-export.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/excel-export\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Excel+Export+Docs","version":"3.3.1","categories":[{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"},{"name":"File Management","slug":"file-management"}]},{"title":"WebMerge","image":"assets\\/img\\/add-ons\\/webmerge.png","content":"Create specifically formatted templates from an uploaded PDF or Word document, then auto-fill them from any WordPress form submission!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/webmerge\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=WebMerge","plugin":"ninja-forms-webmerge\\/ninja-forms-webmerge.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/webmerge\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=WebMerge+Docs","version":"3.0.3","categories":[{"name":"Content Management","slug":"content-management"},{"name":"Actions","slug":"actions"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Agency","slug":"agency"},{"name":"File Management","slug":"file-management"}]},{"title":"Help Scout","image":"assets\\/img\\/add-ons\\/help-scout.png","content":"Offering great support is hard. Tailor your WordPress forms to match your customers\' needs with this Help Scout integration for WordPress.","link":"https:\\/\\/ninjaforms.com\\/extensions\\/help-scout\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Help+Scout","plugin":null,"docs":"https:\\/\\/ninjaforms.com\\/docs\\/help-scout\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Help+Scout+Docs","version":"3.1.3","categories":[{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"},{"name":"User Management","slug":"user-management"}]},{"title":"Emma","image":"assets\\/img\\/add-ons\\/emma.png","content":"Take your email marketing further with handcrafted, easy to build signup forms that connect directly into your Emma account! ","link":"https:\\/\\/ninjaforms.com\\/extensions\\/emma\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Emma","plugin":"ninja-forms-emma\\/ninja-forms-emma.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/emma\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Emma+Docs","version":"3.0.4","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Actions","slug":"actions"},{"name":"Developer","slug":"developer"},{"name":"Membership","slug":"membership"},{"name":"User","slug":"user"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"ClickSend SMS","image":"assets\\/img\\/add-ons\\/clicksend-sms.png","content":"Get instant SMS notifications with every new WordPress form submission. Respond to leads faster and make more personal connections!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/clicksend-sms\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=ClickSend+SMS","plugin":"ninja-forms-clicksend\\/ninja-forms-clicksend.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/clicksend-sms\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=ClickSend+SMS+Docs","version":"3.0.1","categories":[{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"},{"name":"Notification and Workflow","slug":"notification-workflow"}]},{"title":"Twilio SMS","image":"assets\\/img\\/add-ons\\/twilio-sms.png","content":"Get instant SMS notifications with every new WordPress form submission. Respond to leads faster and make more personal connections!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/twilio\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Twilio+SMS","plugin":"ninja-forms-twilio\\/ninja-forms-twilio.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/twilio\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Twilio+SMS+Docs","version":"3.0.1","categories":[{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"},{"name":"Notification and Workflow","slug":"notification-workflow"}]},{"title":"Recurly","image":"assets\\/img\\/add-ons\\/recurly.png","content":"Subscription plans a part of your business model? Let your users subscribe from any WordPress form & make management easier with Recurly!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/recurly\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Recurly","plugin":"ninja-forms-recurly\\/ninja-forms-recurly.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/recurly\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Recurly+Docs","version":"3.0.4","categories":[{"name":"Payment Gateways","slug":"payment-gateways"},{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"User Management","image":"assets\\/img\\/add-ons\\/user-management.png","content":"Allow your users to register, login, and manage their own profiles on your website. Customizable template forms for each, or design your own!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/user-management\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=User+Management","plugin":"ninja-forms-user-management\\/ninja-forms-user-management.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/user-management\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=User+Management+Docs","version":"3.0.12","categories":[{"name":"Content Management","slug":"content-management"},{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"User Management","slug":"user-management"}]},{"title":"Save Progress","image":"assets\\/img\\/add-ons\\/save-progress.png","content":"Let your users save their work and reload it all when they have time to return. Don\'t lose out on valuable submissions for longer forms!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/save-progress\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Save+Progress","plugin":"ninja-forms-save-progress\\/ninja-forms-save-progress.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/save-progress\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Save+Progress+Docs","version":"3.0.24.2","categories":[{"name":"Content Management","slug":"content-management"},{"name":"Membership","slug":"membership"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"},{"name":"Form Function and Design","slug":"form-function-design"}]},{"title":"EmailOctopus","image":"assets\\/img\\/add-ons\\/emailoctopus.png","content":"Pair WordPress\' best drag and drop form builder with your EmailOctopus account for incredibly effective signup forms. Easy, complete integration.","link":"https:\\/\\/ninjaforms.com\\/extensions\\/emailoctopus\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=EmailOctopus","plugin":"ninja-forms-emailoctopus\\/ninja-forms-emailoctopus.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/emailoctopus\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=EmailOctopus+Docs","version":"3.0.0","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Business","slug":"business"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"PipelineDeals CRM","image":"assets\\/img\\/add-ons\\/pipelinedeals-crm.png","content":"Complete, effortless integration with PipelineDeals CRM. Increase the flow of leads into your sales pipeline with upgraded lead generation forms!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/pipelinedeals-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=PipelineDeals+CRM","plugin":"ninja-forms-zoho-crm\\/zoho-integration.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/pipelinedeals-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=PipelineDeals+CRM+Docs","version":"3.0.1","categories":[{"name":"CRM Integrations","slug":"crm-integrations"},{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"Highrise CRM","image":"assets\\/img\\/add-ons\\/highrise-crm.png","content":"Get more out of the functional simplicity of Highrise CRM with forms that can be designed from the ground up to maximize conversion. ","link":"https:\\/\\/ninjaforms.com\\/extensions\\/highrise-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Highrise+CRM","plugin":"ninja-forms-highrise-crm\\/ninja-forms-highrise-crm.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/highrise-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=Highrise+CRM+Docs","version":"3.0.0","categories":[{"name":"CRM Integrations","slug":"crm-integrations"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"ConvertKit","image":"assets\\/img\\/add-ons\\/convertkit.png","content":"Connect WordPress to your ConvertKit account with completely customizable opt-in forms. Watch your audience & sales grow like never before!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/convertkit\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=ConvertKit","plugin":"ninja-forms-convertkit\\/ninja-forms-convertkit.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/convertkit\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=ConvertKit+Docs","version":"3.0.2","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Membership","slug":"membership"},{"name":"Personal","slug":"personal"},{"name":"Professional","slug":"professional"},{"name":"Agency","slug":"agency"}]},{"title":"OnePageCRM","image":"assets\\/img\\/add-ons\\/onepage-crm.png","content":"Integrate WordPress with OnePage CRM seamlessly through highly customizable WordPress forms. Make better conversions <em>your<\\/em> Next Action!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/onepage-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=OnePageCRM","plugin":"ninja-forms-onepage-crm\\/ninja-forms-onepage-crm.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/onepage-crm\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=OnePageCRM+Docs","version":"3.0.0","categories":[{"name":"CRM Integrations","slug":"crm-integrations"},{"name":"Actions","slug":"actions"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]},{"title":"ActiveCampaign","image":"assets\\/img\\/add-ons\\/active-campaign.png","content":"Design custom forms that link perfectly to your ActiveCampaign account for the ultimate in marketing automation. Better leads begin here!","link":"https:\\/\\/ninjaforms.com\\/extensions\\/activecampaign\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=ActiveCampaign","plugin":"ninja-forms-active-campaign\\/ninja-forms-active-campaign.php","docs":"https:\\/\\/ninjaforms.com\\/docs\\/activecampaign\\/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Forms+Addons+Page&utm_content=ActiveCampaign+Docs","version":"3.0.6","categories":[{"name":"Email Marketing","slug":"email-marketing"},{"name":"Membership","slug":"membership"},{"name":"Agency","slug":"agency"}]}]', 'no'),
(421, 'resource-categories_children', 'a:0:{}', 'yes'),
(431, 'duplicate_post_copytitle', '1', 'yes'),
(432, 'duplicate_post_copydate', '', 'yes'),
(433, 'duplicate_post_copystatus', '', 'yes'),
(434, 'duplicate_post_copyslug', '', 'yes'),
(435, 'duplicate_post_copyexcerpt', '1', 'yes'),
(436, 'duplicate_post_copycontent', '1', 'yes'),
(437, 'duplicate_post_copythumbnail', '1', 'yes'),
(438, 'duplicate_post_copytemplate', '1', 'yes'),
(439, 'duplicate_post_copyformat', '1', 'yes'),
(440, 'duplicate_post_copyauthor', '', 'yes'),
(441, 'duplicate_post_copypassword', '', 'yes'),
(442, 'duplicate_post_copyattachments', '', 'yes'),
(443, 'duplicate_post_copychildren', '', 'yes'),
(444, 'duplicate_post_copycomments', '', 'yes'),
(445, 'duplicate_post_copymenuorder', '1', 'yes'),
(446, 'duplicate_post_taxonomies_blacklist', '', 'yes'),
(447, 'duplicate_post_blacklist', '', 'yes'),
(448, 'duplicate_post_types_enabled', 'a:3:{i:0;s:4:"post";i:1;s:4:"page";i:2;s:11:"help-advice";}', 'yes'),
(449, 'duplicate_post_show_row', '1', 'yes'),
(450, 'duplicate_post_show_adminbar', '1', 'yes'),
(451, 'duplicate_post_show_submitbox', '1', 'yes'),
(452, 'duplicate_post_show_bulkactions', '1', 'yes'),
(453, 'duplicate_post_show_original_column', '', 'yes'),
(454, 'duplicate_post_show_original_in_post_states', '', 'yes'),
(455, 'duplicate_post_show_original_meta_box', '', 'yes'),
(465, 'help-advice-categories_children', 'a:0:{}', 'yes'),
(471, 'duplicate_post_title_prefix', '', 'yes'),
(472, 'duplicate_post_title_suffix', '', 'yes'),
(473, 'duplicate_post_increase_menu_order_by', '', 'yes'),
(474, 'duplicate_post_roles', 'a:2:{i:0;s:13:"administrator";i:1;s:6:"editor";}', 'yes'),
(528, 'options_logo_horizontal', '211', 'no'),
(529, '_options_logo_horizontal', 'field_5eff30fe23483', 'no'),
(530, 'options_logo_vertical', '57', 'no'),
(531, '_options_logo_vertical', 'field_5eff311623485', 'no'),
(532, 'options_logo_horizontal_white', '60', 'no'),
(533, '_options_logo_horizontal_white', 'field_5eff312323486', 'no'),
(534, 'options_logo_vertical_white', '58', 'no'),
(535, '_options_logo_vertical_white', 'field_5eff312e23487', 'no'),
(642, 'options_site_address_0_address_line', '4-A Canberra House,  ', 'no'),
(643, '_options_site_address_0_address_line', 'field_58457eceed543', 'no'),
(644, 'options_site_address_1_address_line', 'London Road,', 'no'),
(645, '_options_site_address_1_address_line', 'field_58457eceed543', 'no'),
(646, 'options_site_address_2_address_line', 'St. Albans', 'no'),
(647, '_options_site_address_2_address_line', 'field_58457eceed543', 'no'),
(738, 'options_buckets_0_title', 'Sell your home', 'no'),
(739, '_options_buckets_0_title', 'field_5eff6eb357b83', 'no'),
(740, 'options_buckets_0_intro', 'Selling your home is a big decision, and you need people you can trust to facilitate it...', 'no'),
(741, '_options_buckets_0_intro', 'field_5eff6eb957b84', 'no'),
(742, 'options_buckets_0_background_image', '120', 'no'),
(743, '_options_buckets_0_background_image', 'field_5eff6ec557b85', 'no'),
(744, 'options_buckets_0_button_text', 'Get an instant valuation', 'no'),
(745, '_options_buckets_0_button_text', 'field_5eff6ee857b87', 'no'),
(746, 'options_buckets_0_button_link', 'a:3:{s:5:"title";s:14:"Sell your home";s:3:"url";s:41:"http://fullbrook-floor.vm/sell-your-home/";s:6:"target";s:0:"";}', 'no'),
(747, '_options_buckets_0_button_link', 'field_5eff6ed357b86', 'no'),
(748, 'options_buckets_1_title', 'Buy your next home', 'no'),
(749, '_options_buckets_1_title', 'field_5eff6eb357b83', 'no'),
(750, 'options_buckets_1_intro', 'The best properties in St. Albans, Chiswell Green, Bricket Wood and beyond. If you’re looking for your dream home, we can help you secure it...', 'no'),
(751, '_options_buckets_1_intro', 'field_5eff6eb957b84', 'no'),
(752, 'options_buckets_1_background_image', '119', 'no'),
(753, '_options_buckets_1_background_image', 'field_5eff6ec557b85', 'no'),
(754, 'options_buckets_1_button_text', 'View properties', 'no'),
(755, '_options_buckets_1_button_text', 'field_5eff6ee857b87', 'no'),
(756, 'options_buckets_1_button_link', 'a:3:{s:5:"title";s:10:"Buy a home";s:3:"url";s:37:"http://fullbrook-floor.vm/buy-a-home/";s:6:"target";s:0:"";}', 'no'),
(757, '_options_buckets_1_button_link', 'field_5eff6ed357b86', 'no'),
(758, 'options_buckets_2_title', 'How to sell a home', 'no'),
(759, '_options_buckets_2_title', 'field_5eff6eb357b83', 'no'),
(760, 'options_buckets_2_intro', 'Sell with a friendly local estate agent and let us help to guide you through the process...', 'no'),
(761, '_options_buckets_2_intro', 'field_5eff6eb957b84', 'no'),
(762, 'options_buckets_2_background_image', '121', 'no'),
(763, '_options_buckets_2_background_image', 'field_5eff6ec557b85', 'no'),
(764, 'options_buckets_2_button_text', 'Read our sales guide', 'no'),
(765, '_options_buckets_2_button_text', 'field_5eff6ee857b87', 'no'),
(766, 'options_buckets_2_button_link', 'a:3:{s:5:"title";s:18:"How to sell a home";s:3:"url";s:45:"http://fullbrook-floor.vm/how-to-sell-a-home/";s:6:"target";s:0:"";}', 'no'),
(767, '_options_buckets_2_button_link', 'field_5eff6ed357b86', 'no'),
(768, 'options_buckets', '3', 'no'),
(769, '_options_buckets', 'field_5eff6ea657b82', 'no'),
(1015, 'options_property_placeholder', '122', 'no'),
(1016, '_options_property_placeholder', 'field_5f04e87af1efe', 'no'),
(1037, 'options_why_choose_us_0_image', '57', 'no'),
(1038, '_options_why_choose_us_0_image', 'field_5f04e471f4a3f', 'no'),
(1039, 'options_why_choose_us_0_title', 'Powerful online strategy', 'no'),
(1040, '_options_why_choose_us_0_title', 'field_5f04e477f4a40', 'no'),
(1041, 'options_why_choose_us_0_text', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor ultrices nibh, id mollis turpis lobortis sed. Mauris a consequat ex.', 'no') ;
INSERT INTO `league_options` ( `option_id`, `option_name`, `option_value`, `autoload`) VALUES
(1042, '_options_why_choose_us_0_text', 'field_5f04e47cf4a41', 'no'),
(1043, 'options_why_choose_us_1_image', '57', 'no'),
(1044, '_options_why_choose_us_1_image', 'field_5f04e471f4a3f', 'no'),
(1045, 'options_why_choose_us_1_title', 'Powerful online strategy', 'no'),
(1046, '_options_why_choose_us_1_title', 'field_5f04e477f4a40', 'no'),
(1047, 'options_why_choose_us_1_text', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor ultrices nibh, id mollis turpis lobortis sed. Mauris a consequat ex.', 'no'),
(1048, '_options_why_choose_us_1_text', 'field_5f04e47cf4a41', 'no'),
(1049, 'options_why_choose_us_2_image', '57', 'no'),
(1050, '_options_why_choose_us_2_image', 'field_5f04e471f4a3f', 'no'),
(1051, 'options_why_choose_us_2_title', 'Powerful online strategy', 'no'),
(1052, '_options_why_choose_us_2_title', 'field_5f04e477f4a40', 'no'),
(1053, 'options_why_choose_us_2_text', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor ultrices nibh, id mollis turpis lobortis sed. Mauris a consequat ex.', 'no'),
(1054, '_options_why_choose_us_2_text', 'field_5f04e47cf4a41', 'no'),
(1055, 'options_why_choose_us_3_image', '57', 'no'),
(1056, '_options_why_choose_us_3_image', 'field_5f04e471f4a3f', 'no'),
(1057, 'options_why_choose_us_3_title', 'Powerful online strategy', 'no'),
(1058, '_options_why_choose_us_3_title', 'field_5f04e477f4a40', 'no'),
(1059, 'options_why_choose_us_3_text', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor ultrices nibh, id mollis turpis lobortis sed. Mauris a consequat ex.', 'no'),
(1060, '_options_why_choose_us_3_text', 'field_5f04e47cf4a41', 'no'),
(1061, 'options_why_choose_us_4_image', '57', 'no'),
(1062, '_options_why_choose_us_4_image', 'field_5f04e471f4a3f', 'no'),
(1063, 'options_why_choose_us_4_title', 'Powerful online strategy', 'no'),
(1064, '_options_why_choose_us_4_title', 'field_5f04e477f4a40', 'no'),
(1065, 'options_why_choose_us_4_text', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor ultrices nibh, id mollis turpis lobortis sed. Mauris a consequat ex.', 'no'),
(1066, '_options_why_choose_us_4_text', 'field_5f04e47cf4a41', 'no'),
(1070, 'recovery_mode_email_last_sent', '1595531942', 'yes'),
(1071, 'wp_mail_smtp_debug', 'a:1:{i:0;s:110:"Mailer: Default (none)\r\nPHPMailer was able to connect to SMTP server but failed while trying to send an email.";}', 'no'),
(1117, 'wpmdb_usage', 'a:2:{s:6:"action";s:4:"pull";s:4:"time";i:1595532273;}', 'no'),
(1148, 'options_logos_0_name', 'The property ombudsman', 'no'),
(1149, '_options_logos_0_name', 'field_58482bd942e13', 'no'),
(1150, 'options_logos_0_image', '137', 'no'),
(1151, '_options_logos_0_image', 'field_58482be442e14', 'no'),
(1152, 'options_logos_0_link', '', 'no'),
(1153, '_options_logos_0_link', 'field_58482c0442e15', 'no'),
(1154, 'options_logos_1_name', 'Propertymark', 'no'),
(1155, '_options_logos_1_name', 'field_58482bd942e13', 'no'),
(1156, 'options_logos_1_image', '138', 'no'),
(1157, '_options_logos_1_image', 'field_58482be442e14', 'no'),
(1158, 'options_logos_1_link', '', 'no'),
(1159, '_options_logos_1_link', 'field_58482c0442e15', 'no'),
(1413, 'nf_form_tel_sent', 'true', 'no'),
(1415, 'nf_sub_expiration', 'a:0:{}', 'yes'),
(1615, 'category_children', 'a:0:{}', 'yes'),
(2089, 'options_mover_logo_headline', 'Get your property featured on...', 'no'),
(2090, '_options_mover_logo_headline', 'field_5f0f3585af5a5', 'no'),
(2091, 'options_mover_logos_0_name', 'Rightmove', 'no'),
(2092, '_options_mover_logos_0_name', 'field_5f0f35a3af5a7', 'no'),
(2093, 'options_mover_logos_0_logo', '197', 'no'),
(2094, '_options_mover_logos_0_logo', 'field_5f0f35acaf5a8', 'no'),
(2095, 'options_mover_logos_0_link', 'https://www.rightmove.co.uk/', 'no'),
(2096, '_options_mover_logos_0_link', 'field_5f0f35b1af5a9', 'no'),
(2097, 'options_mover_logos_1_name', 'Zoopla', 'no'),
(2098, '_options_mover_logos_1_name', 'field_5f0f35a3af5a7', 'no'),
(2099, 'options_mover_logos_1_logo', '198', 'no'),
(2100, '_options_mover_logos_1_logo', 'field_5f0f35acaf5a8', 'no'),
(2101, 'options_mover_logos_1_link', 'https://www.zoopla.co.uk/', 'no'),
(2102, '_options_mover_logos_1_link', 'field_5f0f35b1af5a9', 'no'),
(2103, 'options_mover_logos', '2', 'no'),
(2104, '_options_mover_logos', 'field_5f0f359caf5a6', 'no'),
(2165, 'blog_public', '1', 'yes'),
(2166, 'upload_path', '', 'yes'),
(2167, 'upload_url_path', '', 'yes'),
(2295, 'wpmdb_settings', 'a:13:{s:3:"key";s:40:"enYjA8MSDsd6P4bIyZQX1NWYRgPRL94d6DBNRQ3G";s:10:"allow_pull";b:0;s:10:"allow_push";b:0;s:8:"profiles";a:1:{i:0;a:22:{s:13:"save_computer";s:1:"1";s:9:"gzip_file";s:1:"1";s:13:"replace_guids";s:1:"1";s:12:"exclude_spam";s:1:"0";s:19:"keep_active_plugins";s:1:"0";s:13:"create_backup";s:1:"1";s:18:"exclude_post_types";s:1:"0";s:18:"exclude_transients";s:1:"1";s:25:"compatibility_older_mysql";s:1:"0";s:19:"import_find_replace";s:1:"1";s:6:"action";s:4:"pull";s:15:"connection_info";s:98:"https://rodrene:cosyhome@staging.fullbrookandfloor.co.uk\r\nnp55Du1k/7KbXVEq+RgE/ND0PGU0RuIvVfklZSo/";s:11:"replace_old";a:1:{i:1;s:33:"//staging.fullbrookandfloor.co.uk";}s:11:"replace_new";a:1:{i:1;s:20:"//fullbrook-floor.vm";}s:20:"table_migrate_option";s:24:"migrate_only_with_prefix";s:13:"backup_option";s:23:"backup_only_with_prefix";s:11:"media_files";s:1:"1";s:22:"media_migration_option";s:7:"compare";s:22:"save_migration_profile";s:1:"1";s:29:"save_migration_profile_option";s:3:"new";s:18:"create_new_profile";s:24:"Pull // Staging -> Local";s:4:"name";s:24:"Pull // Staging -> Local";}}s:7:"licence";s:36:"d7d11773-7dd5-440e-94d3-d7694f0e925b";s:10:"verify_ssl";b:0;s:17:"whitelist_plugins";a:0:{}s:11:"max_request";i:1048576;s:22:"delay_between_requests";i:0;s:18:"prog_tables_hidden";b:1;s:21:"pause_before_finalize";b:0;s:14:"allow_tracking";N;s:28:"compatibility_plugin_version";s:3:"1.2";}', 'no'),
(2296, '_transient_timeout_wpseo_link_table_inaccessible', '1627067942', 'no'),
(2297, '_transient_wpseo_link_table_inaccessible', '0', 'no'),
(2298, '_transient_timeout_wpseo_meta_table_inaccessible', '1627067942', 'no'),
(2299, '_transient_wpseo_meta_table_inaccessible', '0', 'no'),
(2304, '_transient_timeout_acf_plugin_updates', '1595704827', 'no'),
(2305, '_transient_acf_plugin_updates', 'a:4:{s:7:"plugins";a:0:{}s:10:"expiration";i:172800;s:6:"status";i:1;s:7:"checked";a:1:{s:34:"advanced-custom-fields-pro/acf.php";s:6:"5.8.12";}}', 'no'),
(2306, '_site_transient_timeout_theme_roots', '1595533827', 'no'),
(2307, '_site_transient_theme_roots', 'a:2:{s:13:"adtrak-parent";s:7:"/themes";s:15:"fullbrook-floor";s:7:"/themes";}', 'no'),
(2311, '_site_transient_update_core', 'O:8:"stdClass":4:{s:7:"updates";a:1:{i:0;O:8:"stdClass":10:{s:8:"response";s:6:"latest";s:8:"download";s:65:"https://downloads.wordpress.org/release/en_GB/wordpress-5.4.2.zip";s:6:"locale";s:5:"en_GB";s:8:"packages";O:8:"stdClass":5:{s:4:"full";s:65:"https://downloads.wordpress.org/release/en_GB/wordpress-5.4.2.zip";s:10:"no_content";b:0;s:11:"new_bundled";b:0;s:7:"partial";b:0;s:8:"rollback";b:0;}s:7:"current";s:5:"5.4.2";s:7:"version";s:5:"5.4.2";s:11:"php_version";s:6:"5.6.20";s:13:"mysql_version";s:3:"5.0";s:11:"new_bundled";s:3:"5.3";s:15:"partial_version";s:0:"";}}s:12:"last_checked";i:1595532108;s:15:"version_checked";s:5:"5.4.2";s:12:"translations";a:1:{i:0;a:7:{s:4:"type";s:4:"core";s:4:"slug";s:7:"default";s:8:"language";s:5:"en_GB";s:7:"version";s:5:"5.4.2";s:7:"updated";s:19:"2020-07-14 17:55:29";s:7:"package";s:64:"https://downloads.wordpress.org/translation/core/5.4.2/en_GB.zip";s:10:"autoupdate";b:1;}}}', 'no'),
(2312, '_site_transient_update_themes', 'O:8:"stdClass":4:{s:12:"last_checked";i:1595532141;s:7:"checked";a:2:{s:13:"adtrak-parent";s:5:"2.1.3";s:15:"fullbrook-floor";s:5:"1.0.0";}s:8:"response";a:0:{}s:12:"translations";a:0:{}}', 'no'),
(2314, '_site_transient_timeout_php_check_7ddb89c02f1abf791c6717dc46cef1eb', '1596136831', 'no'),
(2315, '_site_transient_php_check_7ddb89c02f1abf791c6717dc46cef1eb', 'a:5:{s:19:"recommended_version";s:3:"7.4";s:15:"minimum_version";s:6:"5.6.20";s:12:"is_supported";b:1;s:9:"is_secure";b:1;s:13:"is_acceptable";b:1;}', 'no'),
(2316, '_transient_health-check-site-status-result', '{"good":"7","recommended":"10","critical":"0"}', 'yes'),
(2320, '_site_transient_timeout_wpmdb_upgrade_data', '1595575262', 'no'),
(2321, '_site_transient_wpmdb_upgrade_data', 'a:5:{s:17:"wp-migrate-db-pro";a:3:{s:7:"version";s:6:"1.9.10";s:6:"tested";s:5:"5.4.2";s:12:"beta_version";s:5:"2.0b4";}s:29:"wp-migrate-db-pro-media-files";a:2:{s:7:"version";s:6:"1.4.15";s:6:"tested";s:5:"5.4.2";}s:21:"wp-migrate-db-pro-cli";a:2:{s:7:"version";s:5:"1.3.5";s:6:"tested";s:5:"5.4.2";}s:33:"wp-migrate-db-pro-multisite-tools";a:2:{s:7:"version";s:5:"1.2.6";s:6:"tested";s:5:"5.4.2";}s:36:"wp-migrate-db-pro-theme-plugin-files";a:2:{s:7:"version";s:5:"1.0.5";s:6:"tested";s:5:"5.4.2";}}', 'no'),
(2322, 'wpmdb_schema_version', '2', 'no'),
(2323, '_site_transient_timeout_browser_5fa42a5ced972bb6d5ae8800e98bebfb', '1596136863', 'no'),
(2324, '_site_transient_browser_5fa42a5ced972bb6d5ae8800e98bebfb', 'a:10:{s:4:"name";s:6:"Chrome";s:7:"version";s:12:"84.0.4147.89";s:8:"platform";s:7:"Windows";s:10:"update_url";s:29:"https://www.google.com/chrome";s:7:"img_src";s:43:"http://s.w.org/images/browsers/chrome.png?1";s:11:"img_src_ssl";s:44:"https://s.w.org/images/browsers/chrome.png?1";s:15:"current_version";s:2:"18";s:7:"upgrade";b:0;s:8:"insecure";b:0;s:6:"mobile";b:0;}', 'no'),
(2327, 'duplicate_post_version', '3.2.5', 'yes'),
(2328, 'duplicate_post_show_notice', '0', 'no'),
(2330, '_site_transient_update_plugins', 'O:8:"stdClass":5:{s:12:"last_checked";i:1595532169;s:7:"checked";a:15:{s:39:"aryo-activity-log/aryo-activity-log.php";s:5:"2.5.2";s:27:"adtrak-core/adtrak-core.php";s:6:"0.9.27";s:35:"adtrak-location-dynamics/plugin.php";s:5:"3.4.3";s:34:"advanced-custom-fields-pro/acf.php";s:6:"5.8.12";s:33:"classic-editor/classic-editor.php";s:3:"1.5";s:27:"ninja-forms/ninja-forms.php";s:8:"3.4.24.3";s:57:"post-to-google-my-business/post-to-google-my-business.php";s:6:"2.2.25";s:43:"public-post-preview/public-post-preview.php";s:5:"2.9.0";s:69:"public-post-preview-configurator/public-post-preview-configurator.php";s:5:"1.0.3";s:27:"redirection/redirection.php";s:3:"4.8";s:29:"wp-mail-smtp/wp_mail_smtp.php";s:5:"2.2.1";s:39:"wp-migrate-db-pro/wp-migrate-db-pro.php";s:6:"1.9.10";s:63:"wp-migrate-db-pro-media-files/wp-migrate-db-pro-media-files.php";s:6:"1.4.15";s:33:"duplicate-post/duplicate-post.php";s:5:"3.2.5";s:24:"wordpress-seo/wp-seo.php";s:6:"14.6.1";}s:8:"response";a:0:{}s:12:"translations";a:8:{i:0;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:17:"aryo-activity-log";s:8:"language";s:5:"en_GB";s:7:"version";s:5:"2.5.2";s:7:"updated";s:19:"2018-10-30 23:15:37";s:7:"package";s:84:"https://downloads.wordpress.org/translation/plugin/aryo-activity-log/2.5.2/en_GB.zip";s:10:"autoupdate";b:1;}i:1;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:14:"classic-editor";s:8:"language";s:5:"en_GB";s:7:"version";s:3:"1.5";s:7:"updated";s:19:"2019-02-02 15:00:14";s:7:"package";s:79:"https://downloads.wordpress.org/translation/plugin/classic-editor/1.5/en_GB.zip";s:10:"autoupdate";b:1;}i:2;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:11:"ninja-forms";s:8:"language";s:5:"en_GB";s:7:"version";s:8:"3.4.24.3";s:7:"updated";s:19:"2020-06-05 12:29:39";s:7:"package";s:81:"https://downloads.wordpress.org/translation/plugin/ninja-forms/3.4.24.3/en_GB.zip";s:10:"autoupdate";b:1;}i:3;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:19:"public-post-preview";s:8:"language";s:5:"en_GB";s:7:"version";s:5:"2.8.0";s:7:"updated";s:19:"2018-11-27 11:39:17";s:7:"package";s:86:"https://downloads.wordpress.org/translation/plugin/public-post-preview/2.8.0/en_GB.zip";s:10:"autoupdate";b:1;}i:4;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:11:"redirection";s:8:"language";s:5:"en_GB";s:7:"version";s:3:"4.8";s:7:"updated";s:19:"2020-06-06 15:55:00";s:7:"package";s:76:"https://downloads.wordpress.org/translation/plugin/redirection/4.8/en_GB.zip";s:10:"autoupdate";b:1;}i:5;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:12:"wp-mail-smtp";s:8:"language";s:5:"en_GB";s:7:"version";s:5:"2.2.1";s:7:"updated";s:19:"2018-11-29 18:52:44";s:7:"package";s:79:"https://downloads.wordpress.org/translation/plugin/wp-mail-smtp/2.2.1/en_GB.zip";s:10:"autoupdate";b:1;}i:6;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:14:"duplicate-post";s:8:"language";s:5:"en_GB";s:7:"version";s:5:"3.2.5";s:7:"updated";s:19:"2020-07-11 12:11:51";s:7:"package";s:81:"https://downloads.wordpress.org/translation/plugin/duplicate-post/3.2.5/en_GB.zip";s:10:"autoupdate";b:1;}i:7;a:7:{s:4:"type";s:6:"plugin";s:4:"slug";s:13:"wordpress-seo";s:8:"language";s:5:"en_GB";s:7:"version";s:6:"14.6.1";s:7:"updated";s:19:"2020-07-21 07:48:59";s:7:"package";s:81:"https://downloads.wordpress.org/translation/plugin/wordpress-seo/14.6.1/en_GB.zip";s:10:"autoupdate";b:1;}}s:9:"no_update";a:10:{s:39:"aryo-activity-log/aryo-activity-log.php";O:8:"stdClass":9:{s:2:"id";s:31:"w.org/plugins/aryo-activity-log";s:4:"slug";s:17:"aryo-activity-log";s:6:"plugin";s:39:"aryo-activity-log/aryo-activity-log.php";s:11:"new_version";s:5:"2.5.2";s:3:"url";s:48:"https://wordpress.org/plugins/aryo-activity-log/";s:7:"package";s:66:"https://downloads.wordpress.org/plugin/aryo-activity-log.2.5.2.zip";s:5:"icons";a:2:{s:2:"2x";s:70:"https://ps.w.org/aryo-activity-log/assets/icon-256x256.png?rev=1944199";s:2:"1x";s:70:"https://ps.w.org/aryo-activity-log/assets/icon-128x128.png?rev=1944199";}s:7:"banners";a:1:{s:2:"1x";s:71:"https://ps.w.org/aryo-activity-log/assets/banner-772x250.png?rev=852698";}s:11:"banners_rtl";a:0:{}}s:33:"classic-editor/classic-editor.php";O:8:"stdClass":9:{s:2:"id";s:28:"w.org/plugins/classic-editor";s:4:"slug";s:14:"classic-editor";s:6:"plugin";s:33:"classic-editor/classic-editor.php";s:11:"new_version";s:3:"1.5";s:3:"url";s:45:"https://wordpress.org/plugins/classic-editor/";s:7:"package";s:61:"https://downloads.wordpress.org/plugin/classic-editor.1.5.zip";s:5:"icons";a:2:{s:2:"2x";s:67:"https://ps.w.org/classic-editor/assets/icon-256x256.png?rev=1998671";s:2:"1x";s:67:"https://ps.w.org/classic-editor/assets/icon-128x128.png?rev=1998671";}s:7:"banners";a:2:{s:2:"2x";s:70:"https://ps.w.org/classic-editor/assets/banner-1544x500.png?rev=1998671";s:2:"1x";s:69:"https://ps.w.org/classic-editor/assets/banner-772x250.png?rev=1998676";}s:11:"banners_rtl";a:0:{}}s:27:"ninja-forms/ninja-forms.php";O:8:"stdClass":9:{s:2:"id";s:25:"w.org/plugins/ninja-forms";s:4:"slug";s:11:"ninja-forms";s:6:"plugin";s:27:"ninja-forms/ninja-forms.php";s:11:"new_version";s:8:"3.4.24.3";s:3:"url";s:42:"https://wordpress.org/plugins/ninja-forms/";s:7:"package";s:63:"https://downloads.wordpress.org/plugin/ninja-forms.3.4.24.3.zip";s:5:"icons";a:2:{s:2:"2x";s:64:"https://ps.w.org/ninja-forms/assets/icon-256x256.png?rev=1649747";s:2:"1x";s:64:"https://ps.w.org/ninja-forms/assets/icon-128x128.png?rev=1649747";}s:7:"banners";a:2:{s:2:"2x";s:67:"https://ps.w.org/ninja-forms/assets/banner-1544x500.png?rev=2069024";s:2:"1x";s:66:"https://ps.w.org/ninja-forms/assets/banner-772x250.png?rev=2069024";}s:11:"banners_rtl";a:0:{}}s:57:"post-to-google-my-business/post-to-google-my-business.php";O:8:"stdClass":9:{s:2:"id";s:40:"w.org/plugins/post-to-google-my-business";s:4:"slug";s:26:"post-to-google-my-business";s:6:"plugin";s:57:"post-to-google-my-business/post-to-google-my-business.php";s:11:"new_version";s:6:"2.2.25";s:3:"url";s:57:"https://wordpress.org/plugins/post-to-google-my-business/";s:7:"package";s:76:"https://downloads.wordpress.org/plugin/post-to-google-my-business.2.2.25.zip";s:5:"icons";a:2:{s:2:"2x";s:79:"https://ps.w.org/post-to-google-my-business/assets/icon-256x256.png?rev=1904100";s:2:"1x";s:79:"https://ps.w.org/post-to-google-my-business/assets/icon-128x128.png?rev=1904100";}s:7:"banners";a:2:{s:2:"2x";s:82:"https://ps.w.org/post-to-google-my-business/assets/banner-1544x500.png?rev=1904100";s:2:"1x";s:81:"https://ps.w.org/post-to-google-my-business/assets/banner-772x250.png?rev=1904100";}s:11:"banners_rtl";a:0:{}}s:43:"public-post-preview/public-post-preview.php";O:8:"stdClass":9:{s:2:"id";s:33:"w.org/plugins/public-post-preview";s:4:"slug";s:19:"public-post-preview";s:6:"plugin";s:43:"public-post-preview/public-post-preview.php";s:11:"new_version";s:5:"2.9.0";s:3:"url";s:50:"https://wordpress.org/plugins/public-post-preview/";s:7:"package";s:68:"https://downloads.wordpress.org/plugin/public-post-preview.2.9.0.zip";s:5:"icons";a:2:{s:2:"2x";s:71:"https://ps.w.org/public-post-preview/assets/icon-256x256.png?rev=970573";s:2:"1x";s:71:"https://ps.w.org/public-post-preview/assets/icon-128x128.png?rev=970573";}s:7:"banners";a:1:{s:2:"1x";s:73:"https://ps.w.org/public-post-preview/assets/banner-772x250.png?rev=575724";}s:11:"banners_rtl";a:0:{}}s:69:"public-post-preview-configurator/public-post-preview-configurator.php";O:8:"stdClass":9:{s:2:"id";s:46:"w.org/plugins/public-post-preview-configurator";s:4:"slug";s:32:"public-post-preview-configurator";s:6:"plugin";s:69:"public-post-preview-configurator/public-post-preview-configurator.php";s:11:"new_version";s:5:"1.0.3";s:3:"url";s:63:"https://wordpress.org/plugins/public-post-preview-configurator/";s:7:"package";s:75:"https://downloads.wordpress.org/plugin/public-post-preview-configurator.zip";s:5:"icons";a:1:{s:7:"default";s:76:"https://s.w.org/plugins/geopattern-icon/public-post-preview-configurator.svg";}s:7:"banners";a:0:{}s:11:"banners_rtl";a:0:{}}s:27:"redirection/redirection.php";O:8:"stdClass":9:{s:2:"id";s:25:"w.org/plugins/redirection";s:4:"slug";s:11:"redirection";s:6:"plugin";s:27:"redirection/redirection.php";s:11:"new_version";s:3:"4.8";s:3:"url";s:42:"https://wordpress.org/plugins/redirection/";s:7:"package";s:58:"https://downloads.wordpress.org/plugin/redirection.4.8.zip";s:5:"icons";a:2:{s:2:"2x";s:63:"https://ps.w.org/redirection/assets/icon-256x256.jpg?rev=983639";s:2:"1x";s:63:"https://ps.w.org/redirection/assets/icon-128x128.jpg?rev=983640";}s:7:"banners";a:2:{s:2:"2x";s:66:"https://ps.w.org/redirection/assets/banner-1544x500.jpg?rev=983641";s:2:"1x";s:65:"https://ps.w.org/redirection/assets/banner-772x250.jpg?rev=983642";}s:11:"banners_rtl";a:0:{}}s:29:"wp-mail-smtp/wp_mail_smtp.php";O:8:"stdClass":9:{s:2:"id";s:26:"w.org/plugins/wp-mail-smtp";s:4:"slug";s:12:"wp-mail-smtp";s:6:"plugin";s:29:"wp-mail-smtp/wp_mail_smtp.php";s:11:"new_version";s:5:"2.2.1";s:3:"url";s:43:"https://wordpress.org/plugins/wp-mail-smtp/";s:7:"package";s:61:"https://downloads.wordpress.org/plugin/wp-mail-smtp.2.2.1.zip";s:5:"icons";a:2:{s:2:"2x";s:65:"https://ps.w.org/wp-mail-smtp/assets/icon-256x256.png?rev=1755440";s:2:"1x";s:65:"https://ps.w.org/wp-mail-smtp/assets/icon-128x128.png?rev=1755440";}s:7:"banners";a:2:{s:2:"2x";s:68:"https://ps.w.org/wp-mail-smtp/assets/banner-1544x500.png?rev=2120094";s:2:"1x";s:67:"https://ps.w.org/wp-mail-smtp/assets/banner-772x250.png?rev=2120094";}s:11:"banners_rtl";a:0:{}}s:33:"duplicate-post/duplicate-post.php";O:8:"stdClass":9:{s:2:"id";s:28:"w.org/plugins/duplicate-post";s:4:"slug";s:14:"duplicate-post";s:6:"plugin";s:33:"duplicate-post/duplicate-post.php";s:11:"new_version";s:5:"3.2.5";s:3:"url";s:45:"https://wordpress.org/plugins/duplicate-post/";s:7:"package";s:63:"https://downloads.wordpress.org/plugin/duplicate-post.3.2.5.zip";s:5:"icons";a:2:{s:2:"2x";s:67:"https://ps.w.org/duplicate-post/assets/icon-256x256.png?rev=2336666";s:2:"1x";s:67:"https://ps.w.org/duplicate-post/assets/icon-128x128.png?rev=2336666";}s:7:"banners";a:2:{s:2:"2x";s:70:"https://ps.w.org/duplicate-post/assets/banner-1544x500.png?rev=2336666";s:2:"1x";s:69:"https://ps.w.org/duplicate-post/assets/banner-772x250.png?rev=2336666";}s:11:"banners_rtl";a:0:{}}s:24:"wordpress-seo/wp-seo.php";O:8:"stdClass":9:{s:2:"id";s:27:"w.org/plugins/wordpress-seo";s:4:"slug";s:13:"wordpress-seo";s:6:"plugin";s:24:"wordpress-seo/wp-seo.php";s:11:"new_version";s:6:"14.6.1";s:3:"url";s:44:"https://wordpress.org/plugins/wordpress-seo/";s:7:"package";s:63:"https://downloads.wordpress.org/plugin/wordpress-seo.14.6.1.zip";s:5:"icons";a:3:{s:2:"2x";s:66:"https://ps.w.org/wordpress-seo/assets/icon-256x256.png?rev=1834347";s:2:"1x";s:58:"https://ps.w.org/wordpress-seo/assets/icon.svg?rev=1946641";s:3:"svg";s:58:"https://ps.w.org/wordpress-seo/assets/icon.svg?rev=1946641";}s:7:"banners";a:2:{s:2:"2x";s:69:"https://ps.w.org/wordpress-seo/assets/banner-1544x500.png?rev=1843435";s:2:"1x";s:68:"https://ps.w.org/wordpress-seo/assets/banner-772x250.png?rev=1843435";}s:11:"banners_rtl";a:2:{s:2:"2x";s:73:"https://ps.w.org/wordpress-seo/assets/banner-1544x500-rtl.png?rev=1843435";s:2:"1x";s:72:"https://ps.w.org/wordpress-seo/assets/banner-772x250-rtl.png?rev=1843435";}}}}', 'no'),
(2331, '_transient_timeout_wpseo_total_unindexed_posts', '1595618543', 'no'),
(2332, '_transient_wpseo_total_unindexed_posts', '0', 'no'),
(2333, '_transient_timeout_wpseo_total_unindexed_terms', '1595618543', 'no'),
(2334, '_transient_wpseo_total_unindexed_terms', '0', 'no'),
(2335, '_transient_timeout_wpseo_total_unindexed_post_type_archives', '1595618543', 'no'),
(2336, '_transient_wpseo_total_unindexed_post_type_archives', '0', 'no'),
(2337, 'rewrite_rules', 'a:155:{s:14:"help-advice/?$";s:31:"index.php?post_type=help-advice";s:44:"help-advice/feed/(feed|rdf|rss|rss2|atom)/?$";s:48:"index.php?post_type=help-advice&feed=$matches[1]";s:39:"help-advice/(feed|rdf|rss|rss2|atom)/?$";s:48:"index.php?post_type=help-advice&feed=$matches[1]";s:31:"help-advice/page/([0-9]{1,})/?$";s:49:"index.php?post_type=help-advice&paged=$matches[1]";s:19:"sitemap_index\\.xml$";s:19:"index.php?sitemap=1";s:31:"([^/]+?)-sitemap([0-9]+)?\\.xml$";s:51:"index.php?sitemap=$matches[1]&sitemap_n=$matches[2]";s:24:"([a-z]+)?-?sitemap\\.xsl$";s:39:"index.php?yoast-sitemap-xsl=$matches[1]";s:29:"^ninja-forms/([a-zA-Z0-9]+)/?";s:36:"index.php?nf_public_link=$matches[1]";s:11:"^wp-json/?$";s:22:"index.php?rest_route=/";s:14:"^wp-json/(.*)?";s:33:"index.php?rest_route=/$matches[1]";s:21:"^index.php/wp-json/?$";s:22:"index.php?rest_route=/";s:24:"^index.php/wp-json/(.*)?";s:33:"index.php?rest_route=/$matches[1]";s:47:"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$";s:52:"index.php?category_name=$matches[1]&feed=$matches[2]";s:42:"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$";s:52:"index.php?category_name=$matches[1]&feed=$matches[2]";s:23:"category/(.+?)/embed/?$";s:46:"index.php?category_name=$matches[1]&embed=true";s:35:"category/(.+?)/page/?([0-9]{1,})/?$";s:53:"index.php?category_name=$matches[1]&paged=$matches[2]";s:17:"category/(.+?)/?$";s:35:"index.php?category_name=$matches[1]";s:44:"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?tag=$matches[1]&feed=$matches[2]";s:39:"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?tag=$matches[1]&feed=$matches[2]";s:20:"tag/([^/]+)/embed/?$";s:36:"index.php?tag=$matches[1]&embed=true";s:32:"tag/([^/]+)/page/?([0-9]{1,})/?$";s:43:"index.php?tag=$matches[1]&paged=$matches[2]";s:14:"tag/([^/]+)/?$";s:25:"index.php?tag=$matches[1]";s:45:"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?post_format=$matches[1]&feed=$matches[2]";s:40:"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?post_format=$matches[1]&feed=$matches[2]";s:21:"type/([^/]+)/embed/?$";s:44:"index.php?post_format=$matches[1]&embed=true";s:33:"type/([^/]+)/page/?([0-9]{1,})/?$";s:51:"index.php?post_format=$matches[1]&paged=$matches[2]";s:15:"type/([^/]+)/?$";s:33:"index.php?post_format=$matches[1]";s:39:"help-advice/[^/]+/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:49:"help-advice/[^/]+/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:69:"help-advice/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:64:"help-advice/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:64:"help-advice/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:45:"help-advice/[^/]+/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:28:"help-advice/([^/]+)/embed/?$";s:44:"index.php?help-advice=$matches[1]&embed=true";s:32:"help-advice/([^/]+)/trackback/?$";s:38:"index.php?help-advice=$matches[1]&tb=1";s:52:"help-advice/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?help-advice=$matches[1]&feed=$matches[2]";s:47:"help-advice/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?help-advice=$matches[1]&feed=$matches[2]";s:40:"help-advice/([^/]+)/page/?([0-9]{1,})/?$";s:51:"index.php?help-advice=$matches[1]&paged=$matches[2]";s:47:"help-advice/([^/]+)/comment-page-([0-9]{1,})/?$";s:51:"index.php?help-advice=$matches[1]&cpage=$matches[2]";s:36:"help-advice/([^/]+)(?:/([0-9]+))?/?$";s:50:"index.php?help-advice=$matches[1]&page=$matches[2]";s:28:"help-advice/[^/]+/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:38:"help-advice/[^/]+/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:58:"help-advice/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:53:"help-advice/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:53:"help-advice/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:34:"help-advice/[^/]+/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:63:"help-advice-categories/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:61:"index.php?help-advice-categories=$matches[1]&feed=$matches[2]";s:58:"help-advice-categories/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:61:"index.php?help-advice-categories=$matches[1]&feed=$matches[2]";s:39:"help-advice-categories/([^/]+)/embed/?$";s:55:"index.php?help-advice-categories=$matches[1]&embed=true";s:51:"help-advice-categories/([^/]+)/page/?([0-9]{1,})/?$";s:62:"index.php?help-advice-categories=$matches[1]&paged=$matches[2]";s:33:"help-advice-categories/([^/]+)/?$";s:44:"index.php?help-advice-categories=$matches[1]";s:34:"nf_sub/[^/]+/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:44:"nf_sub/[^/]+/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:64:"nf_sub/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:59:"nf_sub/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:59:"nf_sub/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:40:"nf_sub/[^/]+/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:23:"nf_sub/([^/]+)/embed/?$";s:39:"index.php?nf_sub=$matches[1]&embed=true";s:27:"nf_sub/([^/]+)/trackback/?$";s:33:"index.php?nf_sub=$matches[1]&tb=1";s:35:"nf_sub/([^/]+)/page/?([0-9]{1,})/?$";s:46:"index.php?nf_sub=$matches[1]&paged=$matches[2]";s:42:"nf_sub/([^/]+)/comment-page-([0-9]{1,})/?$";s:46:"index.php?nf_sub=$matches[1]&cpage=$matches[2]";s:31:"nf_sub/([^/]+)(?:/([0-9]+))?/?$";s:45:"index.php?nf_sub=$matches[1]&page=$matches[2]";s:23:"nf_sub/[^/]+/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:33:"nf_sub/[^/]+/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:53:"nf_sub/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:48:"nf_sub/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:48:"nf_sub/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:29:"nf_sub/[^/]+/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:47:"mbp-google-subposts/[^/]+/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:57:"mbp-google-subposts/[^/]+/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:77:"mbp-google-subposts/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:72:"mbp-google-subposts/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:72:"mbp-google-subposts/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:53:"mbp-google-subposts/[^/]+/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:36:"mbp-google-subposts/([^/]+)/embed/?$";s:52:"index.php?mbp-google-subposts=$matches[1]&embed=true";s:40:"mbp-google-subposts/([^/]+)/trackback/?$";s:46:"index.php?mbp-google-subposts=$matches[1]&tb=1";s:48:"mbp-google-subposts/([^/]+)/page/?([0-9]{1,})/?$";s:59:"index.php?mbp-google-subposts=$matches[1]&paged=$matches[2]";s:55:"mbp-google-subposts/([^/]+)/comment-page-([0-9]{1,})/?$";s:59:"index.php?mbp-google-subposts=$matches[1]&cpage=$matches[2]";s:44:"mbp-google-subposts/([^/]+)(?:/([0-9]+))?/?$";s:58:"index.php?mbp-google-subposts=$matches[1]&page=$matches[2]";s:36:"mbp-google-subposts/[^/]+/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:46:"mbp-google-subposts/[^/]+/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:66:"mbp-google-subposts/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:61:"mbp-google-subposts/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:61:"mbp-google-subposts/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:42:"mbp-google-subposts/[^/]+/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:12:"robots\\.txt$";s:18:"index.php?robots=1";s:13:"favicon\\.ico$";s:19:"index.php?favicon=1";s:48:".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$";s:18:"index.php?feed=old";s:20:".*wp-app\\.php(/.*)?$";s:19:"index.php?error=403";s:18:".*wp-register.php$";s:23:"index.php?register=true";s:32:"feed/(feed|rdf|rss|rss2|atom)/?$";s:27:"index.php?&feed=$matches[1]";s:27:"(feed|rdf|rss|rss2|atom)/?$";s:27:"index.php?&feed=$matches[1]";s:8:"embed/?$";s:21:"index.php?&embed=true";s:20:"page/?([0-9]{1,})/?$";s:28:"index.php?&paged=$matches[1]";s:27:"comment-page-([0-9]{1,})/?$";s:38:"index.php?&page_id=6&cpage=$matches[1]";s:41:"comments/feed/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?&feed=$matches[1]&withcomments=1";s:36:"comments/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?&feed=$matches[1]&withcomments=1";s:17:"comments/embed/?$";s:21:"index.php?&embed=true";s:44:"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:40:"index.php?s=$matches[1]&feed=$matches[2]";s:39:"search/(.+)/(feed|rdf|rss|rss2|atom)/?$";s:40:"index.php?s=$matches[1]&feed=$matches[2]";s:20:"search/(.+)/embed/?$";s:34:"index.php?s=$matches[1]&embed=true";s:32:"search/(.+)/page/?([0-9]{1,})/?$";s:41:"index.php?s=$matches[1]&paged=$matches[2]";s:14:"search/(.+)/?$";s:23:"index.php?s=$matches[1]";s:47:"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?author_name=$matches[1]&feed=$matches[2]";s:42:"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?author_name=$matches[1]&feed=$matches[2]";s:23:"author/([^/]+)/embed/?$";s:44:"index.php?author_name=$matches[1]&embed=true";s:35:"author/([^/]+)/page/?([0-9]{1,})/?$";s:51:"index.php?author_name=$matches[1]&paged=$matches[2]";s:17:"author/([^/]+)/?$";s:33:"index.php?author_name=$matches[1]";s:69:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$";s:80:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]";s:64:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$";s:80:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]";s:45:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$";s:74:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true";s:57:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$";s:81:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]";s:39:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$";s:63:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]";s:56:"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$";s:64:"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]";s:51:"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$";s:64:"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]";s:32:"([0-9]{4})/([0-9]{1,2})/embed/?$";s:58:"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true";s:44:"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$";s:65:"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]";s:26:"([0-9]{4})/([0-9]{1,2})/?$";s:47:"index.php?year=$matches[1]&monthnum=$matches[2]";s:43:"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?year=$matches[1]&feed=$matches[2]";s:38:"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?year=$matches[1]&feed=$matches[2]";s:19:"([0-9]{4})/embed/?$";s:37:"index.php?year=$matches[1]&embed=true";s:31:"([0-9]{4})/page/?([0-9]{1,})/?$";s:44:"index.php?year=$matches[1]&paged=$matches[2]";s:13:"([0-9]{4})/?$";s:26:"index.php?year=$matches[1]";s:27:".?.+?/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:37:".?.+?/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:57:".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:33:".?.+?/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:16:"(.?.+?)/embed/?$";s:41:"index.php?pagename=$matches[1]&embed=true";s:20:"(.?.+?)/trackback/?$";s:35:"index.php?pagename=$matches[1]&tb=1";s:40:"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$";s:47:"index.php?pagename=$matches[1]&feed=$matches[2]";s:35:"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$";s:47:"index.php?pagename=$matches[1]&feed=$matches[2]";s:28:"(.?.+?)/page/?([0-9]{1,})/?$";s:48:"index.php?pagename=$matches[1]&paged=$matches[2]";s:35:"(.?.+?)/comment-page-([0-9]{1,})/?$";s:48:"index.php?pagename=$matches[1]&cpage=$matches[2]";s:24:"(.?.+?)(?:/([0-9]+))?/?$";s:47:"index.php?pagename=$matches[1]&page=$matches[2]";s:27:"[^/]+/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:37:"[^/]+/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:57:"[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:"[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:"[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:33:"[^/]+/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:16:"([^/]+)/embed/?$";s:37:"index.php?name=$matches[1]&embed=true";s:20:"([^/]+)/trackback/?$";s:31:"index.php?name=$matches[1]&tb=1";s:40:"([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?name=$matches[1]&feed=$matches[2]";s:35:"([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?name=$matches[1]&feed=$matches[2]";s:28:"([^/]+)/page/?([0-9]{1,})/?$";s:44:"index.php?name=$matches[1]&paged=$matches[2]";s:35:"([^/]+)/comment-page-([0-9]{1,})/?$";s:44:"index.php?name=$matches[1]&cpage=$matches[2]";s:24:"([^/]+)(?:/([0-9]+))?/?$";s:43:"index.php?name=$matches[1]&page=$matches[2]";s:16:"[^/]+/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:26:"[^/]+/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:46:"[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:41:"[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:41:"[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:22:"[^/]+/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";}', 'yes'),
(2339, '_site_transient_timeout_wpmdb_licence_response', '1595575368', 'no') ;
INSERT INTO `league_options` ( `option_id`, `option_name`, `option_value`, `autoload`) VALUES
(2340, '_site_transient_wpmdb_licence_response', '{"addons_available":"1","addons_available_list":{"wp-migrate-db-pro-media-files":2351,"wp-migrate-db-pro-cli":3948,"wp-migrate-db-pro-multisite-tools":7999,"wp-migrate-db-pro-theme-plugin-files":36287},"addon_list":{"wp-migrate-db-pro-media-files":{"type":"feature","name":"Media Files","desc":"Allows you to push and pull your files in the Media Library between two WordPress installs. It can compare both libraries and only migrate those missing or updated, or it can do a complete copy of one site\\u2019s library to another. <a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/doc\\/media-files-addon\\/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin\\">More Details &rarr;<\\/a>","version":"1.4.15","beta_version":false,"tested":"5.4.2"},"wp-migrate-db-pro-cli":{"type":"feature","name":"CLI","desc":"Integrates WP Migrate DB Pro with WP-CLI allowing you to run migrations from the command line: <code>wp migratedb &lt;push|pull&gt; &lt;url&gt; &lt;secret-key&gt;<\\/code> <code>[--find=&lt;strings&gt;] [--replace=&lt;strings&gt;] ...<\\/code> <a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/doc\\/cli-addon\\/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin\\">More Details &rarr;<\\/a>","required":"1.4b1","version":"1.3.5","beta_version":false,"tested":"5.4.2"},"wp-migrate-db-pro-multisite-tools":{"type":"feature","name":"Multisite Tools","desc":"Export a subsite as an SQL file that can then be imported as a single site install. <a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/doc\\/multisite-tools-addon\\/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin\\">More Details &rarr;<\\/a>","required":"1.5-dev","version":"1.2.6","beta_version":false,"tested":"5.4.2"},"wp-migrate-db-pro-theme-plugin-files":{"type":"feature","name":"Theme & Plugin Files","desc":"Allows you to push and pull your theme and plugin files between two WordPress installs. <a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/doc\\/theme-plugin-files-addon\\/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin\\">More Details &rarr;<\\/a>","required":"1.8.2b1","version":"1.0.5","beta_version":false,"tested":"5.4.2"}},"message":"<style type=\\"text\\/css\\" media=\\"screen\\">body .support .support-content{overflow:hidden;width:727px}body .support .support-content .intro{margin-bottom:20px}body .support .support-content .submission-error p,body .support .support-content .submission-success p{padding:2px;margin:.5em 0;font-size:13px;line-height:1.5}body .support .support-content .dbrains-support-form{width:475px;float:left}body .support .support-content .dbrains-support-form p{width:auto}body .support .support-content .dbrains-support-form .field{margin-bottom:5px}body .support .support-content .dbrains-support-form input[type=text],body .support .support-content .dbrains-support-form textarea{width:100%}body .support .support-content .dbrains-support-form .field.from label{float:left;line-height:28px;display:block;font-weight:700}body .support .support-content .dbrains-support-form .field.from select{float:right;width:400px}body .support .support-content .dbrains-support-form .field.from .note{clear:both;padding-top:5px}body .support .support-content .dbrains-support-form .field.email-message textarea{height:170px}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content{padding-left:20px}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content ol{margin:0 0 5px 20px}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content li{font-size:12px;color:#666;margin-bottom:0;line-height:1.4em}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content textarea{height:80px}body .support .support-content .dbrains-support-form .note{font-size:12px;color:#666}body .support .support-content .dbrains-support-form .submit-form{overflow:hidden;padding:10px 0}body .support .support-content .dbrains-support-form .button{float:left}body .support .support-content .dbrains-support-form .button:active,body .support .support-content .dbrains-support-form .button:focus{outline:0}body .support .support-content .dbrains-support-form .ajax-spinner{float:left;margin-left:5px;margin-top:3px}body .support .support-content .additional-help{float:right;width:220px}body .support .support-content .additional-help a{text-decoration:none}body .support .support-content .additional-help h1{margin:0 0 12px;padding:0;font-size:18px;font-weight:400;line-height:1}body .support .support-content .additional-help h1 a{color:#333}body .support .support-content .additional-help .docs{background-color:#e6e6e6;padding:15px 15px 10px}body .support .support-content .additional-help .docs ul{margin:0}body .support .support-content .additional-help .docs li{font-size:14px}<\\/style><section class=\\"dbrains-support-form\\">\\n\\n<p class=\\"intro\\">\\n\\tYou have an active <strong>Agency<\\/strong> license. You will get front-of-the-line email support service when submitting the form below.<\\/p>\\n\\n<div class=\\"updated submission-success\\" style=\\"display: none;\\">\\n\\t<p><strong>Success!<\\/strong> &mdash; Thanks for submitting your support request. We\'ll be in touch soon.<\\/p>\\n<\\/div>\\n\\n<div class=\\"error submission-error api-error\\" style=\\"display: none;\\">\\n\\t<p><strong>Error!<\\/strong> &mdash; <\\/p>\\n<\\/div>\\n\\n<div class=\\"error submission-error xhr-error\\" style=\\"display: none;\\">\\n\\t<p><strong>Error!<\\/strong> &mdash; There was a problem submitting your request:<\\/p>\\n<\\/div>\\n\\n<div class=\\"error submission-error email-error\\" style=\\"display: none;\\">\\n\\t<p><strong>Error!<\\/strong> &mdash; Please select your email address.<\\/p>\\n<\\/div>\\n\\n<div class=\\"error submission-error subject-error\\" style=\\"display: none;\\">\\n\\t<p><strong>Error!<\\/strong> &mdash; Please enter a subject.<\\/p>\\n<\\/div>\\n\\n<div class=\\"error submission-error message-error\\" style=\\"display: none;\\">\\n\\t<p><strong>Error!<\\/strong> &mdash; Please enter a message.<\\/p>\\n<\\/div>\\n\\n<div class=\\"error submission-error remote-diagnostic-content-error\\" style=\\"display: none;\\">\\n\\t<p><strong>Error!<\\/strong> &mdash; Please paste in the Diagnostic Info &amp; Error Log from your <strong>remote site<\\/strong>.<\\/p>\\n<\\/div>\\n\\n<div class=\\"error submission-error both-diagnostic-same-error\\" style=\\"display: none;\\">\\n\\t<p><strong>Error!<\\/strong> &mdash; Looks like you pasted the local Diagnostic Info &amp; Error Log into the textbox for the remote info. Please get the info for your <strong>remote site<\\/strong> and paste it in, or just uncheck the second checkbox if you&#8217;d rather not include your remote site info.<\\/p>\\n<\\/div>\\n\\n<form target=\\"_blank\\" method=\\"post\\" action=\\"https:\\/\\/api.deliciousbrains.com\\/?wc-api=delicious-brains&request=submit_support_request&licence_key=d7d11773-7dd5-440e-94d3-d7694f0e925b&product=wp-migrate-db-pro\\">\\n\\n\\t<div class=\\"field from\\">\\n\\t\\t<label>From:<\\/label>\\n\\t\\t<select name=\\"email\\">\\n\\t\\t<option value=\\"\\">&mdash; Select your email address &mdash;<\\/option>\\n\\t\\t<option value=\\"staff.development@adtrak.co.uk\\">staff.development@adtrak.co.uk<\\/option><option value=\\"tom.nightingale@adtrak.co.uk\\">tom.nightingale@adtrak.co.uk<\\/option><option value=\\"dan.farrow@adtrak.co.uk\\">dan.farrow@adtrak.co.uk<\\/option>\\t\\t<\\/select>\\n\\n\\t\\t<p class=\\"note\\">\\n\\t\\t\\tReplies will be sent to this email address. Update your name &amp; email in <a href=\\"https:\\/\\/deliciousbrains.com\\/my-account\\/\\">My Account<\\/a>.\\t\\t<\\/p>\\n\\t<\\/div>\\n\\n\\t<div class=\\"field subject\\">\\n\\t\\t<input type=\\"text\\" name=\\"subject\\" placeholder=\\"Subject\\">\\n\\t<\\/div>\\n\\n\\t<div class=\\"field email-message\\">\\n\\t\\t<textarea name=\\"message\\" placeholder=\\"Message\\"><\\/textarea>\\n\\t<\\/div>\\n\\n\\t<div class=\\"field checkbox local-diagnostic\\">\\n\\t\\t<label>\\n\\t\\t\\t<input type=\\"checkbox\\" name=\\"local-diagnostic\\" value=\\"1\\" checked>\\n\\t\\t\\tAttach <strong>this site&#8217;s<\\/strong> Diagnostic Info &amp; Error Log (below)\\t\\t<\\/label>\\n\\t<\\/div>\\n\\t\\t<div class=\\"field checkbox remote-diagnostic\\">\\n\\t\\t<label>\\n\\t\\t\\t<input type=\\"checkbox\\" name=\\"remote-diagnostic\\" value=\\"1\\" checked>\\n\\t\\t\\tAttach the <strong>remote site&#8217;s<\\/strong> Diagnostic Info &amp; Error Log\\t\\t<\\/label>\\n\\t<\\/div>\\n\\n\\t<div class=\\"field remote-diagnostic-content\\">\\n\\t\\t<ol>\\n\\t\\t\\t<li>Go to the Help tab of the remote site<\\/li>\\n\\t\\t\\t<li>Copy the Diagnostic Info &amp; Error Log<\\/li>\\n\\t\\t\\t<li>Paste it below<\\/li>\\n\\t\\t<\\/ol>\\n\\t\\t<textarea name=\\"remote-diagnostic-content\\" placeholder=\\"Remote site&#8217;s Diagnostic Info &amp; Error Log\\"><\\/textarea>\\n\\t<\\/div>\\n\\t\\t<div class=\\"submit-form\\">\\n\\t\\t<button type=\\"submit\\" class=\\"button\\">Send Email<\\/button>\\n\\t<\\/div>\\n\\n\\t<p class=\\"note trouble\\">\\n\\t\\tHaving trouble submitting the form? Email your support request to <a href=\\"mailto:priority-wpmdb@deliciousbrains.com\\">priority-wpmdb@deliciousbrains.com<\\/a> instead.\\t<\\/p>\\n\\n<\\/form>\\n\\n<\\/section>\\n\\n\\t<aside class=\\"additional-help\\">\\n\\t\\t<section class=\\"docs\\">\\n\\t\\t\\t<h1><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/\\">Documentation<\\/a><\\/h1>\\n\\t\\t\\t<ul class=\\"categories\\">\\n\\t\\t\\t\\t<li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/getting-started\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">Getting Started<\\/a><\\/li><li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/debugging\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">Debugging<\\/a><\\/li><li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/cli\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">CLI<\\/a><\\/li><li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/common-errors\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">Common Errors<\\/a><\\/li><li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/howto\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">How To<\\/a><\\/li><li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/addons\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">Addons<\\/a><\\/li><li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/multisite\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">Multisite<\\/a><\\/li><li><a href=\\"https:\\/\\/deliciousbrains.com\\/wp-migrate-db-pro\\/docs\\/changelogs\\/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin\\">Changelogs<\\/a><\\/li>\\t\\t\\t<\\/ul>\\n\\t\\t<\\/section>\\n\\t<\\/aside>\\n<script>\\"use strict\\";!function($){var $form=$(\\".dbrains-support-form form\\"),$submit_div=$(\\".submit-form\\",$form),is_submitting=!1,$checkbox=$(\\".remote-diagnostic input\\",$form),$content=$(\\".remote-diagnostic-content\\",$form);$checkbox.on(\\"click\\",function(){$checkbox.prop(\\"checked\\")?$content.show():$content.hide()});var spinner_url=ajaxurl.replace(\\"\\/admin-ajax.php\\",\\"\\/images\\/wpspin_light\\");2<=window.devicePixelRatio&&(spinner_url+=\\"-2x\\"),spinner_url+=\\".gif\\",$form.submit(function(e){if(e.preventDefault(),!is_submitting){is_submitting=!0,$(\\".button\\",$form).blur();var $spinner=$(\\".ajax-spinner\\",$submit_div);$spinner[0]?$spinner.show():($spinner=$(\'<img src=\\"\'+spinner_url+\'\\" alt=\\"\\" class=\\"ajax-spinner general-spinner\\" \\/>\'),$submit_div.append($spinner)),$(\\".submission-error\\").hide();var required=[\\"email\\",\\"subject\\",\\"message\\"],form_data={},is_error=!1;$.each($form.serializeArray(),function(i,object){form_data[object.name]=object.value,-1<$.inArray(object.name,required)&&\\"\\"===object.value&&($(\\".\\"+object.name+\\"-error\\").fadeIn(),is_error=!0)});var is_remote_diagnostic_checked=$(\\"input[name=remote-diagnostic]\\",$form).is(\\":checked\\");if(is_remote_diagnostic_checked)if(\\"\\"===form_data[\\"remote-diagnostic-content\\"])$(\\".remote-diagnostic-content-error\\").fadeIn(),is_error=!0;else{var remote_first_line=form_data[\\"remote-diagnostic-content\\"].substr(0,form_data[\\"remote-diagnostic-content\\"].indexOf(\\"\\\\n\\")),local_textarea=$(\\".debug-log-textarea\\")[0],local_first_line=local_textarea.value.substr(0,local_textarea.value.indexOf(\\"\\\\n\\"));remote_first_line.trim()==local_first_line.trim()&&($(\\".both-diagnostic-same-error\\").fadeIn(),is_error=!0)}if(is_error)return $spinner.hide(),void(is_submitting=!1);is_remote_diagnostic_checked||(form_data[\\"remote-diagnostic-content\\"]=\\"\\"),$(\\"input[name=local-diagnostic]\\",$form).is(\\":checked\\")&&(form_data[\\"local-diagnostic-content\\"]=$(\\".debug-log-textarea\\").val()),$.ajax({url:$form.prop(\\"action\\"),type:\\"POST\\",dataType:\\"JSON\\",cache:!1,data:form_data,error:function(jqXHR,textStatus,errorThrown){var $error=$(\\".xhr-error\\");$(\\"p\\",$error).append(\\" \\"+errorThrown+\\" (\\"+textStatus+\\")\\"),$error.show(),$spinner.hide(),is_submitting=!1},success:function(data){if(void 0!==data.errors){var $error=$(\\".api-error\\");return $.each(data.errors,function(key,error_msg){return $(\\"p\\",$error).append(error_msg),!1}),$error.show(),$spinner.hide(),void(is_submitting=!1)}$(\\".submission-success\\").show(),$form.hide(),$spinner.hide(),is_submitting=!1}})}})}(jQuery);<\\/script>"}', 'no'),
(2341, '_site_transient_timeout_wpmdb_help_message', '1595575368', 'no'),
(2342, '_site_transient_wpmdb_help_message', '<style type="text/css" media="screen">body .support .support-content{overflow:hidden;width:727px}body .support .support-content .intro{margin-bottom:20px}body .support .support-content .submission-error p,body .support .support-content .submission-success p{padding:2px;margin:.5em 0;font-size:13px;line-height:1.5}body .support .support-content .dbrains-support-form{width:475px;float:left}body .support .support-content .dbrains-support-form p{width:auto}body .support .support-content .dbrains-support-form .field{margin-bottom:5px}body .support .support-content .dbrains-support-form input[type=text],body .support .support-content .dbrains-support-form textarea{width:100%}body .support .support-content .dbrains-support-form .field.from label{float:left;line-height:28px;display:block;font-weight:700}body .support .support-content .dbrains-support-form .field.from select{float:right;width:400px}body .support .support-content .dbrains-support-form .field.from .note{clear:both;padding-top:5px}body .support .support-content .dbrains-support-form .field.email-message textarea{height:170px}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content{padding-left:20px}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content ol{margin:0 0 5px 20px}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content li{font-size:12px;color:#666;margin-bottom:0;line-height:1.4em}body .support .support-content .dbrains-support-form .field.remote-diagnostic-content textarea{height:80px}body .support .support-content .dbrains-support-form .note{font-size:12px;color:#666}body .support .support-content .dbrains-support-form .submit-form{overflow:hidden;padding:10px 0}body .support .support-content .dbrains-support-form .button{float:left}body .support .support-content .dbrains-support-form .button:active,body .support .support-content .dbrains-support-form .button:focus{outline:0}body .support .support-content .dbrains-support-form .ajax-spinner{float:left;margin-left:5px;margin-top:3px}body .support .support-content .additional-help{float:right;width:220px}body .support .support-content .additional-help a{text-decoration:none}body .support .support-content .additional-help h1{margin:0 0 12px;padding:0;font-size:18px;font-weight:400;line-height:1}body .support .support-content .additional-help h1 a{color:#333}body .support .support-content .additional-help .docs{background-color:#e6e6e6;padding:15px 15px 10px}body .support .support-content .additional-help .docs ul{margin:0}body .support .support-content .additional-help .docs li{font-size:14px}</style><section class="dbrains-support-form">\n\n<p class="intro">\n	You have an active <strong>Agency</strong> license. You will get front-of-the-line email support service when submitting the form below.</p>\n\n<div class="updated submission-success" style="display: none;">\n	<p><strong>Success!</strong> &mdash; Thanks for submitting your support request. We\'ll be in touch soon.</p>\n</div>\n\n<div class="error submission-error api-error" style="display: none;">\n	<p><strong>Error!</strong> &mdash; </p>\n</div>\n\n<div class="error submission-error xhr-error" style="display: none;">\n	<p><strong>Error!</strong> &mdash; There was a problem submitting your request:</p>\n</div>\n\n<div class="error submission-error email-error" style="display: none;">\n	<p><strong>Error!</strong> &mdash; Please select your email address.</p>\n</div>\n\n<div class="error submission-error subject-error" style="display: none;">\n	<p><strong>Error!</strong> &mdash; Please enter a subject.</p>\n</div>\n\n<div class="error submission-error message-error" style="display: none;">\n	<p><strong>Error!</strong> &mdash; Please enter a message.</p>\n</div>\n\n<div class="error submission-error remote-diagnostic-content-error" style="display: none;">\n	<p><strong>Error!</strong> &mdash; Please paste in the Diagnostic Info &amp; Error Log from your <strong>remote site</strong>.</p>\n</div>\n\n<div class="error submission-error both-diagnostic-same-error" style="display: none;">\n	<p><strong>Error!</strong> &mdash; Looks like you pasted the local Diagnostic Info &amp; Error Log into the textbox for the remote info. Please get the info for your <strong>remote site</strong> and paste it in, or just uncheck the second checkbox if you&#8217;d rather not include your remote site info.</p>\n</div>\n\n<form target="_blank" method="post" action="https://api.deliciousbrains.com/?wc-api=delicious-brains&request=submit_support_request&licence_key=d7d11773-7dd5-440e-94d3-d7694f0e925b&product=wp-migrate-db-pro">\n\n	<div class="field from">\n		<label>From:</label>\n		<select name="email">\n		<option value="">&mdash; Select your email address &mdash;</option>\n		<option value="staff.development@adtrak.co.uk">staff.development@adtrak.co.uk</option><option value="tom.nightingale@adtrak.co.uk">tom.nightingale@adtrak.co.uk</option><option value="dan.farrow@adtrak.co.uk">dan.farrow@adtrak.co.uk</option>		</select>\n\n		<p class="note">\n			Replies will be sent to this email address. Update your name &amp; email in <a href="https://deliciousbrains.com/my-account/">My Account</a>.		</p>\n	</div>\n\n	<div class="field subject">\n		<input type="text" name="subject" placeholder="Subject">\n	</div>\n\n	<div class="field email-message">\n		<textarea name="message" placeholder="Message"></textarea>\n	</div>\n\n	<div class="field checkbox local-diagnostic">\n		<label>\n			<input type="checkbox" name="local-diagnostic" value="1" checked>\n			Attach <strong>this site&#8217;s</strong> Diagnostic Info &amp; Error Log (below)		</label>\n	</div>\n		<div class="field checkbox remote-diagnostic">\n		<label>\n			<input type="checkbox" name="remote-diagnostic" value="1" checked>\n			Attach the <strong>remote site&#8217;s</strong> Diagnostic Info &amp; Error Log		</label>\n	</div>\n\n	<div class="field remote-diagnostic-content">\n		<ol>\n			<li>Go to the Help tab of the remote site</li>\n			<li>Copy the Diagnostic Info &amp; Error Log</li>\n			<li>Paste it below</li>\n		</ol>\n		<textarea name="remote-diagnostic-content" placeholder="Remote site&#8217;s Diagnostic Info &amp; Error Log"></textarea>\n	</div>\n		<div class="submit-form">\n		<button type="submit" class="button">Send Email</button>\n	</div>\n\n	<p class="note trouble">\n		Having trouble submitting the form? Email your support request to <a href="mailto:priority-wpmdb@deliciousbrains.com">priority-wpmdb@deliciousbrains.com</a> instead.	</p>\n\n</form>\n\n</section>\n\n	<aside class="additional-help">\n		<section class="docs">\n			<h1><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/">Documentation</a></h1>\n			<ul class="categories">\n				<li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/getting-started/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">Getting Started</a></li><li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/debugging/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">Debugging</a></li><li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/cli/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">CLI</a></li><li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/common-errors/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">Common Errors</a></li><li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/howto/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">How To</a></li><li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/addons/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">Addons</a></li><li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/multisite/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">Multisite</a></li><li><a href="https://deliciousbrains.com/wp-migrate-db-pro/docs/changelogs/?utm_source=MDB%2BPaid&#038;utm_campaign=support%2Bdocs&#038;utm_medium=insideplugin">Changelogs</a></li>			</ul>\n		</section>\n	</aside>\n<script>"use strict";!function($){var $form=$(".dbrains-support-form form"),$submit_div=$(".submit-form",$form),is_submitting=!1,$checkbox=$(".remote-diagnostic input",$form),$content=$(".remote-diagnostic-content",$form);$checkbox.on("click",function(){$checkbox.prop("checked")?$content.show():$content.hide()});var spinner_url=ajaxurl.replace("/admin-ajax.php","/images/wpspin_light");2<=window.devicePixelRatio&&(spinner_url+="-2x"),spinner_url+=".gif",$form.submit(function(e){if(e.preventDefault(),!is_submitting){is_submitting=!0,$(".button",$form).blur();var $spinner=$(".ajax-spinner",$submit_div);$spinner[0]?$spinner.show():($spinner=$(\'<img src="\'+spinner_url+\'" alt="" class="ajax-spinner general-spinner" />\'),$submit_div.append($spinner)),$(".submission-error").hide();var required=["email","subject","message"],form_data={},is_error=!1;$.each($form.serializeArray(),function(i,object){form_data[object.name]=object.value,-1<$.inArray(object.name,required)&&""===object.value&&($("."+object.name+"-error").fadeIn(),is_error=!0)});var is_remote_diagnostic_checked=$("input[name=remote-diagnostic]",$form).is(":checked");if(is_remote_diagnostic_checked)if(""===form_data["remote-diagnostic-content"])$(".remote-diagnostic-content-error").fadeIn(),is_error=!0;else{var remote_first_line=form_data["remote-diagnostic-content"].substr(0,form_data["remote-diagnostic-content"].indexOf("\\n")),local_textarea=$(".debug-log-textarea")[0],local_first_line=local_textarea.value.substr(0,local_textarea.value.indexOf("\\n"));remote_first_line.trim()==local_first_line.trim()&&($(".both-diagnostic-same-error").fadeIn(),is_error=!0)}if(is_error)return $spinner.hide(),void(is_submitting=!1);is_remote_diagnostic_checked||(form_data["remote-diagnostic-content"]=""),$("input[name=local-diagnostic]",$form).is(":checked")&&(form_data["local-diagnostic-content"]=$(".debug-log-textarea").val()),$.ajax({url:$form.prop("action"),type:"POST",dataType:"JSON",cache:!1,data:form_data,error:function(jqXHR,textStatus,errorThrown){var $error=$(".xhr-error");$("p",$error).append(" "+errorThrown+" ("+textStatus+")"),$error.show(),$spinner.hide(),is_submitting=!1},success:function(data){if(void 0!==data.errors){var $error=$(".api-error");return $.each(data.errors,function(key,error_msg){return $("p",$error).append(error_msg),!1}),$error.show(),$spinner.hide(),void(is_submitting=!1)}$(".submission-success").show(),$form.hide(),$spinner.hide(),is_submitting=!1}})}})}(jQuery);</script>', 'no'),
(2343, '_site_transient_timeout_wpmdb_addons', '1600716168', 'no'),
(2344, '_site_transient_wpmdb_addons', 'a:4:{s:29:"wp-migrate-db-pro-media-files";a:6:{s:4:"type";s:7:"feature";s:4:"name";s:11:"Media Files";s:4:"desc";s:412:"Allows you to push and pull your files in the Media Library between two WordPress installs. It can compare both libraries and only migrate those missing or updated, or it can do a complete copy of one site’s library to another. <a href="https://deliciousbrains.com/wp-migrate-db-pro/doc/media-files-addon/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin">More Details &rarr;</a>";s:7:"version";s:6:"1.4.15";s:12:"beta_version";b:0;s:6:"tested";s:5:"5.4.2";}s:21:"wp-migrate-db-pro-cli";a:7:{s:4:"type";s:7:"feature";s:4:"name";s:3:"CLI";s:4:"desc";s:414:"Integrates WP Migrate DB Pro with WP-CLI allowing you to run migrations from the command line: <code>wp migratedb &lt;push|pull&gt; &lt;url&gt; &lt;secret-key&gt;</code> <code>[--find=&lt;strings&gt;] [--replace=&lt;strings&gt;] ...</code> <a href="https://deliciousbrains.com/wp-migrate-db-pro/doc/cli-addon/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin">More Details &rarr;</a>";s:8:"required";s:5:"1.4b1";s:7:"version";s:5:"1.3.5";s:12:"beta_version";b:0;s:6:"tested";s:5:"5.4.2";}s:33:"wp-migrate-db-pro-multisite-tools";a:7:{s:4:"type";s:7:"feature";s:4:"name";s:15:"Multisite Tools";s:4:"desc";s:270:"Export a subsite as an SQL file that can then be imported as a single site install. <a href="https://deliciousbrains.com/wp-migrate-db-pro/doc/multisite-tools-addon/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin">More Details &rarr;</a>";s:8:"required";s:7:"1.5-dev";s:7:"version";s:5:"1.2.6";s:12:"beta_version";b:0;s:6:"tested";s:5:"5.4.2";}s:36:"wp-migrate-db-pro-theme-plugin-files";a:7:{s:4:"type";s:7:"feature";s:4:"name";s:20:"Theme & Plugin Files";s:4:"desc";s:277:"Allows you to push and pull your theme and plugin files between two WordPress installs. <a href="https://deliciousbrains.com/wp-migrate-db-pro/doc/theme-plugin-files-addon/?utm_campaign=addons%252Binstall&utm_source=MDB%252BPaid&utm_medium=insideplugin">More Details &rarr;</a>";s:8:"required";s:7:"1.8.2b1";s:7:"version";s:5:"1.0.5";s:12:"beta_version";b:0;s:6:"tested";s:5:"5.4.2";}}', 'no'),
(2346, 'wpmdb_error_log', '********************************************\n******  Log date: 2020/07/23 19:24:13 ******\n********************************************\n\nIntent: pull\nAction: wpmdb_verify_connection_to_remote_site\nWPMDB Error: The remote site is protected with Basic Authentication. Please enter the username and password above to continue. (401 Unauthorized)\n\nArray\n(\n    [headers] => Requests_Utility_CaseInsensitiveDictionary Object\n        (\n            [data:protected] => Array\n                (\n                    [date] => Thu, 23 Jul 2020 19:24:13 GMT\n                    [server] => Apache\n                    [www-authenticate] => Basic realm="Restricted Area"\n                    [content-length] => 503\n                    [content-type] => text/html; charset=iso-8859-1\n                )\n\n        )\n\n    [body] => <!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">\n<html><head>\n<title>401 Unauthorized</title>\n</head><body>\n<h1>Unauthorized</h1>\n<p>This server could not verify that you\nare authorized to access the document\nrequested.  Either you supplied the wrong\ncredentials (e.g., bad password), or your\nbrowser doesn\'t understand how to supply\nthe credentials required.</p>\n<p>Additionally, a 401 Unauthorized\nerror was encountered while trying to use an ErrorDocument to handle the request.</p>\n</body></html>\n\n    [response] => Array\n        (\n            [code] => 401\n            [message] => Unauthorized\n        )\n\n    [cookies] => Array\n        (\n        )\n\n    [filename] => \n    [url] => https://staging.fullbrookandfloor.co.uk/wp-admin/admin-ajax.php\n)\n\n\n', 'no'),
(2348, 'wpmdb_state_timeout_5f19e3f15b08c', '1595618677', 'no'),
(2349, 'wpmdb_state_5f19e3f15b08c', 'a:25:{s:6:"action";s:19:"wpmdb_migrate_table";s:6:"intent";s:4:"pull";s:3:"url";s:56:"https://rodrene:cosyhome@staging.fullbrookandfloor.co.uk";s:3:"key";s:40:"np55Du1k/7KbXVEq+RgE/ND0PGU0RuIvVfklZSo/";s:9:"form_data";s:644:"save_computer=1&gzip_file=1&action=pull&connection_info=https%3A%2F%2Frodrene%3Acosyhome%40staging.fullbrookandfloor.co.uk%0D%0Anp55Du1k%2F7KbXVEq%2BRgE%2FND0PGU0RuIvVfklZSo%2F&import_find_replace=1&replace_old%5B%5D=&replace_new%5B%5D=&replace_old%5B%5D=%2F%2Fstaging.fullbrookandfloor.co.uk&replace_new%5B%5D=%2F%2Ffullbrook-floor.vm&table_migrate_option=migrate_only_with_prefix&replace_guids=1&exclude_transients=1&create_backup=1&backup_option=backup_only_with_prefix&media_files=1&media_migration_option=compare&save_migration_profile=1&save_migration_profile_option=new&create_new_profile=Pull+%2F%2F+Staging+-%3E+Local&remote_json_data=";s:5:"stage";s:6:"backup";s:5:"nonce";s:10:"3cb2c825ec";s:12:"site_details";a:2:{s:5:"local";a:10:{s:12:"is_multisite";s:5:"false";s:8:"site_url";s:25:"http://fullbrook-floor.vm";s:8:"home_url";s:25:"http://fullbrook-floor.vm";s:6:"prefix";s:7:"league_";s:15:"uploads_baseurl";s:45:"http://fullbrook-floor.vm/wp-content/uploads/";s:7:"uploads";a:7:{s:4:"path";s:59:"C:\\wamp64\\www\\fullbrook-floor.vm/wp-content/uploads/2020/07";s:3:"url";s:52:"http://fullbrook-floor.vm/wp-content/uploads/2020/07";s:6:"subdir";s:8:"/2020/07";s:7:"basedir";s:51:"C:\\wamp64\\www\\fullbrook-floor.vm/wp-content/uploads";s:7:"baseurl";s:44:"http://fullbrook-floor.vm/wp-content/uploads";s:5:"error";b:0;s:8:"relative";s:19:"/wp-content/uploads";}s:11:"uploads_dir";s:33:"wp-content/uploads/wp-migrate-db/";s:8:"subsites";a:0:{}s:13:"subsites_info";a:0:{}s:20:"is_subdomain_install";s:5:"false";}s:6:"remote";a:10:{s:12:"is_multisite";s:5:"false";s:8:"site_url";s:39:"https://staging.fullbrookandfloor.co.uk";s:8:"home_url";s:38:"http://staging.fullbrookandfloor.co.uk";s:6:"prefix";s:7:"league_";s:15:"uploads_baseurl";s:58:"http://staging.fullbrookandfloor.co.uk/wp-content/uploads/";s:7:"uploads";a:6:{s:4:"path";s:69:"/home/fullbrookandfloo/public_html/staging/wp-content/uploads/2020/07";s:3:"url";s:65:"http://staging.fullbrookandfloor.co.uk/wp-content/uploads/2020/07";s:6:"subdir";s:8:"/2020/07";s:7:"basedir";s:61:"/home/fullbrookandfloo/public_html/staging/wp-content/uploads";s:7:"baseurl";s:57:"http://staging.fullbrookandfloor.co.uk/wp-content/uploads";s:5:"error";b:0;}s:11:"uploads_dir";s:33:"wp-content/uploads/wp-migrate-db/";s:8:"subsites";a:0:{}s:13:"subsites_info";a:0:{}s:20:"is_subdomain_install";s:5:"false";}}s:11:"temp_prefix";s:5:"_mig_";s:5:"error";i:0;s:15:"remote_state_id";s:13:"5f19e3f13fc15";s:13:"dump_filename";s:46:"fullbrook-floor-vm-backup-20200723192433-0vnhe";s:8:"dump_url";s:109:"http://fullbrook-floor.vm/wp-content/uploads/wp-migrate-db/fullbrook-floor-vm-backup-20200723192433-uyqm0.sql";s:10:"db_version";s:5:"5.5.5";s:8:"site_url";s:25:"http://fullbrook-floor.vm";s:18:"find_replace_pairs";a:2:{s:11:"replace_old";a:1:{i:1;s:33:"//staging.fullbrookandfloor.co.uk";}s:11:"replace_new";a:1:{i:1;s:20:"//fullbrook-floor.vm";}}s:18:"migration_state_id";s:13:"5f19e3f15b08c";s:5:"table";s:14:"league_options";s:11:"current_row";s:0:"";s:10:"last_table";s:1:"0";s:12:"primary_keys";s:0:"";s:4:"gzip";i:1;s:10:"bottleneck";d:1048576;s:6:"prefix";s:7:"league_";s:16:"dumpfile_created";b:1;}', 'no') ;

#
# End of data contents of table `league_options`
# --------------------------------------------------------



#
# Delete any existing table `league_postmeta`
#

DROP TABLE IF EXISTS `league_postmeta`;


#
# Table structure of table `league_postmeta`
#

CREATE TABLE `league_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM AUTO_INCREMENT=2428 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_postmeta`
#
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1, 2, '_wp_page_template', 'default'),
(2, 3, '_wp_page_template', 'default'),
(3, 6, '_edit_last', '1'),
(4, 6, '_edit_lock', '1595435256:1'),
(5, 8, '_edit_last', '1'),
(6, 8, '_edit_lock', '1595433650:1'),
(7, 10, '_edit_last', '1'),
(8, 10, '_edit_lock', '1595433649:1'),
(9, 12, '_edit_last', '1'),
(10, 12, '_edit_lock', '1594155259:1'),
(11, 14, '_edit_last', '1'),
(12, 14, '_edit_lock', '1595433576:1'),
(13, 16, '_edit_last', '1'),
(14, 16, '_edit_lock', '1595433650:1'),
(15, 18, '_edit_last', '1'),
(16, 18, '_edit_lock', '1595433832:1'),
(17, 20, '_edit_last', '1'),
(18, 20, '_edit_lock', '1594469154:1'),
(19, 2, '_wp_trash_meta_status', 'publish'),
(20, 2, '_wp_trash_meta_time', '1593713401'),
(21, 2, '_wp_desired_post_slug', 'sample-page'),
(22, 23, '_menu_item_type', 'post_type'),
(23, 23, '_menu_item_menu_item_parent', '0'),
(24, 23, '_menu_item_object_id', '6'),
(25, 23, '_menu_item_object', 'page'),
(26, 23, '_menu_item_target', ''),
(27, 23, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(28, 23, '_menu_item_xfn', ''),
(29, 23, '_menu_item_url', ''),
(31, 24, '_menu_item_type', 'post_type'),
(32, 24, '_menu_item_menu_item_parent', '0'),
(33, 24, '_menu_item_object_id', '14'),
(34, 24, '_menu_item_object', 'page'),
(35, 24, '_menu_item_target', ''),
(36, 24, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(37, 24, '_menu_item_xfn', ''),
(38, 24, '_menu_item_url', ''),
(40, 25, '_menu_item_type', 'post_type'),
(41, 25, '_menu_item_menu_item_parent', '24'),
(42, 25, '_menu_item_object_id', '16'),
(43, 25, '_menu_item_object', 'page'),
(44, 25, '_menu_item_target', ''),
(45, 25, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(46, 25, '_menu_item_xfn', ''),
(47, 25, '_menu_item_url', ''),
(49, 26, '_menu_item_type', 'post_type'),
(50, 26, '_menu_item_menu_item_parent', '0'),
(51, 26, '_menu_item_object_id', '8'),
(52, 26, '_menu_item_object', 'page'),
(53, 26, '_menu_item_target', ''),
(54, 26, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(55, 26, '_menu_item_xfn', ''),
(56, 26, '_menu_item_url', ''),
(58, 27, '_menu_item_type', 'post_type'),
(59, 27, '_menu_item_menu_item_parent', '0'),
(60, 27, '_menu_item_object_id', '20'),
(61, 27, '_menu_item_object', 'page'),
(62, 27, '_menu_item_target', ''),
(63, 27, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(64, 27, '_menu_item_xfn', ''),
(65, 27, '_menu_item_url', ''),
(67, 28, '_menu_item_type', 'post_type'),
(68, 28, '_menu_item_menu_item_parent', '0'),
(69, 28, '_menu_item_object_id', '18'),
(70, 28, '_menu_item_object', 'page'),
(71, 28, '_menu_item_target', ''),
(72, 28, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(73, 28, '_menu_item_xfn', ''),
(74, 28, '_menu_item_url', ''),
(76, 29, '_menu_item_type', 'post_type'),
(77, 29, '_menu_item_menu_item_parent', '0'),
(78, 29, '_menu_item_object_id', '10'),
(79, 29, '_menu_item_object', 'page'),
(80, 29, '_menu_item_target', ''),
(81, 29, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(82, 29, '_menu_item_xfn', ''),
(83, 29, '_menu_item_url', ''),
(85, 30, '_menu_item_type', 'post_type'),
(86, 30, '_menu_item_menu_item_parent', '29'),
(87, 30, '_menu_item_object_id', '12'),
(88, 30, '_menu_item_object', 'page'),
(89, 30, '_menu_item_target', ''),
(90, 30, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(91, 30, '_menu_item_xfn', ''),
(92, 30, '_menu_item_url', ''),
(94, 32, '_menu_item_type', 'custom'),
(95, 32, '_menu_item_menu_item_parent', '0'),
(96, 32, '_menu_item_object_id', '32'),
(97, 32, '_menu_item_object', 'custom'),
(98, 32, '_menu_item_target', ''),
(99, 32, '_menu_item_classes', 'a:1:{i:0;s:0:"";}'),
(100, 32, '_menu_item_xfn', ''),
(101, 32, '_menu_item_url', 'http://fullbrook-floor.vm/help-advice'),
(102, 32, '_menu_item_orphaned', '1593725039'),
(103, 33, '_menu_item_type', 'custom'),
(104, 33, '_menu_item_menu_item_parent', '0'),
(105, 33, '_menu_item_object_id', '33'),
(106, 33, '_menu_item_object', 'custom'),
(107, 33, '_menu_item_target', ''),
(108, 33, '_menu_item_classes', 'a:1:{i:0;s:0:"";}') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(109, 33, '_menu_item_xfn', ''),
(110, 33, '_menu_item_url', 'http://fullbrook-floor.vm/help-advice/'),
(112, 36, '_edit_last', '1'),
(113, 36, '_edit_lock', '1594401877:1'),
(114, 37, '_wp_attached_file', '2020/07/placeholder-images-image_large.png'),
(115, 37, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:480;s:6:"height";i:480;s:4:"file";s:42:"2020/07/placeholder-images-image_large.png";s:5:"sizes";a:2:{s:6:"medium";a:4:{s:4:"file";s:42:"placeholder-images-image_large-300x300.png";s:5:"width";i:300;s:6:"height";i:300;s:9:"mime-type";s:9:"image/png";}s:9:"thumbnail";a:4:{s:4:"file";s:42:"placeholder-images-image_large-150x150.png";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:9:"image/png";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(116, 36, '_thumbnail_id', '122'),
(117, 36, '_yoast_wpseo_primary_help-advice-categories', '10'),
(118, 36, '_yoast_wpseo_content_score', '30'),
(119, 38, '_thumbnail_id', '67'),
(120, 38, '_yoast_wpseo_primary_help-advice-categories', '10'),
(121, 38, '_yoast_wpseo_content_score', '30'),
(122, 38, '_dp_original', '36'),
(123, 39, '_thumbnail_id', '119'),
(124, 39, '_yoast_wpseo_primary_help-advice-categories', '10'),
(125, 39, '_yoast_wpseo_content_score', '30'),
(126, 39, '_dp_original', '36'),
(127, 40, '_thumbnail_id', '120'),
(128, 40, '_yoast_wpseo_primary_help-advice-categories', '10'),
(129, 40, '_yoast_wpseo_content_score', '30'),
(130, 40, '_dp_original', '36'),
(131, 41, '_thumbnail_id', '67'),
(132, 41, '_yoast_wpseo_primary_help-advice-categories', '10'),
(133, 41, '_yoast_wpseo_content_score', '30'),
(134, 41, '_dp_original', '36'),
(135, 42, '_thumbnail_id', '122'),
(136, 42, '_yoast_wpseo_primary_help-advice-categories', '10'),
(137, 42, '_yoast_wpseo_content_score', '30'),
(138, 42, '_dp_original', '36'),
(139, 43, '_thumbnail_id', '121'),
(140, 43, '_yoast_wpseo_primary_help-advice-categories', '10'),
(141, 43, '_yoast_wpseo_content_score', '30'),
(142, 43, '_dp_original', '36'),
(143, 44, '_thumbnail_id', '119'),
(144, 44, '_yoast_wpseo_primary_help-advice-categories', '10'),
(145, 44, '_yoast_wpseo_content_score', '30'),
(146, 44, '_dp_original', '36'),
(147, 45, '_thumbnail_id', '67'),
(148, 45, '_yoast_wpseo_primary_help-advice-categories', '10'),
(149, 45, '_yoast_wpseo_content_score', '30'),
(150, 45, '_dp_original', '36'),
(151, 46, '_thumbnail_id', '121'),
(152, 46, '_yoast_wpseo_primary_help-advice-categories', '10'),
(153, 46, '_yoast_wpseo_content_score', '30'),
(154, 46, '_dp_original', '36'),
(155, 38, '_edit_last', '1'),
(156, 38, '_edit_lock', '1594401878:1'),
(157, 39, '_edit_last', '1'),
(158, 39, '_edit_lock', '1594401878:1'),
(159, 43, '_edit_last', '1'),
(160, 43, '_edit_lock', '1594401878:1'),
(161, 42, '_edit_last', '1'),
(162, 42, '_edit_lock', '1594401879:1'),
(163, 41, '_edit_last', '1'),
(164, 41, '_edit_lock', '1594401879:1'),
(165, 40, '_edit_last', '1'),
(166, 40, '_edit_lock', '1594401879:1'),
(167, 46, '_edit_last', '1'),
(168, 46, '_edit_lock', '1594402353:1'),
(169, 45, '_edit_last', '1'),
(170, 45, '_edit_lock', '1594401876:1'),
(171, 44, '_edit_last', '1'),
(172, 44, '_edit_lock', '1594475419:1'),
(173, 47, '_edit_last', '1'),
(174, 47, '_edit_lock', '1594832214:1'),
(179, 57, '_wp_attached_file', '2020/07/logo-vertical.svg'),
(180, 58, '_wp_attached_file', '2020/07/logo-vertical-white.svg'),
(181, 59, '_wp_attached_file', '2020/07/logo-horizontal.svg'),
(182, 60, '_wp_attached_file', '2020/07/logo-horizontal-white.svg'),
(183, 61, '_edit_last', '1'),
(184, 61, '_edit_lock', '1594831522:1'),
(185, 6, 'hero_top_line', 'Start your journey with Fullbrook & Floor'),
(186, 6, '_hero_top_line', 'field_5eff38fcccfce'),
(187, 6, 'hero_main_line', 'Experienced local estate agents in St. Albans'),
(188, 6, '_hero_main_line', 'field_5eff3910ccfcf'),
(189, 65, 'hero_top_line', 'Friendly, local'),
(190, 65, '_hero_top_line', 'field_5eff38fcccfce'),
(191, 65, 'hero_main_line', 'Estate Agents'),
(192, 65, '_hero_main_line', 'field_5eff3910ccfcf'),
(193, 66, 'hero_top_line', 'Friendly, local'),
(194, 66, '_hero_top_line', 'field_5eff38fcccfce'),
(195, 66, 'hero_main_line', 'Estate agents in St. Albans'),
(196, 66, '_hero_main_line', 'field_5eff3910ccfcf'),
(197, 67, '_wp_attached_file', '2020/07/istockphoto-1225367483-1024x1024-1.jpg'),
(198, 67, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:1024;s:6:"height";i:682;s:4:"file";s:46:"2020/07/istockphoto-1225367483-1024x1024-1.jpg";s:5:"sizes";a:3:{s:6:"medium";a:4:{s:4:"file";s:46:"istockphoto-1225367483-1024x1024-1-300x200.jpg";s:5:"width";i:300;s:6:"height";i:200;s:9:"mime-type";s:10:"image/jpeg";}s:9:"thumbnail";a:4:{s:4:"file";s:46:"istockphoto-1225367483-1024x1024-1-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}s:12:"medium_large";a:4:{s:4:"file";s:46:"istockphoto-1225367483-1024x1024-1-768x512.jpg";s:5:"width";i:768;s:6:"height";i:512;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:12:"Getty Images";s:6:"camera";s:0:"";s:7:"caption";s:124:"An aerial photo of the Cathedral &amp; City of St Albans in Hertfordshire, England.  \n\nShot during the Coronavirus pandemic.";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(199, 6, '_thumbnail_id', '67'),
(200, 6, '_yoast_wpseo_content_score', '90'),
(201, 68, '_edit_last', '1'),
(202, 68, '_edit_lock', '1594479088:1'),
(203, 8, 'hero_heading', 'Search for a property to buy'),
(204, 8, '_hero_heading', 'field_5eff48a05507c'),
(205, 8, 'has_search_bar', '1'),
(206, 8, '_has_search_bar', 'field_5eff48a65507d'),
(207, 72, 'hero_heading', 'Search for a home to buy'),
(208, 72, '_hero_heading', 'field_5eff48a05507c'),
(209, 72, 'has_search_bar', '1'),
(210, 72, '_has_search_bar', 'field_5eff48a65507d'),
(211, 10, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(212, 10, '_hero_heading', 'field_5eff48a05507c'),
(213, 10, 'has_search_bar', '0') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(214, 10, '_has_search_bar', 'field_5eff48a65507d'),
(215, 76, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(216, 76, '_hero_heading', 'field_5eff48a05507c'),
(217, 76, 'has_search_bar', '0'),
(218, 76, '_has_search_bar', 'field_5eff48a65507d'),
(219, 12, 'hero_heading', 'Get a FREE home valuation'),
(220, 12, '_hero_heading', 'field_5eff48a05507c'),
(221, 12, 'has_search_bar', '0'),
(222, 12, '_has_search_bar', 'field_5eff48a65507d'),
(223, 77, 'hero_heading', 'Get a FREE home valuation'),
(224, 77, '_hero_heading', 'field_5eff48a05507c'),
(225, 77, 'has_search_bar', '0'),
(226, 77, '_has_search_bar', 'field_5eff48a65507d'),
(227, 14, 'hero_heading', 'Learn about Fullbrook & Floor'),
(228, 14, '_hero_heading', 'field_5eff48a05507c'),
(229, 14, 'has_search_bar', '0'),
(230, 14, '_has_search_bar', 'field_5eff48a65507d'),
(231, 78, 'hero_heading', 'Learn about Fullbrook & Floor'),
(232, 78, '_hero_heading', 'field_5eff48a05507c'),
(233, 78, 'has_search_bar', '0'),
(234, 78, '_has_search_bar', 'field_5eff48a65507d'),
(235, 16, 'hero_heading', 'Meet Rene & Rod'),
(236, 16, '_hero_heading', 'field_5eff48a05507c'),
(237, 16, 'has_search_bar', '0'),
(238, 16, '_has_search_bar', 'field_5eff48a65507d'),
(239, 79, 'hero_heading', 'Meet Fullbrook & Floor'),
(240, 79, '_hero_heading', 'field_5eff48a05507c'),
(241, 79, 'has_search_bar', '0'),
(242, 79, '_has_search_bar', 'field_5eff48a65507d'),
(243, 20, 'hero_heading', 'Contact Fullbrook & Floor'),
(244, 20, '_hero_heading', 'field_5eff48a05507c'),
(245, 20, 'has_search_bar', '0'),
(246, 20, '_has_search_bar', 'field_5eff48a65507d'),
(247, 80, 'hero_heading', 'Contact Fullbrook & Floor'),
(248, 80, '_hero_heading', 'field_5eff48a05507c'),
(249, 80, 'has_search_bar', '0'),
(250, 80, '_has_search_bar', 'field_5eff48a65507d'),
(251, 18, 'hero_heading', 'Our guide to selling your home'),
(252, 18, '_hero_heading', 'field_5eff48a05507c'),
(253, 18, 'has_search_bar', '0'),
(254, 18, '_has_search_bar', 'field_5eff48a65507d'),
(255, 81, 'hero_heading', 'Our guide to selling a home'),
(256, 81, '_hero_heading', 'field_5eff48a05507c'),
(257, 81, 'has_search_bar', '0'),
(258, 81, '_has_search_bar', 'field_5eff48a65507d'),
(259, 82, 'hero_heading', 'Our guide to selling your home'),
(260, 82, '_hero_heading', 'field_5eff48a05507c'),
(261, 82, 'has_search_bar', '0'),
(262, 82, '_has_search_bar', 'field_5eff48a65507d'),
(263, 83, '_edit_last', '1'),
(264, 83, '_edit_lock', '1594834966:1'),
(265, 91, '_wp_attached_file', '2020/07/profile-image.png'),
(266, 91, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:248;s:6:"height";i:348;s:4:"file";s:25:"2020/07/profile-image.png";s:5:"sizes";a:2:{s:6:"medium";a:4:{s:4:"file";s:25:"profile-image-214x300.png";s:5:"width";i:214;s:6:"height";i:300;s:9:"mime-type";s:9:"image/png";}s:9:"thumbnail";a:4:{s:4:"file";s:25:"profile-image-150x150.png";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:9:"image/png";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(267, 16, 'team_members_0_name', 'Rod Fullbrook'),
(268, 16, '_team_members_0_name', 'field_5eff534e75de2'),
(269, 16, 'team_members_0_job_title', 'Director'),
(270, 16, '_team_members_0_job_title', 'field_5eff535575de3'),
(271, 16, 'team_members_0_phone_number', '020 000 0000'),
(272, 16, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(273, 16, 'team_members_0_email_address', 'rod@fullbrookandfloor.co.uk'),
(274, 16, '_team_members_0_email_address', 'field_5eff536475de5'),
(275, 16, 'team_members_0_profile_photo', '91'),
(276, 16, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(277, 16, 'team_members_0_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is an aspiring golfer with an enviable single-figure handicap, and Sam provides his footballing talents to the local Harvesters football club.\r\n\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(278, 16, '_team_members_0_biography', 'field_5eff536f75de6'),
(279, 16, 'team_members_1_name', 'Rene Floor'),
(280, 16, '_team_members_1_name', 'field_5eff534e75de2'),
(281, 16, 'team_members_1_job_title', 'Director'),
(282, 16, '_team_members_1_job_title', 'field_5eff535575de3'),
(283, 16, 'team_members_1_phone_number', '020 000 0000'),
(284, 16, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(285, 16, 'team_members_1_email_address', 'rene@fullbrookandfloor.co.uk'),
(286, 16, '_team_members_1_email_address', 'field_5eff536475de5'),
(287, 16, 'team_members_1_profile_photo', '91'),
(288, 16, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(289, 16, 'team_members_1_biography', 'René met his English wife Jo in the mid 90s, and they lived near Amsterdam until 2005, when they moved to Hertfordshire. René started his agency career at a respectable agency in Amsterdam, describing working in the Dutch property market as “different in the buying and selling process, but with the same key elements - helping people with the biggest assets of their lives”. Since 2005, René has worked for two agencies in St. Albans, mastering the challenges faced in a different property market and helping people find their ideal home.\r\n\r\nOutside of work, René loves to spend time with his sports-mad family: his wife Jo is a keen hockey player, and his son Casper is an enthusiastic judoka, and Jack is a promising footballer and hockey player. When not spending time with his family, René likes to test his own sporting talents through running, tennis and a kickabout with friends.'),
(290, 16, '_team_members_1_biography', 'field_5eff536f75de6'),
(291, 16, 'team_members', '2'),
(292, 16, '_team_members', 'field_5eff533075de0'),
(293, 92, 'hero_heading', 'Meet Fullbrook & Floor'),
(294, 92, '_hero_heading', 'field_5eff48a05507c'),
(295, 92, 'has_search_bar', '0'),
(296, 92, '_has_search_bar', 'field_5eff48a65507d'),
(297, 92, 'team_members_0_name', 'Rene Floor'),
(298, 92, '_team_members_0_name', 'field_5eff534e75de2'),
(299, 92, 'team_members_0_job_title', 'Director'),
(300, 92, '_team_members_0_job_title', 'field_5eff535575de3'),
(301, 92, 'team_members_0_phone_number', '020 000 0000'),
(302, 92, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(303, 92, 'team_members_0_email_address', 'rene@fullbrookandfloor.co.uk'),
(304, 92, '_team_members_0_email_address', 'field_5eff536475de5'),
(305, 92, 'team_members_0_profile_photo', '91'),
(306, 92, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(307, 92, 'team_members_0_biography', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer a erat eget nisl eleifend semper id in lorem. Etiam eleifend ullamcorper tempor. Maecenas ac magna et erat porttitor ultrices. Sed nec scelerisque libero, ac condimentum sapien. Etiam et lectus id ipsum gravida elementum id sed dui. Mauris est sem, aliquet eu tristique tincidunt, volutpat ac mi. Curabitur sit amet tristique ligula. Nullam vel finibus ante, in posuere orci. Interdum et malesuada fames ac ante ipsum primis in faucibus.\r\n\r\nNunc egestas arcu non turpis tincidunt feugiat. Praesent blandit magna ac sagittis cursus. Quisque nec erat orci. Suspendisse potenti. Nunc sagittis nisi erat, ac imperdiet sapien rhoncus quis. Suspendisse condimentum elementum dictum. Nulla facilisi. Sed sed nisl dictum, venenatis nisi in, dignissim nisl. Nullam et eros eget nibh ornare tempor sit amet in nisi. Integer convallis ante eu lectus euismod, a molestie nulla volutpat. Morbi elementum purus eu justo varius, nec iaculis nunc finibus. Donec eu ex euismod, eleifend massa in, hendrerit leo. Vestibulum dapibus orci cursus velit eleifend, eu cursus nunc tincidunt. Ut est lectus, egestas quis neque ac, bibendum vestibulum nisl. Praesent pulvinar, elit sed accumsan fermentum, nisi risus aliquet orci, eu cursus est augue sit amet sem.'),
(308, 92, '_team_members_0_biography', 'field_5eff536f75de6'),
(309, 92, 'team_members_1_name', 'Rod Fullbrook'),
(310, 92, '_team_members_1_name', 'field_5eff534e75de2'),
(311, 92, 'team_members_1_job_title', 'Director'),
(312, 92, '_team_members_1_job_title', 'field_5eff535575de3'),
(313, 92, 'team_members_1_phone_number', '020 000 0000') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(314, 92, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(315, 92, 'team_members_1_email_address', 'rod@fullbrookandfloor.co.uk'),
(316, 92, '_team_members_1_email_address', 'field_5eff536475de5'),
(317, 92, 'team_members_1_profile_photo', '91'),
(318, 92, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(319, 92, 'team_members_1_biography', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer a erat eget nisl eleifend semper id in lorem. Etiam eleifend ullamcorper tempor. Maecenas ac magna et erat porttitor ultrices. Sed nec scelerisque libero, ac condimentum sapien. Etiam et lectus id ipsum gravida elementum id sed dui. Mauris est sem, aliquet eu tristique tincidunt, volutpat ac mi. Curabitur sit amet tristique ligula. Nullam vel finibus ante, in posuere orci. Interdum et malesuada fames ac ante ipsum primis in faucibus.\r\n\r\nNunc egestas arcu non turpis tincidunt feugiat. Praesent blandit magna ac sagittis cursus. Quisque nec erat orci. Suspendisse potenti. Nunc sagittis nisi erat, ac imperdiet sapien rhoncus quis. Suspendisse condimentum elementum dictum. Nulla facilisi. Sed sed nisl dictum, venenatis nisi in, dignissim nisl. Nullam et eros eget nibh ornare tempor sit amet in nisi. Integer convallis ante eu lectus euismod, a molestie nulla volutpat. Morbi elementum purus eu justo varius, nec iaculis nunc finibus. Donec eu ex euismod, eleifend massa in, hendrerit leo. Vestibulum dapibus orci cursus velit eleifend, eu cursus nunc tincidunt. Ut est lectus, egestas quis neque ac, bibendum vestibulum nisl. Praesent pulvinar, elit sed accumsan fermentum, nisi risus aliquet orci, eu cursus est augue sit amet sem.'),
(320, 92, '_team_members_1_biography', 'field_5eff536f75de6'),
(321, 92, 'team_members', '2'),
(322, 92, '_team_members', 'field_5eff533075de0'),
(323, 100, 'hero_top_line', 'Friendly, local'),
(324, 100, '_hero_top_line', 'field_5eff38fcccfce'),
(325, 100, 'hero_main_line', 'Estate agents in St. Albans'),
(326, 100, '_hero_main_line', 'field_5eff3910ccfcf'),
(327, 6, 'h1', 'Estate agents in St. Albans'),
(328, 6, '_h1', 'field_5f04a835c98a9'),
(329, 103, 'hero_top_line', 'Friendly, local'),
(330, 103, '_hero_top_line', 'field_5eff38fcccfce'),
(331, 103, 'hero_main_line', 'Estate agents in St. Albans'),
(332, 103, '_hero_main_line', 'field_5eff3910ccfcf'),
(333, 103, 'h1', 'Estate agents in St. Albans'),
(334, 103, '_h1', 'field_5f04a835c98a9'),
(335, 10, '_yoast_wpseo_content_score', '90'),
(336, 104, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(337, 104, '_hero_heading', 'field_5eff48a05507c'),
(338, 104, 'has_search_bar', '0'),
(339, 104, '_has_search_bar', 'field_5eff48a65507d'),
(340, 12, '_yoast_wpseo_content_score', '60'),
(341, 105, 'hero_heading', 'Get a FREE home valuation'),
(342, 105, '_hero_heading', 'field_5eff48a05507c'),
(343, 105, 'has_search_bar', '0'),
(344, 105, '_has_search_bar', 'field_5eff48a65507d'),
(345, 18, '_yoast_wpseo_content_score', '60'),
(346, 106, 'hero_heading', 'Our guide to selling your home'),
(347, 106, '_hero_heading', 'field_5eff48a05507c'),
(348, 106, 'has_search_bar', '0'),
(349, 106, '_has_search_bar', 'field_5eff48a65507d'),
(350, 8, '_yoast_wpseo_content_score', '90'),
(351, 107, 'hero_heading', 'Search for a home to buy'),
(352, 107, '_hero_heading', 'field_5eff48a05507c'),
(353, 107, 'has_search_bar', '1'),
(354, 107, '_has_search_bar', 'field_5eff48a65507d'),
(355, 14, '_yoast_wpseo_content_score', '30'),
(356, 108, 'hero_heading', 'Learn about Fullbrook & Floor'),
(357, 108, '_hero_heading', 'field_5eff48a05507c'),
(358, 108, 'has_search_bar', '0'),
(359, 108, '_has_search_bar', 'field_5eff48a65507d'),
(360, 10, 'h1', 'Sell your home with us'),
(361, 10, '_h1', 'field_5f04e1b0f6df1'),
(362, 111, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(363, 111, '_hero_heading', 'field_5eff48a05507c'),
(364, 111, 'has_search_bar', '0'),
(365, 111, '_has_search_bar', 'field_5eff48a65507d'),
(366, 111, 'h1', 'Sell your home with us'),
(367, 111, '_h1', 'field_5f04e1b0f6df1'),
(368, 112, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(369, 112, '_hero_heading', 'field_5eff48a05507c'),
(370, 112, 'has_search_bar', '0'),
(371, 112, '_has_search_bar', 'field_5eff48a65507d'),
(372, 112, 'h1', 'Sell your home with us'),
(373, 112, '_h1', 'field_5f04e1b0f6df1'),
(374, 113, '_edit_last', '1'),
(375, 113, '_edit_lock', '1594158340:1'),
(406, 10, 'why_choose_us', ''),
(407, 10, '_why_choose_us', 'field_5f04e465f4a3e'),
(408, 118, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(409, 118, '_hero_heading', 'field_5eff48a05507c'),
(410, 118, 'has_search_bar', '0'),
(411, 118, '_has_search_bar', 'field_5eff48a65507d'),
(412, 118, 'h1', 'Sell your home with us'),
(413, 118, '_h1', 'field_5f04e1b0f6df1'),
(414, 118, 'why_choose_us_0_image', '57'),
(415, 118, '_why_choose_us_0_image', 'field_5f04e471f4a3f'),
(416, 118, 'why_choose_us_0_title', 'Powerful Online Strategy'),
(417, 118, '_why_choose_us_0_title', 'field_5f04e477f4a40'),
(418, 118, 'why_choose_us_0_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(419, 118, '_why_choose_us_0_text', 'field_5f04e47cf4a41'),
(420, 118, 'why_choose_us_1_image', '91'),
(421, 118, '_why_choose_us_1_image', 'field_5f04e471f4a3f'),
(422, 118, 'why_choose_us_1_title', 'Powerful Online Strategy'),
(423, 118, '_why_choose_us_1_title', 'field_5f04e477f4a40'),
(424, 118, 'why_choose_us_1_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(425, 118, '_why_choose_us_1_text', 'field_5f04e47cf4a41'),
(426, 118, 'why_choose_us_2_image', '67'),
(427, 118, '_why_choose_us_2_image', 'field_5f04e471f4a3f'),
(428, 118, 'why_choose_us_2_title', 'Powerful Online Strategy'),
(429, 118, '_why_choose_us_2_title', 'field_5f04e477f4a40'),
(430, 118, 'why_choose_us_2_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(431, 118, '_why_choose_us_2_text', 'field_5f04e47cf4a41'),
(432, 118, 'why_choose_us_3_image', '57'),
(433, 118, '_why_choose_us_3_image', 'field_5f04e471f4a3f'),
(434, 118, 'why_choose_us_3_title', 'Powerful Online Strategy'),
(435, 118, '_why_choose_us_3_title', 'field_5f04e477f4a40'),
(436, 118, 'why_choose_us_3_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(437, 118, '_why_choose_us_3_text', 'field_5f04e47cf4a41'),
(438, 118, 'why_choose_us_4_image', '57'),
(439, 118, '_why_choose_us_4_image', 'field_5f04e471f4a3f'),
(440, 118, 'why_choose_us_4_title', 'Powerful Online Strategy'),
(441, 118, '_why_choose_us_4_title', 'field_5f04e477f4a40'),
(442, 118, 'why_choose_us_4_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(443, 118, '_why_choose_us_4_text', 'field_5f04e47cf4a41') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(444, 118, 'why_choose_us', '5'),
(445, 118, '_why_choose_us', 'field_5f04e465f4a3e'),
(446, 119, '_wp_attached_file', '2020/07/iStock-1078076954.jpg'),
(447, 119, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:2121;s:6:"height";i:1414;s:4:"file";s:29:"2020/07/iStock-1078076954.jpg";s:5:"sizes";a:6:{s:6:"medium";a:4:{s:4:"file";s:29:"iStock-1078076954-300x200.jpg";s:5:"width";i:300;s:6:"height";i:200;s:9:"mime-type";s:10:"image/jpeg";}s:5:"large";a:4:{s:4:"file";s:30:"iStock-1078076954-1024x683.jpg";s:5:"width";i:1024;s:6:"height";i:683;s:9:"mime-type";s:10:"image/jpeg";}s:9:"thumbnail";a:4:{s:4:"file";s:29:"iStock-1078076954-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}s:12:"medium_large";a:4:{s:4:"file";s:29:"iStock-1078076954-768x512.jpg";s:5:"width";i:768;s:6:"height";i:512;s:9:"mime-type";s:10:"image/jpeg";}s:9:"1536x1536";a:4:{s:4:"file";s:31:"iStock-1078076954-1536x1024.jpg";s:5:"width";i:1536;s:6:"height";i:1024;s:9:"mime-type";s:10:"image/jpeg";}s:9:"2048x2048";a:4:{s:4:"file";s:31:"iStock-1078076954-2048x1365.jpg";s:5:"width";i:2048;s:6:"height";i:1365;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:3:"1.8";s:6:"credit";s:24:"Getty Images/iStockphoto";s:6:"camera";s:20:"Canon EOS 5D Mark IV";s:7:"caption";s:122:"A point of view shot of a small group of friends arriving at a housewarming party, they have brought gifts for their host.";s:17:"created_timestamp";s:10:"1536950954";s:9:"copyright";s:52:"SOL STOCK LTD (SOL STOCK LTD (Photographer) - [None]";s:12:"focal_length";s:2:"35";s:3:"iso";s:3:"400";s:13:"shutter_speed";s:5:"0.004";s:5:"title";s:37:"Friends Arriving for Social Gathering";s:11:"orientation";s:1:"1";s:8:"keywords";a:0:{}}}'),
(448, 120, '_wp_attached_file', '2020/07/iStock-546201852-scaled.jpg'),
(449, 120, '_wp_attachment_metadata', 'a:6:{s:5:"width";i:2560;s:6:"height";i:1706;s:4:"file";s:35:"2020/07/iStock-546201852-scaled.jpg";s:5:"sizes";a:6:{s:6:"medium";a:4:{s:4:"file";s:28:"iStock-546201852-300x200.jpg";s:5:"width";i:300;s:6:"height";i:200;s:9:"mime-type";s:10:"image/jpeg";}s:5:"large";a:4:{s:4:"file";s:29:"iStock-546201852-1024x682.jpg";s:5:"width";i:1024;s:6:"height";i:682;s:9:"mime-type";s:10:"image/jpeg";}s:9:"thumbnail";a:4:{s:4:"file";s:28:"iStock-546201852-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}s:12:"medium_large";a:4:{s:4:"file";s:28:"iStock-546201852-768x512.jpg";s:5:"width";i:768;s:6:"height";i:512;s:9:"mime-type";s:10:"image/jpeg";}s:9:"1536x1536";a:4:{s:4:"file";s:30:"iStock-546201852-1536x1024.jpg";s:5:"width";i:1536;s:6:"height";i:1024;s:9:"mime-type";s:10:"image/jpeg";}s:9:"2048x2048";a:4:{s:4:"file";s:30:"iStock-546201852-2048x1365.jpg";s:5:"width";i:2048;s:6:"height";i:1365;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"8";s:6:"credit";s:24:"Getty Images/iStockphoto";s:6:"camera";s:14:"Canon EOS-1D X";s:7:"caption";s:53:"Young Family Collecting Keys To New Home From Realtor";s:17:"created_timestamp";s:10:"1462336561";s:9:"copyright";s:20:"monkeybusinessimages";s:12:"focal_length";s:2:"35";s:3:"iso";s:3:"400";s:13:"shutter_speed";s:5:"0.005";s:5:"title";s:53:"Young Family Collecting Keys To New Home From Realtor";s:11:"orientation";s:1:"1";s:8:"keywords";a:33:{i:0;s:16:"Indian Ethnicity";i:1;s:11:"Five People";i:2;s:14:"Home Ownership";i:3;s:10:"Relocation";i:4;s:5:"Women";i:5;s:7:"Females";i:6;s:3:"Men";i:7;s:5:"Males";i:8;s:12:"Moving House";i:9;s:11:"30-39 Years";i:10;s:11:"20-29 Years";i:11;s:5:"Child";i:12;s:4:"Baby";i:13;s:6:"Buying";i:14;s:7:"Holding";i:15;s:10:"Picking Up";i:16;s:3:"Key";i:17;s:19:"Caucasian Ethnicity";i:18;s:17:"Mixed Race Person";i:19;s:5:"Empty";i:20;s:8:"Outdoors";i:21;s:10:"Horizontal";i:22;s:17:"Real Estate Agent";i:23;s:3:"Son";i:24;s:8:"Daughter";i:25;s:6:"Mother";i:26;s:6:"Father";i:27;s:6:"Family";i:28;s:6:"People";i:29;s:5:"House";i:30;s:4:"Sign";i:31;s:17:"First Time Buyers";i:32;s:8:"Mortgage";}}s:14:"original_image";s:20:"iStock-546201852.jpg";}'),
(450, 121, '_wp_attached_file', '2020/07/iStock-481304898.jpg'),
(451, 121, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:724;s:6:"height";i:483;s:4:"file";s:28:"2020/07/iStock-481304898.jpg";s:5:"sizes";a:2:{s:6:"medium";a:4:{s:4:"file";s:28:"iStock-481304898-300x200.jpg";s:5:"width";i:300;s:6:"height";i:200;s:9:"mime-type";s:10:"image/jpeg";}s:9:"thumbnail";a:4:{s:4:"file";s:28:"iStock-481304898-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:3:"2.8";s:6:"credit";s:24:"Getty Images/iStockphoto";s:6:"camera";s:20:"Canon EOS 5D Mark II";s:7:"caption";s:90:"Closeup of a young couple using bubble wrap to pack their stuff in boxes before moving out";s:17:"created_timestamp";s:10:"1436810541";s:9:"copyright";s:12:"Antonio_Diaz";s:12:"focal_length";s:2:"53";s:3:"iso";s:3:"100";s:13:"shutter_speed";s:7:"0.00625";s:5:"title";s:23:"Couple packing together";s:11:"orientation";s:1:"1";s:8:"keywords";a:31:{i:0;s:6:"Couple";i:1;s:14:"Home Ownership";i:2;s:10:"Relocation";i:3;s:5:"Women";i:4;s:3:"Men";i:5;s:10:"Two People";i:6;s:11:"Bubble Wrap";i:7;s:12:"Moving House";i:8;s:11:"20-29 Years";i:9;s:11:"Young Adult";i:10;s:5:"Adult";i:11;s:7:"Packing";i:12;s:8:"Newlywed";i:13;s:12:"Togetherness";i:14;s:9:"Cardboard";i:15;s:3:"New";i:16;s:7:"Indoors";i:17;s:8:"Close-up";i:18;s:7:"Married";i:19;s:19:"Heterosexual Couple";i:20;s:9:"Apartment";i:21;s:5:"House";i:22;s:13:"Home Interior";i:23;s:15:"Casual Clothing";i:24;s:8:"Crockery";i:25;s:15:"Box - Container";i:26;s:7:"Husband";i:27;s:4:"Wife";i:28;s:10:"Girlfriend";i:29;s:9:"Boyfriend";i:30;s:8:"Mortgage";}}}'),
(452, 122, '_wp_attached_file', '2020/07/3122736571_f.jpg'),
(453, 122, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:1200;s:6:"height";i:800;s:4:"file";s:24:"2020/07/3122736571_f.jpg";s:5:"sizes";a:4:{s:6:"medium";a:4:{s:4:"file";s:24:"3122736571_f-300x200.jpg";s:5:"width";i:300;s:6:"height";i:200;s:9:"mime-type";s:10:"image/jpeg";}s:5:"large";a:4:{s:4:"file";s:25:"3122736571_f-1024x683.jpg";s:5:"width";i:1024;s:6:"height";i:683;s:9:"mime-type";s:10:"image/jpeg";}s:9:"thumbnail";a:4:{s:4:"file";s:24:"3122736571_f-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}s:12:"medium_large";a:4:{s:4:"file";s:24:"3122736571_f-768x512.jpg";s:5:"width";i:768;s:6:"height";i:512;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(454, 10, 'show_why_choose_us', '1'),
(455, 10, '_show_why_choose_us', 'field_5f04ecb34e432'),
(456, 10, 'show_buckets', '1'),
(457, 10, '_show_buckets', 'field_5f04ecbe4e433'),
(458, 128, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(459, 128, '_hero_heading', 'field_5eff48a05507c'),
(460, 128, 'has_search_bar', '0'),
(461, 128, '_has_search_bar', 'field_5eff48a65507d'),
(462, 128, 'h1', 'Sell your home with us'),
(463, 128, '_h1', 'field_5f04e1b0f6df1'),
(464, 128, 'why_choose_us_0_image', '57'),
(465, 128, '_why_choose_us_0_image', 'field_5f04e471f4a3f'),
(466, 128, 'why_choose_us_0_title', 'Powerful Online Strategy'),
(467, 128, '_why_choose_us_0_title', 'field_5f04e477f4a40'),
(468, 128, 'why_choose_us_0_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(469, 128, '_why_choose_us_0_text', 'field_5f04e47cf4a41'),
(470, 128, 'why_choose_us_1_image', '91'),
(471, 128, '_why_choose_us_1_image', 'field_5f04e471f4a3f'),
(472, 128, 'why_choose_us_1_title', 'Powerful Online Strategy'),
(473, 128, '_why_choose_us_1_title', 'field_5f04e477f4a40'),
(474, 128, 'why_choose_us_1_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(475, 128, '_why_choose_us_1_text', 'field_5f04e47cf4a41'),
(476, 128, 'why_choose_us_2_image', '67'),
(477, 128, '_why_choose_us_2_image', 'field_5f04e471f4a3f'),
(478, 128, 'why_choose_us_2_title', 'Powerful Online Strategy'),
(479, 128, '_why_choose_us_2_title', 'field_5f04e477f4a40'),
(480, 128, 'why_choose_us_2_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(481, 128, '_why_choose_us_2_text', 'field_5f04e47cf4a41'),
(482, 128, 'why_choose_us_3_image', '57'),
(483, 128, '_why_choose_us_3_image', 'field_5f04e471f4a3f'),
(484, 128, 'why_choose_us_3_title', 'Powerful Online Strategy'),
(485, 128, '_why_choose_us_3_title', 'field_5f04e477f4a40'),
(486, 128, 'why_choose_us_3_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(487, 128, '_why_choose_us_3_text', 'field_5f04e47cf4a41'),
(488, 128, 'why_choose_us_4_image', '57'),
(489, 128, '_why_choose_us_4_image', 'field_5f04e471f4a3f'),
(490, 128, 'why_choose_us_4_title', 'Powerful Online Strategy'),
(491, 128, '_why_choose_us_4_title', 'field_5f04e477f4a40'),
(492, 128, 'why_choose_us_4_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(493, 128, '_why_choose_us_4_text', 'field_5f04e47cf4a41'),
(494, 128, 'why_choose_us', '5'),
(495, 128, '_why_choose_us', 'field_5f04e465f4a3e'),
(496, 128, 'show_why_choose_us', '1'),
(497, 128, '_show_why_choose_us', 'field_5f04ecb34e432'),
(498, 128, 'show_buckets', '0'),
(499, 128, '_show_buckets', 'field_5f04ecbe4e433'),
(500, 129, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(501, 129, '_hero_heading', 'field_5eff48a05507c'),
(502, 129, 'has_search_bar', '0'),
(503, 129, '_has_search_bar', 'field_5eff48a65507d'),
(504, 129, 'h1', 'Sell your home with us'),
(505, 129, '_h1', 'field_5f04e1b0f6df1'),
(506, 129, 'why_choose_us_0_image', '57'),
(507, 129, '_why_choose_us_0_image', 'field_5f04e471f4a3f'),
(508, 129, 'why_choose_us_0_title', 'Powerful Online Strategy'),
(509, 129, '_why_choose_us_0_title', 'field_5f04e477f4a40'),
(510, 129, 'why_choose_us_0_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(511, 129, '_why_choose_us_0_text', 'field_5f04e47cf4a41'),
(512, 129, 'why_choose_us_1_image', '91'),
(513, 129, '_why_choose_us_1_image', 'field_5f04e471f4a3f'),
(514, 129, 'why_choose_us_1_title', 'Powerful Online Strategy'),
(515, 129, '_why_choose_us_1_title', 'field_5f04e477f4a40'),
(516, 129, 'why_choose_us_1_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(517, 129, '_why_choose_us_1_text', 'field_5f04e47cf4a41'),
(518, 129, 'why_choose_us_2_image', '67'),
(519, 129, '_why_choose_us_2_image', 'field_5f04e471f4a3f'),
(520, 129, 'why_choose_us_2_title', 'Powerful Online Strategy'),
(521, 129, '_why_choose_us_2_title', 'field_5f04e477f4a40'),
(522, 129, 'why_choose_us_2_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(523, 129, '_why_choose_us_2_text', 'field_5f04e47cf4a41'),
(524, 129, 'why_choose_us_3_image', '57'),
(525, 129, '_why_choose_us_3_image', 'field_5f04e471f4a3f'),
(526, 129, 'why_choose_us_3_title', 'Powerful Online Strategy'),
(527, 129, '_why_choose_us_3_title', 'field_5f04e477f4a40'),
(528, 129, 'why_choose_us_3_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(529, 129, '_why_choose_us_3_text', 'field_5f04e47cf4a41'),
(530, 129, 'why_choose_us_4_image', '57'),
(531, 129, '_why_choose_us_4_image', 'field_5f04e471f4a3f'),
(532, 129, 'why_choose_us_4_title', 'Powerful Online Strategy'),
(533, 129, '_why_choose_us_4_title', 'field_5f04e477f4a40'),
(534, 129, 'why_choose_us_4_text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusantium, deleniti beatae qui quam vitae dolor maxime libero animi amet quidem velit voluptatum, dolorum quis?'),
(535, 129, '_why_choose_us_4_text', 'field_5f04e47cf4a41'),
(536, 129, 'why_choose_us', '5'),
(537, 129, '_why_choose_us', 'field_5f04e465f4a3e'),
(538, 129, 'show_why_choose_us', '0'),
(539, 129, '_show_why_choose_us', 'field_5f04ecb34e432'),
(540, 129, 'show_buckets', '0'),
(541, 129, '_show_buckets', 'field_5f04ecbe4e433'),
(542, 10, 'show_team', '1'),
(543, 10, '_show_team', 'field_5f04ed2a64341') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(544, 131, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(545, 131, '_hero_heading', 'field_5eff48a05507c'),
(546, 131, 'has_search_bar', '0'),
(547, 131, '_has_search_bar', 'field_5eff48a65507d'),
(548, 131, 'h1', 'Sell your home with us'),
(549, 131, '_h1', 'field_5f04e1b0f6df1'),
(550, 131, 'why_choose_us', ''),
(551, 131, '_why_choose_us', 'field_5f04e465f4a3e'),
(552, 131, 'show_why_choose_us', '0'),
(553, 131, '_show_why_choose_us', 'field_5f04ecb34e432'),
(554, 131, 'show_buckets', '0'),
(555, 131, '_show_buckets', 'field_5f04ecbe4e433'),
(556, 131, 'show_team', '0'),
(557, 131, '_show_team', 'field_5f04ed2a64341'),
(558, 113, '_wp_trash_meta_status', 'publish'),
(559, 113, '_wp_trash_meta_time', '1594158486'),
(560, 113, '_wp_desired_post_slug', 'group_5f04e460be7f0'),
(561, 133, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(562, 133, '_hero_heading', 'field_5eff48a05507c'),
(563, 133, 'has_search_bar', '0'),
(564, 133, '_has_search_bar', 'field_5eff48a65507d'),
(565, 133, 'h1', 'Sell your home with us'),
(566, 133, '_h1', 'field_5f04e1b0f6df1'),
(567, 133, 'why_choose_us', ''),
(568, 133, '_why_choose_us', 'field_5f04e465f4a3e'),
(569, 133, 'show_why_choose_us', '1'),
(570, 133, '_show_why_choose_us', 'field_5f04ecb34e432'),
(571, 133, 'show_buckets', '0'),
(572, 133, '_show_buckets', 'field_5f04ecbe4e433'),
(573, 133, 'show_team', '0'),
(574, 133, '_show_team', 'field_5f04ed2a64341'),
(575, 134, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(576, 134, '_hero_heading', 'field_5eff48a05507c'),
(577, 134, 'has_search_bar', '0'),
(578, 134, '_has_search_bar', 'field_5eff48a65507d'),
(579, 134, 'h1', 'Sell your home with us'),
(580, 134, '_h1', 'field_5f04e1b0f6df1'),
(581, 134, 'why_choose_us', ''),
(582, 134, '_why_choose_us', 'field_5f04e465f4a3e'),
(583, 134, 'show_why_choose_us', '1'),
(584, 134, '_show_why_choose_us', 'field_5f04ecb34e432'),
(585, 134, 'show_buckets', '1'),
(586, 134, '_show_buckets', 'field_5f04ecbe4e433'),
(587, 134, 'show_team', '0'),
(588, 134, '_show_team', 'field_5f04ed2a64341'),
(589, 135, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(590, 135, '_hero_heading', 'field_5eff48a05507c'),
(591, 135, 'has_search_bar', '0'),
(592, 135, '_has_search_bar', 'field_5eff48a65507d'),
(593, 135, 'h1', 'Sell your home with us'),
(594, 135, '_h1', 'field_5f04e1b0f6df1'),
(595, 135, 'why_choose_us', ''),
(596, 135, '_why_choose_us', 'field_5f04e465f4a3e'),
(597, 135, 'show_why_choose_us', '1'),
(598, 135, '_show_why_choose_us', 'field_5f04ecb34e432'),
(599, 135, 'show_buckets', '1'),
(600, 135, '_show_buckets', 'field_5f04ecbe4e433'),
(601, 135, 'show_team', '1'),
(602, 135, '_show_team', 'field_5f04ed2a64341'),
(605, 137, '_wp_attached_file', '2020/07/Property-Ombudsman-Logo-e1594386561769.png'),
(606, 137, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:570;s:6:"height";i:173;s:4:"file";s:50:"2020/07/Property-Ombudsman-Logo-e1594386561769.png";s:5:"sizes";a:2:{s:6:"medium";a:4:{s:4:"file";s:49:"Property-Ombudsman-Logo-e1594386561769-300x91.png";s:5:"width";i:300;s:6:"height";i:91;s:9:"mime-type";s:9:"image/png";}s:9:"thumbnail";a:4:{s:4:"file";s:50:"Property-Ombudsman-Logo-e1594386561769-150x150.png";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:9:"image/png";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(607, 138, '_wp_attached_file', '2020/07/property-mark.png'),
(608, 138, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:300;s:6:"height";i:109;s:4:"file";s:25:"2020/07/property-mark.png";s:5:"sizes";a:1:{s:9:"thumbnail";a:4:{s:4:"file";s:25:"property-mark-150x109.png";s:5:"width";i:150;s:6:"height";i:109;s:9:"mime-type";s:9:"image/png";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(609, 137, '_wp_attachment_backup_sizes', 'a:3:{s:9:"full-orig";a:3:{s:5:"width";i:570;s:6:"height";i:197;s:4:"file";s:27:"Property-Ombudsman-Logo.png";}s:14:"thumbnail-orig";a:4:{s:4:"file";s:35:"Property-Ombudsman-Logo-150x150.png";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:9:"image/png";}s:11:"medium-orig";a:4:{s:4:"file";s:35:"Property-Ombudsman-Logo-300x104.png";s:5:"width";i:300;s:6:"height";i:104;s:9:"mime-type";s:9:"image/png";}}'),
(610, 139, '_edit_last', '1'),
(611, 139, '_edit_lock', '1594394146:1'),
(612, 18, 'guide_steps_0_image', '121'),
(613, 18, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(614, 18, 'guide_steps_0_title', 'Make your home look its best'),
(615, 18, '_guide_steps_0_title', 'field_5f087a079a053'),
(616, 18, 'guide_steps_0_content', 'When you make the decision to sell your home, agents will establish pricing for the property. Think about whether you’d like to stay in the same area, too: we’ll be able to advise you on the market around the region.'),
(617, 18, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(618, 18, 'guide_steps_0_is_highlighted', '0'),
(619, 18, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(620, 18, 'guide_steps_1_image', '119'),
(621, 18, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(622, 18, 'guide_steps_1_title', 'Get your property valued'),
(623, 18, '_guide_steps_1_title', 'field_5f087a079a053'),
(624, 18, 'guide_steps_1_content', 'We recommend putting your house on the market before making an offer on a house you’d like to buy. Make sure your home is looking its absolute best so you can attract potential buyers.'),
(625, 18, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(626, 18, 'guide_steps_1_is_highlighted', '0'),
(627, 18, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(628, 18, 'guide_steps_2_image', '67'),
(629, 18, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(630, 18, 'guide_steps_2_title', 'Get the property on the market'),
(631, 18, '_guide_steps_2_title', 'field_5f087a079a053'),
(632, 18, 'guide_steps_2_content', 'Once we’ve determined the terms and conditions, we can then finalise the marketing approach.'),
(633, 18, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(634, 18, 'guide_steps_2_is_highlighted', '0'),
(635, 18, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(636, 18, 'guide_steps_3_image', '121'),
(637, 18, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(638, 18, 'guide_steps_3_title', 'Carry out viewings'),
(639, 18, '_guide_steps_3_title', 'field_5f087a079a053'),
(640, 18, 'guide_steps_3_content', 'We always advise the agent to carry out the viewings; people often feel more comfortable giving honest feedback to agents.'),
(641, 18, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(642, 18, 'guide_steps_3_is_highlighted', '0'),
(643, 18, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(644, 18, 'guide_steps_4_image', '120'),
(645, 18, '_guide_steps_4_image', 'field_5f0879fa9a052') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(646, 18, 'guide_steps_4_title', 'Review any feedback'),
(647, 18, '_guide_steps_4_title', 'field_5f087a079a053'),
(648, 18, 'guide_steps_4_content', 'Any feedback we receive will be passed on to you promptly.'),
(649, 18, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(650, 18, 'guide_steps_4_is_highlighted', '0'),
(651, 18, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(652, 18, 'guide_steps_5_image', '119'),
(653, 18, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(654, 18, 'guide_steps_5_title', 'Receive offers to buy'),
(655, 18, '_guide_steps_5_title', 'field_5f087a079a053'),
(656, 18, 'guide_steps_5_content', 'When an offer is received, the agent will check and vet the potential buyer, finance, and chain and discuss the next steps with the seller. We’ll work on your behalf to get you the best possible offer from the best possible buyer.'),
(657, 18, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(658, 18, 'guide_steps_5_is_highlighted', '0'),
(659, 18, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(660, 18, 'guide_steps_6_image', '120'),
(661, 18, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(662, 18, 'guide_steps_6_title', 'Accept an offer'),
(663, 18, '_guide_steps_6_title', 'field_5f087a079a053'),
(664, 18, 'guide_steps_6_content', 'When the offer is accepted, we’ll send out the memorandum of sale: this includes all relevant information regarding the sale.'),
(665, 18, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(666, 18, 'guide_steps_6_is_highlighted', '0'),
(667, 18, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(668, 18, 'guide_steps_7_image', '122'),
(669, 18, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(670, 18, 'guide_steps_7_title', 'Hire a solicitor'),
(671, 18, '_guide_steps_7_title', 'field_5f087a079a053'),
(672, 18, 'guide_steps_7_content', 'Solicitors will be instructed, and we will oversee the sale, communicating with all parties in the chain to make this process as smooth as possible.'),
(673, 18, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(674, 18, 'guide_steps_7_is_highlighted', '0'),
(675, 18, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(676, 18, 'guide_steps_8_image', '67'),
(677, 18, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(678, 18, 'guide_steps_8_title', 'Hire a removals company'),
(679, 18, '_guide_steps_8_title', 'field_5f087a079a053'),
(680, 18, 'guide_steps_8_content', 'Once the sale has been completed, it’s time to hire a removal company.'),
(681, 18, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(682, 18, 'guide_steps_8_is_highlighted', '0'),
(683, 18, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(684, 18, 'guide_steps_9_image', '121'),
(685, 18, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(686, 18, 'guide_steps_9_title', 'Exchange keys!'),
(687, 18, '_guide_steps_9_title', 'field_5f087a079a053'),
(688, 18, 'guide_steps_9_content', 'When the solicitor confirms the funds have been received, we can then hand over the keys to the property.'),
(689, 18, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(690, 18, 'guide_steps_9_is_highlighted', '1'),
(691, 18, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(716, 18, 'guide_steps', '10'),
(717, 18, '_guide_steps', 'field_5f0879ee9a051'),
(718, 18, 'h1', ''),
(719, 18, '_h1', 'field_5f04e1b0f6df1'),
(720, 18, 'show_why_choose_us', '0'),
(721, 18, '_show_why_choose_us', 'field_5f04ecb34e432'),
(722, 18, 'show_buckets', '0'),
(723, 18, '_show_buckets', 'field_5f04ecbe4e433'),
(724, 18, 'show_team', '1'),
(725, 18, '_show_team', 'field_5f04ed2a64341'),
(726, 145, 'hero_heading', 'Our guide to selling your home'),
(727, 145, '_hero_heading', 'field_5eff48a05507c'),
(728, 145, 'has_search_bar', '0'),
(729, 145, '_has_search_bar', 'field_5eff48a65507d'),
(730, 145, 'guide_steps_0_image', ''),
(731, 145, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(732, 145, 'guide_steps_0_title', 'Get your finances in order'),
(733, 145, '_guide_steps_0_title', 'field_5f087a079a053'),
(734, 145, 'guide_steps_0_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(735, 145, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(736, 145, 'guide_steps_0_is_highlighted', '0'),
(737, 145, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(738, 145, 'guide_steps_1_image', ''),
(739, 145, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(740, 145, 'guide_steps_1_title', 'Make your home look its best'),
(741, 145, '_guide_steps_1_title', 'field_5f087a079a053'),
(742, 145, 'guide_steps_1_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(743, 145, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(744, 145, 'guide_steps_1_is_highlighted', '0'),
(745, 145, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(746, 145, 'guide_steps_2_image', ''),
(747, 145, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(748, 145, 'guide_steps_2_title', 'Get your property valued'),
(749, 145, '_guide_steps_2_title', 'field_5f087a079a053'),
(750, 145, 'guide_steps_2_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(751, 145, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(752, 145, 'guide_steps_2_is_highlighted', '0'),
(753, 145, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(754, 145, 'guide_steps_3_image', ''),
(755, 145, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(756, 145, 'guide_steps_3_title', 'Set an asking price'),
(757, 145, '_guide_steps_3_title', 'field_5f087a079a053'),
(758, 145, 'guide_steps_3_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(759, 145, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(760, 145, 'guide_steps_3_is_highlighted', '0'),
(761, 145, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(762, 145, 'guide_steps_4_image', ''),
(763, 145, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(764, 145, 'guide_steps_4_title', 'Instruct an estate agent'),
(765, 145, '_guide_steps_4_title', 'field_5f087a079a053'),
(766, 145, 'guide_steps_4_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(767, 145, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(768, 145, 'guide_steps_4_is_highlighted', '1'),
(769, 145, '_guide_steps_4_is_highlighted', 'field_5f087a179a055') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(770, 145, 'guide_steps_5_image', ''),
(771, 145, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(772, 145, 'guide_steps_5_title', 'Help prepare the marketing materials'),
(773, 145, '_guide_steps_5_title', 'field_5f087a079a053'),
(774, 145, 'guide_steps_5_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(775, 145, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(776, 145, 'guide_steps_5_is_highlighted', '0'),
(777, 145, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(778, 145, 'guide_steps_6_image', ''),
(779, 145, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(780, 145, 'guide_steps_6_title', 'Get your paperwork in order'),
(781, 145, '_guide_steps_6_title', 'field_5f087a079a053'),
(782, 145, 'guide_steps_6_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(783, 145, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(784, 145, 'guide_steps_6_is_highlighted', '0'),
(785, 145, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(786, 145, 'guide_steps_7_image', ''),
(787, 145, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(788, 145, 'guide_steps_7_title', 'Conduct or work around viewings'),
(789, 145, '_guide_steps_7_title', 'field_5f087a079a053'),
(790, 145, 'guide_steps_7_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(791, 145, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(792, 145, 'guide_steps_7_is_highlighted', '0'),
(793, 145, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(794, 145, 'guide_steps_8_image', ''),
(795, 145, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(796, 145, 'guide_steps_8_title', 'Hire a solicitor or conveyancer'),
(797, 145, '_guide_steps_8_title', 'field_5f087a079a053'),
(798, 145, 'guide_steps_8_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(799, 145, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(800, 145, 'guide_steps_8_is_highlighted', '0'),
(801, 145, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(802, 145, 'guide_steps_9_image', ''),
(803, 145, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(804, 145, 'guide_steps_9_title', 'Receive offers'),
(805, 145, '_guide_steps_9_title', 'field_5f087a079a053'),
(806, 145, 'guide_steps_9_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(807, 145, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(808, 145, 'guide_steps_9_is_highlighted', '0'),
(809, 145, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(810, 145, 'guide_steps_10_image', ''),
(811, 145, '_guide_steps_10_image', 'field_5f0879fa9a052'),
(812, 145, 'guide_steps_10_title', 'Accept or negotiate an offer'),
(813, 145, '_guide_steps_10_title', 'field_5f087a079a053'),
(814, 145, 'guide_steps_10_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(815, 145, '_guide_steps_10_content', 'field_5f087a0b9a054'),
(816, 145, 'guide_steps_10_is_highlighted', '0'),
(817, 145, '_guide_steps_10_is_highlighted', 'field_5f087a179a055'),
(818, 145, 'guide_steps_11_image', ''),
(819, 145, '_guide_steps_11_image', 'field_5f0879fa9a052'),
(820, 145, 'guide_steps_11_title', 'Start house-hunting!'),
(821, 145, '_guide_steps_11_title', 'field_5f087a079a053'),
(822, 145, 'guide_steps_11_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(823, 145, '_guide_steps_11_content', 'field_5f087a0b9a054'),
(824, 145, 'guide_steps_11_is_highlighted', '0'),
(825, 145, '_guide_steps_11_is_highlighted', 'field_5f087a179a055'),
(826, 145, 'guide_steps_12_image', ''),
(827, 145, '_guide_steps_12_image', 'field_5f0879fa9a052'),
(828, 145, 'guide_steps_12_title', 'Exchange & complete'),
(829, 145, '_guide_steps_12_title', 'field_5f087a079a053'),
(830, 145, 'guide_steps_12_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(831, 145, '_guide_steps_12_content', 'field_5f087a0b9a054'),
(832, 145, 'guide_steps_12_is_highlighted', '1'),
(833, 145, '_guide_steps_12_is_highlighted', 'field_5f087a179a055'),
(834, 145, 'guide_steps', '13'),
(835, 145, '_guide_steps', 'field_5f0879ee9a051'),
(836, 145, 'h1', ''),
(837, 145, '_h1', 'field_5f04e1b0f6df1'),
(838, 145, 'show_why_choose_us', '0'),
(839, 145, '_show_why_choose_us', 'field_5f04ecb34e432'),
(840, 145, 'show_buckets', '0'),
(841, 145, '_show_buckets', 'field_5f04ecbe4e433'),
(842, 145, 'show_team', '0'),
(843, 145, '_show_team', 'field_5f04ed2a64341'),
(844, 146, 'hero_heading', 'Our guide to selling your home'),
(845, 146, '_hero_heading', 'field_5eff48a05507c'),
(846, 146, 'has_search_bar', '0'),
(847, 146, '_has_search_bar', 'field_5eff48a65507d'),
(848, 146, 'guide_steps_0_image', ''),
(849, 146, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(850, 146, 'guide_steps_0_title', 'Get your finances in order'),
(851, 146, '_guide_steps_0_title', 'field_5f087a079a053'),
(852, 146, 'guide_steps_0_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst.'),
(853, 146, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(854, 146, 'guide_steps_0_is_highlighted', '0'),
(855, 146, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(856, 146, 'guide_steps_1_image', ''),
(857, 146, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(858, 146, 'guide_steps_1_title', 'Make your home look its best'),
(859, 146, '_guide_steps_1_title', 'field_5f087a079a053'),
(860, 146, 'guide_steps_1_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(861, 146, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(862, 146, 'guide_steps_1_is_highlighted', '0'),
(863, 146, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(864, 146, 'guide_steps_2_image', ''),
(865, 146, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(866, 146, 'guide_steps_2_title', 'Get your property valued'),
(867, 146, '_guide_steps_2_title', 'field_5f087a079a053'),
(868, 146, 'guide_steps_2_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(869, 146, '_guide_steps_2_content', 'field_5f087a0b9a054') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(870, 146, 'guide_steps_2_is_highlighted', '0'),
(871, 146, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(872, 146, 'guide_steps_3_image', ''),
(873, 146, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(874, 146, 'guide_steps_3_title', 'Set an asking price'),
(875, 146, '_guide_steps_3_title', 'field_5f087a079a053'),
(876, 146, 'guide_steps_3_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(877, 146, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(878, 146, 'guide_steps_3_is_highlighted', '0'),
(879, 146, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(880, 146, 'guide_steps_4_image', ''),
(881, 146, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(882, 146, 'guide_steps_4_title', 'Instruct an estate agent'),
(883, 146, '_guide_steps_4_title', 'field_5f087a079a053'),
(884, 146, 'guide_steps_4_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(885, 146, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(886, 146, 'guide_steps_4_is_highlighted', '1'),
(887, 146, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(888, 146, 'guide_steps_5_image', ''),
(889, 146, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(890, 146, 'guide_steps_5_title', 'Help prepare the marketing materials'),
(891, 146, '_guide_steps_5_title', 'field_5f087a079a053'),
(892, 146, 'guide_steps_5_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(893, 146, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(894, 146, 'guide_steps_5_is_highlighted', '0'),
(895, 146, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(896, 146, 'guide_steps_6_image', ''),
(897, 146, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(898, 146, 'guide_steps_6_title', 'Get your paperwork in order'),
(899, 146, '_guide_steps_6_title', 'field_5f087a079a053'),
(900, 146, 'guide_steps_6_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(901, 146, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(902, 146, 'guide_steps_6_is_highlighted', '0'),
(903, 146, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(904, 146, 'guide_steps_7_image', ''),
(905, 146, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(906, 146, 'guide_steps_7_title', 'Conduct or work around viewings'),
(907, 146, '_guide_steps_7_title', 'field_5f087a079a053'),
(908, 146, 'guide_steps_7_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(909, 146, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(910, 146, 'guide_steps_7_is_highlighted', '0'),
(911, 146, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(912, 146, 'guide_steps_8_image', ''),
(913, 146, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(914, 146, 'guide_steps_8_title', 'Hire a solicitor or conveyancer'),
(915, 146, '_guide_steps_8_title', 'field_5f087a079a053'),
(916, 146, 'guide_steps_8_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(917, 146, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(918, 146, 'guide_steps_8_is_highlighted', '0'),
(919, 146, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(920, 146, 'guide_steps_9_image', ''),
(921, 146, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(922, 146, 'guide_steps_9_title', 'Receive offers'),
(923, 146, '_guide_steps_9_title', 'field_5f087a079a053'),
(924, 146, 'guide_steps_9_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(925, 146, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(926, 146, 'guide_steps_9_is_highlighted', '0'),
(927, 146, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(928, 146, 'guide_steps_10_image', ''),
(929, 146, '_guide_steps_10_image', 'field_5f0879fa9a052'),
(930, 146, 'guide_steps_10_title', 'Accept or negotiate an offer'),
(931, 146, '_guide_steps_10_title', 'field_5f087a079a053'),
(932, 146, 'guide_steps_10_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(933, 146, '_guide_steps_10_content', 'field_5f087a0b9a054'),
(934, 146, 'guide_steps_10_is_highlighted', '0'),
(935, 146, '_guide_steps_10_is_highlighted', 'field_5f087a179a055'),
(936, 146, 'guide_steps_11_image', ''),
(937, 146, '_guide_steps_11_image', 'field_5f0879fa9a052'),
(938, 146, 'guide_steps_11_title', 'Start house-hunting!'),
(939, 146, '_guide_steps_11_title', 'field_5f087a079a053'),
(940, 146, 'guide_steps_11_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(941, 146, '_guide_steps_11_content', 'field_5f087a0b9a054'),
(942, 146, 'guide_steps_11_is_highlighted', '0'),
(943, 146, '_guide_steps_11_is_highlighted', 'field_5f087a179a055'),
(944, 146, 'guide_steps_12_image', ''),
(945, 146, '_guide_steps_12_image', 'field_5f0879fa9a052'),
(946, 146, 'guide_steps_12_title', 'Exchange & complete'),
(947, 146, '_guide_steps_12_title', 'field_5f087a079a053'),
(948, 146, 'guide_steps_12_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(949, 146, '_guide_steps_12_content', 'field_5f087a0b9a054'),
(950, 146, 'guide_steps_12_is_highlighted', '1'),
(951, 146, '_guide_steps_12_is_highlighted', 'field_5f087a179a055'),
(952, 146, 'guide_steps', '13'),
(953, 146, '_guide_steps', 'field_5f0879ee9a051'),
(954, 146, 'h1', ''),
(955, 146, '_h1', 'field_5f04e1b0f6df1'),
(956, 146, 'show_why_choose_us', '0'),
(957, 146, '_show_why_choose_us', 'field_5f04ecb34e432'),
(958, 146, 'show_buckets', '0'),
(959, 146, '_show_buckets', 'field_5f04ecbe4e433'),
(960, 146, 'show_team', '0'),
(961, 146, '_show_team', 'field_5f04ed2a64341'),
(962, 147, 'hero_heading', 'Our guide to selling your home'),
(963, 147, '_hero_heading', 'field_5eff48a05507c'),
(964, 147, 'has_search_bar', '0'),
(965, 147, '_has_search_bar', 'field_5eff48a65507d'),
(966, 147, 'guide_steps_0_image', '122'),
(967, 147, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(968, 147, 'guide_steps_0_title', 'Get your finances in order'),
(969, 147, '_guide_steps_0_title', 'field_5f087a079a053') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(970, 147, 'guide_steps_0_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst.'),
(971, 147, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(972, 147, 'guide_steps_0_is_highlighted', '0'),
(973, 147, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(974, 147, 'guide_steps_1_image', '121'),
(975, 147, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(976, 147, 'guide_steps_1_title', 'Make your home look its best'),
(977, 147, '_guide_steps_1_title', 'field_5f087a079a053'),
(978, 147, 'guide_steps_1_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(979, 147, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(980, 147, 'guide_steps_1_is_highlighted', '0'),
(981, 147, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(982, 147, 'guide_steps_2_image', '119'),
(983, 147, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(984, 147, 'guide_steps_2_title', 'Get your property valued'),
(985, 147, '_guide_steps_2_title', 'field_5f087a079a053'),
(986, 147, 'guide_steps_2_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(987, 147, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(988, 147, 'guide_steps_2_is_highlighted', '0'),
(989, 147, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(990, 147, 'guide_steps_3_image', '67'),
(991, 147, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(992, 147, 'guide_steps_3_title', 'Set an asking price'),
(993, 147, '_guide_steps_3_title', 'field_5f087a079a053'),
(994, 147, 'guide_steps_3_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(995, 147, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(996, 147, 'guide_steps_3_is_highlighted', '0'),
(997, 147, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(998, 147, 'guide_steps_4_image', '121'),
(999, 147, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(1000, 147, 'guide_steps_4_title', 'Instruct an estate agent'),
(1001, 147, '_guide_steps_4_title', 'field_5f087a079a053'),
(1002, 147, 'guide_steps_4_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1003, 147, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(1004, 147, 'guide_steps_4_is_highlighted', '1'),
(1005, 147, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(1006, 147, 'guide_steps_5_image', '120'),
(1007, 147, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(1008, 147, 'guide_steps_5_title', 'Help prepare the marketing materials'),
(1009, 147, '_guide_steps_5_title', 'field_5f087a079a053'),
(1010, 147, 'guide_steps_5_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1011, 147, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(1012, 147, 'guide_steps_5_is_highlighted', '0'),
(1013, 147, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(1014, 147, 'guide_steps_6_image', '119'),
(1015, 147, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(1016, 147, 'guide_steps_6_title', 'Get your paperwork in order'),
(1017, 147, '_guide_steps_6_title', 'field_5f087a079a053'),
(1018, 147, 'guide_steps_6_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1019, 147, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(1020, 147, 'guide_steps_6_is_highlighted', '0'),
(1021, 147, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(1022, 147, 'guide_steps_7_image', '120'),
(1023, 147, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(1024, 147, 'guide_steps_7_title', 'Conduct or work around viewings'),
(1025, 147, '_guide_steps_7_title', 'field_5f087a079a053'),
(1026, 147, 'guide_steps_7_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1027, 147, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(1028, 147, 'guide_steps_7_is_highlighted', '0'),
(1029, 147, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(1030, 147, 'guide_steps_8_image', '122'),
(1031, 147, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(1032, 147, 'guide_steps_8_title', 'Hire a solicitor or conveyancer'),
(1033, 147, '_guide_steps_8_title', 'field_5f087a079a053'),
(1034, 147, 'guide_steps_8_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1035, 147, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(1036, 147, 'guide_steps_8_is_highlighted', '0'),
(1037, 147, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(1038, 147, 'guide_steps_9_image', '67'),
(1039, 147, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(1040, 147, 'guide_steps_9_title', 'Receive offers'),
(1041, 147, '_guide_steps_9_title', 'field_5f087a079a053'),
(1042, 147, 'guide_steps_9_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1043, 147, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(1044, 147, 'guide_steps_9_is_highlighted', '0'),
(1045, 147, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(1046, 147, 'guide_steps_10_image', '121'),
(1047, 147, '_guide_steps_10_image', 'field_5f0879fa9a052'),
(1048, 147, 'guide_steps_10_title', 'Accept or negotiate an offer'),
(1049, 147, '_guide_steps_10_title', 'field_5f087a079a053'),
(1050, 147, 'guide_steps_10_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1051, 147, '_guide_steps_10_content', 'field_5f087a0b9a054'),
(1052, 147, 'guide_steps_10_is_highlighted', '0'),
(1053, 147, '_guide_steps_10_is_highlighted', 'field_5f087a179a055'),
(1054, 147, 'guide_steps_11_image', '120'),
(1055, 147, '_guide_steps_11_image', 'field_5f0879fa9a052'),
(1056, 147, 'guide_steps_11_title', 'Start house-hunting!'),
(1057, 147, '_guide_steps_11_title', 'field_5f087a079a053'),
(1058, 147, 'guide_steps_11_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1059, 147, '_guide_steps_11_content', 'field_5f087a0b9a054'),
(1060, 147, 'guide_steps_11_is_highlighted', '0'),
(1061, 147, '_guide_steps_11_is_highlighted', 'field_5f087a179a055'),
(1062, 147, 'guide_steps_12_image', ''),
(1063, 147, '_guide_steps_12_image', 'field_5f0879fa9a052'),
(1064, 147, 'guide_steps_12_title', 'Exchange & complete'),
(1065, 147, '_guide_steps_12_title', 'field_5f087a079a053'),
(1066, 147, 'guide_steps_12_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1067, 147, '_guide_steps_12_content', 'field_5f087a0b9a054'),
(1068, 147, 'guide_steps_12_is_highlighted', '1'),
(1069, 147, '_guide_steps_12_is_highlighted', 'field_5f087a179a055') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1070, 147, 'guide_steps', '13'),
(1071, 147, '_guide_steps', 'field_5f0879ee9a051'),
(1072, 147, 'h1', ''),
(1073, 147, '_h1', 'field_5f04e1b0f6df1'),
(1074, 147, 'show_why_choose_us', '0'),
(1075, 147, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1076, 147, 'show_buckets', '0'),
(1077, 147, '_show_buckets', 'field_5f04ecbe4e433'),
(1078, 147, 'show_team', '0'),
(1079, 147, '_show_team', 'field_5f04ed2a64341'),
(1080, 148, 'hero_heading', 'Our guide to selling your home'),
(1081, 148, '_hero_heading', 'field_5eff48a05507c'),
(1082, 148, 'has_search_bar', '0'),
(1083, 148, '_has_search_bar', 'field_5eff48a65507d'),
(1084, 148, 'guide_steps_0_image', '122'),
(1085, 148, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(1086, 148, 'guide_steps_0_title', 'Get your finances in order'),
(1087, 148, '_guide_steps_0_title', 'field_5f087a079a053'),
(1088, 148, 'guide_steps_0_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst.'),
(1089, 148, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(1090, 148, 'guide_steps_0_is_highlighted', '0'),
(1091, 148, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(1092, 148, 'guide_steps_1_image', '121'),
(1093, 148, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(1094, 148, 'guide_steps_1_title', 'Make your home look its best'),
(1095, 148, '_guide_steps_1_title', 'field_5f087a079a053'),
(1096, 148, 'guide_steps_1_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1097, 148, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(1098, 148, 'guide_steps_1_is_highlighted', '0'),
(1099, 148, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(1100, 148, 'guide_steps_2_image', '119'),
(1101, 148, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(1102, 148, 'guide_steps_2_title', 'Get your property valued'),
(1103, 148, '_guide_steps_2_title', 'field_5f087a079a053'),
(1104, 148, 'guide_steps_2_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1105, 148, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(1106, 148, 'guide_steps_2_is_highlighted', '0'),
(1107, 148, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(1108, 148, 'guide_steps_3_image', '67'),
(1109, 148, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(1110, 148, 'guide_steps_3_title', 'Set an asking price'),
(1111, 148, '_guide_steps_3_title', 'field_5f087a079a053'),
(1112, 148, 'guide_steps_3_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1113, 148, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(1114, 148, 'guide_steps_3_is_highlighted', '0'),
(1115, 148, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(1116, 148, 'guide_steps_4_image', '121'),
(1117, 148, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(1118, 148, 'guide_steps_4_title', 'Instruct an estate agent'),
(1119, 148, '_guide_steps_4_title', 'field_5f087a079a053'),
(1120, 148, 'guide_steps_4_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1121, 148, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(1122, 148, 'guide_steps_4_is_highlighted', '1'),
(1123, 148, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(1124, 148, 'guide_steps_5_image', '120'),
(1125, 148, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(1126, 148, 'guide_steps_5_title', 'Help prepare the marketing materials'),
(1127, 148, '_guide_steps_5_title', 'field_5f087a079a053'),
(1128, 148, 'guide_steps_5_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1129, 148, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(1130, 148, 'guide_steps_5_is_highlighted', '0'),
(1131, 148, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(1132, 148, 'guide_steps_6_image', '119'),
(1133, 148, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(1134, 148, 'guide_steps_6_title', 'Get your paperwork in order'),
(1135, 148, '_guide_steps_6_title', 'field_5f087a079a053'),
(1136, 148, 'guide_steps_6_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1137, 148, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(1138, 148, 'guide_steps_6_is_highlighted', '0'),
(1139, 148, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(1140, 148, 'guide_steps_7_image', '120'),
(1141, 148, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(1142, 148, 'guide_steps_7_title', 'Conduct or work around viewings'),
(1143, 148, '_guide_steps_7_title', 'field_5f087a079a053'),
(1144, 148, 'guide_steps_7_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1145, 148, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(1146, 148, 'guide_steps_7_is_highlighted', '0'),
(1147, 148, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(1148, 148, 'guide_steps_8_image', '122'),
(1149, 148, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(1150, 148, 'guide_steps_8_title', 'Hire a solicitor or conveyancer'),
(1151, 148, '_guide_steps_8_title', 'field_5f087a079a053'),
(1152, 148, 'guide_steps_8_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1153, 148, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(1154, 148, 'guide_steps_8_is_highlighted', '0'),
(1155, 148, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(1156, 148, 'guide_steps_9_image', '67'),
(1157, 148, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(1158, 148, 'guide_steps_9_title', 'Receive offers'),
(1159, 148, '_guide_steps_9_title', 'field_5f087a079a053'),
(1160, 148, 'guide_steps_9_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1161, 148, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(1162, 148, 'guide_steps_9_is_highlighted', '0'),
(1163, 148, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(1164, 148, 'guide_steps_10_image', '121'),
(1165, 148, '_guide_steps_10_image', 'field_5f0879fa9a052'),
(1166, 148, 'guide_steps_10_title', 'Accept or negotiate an offer'),
(1167, 148, '_guide_steps_10_title', 'field_5f087a079a053'),
(1168, 148, 'guide_steps_10_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1169, 148, '_guide_steps_10_content', 'field_5f087a0b9a054') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1170, 148, 'guide_steps_10_is_highlighted', '0'),
(1171, 148, '_guide_steps_10_is_highlighted', 'field_5f087a179a055'),
(1172, 148, 'guide_steps_11_image', '120'),
(1173, 148, '_guide_steps_11_image', 'field_5f0879fa9a052'),
(1174, 148, 'guide_steps_11_title', 'Start house-hunting!'),
(1175, 148, '_guide_steps_11_title', 'field_5f087a079a053'),
(1176, 148, 'guide_steps_11_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1177, 148, '_guide_steps_11_content', 'field_5f087a0b9a054'),
(1178, 148, 'guide_steps_11_is_highlighted', '0'),
(1179, 148, '_guide_steps_11_is_highlighted', 'field_5f087a179a055'),
(1180, 148, 'guide_steps_12_image', '121'),
(1181, 148, '_guide_steps_12_image', 'field_5f0879fa9a052'),
(1182, 148, 'guide_steps_12_title', 'Exchange & complete'),
(1183, 148, '_guide_steps_12_title', 'field_5f087a079a053'),
(1184, 148, 'guide_steps_12_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1185, 148, '_guide_steps_12_content', 'field_5f087a0b9a054'),
(1186, 148, 'guide_steps_12_is_highlighted', '1'),
(1187, 148, '_guide_steps_12_is_highlighted', 'field_5f087a179a055'),
(1188, 148, 'guide_steps', '13'),
(1189, 148, '_guide_steps', 'field_5f0879ee9a051'),
(1190, 148, 'h1', ''),
(1191, 148, '_h1', 'field_5f04e1b0f6df1'),
(1192, 148, 'show_why_choose_us', '0'),
(1193, 148, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1194, 148, 'show_buckets', '0'),
(1195, 148, '_show_buckets', 'field_5f04ecbe4e433'),
(1196, 148, 'show_team', '0'),
(1197, 148, '_show_team', 'field_5f04ed2a64341'),
(1198, 149, 'hero_heading', 'Our guide to selling your home'),
(1199, 149, '_hero_heading', 'field_5eff48a05507c'),
(1200, 149, 'has_search_bar', '0'),
(1201, 149, '_has_search_bar', 'field_5eff48a65507d'),
(1202, 149, 'guide_steps_0_image', '122'),
(1203, 149, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(1204, 149, 'guide_steps_0_title', 'Get your finances in order'),
(1205, 149, '_guide_steps_0_title', 'field_5f087a079a053'),
(1206, 149, 'guide_steps_0_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst.'),
(1207, 149, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(1208, 149, 'guide_steps_0_is_highlighted', '0'),
(1209, 149, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(1210, 149, 'guide_steps_1_image', '121'),
(1211, 149, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(1212, 149, 'guide_steps_1_title', 'Make your home look its best'),
(1213, 149, '_guide_steps_1_title', 'field_5f087a079a053'),
(1214, 149, 'guide_steps_1_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1215, 149, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(1216, 149, 'guide_steps_1_is_highlighted', '0'),
(1217, 149, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(1218, 149, 'guide_steps_2_image', '119'),
(1219, 149, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(1220, 149, 'guide_steps_2_title', 'Get your property valued'),
(1221, 149, '_guide_steps_2_title', 'field_5f087a079a053'),
(1222, 149, 'guide_steps_2_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1223, 149, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(1224, 149, 'guide_steps_2_is_highlighted', '0'),
(1225, 149, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(1226, 149, 'guide_steps_3_image', '67'),
(1227, 149, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(1228, 149, 'guide_steps_3_title', 'Set an asking price'),
(1229, 149, '_guide_steps_3_title', 'field_5f087a079a053'),
(1230, 149, 'guide_steps_3_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1231, 149, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(1232, 149, 'guide_steps_3_is_highlighted', '0'),
(1233, 149, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(1234, 149, 'guide_steps_4_image', '121'),
(1235, 149, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(1236, 149, 'guide_steps_4_title', 'Instruct an estate agent'),
(1237, 149, '_guide_steps_4_title', 'field_5f087a079a053'),
(1238, 149, 'guide_steps_4_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1239, 149, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(1240, 149, 'guide_steps_4_is_highlighted', '1'),
(1241, 149, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(1242, 149, 'guide_steps_5_image', '120'),
(1243, 149, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(1244, 149, 'guide_steps_5_title', 'Help prepare the marketing materials'),
(1245, 149, '_guide_steps_5_title', 'field_5f087a079a053'),
(1246, 149, 'guide_steps_5_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1247, 149, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(1248, 149, 'guide_steps_5_is_highlighted', '0'),
(1249, 149, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(1250, 149, 'guide_steps_6_image', '119'),
(1251, 149, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(1252, 149, 'guide_steps_6_title', 'Get your paperwork in order'),
(1253, 149, '_guide_steps_6_title', 'field_5f087a079a053'),
(1254, 149, 'guide_steps_6_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1255, 149, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(1256, 149, 'guide_steps_6_is_highlighted', '0'),
(1257, 149, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(1258, 149, 'guide_steps_7_image', '120'),
(1259, 149, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(1260, 149, 'guide_steps_7_title', 'Conduct or work around viewings'),
(1261, 149, '_guide_steps_7_title', 'field_5f087a079a053'),
(1262, 149, 'guide_steps_7_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1263, 149, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(1264, 149, 'guide_steps_7_is_highlighted', '0'),
(1265, 149, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(1266, 149, 'guide_steps_8_image', '122'),
(1267, 149, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(1268, 149, 'guide_steps_8_title', 'Hire a solicitor or conveyancer'),
(1269, 149, '_guide_steps_8_title', 'field_5f087a079a053') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1270, 149, 'guide_steps_8_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1271, 149, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(1272, 149, 'guide_steps_8_is_highlighted', '0'),
(1273, 149, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(1274, 149, 'guide_steps_9_image', '67'),
(1275, 149, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(1276, 149, 'guide_steps_9_title', 'Receive offers'),
(1277, 149, '_guide_steps_9_title', 'field_5f087a079a053'),
(1278, 149, 'guide_steps_9_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1279, 149, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(1280, 149, 'guide_steps_9_is_highlighted', '0'),
(1281, 149, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(1282, 149, 'guide_steps_10_image', '121'),
(1283, 149, '_guide_steps_10_image', 'field_5f0879fa9a052'),
(1284, 149, 'guide_steps_10_title', 'Accept or negotiate an offer'),
(1285, 149, '_guide_steps_10_title', 'field_5f087a079a053'),
(1286, 149, 'guide_steps_10_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1287, 149, '_guide_steps_10_content', 'field_5f087a0b9a054'),
(1288, 149, 'guide_steps_10_is_highlighted', '0'),
(1289, 149, '_guide_steps_10_is_highlighted', 'field_5f087a179a055'),
(1290, 149, 'guide_steps_11_image', '120'),
(1291, 149, '_guide_steps_11_image', 'field_5f0879fa9a052'),
(1292, 149, 'guide_steps_11_title', 'Start house-hunting!'),
(1293, 149, '_guide_steps_11_title', 'field_5f087a079a053'),
(1294, 149, 'guide_steps_11_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1295, 149, '_guide_steps_11_content', 'field_5f087a0b9a054'),
(1296, 149, 'guide_steps_11_is_highlighted', '0'),
(1297, 149, '_guide_steps_11_is_highlighted', 'field_5f087a179a055'),
(1298, 149, 'guide_steps_12_image', '121'),
(1299, 149, '_guide_steps_12_image', 'field_5f0879fa9a052'),
(1300, 149, 'guide_steps_12_title', 'Exchange & complete'),
(1301, 149, '_guide_steps_12_title', 'field_5f087a079a053'),
(1302, 149, 'guide_steps_12_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1303, 149, '_guide_steps_12_content', 'field_5f087a0b9a054'),
(1304, 149, 'guide_steps_12_is_highlighted', '1'),
(1305, 149, '_guide_steps_12_is_highlighted', 'field_5f087a179a055'),
(1306, 149, 'guide_steps', '13'),
(1307, 149, '_guide_steps', 'field_5f0879ee9a051'),
(1308, 149, 'h1', ''),
(1309, 149, '_h1', 'field_5f04e1b0f6df1'),
(1310, 149, 'show_why_choose_us', '1'),
(1311, 149, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1312, 149, 'show_buckets', '0'),
(1313, 149, '_show_buckets', 'field_5f04ecbe4e433'),
(1314, 149, 'show_team', '1'),
(1315, 149, '_show_team', 'field_5f04ed2a64341'),
(1316, 150, 'hero_heading', 'Our guide to selling your home'),
(1317, 150, '_hero_heading', 'field_5eff48a05507c'),
(1318, 150, 'has_search_bar', '0'),
(1319, 150, '_has_search_bar', 'field_5eff48a65507d'),
(1320, 150, 'guide_steps_0_image', '122'),
(1321, 150, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(1322, 150, 'guide_steps_0_title', 'Get your finances in order'),
(1323, 150, '_guide_steps_0_title', 'field_5f087a079a053'),
(1324, 150, 'guide_steps_0_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst.'),
(1325, 150, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(1326, 150, 'guide_steps_0_is_highlighted', '0'),
(1327, 150, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(1328, 150, 'guide_steps_1_image', '121'),
(1329, 150, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(1330, 150, 'guide_steps_1_title', 'Make your home look its best'),
(1331, 150, '_guide_steps_1_title', 'field_5f087a079a053'),
(1332, 150, 'guide_steps_1_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1333, 150, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(1334, 150, 'guide_steps_1_is_highlighted', '0'),
(1335, 150, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(1336, 150, 'guide_steps_2_image', '119'),
(1337, 150, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(1338, 150, 'guide_steps_2_title', 'Get your property valued'),
(1339, 150, '_guide_steps_2_title', 'field_5f087a079a053'),
(1340, 150, 'guide_steps_2_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1341, 150, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(1342, 150, 'guide_steps_2_is_highlighted', '0'),
(1343, 150, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(1344, 150, 'guide_steps_3_image', '67'),
(1345, 150, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(1346, 150, 'guide_steps_3_title', 'Set an asking price'),
(1347, 150, '_guide_steps_3_title', 'field_5f087a079a053'),
(1348, 150, 'guide_steps_3_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1349, 150, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(1350, 150, 'guide_steps_3_is_highlighted', '0'),
(1351, 150, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(1352, 150, 'guide_steps_4_image', '121'),
(1353, 150, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(1354, 150, 'guide_steps_4_title', 'Instruct an estate agent'),
(1355, 150, '_guide_steps_4_title', 'field_5f087a079a053'),
(1356, 150, 'guide_steps_4_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1357, 150, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(1358, 150, 'guide_steps_4_is_highlighted', '1'),
(1359, 150, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(1360, 150, 'guide_steps_5_image', '120'),
(1361, 150, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(1362, 150, 'guide_steps_5_title', 'Help prepare the marketing materials'),
(1363, 150, '_guide_steps_5_title', 'field_5f087a079a053'),
(1364, 150, 'guide_steps_5_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1365, 150, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(1366, 150, 'guide_steps_5_is_highlighted', '0'),
(1367, 150, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(1368, 150, 'guide_steps_6_image', '119'),
(1369, 150, '_guide_steps_6_image', 'field_5f0879fa9a052') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1370, 150, 'guide_steps_6_title', 'Get your paperwork in order'),
(1371, 150, '_guide_steps_6_title', 'field_5f087a079a053'),
(1372, 150, 'guide_steps_6_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1373, 150, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(1374, 150, 'guide_steps_6_is_highlighted', '0'),
(1375, 150, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(1376, 150, 'guide_steps_7_image', '120'),
(1377, 150, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(1378, 150, 'guide_steps_7_title', 'Conduct or work around viewings'),
(1379, 150, '_guide_steps_7_title', 'field_5f087a079a053'),
(1380, 150, 'guide_steps_7_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1381, 150, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(1382, 150, 'guide_steps_7_is_highlighted', '0'),
(1383, 150, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(1384, 150, 'guide_steps_8_image', '122'),
(1385, 150, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(1386, 150, 'guide_steps_8_title', 'Hire a solicitor or conveyancer'),
(1387, 150, '_guide_steps_8_title', 'field_5f087a079a053'),
(1388, 150, 'guide_steps_8_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1389, 150, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(1390, 150, 'guide_steps_8_is_highlighted', '0'),
(1391, 150, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(1392, 150, 'guide_steps_9_image', '67'),
(1393, 150, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(1394, 150, 'guide_steps_9_title', 'Receive offers'),
(1395, 150, '_guide_steps_9_title', 'field_5f087a079a053'),
(1396, 150, 'guide_steps_9_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1397, 150, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(1398, 150, 'guide_steps_9_is_highlighted', '0'),
(1399, 150, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(1400, 150, 'guide_steps_10_image', '121'),
(1401, 150, '_guide_steps_10_image', 'field_5f0879fa9a052'),
(1402, 150, 'guide_steps_10_title', 'Accept or negotiate an offer'),
(1403, 150, '_guide_steps_10_title', 'field_5f087a079a053'),
(1404, 150, 'guide_steps_10_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1405, 150, '_guide_steps_10_content', 'field_5f087a0b9a054'),
(1406, 150, 'guide_steps_10_is_highlighted', '0'),
(1407, 150, '_guide_steps_10_is_highlighted', 'field_5f087a179a055'),
(1408, 150, 'guide_steps_11_image', '120'),
(1409, 150, '_guide_steps_11_image', 'field_5f0879fa9a052'),
(1410, 150, 'guide_steps_11_title', 'Start house-hunting!'),
(1411, 150, '_guide_steps_11_title', 'field_5f087a079a053'),
(1412, 150, 'guide_steps_11_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1413, 150, '_guide_steps_11_content', 'field_5f087a0b9a054'),
(1414, 150, 'guide_steps_11_is_highlighted', '0'),
(1415, 150, '_guide_steps_11_is_highlighted', 'field_5f087a179a055'),
(1416, 150, 'guide_steps_12_image', '121'),
(1417, 150, '_guide_steps_12_image', 'field_5f0879fa9a052'),
(1418, 150, 'guide_steps_12_title', 'Exchange & complete'),
(1419, 150, '_guide_steps_12_title', 'field_5f087a079a053'),
(1420, 150, 'guide_steps_12_content', 'Ut lectus lacus, bibendum nec mauris a, sodales hendrerit justo. Pellentesque eget placerat nulla. Cras ornare lobortis feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras iaculis finibus porta. Nulla in lacinia nisl. In hac habitasse platea dictumst. Fusce rutrum massa ullamcorper lacus semper, ut tempus urna condimentum.'),
(1421, 150, '_guide_steps_12_content', 'field_5f087a0b9a054'),
(1422, 150, 'guide_steps_12_is_highlighted', '1'),
(1423, 150, '_guide_steps_12_is_highlighted', 'field_5f087a179a055'),
(1424, 150, 'guide_steps', '13'),
(1425, 150, '_guide_steps', 'field_5f0879ee9a051'),
(1426, 150, 'h1', ''),
(1427, 150, '_h1', 'field_5f04e1b0f6df1'),
(1428, 150, 'show_why_choose_us', '0'),
(1429, 150, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1430, 150, 'show_buckets', '0'),
(1431, 150, '_show_buckets', 'field_5f04ecbe4e433'),
(1432, 150, 'show_team', '1'),
(1433, 150, '_show_team', 'field_5f04ed2a64341'),
(1434, 14, 'h1', ''),
(1435, 14, '_h1', 'field_5f04e1b0f6df1'),
(1436, 14, 'show_why_choose_us', '0'),
(1437, 14, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1438, 14, 'show_buckets', '1'),
(1439, 14, '_show_buckets', 'field_5f04ecbe4e433'),
(1440, 14, 'show_team', '1'),
(1441, 14, '_show_team', 'field_5f04ed2a64341'),
(1442, 151, 'hero_heading', 'Learn about Fullbrook & Floor'),
(1443, 151, '_hero_heading', 'field_5eff48a05507c'),
(1444, 151, 'has_search_bar', '0'),
(1445, 151, '_has_search_bar', 'field_5eff48a65507d'),
(1446, 151, 'h1', ''),
(1447, 151, '_h1', 'field_5f04e1b0f6df1'),
(1448, 151, 'show_why_choose_us', '0'),
(1449, 151, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1450, 151, 'show_buckets', '1'),
(1451, 151, '_show_buckets', 'field_5f04ecbe4e433'),
(1452, 151, 'show_team', '1'),
(1453, 151, '_show_team', 'field_5f04ed2a64341'),
(1454, 20, '_yoast_wpseo_content_score', '30'),
(1455, 20, 'h1', 'Get in touch today!'),
(1456, 20, '_h1', 'field_5f04e1b0f6df1'),
(1457, 20, 'show_why_choose_us', '1'),
(1458, 20, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1459, 20, 'show_buckets', '0'),
(1460, 20, '_show_buckets', 'field_5f04ecbe4e433'),
(1461, 20, 'show_team', '0'),
(1462, 20, '_show_team', 'field_5f04ed2a64341'),
(1463, 154, 'hero_heading', 'Contact Fullbrook & Floor'),
(1464, 154, '_hero_heading', 'field_5eff48a05507c'),
(1465, 154, 'has_search_bar', '0'),
(1466, 154, '_has_search_bar', 'field_5eff48a65507d'),
(1467, 154, 'h1', 'Get in touch today!'),
(1468, 154, '_h1', 'field_5f04e1b0f6df1'),
(1469, 154, 'show_why_choose_us', '1') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1470, 154, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1471, 154, 'show_buckets', '0'),
(1472, 154, '_show_buckets', 'field_5f04ecbe4e433'),
(1473, 154, 'show_team', '0'),
(1474, 154, '_show_team', 'field_5f04ed2a64341'),
(1475, 155, 'hero_heading', 'Contact Fullbrook & Floor'),
(1476, 155, '_hero_heading', 'field_5eff48a05507c'),
(1477, 155, 'has_search_bar', '0'),
(1478, 155, '_has_search_bar', 'field_5eff48a65507d'),
(1479, 155, 'h1', 'Get in touch today!'),
(1480, 155, '_h1', 'field_5f04e1b0f6df1'),
(1481, 155, 'show_why_choose_us', '1'),
(1482, 155, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1483, 155, 'show_buckets', '0'),
(1484, 155, '_show_buckets', 'field_5f04ecbe4e433'),
(1485, 155, 'show_team', '0'),
(1486, 155, '_show_team', 'field_5f04ed2a64341'),
(1487, 44, 'hero_heading', 'Selling your home Heading'),
(1488, 44, '_hero_heading', 'field_5eff48a05507c'),
(1489, 44, 'has_search_bar', '0'),
(1490, 44, '_has_search_bar', 'field_5eff48a65507d'),
(1491, 44, 'h1', 'A h1 goes here'),
(1492, 44, '_h1', 'field_5f04e1b0f6df1'),
(1493, 44, 'show_why_choose_us', '0'),
(1494, 44, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1495, 44, 'show_buckets', '0'),
(1496, 44, '_show_buckets', 'field_5f04ecbe4e433'),
(1497, 44, 'show_team', '0'),
(1498, 44, '_show_team', 'field_5f04ed2a64341'),
(1499, 156, '_thumbnail_id', '119'),
(1500, 156, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1501, 156, '_yoast_wpseo_content_score', '30'),
(1503, 156, 'hero_heading', 'Selling your home Heading'),
(1504, 156, '_hero_heading', 'field_5eff48a05507c'),
(1505, 156, 'has_search_bar', '0'),
(1506, 156, '_has_search_bar', 'field_5eff48a65507d'),
(1507, 156, 'h1', 'A h1 goes here'),
(1508, 156, '_h1', 'field_5f04e1b0f6df1'),
(1509, 156, 'show_why_choose_us', '0'),
(1510, 156, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1511, 156, 'show_buckets', '0'),
(1512, 156, '_show_buckets', 'field_5f04ecbe4e433'),
(1513, 156, 'show_team', '0'),
(1514, 156, '_show_team', 'field_5f04ed2a64341'),
(1515, 156, '_dp_original', '44'),
(1516, 157, '_thumbnail_id', '67'),
(1517, 157, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1518, 157, '_yoast_wpseo_content_score', '30'),
(1520, 157, '_dp_original', '45'),
(1521, 158, '_thumbnail_id', '121'),
(1522, 158, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1523, 158, '_yoast_wpseo_content_score', '30'),
(1525, 158, '_dp_original', '46'),
(1526, 159, '_thumbnail_id', '120'),
(1527, 159, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1528, 159, '_yoast_wpseo_content_score', '30'),
(1530, 159, '_dp_original', '40'),
(1531, 160, '_thumbnail_id', '67'),
(1532, 160, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1533, 160, '_yoast_wpseo_content_score', '30'),
(1535, 160, '_dp_original', '41'),
(1536, 161, '_thumbnail_id', '122'),
(1537, 161, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1538, 161, '_yoast_wpseo_content_score', '30'),
(1540, 161, '_dp_original', '42'),
(1541, 162, '_thumbnail_id', '121'),
(1542, 162, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1543, 162, '_yoast_wpseo_content_score', '30'),
(1545, 162, '_dp_original', '43'),
(1546, 163, '_thumbnail_id', '119'),
(1547, 163, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1548, 163, '_yoast_wpseo_content_score', '30'),
(1550, 163, '_dp_original', '39'),
(1551, 164, '_thumbnail_id', '67'),
(1552, 164, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1553, 164, '_yoast_wpseo_content_score', '30'),
(1555, 164, '_dp_original', '38'),
(1556, 165, '_thumbnail_id', '122'),
(1557, 165, '_yoast_wpseo_primary_help-advice-categories', '10'),
(1558, 165, '_yoast_wpseo_content_score', '30'),
(1559, 165, '_dp_original', '36'),
(1568, 14, 'sidebar_logos_0_name', 'Rightmove'),
(1569, 14, '_sidebar_logos_0_name', 'field_5f09cd9466109'),
(1570, 14, 'sidebar_logos_0_image', '197'),
(1571, 14, '_sidebar_logos_0_image', 'field_5f09cd8a66108'),
(1572, 14, 'sidebar_logos_0_link', 'https://www.rightmove.co.uk/'),
(1573, 14, '_sidebar_logos_0_link', 'field_5f09cd9d6610a'),
(1574, 14, 'sidebar_logos_1_name', 'Zoopla'),
(1575, 14, '_sidebar_logos_1_name', 'field_5f09cd9466109'),
(1576, 14, 'sidebar_logos_1_image', '198'),
(1577, 14, '_sidebar_logos_1_image', 'field_5f09cd8a66108'),
(1578, 14, 'sidebar_logos_1_link', 'https://www.zoopla.co.uk/'),
(1579, 14, '_sidebar_logos_1_link', 'field_5f09cd9d6610a'),
(1592, 14, 'sidebar_logos', '2'),
(1593, 14, '_sidebar_logos', 'field_5f09cd7166107'),
(1594, 175, 'hero_heading', 'Learn about Fullbrook & Floor'),
(1595, 175, '_hero_heading', 'field_5eff48a05507c'),
(1596, 175, 'has_search_bar', '0'),
(1597, 175, '_has_search_bar', 'field_5eff48a65507d'),
(1598, 175, 'h1', '') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1599, 175, '_h1', 'field_5f04e1b0f6df1'),
(1600, 175, 'show_why_choose_us', '0'),
(1601, 175, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1602, 175, 'show_buckets', '1'),
(1603, 175, '_show_buckets', 'field_5f04ecbe4e433'),
(1604, 175, 'show_team', '1'),
(1605, 175, '_show_team', 'field_5f04ed2a64341'),
(1606, 175, 'sidebar_logos_0_name', 'Rightmove'),
(1607, 175, '_sidebar_logos_0_name', 'field_5f09cd9466109'),
(1608, 175, 'sidebar_logos_0_image', '171'),
(1609, 175, '_sidebar_logos_0_image', 'field_5f09cd8a66108'),
(1610, 175, 'sidebar_logos_0_link', 'https://www.rightmove.co.uk/'),
(1611, 175, '_sidebar_logos_0_link', 'field_5f09cd9d6610a'),
(1612, 175, 'sidebar_logos_1_name', 'Zoopla'),
(1613, 175, '_sidebar_logos_1_name', 'field_5f09cd9466109'),
(1614, 175, 'sidebar_logos_1_image', '172'),
(1615, 175, '_sidebar_logos_1_image', 'field_5f09cd8a66108'),
(1616, 175, 'sidebar_logos_1_link', 'https://www.zoopla.co.uk/'),
(1617, 175, '_sidebar_logos_1_link', 'field_5f09cd9d6610a'),
(1618, 175, 'sidebar_logos_2_name', 'Net House Prices'),
(1619, 175, '_sidebar_logos_2_name', 'field_5f09cd9466109'),
(1620, 175, 'sidebar_logos_2_image', '173'),
(1621, 175, '_sidebar_logos_2_image', 'field_5f09cd8a66108'),
(1622, 175, 'sidebar_logos_2_link', 'https://nethouseprices.com/'),
(1623, 175, '_sidebar_logos_2_link', 'field_5f09cd9d6610a'),
(1624, 175, 'sidebar_logos_3_name', 'PrimeLocation'),
(1625, 175, '_sidebar_logos_3_name', 'field_5f09cd9466109'),
(1626, 175, 'sidebar_logos_3_image', '174'),
(1627, 175, '_sidebar_logos_3_image', 'field_5f09cd8a66108'),
(1628, 175, 'sidebar_logos_3_link', 'https://www.primelocation.com/'),
(1629, 175, '_sidebar_logos_3_link', 'field_5f09cd9d6610a'),
(1630, 175, 'sidebar_logos', '4'),
(1631, 175, '_sidebar_logos', 'field_5f09cd7166107'),
(1632, 176, 'hero_top_line', 'Friendly, local'),
(1633, 176, '_hero_top_line', 'field_5eff38fcccfce'),
(1634, 176, 'hero_main_line', 'Estate agents in St. Albans'),
(1635, 176, '_hero_main_line', 'field_5eff3910ccfcf'),
(1636, 176, 'h1', 'Estate agents in St. Albans'),
(1637, 176, '_h1', 'field_5f04a835c98a9'),
(1638, 177, 'hero_heading', 'Learn about Fullbrook & Floor'),
(1639, 177, '_hero_heading', 'field_5eff48a05507c'),
(1640, 177, 'has_search_bar', '0'),
(1641, 177, '_has_search_bar', 'field_5eff48a65507d'),
(1642, 177, 'h1', ''),
(1643, 177, '_h1', 'field_5f04e1b0f6df1'),
(1644, 177, 'show_why_choose_us', '0'),
(1645, 177, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1646, 177, 'show_buckets', '1'),
(1647, 177, '_show_buckets', 'field_5f04ecbe4e433'),
(1648, 177, 'show_team', '1'),
(1649, 177, '_show_team', 'field_5f04ed2a64341'),
(1650, 177, 'sidebar_logos_0_name', 'Rightmove'),
(1651, 177, '_sidebar_logos_0_name', 'field_5f09cd9466109'),
(1652, 177, 'sidebar_logos_0_image', '171'),
(1653, 177, '_sidebar_logos_0_image', 'field_5f09cd8a66108'),
(1654, 177, 'sidebar_logos_0_link', 'https://www.rightmove.co.uk/'),
(1655, 177, '_sidebar_logos_0_link', 'field_5f09cd9d6610a'),
(1656, 177, 'sidebar_logos_1_name', 'Zoopla'),
(1657, 177, '_sidebar_logos_1_name', 'field_5f09cd9466109'),
(1658, 177, 'sidebar_logos_1_image', '172'),
(1659, 177, '_sidebar_logos_1_image', 'field_5f09cd8a66108'),
(1660, 177, 'sidebar_logos_1_link', 'https://www.zoopla.co.uk/'),
(1661, 177, '_sidebar_logos_1_link', 'field_5f09cd9d6610a'),
(1662, 177, 'sidebar_logos_2_name', 'Net House Prices'),
(1663, 177, '_sidebar_logos_2_name', 'field_5f09cd9466109'),
(1664, 177, 'sidebar_logos_2_image', '173'),
(1665, 177, '_sidebar_logos_2_image', 'field_5f09cd8a66108'),
(1666, 177, 'sidebar_logos_2_link', 'https://nethouseprices.com/'),
(1667, 177, '_sidebar_logos_2_link', 'field_5f09cd9d6610a'),
(1668, 177, 'sidebar_logos_3_name', 'PrimeLocation'),
(1669, 177, '_sidebar_logos_3_name', 'field_5f09cd9466109'),
(1670, 177, 'sidebar_logos_3_image', '174'),
(1671, 177, '_sidebar_logos_3_image', 'field_5f09cd8a66108'),
(1672, 177, 'sidebar_logos_3_link', 'https://www.primelocation.com/'),
(1673, 177, '_sidebar_logos_3_link', 'field_5f09cd9d6610a'),
(1674, 177, 'sidebar_logos', '4'),
(1675, 177, '_sidebar_logos', 'field_5f09cd7166107'),
(1676, 8, 'h1', ''),
(1677, 8, '_h1', 'field_5f04e1b0f6df1'),
(1678, 8, 'show_why_choose_us', '0'),
(1679, 8, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1680, 8, 'show_buckets', '0'),
(1681, 8, '_show_buckets', 'field_5f04ecbe4e433'),
(1682, 8, 'show_team', '0'),
(1683, 8, '_show_team', 'field_5f04ed2a64341'),
(1684, 8, 'sidebar_logos', ''),
(1685, 8, '_sidebar_logos', 'field_5f09cd7166107'),
(1686, 178, 'hero_heading', 'Search for a home to buy'),
(1687, 178, '_hero_heading', 'field_5eff48a05507c'),
(1688, 178, 'has_search_bar', '1'),
(1689, 178, '_has_search_bar', 'field_5eff48a65507d'),
(1690, 178, 'h1', ''),
(1691, 178, '_h1', 'field_5f04e1b0f6df1'),
(1692, 178, 'show_why_choose_us', '0'),
(1693, 178, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1694, 178, 'show_buckets', '0'),
(1695, 178, '_show_buckets', 'field_5f04ecbe4e433'),
(1696, 178, 'show_team', '0'),
(1697, 178, '_show_team', 'field_5f04ed2a64341'),
(1698, 178, 'sidebar_logos', '') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1699, 178, '_sidebar_logos', 'field_5f09cd7166107'),
(1700, 10, 'sidebar_logos', ''),
(1701, 10, '_sidebar_logos', 'field_5f09cd7166107'),
(1702, 179, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(1703, 179, '_hero_heading', 'field_5eff48a05507c'),
(1704, 179, 'has_search_bar', '0'),
(1705, 179, '_has_search_bar', 'field_5eff48a65507d'),
(1706, 179, 'h1', 'Sell your home with us'),
(1707, 179, '_h1', 'field_5f04e1b0f6df1'),
(1708, 179, 'why_choose_us', ''),
(1709, 179, '_why_choose_us', 'field_5f04e465f4a3e'),
(1710, 179, 'show_why_choose_us', '1'),
(1711, 179, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1712, 179, 'show_buckets', '1'),
(1713, 179, '_show_buckets', 'field_5f04ecbe4e433'),
(1714, 179, 'show_team', '1'),
(1715, 179, '_show_team', 'field_5f04ed2a64341'),
(1716, 179, 'sidebar_logos', ''),
(1717, 179, '_sidebar_logos', 'field_5f09cd7166107'),
(1718, 18, 'sidebar_logos', ''),
(1719, 18, '_sidebar_logos', 'field_5f09cd7166107'),
(1720, 180, 'hero_heading', 'Our guide to selling your home'),
(1721, 180, '_hero_heading', 'field_5eff48a05507c'),
(1722, 180, 'has_search_bar', '0'),
(1723, 180, '_has_search_bar', 'field_5eff48a65507d'),
(1724, 180, 'guide_steps_0_image', '121'),
(1725, 180, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(1726, 180, 'guide_steps_0_title', 'Make your home look its best'),
(1727, 180, '_guide_steps_0_title', 'field_5f087a079a053'),
(1728, 180, 'guide_steps_0_content', 'When you make the decision to sell your home, agents will establish pricing for the property. Think about whether you’d like to stay in the same area, too: we’ll be able to advise you on the market around the region.'),
(1729, 180, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(1730, 180, 'guide_steps_0_is_highlighted', '0'),
(1731, 180, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(1732, 180, 'guide_steps_1_image', '119'),
(1733, 180, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(1734, 180, 'guide_steps_1_title', 'Get your property valued'),
(1735, 180, '_guide_steps_1_title', 'field_5f087a079a053'),
(1736, 180, 'guide_steps_1_content', 'We recommend putting your house on the market before making an offer on a house you’d like to buy. Make sure your home is looking its absolute best so you can attract potential buyers.'),
(1737, 180, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(1738, 180, 'guide_steps_1_is_highlighted', '0'),
(1739, 180, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(1740, 180, 'guide_steps_2_image', '67'),
(1741, 180, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(1742, 180, 'guide_steps_2_title', 'Set an asking price'),
(1743, 180, '_guide_steps_2_title', 'field_5f087a079a053'),
(1744, 180, 'guide_steps_2_content', 'Once we’ve determined the terms and conditions, we can then finalise the marketing approach.'),
(1745, 180, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(1746, 180, 'guide_steps_2_is_highlighted', '0'),
(1747, 180, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(1748, 180, 'guide_steps_3_image', '121'),
(1749, 180, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(1750, 180, 'guide_steps_3_title', 'Instruct an estate agent'),
(1751, 180, '_guide_steps_3_title', 'field_5f087a079a053'),
(1752, 180, 'guide_steps_3_content', 'We always advise the agent to carry out the viewings; people often feel more comfortable giving honest feedback to agents.'),
(1753, 180, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(1754, 180, 'guide_steps_3_is_highlighted', '1'),
(1755, 180, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(1756, 180, 'guide_steps_4_image', '120'),
(1757, 180, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(1758, 180, 'guide_steps_4_title', 'Help prepare the marketing materials'),
(1759, 180, '_guide_steps_4_title', 'field_5f087a079a053'),
(1760, 180, 'guide_steps_4_content', 'Any feedback we receive will be passed on to you promptly.'),
(1761, 180, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(1762, 180, 'guide_steps_4_is_highlighted', '0'),
(1763, 180, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(1764, 180, 'guide_steps_5_image', '119'),
(1765, 180, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(1766, 180, 'guide_steps_5_title', 'Get your paperwork in order'),
(1767, 180, '_guide_steps_5_title', 'field_5f087a079a053'),
(1768, 180, 'guide_steps_5_content', 'When an offer is received, the agent will check and vet the potential buyer, finance, and chain and discuss the next steps with the seller. We’ll work on your behalf to get you the best possible offer from the best possible buyer.'),
(1769, 180, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(1770, 180, 'guide_steps_5_is_highlighted', '0'),
(1771, 180, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(1772, 180, 'guide_steps_6_image', '120'),
(1773, 180, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(1774, 180, 'guide_steps_6_title', 'Conduct or work around viewings'),
(1775, 180, '_guide_steps_6_title', 'field_5f087a079a053'),
(1776, 180, 'guide_steps_6_content', 'When the offer is accepted, we’ll send out the memorandum of sale: this includes all relevant information regarding the sale.'),
(1777, 180, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(1778, 180, 'guide_steps_6_is_highlighted', '0'),
(1779, 180, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(1780, 180, 'guide_steps_7_image', '122'),
(1781, 180, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(1782, 180, 'guide_steps_7_title', 'Hire a solicitor or conveyancer'),
(1783, 180, '_guide_steps_7_title', 'field_5f087a079a053'),
(1784, 180, 'guide_steps_7_content', 'Solicitors will be instructed, and we will oversee the sale, communicating with all parties in the chain to make this process as smooth as possible.'),
(1785, 180, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(1786, 180, 'guide_steps_7_is_highlighted', '0'),
(1787, 180, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(1788, 180, 'guide_steps_8_image', '67'),
(1789, 180, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(1790, 180, 'guide_steps_8_title', 'Receive offers'),
(1791, 180, '_guide_steps_8_title', 'field_5f087a079a053'),
(1792, 180, 'guide_steps_8_content', 'Once the sale has been completed, it’s time to hire a removal company.'),
(1793, 180, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(1794, 180, 'guide_steps_8_is_highlighted', '0'),
(1795, 180, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(1796, 180, 'guide_steps_9_image', '121'),
(1797, 180, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(1798, 180, 'guide_steps_9_title', 'Accept or negotiate an offer') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1799, 180, '_guide_steps_9_title', 'field_5f087a079a053'),
(1800, 180, 'guide_steps_9_content', 'When the solicitor confirms the funds have been received, we can then hand over the keys to the property.'),
(1801, 180, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(1802, 180, 'guide_steps_9_is_highlighted', '0'),
(1803, 180, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(1804, 180, 'guide_steps', '10'),
(1805, 180, '_guide_steps', 'field_5f0879ee9a051'),
(1806, 180, 'h1', ''),
(1807, 180, '_h1', 'field_5f04e1b0f6df1'),
(1808, 180, 'show_why_choose_us', '0'),
(1809, 180, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1810, 180, 'show_buckets', '0'),
(1811, 180, '_show_buckets', 'field_5f04ecbe4e433'),
(1812, 180, 'show_team', '1'),
(1813, 180, '_show_team', 'field_5f04ed2a64341'),
(1814, 180, 'sidebar_logos', ''),
(1815, 180, '_sidebar_logos', 'field_5f09cd7166107'),
(1816, 181, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(1817, 181, '_hero_heading', 'field_5eff48a05507c'),
(1818, 181, 'has_search_bar', '0'),
(1819, 181, '_has_search_bar', 'field_5eff48a65507d'),
(1820, 181, 'h1', 'Sell your home with us'),
(1821, 181, '_h1', 'field_5f04e1b0f6df1'),
(1822, 181, 'why_choose_us', ''),
(1823, 181, '_why_choose_us', 'field_5f04e465f4a3e'),
(1824, 181, 'show_why_choose_us', '1'),
(1825, 181, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1826, 181, 'show_buckets', '1'),
(1827, 181, '_show_buckets', 'field_5f04ecbe4e433'),
(1828, 181, 'show_team', '1'),
(1829, 181, '_show_team', 'field_5f04ed2a64341'),
(1830, 181, 'sidebar_logos', ''),
(1831, 181, '_sidebar_logos', 'field_5f09cd7166107'),
(1832, 182, 'hero_heading', 'Our guide to selling your home'),
(1833, 182, '_hero_heading', 'field_5eff48a05507c'),
(1834, 182, 'has_search_bar', '0'),
(1835, 182, '_has_search_bar', 'field_5eff48a65507d'),
(1836, 182, 'guide_steps_0_image', '121'),
(1837, 182, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(1838, 182, 'guide_steps_0_title', 'Make your home look its best'),
(1839, 182, '_guide_steps_0_title', 'field_5f087a079a053'),
(1840, 182, 'guide_steps_0_content', 'When you make the decision to sell your home, agents will establish pricing for the property. Think about whether you’d like to stay in the same area, too: we’ll be able to advise you on the market around the region.'),
(1841, 182, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(1842, 182, 'guide_steps_0_is_highlighted', '0'),
(1843, 182, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(1844, 182, 'guide_steps_1_image', '119'),
(1845, 182, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(1846, 182, 'guide_steps_1_title', 'Get your property valued'),
(1847, 182, '_guide_steps_1_title', 'field_5f087a079a053'),
(1848, 182, 'guide_steps_1_content', 'We recommend putting your house on the market before making an offer on a house you’d like to buy. Make sure your home is looking its absolute best so you can attract potential buyers.'),
(1849, 182, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(1850, 182, 'guide_steps_1_is_highlighted', '0'),
(1851, 182, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(1852, 182, 'guide_steps_2_image', '67'),
(1853, 182, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(1854, 182, 'guide_steps_2_title', 'Get the property on the market'),
(1855, 182, '_guide_steps_2_title', 'field_5f087a079a053'),
(1856, 182, 'guide_steps_2_content', 'Once we’ve determined the terms and conditions, we can then finalise the marketing approach.'),
(1857, 182, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(1858, 182, 'guide_steps_2_is_highlighted', '0'),
(1859, 182, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(1860, 182, 'guide_steps_3_image', '121'),
(1861, 182, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(1862, 182, 'guide_steps_3_title', 'Carry out viewings'),
(1863, 182, '_guide_steps_3_title', 'field_5f087a079a053'),
(1864, 182, 'guide_steps_3_content', 'We always advise the agent to carry out the viewings; people often feel more comfortable giving honest feedback to agents.'),
(1865, 182, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(1866, 182, 'guide_steps_3_is_highlighted', '0'),
(1867, 182, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(1868, 182, 'guide_steps_4_image', '120'),
(1869, 182, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(1870, 182, 'guide_steps_4_title', 'Review any feedback'),
(1871, 182, '_guide_steps_4_title', 'field_5f087a079a053'),
(1872, 182, 'guide_steps_4_content', 'Any feedback we receive will be passed on to you promptly.'),
(1873, 182, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(1874, 182, 'guide_steps_4_is_highlighted', '0'),
(1875, 182, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(1876, 182, 'guide_steps_5_image', '119'),
(1877, 182, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(1878, 182, 'guide_steps_5_title', 'Receive offers to buy'),
(1879, 182, '_guide_steps_5_title', 'field_5f087a079a053'),
(1880, 182, 'guide_steps_5_content', 'When an offer is received, the agent will check and vet the potential buyer, finance, and chain and discuss the next steps with the seller. We’ll work on your behalf to get you the best possible offer from the best possible buyer.'),
(1881, 182, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(1882, 182, 'guide_steps_5_is_highlighted', '0'),
(1883, 182, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(1884, 182, 'guide_steps_6_image', '120'),
(1885, 182, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(1886, 182, 'guide_steps_6_title', 'Accept an offer'),
(1887, 182, '_guide_steps_6_title', 'field_5f087a079a053'),
(1888, 182, 'guide_steps_6_content', 'When the offer is accepted, we’ll send out the memorandum of sale: this includes all relevant information regarding the sale.'),
(1889, 182, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(1890, 182, 'guide_steps_6_is_highlighted', '0'),
(1891, 182, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(1892, 182, 'guide_steps_7_image', '122'),
(1893, 182, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(1894, 182, 'guide_steps_7_title', 'Hire a solicitor'),
(1895, 182, '_guide_steps_7_title', 'field_5f087a079a053'),
(1896, 182, 'guide_steps_7_content', 'Solicitors will be instructed, and we will oversee the sale, communicating with all parties in the chain to make this process as smooth as possible.'),
(1897, 182, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(1898, 182, 'guide_steps_7_is_highlighted', '0') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1899, 182, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(1900, 182, 'guide_steps_8_image', '67'),
(1901, 182, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(1902, 182, 'guide_steps_8_title', 'Hire a removals company'),
(1903, 182, '_guide_steps_8_title', 'field_5f087a079a053'),
(1904, 182, 'guide_steps_8_content', 'Once the sale has been completed, it’s time to hire a removal company.'),
(1905, 182, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(1906, 182, 'guide_steps_8_is_highlighted', '0'),
(1907, 182, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(1908, 182, 'guide_steps_9_image', '121'),
(1909, 182, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(1910, 182, 'guide_steps_9_title', 'Exchange keys!'),
(1911, 182, '_guide_steps_9_title', 'field_5f087a079a053'),
(1912, 182, 'guide_steps_9_content', 'When the solicitor confirms the funds have been received, we can then hand over the keys to the property.'),
(1913, 182, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(1914, 182, 'guide_steps_9_is_highlighted', '1'),
(1915, 182, '_guide_steps_9_is_highlighted', 'field_5f087a179a055'),
(1916, 182, 'guide_steps', '10'),
(1917, 182, '_guide_steps', 'field_5f0879ee9a051'),
(1918, 182, 'h1', ''),
(1919, 182, '_h1', 'field_5f04e1b0f6df1'),
(1920, 182, 'show_why_choose_us', '0'),
(1921, 182, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1922, 182, 'show_buckets', '0'),
(1923, 182, '_show_buckets', 'field_5f04ecbe4e433'),
(1924, 182, 'show_team', '1'),
(1925, 182, '_show_team', 'field_5f04ed2a64341'),
(1926, 182, 'sidebar_logos', ''),
(1927, 182, '_sidebar_logos', 'field_5f09cd7166107'),
(1928, 16, 'h1', ''),
(1929, 16, '_h1', 'field_5f04e1b0f6df1'),
(1930, 16, 'show_why_choose_us', '0'),
(1931, 16, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1932, 16, 'show_buckets', '0'),
(1933, 16, '_show_buckets', 'field_5f04ecbe4e433'),
(1934, 16, 'show_team', '0'),
(1935, 16, '_show_team', 'field_5f04ed2a64341'),
(1936, 16, 'sidebar_logos', ''),
(1937, 16, '_sidebar_logos', 'field_5f09cd7166107'),
(1938, 183, 'hero_heading', 'Meet Fullbrook & Floor'),
(1939, 183, '_hero_heading', 'field_5eff48a05507c'),
(1940, 183, 'has_search_bar', '0'),
(1941, 183, '_has_search_bar', 'field_5eff48a65507d'),
(1942, 183, 'team_members_0_name', 'Rene Floor'),
(1943, 183, '_team_members_0_name', 'field_5eff534e75de2'),
(1944, 183, 'team_members_0_job_title', 'Director'),
(1945, 183, '_team_members_0_job_title', 'field_5eff535575de3'),
(1946, 183, 'team_members_0_phone_number', '020 000 0000'),
(1947, 183, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(1948, 183, 'team_members_0_email_address', 'rene@fullbrookandfloor.co.uk'),
(1949, 183, '_team_members_0_email_address', 'field_5eff536475de5'),
(1950, 183, 'team_members_0_profile_photo', '91'),
(1951, 183, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(1952, 183, 'team_members_0_biography', 'Rene met his wife Jo in 1996, and lived in Amsterdam until 2005, when they moved to Hertfordshire. Rene started his agency career at a respectable agency in Amsterdam. He describes working in the Amsterdam property market as “different in the buying and selling process, but with the same key elements - helping people with the biggest assets of their lives”. Since 2005, Rene has worked for two agencies in St. Albans, mastering the challenges faced in a different property market and helping people find their dream home.\r\nOutside of work, Rene loves to spend time with his sports-mad family: his wife Jo is a keen hockey player, and his son Casper is a talented judoka, and Jack is a promising footballer and hockey player. When not spending time with his family, Rene likes to test his own sporting talents through running, tennis and a kickabout with friends.'),
(1953, 183, '_team_members_0_biography', 'field_5eff536f75de6'),
(1954, 183, 'team_members_1_name', 'Rod Fullbrook'),
(1955, 183, '_team_members_1_name', 'field_5eff534e75de2'),
(1956, 183, 'team_members_1_job_title', 'Director'),
(1957, 183, '_team_members_1_job_title', 'field_5eff535575de3'),
(1958, 183, 'team_members_1_phone_number', '020 000 0000'),
(1959, 183, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(1960, 183, 'team_members_1_email_address', 'rod@fullbrookandfloor.co.uk'),
(1961, 183, '_team_members_1_email_address', 'field_5eff536475de5'),
(1962, 183, 'team_members_1_profile_photo', '91'),
(1963, 183, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(1964, 183, 'team_members_1_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is a brilliant golfer with an enviable single-figure handicap, and Jack provides his footballing talents to the local Harvesters football club.\r\n\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(1965, 183, '_team_members_1_biography', 'field_5eff536f75de6'),
(1966, 183, 'team_members', '2'),
(1967, 183, '_team_members', 'field_5eff533075de0'),
(1968, 183, 'h1', ''),
(1969, 183, '_h1', 'field_5f04e1b0f6df1'),
(1970, 183, 'show_why_choose_us', '0'),
(1971, 183, '_show_why_choose_us', 'field_5f04ecb34e432'),
(1972, 183, 'show_buckets', '0'),
(1973, 183, '_show_buckets', 'field_5f04ecbe4e433'),
(1974, 183, 'show_team', '0'),
(1975, 183, '_show_team', 'field_5f04ed2a64341'),
(1976, 183, 'sidebar_logos', ''),
(1977, 183, '_sidebar_logos', 'field_5f09cd7166107'),
(1978, 184, 'hero_heading', 'Meet Fullbrook & Floor'),
(1979, 184, '_hero_heading', 'field_5eff48a05507c'),
(1980, 184, 'has_search_bar', '0'),
(1981, 184, '_has_search_bar', 'field_5eff48a65507d'),
(1982, 184, 'team_members_0_name', 'Rene Floor'),
(1983, 184, '_team_members_0_name', 'field_5eff534e75de2'),
(1984, 184, 'team_members_0_job_title', 'Director'),
(1985, 184, '_team_members_0_job_title', 'field_5eff535575de3'),
(1986, 184, 'team_members_0_phone_number', '020 000 0000'),
(1987, 184, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(1988, 184, 'team_members_0_email_address', 'rene@fullbrookandfloor.co.uk'),
(1989, 184, '_team_members_0_email_address', 'field_5eff536475de5'),
(1990, 184, 'team_members_0_profile_photo', '91'),
(1991, 184, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(1992, 184, 'team_members_0_biography', 'Rene met his wife Jo in 1996, and lived in Amsterdam until 2005, when they moved to Hertfordshire. Rene started his agency career at a respectable agency in Amsterdam. He describes working in the Amsterdam property market as “different in the buying and selling process, but with the same key elements - helping people with the biggest assets of their lives”. Since 2005, Rene has worked for two agencies in St. Albans, mastering the challenges faced in a different property market and helping people find their dream home.\r\n\r\nOutside of work, Rene loves to spend time with his sports-mad family: his wife Jo is a keen hockey player, and his son Casper is a talented judoka, and Jack is a promising footballer and hockey player. When not spending time with his family, Rene likes to test his own sporting talents through running, tennis and a kickabout with friends.'),
(1993, 184, '_team_members_0_biography', 'field_5eff536f75de6'),
(1994, 184, 'team_members_1_name', 'Rod Fullbrook'),
(1995, 184, '_team_members_1_name', 'field_5eff534e75de2'),
(1996, 184, 'team_members_1_job_title', 'Director'),
(1997, 184, '_team_members_1_job_title', 'field_5eff535575de3'),
(1998, 184, 'team_members_1_phone_number', '020 000 0000') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1999, 184, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(2000, 184, 'team_members_1_email_address', 'rod@fullbrookandfloor.co.uk'),
(2001, 184, '_team_members_1_email_address', 'field_5eff536475de5'),
(2002, 184, 'team_members_1_profile_photo', '91'),
(2003, 184, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(2004, 184, 'team_members_1_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is a brilliant golfer with an enviable single-figure handicap, and Jack provides his footballing talents to the local Harvesters football club.\r\n\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(2005, 184, '_team_members_1_biography', 'field_5eff536f75de6'),
(2006, 184, 'team_members', '2'),
(2007, 184, '_team_members', 'field_5eff533075de0'),
(2008, 184, 'h1', ''),
(2009, 184, '_h1', 'field_5f04e1b0f6df1'),
(2010, 184, 'show_why_choose_us', '0'),
(2011, 184, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2012, 184, 'show_buckets', '0'),
(2013, 184, '_show_buckets', 'field_5f04ecbe4e433'),
(2014, 184, 'show_team', '0'),
(2015, 184, '_show_team', 'field_5f04ed2a64341'),
(2016, 184, 'sidebar_logos', ''),
(2017, 184, '_sidebar_logos', 'field_5f09cd7166107'),
(2018, 185, 'hero_heading', 'Search for a property to buy'),
(2019, 185, '_hero_heading', 'field_5eff48a05507c'),
(2020, 185, 'has_search_bar', '1'),
(2021, 185, '_has_search_bar', 'field_5eff48a65507d'),
(2022, 185, 'h1', ''),
(2023, 185, '_h1', 'field_5f04e1b0f6df1'),
(2024, 185, 'show_why_choose_us', '0'),
(2025, 185, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2026, 185, 'show_buckets', '0'),
(2027, 185, '_show_buckets', 'field_5f04ecbe4e433'),
(2028, 185, 'show_team', '0'),
(2029, 185, '_show_team', 'field_5f04ed2a64341'),
(2030, 185, 'sidebar_logos', ''),
(2031, 185, '_sidebar_logos', 'field_5f09cd7166107'),
(2032, 186, 'hero_heading', 'Meet Rene & Rod'),
(2033, 186, '_hero_heading', 'field_5eff48a05507c'),
(2034, 186, 'has_search_bar', '0'),
(2035, 186, '_has_search_bar', 'field_5eff48a65507d'),
(2036, 186, 'team_members_0_name', 'Rod Fullbrook'),
(2037, 186, '_team_members_0_name', 'field_5eff534e75de2'),
(2038, 186, 'team_members_0_job_title', 'Director'),
(2039, 186, '_team_members_0_job_title', 'field_5eff535575de3'),
(2040, 186, 'team_members_0_phone_number', '020 000 0000'),
(2041, 186, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(2042, 186, 'team_members_0_email_address', 'rod@fullbrookandfloor.co.uk'),
(2043, 186, '_team_members_0_email_address', 'field_5eff536475de5'),
(2044, 186, 'team_members_0_profile_photo', '91'),
(2045, 186, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(2046, 186, 'team_members_0_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is a brilliant golfer with an enviable single-figure handicap, and Jack provides his footballing talents to the local Harvesters football club.\r\n\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(2047, 186, '_team_members_0_biography', 'field_5eff536f75de6'),
(2048, 186, 'team_members_1_name', 'Rene Floor'),
(2049, 186, '_team_members_1_name', 'field_5eff534e75de2'),
(2050, 186, 'team_members_1_job_title', 'Director'),
(2051, 186, '_team_members_1_job_title', 'field_5eff535575de3'),
(2052, 186, 'team_members_1_phone_number', '020 000 0000'),
(2053, 186, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(2054, 186, 'team_members_1_email_address', 'rene@fullbrookandfloor.co.uk'),
(2055, 186, '_team_members_1_email_address', 'field_5eff536475de5'),
(2056, 186, 'team_members_1_profile_photo', '91'),
(2057, 186, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(2058, 186, 'team_members_1_biography', 'Rene met his wife Jo in 1996, and lived in Amsterdam until 2005, when they moved to Hertfordshire. Rene started his agency career at a respectable agency in Amsterdam. He describes working in the Amsterdam property market as “different in the buying and selling process, but with the same key elements - helping people with the biggest assets of their lives”. Since 2005, Rene has worked for two agencies in St. Albans, mastering the challenges faced in a different property market and helping people find their dream home.\r\n\r\nOutside of work, Rene loves to spend time with his sports-mad family: his wife Jo is a keen hockey player, and his son Casper is a talented judoka, and Jack is a promising footballer and hockey player. When not spending time with his family, Rene likes to test his own sporting talents through running, tennis and a kickabout with friends.'),
(2059, 186, '_team_members_1_biography', 'field_5eff536f75de6'),
(2060, 186, 'team_members', '2'),
(2061, 186, '_team_members', 'field_5eff533075de0'),
(2062, 186, 'h1', ''),
(2063, 186, '_h1', 'field_5f04e1b0f6df1'),
(2064, 186, 'show_why_choose_us', '0'),
(2065, 186, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2066, 186, 'show_buckets', '0'),
(2067, 186, '_show_buckets', 'field_5f04ecbe4e433'),
(2068, 186, 'show_team', '0'),
(2069, 186, '_show_team', 'field_5f04ed2a64341'),
(2070, 186, 'sidebar_logos', ''),
(2071, 186, '_sidebar_logos', 'field_5f09cd7166107'),
(2072, 6, 'hero_carousel', 'a:3:{i:0;s:2:"67";i:1;s:3:"122";i:2;s:3:"120";}'),
(2073, 6, '_hero_carousel', 'field_5f0f32444af92'),
(2074, 189, 'hero_top_line', 'Friendly, local'),
(2075, 189, '_hero_top_line', 'field_5eff38fcccfce'),
(2076, 189, 'hero_main_line', 'Estate agents in St. Albans'),
(2077, 189, '_hero_main_line', 'field_5eff3910ccfcf'),
(2078, 189, 'h1', 'Estate agents in St. Albans'),
(2079, 189, '_h1', 'field_5f04a835c98a9'),
(2080, 189, 'hero_carousel', 'a:3:{i:0;s:2:"67";i:1;s:3:"122";i:2;s:3:"120";}'),
(2081, 189, '_hero_carousel', 'field_5f0f32444af92'),
(2082, 190, 'hero_heading', 'Learn about Fullbrook & Floor'),
(2083, 190, '_hero_heading', 'field_5eff48a05507c'),
(2084, 190, 'has_search_bar', '0'),
(2085, 190, '_has_search_bar', 'field_5eff48a65507d'),
(2086, 190, 'h1', ''),
(2087, 190, '_h1', 'field_5f04e1b0f6df1'),
(2088, 190, 'show_why_choose_us', '0'),
(2089, 190, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2090, 190, 'show_buckets', '1'),
(2091, 190, '_show_buckets', 'field_5f04ecbe4e433'),
(2092, 190, 'show_team', '1'),
(2093, 190, '_show_team', 'field_5f04ed2a64341'),
(2094, 190, 'sidebar_logos_0_name', 'Rightmove'),
(2095, 190, '_sidebar_logos_0_name', 'field_5f09cd9466109'),
(2096, 190, 'sidebar_logos_0_image', '171'),
(2097, 190, '_sidebar_logos_0_image', 'field_5f09cd8a66108'),
(2098, 190, 'sidebar_logos_0_link', 'https://www.rightmove.co.uk/') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(2099, 190, '_sidebar_logos_0_link', 'field_5f09cd9d6610a'),
(2100, 190, 'sidebar_logos_1_name', 'Zoopla'),
(2101, 190, '_sidebar_logos_1_name', 'field_5f09cd9466109'),
(2102, 190, 'sidebar_logos_1_image', '172'),
(2103, 190, '_sidebar_logos_1_image', 'field_5f09cd8a66108'),
(2104, 190, 'sidebar_logos_1_link', 'https://www.zoopla.co.uk/'),
(2105, 190, '_sidebar_logos_1_link', 'field_5f09cd9d6610a'),
(2106, 190, 'sidebar_logos', '2'),
(2107, 190, '_sidebar_logos', 'field_5f09cd7166107'),
(2108, 197, '_wp_attached_file', '2020/07/Rightmove_logo_DEC2016.png'),
(2109, 197, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:798;s:6:"height";i:174;s:4:"file";s:34:"2020/07/Rightmove_logo_DEC2016.png";s:5:"sizes";a:3:{s:6:"medium";a:4:{s:4:"file";s:33:"Rightmove_logo_DEC2016-300x65.png";s:5:"width";i:300;s:6:"height";i:65;s:9:"mime-type";s:9:"image/png";}s:9:"thumbnail";a:4:{s:4:"file";s:34:"Rightmove_logo_DEC2016-150x150.png";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:9:"image/png";}s:12:"medium_large";a:4:{s:4:"file";s:34:"Rightmove_logo_DEC2016-768x167.png";s:5:"width";i:768;s:6:"height";i:167;s:9:"mime-type";s:9:"image/png";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(2110, 198, '_wp_attached_file', '2020/07/Zoopla-logo-Purple-RGBPNG.png'),
(2111, 198, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:842;s:6:"height";i:243;s:4:"file";s:37:"2020/07/Zoopla-logo-Purple-RGBPNG.png";s:5:"sizes";a:3:{s:6:"medium";a:4:{s:4:"file";s:36:"Zoopla-logo-Purple-RGBPNG-300x87.png";s:5:"width";i:300;s:6:"height";i:87;s:9:"mime-type";s:9:"image/png";}s:9:"thumbnail";a:4:{s:4:"file";s:37:"Zoopla-logo-Purple-RGBPNG-150x150.png";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:9:"image/png";}s:12:"medium_large";a:4:{s:4:"file";s:37:"Zoopla-logo-Purple-RGBPNG-768x222.png";s:5:"width";i:768;s:6:"height";i:222;s:9:"mime-type";s:9:"image/png";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(2112, 199, 'hero_heading', 'Learn about Fullbrook & Floor'),
(2113, 199, '_hero_heading', 'field_5eff48a05507c'),
(2114, 199, 'has_search_bar', '0'),
(2115, 199, '_has_search_bar', 'field_5eff48a65507d'),
(2116, 199, 'h1', ''),
(2117, 199, '_h1', 'field_5f04e1b0f6df1'),
(2118, 199, 'show_why_choose_us', '0'),
(2119, 199, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2120, 199, 'show_buckets', '1'),
(2121, 199, '_show_buckets', 'field_5f04ecbe4e433'),
(2122, 199, 'show_team', '1'),
(2123, 199, '_show_team', 'field_5f04ed2a64341'),
(2124, 199, 'sidebar_logos_0_name', 'Rightmove'),
(2125, 199, '_sidebar_logos_0_name', 'field_5f09cd9466109'),
(2126, 199, 'sidebar_logos_0_image', '197'),
(2127, 199, '_sidebar_logos_0_image', 'field_5f09cd8a66108'),
(2128, 199, 'sidebar_logos_0_link', 'https://www.rightmove.co.uk/'),
(2129, 199, '_sidebar_logos_0_link', 'field_5f09cd9d6610a'),
(2130, 199, 'sidebar_logos_1_name', 'Zoopla'),
(2131, 199, '_sidebar_logos_1_name', 'field_5f09cd9466109'),
(2132, 199, 'sidebar_logos_1_image', '198'),
(2133, 199, '_sidebar_logos_1_image', 'field_5f09cd8a66108'),
(2134, 199, 'sidebar_logos_1_link', 'https://www.zoopla.co.uk/'),
(2135, 199, '_sidebar_logos_1_link', 'field_5f09cd9d6610a'),
(2136, 199, 'sidebar_logos', '2'),
(2137, 199, '_sidebar_logos', 'field_5f09cd7166107'),
(2138, 201, '_wp_attached_file', '2020/07/profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D.jpg'),
(2139, 201, '_wp_attachment_metadata', 'a:5:{s:5:"width";i:1300;s:6:"height";i:956;s:4:"file";s:163:"2020/07/profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D.jpg";s:5:"sizes";a:4:{s:6:"medium";a:4:{s:4:"file";s:163:"profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D-300x221.jpg";s:5:"width";i:300;s:6:"height";i:221;s:9:"mime-type";s:10:"image/jpeg";}s:5:"large";a:4:{s:4:"file";s:164:"profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D-1024x753.jpg";s:5:"width";i:1024;s:6:"height";i:753;s:9:"mime-type";s:10:"image/jpeg";}s:9:"thumbnail";a:4:{s:4:"file";s:163:"profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}s:12:"medium_large";a:4:{s:4:"file";s:163:"profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D-768x565.jpg";s:5:"width";i:768;s:6:"height";i:565;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}'),
(2140, 16, 'team_members_0_casual_photo', '201'),
(2141, 16, '_team_members_0_casual_photo', 'field_5f0f3a3dda3d7'),
(2142, 16, 'team_members_1_casual_photo', '201'),
(2143, 16, '_team_members_1_casual_photo', 'field_5f0f3a3dda3d7'),
(2144, 202, 'hero_heading', 'Meet Rene & Rod'),
(2145, 202, '_hero_heading', 'field_5eff48a05507c'),
(2146, 202, 'has_search_bar', '0'),
(2147, 202, '_has_search_bar', 'field_5eff48a65507d'),
(2148, 202, 'team_members_0_name', 'Rod Fullbrook'),
(2149, 202, '_team_members_0_name', 'field_5eff534e75de2'),
(2150, 202, 'team_members_0_job_title', 'Director'),
(2151, 202, '_team_members_0_job_title', 'field_5eff535575de3'),
(2152, 202, 'team_members_0_phone_number', '020 000 0000'),
(2153, 202, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(2154, 202, 'team_members_0_email_address', 'rod@fullbrookandfloor.co.uk'),
(2155, 202, '_team_members_0_email_address', 'field_5eff536475de5'),
(2156, 202, 'team_members_0_profile_photo', '91'),
(2157, 202, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(2158, 202, 'team_members_0_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is a brilliant golfer with an enviable single-figure handicap, and Jack provides his footballing talents to the local Harvesters football club.\r\n\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(2159, 202, '_team_members_0_biography', 'field_5eff536f75de6'),
(2160, 202, 'team_members_1_name', 'Rene Floor'),
(2161, 202, '_team_members_1_name', 'field_5eff534e75de2'),
(2162, 202, 'team_members_1_job_title', 'Director'),
(2163, 202, '_team_members_1_job_title', 'field_5eff535575de3'),
(2164, 202, 'team_members_1_phone_number', '020 000 0000'),
(2165, 202, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(2166, 202, 'team_members_1_email_address', 'rene@fullbrookandfloor.co.uk'),
(2167, 202, '_team_members_1_email_address', 'field_5eff536475de5'),
(2168, 202, 'team_members_1_profile_photo', '91'),
(2169, 202, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(2170, 202, 'team_members_1_biography', 'Rene met his wife Jo in 1996, and lived in Amsterdam until 2005, when they moved to Hertfordshire. Rene started his agency career at a respectable agency in Amsterdam. He describes working in the Amsterdam property market as “different in the buying and selling process, but with the same key elements - helping people with the biggest assets of their lives”. Since 2005, Rene has worked for two agencies in St. Albans, mastering the challenges faced in a different property market and helping people find their dream home.\r\n\r\nOutside of work, Rene loves to spend time with his sports-mad family: his wife Jo is a keen hockey player, and his son Casper is a talented judoka, and Jack is a promising footballer and hockey player. When not spending time with his family, Rene likes to test his own sporting talents through running, tennis and a kickabout with friends.'),
(2171, 202, '_team_members_1_biography', 'field_5eff536f75de6'),
(2172, 202, 'team_members', '2'),
(2173, 202, '_team_members', 'field_5eff533075de0'),
(2174, 202, 'h1', ''),
(2175, 202, '_h1', 'field_5f04e1b0f6df1'),
(2176, 202, 'show_why_choose_us', '0'),
(2177, 202, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2178, 202, 'show_buckets', '0'),
(2179, 202, '_show_buckets', 'field_5f04ecbe4e433'),
(2180, 202, 'show_team', '0'),
(2181, 202, '_show_team', 'field_5f04ed2a64341'),
(2182, 202, 'sidebar_logos', ''),
(2183, 202, '_sidebar_logos', 'field_5f09cd7166107'),
(2184, 202, 'team_members_0_casual_photo', '201'),
(2185, 202, '_team_members_0_casual_photo', 'field_5f0f3a3dda3d7'),
(2186, 202, 'team_members_1_casual_photo', '201'),
(2187, 202, '_team_members_1_casual_photo', 'field_5f0f3a3dda3d7'),
(2188, 203, '_wp_attached_file', '2020/07/logo-update-v8.svg'),
(2189, 204, 'hero_top_line', 'Friendly, local'),
(2190, 204, '_hero_top_line', 'field_5eff38fcccfce'),
(2191, 204, 'hero_main_line', 'Estate agents in St. Albans'),
(2192, 204, '_hero_main_line', 'field_5eff3910ccfcf'),
(2193, 204, 'h1', 'Estate agents in St. Albans'),
(2194, 204, '_h1', 'field_5f04a835c98a9'),
(2195, 204, 'hero_carousel', 'a:3:{i:0;s:2:"67";i:1;s:3:"122";i:2;s:3:"120";}'),
(2196, 204, '_hero_carousel', 'field_5f0f32444af92'),
(2197, 205, 'hero_heading', 'Meet Rene & Rod'),
(2198, 205, '_hero_heading', 'field_5eff48a05507c') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(2199, 205, 'has_search_bar', '0'),
(2200, 205, '_has_search_bar', 'field_5eff48a65507d'),
(2201, 205, 'team_members_0_name', 'Rod Fullbrook'),
(2202, 205, '_team_members_0_name', 'field_5eff534e75de2'),
(2203, 205, 'team_members_0_job_title', 'Director'),
(2204, 205, '_team_members_0_job_title', 'field_5eff535575de3'),
(2205, 205, 'team_members_0_phone_number', '020 000 0000'),
(2206, 205, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(2207, 205, 'team_members_0_email_address', 'rod@fullbrookandfloor.co.uk'),
(2208, 205, '_team_members_0_email_address', 'field_5eff536475de5'),
(2209, 205, 'team_members_0_profile_photo', '91'),
(2210, 205, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(2211, 205, 'team_members_0_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is an aspiring golfer with an enviable single-figure handicap, and Sam provides his footballing talents to the local Harvesters football club.\r\n\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(2212, 205, '_team_members_0_biography', 'field_5eff536f75de6'),
(2213, 205, 'team_members_1_name', 'Rene Floor'),
(2214, 205, '_team_members_1_name', 'field_5eff534e75de2'),
(2215, 205, 'team_members_1_job_title', 'Director'),
(2216, 205, '_team_members_1_job_title', 'field_5eff535575de3'),
(2217, 205, 'team_members_1_phone_number', '020 000 0000'),
(2218, 205, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(2219, 205, 'team_members_1_email_address', 'rene@fullbrookandfloor.co.uk'),
(2220, 205, '_team_members_1_email_address', 'field_5eff536475de5'),
(2221, 205, 'team_members_1_profile_photo', '91'),
(2222, 205, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(2223, 205, 'team_members_1_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is an aspiring golfer with an enviable single-figure handicap, and Sam provides his footballing talents to the local Harvesters football club.\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(2224, 205, '_team_members_1_biography', 'field_5eff536f75de6'),
(2225, 205, 'team_members', '2'),
(2226, 205, '_team_members', 'field_5eff533075de0'),
(2227, 205, 'h1', ''),
(2228, 205, '_h1', 'field_5f04e1b0f6df1'),
(2229, 205, 'show_why_choose_us', '0'),
(2230, 205, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2231, 205, 'show_buckets', '0'),
(2232, 205, '_show_buckets', 'field_5f04ecbe4e433'),
(2233, 205, 'show_team', '0'),
(2234, 205, '_show_team', 'field_5f04ed2a64341'),
(2235, 205, 'sidebar_logos', ''),
(2236, 205, '_sidebar_logos', 'field_5f09cd7166107'),
(2237, 205, 'team_members_0_casual_photo', '201'),
(2238, 205, '_team_members_0_casual_photo', 'field_5f0f3a3dda3d7'),
(2239, 205, 'team_members_1_casual_photo', '201'),
(2240, 205, '_team_members_1_casual_photo', 'field_5f0f3a3dda3d7'),
(2241, 206, 'hero_heading', 'Meet Rene & Rod'),
(2242, 206, '_hero_heading', 'field_5eff48a05507c'),
(2243, 206, 'has_search_bar', '0'),
(2244, 206, '_has_search_bar', 'field_5eff48a65507d'),
(2245, 206, 'team_members_0_name', 'Rod Fullbrook'),
(2246, 206, '_team_members_0_name', 'field_5eff534e75de2'),
(2247, 206, 'team_members_0_job_title', 'Director'),
(2248, 206, '_team_members_0_job_title', 'field_5eff535575de3'),
(2249, 206, 'team_members_0_phone_number', '020 000 0000'),
(2250, 206, '_team_members_0_phone_number', 'field_5eff535c75de4'),
(2251, 206, 'team_members_0_email_address', 'rod@fullbrookandfloor.co.uk'),
(2252, 206, '_team_members_0_email_address', 'field_5eff536475de5'),
(2253, 206, 'team_members_0_profile_photo', '91'),
(2254, 206, '_team_members_0_profile_photo', 'field_5eff534275de1'),
(2255, 206, 'team_members_0_biography', 'A born-and-bred St. Albans native, Rod lives in the city with his wife, Kamila and his children, 14 year-old Sophia and 11 year-old Sam. Sophia is an aspiring golfer with an enviable single-figure handicap, and Sam provides his footballing talents to the local Harvesters football club.\r\n\r\nRod’s connection to the area is a deep-rooted one; before starting his career in estate agency, he worked as a sports coach, teaching swimming and football to kids in the community. When he and Kamila purchased their first house in St. Albans, Rod was inspired to start his own career in estate agency.'),
(2256, 206, '_team_members_0_biography', 'field_5eff536f75de6'),
(2257, 206, 'team_members_1_name', 'Rene Floor'),
(2258, 206, '_team_members_1_name', 'field_5eff534e75de2'),
(2259, 206, 'team_members_1_job_title', 'Director'),
(2260, 206, '_team_members_1_job_title', 'field_5eff535575de3'),
(2261, 206, 'team_members_1_phone_number', '020 000 0000'),
(2262, 206, '_team_members_1_phone_number', 'field_5eff535c75de4'),
(2263, 206, 'team_members_1_email_address', 'rene@fullbrookandfloor.co.uk'),
(2264, 206, '_team_members_1_email_address', 'field_5eff536475de5'),
(2265, 206, 'team_members_1_profile_photo', '91'),
(2266, 206, '_team_members_1_profile_photo', 'field_5eff534275de1'),
(2267, 206, 'team_members_1_biography', 'René met his English wife Jo in the mid 90s, and they lived near Amsterdam until 2005, when they moved to Hertfordshire. René started his agency career at a respectable agency in Amsterdam, describing working in the Dutch property market as “different in the buying and selling process, but with the same key elements - helping people with the biggest assets of their lives”. Since 2005, René has worked for two agencies in St. Albans, mastering the challenges faced in a different property market and helping people find their ideal home.\r\n\r\nOutside of work, René loves to spend time with his sports-mad family: his wife Jo is a keen hockey player, and his son Casper is an enthusiastic judoka, and Jack is a promising footballer and hockey player. When not spending time with his family, René likes to test his own sporting talents through running, tennis and a kickabout with friends.'),
(2268, 206, '_team_members_1_biography', 'field_5eff536f75de6'),
(2269, 206, 'team_members', '2'),
(2270, 206, '_team_members', 'field_5eff533075de0'),
(2271, 206, 'h1', ''),
(2272, 206, '_h1', 'field_5f04e1b0f6df1'),
(2273, 206, 'show_why_choose_us', '0'),
(2274, 206, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2275, 206, 'show_buckets', '0'),
(2276, 206, '_show_buckets', 'field_5f04ecbe4e433'),
(2277, 206, 'show_team', '0'),
(2278, 206, '_show_team', 'field_5f04ed2a64341'),
(2279, 206, 'sidebar_logos', ''),
(2280, 206, '_sidebar_logos', 'field_5f09cd7166107'),
(2281, 206, 'team_members_0_casual_photo', '201'),
(2282, 206, '_team_members_0_casual_photo', 'field_5f0f3a3dda3d7'),
(2283, 206, 'team_members_1_casual_photo', '201'),
(2284, 206, '_team_members_1_casual_photo', 'field_5f0f3a3dda3d7'),
(2285, 207, 'hero_heading', 'Search for a property to buy'),
(2286, 207, '_hero_heading', 'field_5eff48a05507c'),
(2287, 207, 'has_search_bar', '1'),
(2288, 207, '_has_search_bar', 'field_5eff48a65507d'),
(2289, 207, 'h1', ''),
(2290, 207, '_h1', 'field_5f04e1b0f6df1'),
(2291, 207, 'show_why_choose_us', '0'),
(2292, 207, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2293, 207, 'show_buckets', '0'),
(2294, 207, '_show_buckets', 'field_5f04ecbe4e433'),
(2295, 207, 'show_team', '0'),
(2296, 207, '_show_team', 'field_5f04ed2a64341'),
(2297, 207, 'sidebar_logos', ''),
(2298, 207, '_sidebar_logos', 'field_5f09cd7166107') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(2299, 209, 'hero_heading', 'Sell your home with Fullbrook & Floor'),
(2300, 209, '_hero_heading', 'field_5eff48a05507c'),
(2301, 209, 'has_search_bar', '0'),
(2302, 209, '_has_search_bar', 'field_5eff48a65507d'),
(2303, 209, 'h1', 'Sell your home with us'),
(2304, 209, '_h1', 'field_5f04e1b0f6df1'),
(2305, 209, 'why_choose_us', ''),
(2306, 209, '_why_choose_us', 'field_5f04e465f4a3e'),
(2307, 209, 'show_why_choose_us', '1'),
(2308, 209, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2309, 209, 'show_buckets', '1'),
(2310, 209, '_show_buckets', 'field_5f04ecbe4e433'),
(2311, 209, 'show_team', '1'),
(2312, 209, '_show_team', 'field_5f04ed2a64341'),
(2313, 209, 'sidebar_logos', ''),
(2314, 209, '_sidebar_logos', 'field_5f09cd7166107'),
(2315, 210, 'hero_heading', 'Our guide to selling your home'),
(2316, 210, '_hero_heading', 'field_5eff48a05507c'),
(2317, 210, 'has_search_bar', '0'),
(2318, 210, '_has_search_bar', 'field_5eff48a65507d'),
(2319, 210, 'guide_steps_0_image', '121'),
(2320, 210, '_guide_steps_0_image', 'field_5f0879fa9a052'),
(2321, 210, 'guide_steps_0_title', 'Make your home look its best'),
(2322, 210, '_guide_steps_0_title', 'field_5f087a079a053'),
(2323, 210, 'guide_steps_0_content', 'When you make the decision to sell your home, agents will establish pricing for the property. Think about whether you’d like to stay in the same area, too: we’ll be able to advise you on the market around the region.'),
(2324, 210, '_guide_steps_0_content', 'field_5f087a0b9a054'),
(2325, 210, 'guide_steps_0_is_highlighted', '0'),
(2326, 210, '_guide_steps_0_is_highlighted', 'field_5f087a179a055'),
(2327, 210, 'guide_steps_1_image', '119'),
(2328, 210, '_guide_steps_1_image', 'field_5f0879fa9a052'),
(2329, 210, 'guide_steps_1_title', 'Get your property valued'),
(2330, 210, '_guide_steps_1_title', 'field_5f087a079a053'),
(2331, 210, 'guide_steps_1_content', 'We recommend putting your house on the market before making an offer on a house you’d like to buy. Make sure your home is looking its absolute best so you can attract potential buyers.'),
(2332, 210, '_guide_steps_1_content', 'field_5f087a0b9a054'),
(2333, 210, 'guide_steps_1_is_highlighted', '0'),
(2334, 210, '_guide_steps_1_is_highlighted', 'field_5f087a179a055'),
(2335, 210, 'guide_steps_2_image', '67'),
(2336, 210, '_guide_steps_2_image', 'field_5f0879fa9a052'),
(2337, 210, 'guide_steps_2_title', 'Get the property on the market'),
(2338, 210, '_guide_steps_2_title', 'field_5f087a079a053'),
(2339, 210, 'guide_steps_2_content', 'Once we’ve determined the terms and conditions, we can then finalise the marketing approach.'),
(2340, 210, '_guide_steps_2_content', 'field_5f087a0b9a054'),
(2341, 210, 'guide_steps_2_is_highlighted', '0'),
(2342, 210, '_guide_steps_2_is_highlighted', 'field_5f087a179a055'),
(2343, 210, 'guide_steps_3_image', '121'),
(2344, 210, '_guide_steps_3_image', 'field_5f0879fa9a052'),
(2345, 210, 'guide_steps_3_title', 'Carry out viewings'),
(2346, 210, '_guide_steps_3_title', 'field_5f087a079a053'),
(2347, 210, 'guide_steps_3_content', 'We always advise the agent to carry out the viewings; people often feel more comfortable giving honest feedback to agents.'),
(2348, 210, '_guide_steps_3_content', 'field_5f087a0b9a054'),
(2349, 210, 'guide_steps_3_is_highlighted', '0'),
(2350, 210, '_guide_steps_3_is_highlighted', 'field_5f087a179a055'),
(2351, 210, 'guide_steps_4_image', '120'),
(2352, 210, '_guide_steps_4_image', 'field_5f0879fa9a052'),
(2353, 210, 'guide_steps_4_title', 'Review any feedback'),
(2354, 210, '_guide_steps_4_title', 'field_5f087a079a053'),
(2355, 210, 'guide_steps_4_content', 'Any feedback we receive will be passed on to you promptly.'),
(2356, 210, '_guide_steps_4_content', 'field_5f087a0b9a054'),
(2357, 210, 'guide_steps_4_is_highlighted', '0'),
(2358, 210, '_guide_steps_4_is_highlighted', 'field_5f087a179a055'),
(2359, 210, 'guide_steps_5_image', '119'),
(2360, 210, '_guide_steps_5_image', 'field_5f0879fa9a052'),
(2361, 210, 'guide_steps_5_title', 'Receive offers to buy'),
(2362, 210, '_guide_steps_5_title', 'field_5f087a079a053'),
(2363, 210, 'guide_steps_5_content', 'When an offer is received, the agent will check and vet the potential buyer, finance, and chain and discuss the next steps with the seller. We’ll work on your behalf to get you the best possible offer from the best possible buyer.'),
(2364, 210, '_guide_steps_5_content', 'field_5f087a0b9a054'),
(2365, 210, 'guide_steps_5_is_highlighted', '0'),
(2366, 210, '_guide_steps_5_is_highlighted', 'field_5f087a179a055'),
(2367, 210, 'guide_steps_6_image', '120'),
(2368, 210, '_guide_steps_6_image', 'field_5f0879fa9a052'),
(2369, 210, 'guide_steps_6_title', 'Accept an offer'),
(2370, 210, '_guide_steps_6_title', 'field_5f087a079a053'),
(2371, 210, 'guide_steps_6_content', 'When the offer is accepted, we’ll send out the memorandum of sale: this includes all relevant information regarding the sale.'),
(2372, 210, '_guide_steps_6_content', 'field_5f087a0b9a054'),
(2373, 210, 'guide_steps_6_is_highlighted', '0'),
(2374, 210, '_guide_steps_6_is_highlighted', 'field_5f087a179a055'),
(2375, 210, 'guide_steps_7_image', '122'),
(2376, 210, '_guide_steps_7_image', 'field_5f0879fa9a052'),
(2377, 210, 'guide_steps_7_title', 'Hire a solicitor'),
(2378, 210, '_guide_steps_7_title', 'field_5f087a079a053'),
(2379, 210, 'guide_steps_7_content', 'Solicitors will be instructed, and we will oversee the sale, communicating with all parties in the chain to make this process as smooth as possible.'),
(2380, 210, '_guide_steps_7_content', 'field_5f087a0b9a054'),
(2381, 210, 'guide_steps_7_is_highlighted', '0'),
(2382, 210, '_guide_steps_7_is_highlighted', 'field_5f087a179a055'),
(2383, 210, 'guide_steps_8_image', '67'),
(2384, 210, '_guide_steps_8_image', 'field_5f0879fa9a052'),
(2385, 210, 'guide_steps_8_title', 'Hire a removals company'),
(2386, 210, '_guide_steps_8_title', 'field_5f087a079a053'),
(2387, 210, 'guide_steps_8_content', 'Once the sale has been completed, it’s time to hire a removal company.'),
(2388, 210, '_guide_steps_8_content', 'field_5f087a0b9a054'),
(2389, 210, 'guide_steps_8_is_highlighted', '0'),
(2390, 210, '_guide_steps_8_is_highlighted', 'field_5f087a179a055'),
(2391, 210, 'guide_steps_9_image', '121'),
(2392, 210, '_guide_steps_9_image', 'field_5f0879fa9a052'),
(2393, 210, 'guide_steps_9_title', 'Exchange keys!'),
(2394, 210, '_guide_steps_9_title', 'field_5f087a079a053'),
(2395, 210, 'guide_steps_9_content', 'When the solicitor confirms the funds have been received, we can then hand over the keys to the property.'),
(2396, 210, '_guide_steps_9_content', 'field_5f087a0b9a054'),
(2397, 210, 'guide_steps_9_is_highlighted', '1'),
(2398, 210, '_guide_steps_9_is_highlighted', 'field_5f087a179a055') ;
INSERT INTO `league_postmeta` ( `meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(2399, 210, 'guide_steps', '10'),
(2400, 210, '_guide_steps', 'field_5f0879ee9a051'),
(2401, 210, 'h1', ''),
(2402, 210, '_h1', 'field_5f04e1b0f6df1'),
(2403, 210, 'show_why_choose_us', '0'),
(2404, 210, '_show_why_choose_us', 'field_5f04ecb34e432'),
(2405, 210, 'show_buckets', '0'),
(2406, 210, '_show_buckets', 'field_5f04ecbe4e433'),
(2407, 210, 'show_team', '1'),
(2408, 210, '_show_team', 'field_5f04ed2a64341'),
(2409, 210, 'sidebar_logos', ''),
(2410, 210, '_sidebar_logos', 'field_5f09cd7166107'),
(2411, 211, '_wp_attached_file', '2020/07/logo-update-v9.svg'),
(2412, 212, 'hero_top_line', 'Start your journey with Fullbrook & Floor'),
(2413, 212, '_hero_top_line', 'field_5eff38fcccfce'),
(2414, 212, 'hero_main_line', 'Local Estate agents in St. Albans'),
(2415, 212, '_hero_main_line', 'field_5eff3910ccfcf'),
(2416, 212, 'h1', 'Estate agents in St. Albans'),
(2417, 212, '_h1', 'field_5f04a835c98a9'),
(2418, 212, 'hero_carousel', 'a:3:{i:0;s:2:"67";i:1;s:3:"122";i:2;s:3:"120";}'),
(2419, 212, '_hero_carousel', 'field_5f0f32444af92'),
(2420, 213, 'hero_top_line', 'Start your journey with Fullbrook & Floor'),
(2421, 213, '_hero_top_line', 'field_5eff38fcccfce'),
(2422, 213, 'hero_main_line', 'Experienced local estate agents in St. Albans'),
(2423, 213, '_hero_main_line', 'field_5eff3910ccfcf'),
(2424, 213, 'h1', 'Estate agents in St. Albans'),
(2425, 213, '_h1', 'field_5f04a835c98a9'),
(2426, 213, 'hero_carousel', 'a:3:{i:0;s:2:"67";i:1;s:3:"122";i:2;s:3:"120";}'),
(2427, 213, '_hero_carousel', 'field_5f0f32444af92') ;

#
# End of data contents of table `league_postmeta`
# --------------------------------------------------------



#
# Delete any existing table `league_posts`
#

DROP TABLE IF EXISTS `league_posts`;


#
# Table structure of table `league_posts`
#

CREATE TABLE `league_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `guid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=MyISAM AUTO_INCREMENT=214 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_posts`
#
INSERT INTO `league_posts` ( `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(1, 1, '2020-07-02 18:33:39', '2020-07-02 17:33:39', '<!-- wp:paragraph -->\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n<!-- /wp:paragraph -->', 'Hello world!', '', 'publish', 'open', 'open', '', 'hello-world', '', '', '2020-07-02 18:33:39', '2020-07-02 17:33:39', '', 0, 'http://fullbrook-floor.vm/?p=1', 0, 'post', '', 1),
(2, 1, '2020-07-02 18:33:39', '2020-07-02 17:33:39', '<!-- wp:paragraph -->\n<p>This is an example page. It\'s different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class="wp-block-quote"><p>Hi there! I\'m a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin\' caught in the rain.)</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>...or something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class="wp-block-quote"><p>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>As a new WordPress user, you should go to <a href="http://fullbrook-floor.vm/wp-admin/">your dashboard</a> to delete this page and create new pages for your content. Have fun!</p>\n<!-- /wp:paragraph -->', 'Sample Page', '', 'trash', 'closed', 'open', '', 'sample-page__trashed', '', '', '2020-07-02 19:10:01', '2020-07-02 18:10:01', '', 0, 'http://fullbrook-floor.vm/?page_id=2', 0, 'page', '', 0),
(3, 1, '2020-07-02 18:33:39', '2020-07-02 17:33:39', '<!-- wp:heading --><h2>Who we are</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Our website address is: http://fullbrook-floor.vm.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What personal data we collect and why we collect it</h2><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3>Comments</h3><!-- /wp:heading --><!-- wp:paragraph --><p>When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor&#8217;s IP address and browser user agent string to help spam detection.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>An anonymised string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service Privacy Policy is available here: https://automattic.com/privacy/. After approval of your comment, your profile picture is visible to the public in the context of your comment.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Media</h3><!-- /wp:heading --><!-- wp:paragraph --><p>If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Contact forms</h3><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3>Cookies</h3><!-- /wp:heading --><!-- wp:paragraph --><p>If you leave a comment on our site you may opt in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select &quot;Remember Me&quot;, your login will persist for two weeks. If you log out of your account, the login cookies will be removed.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Embedded content from other websites</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Analytics</h3><!-- /wp:heading --><!-- wp:heading --><h2>Who we share your data with</h2><!-- /wp:heading --><!-- wp:heading --><h2>How long we retain your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p>If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognise and approve any follow-up comments automatically instead of holding them in a moderation queue.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What rights you have over your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p>If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Where we send your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Visitor comments may be checked through an automated spam detection service.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Your contact information</h2><!-- /wp:heading --><!-- wp:heading --><h2>Additional information</h2><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3>How we protect your data</h3><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3>What data breach procedures we have in place</h3><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3>What third parties we receive data from</h3><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3>What automated decision making and/or profiling we do with user data</h3><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3>Industry regulatory disclosure requirements</h3><!-- /wp:heading -->', 'Privacy Policy', '', 'draft', 'closed', 'open', '', 'privacy-policy', '', '', '2020-07-02 18:33:39', '2020-07-02 17:33:39', '', 0, 'http://fullbrook-floor.vm/?page_id=3', 0, 'page', '', 0),
(5, 0, '2020-07-02 17:34:30', '0000-00-00 00:00:00', '<h2>Cookies</h2> <p>By using the website of you consent to the usage of data captured by the use of cookies. Cookies allow us to do multiple things to enhance and improve your browsing experience on our website. If you wish to turn off cookies, please adjust your browser settings. Our website will continue to function without cookies.</p> <p>We use cookies to track visitors to our website; these details are in no way personal or identifiable details and will never be shared. Our cookies are for the sole purpose of improving the performance of our website for you, the user; this includes allowing us to geo-target our users, to make websites more personal and relevant to you.</p> <p><b>Below are the third party tools we use:</b></p> <h3>Google Analytics</h3> <p>Page views, source and time spent on website are part of the user website activities information we can see with this cookie. This information cannot be tracked back to any individuals as it is displayed as depersonalised numbers; this is in order to help protect your privacy whilst using our website.</p> <p>Using Google Analytics we can take account of which content is popular, helping us to provide you with reading and viewing materials which you will enjoy and find useful in the future.</p> <p>We also use Google Analytics Remarketing cookies to display adverts on third party websites to our past site users, based on their past visits. The data we collect will only be used in accordance with our own privacy policy and <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/cookie-usage">Google’s privacy policy</a>.</p> <p>Should you not wish for your website visits to be recorded by Google Analytics, you are able to opt-out with the addition of a browser add-on: <a href="https://tools.google.com/dlpage/gaoptout/">Google Analytics Opt-out Browser Add-on</a></p> <h3>Google Analytics Advertiser</h3> <p>We use Google Analytics Advertiser Features, which helps us to better understand site visitors, via anonymised data. This can include collecting information from:</p> <ul> <li>Google Display Network Impression Reporting</li> <li>DoubleClick Platform integrations</li> <li>Google Analytics Demographics and Interest Reporting</li> <li>Remarketing with Google Analytics</li> </ul> <p>This information is collected via Google advertising cookies and anonymous identifiers, in addition to data collected through the standard Google Analytics implementation. It allows us to understand what type of users visit the site, which then allows us to improve the website’s offerings for a better user experience.</p> <h3>Google AdWords</h3> <p>We use Google AdWords to see which pages led to our users submitting contact forms to us, which allows us to create a more effective marketing campaign, and make better use of our paid search budget.</p> <h3>DoubleClick</h3> <p>We use DoubleClick cookies and remarketing codes on our website to record user activity. The information we collect allows us to create targeted advertising in future work and across Google’s network of partners.</p> <h3>Website Optimiser</h3> <p>Our website optimiser uses cookies to remember your search history. The information collected is anonymous and not personally identifiable, and allows us to generate more relevant results for your searches in the future.</p><h3>Call Tracking</h3><p>We use Call Tracking to set dynamic phone numbers on our site. These help us identify how you found the website when you call us and allows us to identify the source that you used to find the website. It gives a better idea of our users’ requirements and lets us tailor our advertising methods in the future.&nbsp;If you phone us, your call may be recorded for training and quality purposes.</p><h3>Visitor Tracking</h3><p>We often record and monitor user’s behaviour around a website&nbsp;to analyse how we can improve its&nbsp;performance.</p>', 'Cookie Policy', '', 'draft', 'closed', 'closed', '', 'cookie-policy', '', '', '2020-07-02 17:34:30', '0000-00-00 00:00:00', '', 0, 'http://fullbrook-floor.vm/?page_id=5', 0, 'page', '', 0),
(6, 1, '2020-07-02 18:58:24', '2020-07-02 17:58:24', 'With a combined 40 years of experience and an unrivalled knowledge of St. Albans and the surrounding area, Rod Fullbrook and René Floor are here to help you sell your home. With over three decades of experience in the industry, we’ve guided thousands of clients through the moving process, making it as simple as possible from start to finish.\r\n\r\nWe’ve set up our own agency because we know we can improve the selling experience for the people of St. Albans. Whether it’s your first move or your fifth, we’ll assist you with every step of your moving journey, from finding the right buyer at the start to handing you the keys upon completion. Our commitment to our clients is total - long after your move, you can come to us for expert advice.\r\n\r\nBy selling with Fullbrook &amp; Floor, you’ll get the best possible buyer and the best possible price for your home. We work for you - we’re driven by a determination to achieve the very best for our clients across St. Albans, Chiswell Green, Brickett Wood, Park Street and the surrounding areas. Talk to us today to hear more from the best independent estate agents in St. Albans.', 'Home', '', 'publish', 'closed', 'closed', '', 'home', '', '', '2020-07-22 17:26:01', '2020-07-22 16:26:01', '', 0, 'http://fullbrook-floor.vm/?page_id=6', 0, 'page', '', 0),
(7, 1, '2020-07-02 18:58:24', '2020-07-02 17:58:24', '', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-02 18:58:24', '2020-07-02 17:58:24', '', 6, 'http://fullbrook-floor.vm/2020/07/02/6-revision-v1/', 0, 'revision', '', 0),
(8, 1, '2020-07-02 18:58:37', '2020-07-02 17:58:37', 'We offer a wide range of quality properties in St. Albans and the surrounding villages, including Chiswell Green, Brickett Wood and Park Street. If you’re looking for your next home, we can help you find it. Click here to browse houses for sale in the area.', 'Buy a home', '', 'publish', 'closed', 'closed', '', 'buy-a-home', '', '', '2020-07-22 17:02:45', '2020-07-22 16:02:45', '', 0, 'http://fullbrook-floor.vm/?page_id=8', 0, 'page', '', 0),
(9, 1, '2020-07-02 18:58:37', '2020-07-02 17:58:37', '', 'Buy a home', '', 'inherit', 'closed', 'closed', '', '8-revision-v1', '', '', '2020-07-02 18:58:37', '2020-07-02 17:58:37', '', 8, 'http://fullbrook-floor.vm/2020/07/02/8-revision-v1/', 0, 'revision', '', 0),
(10, 1, '2020-07-02 18:58:51', '2020-07-02 17:58:51', 'Selling your home is a big decision, and you need people you can trust to facilitate it. Whether it’s your first selling experience or you’ve sold a home before, Fullbrook &amp; Floor will be with you at every turn, ensuring you get the best possible price for your home and that your entire moving process is as stress-free as possible. From valuing your property to overseeing the house sale, let us do the hard work while you focus on your future. Contact us today for expert advice on selling a property in St. Albans - we help clients across Park Street, Chiswell Green, Brickett Wood and beyond.', 'Sell your home', '', 'publish', 'closed', 'closed', '', 'sell-your-home', '', '', '2020-07-22 17:02:59', '2020-07-22 16:02:59', '', 0, 'http://fullbrook-floor.vm/?page_id=10', 0, 'page', '', 0),
(11, 1, '2020-07-02 18:58:51', '2020-07-02 17:58:51', '', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-02 18:58:51', '2020-07-02 17:58:51', '', 10, 'http://fullbrook-floor.vm/2020/07/02/10-revision-v1/', 0, 'revision', '', 0),
(12, 1, '2020-07-02 18:59:07', '2020-07-02 17:59:07', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Free sales valuations', '', 'publish', 'closed', 'closed', '', 'free-sales-valuations', '', '', '2020-07-07 21:56:25', '2020-07-07 20:56:25', '', 10, 'http://fullbrook-floor.vm/?page_id=12', 0, 'page', '', 0),
(13, 1, '2020-07-02 18:59:07', '2020-07-02 17:59:07', '', 'Free sales valuations', '', 'inherit', 'closed', 'closed', '', '12-revision-v1', '', '', '2020-07-02 18:59:07', '2020-07-02 17:59:07', '', 12, 'http://fullbrook-floor.vm/2020/07/02/12-revision-v1/', 0, 'revision', '', 0),
(14, 1, '2020-07-02 18:59:26', '2020-07-02 17:59:26', 'After working for decades for an established, reputable estate agents in St. Albans, we have branched out into our own agency to offer an enhanced experience for home buyers and sellers in the area.\r\n\r\nWorking together instead of as individuals means that we offer a greater level of flexibility and a personal touch that cannot be matched - you’ll be supported, advised and guided through every step of the moving process by experts. You’ll deal with us and only us - you can place complete faith in our integrity and our knowledge as we strive to get you the best possible price for your home.\r\n\r\nWe’re determined, but we’re not pushy; we want what’s best for you, not what’s best for us. Through our work in the St. Albans area, we’ve helped thousands of people move, getting the best prices and supporting people on their journey to their dream home. We’re deeply connected and committed to our community, and it’s this connection that makes us the go-to people for moving home in St. Albans.', 'About us', '', 'publish', 'closed', 'closed', '', 'about-us', '', '', '2020-07-22 17:01:42', '2020-07-22 16:01:42', '', 0, 'http://fullbrook-floor.vm/?page_id=14', 0, 'page', '', 0),
(15, 1, '2020-07-02 18:59:26', '2020-07-02 17:59:26', '', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-02 18:59:26', '2020-07-02 17:59:26', '', 14, 'http://fullbrook-floor.vm/2020/07/02/14-revision-v1/', 0, 'revision', '', 0),
(16, 1, '2020-07-02 18:59:40', '2020-07-02 17:59:40', '', 'Meet Rene & Rod', '', 'publish', 'closed', 'closed', '', 'meet-the-team', '', '', '2020-07-22 17:02:36', '2020-07-22 16:02:36', '', 14, 'http://fullbrook-floor.vm/?page_id=16', 0, 'page', '', 0),
(17, 1, '2020-07-02 18:59:40', '2020-07-02 17:59:40', '', 'Meet the team', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-02 18:59:40', '2020-07-02 17:59:40', '', 16, 'http://fullbrook-floor.vm/2020/07/02/16-revision-v1/', 0, 'revision', '', 0),
(18, 1, '2020-07-02 19:00:13', '2020-07-02 18:00:13', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'publish', 'closed', 'closed', '', 'how-to-sell-a-home', '', '', '2020-07-22 17:04:43', '2020-07-22 16:04:43', '', 0, 'http://fullbrook-floor.vm/?page_id=18', 0, 'page', '', 0),
(19, 1, '2020-07-02 19:00:13', '2020-07-02 18:00:13', '', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-02 19:00:13', '2020-07-02 18:00:13', '', 18, 'http://fullbrook-floor.vm/2020/07/02/18-revision-v1/', 0, 'revision', '', 0),
(20, 1, '2020-07-02 19:00:27', '2020-07-02 18:00:27', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin neque neque, suscipit eget fringilla sit amet, fermentum at lacus. Etiam et ligula gravida, pulvinar diam eget, congue magna.\r\n\r\n[ninja_form id=1]\r\n\r\n&nbsp;', 'Contact Us', '', 'publish', 'closed', 'closed', '', 'contact-us', '', '', '2020-07-11 13:04:28', '2020-07-11 12:04:28', '', 0, 'http://fullbrook-floor.vm/?page_id=20', 0, 'page', '', 0),
(21, 1, '2020-07-02 19:00:27', '2020-07-02 18:00:27', '', 'Contact Us', '', 'inherit', 'closed', 'closed', '', '20-revision-v1', '', '', '2020-07-02 19:00:27', '2020-07-02 18:00:27', '', 20, 'http://fullbrook-floor.vm/2020/07/02/20-revision-v1/', 0, 'revision', '', 0),
(22, 1, '2020-07-02 19:10:01', '2020-07-02 18:10:01', '<!-- wp:paragraph -->\n<p>This is an example page. It\'s different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class="wp-block-quote"><p>Hi there! I\'m a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin\' caught in the rain.)</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>...or something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class="wp-block-quote"><p>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>As a new WordPress user, you should go to <a href="http://fullbrook-floor.vm/wp-admin/">your dashboard</a> to delete this page and create new pages for your content. Have fun!</p>\n<!-- /wp:paragraph -->', 'Sample Page', '', 'inherit', 'closed', 'closed', '', '2-revision-v1', '', '', '2020-07-02 19:10:01', '2020-07-02 18:10:01', '', 2, 'http://fullbrook-floor.vm/2-revision-v1/', 0, 'revision', '', 0),
(23, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', ' ', '', '', 'publish', 'closed', 'closed', '', '23', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 0, 'http://fullbrook-floor.vm/?p=23', 1, 'nav_menu_item', '', 0),
(24, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', ' ', '', '', 'publish', 'closed', 'closed', '', '24', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 0, 'http://fullbrook-floor.vm/?p=24', 6, 'nav_menu_item', '', 0),
(25, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', ' ', '', '', 'publish', 'closed', 'closed', '', '25', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 14, 'http://fullbrook-floor.vm/?p=25', 7, 'nav_menu_item', '', 0),
(26, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', '', 'Buy with us', '', 'publish', 'closed', 'closed', '', '26', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 0, 'http://fullbrook-floor.vm/?p=26', 2, 'nav_menu_item', '', 0),
(27, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', ' ', '', '', 'publish', 'closed', 'closed', '', '27', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 0, 'http://fullbrook-floor.vm/?p=27', 9, 'nav_menu_item', '', 0),
(28, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', ' ', '', '', 'publish', 'closed', 'closed', '', '28', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 0, 'http://fullbrook-floor.vm/?p=28', 5, 'nav_menu_item', '', 0),
(29, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', '', 'Sell with us', '', 'publish', 'closed', 'closed', '', '29', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 0, 'http://fullbrook-floor.vm/?p=29', 3, 'nav_menu_item', '', 0),
(30, 1, '2020-07-02 19:10:41', '2020-07-02 18:10:41', ' ', '', '', 'publish', 'closed', 'closed', '', '30', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 10, 'http://fullbrook-floor.vm/?p=30', 4, 'nav_menu_item', '', 0),
(32, 1, '2020-07-02 22:23:59', '0000-00-00 00:00:00', '', 'Help & Advice', '', 'draft', 'closed', 'closed', '', '', '', '', '2020-07-02 22:23:59', '0000-00-00 00:00:00', '', 0, 'http://fullbrook-floor.vm/?p=32', 1, 'nav_menu_item', '', 0),
(33, 1, '2020-07-02 22:26:25', '2020-07-02 21:26:25', '', 'Help & Advice', '', 'publish', 'closed', 'closed', '', 'help-advice', '', '', '2020-07-15 18:06:41', '2020-07-15 17:06:41', '', 0, 'http://fullbrook-floor.vm/?p=33', 8, 'nav_menu_item', '', 0),
(36, 1, '2020-07-02 22:30:36', '2020-07-02 21:30:36', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice buying a home', '', 'publish', 'closed', 'closed', '', 'help-advice-buying-a-home', '', '', '2020-07-10 18:26:57', '2020-07-10 17:26:57', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=36', 0, 'help-advice', '', 0),
(37, 1, '2020-07-02 22:30:14', '2020-07-02 21:30:14', '', 'placeholder-images-image_large', '', 'inherit', 'closed', 'closed', '', 'placeholder-images-image_large', '', '', '2020-07-02 22:30:14', '2020-07-02 21:30:14', '', 36, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/placeholder-images-image_large.png', 0, 'attachment', 'image/png', 0),
(38, 1, '2020-07-02 22:31:18', '2020-07-02 21:31:18', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice finance', '', 'publish', 'closed', 'closed', '', 'help-advice-finance', '', '', '2020-07-10 18:26:49', '2020-07-10 17:26:49', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=38', 0, 'help-advice', '', 0),
(39, 1, '2020-07-02 22:31:23', '2020-07-02 21:31:23', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice housing market', '', 'publish', 'closed', 'closed', '', 'help-advice-housing-market', '', '', '2020-07-10 18:26:41', '2020-07-10 17:26:41', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=39', 0, 'help-advice', '', 0),
(40, 1, '2020-07-02 22:31:25', '2020-07-02 21:31:25', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-your-home', '', '', '2020-07-10 18:26:01', '2020-07-10 17:26:01', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=40', 0, 'help-advice', '', 0),
(41, 1, '2020-07-02 22:31:25', '2020-07-02 21:31:25', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice mortgages', '', 'publish', 'closed', 'closed', '', 'help-advice-mortgages', '', '', '2020-07-10 18:26:14', '2020-07-10 17:26:14', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=41', 0, 'help-advice', '', 0),
(42, 1, '2020-07-02 22:31:25', '2020-07-02 21:31:25', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice lettings', '', 'publish', 'closed', 'closed', '', 'help-advice-lettings', '', '', '2020-07-10 18:26:24', '2020-07-10 17:26:24', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=42', 0, 'help-advice', '', 0),
(43, 1, '2020-07-02 22:31:25', '2020-07-02 21:31:25', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice landlords', '', 'publish', 'closed', 'closed', '', 'help-advice-landlords', '', '', '2020-07-10 18:26:34', '2020-07-10 17:26:34', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=43', 0, 'help-advice', '', 0),
(44, 1, '2020-07-02 22:31:26', '2020-07-02 21:31:26', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\n[caption id="attachment_122" align="aligncenter" width="1024"]<img class="wp-image-122 size-large" src="http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f-1024x683.jpg" alt="" width="1024" height="683" /> this is a caption of the image in question[/caption]\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling you home 4', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-you-home-4', '', '', '2020-07-11 14:23:07', '2020-07-11 13:23:07', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=44', 0, 'help-advice', '', 0),
(45, 1, '2020-07-02 22:31:26', '2020-07-02 21:31:26', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home 3', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-your-home-3', '', '', '2020-07-10 18:25:01', '2020-07-10 17:25:01', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=45', 0, 'help-advice', '', 0) ;
INSERT INTO `league_posts` ( `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(46, 1, '2020-07-02 22:31:26', '2020-07-02 21:31:26', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home 2', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-your-home-2', '', '', '2020-07-10 18:25:54', '2020-07-10 17:25:54', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=46', 0, 'help-advice', '', 0),
(47, 1, '2020-07-03 14:23:09', '2020-07-03 13:23:09', 'a:7:{s:8:"location";a:1:{i:0;a:1:{i:0;a:3:{s:5:"param";s:12:"options_page";s:8:"operator";s:2:"==";s:5:"value";s:12:"site-options";}}}s:8:"position";s:6:"normal";s:5:"style";s:7:"default";s:15:"label_placement";s:3:"top";s:21:"instruction_placement";s:5:"label";s:14:"hide_on_screen";s:0:"";s:11:"description";s:0:"";}', 'Additional Site Options', 'additional-site-options', 'publish', 'closed', 'closed', '', 'group_5eff30f8bf9fa', '', '', '2020-07-15 17:58:43', '2020-07-15 16:58:43', '', 0, 'http://fullbrook-floor.vm/?post_type=acf-field-group&#038;p=47', 0, 'acf-field-group', '', 0),
(48, 1, '2020-07-03 14:23:10', '2020-07-03 13:23:10', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Logo Options', 'logo_options', 'publish', 'closed', 'closed', '', 'field_5eff310c23484', '', '', '2020-07-03 14:23:10', '2020-07-03 13:23:10', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&p=48', 0, 'acf-field', '', 0),
(49, 1, '2020-07-03 14:23:10', '2020-07-03 13:23:10', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Logo Horizontal', 'logo_horizontal', 'publish', 'closed', 'closed', '', 'field_5eff30fe23483', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=49', 1, 'acf-field', '', 0),
(50, 1, '2020-07-03 14:23:10', '2020-07-03 13:23:10', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Logo Vertical', 'logo_vertical', 'publish', 'closed', 'closed', '', 'field_5eff311623485', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=50', 2, 'acf-field', '', 0),
(51, 1, '2020-07-03 14:23:10', '2020-07-03 13:23:10', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Logo Horizontal White', 'logo_horizontal_white', 'publish', 'closed', 'closed', '', 'field_5eff312323486', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=51', 3, 'acf-field', '', 0),
(52, 1, '2020-07-03 14:23:10', '2020-07-03 13:23:10', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Logo Vertical White', 'logo_vertical_white', 'publish', 'closed', 'closed', '', 'field_5eff312e23487', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=52', 4, 'acf-field', '', 0),
(57, 1, '2020-07-03 14:30:38', '2020-07-03 13:30:38', '', 'logo-vertical', '', 'inherit', 'closed', 'closed', '', 'logo-vertical', '', '', '2020-07-07 22:11:30', '2020-07-07 21:11:30', '', 10, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-vertical.svg', 0, 'attachment', 'image/svg+xml', 0),
(58, 1, '2020-07-03 14:31:04', '2020-07-03 13:31:04', '', 'logo-vertical-white', '', 'inherit', 'closed', 'closed', '', 'logo-vertical-white', '', '', '2020-07-03 14:31:04', '2020-07-03 13:31:04', '', 0, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-vertical-white.svg', 0, 'attachment', 'image/svg+xml', 0),
(59, 1, '2020-07-03 14:32:31', '2020-07-03 13:32:31', '', 'logo-horizontal', '', 'inherit', 'closed', 'closed', '', 'logo-horizontal', '', '', '2020-07-03 14:32:31', '2020-07-03 13:32:31', '', 0, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-horizontal.svg', 0, 'attachment', 'image/svg+xml', 0),
(60, 1, '2020-07-03 14:32:32', '2020-07-03 13:32:32', '', 'logo-horizontal-white', '', 'inherit', 'closed', 'closed', '', 'logo-horizontal-white', '', '', '2020-07-03 14:32:32', '2020-07-03 13:32:32', '', 0, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-horizontal-white.svg', 0, 'attachment', 'image/svg+xml', 0),
(61, 1, '2020-07-03 14:58:19', '2020-07-03 13:58:19', 'a:7:{s:8:"location";a:1:{i:0;a:1:{i:0;a:3:{s:5:"param";s:4:"page";s:8:"operator";s:2:"==";s:5:"value";s:1:"6";}}}s:8:"position";s:15:"acf_after_title";s:5:"style";s:7:"default";s:15:"label_placement";s:3:"top";s:21:"instruction_placement";s:5:"label";s:14:"hide_on_screen";s:0:"";s:11:"description";s:0:"";}', 'Homepage Options', 'homepage-options', 'publish', 'closed', 'closed', '', 'group_5eff38f5283df', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 0, 'http://fullbrook-floor.vm/?post_type=acf-field-group&#038;p=61', 0, 'acf-field-group', '', 0),
(62, 1, '2020-07-03 14:58:19', '2020-07-03 13:58:19', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Hero Message', 'hero_message_', 'publish', 'closed', 'closed', '', 'field_5eff3931ccfd1', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 61, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=62', 2, 'acf-field', '', 0),
(63, 1, '2020-07-03 14:58:19', '2020-07-03 13:58:19', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Hero Top Line', 'hero_top_line', 'publish', 'closed', 'closed', '', 'field_5eff38fcccfce', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 61, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=63', 3, 'acf-field', '', 0),
(64, 1, '2020-07-03 14:58:19', '2020-07-03 13:58:19', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Hero Main Line', 'hero_main_line', 'publish', 'closed', 'closed', '', 'field_5eff3910ccfcf', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 61, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=64', 4, 'acf-field', '', 0),
(65, 1, '2020-07-03 14:58:51', '2020-07-03 13:58:51', '', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-03 14:58:51', '2020-07-03 13:58:51', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0),
(66, 1, '2020-07-03 14:59:33', '2020-07-03 13:59:33', '', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-03 14:59:33', '2020-07-03 13:59:33', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0),
(67, 1, '2020-07-03 15:07:48', '2020-07-03 14:07:48', '', 'istockphoto-1225367483-1024x1024', 'An aerial photo of the Cathedral &amp; City of St Albans in Hertfordshire, England.  \n\nShot during the Coronavirus pandemic.', 'inherit', 'closed', 'closed', '', 'istockphoto-1225367483-1024x1024', '', '', '2020-07-03 15:07:48', '2020-07-03 14:07:48', '', 6, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', 0, 'attachment', 'image/jpeg', 0),
(68, 1, '2020-07-03 16:03:30', '2020-07-03 15:03:30', 'a:7:{s:8:"location";a:2:{i:0;a:2:{i:0;a:3:{s:5:"param";s:9:"post_type";s:8:"operator";s:2:"==";s:5:"value";s:4:"page";}i:1;a:3:{s:5:"param";s:4:"page";s:8:"operator";s:2:"!=";s:5:"value";s:1:"6";}}i:1;a:1:{i:0;a:3:{s:5:"param";s:9:"post_type";s:8:"operator";s:2:"==";s:5:"value";s:11:"help-advice";}}}s:8:"position";s:15:"acf_after_title";s:5:"style";s:7:"default";s:15:"label_placement";s:3:"top";s:21:"instruction_placement";s:5:"label";s:14:"hide_on_screen";s:0:"";s:11:"description";s:0:"";}', 'Page Options', 'page-options', 'publish', 'closed', 'closed', '', 'group_5eff4889c7630', '', '', '2020-07-11 15:33:12', '2020-07-11 14:33:12', '', 0, 'http://fullbrook-floor.vm/?post_type=acf-field-group&#038;p=68', 0, 'acf-field-group', '', 0),
(69, 1, '2020-07-03 16:03:30', '2020-07-03 15:03:30', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Hero', 'hero', 'publish', 'closed', 'closed', '', 'field_5eff488d5507b', '', '', '2020-07-03 16:03:30', '2020-07-03 15:03:30', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=69', 0, 'acf-field', '', 0),
(70, 1, '2020-07-03 16:03:30', '2020-07-03 15:03:30', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Hero Heading', 'hero_heading', 'publish', 'closed', 'closed', '', 'field_5eff48a05507c', '', '', '2020-07-03 16:03:30', '2020-07-03 15:03:30', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=70', 1, 'acf-field', '', 0),
(71, 1, '2020-07-03 16:03:30', '2020-07-03 15:03:30', 'a:10:{s:4:"type";s:10:"true_false";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:7:"message";s:0:"";s:13:"default_value";i:0;s:2:"ui";i:1;s:10:"ui_on_text";s:0:"";s:11:"ui_off_text";s:0:"";}', 'Has Search Bar', 'has_search_bar', 'publish', 'closed', 'closed', '', 'field_5eff48a65507d', '', '', '2020-07-03 16:03:30', '2020-07-03 15:03:30', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=71', 2, 'acf-field', '', 0),
(72, 1, '2020-07-03 16:04:11', '2020-07-03 15:04:11', '', 'Buy a home', '', 'inherit', 'closed', 'closed', '', '8-revision-v1', '', '', '2020-07-03 16:04:11', '2020-07-03 15:04:11', '', 8, 'http://fullbrook-floor.vm/8-revision-v1/', 0, 'revision', '', 0),
(73, 1, '2020-07-03 16:16:34', '2020-07-03 15:16:34', '', 'Contact Us', '', 'inherit', 'closed', 'closed', '', '20-autosave-v1', '', '', '2020-07-03 16:16:34', '2020-07-03 15:16:34', '', 20, 'http://fullbrook-floor.vm/20-autosave-v1/', 0, 'revision', '', 0),
(76, 1, '2020-07-03 16:16:55', '2020-07-03 15:16:55', '', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-03 16:16:55', '2020-07-03 15:16:55', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(77, 1, '2020-07-03 16:17:14', '2020-07-03 15:17:14', '', 'Free sales valuations', '', 'inherit', 'closed', 'closed', '', '12-revision-v1', '', '', '2020-07-03 16:17:14', '2020-07-03 15:17:14', '', 12, 'http://fullbrook-floor.vm/12-revision-v1/', 0, 'revision', '', 0),
(78, 1, '2020-07-03 16:17:25', '2020-07-03 15:17:25', '', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-03 16:17:25', '2020-07-03 15:17:25', '', 14, 'http://fullbrook-floor.vm/14-revision-v1/', 0, 'revision', '', 0),
(79, 1, '2020-07-03 16:17:35', '2020-07-03 15:17:35', '', 'Meet the team', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-03 16:17:35', '2020-07-03 15:17:35', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(80, 1, '2020-07-03 16:17:45', '2020-07-03 15:17:45', '', 'Contact Us', '', 'inherit', 'closed', 'closed', '', '20-revision-v1', '', '', '2020-07-03 16:17:45', '2020-07-03 15:17:45', '', 20, 'http://fullbrook-floor.vm/20-revision-v1/', 0, 'revision', '', 0),
(81, 1, '2020-07-03 16:18:00', '2020-07-03 15:18:00', '', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-03 16:18:00', '2020-07-03 15:18:00', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(82, 1, '2020-07-03 16:19:22', '2020-07-03 15:19:22', '', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-03 16:19:22', '2020-07-03 15:19:22', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(83, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:7:{s:8:"location";a:1:{i:0;a:1:{i:0;a:3:{s:5:"param";s:4:"page";s:8:"operator";s:2:"==";s:5:"value";s:2:"16";}}}s:8:"position";s:15:"acf_after_title";s:5:"style";s:7:"default";s:15:"label_placement";s:3:"top";s:21:"instruction_placement";s:5:"label";s:14:"hide_on_screen";a:2:{i:0;s:11:"the_content";i:1;s:14:"featured_image";}s:11:"description";s:0:"";}', 'Meet the team options', 'meet-the-team-options', 'publish', 'closed', 'closed', '', 'group_5eff532b17dbb', '', '', '2020-07-15 18:19:01', '2020-07-15 17:19:01', '', 0, 'http://fullbrook-floor.vm/?post_type=acf-field-group&#038;p=83', 0, 'acf-field-group', '', 0),
(84, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:10:{s:4:"type";s:8:"repeater";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"collapsed";s:0:"";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"layout";s:5:"block";s:12:"button_label";s:15:"Add team member";}', 'Team Members', 'team_members', 'publish', 'closed', 'closed', '', 'field_5eff533075de0', '', '', '2020-07-03 16:50:07', '2020-07-03 15:50:07', '', 83, 'http://fullbrook-floor.vm/?post_type=acf-field&p=84', 0, 'acf-field', '', 0),
(85, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Name', 'name', 'publish', 'closed', 'closed', '', 'field_5eff534e75de2', '', '', '2020-07-15 18:19:00', '2020-07-15 17:19:00', '', 84, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=85', 0, 'acf-field', '', 0),
(86, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Job Title', 'job_title', 'publish', 'closed', 'closed', '', 'field_5eff535575de3', '', '', '2020-07-15 18:19:00', '2020-07-15 17:19:00', '', 84, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=86', 1, 'acf-field', '', 0),
(87, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Phone Number', 'phone_number', 'publish', 'closed', 'closed', '', 'field_5eff535c75de4', '', '', '2020-07-15 18:18:28', '2020-07-15 17:18:28', '', 84, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=87', 2, 'acf-field', '', 0),
(88, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:9:{s:4:"type";s:5:"email";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";}', 'Email address', 'email_address', 'publish', 'closed', 'closed', '', 'field_5eff536475de5', '', '', '2020-07-15 18:18:28', '2020-07-15 17:18:28', '', 84, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=88', 3, 'acf-field', '', 0),
(89, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Profile Photo', 'profile_photo', 'publish', 'closed', 'closed', '', 'field_5eff534275de1', '', '', '2020-07-15 18:18:28', '2020-07-15 17:18:28', '', 84, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=89', 4, 'acf-field', '', 0),
(90, 1, '2020-07-03 16:50:07', '2020-07-03 15:50:07', 'a:10:{s:4:"type";s:7:"wysiwyg";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:4:"tabs";s:3:"all";s:7:"toolbar";s:4:"full";s:12:"media_upload";i:1;s:5:"delay";i:0;}', 'Biography', 'biography', 'publish', 'closed', 'closed', '', 'field_5eff536f75de6', '', '', '2020-07-15 18:18:28', '2020-07-15 17:18:28', '', 84, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=90', 6, 'acf-field', '', 0),
(91, 1, '2020-07-03 16:51:12', '2020-07-03 15:51:12', '', 'profile-image', '', 'inherit', 'closed', 'closed', '', 'profile-image', '', '', '2020-07-03 16:51:12', '2020-07-03 15:51:12', '', 16, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/profile-image.png', 0, 'attachment', 'image/png', 0),
(92, 1, '2020-07-03 16:51:49', '2020-07-03 15:51:49', '', 'Meet the team', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-03 16:51:49', '2020-07-03 15:51:49', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(93, 1, '2020-07-03 18:46:47', '2020-07-03 17:46:47', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Buckets', 'buckets', 'publish', 'closed', 'closed', '', 'field_5eff6e9c57b81', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=93', 5, 'acf-field', '', 0),
(94, 1, '2020-07-03 18:46:47', '2020-07-03 17:46:47', 'a:10:{s:4:"type";s:8:"repeater";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"collapsed";s:0:"";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"layout";s:5:"block";s:12:"button_label";s:10:"Add bucket";}', 'Buckets', 'buckets', 'publish', 'closed', 'closed', '', 'field_5eff6ea657b82', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=94', 6, 'acf-field', '', 0),
(95, 1, '2020-07-03 18:46:47', '2020-07-03 17:46:47', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Title', 'title', 'publish', 'closed', 'closed', '', 'field_5eff6eb357b83', '', '', '2020-07-03 18:46:47', '2020-07-03 17:46:47', '', 94, 'http://fullbrook-floor.vm/?post_type=acf-field&p=95', 0, 'acf-field', '', 0),
(96, 1, '2020-07-03 18:46:47', '2020-07-03 17:46:47', 'a:10:{s:4:"type";s:7:"wysiwyg";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:4:"tabs";s:3:"all";s:7:"toolbar";s:4:"full";s:12:"media_upload";i:1;s:5:"delay";i:0;}', 'Intro', 'intro', 'publish', 'closed', 'closed', '', 'field_5eff6eb957b84', '', '', '2020-07-07 19:11:22', '2020-07-07 18:11:22', '', 94, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=96', 1, 'acf-field', '', 0),
(97, 1, '2020-07-03 18:46:47', '2020-07-03 17:46:47', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Background Image', 'background_image', 'publish', 'closed', 'closed', '', 'field_5eff6ec557b85', '', '', '2020-07-03 18:46:47', '2020-07-03 17:46:47', '', 94, 'http://fullbrook-floor.vm/?post_type=acf-field&p=97', 2, 'acf-field', '', 0),
(98, 1, '2020-07-03 18:46:47', '2020-07-03 17:46:47', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Button Text', 'button_text', 'publish', 'closed', 'closed', '', 'field_5eff6ee857b87', '', '', '2020-07-03 18:46:47', '2020-07-03 17:46:47', '', 94, 'http://fullbrook-floor.vm/?post_type=acf-field&p=98', 3, 'acf-field', '', 0),
(99, 1, '2020-07-03 18:46:47', '2020-07-03 17:46:47', 'a:6:{s:4:"type";s:4:"link";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";}', 'Button Link', 'button_link', 'publish', 'closed', 'closed', '', 'field_5eff6ed357b86', '', '', '2020-07-03 18:46:47', '2020-07-03 17:46:47', '', 94, 'http://fullbrook-floor.vm/?post_type=acf-field&p=99', 4, 'acf-field', '', 0),
(100, 1, '2020-07-07 17:51:26', '2020-07-07 16:51:26', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis fermentum sapien augue, efficitur commodo odio fringilla ac. Integer in diam diam. Nulla volutpat blandit dui a fermentum. Vestibulum condimentum suscipit erat sed placerat. In convallis egestas turpis ac tempus. Nunc sed risus arcu. Proin non sapien mollis, ultrices ligula nec, pellentesque risus. Nulla facilisi.\r\n\r\nMauris sit amet efficitur quam. Praesent tincidunt, lectus eu condimentum dignissim, magna lacus dapibus massa, quis faucibus metus leo quis enim. Quisque fringilla nisi non hendrerit laoreet. Vestibulum suscipit, elit id blandit consectetur, eros lorem faucibus turpis, eget molestie augue purus id magna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam mauris diam, interdum sed diam at, dictum volutpat leo. Ut eleifend neque a leo sollicitudin, nec luctus dui elementum. Maecenas lacinia laoreet erat, in consequat augue molestie non. Aliquam laoreet lobortis feugiat. Aliquam eu cursus ante, a pulvinar ligula. Fusce mollis a metus sit amet ultricies.', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-07 17:51:26', '2020-07-07 16:51:26', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0),
(101, 1, '2020-07-07 17:52:17', '2020-07-07 16:52:17', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'H1', 'h1', 'publish', 'closed', 'closed', '', 'field_5f04a82ec98a8', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 61, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=101', 5, 'acf-field', '', 0),
(102, 1, '2020-07-07 17:52:17', '2020-07-07 16:52:17', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'H1', 'h1', 'publish', 'closed', 'closed', '', 'field_5f04a835c98a9', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 61, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=102', 6, 'acf-field', '', 0),
(103, 1, '2020-07-07 17:52:32', '2020-07-07 16:52:32', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis fermentum sapien augue, efficitur commodo odio fringilla ac. Integer in diam diam. Nulla volutpat blandit dui a fermentum. Vestibulum condimentum suscipit erat sed placerat. In convallis egestas turpis ac tempus. Nunc sed risus arcu. Proin non sapien mollis, ultrices ligula nec, pellentesque risus. Nulla facilisi.\r\n\r\nMauris sit amet efficitur quam. Praesent tincidunt, lectus eu condimentum dignissim, magna lacus dapibus massa, quis faucibus metus leo quis enim. Quisque fringilla nisi non hendrerit laoreet. Vestibulum suscipit, elit id blandit consectetur, eros lorem faucibus turpis, eget molestie augue purus id magna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam mauris diam, interdum sed diam at, dictum volutpat leo. Ut eleifend neque a leo sollicitudin, nec luctus dui elementum. Maecenas lacinia laoreet erat, in consequat augue molestie non. Aliquam laoreet lobortis feugiat. Aliquam eu cursus ante, a pulvinar ligula. Fusce mollis a metus sit amet ultricies.', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-07 17:52:32', '2020-07-07 16:52:32', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0),
(104, 1, '2020-07-07 21:56:22', '2020-07-07 20:56:22', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 21:56:22', '2020-07-07 20:56:22', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(105, 1, '2020-07-07 21:56:25', '2020-07-07 20:56:25', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Free sales valuations', '', 'inherit', 'closed', 'closed', '', '12-revision-v1', '', '', '2020-07-07 21:56:25', '2020-07-07 20:56:25', '', 12, 'http://fullbrook-floor.vm/12-revision-v1/', 0, 'revision', '', 0),
(106, 1, '2020-07-07 21:56:28', '2020-07-07 20:56:28', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-07 21:56:28', '2020-07-07 20:56:28', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(107, 1, '2020-07-07 21:56:30', '2020-07-07 20:56:30', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Buy a home', '', 'inherit', 'closed', 'closed', '', '8-revision-v1', '', '', '2020-07-07 21:56:30', '2020-07-07 20:56:30', '', 8, 'http://fullbrook-floor.vm/8-revision-v1/', 0, 'revision', '', 0),
(108, 1, '2020-07-07 21:56:34', '2020-07-07 20:56:34', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-07 21:56:34', '2020-07-07 20:56:34', '', 14, 'http://fullbrook-floor.vm/14-revision-v1/', 0, 'revision', '', 0),
(109, 1, '2020-07-07 21:57:27', '2020-07-07 20:57:27', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'H1', 'h1', 'publish', 'closed', 'closed', '', 'field_5f04e1a8f6df0', '', '', '2020-07-07 21:57:27', '2020-07-07 20:57:27', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=109', 3, 'acf-field', '', 0),
(110, 1, '2020-07-07 21:57:27', '2020-07-07 20:57:27', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'H1', 'h1', 'publish', 'closed', 'closed', '', 'field_5f04e1b0f6df1', '', '', '2020-07-07 21:57:27', '2020-07-07 20:57:27', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=110', 4, 'acf-field', '', 0) ;
INSERT INTO `league_posts` ( `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(111, 1, '2020-07-07 21:58:04', '2020-07-07 20:58:04', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 21:58:04', '2020-07-07 20:58:04', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(112, 1, '2020-07-07 21:59:44', '2020-07-07 20:59:44', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 21:59:44', '2020-07-07 20:59:44', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(113, 1, '2020-07-07 22:09:59', '2020-07-07 21:09:59', 'a:7:{s:8:"location";a:1:{i:0;a:1:{i:0;a:3:{s:5:"param";s:4:"page";s:8:"operator";s:2:"==";s:5:"value";s:2:"10";}}}s:8:"position";s:6:"normal";s:5:"style";s:7:"default";s:15:"label_placement";s:3:"top";s:21:"instruction_placement";s:5:"label";s:14:"hide_on_screen";s:0:"";s:11:"description";s:0:"";}', 'Why Choose Us', 'why-choose-us', 'trash', 'closed', 'closed', '', 'group_5f04e460be7f0__trashed', '', '', '2020-07-07 22:48:06', '2020-07-07 21:48:06', '', 0, 'http://fullbrook-floor.vm/?post_type=acf-field-group&#038;p=113', 0, 'acf-field-group', '', 0),
(114, 1, '2020-07-07 22:09:59', '2020-07-07 21:09:59', 'a:10:{s:4:"type";s:8:"repeater";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"collapsed";s:0:"";s:3:"min";i:0;s:3:"max";i:0;s:6:"layout";s:5:"table";s:12:"button_label";s:10:"Add reason";}', 'Why choose us', 'why_choose_us', 'publish', 'closed', 'closed', '', 'field_5f04e465f4a3e', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=114', 10, 'acf-field', '', 0),
(115, 1, '2020-07-07 22:09:59', '2020-07-07 21:09:59', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Image', 'image', 'publish', 'closed', 'closed', '', 'field_5f04e471f4a3f', '', '', '2020-07-07 22:09:59', '2020-07-07 21:09:59', '', 114, 'http://fullbrook-floor.vm/?post_type=acf-field&p=115', 0, 'acf-field', '', 0),
(116, 1, '2020-07-07 22:09:59', '2020-07-07 21:09:59', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'TItle', 'title', 'publish', 'closed', 'closed', '', 'field_5f04e477f4a40', '', '', '2020-07-07 22:09:59', '2020-07-07 21:09:59', '', 114, 'http://fullbrook-floor.vm/?post_type=acf-field&p=116', 1, 'acf-field', '', 0),
(117, 1, '2020-07-07 22:10:00', '2020-07-07 21:10:00', 'a:10:{s:4:"type";s:7:"wysiwyg";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:4:"tabs";s:3:"all";s:7:"toolbar";s:4:"full";s:12:"media_upload";i:1;s:5:"delay";i:0;}', 'Text', 'text', 'publish', 'closed', 'closed', '', 'field_5f04e47cf4a41', '', '', '2020-07-07 22:10:00', '2020-07-07 21:10:00', '', 114, 'http://fullbrook-floor.vm/?post_type=acf-field&p=117', 2, 'acf-field', '', 0),
(118, 1, '2020-07-07 22:11:30', '2020-07-07 21:11:30', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 22:11:30', '2020-07-07 21:11:30', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(119, 1, '2020-07-07 22:18:42', '2020-07-07 21:18:42', '', 'Friends Arriving for Social Gathering', 'A point of view shot of a small group of friends arriving at a housewarming party, they have brought gifts for their host.', 'inherit', 'closed', 'closed', '', 'friends-arriving-for-social-gathering', '', '', '2020-07-10 15:58:33', '2020-07-10 14:58:33', '', 18, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', 0, 'attachment', 'image/jpeg', 0),
(120, 1, '2020-07-07 22:19:35', '2020-07-07 21:19:35', '', 'Young Family Collecting Keys To New Home From Realtor', 'Young Family Collecting Keys To New Home From Realtor', 'inherit', 'closed', 'closed', '', 'young-family-collecting-keys-to-new-home-from-realtor', '', '', '2020-07-15 17:47:59', '2020-07-15 16:47:59', '', 18, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852.jpg', 0, 'attachment', 'image/jpeg', 0),
(121, 1, '2020-07-07 22:20:18', '2020-07-07 21:20:18', '', 'Couple packing together', 'Closeup of a young couple using bubble wrap to pack their stuff in boxes before moving out', 'inherit', 'closed', 'closed', '', 'couple-packing-together', '', '', '2020-07-10 15:58:33', '2020-07-10 14:58:33', '', 18, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', 0, 'attachment', 'image/jpeg', 0),
(122, 1, '2020-07-07 22:26:05', '2020-07-07 21:26:05', '', '3122736571_f', '', 'inherit', 'closed', 'closed', '', '3122736571_f', '', '', '2020-07-10 15:58:33', '2020-07-10 14:58:33', '', 18, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', 0, 'attachment', 'image/jpeg', 0),
(123, 1, '2020-07-07 22:26:40', '2020-07-07 21:26:40', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Property Placeholder', 'property_placeholder', 'publish', 'closed', 'closed', '', 'field_5f04e883f1eff', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=123', 7, 'acf-field', '', 0),
(124, 1, '2020-07-07 22:26:40', '2020-07-07 21:26:40', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Property Placeholder', 'property_placeholder', 'publish', 'closed', 'closed', '', 'field_5f04e87af1efe', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=124', 8, 'acf-field', '', 0),
(125, 1, '2020-07-07 22:45:33', '2020-07-07 21:45:33', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Page Options', 'page_options', 'publish', 'closed', 'closed', '', 'field_5f04eca64e431', '', '', '2020-07-07 22:45:33', '2020-07-07 21:45:33', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=125', 5, 'acf-field', '', 0),
(126, 1, '2020-07-07 22:45:33', '2020-07-07 21:45:33', 'a:10:{s:4:"type";s:10:"true_false";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"33";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:7:"message";s:0:"";s:13:"default_value";i:0;s:2:"ui";i:1;s:10:"ui_on_text";s:0:"";s:11:"ui_off_text";s:0:"";}', 'Show Why Choose Us', 'show_why_choose_us', 'publish', 'closed', 'closed', '', 'field_5f04ecb34e432', '', '', '2020-07-07 22:46:53', '2020-07-07 21:46:53', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=126', 6, 'acf-field', '', 0),
(127, 1, '2020-07-07 22:45:33', '2020-07-07 21:45:33', 'a:10:{s:4:"type";s:10:"true_false";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"33";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:7:"message";s:0:"";s:13:"default_value";i:0;s:2:"ui";i:1;s:10:"ui_on_text";s:0:"";s:11:"ui_off_text";s:0:"";}', 'Show Buckets', 'show_buckets', 'publish', 'closed', 'closed', '', 'field_5f04ecbe4e433', '', '', '2020-07-07 22:46:53', '2020-07-07 21:46:53', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=127', 7, 'acf-field', '', 0),
(128, 1, '2020-07-07 22:45:47', '2020-07-07 21:45:47', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 22:45:47', '2020-07-07 21:45:47', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(129, 1, '2020-07-07 22:45:55', '2020-07-07 21:45:55', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 22:45:55', '2020-07-07 21:45:55', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(130, 1, '2020-07-07 22:46:53', '2020-07-07 21:46:53', 'a:10:{s:4:"type";s:10:"true_false";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"33";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:7:"message";s:0:"";s:13:"default_value";i:0;s:2:"ui";i:1;s:10:"ui_on_text";s:0:"";s:11:"ui_off_text";s:0:"";}', 'Show Team', 'show_team', 'publish', 'closed', 'closed', '', 'field_5f04ed2a64341', '', '', '2020-07-07 22:46:53', '2020-07-07 21:46:53', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=130', 8, 'acf-field', '', 0),
(131, 1, '2020-07-07 22:47:42', '2020-07-07 21:47:42', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 22:47:42', '2020-07-07 21:47:42', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(132, 1, '2020-07-07 22:48:36', '2020-07-07 21:48:36', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Why Choose Us', 'why_choose_us', 'publish', 'closed', 'closed', '', 'field_5f04ed9b788c3', '', '', '2020-07-07 22:48:36', '2020-07-07 21:48:36', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&p=132', 9, 'acf-field', '', 0),
(133, 1, '2020-07-07 22:49:15', '2020-07-07 21:49:15', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 22:49:15', '2020-07-07 21:49:15', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(134, 1, '2020-07-07 22:54:19', '2020-07-07 21:54:19', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 22:54:19', '2020-07-07 21:54:19', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(135, 1, '2020-07-07 22:54:32', '2020-07-07 21:54:32', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-07 22:54:32', '2020-07-07 21:54:32', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(137, 1, '2020-07-10 14:04:51', '2020-07-10 13:04:51', '', 'Property Ombudsman Logo', '', 'inherit', 'closed', 'closed', '', 'property-ombudsman-logo', '', '', '2020-07-10 14:04:51', '2020-07-10 13:04:51', '', 0, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Property-Ombudsman-Logo.png', 0, 'attachment', 'image/png', 0),
(138, 1, '2020-07-10 14:08:46', '2020-07-10 13:08:46', '', 'property-mark', '', 'inherit', 'closed', 'closed', '', 'property-mark', '', '', '2020-07-10 14:08:46', '2020-07-10 13:08:46', '', 0, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/property-mark.png', 0, 'attachment', 'image/png', 0),
(139, 1, '2020-07-10 15:25:11', '2020-07-10 14:25:11', 'a:7:{s:8:"location";a:1:{i:0;a:1:{i:0;a:3:{s:5:"param";s:4:"page";s:8:"operator";s:2:"==";s:5:"value";s:2:"18";}}}s:8:"position";s:15:"acf_after_title";s:5:"style";s:7:"default";s:15:"label_placement";s:3:"top";s:21:"instruction_placement";s:5:"label";s:14:"hide_on_screen";s:0:"";s:11:"description";s:0:"";}', 'Guide To Selling Options', 'guide-to-selling-options', 'publish', 'closed', 'closed', '', 'group_5f0879e41bbf1', '', '', '2020-07-10 15:43:43', '2020-07-10 14:43:43', '', 0, 'http://fullbrook-floor.vm/?post_type=acf-field-group&#038;p=139', 0, 'acf-field-group', '', 0),
(140, 1, '2020-07-10 15:25:11', '2020-07-10 14:25:11', 'a:10:{s:4:"type";s:8:"repeater";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"collapsed";s:0:"";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"layout";s:5:"block";s:12:"button_label";s:14:"Add guide step";}', 'Guide Steps', 'guide_steps', 'publish', 'closed', 'closed', '', 'field_5f0879ee9a051', '', '', '2020-07-10 15:43:43', '2020-07-10 14:43:43', '', 139, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=140', 0, 'acf-field', '', 0),
(141, 1, '2020-07-10 15:25:11', '2020-07-10 14:25:11', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Image', 'image', 'publish', 'closed', 'closed', '', 'field_5f0879fa9a052', '', '', '2020-07-10 15:42:43', '2020-07-10 14:42:43', '', 140, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=141', 0, 'acf-field', '', 0),
(142, 1, '2020-07-10 15:25:11', '2020-07-10 14:25:11', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Title', 'title', 'publish', 'closed', 'closed', '', 'field_5f087a079a053', '', '', '2020-07-10 15:42:43', '2020-07-10 14:42:43', '', 140, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=142', 2, 'acf-field', '', 0),
(143, 1, '2020-07-10 15:25:11', '2020-07-10 14:25:11', 'a:10:{s:4:"type";s:7:"wysiwyg";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:4:"tabs";s:3:"all";s:7:"toolbar";s:4:"full";s:12:"media_upload";i:1;s:5:"delay";i:0;}', 'Content', 'content', 'publish', 'closed', 'closed', '', 'field_5f087a0b9a054', '', '', '2020-07-10 15:42:44', '2020-07-10 14:42:44', '', 140, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=143', 3, 'acf-field', '', 0),
(144, 1, '2020-07-10 15:25:11', '2020-07-10 14:25:11', 'a:10:{s:4:"type";s:10:"true_false";s:12:"instructions";s:66:"If this is a highlighted step, select this option to add emphasis.";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:7:"message";s:0:"";s:13:"default_value";i:0;s:2:"ui";i:1;s:10:"ui_on_text";s:0:"";s:11:"ui_off_text";s:0:"";}', 'Is Highlighted', 'is_highlighted', 'publish', 'closed', 'closed', '', 'field_5f087a179a055', '', '', '2020-07-10 15:42:43', '2020-07-10 14:42:43', '', 140, 'http://fullbrook-floor.vm/?post_type=acf-field&#038;p=144', 1, 'acf-field', '', 0),
(145, 1, '2020-07-10 15:42:06', '2020-07-10 14:42:06', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-10 15:42:06', '2020-07-10 14:42:06', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(146, 1, '2020-07-10 15:50:27', '2020-07-10 14:50:27', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-10 15:50:27', '2020-07-10 14:50:27', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(147, 1, '2020-07-10 15:58:33', '2020-07-10 14:58:33', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-10 15:58:33', '2020-07-10 14:58:33', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(148, 1, '2020-07-10 16:01:36', '2020-07-10 15:01:36', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-10 16:01:36', '2020-07-10 15:01:36', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(149, 1, '2020-07-10 16:16:16', '2020-07-10 15:16:16', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-10 16:16:16', '2020-07-10 15:16:16', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(150, 1, '2020-07-10 16:16:42', '2020-07-10 15:16:42', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-10 16:16:42', '2020-07-10 15:16:42', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(151, 1, '2020-07-10 16:18:20', '2020-07-10 15:18:20', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-10 16:18:20', '2020-07-10 15:18:20', '', 14, 'http://fullbrook-floor.vm/14-revision-v1/', 0, 'revision', '', 0),
(152, 1, '2020-07-10 18:24:43', '2020-07-10 17:24:43', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\n\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\n\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home 3', '', 'inherit', 'closed', 'closed', '', '45-autosave-v1', '', '', '2020-07-10 18:24:43', '2020-07-10 17:24:43', '', 45, 'http://fullbrook-floor.vm/45-autosave-v1/', 0, 'revision', '', 0),
(153, 1, '2020-07-10 18:25:40', '2020-07-10 17:25:40', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\n\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\n\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home 2', '', 'inherit', 'closed', 'closed', '', '46-autosave-v1', '', '', '2020-07-10 18:25:40', '2020-07-10 17:25:40', '', 46, 'http://fullbrook-floor.vm/46-autosave-v1/', 0, 'revision', '', 0),
(154, 1, '2020-07-11 12:59:38', '2020-07-11 11:59:38', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin neque neque, suscipit eget fringilla sit amet, fermentum at lacus. Etiam et ligula gravida, pulvinar diam eget, congue magna.\r\n\r\n&nbsp;', 'Contact Us', '', 'inherit', 'closed', 'closed', '', '20-revision-v1', '', '', '2020-07-11 12:59:38', '2020-07-11 11:59:38', '', 20, 'http://fullbrook-floor.vm/20-revision-v1/', 0, 'revision', '', 0),
(155, 1, '2020-07-11 13:04:28', '2020-07-11 12:04:28', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin neque neque, suscipit eget fringilla sit amet, fermentum at lacus. Etiam et ligula gravida, pulvinar diam eget, congue magna.\r\n\r\n[ninja_form id=1]\r\n\r\n&nbsp;', 'Contact Us', '', 'inherit', 'closed', 'closed', '', '20-revision-v1', '', '', '2020-07-11 13:04:28', '2020-07-11 12:04:28', '', 20, 'http://fullbrook-floor.vm/20-revision-v1/', 0, 'revision', '', 0),
(156, 1, '2020-07-11 14:59:05', '2020-07-11 13:59:05', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\n[caption id="attachment_122" align="aligncenter" width="1024"]<img class="wp-image-122 size-large" src="http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f-1024x683.jpg" alt="" width="1024" height="683" /> this is a caption of the image in question[/caption]\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling you home 4', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-you-home-4-2', '', '', '2020-07-11 14:59:05', '2020-07-11 13:59:05', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=156', 0, 'help-advice', '', 0),
(157, 1, '2020-07-11 14:59:04', '2020-07-11 13:59:04', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home 3', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-your-home-3-2', '', '', '2020-07-11 14:59:04', '2020-07-11 13:59:04', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=157', 0, 'help-advice', '', 0),
(158, 1, '2020-07-11 14:59:04', '2020-07-11 13:59:04', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home 2', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-your-home-2-2', '', '', '2020-07-11 14:59:04', '2020-07-11 13:59:04', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=158', 0, 'help-advice', '', 0) ;
INSERT INTO `league_posts` ( `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(159, 1, '2020-07-11 14:59:04', '2020-07-11 13:59:04', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice selling your home', '', 'publish', 'closed', 'closed', '', 'help-advice-selling-your-home-4', '', '', '2020-07-11 14:59:04', '2020-07-11 13:59:04', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=159', 0, 'help-advice', '', 0),
(160, 1, '2020-07-11 14:59:04', '2020-07-11 13:59:04', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice mortgages', '', 'publish', 'closed', 'closed', '', 'help-advice-mortgages-2', '', '', '2020-07-11 14:59:04', '2020-07-11 13:59:04', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=160', 0, 'help-advice', '', 0),
(161, 1, '2020-07-11 14:59:05', '2020-07-11 13:59:05', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice lettings', '', 'publish', 'closed', 'closed', '', 'help-advice-lettings-2', '', '', '2020-07-11 14:59:05', '2020-07-11 13:59:05', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=161', 0, 'help-advice', '', 0),
(162, 1, '2020-07-11 14:59:05', '2020-07-11 13:59:05', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice landlords', '', 'publish', 'closed', 'closed', '', 'help-advice-landlords-2', '', '', '2020-07-11 14:59:05', '2020-07-11 13:59:05', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=162', 0, 'help-advice', '', 0),
(163, 1, '2020-07-11 14:59:05', '2020-07-11 13:59:05', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice housing market', '', 'publish', 'closed', 'closed', '', 'help-advice-housing-market-2', '', '', '2020-07-11 14:59:05', '2020-07-11 13:59:05', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=163', 0, 'help-advice', '', 0),
(164, 1, '2020-07-11 14:59:05', '2020-07-11 13:59:05', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice finance', '', 'publish', 'closed', 'closed', '', 'help-advice-finance-2', '', '', '2020-07-11 14:59:05', '2020-07-11 13:59:05', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=164', 0, 'help-advice', '', 0),
(165, 1, '2020-07-11 14:59:05', '2020-07-11 13:59:05', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent tristique in felis vitae vestibulum. Nam quis commodo quam. Pellentesque sit amet leo fringilla, venenatis mauris ac, pharetra velit. Sed nec ultrices ex, laoreet ornare lorem. Suspendisse vulputate justo id risus finibus, vitae lacinia elit consectetur. Ut vitae interdum diam. Integer sem orci, gravida vel tempor vel, porttitor vel velit. Sed vulputate aliquam est, at pulvinar tellus consequat venenatis. In imperdiet magna vitae eros auctor, vitae volutpat felis pretium.\r\n\r\nAenean laoreet molestie pharetra. Aliquam ultrices ac purus a egestas. Pellentesque in semper sapien, sed tempor dolor. Nunc sit amet est at lectus semper pharetra et vel sem. Praesent faucibus nisi a interdum convallis. Proin feugiat justo non vehicula feugiat. Etiam maximus suscipit diam nec porttitor. Nullam condimentum facilisis neque sit amet pulvinar. Aenean gravida elit arcu, sed varius purus maximus eget. Proin non orci luctus, finibus diam quis, mattis tellus. Sed malesuada, mi sed varius accumsan, dolor est efficitur neque, et aliquet libero leo pellentesque sem. Duis euismod non tortor sed pulvinar. Nam faucibus eros vitae mi laoreet euismod. Ut facilisis consequat nibh nec elementum. Pellentesque nunc nibh, euismod at pellentesque sed, fermentum vitae libero. Vestibulum erat arcu, condimentum in ante eu, lobortis egestas ipsum.\r\n\r\nSed orci nunc, rutrum vel risus a, viverra bibendum quam. Curabitur sagittis nibh ipsum, ut efficitur neque sagittis at. Nullam feugiat nunc non nulla imperdiet sagittis. Nunc dignissim tristique efficitur. Integer pretium consequat lacus, sed interdum erat. Sed varius suscipit lorem, vel maximus turpis ultricies sed. Nullam vel tellus at dolor consectetur semper. Duis ac risus ac sapien faucibus gravida. Phasellus rutrum magna a elementum condimentum. Etiam non turpis urna. Sed tellus ante, consectetur at risus in, gravida posuere velit. Nulla a turpis sed augue vulputate pharetra. Nulla eget tortor velit. Mauris pellentesque ante erat, quis efficitur velit hendrerit et. Quisque varius consequat pretium. Proin egestas, ligula nec placerat commodo, lacus felis lobortis metus, a aliquam mi libero gravida ante.', 'Help & advice buying a home', '', 'publish', 'closed', 'closed', '', 'help-advice-buying-a-home-2', '', '', '2020-07-11 14:59:05', '2020-07-11 13:59:05', '', 0, 'http://fullbrook-floor.vm/?post_type=help-advice&#038;p=165', 0, 'help-advice', '', 0),
(166, 1, '2020-07-11 15:33:12', '2020-07-11 14:33:12', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Sidebar Logos', 'sidebar_logos', 'publish', 'closed', 'closed', '', 'field_5f09cd6666106', '', '', '2020-07-11 15:33:12', '2020-07-11 14:33:12', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=166', 9, 'acf-field', '', 0),
(167, 1, '2020-07-11 15:33:12', '2020-07-11 14:33:12', 'a:10:{s:4:"type";s:8:"repeater";s:12:"instructions";s:53:"Add logos here to add them to the sidebar of the page";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"collapsed";s:0:"";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"layout";s:5:"table";s:12:"button_label";s:8:"Add Logo";}', 'Sidebar Logos', 'sidebar_logos', 'publish', 'closed', 'closed', '', 'field_5f09cd7166107', '', '', '2020-07-11 15:33:12', '2020-07-11 14:33:12', '', 68, 'http://fullbrook-floor.vm/?post_type=acf-field&p=167', 10, 'acf-field', '', 0),
(168, 1, '2020-07-11 15:33:12', '2020-07-11 14:33:12', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Name', 'name', 'publish', 'closed', 'closed', '', 'field_5f09cd9466109', '', '', '2020-07-11 15:33:12', '2020-07-11 14:33:12', '', 167, 'http://fullbrook-floor.vm/?post_type=acf-field&p=168', 0, 'acf-field', '', 0),
(169, 1, '2020-07-11 15:33:12', '2020-07-11 14:33:12', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Image', 'image', 'publish', 'closed', 'closed', '', 'field_5f09cd8a66108', '', '', '2020-07-11 15:33:12', '2020-07-11 14:33:12', '', 167, 'http://fullbrook-floor.vm/?post_type=acf-field&p=169', 1, 'acf-field', '', 0),
(170, 1, '2020-07-11 15:33:12', '2020-07-11 14:33:12', 'a:7:{s:4:"type";s:3:"url";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";}', 'Link', 'link', 'publish', 'closed', 'closed', '', 'field_5f09cd9d6610a', '', '', '2020-07-11 15:33:12', '2020-07-11 14:33:12', '', 167, 'http://fullbrook-floor.vm/?post_type=acf-field&p=170', 2, 'acf-field', '', 0),
(175, 1, '2020-07-11 15:38:48', '2020-07-11 14:38:48', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-11 15:38:48', '2020-07-11 14:38:48', '', 14, 'http://fullbrook-floor.vm/14-revision-v1/', 0, 'revision', '', 0),
(176, 1, '2020-07-15 09:48:39', '2020-07-15 08:48:39', 'With a combined 40 years of experience and an unrivalled knowledge of St. Albans and the surrounding area, Rod Fullbrook and Rene Floor are here to help you sell your home. Over our decades of experience in the industry, we’ve guided thousands of people through the moving process, making it as simple as possible from start to finish.\r\n\r\nWe’ve set up our own agency because we know we can improve the selling experience for the people of St. Albans. Whether it’s your first move or your fifth, we’ll assist you with every step of your moving journey, from finding a conveyancer at the start to handing you the keys at the end. Our commitment to our clients is total - long after your move, you can come to us for expert advice.\r\n\r\nBy selling with Fullbrook &amp; Floor, you’ll get the best price for your home. We work for you - we’re driven by a determination to achieve the very best for our customers.', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-15 09:48:39', '2020-07-15 08:48:39', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0),
(177, 1, '2020-07-15 09:48:56', '2020-07-15 08:48:56', 'After working for decades for an established, reputable estate agents in St. Albans, we have branched out into our own agency to offer an enhanced experience for home buyers and sellers in the area.\r\n\r\nWorking together instead of as individuals means that we offer a greater level of flexibility and a personal touch that cannot be matched - you’ll be supported, advised and guided through every step of the moving process by experts. You’ll deal with us and only us - you can place complete faith in our integrity and our knowledge as we strive to get you the best possible price for your home.\r\n\r\nWe’re determined, but we’re not pushy; we want what’s best for you, not what’s best for us. Through our work in the St. Albans area, we’ve helped thousands of people move, getting the best prices and supporting people on their journey to their dream home. We’re deeply connected and committed to our community, and it’s this connection that makes us the go-to people for moving home in St. Albans.', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-15 09:48:56', '2020-07-15 08:48:56', '', 14, 'http://fullbrook-floor.vm/14-revision-v1/', 0, 'revision', '', 0),
(178, 1, '2020-07-15 09:49:16', '2020-07-15 08:49:16', 'The best properties in St. Albans, Chiswell Green, Bricket Wood and beyond. If you’re looking for your dream home, we can help you secure it. Browse available properties here.', 'Buy a home', '', 'inherit', 'closed', 'closed', '', '8-revision-v1', '', '', '2020-07-15 09:49:16', '2020-07-15 08:49:16', '', 8, 'http://fullbrook-floor.vm/8-revision-v1/', 0, 'revision', '', 0),
(179, 1, '2020-07-15 09:49:35', '2020-07-15 08:49:35', 'Selling your home is a big decision, and you need people you can trust to facilitate it. Whether it’s your first sale or you’ve sold a home before, Fullbrook &amp; Floor will be with you at every turn, ensuring you get the best price for your home and that your entire moving process is hassle-free. From pricing your house to overseeing the sale, let us do the hard work while you focus on your future. Contact us today for expert advice on selling a home in St. Albans.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-15 09:49:35', '2020-07-15 08:49:35', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(180, 1, '2020-07-15 09:51:24', '2020-07-15 08:51:24', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-15 09:51:24', '2020-07-15 08:51:24', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(181, 1, '2020-07-15 09:52:36', '2020-07-15 08:52:36', 'Selling your home is a big decision, and you need people you can trust to facilitate it.\r\n\r\nWhether it’s your first sale or you’ve sold a home before, Fullbrook &amp; Floor will be with you at every turn, ensuring you get the best price for your home and that your entire moving process is hassle-free. From pricing your house to overseeing the sale, let us do the hard work while you focus on your future. Contact us today for expert advice on selling a home in St. Albans.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-15 09:52:36', '2020-07-15 08:52:36', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(182, 1, '2020-07-15 09:57:40', '2020-07-15 08:57:40', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-15 09:57:40', '2020-07-15 08:57:40', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(183, 1, '2020-07-15 10:01:40', '2020-07-15 09:01:40', '', 'Meet the team', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-15 10:01:40', '2020-07-15 09:01:40', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(184, 1, '2020-07-15 10:10:15', '2020-07-15 09:10:15', '', 'Meet the team', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-15 10:10:15', '2020-07-15 09:10:15', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(185, 1, '2020-07-15 17:34:37', '2020-07-15 16:34:37', 'The best properties in St. Albans, Chiswell Green, Bricket Wood and beyond. If you’re looking for your dream home, we can help you secure it. Browse available properties here.', 'Buy a home', '', 'inherit', 'closed', 'closed', '', '8-revision-v1', '', '', '2020-07-15 17:34:37', '2020-07-15 16:34:37', '', 8, 'http://fullbrook-floor.vm/8-revision-v1/', 0, 'revision', '', 0),
(186, 1, '2020-07-15 17:35:25', '2020-07-15 16:35:25', '', 'Meet Rene & Rod', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-15 17:35:25', '2020-07-15 16:35:25', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(187, 1, '2020-07-15 17:44:33', '2020-07-15 16:44:33', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Hero Carousel', 'homepage_carousel', 'publish', 'closed', 'closed', '', 'field_5f0f323a4af91', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 61, 'http://fullbrook-floor.vm/?post_type=acf-field&p=187', 0, 'acf-field', '', 0),
(188, 1, '2020-07-15 17:44:33', '2020-07-15 16:44:33', 'a:18:{s:4:"type";s:7:"gallery";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:6:"insert";s:6:"append";s:7:"library";s:3:"all";s:3:"min";s:0:"";s:3:"max";s:0:"";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Hero Carousel', 'hero_carousel', 'publish', 'closed', 'closed', '', 'field_5f0f32444af92', '', '', '2020-07-15 17:44:33', '2020-07-15 16:44:33', '', 61, 'http://fullbrook-floor.vm/?post_type=acf-field&p=188', 1, 'acf-field', '', 0),
(189, 1, '2020-07-15 17:48:01', '2020-07-15 16:48:01', 'With a combined 40 years of experience and an unrivalled knowledge of St. Albans and the surrounding area, Rod Fullbrook and Rene Floor are here to help you sell your home. Over our decades of experience in the industry, we’ve guided thousands of people through the moving process, making it as simple as possible from start to finish.\r\n\r\nWe’ve set up our own agency because we know we can improve the selling experience for the people of St. Albans. Whether it’s your first move or your fifth, we’ll assist you with every step of your moving journey, from finding a conveyancer at the start to handing you the keys at the end. Our commitment to our clients is total - long after your move, you can come to us for expert advice.\r\n\r\nBy selling with Fullbrook &amp; Floor, you’ll get the best price for your home. We work for you - we’re driven by a determination to achieve the very best for our customers.', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-15 17:48:01', '2020-07-15 16:48:01', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0),
(190, 1, '2020-07-15 17:55:09', '2020-07-15 16:55:09', 'After working for decades for an established, reputable estate agents in St. Albans, we have branched out into our own agency to offer an enhanced experience for home buyers and sellers in the area.\r\n\r\nWorking together instead of as individuals means that we offer a greater level of flexibility and a personal touch that cannot be matched - you’ll be supported, advised and guided through every step of the moving process by experts. You’ll deal with us and only us - you can place complete faith in our integrity and our knowledge as we strive to get you the best possible price for your home.\r\n\r\nWe’re determined, but we’re not pushy; we want what’s best for you, not what’s best for us. Through our work in the St. Albans area, we’ve helped thousands of people move, getting the best prices and supporting people on their journey to their dream home. We’re deeply connected and committed to our community, and it’s this connection that makes us the go-to people for moving home in St. Albans.', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-15 17:55:09', '2020-07-15 16:55:09', '', 14, 'http://fullbrook-floor.vm/14-revision-v1/', 0, 'revision', '', 0),
(191, 1, '2020-07-15 17:58:42', '2020-07-15 16:58:42', 'a:7:{s:4:"type";s:3:"tab";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"placement";s:4:"left";s:8:"endpoint";i:0;}', 'Mover Logos', 'mover_logos', 'publish', 'closed', 'closed', '', 'field_5f0f357caf5a4', '', '', '2020-07-15 17:58:42', '2020-07-15 16:58:42', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&p=191', 11, 'acf-field', '', 0),
(192, 1, '2020-07-15 17:58:42', '2020-07-15 16:58:42', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Mover Logo Headline', 'mover_logo_headline', 'publish', 'closed', 'closed', '', 'field_5f0f3585af5a5', '', '', '2020-07-15 17:58:42', '2020-07-15 16:58:42', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&p=192', 12, 'acf-field', '', 0),
(193, 1, '2020-07-15 17:58:43', '2020-07-15 16:58:43', 'a:10:{s:4:"type";s:8:"repeater";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"collapsed";s:0:"";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"layout";s:5:"table";s:12:"button_label";s:8:"Add logo";}', 'Mover Logos', 'mover_logos', 'publish', 'closed', 'closed', '', 'field_5f0f359caf5a6', '', '', '2020-07-15 17:58:43', '2020-07-15 16:58:43', '', 47, 'http://fullbrook-floor.vm/?post_type=acf-field&p=193', 13, 'acf-field', '', 0),
(194, 1, '2020-07-15 17:58:43', '2020-07-15 16:58:43', 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'Name', 'name', 'publish', 'closed', 'closed', '', 'field_5f0f35a3af5a7', '', '', '2020-07-15 17:58:43', '2020-07-15 16:58:43', '', 193, 'http://fullbrook-floor.vm/?post_type=acf-field&p=194', 0, 'acf-field', '', 0),
(195, 1, '2020-07-15 17:58:43', '2020-07-15 16:58:43', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Logo', 'logo', 'publish', 'closed', 'closed', '', 'field_5f0f35acaf5a8', '', '', '2020-07-15 17:58:43', '2020-07-15 16:58:43', '', 193, 'http://fullbrook-floor.vm/?post_type=acf-field&p=195', 1, 'acf-field', '', 0),
(196, 1, '2020-07-15 17:58:43', '2020-07-15 16:58:43', 'a:7:{s:4:"type";s:3:"url";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";}', 'Link', 'link', 'publish', 'closed', 'closed', '', 'field_5f0f35b1af5a9', '', '', '2020-07-15 17:58:43', '2020-07-15 16:58:43', '', 193, 'http://fullbrook-floor.vm/?post_type=acf-field&p=196', 2, 'acf-field', '', 0),
(197, 1, '2020-07-15 18:01:52', '2020-07-15 17:01:52', '', 'Rightmove_logo_DEC2016', '', 'inherit', 'closed', 'closed', '', 'rightmove_logo_dec2016', '', '', '2020-07-15 18:02:11', '2020-07-15 17:02:11', '', 14, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Rightmove_logo_DEC2016.png', 0, 'attachment', 'image/png', 0),
(198, 1, '2020-07-15 18:01:53', '2020-07-15 17:01:53', '', 'Zoopla-logo-Purple-RGBPNG', '', 'inherit', 'closed', 'closed', '', 'zoopla-logo-purple-rgbpng', '', '', '2020-07-15 18:02:11', '2020-07-15 17:02:11', '', 14, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Zoopla-logo-Purple-RGBPNG.png', 0, 'attachment', 'image/png', 0),
(199, 1, '2020-07-15 18:02:11', '2020-07-15 17:02:11', 'After working for decades for an established, reputable estate agents in St. Albans, we have branched out into our own agency to offer an enhanced experience for home buyers and sellers in the area.\r\n\r\nWorking together instead of as individuals means that we offer a greater level of flexibility and a personal touch that cannot be matched - you’ll be supported, advised and guided through every step of the moving process by experts. You’ll deal with us and only us - you can place complete faith in our integrity and our knowledge as we strive to get you the best possible price for your home.\r\n\r\nWe’re determined, but we’re not pushy; we want what’s best for you, not what’s best for us. Through our work in the St. Albans area, we’ve helped thousands of people move, getting the best prices and supporting people on their journey to their dream home. We’re deeply connected and committed to our community, and it’s this connection that makes us the go-to people for moving home in St. Albans.', 'About us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2020-07-15 18:02:11', '2020-07-15 17:02:11', '', 14, 'http://fullbrook-floor.vm/14-revision-v1/', 0, 'revision', '', 0),
(200, 1, '2020-07-15 18:18:28', '2020-07-15 17:18:28', 'a:15:{s:4:"type";s:5:"image";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:2:"50";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"return_format";s:5:"array";s:12:"preview_size";s:6:"medium";s:7:"library";s:3:"all";s:9:"min_width";s:0:"";s:10:"min_height";s:0:"";s:8:"min_size";s:0:"";s:9:"max_width";s:0:"";s:10:"max_height";s:0:"";s:8:"max_size";s:0:"";s:10:"mime_types";s:0:"";}', 'Casual Photo', 'casual_photo', 'publish', 'closed', 'closed', '', 'field_5f0f3a3dda3d7', '', '', '2020-07-15 18:18:28', '2020-07-15 17:18:28', '', 84, 'http://fullbrook-floor.vm/?post_type=acf-field&p=200', 5, 'acf-field', '', 0),
(201, 1, '2020-07-15 18:20:24', '2020-07-15 17:20:24', '', 'profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D', '', 'inherit', 'closed', 'closed', '', 'profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2afh77d', '', '', '2020-07-15 18:20:24', '2020-07-15 17:20:24', '', 16, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D.jpg', 0, 'attachment', 'image/jpeg', 0),
(202, 1, '2020-07-15 18:20:40', '2020-07-15 17:20:40', '', 'Meet Rene & Rod', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-15 18:20:40', '2020-07-15 17:20:40', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(203, 1, '2020-07-22 16:58:06', '2020-07-22 15:58:06', '', 'logo-update-v8', '', 'inherit', 'closed', 'closed', '', 'logo-update-v8', '', '', '2020-07-22 16:58:06', '2020-07-22 15:58:06', '', 0, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-update-v8.svg', 0, 'attachment', 'image/svg+xml', 0),
(204, 1, '2020-07-22 17:01:28', '2020-07-22 16:01:28', 'With a combined 40 years of experience and an unrivalled knowledge of St. Albans and the surrounding area, Rod Fullbrook and René Floor are here to help you sell your home. With over three decades of experience in the industry, we’ve guided thousands of clients through the moving process, making it as simple as possible from start to finish.\r\n\r\nWe’ve set up our own agency because we know we can improve the selling experience for the people of St. Albans. Whether it’s your first move or your fifth, we’ll assist you with every step of your moving journey, from finding the right buyer at the start to handing you the keys upon completion. Our commitment to our clients is total - long after your move, you can come to us for expert advice.\r\n\r\nBy selling with Fullbrook &amp; Floor, you’ll get the best possible buyer and the best possible price for your home. We work for you - we’re driven by a determination to achieve the very best for our clients across St. Albans, Chiswell Green, Brickett Wood, Park Street and the surrounding areas. Talk to us today to hear more from the best independent estate agents in St. Albans.', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-22 17:01:28', '2020-07-22 16:01:28', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0),
(205, 1, '2020-07-22 17:02:22', '2020-07-22 16:02:22', '', 'Meet Rene & Rod', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-22 17:02:22', '2020-07-22 16:02:22', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(206, 1, '2020-07-22 17:02:36', '2020-07-22 16:02:36', '', 'Meet Rene & Rod', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2020-07-22 17:02:36', '2020-07-22 16:02:36', '', 16, 'http://fullbrook-floor.vm/16-revision-v1/', 0, 'revision', '', 0),
(207, 1, '2020-07-22 17:02:45', '2020-07-22 16:02:45', 'We offer a wide range of quality properties in St. Albans and the surrounding villages, including Chiswell Green, Brickett Wood and Park Street. If you’re looking for your next home, we can help you find it. Click here to browse houses for sale in the area.', 'Buy a home', '', 'inherit', 'closed', 'closed', '', '8-revision-v1', '', '', '2020-07-22 17:02:45', '2020-07-22 16:02:45', '', 8, 'http://fullbrook-floor.vm/8-revision-v1/', 0, 'revision', '', 0),
(208, 1, '2020-07-22 17:02:52', '2020-07-22 16:02:52', 'Selling your home is a big decision, and you need people you can trust to facilitate it.\n\nWhether it’s your first sale or you’ve sold a home before, Fullbrook &amp; Floor will be with you at every turn, ensuring you get the best price for your home and that your entire moving process is hassle-free. From pricing your house to overseeing the sale, let us do the hard work while you focus on your future. Contact us today for expert advice on selling a home in St. Albans.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-autosave-v1', '', '', '2020-07-22 17:02:52', '2020-07-22 16:02:52', '', 10, 'http://fullbrook-floor.vm/10-autosave-v1/', 0, 'revision', '', 0),
(209, 1, '2020-07-22 17:02:59', '2020-07-22 16:02:59', 'Selling your home is a big decision, and you need people you can trust to facilitate it. Whether it’s your first selling experience or you’ve sold a home before, Fullbrook &amp; Floor will be with you at every turn, ensuring you get the best possible price for your home and that your entire moving process is as stress-free as possible. From valuing your property to overseeing the house sale, let us do the hard work while you focus on your future. Contact us today for expert advice on selling a property in St. Albans - we help clients across Park Street, Chiswell Green, Brickett Wood and beyond.', 'Sell your home', '', 'inherit', 'closed', 'closed', '', '10-revision-v1', '', '', '2020-07-22 17:02:59', '2020-07-22 16:02:59', '', 10, 'http://fullbrook-floor.vm/10-revision-v1/', 0, 'revision', '', 0),
(210, 1, '2020-07-22 17:04:43', '2020-07-22 16:04:43', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam dictum vitae augue id blandit. Donec ut lorem at ligula feugiat commodo. Duis non pulvinar nisl. Suspendisse in dolor sit amet sapien aliquet auctor. Donec ornare sed nisl ac condimentum. Quisque in mollis justo. Aenean facilisis, leo rutrum aliquam imperdiet, risus sem lobortis eros, ac lobortis nisi ligula sed arcu. Suspendisse dolor magna, commodo non nisi molestie, rutrum euismod nisi. Nam et risus sed neque commodo rutrum.\r\n\r\nPraesent rutrum nisl enim, efficitur elementum odio aliquet ut. Sed accumsan dui at lorem elementum pulvinar. Donec condimentum ligula lorem, vitae mollis justo dignissim ut. Fusce non eros vitae urna elementum molestie sit amet at dui. Nullam vitae scelerisque est. Vestibulum posuere tortor at felis venenatis accumsan. Donec venenatis, urna at dignissim vestibulum, risus nunc viverra lorem, eu hendrerit felis ipsum at odio. Pellentesque at pretium libero, sit amet convallis mauris. Maecenas ut massa nec elit molestie condimentum in congue nulla. Morbi sit amet est bibendum, lobortis lectus vel, ultrices turpis. Morbi imperdiet augue vitae consectetur elementum. Mauris sit amet nisl non magna lacinia consequat id a est.', 'How to sell a home', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2020-07-22 17:04:43', '2020-07-22 16:04:43', '', 18, 'http://fullbrook-floor.vm/18-revision-v1/', 0, 'revision', '', 0),
(211, 1, '2020-07-22 17:22:06', '2020-07-22 16:22:06', '', 'logo-update-v9', '', 'inherit', 'closed', 'closed', '', 'logo-update-v9', '', '', '2020-07-22 17:22:06', '2020-07-22 16:22:06', '', 0, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-update-v9.svg', 0, 'attachment', 'image/svg+xml', 0),
(212, 1, '2020-07-22 17:25:06', '2020-07-22 16:25:06', 'With a combined 40 years of experience and an unrivalled knowledge of St. Albans and the surrounding area, Rod Fullbrook and René Floor are here to help you sell your home. With over three decades of experience in the industry, we’ve guided thousands of clients through the moving process, making it as simple as possible from start to finish.\r\n\r\nWe’ve set up our own agency because we know we can improve the selling experience for the people of St. Albans. Whether it’s your first move or your fifth, we’ll assist you with every step of your moving journey, from finding the right buyer at the start to handing you the keys upon completion. Our commitment to our clients is total - long after your move, you can come to us for expert advice.\r\n\r\nBy selling with Fullbrook &amp; Floor, you’ll get the best possible buyer and the best possible price for your home. We work for you - we’re driven by a determination to achieve the very best for our clients across St. Albans, Chiswell Green, Brickett Wood, Park Street and the surrounding areas. Talk to us today to hear more from the best independent estate agents in St. Albans.', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-22 17:25:06', '2020-07-22 16:25:06', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0) ;
INSERT INTO `league_posts` ( `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(213, 1, '2020-07-22 17:26:01', '2020-07-22 16:26:01', 'With a combined 40 years of experience and an unrivalled knowledge of St. Albans and the surrounding area, Rod Fullbrook and René Floor are here to help you sell your home. With over three decades of experience in the industry, we’ve guided thousands of clients through the moving process, making it as simple as possible from start to finish.\r\n\r\nWe’ve set up our own agency because we know we can improve the selling experience for the people of St. Albans. Whether it’s your first move or your fifth, we’ll assist you with every step of your moving journey, from finding the right buyer at the start to handing you the keys upon completion. Our commitment to our clients is total - long after your move, you can come to us for expert advice.\r\n\r\nBy selling with Fullbrook &amp; Floor, you’ll get the best possible buyer and the best possible price for your home. We work for you - we’re driven by a determination to achieve the very best for our clients across St. Albans, Chiswell Green, Brickett Wood, Park Street and the surrounding areas. Talk to us today to hear more from the best independent estate agents in St. Albans.', 'Home', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2020-07-22 17:26:01', '2020-07-22 16:26:01', '', 6, 'http://fullbrook-floor.vm/6-revision-v1/', 0, 'revision', '', 0) ;

#
# End of data contents of table `league_posts`
# --------------------------------------------------------



#
# Delete any existing table `league_redirection_404`
#

DROP TABLE IF EXISTS `league_redirection_404`;


#
# Table structure of table `league_redirection_404`
#

CREATE TABLE `league_redirection_404` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `url` (`url`(191)),
  KEY `referrer` (`referrer`(191)),
  KEY `ip` (`ip`)
) ENGINE=MyISAM AUTO_INCREMENT=790 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_redirection_404`
#
INSERT INTO `league_redirection_404` ( `id`, `created`, `url`, `agent`, `referrer`, `ip`) VALUES
(703, '2020-07-18 09:00:43', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(704, '2020-07-21 15:54:03', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(705, '2020-07-22 16:52:03', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(706, '2020-07-22 16:52:12', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(707, '2020-07-22 16:52:31', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(708, '2020-07-22 16:58:19', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(709, '2020-07-22 16:59:24', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(710, '2020-07-22 17:01:12', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(711, '2020-07-22 17:02:38', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(712, '2020-07-22 17:06:20', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(713, '2020-07-22 17:07:56', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(714, '2020-07-22 17:13:31', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(715, '2020-07-22 17:13:33', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(716, '2020-07-22 17:19:58', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(717, '2020-07-22 17:19:58', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(718, '2020-07-22 17:20:08', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(719, '2020-07-22 17:20:33', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(720, '2020-07-22 17:20:52', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(721, '2020-07-22 17:21:14', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(722, '2020-07-22 17:21:45', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/buy-a-home/', ''),
(723, '2020-07-22 17:22:12', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(724, '2020-07-22 17:22:12', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/buy-a-home/', ''),
(725, '2020-07-22 17:22:18', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(726, '2020-07-22 17:23:51', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(727, '2020-07-22 17:24:16', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(728, '2020-07-22 17:25:09', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(729, '2020-07-22 17:25:23', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(730, '2020-07-22 17:26:05', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(731, '2020-07-22 17:27:03', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(732, '2020-07-22 17:27:14', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(733, '2020-07-22 17:27:24', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(734, '2020-07-22 17:29:19', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(735, '2020-07-22 17:30:26', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(736, '2020-07-22 17:31:11', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(737, '2020-07-22 17:31:49', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(738, '2020-07-22 17:32:11', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(739, '2020-07-22 17:32:18', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(740, '2020-07-22 17:32:24', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/buy-a-home/', ''),
(741, '2020-07-22 17:32:31', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/buy-a-home/?', ''),
(742, '2020-07-22 17:32:41', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(743, '2020-07-22 17:32:56', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/buy-a-home/', ''),
(744, '2020-07-22 17:32:59', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/buy-a-home/?', ''),
(745, '2020-07-22 17:33:12', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(746, '2020-07-22 17:33:15', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(747, '2020-07-22 17:33:19', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(748, '2020-07-22 17:33:24', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(749, '2020-07-22 17:35:43', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(750, '2020-07-22 17:38:08', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(751, '2020-07-22 17:39:42', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(752, '2020-07-22 17:40:15', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(753, '2020-07-22 17:41:30', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(754, '2020-07-22 17:44:28', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(755, '2020-07-22 17:50:19', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(756, '2020-07-22 17:50:28', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(757, '2020-07-22 17:51:29', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(758, '2020-07-22 17:51:34', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(759, '2020-07-22 17:51:45', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(760, '2020-07-22 17:52:20', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(761, '2020-07-22 17:52:50', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(762, '2020-07-22 17:53:49', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(763, '2020-07-22 17:56:11', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(764, '2020-07-22 18:34:31', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(765, '2020-07-22 21:04:42', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(766, '2020-07-22 21:42:22', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(767, '2020-07-22 21:46:36', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(768, '2020-07-22 21:48:18', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(769, '2020-07-22 21:49:42', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(770, '2020-07-23 09:52:10', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(771, '2020-07-23 09:56:08', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(772, '2020-07-23 10:03:30', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(773, '2020-07-23 10:08:28', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(774, '2020-07-23 10:08:48', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(775, '2020-07-23 10:09:05', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(776, '2020-07-23 10:14:40', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(777, '2020-07-23 10:15:02', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(778, '2020-07-23 10:15:04', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(779, '2020-07-23 10:16:25', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(780, '2020-07-23 10:16:48', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(781, '2020-07-23 10:17:29', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(782, '2020-07-23 10:34:48', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18362', 'http://fullbrook-floor.vm/', ''),
(783, '2020-07-23 10:35:46', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(784, '2020-07-23 10:56:56', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(785, '2020-07-23 11:03:26', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(786, '2020-07-23 12:14:00', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(787, '2020-07-23 12:17:10', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', ''),
(788, '2020-07-23 18:46:55', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1', 'http://fullbrook-floor.vm/', ''),
(789, '2020-07-23 20:08:02', '/wp-content/themes/fullbrook-floor/images/angle-down-light.svg', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36', 'http://fullbrook-floor.vm/wp-content/themes/fullbrook-floor/dist/main.min.css', '') ;

#
# End of data contents of table `league_redirection_404`
# --------------------------------------------------------



#
# Delete any existing table `league_redirection_groups`
#

DROP TABLE IF EXISTS `league_redirection_groups`;


#
# Table structure of table `league_redirection_groups`
#

CREATE TABLE `league_redirection_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tracking` int(11) NOT NULL DEFAULT 1,
  `module_id` int(11) unsigned NOT NULL DEFAULT 0,
  `status` enum('enabled','disabled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enabled',
  `position` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_redirection_groups`
#
INSERT INTO `league_redirection_groups` ( `id`, `name`, `tracking`, `module_id`, `status`, `position`) VALUES
(1, 'Redirections', 1, 1, 'enabled', 0),
(2, 'Modified Posts', 1, 1, 'enabled', 1) ;

#
# End of data contents of table `league_redirection_groups`
# --------------------------------------------------------



#
# Delete any existing table `league_redirection_items`
#

DROP TABLE IF EXISTS `league_redirection_items`;


#
# Table structure of table `league_redirection_items`
#

CREATE TABLE `league_redirection_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `match_url` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regex` int(11) unsigned NOT NULL DEFAULT 0,
  `position` int(11) unsigned NOT NULL DEFAULT 0,
  `last_count` int(10) unsigned NOT NULL DEFAULT 0,
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `group_id` int(11) NOT NULL DEFAULT 0,
  `status` enum('enabled','disabled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enabled',
  `action_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_code` int(11) unsigned NOT NULL,
  `action_data` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`(191)),
  KEY `status` (`status`),
  KEY `regex` (`regex`),
  KEY `group_idpos` (`group_id`,`position`),
  KEY `group` (`group_id`),
  KEY `match_url` (`match_url`(191))
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_redirection_items`
#
INSERT INTO `league_redirection_items` ( `id`, `url`, `match_url`, `match_data`, `regex`, `position`, `last_count`, `last_access`, `group_id`, `status`, `action_type`, `action_code`, `action_data`, `match_type`, `title`) VALUES
(1, '/free-sales-valuations/', '/free-sales-valuations', '{"source":{"flag_regex":false}}', 0, 0, 0, '0000-00-00 00:00:00', 1, 'enabled', 'url', 301, '/sell-your-home/free-sales-valuations/', 'url', NULL) ;

#
# End of data contents of table `league_redirection_items`
# --------------------------------------------------------



#
# Delete any existing table `league_redirection_logs`
#

DROP TABLE IF EXISTS `league_redirection_logs`;


#
# Table structure of table `league_redirection_logs`
#

CREATE TABLE `league_redirection_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `url` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_to` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `referrer` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirection_id` int(11) unsigned DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `redirection_id` (`redirection_id`),
  KEY `ip` (`ip`),
  KEY `group_id` (`group_id`),
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_redirection_logs`
#

#
# End of data contents of table `league_redirection_logs`
# --------------------------------------------------------



#
# Delete any existing table `league_term_relationships`
#

DROP TABLE IF EXISTS `league_term_relationships`;


#
# Table structure of table `league_term_relationships`
#

CREATE TABLE `league_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_term_relationships`
#
INSERT INTO `league_term_relationships` ( `object_id`, `term_taxonomy_id`, `term_order`) VALUES
(1, 1, 0),
(23, 2, 0),
(24, 2, 0),
(25, 2, 0),
(26, 2, 0),
(27, 2, 0),
(28, 2, 0),
(29, 2, 0),
(30, 2, 0),
(33, 2, 0),
(36, 10, 0),
(38, 15, 0),
(39, 16, 0),
(40, 10, 0),
(40, 11, 0),
(41, 10, 0),
(41, 12, 0),
(42, 13, 0),
(43, 14, 0),
(44, 11, 0),
(45, 11, 0),
(46, 11, 0),
(156, 11, 0),
(157, 11, 0),
(158, 11, 0),
(159, 10, 0),
(159, 11, 0),
(160, 10, 0),
(160, 12, 0),
(161, 13, 0),
(162, 14, 0),
(163, 16, 0),
(164, 15, 0),
(165, 10, 0) ;

#
# End of data contents of table `league_term_relationships`
# --------------------------------------------------------



#
# Delete any existing table `league_term_taxonomy`
#

DROP TABLE IF EXISTS `league_term_taxonomy`;


#
# Table structure of table `league_term_taxonomy`
#

CREATE TABLE `league_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_term_taxonomy`
#
INSERT INTO `league_term_taxonomy` ( `term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1, 1, 'category', '', 0, 1),
(2, 2, 'nav_menu', '', 0, 9),
(3, 3, 'resource-categories', '', 0, 0),
(4, 4, 'resource-categories', '', 0, 0),
(5, 5, 'resource-categories', '', 0, 0),
(6, 6, 'resource-categories', '', 0, 0),
(7, 7, 'resource-categories', '', 0, 0),
(8, 8, 'resource-categories', '', 0, 0),
(9, 9, 'resource-categories', '', 0, 0),
(10, 10, 'help-advice-categories', '', 0, 6),
(11, 11, 'help-advice-categories', '', 0, 8),
(12, 12, 'help-advice-categories', '', 0, 2),
(13, 13, 'help-advice-categories', '', 0, 2),
(14, 14, 'help-advice-categories', '', 0, 2),
(15, 15, 'help-advice-categories', '', 0, 2),
(16, 16, 'help-advice-categories', '', 0, 2) ;

#
# End of data contents of table `league_term_taxonomy`
# --------------------------------------------------------



#
# Delete any existing table `league_termmeta`
#

DROP TABLE IF EXISTS `league_termmeta`;


#
# Table structure of table `league_termmeta`
#

CREATE TABLE `league_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_termmeta`
#

#
# End of data contents of table `league_termmeta`
# --------------------------------------------------------



#
# Delete any existing table `league_terms`
#

DROP TABLE IF EXISTS `league_terms`;


#
# Table structure of table `league_terms`
#

CREATE TABLE `league_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_terms`
#
INSERT INTO `league_terms` ( `term_id`, `name`, `slug`, `term_group`) VALUES
(1, 'Uncategorised', 'uncategorised', 0),
(2, 'Primary Menu', 'primary-menu', 0),
(3, 'Buying a home', 'buying-a-home', 0),
(4, 'Selling your home', 'selling-your-home', 0),
(5, 'Mortgages', 'mortgages', 0),
(6, 'Lettings', 'lettings', 0),
(7, 'Landlords', 'landlords', 0),
(8, 'Finance', 'finance', 0),
(9, 'Housing market', 'housing-market', 0),
(10, 'Buying a home', 'buying-a-home', 0),
(11, 'Selling your home', 'selling-your-home', 0),
(12, 'Mortgages', 'mortgages', 0),
(13, 'Lettings', 'lettings', 0),
(14, 'Landlords', 'landlords', 0),
(15, 'Finance', 'finance', 0),
(16, 'Housing market', 'housing-market', 0) ;

#
# End of data contents of table `league_terms`
# --------------------------------------------------------



#
# Delete any existing table `league_usermeta`
#

DROP TABLE IF EXISTS `league_usermeta`;


#
# Table structure of table `league_usermeta`
#

CREATE TABLE `league_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_usermeta`
#
INSERT INTO `league_usermeta` ( `umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES
(1, 1, 'nickname', 'admin.league'),
(2, 1, 'first_name', ''),
(3, 1, 'last_name', ''),
(4, 1, 'description', ''),
(5, 1, 'rich_editing', 'true'),
(6, 1, 'syntax_highlighting', 'true'),
(7, 1, 'comment_shortcuts', 'false'),
(8, 1, 'admin_color', 'fresh'),
(9, 1, 'use_ssl', '0'),
(10, 1, 'show_admin_bar_front', 'true'),
(11, 1, 'locale', ''),
(12, 1, 'league_capabilities', 'a:1:{s:13:"administrator";b:1;}'),
(13, 1, 'league_user_level', '10'),
(14, 1, 'dismissed_wp_pointers', ''),
(15, 1, 'show_welcome_panel', '0'),
(16, 1, 'session_tokens', 'a:3:{s:64:"4a19d9ccdca97a01c1536643d2e132d4b166ed9e175f658c0c237292fbcd052e";a:4:{s:10:"expiration";i:1595606037;s:2:"ip";s:13:"86.20.174.156";s:2:"ua";s:114:"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36";s:5:"login";i:1595433237;}s:64:"e0b3f0a0d0b200a3d66e2ff81a6cda10a7bcd18fbc0a0a73b03822ef0a513563";a:4:{s:10:"expiration";i:1595704036;s:2:"ip";s:13:"86.20.174.156";s:2:"ua";s:114:"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36";s:5:"login";i:1595531236;}s:64:"e82c85fad19e47434f53cbb0fb83a215e96417c97ce2f777a69fd62d2b851b45";a:4:{s:10:"expiration";i:1595704861;s:2:"ip";s:3:"::1";s:2:"ua";s:114:"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36";s:5:"login";i:1595532061;}}'),
(17, 1, 'league_dashboard_quick_press_last_post_id', '4'),
(18, 1, '_yoast_wpseo_profile_updated', '1593712482'),
(20, 1, 'managenav-menuscolumnshidden', 'a:5:{i:0;s:11:"link-target";i:1;s:11:"css-classes";i:2;s:3:"xfn";i:3;s:11:"description";i:4;s:15:"title-attribute";}'),
(21, 1, 'metaboxhidden_nav-menus', 'a:3:{i:0;s:23:"add-post-type-resources";i:1;s:12:"add-post_tag";i:2;s:23:"add-resource-categories";}'),
(22, 1, 'nav_menu_recently_edited', '2'),
(23, 1, 'league_user-settings', 'libraryContent=browse&editor=tinymce'),
(24, 1, 'league_user-settings-time', '1594830921'),
(25, 1, 'closedpostboxes_page', 'a:0:{}'),
(26, 1, 'metaboxhidden_page', 'a:7:{i:0;s:12:"postimagediv";i:1;s:12:"revisionsdiv";i:2;s:11:"postexcerpt";i:3;s:16:"commentstatusdiv";i:4;s:11:"commentsdiv";i:5;s:7:"slugdiv";i:6;s:9:"authordiv";}'),
(27, 1, 'meta-box-order_page', 'a:4:{s:15:"acf_after_title";s:47:"acf-group_5eff4889c7630,acf-group_5eff532b17dbb";s:4:"side";s:67:"submitdiv,pageparentdiv,nf_admin_metaboxes_appendaform,postimagediv";s:6:"normal";s:82:"wpseo_meta,revisionsdiv,postexcerpt,commentstatusdiv,commentsdiv,slugdiv,authordiv";s:8:"advanced";s:0:"";}'),
(28, 1, 'screen_layout_page', '2'),
(29, 1, '_aal_elementor_install_notice', 'true'),
(30, 1, 'closedpostboxes_toplevel_page_site-options', 'a:0:{}'),
(31, 1, 'metaboxhidden_toplevel_page_site-options', 'a:0:{}') ;

#
# End of data contents of table `league_usermeta`
# --------------------------------------------------------



#
# Delete any existing table `league_users`
#

DROP TABLE IF EXISTS `league_users`;


#
# Table structure of table `league_users`
#

CREATE TABLE `league_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_users`
#
INSERT INTO `league_users` ( `ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
(1, 'admin.league', '$P$B9.SRjlQrz/7Xfs97oYAFer3u5SVUg0', 'admin-league', 'tom@weareleague.co.uk', 'http://fullbrook-floor.vm', '2020-07-02 17:33:38', '', 0, 'admin.league') ;

#
# End of data contents of table `league_users`
# --------------------------------------------------------



#
# Delete any existing table `league_wpmailsmtp_tasks_meta`
#

DROP TABLE IF EXISTS `league_wpmailsmtp_tasks_meta`;


#
# Table structure of table `league_wpmailsmtp_tasks_meta`
#

CREATE TABLE `league_wpmailsmtp_tasks_meta` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_wpmailsmtp_tasks_meta`
#

#
# End of data contents of table `league_wpmailsmtp_tasks_meta`
# --------------------------------------------------------



#
# Delete any existing table `league_yoast_indexable`
#

DROP TABLE IF EXISTS `league_yoast_indexable`;


#
# Table structure of table `league_yoast_indexable`
#

CREATE TABLE `league_yoast_indexable` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permalink` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permalink_hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_id` int(11) unsigned DEFAULT NULL,
  `object_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `object_sub_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` int(11) unsigned DEFAULT NULL,
  `post_parent` int(11) unsigned DEFAULT NULL,
  `title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `breadcrumb_title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT NULL,
  `is_protected` tinyint(1) DEFAULT 0,
  `has_public_posts` tinyint(1) DEFAULT NULL,
  `number_of_pages` int(11) unsigned DEFAULT NULL,
  `canonical` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_focus_keyword` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_focus_keyword_score` int(3) DEFAULT NULL,
  `readability_score` int(3) DEFAULT NULL,
  `is_cornerstone` tinyint(1) DEFAULT 0,
  `is_robots_noindex` tinyint(1) DEFAULT 0,
  `is_robots_nofollow` tinyint(1) DEFAULT 0,
  `is_robots_noarchive` tinyint(1) DEFAULT 0,
  `is_robots_noimageindex` tinyint(1) DEFAULT 0,
  `is_robots_nosnippet` tinyint(1) DEFAULT 0,
  `twitter_title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_image` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_image_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_image_source` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_graph_title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_graph_description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_graph_image` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_graph_image_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_graph_image_source` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_graph_image_meta` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_count` int(11) DEFAULT NULL,
  `incoming_link_count` int(11) DEFAULT NULL,
  `prominent_words_version` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blog_id` bigint(20) NOT NULL DEFAULT 1,
  `language` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schema_page_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schema_article_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_ancestors` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `object_type_and_sub_type` (`object_type`,`object_sub_type`),
  KEY `permalink_hash` (`permalink_hash`),
  KEY `object_id_and_type` (`object_id`,`object_type`),
  KEY `subpages` (`post_parent`,`object_type`,`post_status`,`object_id`)
) ENGINE=MyISAM AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_yoast_indexable`
#
INSERT INTO `league_yoast_indexable` ( `id`, `permalink`, `permalink_hash`, `object_id`, `object_type`, `object_sub_type`, `author_id`, `post_parent`, `title`, `description`, `breadcrumb_title`, `post_status`, `is_public`, `is_protected`, `has_public_posts`, `number_of_pages`, `canonical`, `primary_focus_keyword`, `primary_focus_keyword_score`, `readability_score`, `is_cornerstone`, `is_robots_noindex`, `is_robots_nofollow`, `is_robots_noarchive`, `is_robots_noimageindex`, `is_robots_nosnippet`, `twitter_title`, `twitter_image`, `twitter_description`, `twitter_image_id`, `twitter_image_source`, `open_graph_title`, `open_graph_description`, `open_graph_image`, `open_graph_image_id`, `open_graph_image_source`, `open_graph_image_meta`, `link_count`, `incoming_link_count`, `prominent_words_version`, `created_at`, `updated_at`, `blog_id`, `language`, `region`, `schema_page_type`, `schema_article_type`, `has_ancestors`) VALUES
(1, NULL, '33:afae19d6e6a05618b98fda8cc03c2aec', 0, 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, 'https://1.gravatar.com/avatar/?s=500&d=mm&r=g', NULL, NULL, 'gravatar-image', NULL, NULL, 'https://1.gravatar.com/avatar/?s=500&d=mm&r=g', NULL, 'gravatar-image', NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-02 19:02:05', 1, NULL, NULL, NULL, NULL, 0),
(2, NULL, '36:307cfb20016b1d929c9cac0e64e3991b', 5, 'post', 'page', 0, 0, NULL, NULL, 'Cookie Policy', 'draft', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-02 19:02:05', 1, NULL, NULL, NULL, NULL, 0),
(3, 'http://fullbrook-floor.vm/author/admin-league/', '59:6e0d0708d84ef54b19e0353b19430aa7', 1, 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, 'https://1.gravatar.com/avatar/dfe41dfa5a074ef646ac0c21df80de31?s=500&d=mm&r=g', NULL, NULL, 'gravatar-image', NULL, NULL, 'https://1.gravatar.com/avatar/dfe41dfa5a074ef646ac0c21df80de31?s=500&d=mm&r=g', NULL, 'gravatar-image', NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-22 16:26:01', 1, NULL, NULL, NULL, NULL, 0),
(4, NULL, '36:97687c2aeaaab8ef8b26fdd0fa1d6010', 3, 'post', 'page', 1, 0, NULL, NULL, 'Privacy Policy', 'draft', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-02 19:02:05', 1, NULL, NULL, NULL, NULL, 0),
(5, 'http://fullbrook-floor.vm/sample-page__trashed/', '47:83075e52b1d78e5543214203c16d5412', 2, 'post', 'page', 1, 0, NULL, NULL, 'Sample Page', 'trash', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-02 18:10:01', 1, NULL, NULL, NULL, NULL, 0),
(7, 'http://fullbrook-floor.vm/hello-world/', '51:402a563ec46fcaafb4219767bbc91721', 1, 'post', 'post', 1, 0, NULL, NULL, 'Hello world!', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-11 14:58:32', 1, NULL, NULL, NULL, NULL, 0),
(8, 'http://fullbrook-floor.vm/category/uncategorised/', '62:64bd1d663f62ba3a1283263cb0416d10', 1, 'term', 'category', NULL, NULL, NULL, NULL, 'Uncategorised', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-11 14:56:56', 1, NULL, NULL, NULL, NULL, 0),
(9, NULL, NULL, NULL, 'system-page', '404', NULL, NULL, 'Page not found %%sep%% %%sitename%%', NULL, 'Error 404: Page not found', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-23 19:21:22', 1, NULL, NULL, NULL, NULL, 0),
(10, NULL, NULL, NULL, 'system-page', 'search-result', NULL, NULL, 'You searched for %%searchphrase%% %%page%% %%sep%% %%sitename%%', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-23 19:21:22', 1, NULL, NULL, NULL, NULL, 0),
(11, NULL, NULL, NULL, 'date-archive', NULL, NULL, NULL, '%%date%% %%page%% %%sep%% %%sitename%%', '', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-23 19:21:22', 1, NULL, NULL, NULL, NULL, 0),
(12, 'http://fullbrook-floor.vm/', '26:c1a259dd434d1571d00dc36b4171d5f2', NULL, 'home-page', NULL, NULL, NULL, '%%sitename%% %%page%% %%sep%% %%sitedesc%%', 'Estate agents St. Albans', 'Home', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, '', '', '', '', NULL, NULL, NULL, NULL, NULL, '2020-07-02 17:34:39', '2020-07-02 18:00:55', 1, NULL, NULL, NULL, NULL, 0),
(13, 'http://fullbrook-floor.vm/', '39:379daf509b51ae8be1a35bfc368d39cb', 6, 'post', 'page', 1, 0, NULL, NULL, 'Home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 90, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '67', 'featured-image', '{"width":1024,"height":682,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","path":"\\/home\\/fullbrookandfloo\\/public_html\\/staging\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","size":"full","id":67,"alt":"","pixels":698368,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 17:58:10', '2020-07-22 16:26:01', 1, NULL, NULL, NULL, NULL, 0),
(14, 'http://fullbrook-floor.vm/buy-a-home/', '50:f669f5099d3f887918a37307b881e525', 8, 'post', 'page', 1, 0, NULL, NULL, 'Buy a home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 90, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2020-07-02 17:58:31', '2020-07-22 16:02:46', 1, NULL, NULL, NULL, NULL, 0),
(15, 'http://fullbrook-floor.vm/sell-your-home/', '54:c887e3640d99f1131a573aa3a53edfdc', 10, 'post', 'page', 1, 0, NULL, NULL, 'Sell your home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 90, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2020-07-02 17:58:42', '2020-07-22 16:02:59', 1, NULL, NULL, NULL, NULL, 0),
(16, 'http://fullbrook-floor.vm/sell-your-home/free-sales-valuations/', '63:39b85fecad9c4b49eefa0497c4f9a039', 12, 'post', 'page', 1, 10, NULL, NULL, 'Free sales valuations', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 60, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2020-07-02 17:58:56', '2020-07-07 20:56:26', 1, NULL, NULL, NULL, NULL, 1),
(17, 'http://fullbrook-floor.vm/about-us/', '48:1be04742e7310201181dee9ed88cc5f7', 14, 'post', 'page', 1, 0, NULL, NULL, 'About us', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2020-07-02 17:59:15', '2020-07-22 16:01:42', 1, NULL, NULL, NULL, NULL, 0),
(18, 'http://fullbrook-floor.vm/about-us/meet-the-team/', '62:b5f5fb9801047eb37730d0d64e49dcf1', 16, 'post', 'page', 1, 14, NULL, NULL, 'Meet Rene &#038; Rod', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2020-07-02 17:59:32', '2020-07-22 16:02:36', 1, NULL, NULL, NULL, NULL, 1),
(19, 'http://fullbrook-floor.vm/how-to-sell-a-home/', '58:689805d1cca47113f2bbbdc4645c94d8', 18, 'post', 'page', 1, 0, NULL, NULL, 'How to sell a home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 60, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2020-07-02 18:00:03', '2020-07-22 16:04:43', 1, NULL, NULL, NULL, NULL, 0),
(20, 'http://fullbrook-floor.vm/contact-us/', '37:7892e4b5207272b0062e534979c3a855', 20, 'post', 'page', 1, 0, NULL, NULL, 'Contact Us', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2020-07-02 18:00:17', '2020-07-11 12:04:28', 1, NULL, NULL, NULL, NULL, 0),
(21, 'http://fullbrook-floor.vm/resources/', '36:702147e6b7bca74501e27e3c08b2e1c8', NULL, 'post-type-archive', 'resources', NULL, NULL, '%%pt_plural%% Archive %%page%% %%sep%% %%sitename%%', '', 'Resources', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:07:14', '2020-07-02 18:07:14', 1, NULL, NULL, NULL, NULL, 0),
(22, 'http://fullbrook-floor.vm/23/', '29:4aea74c643b62d681d20dc0ed0e899c1', 23, 'post', 'nav_menu_item', 1, 0, NULL, NULL, '', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 0),
(23, 'http://fullbrook-floor.vm/24/', '29:ec966f07372635b4c70a7611c77e88aa', 24, 'post', 'nav_menu_item', 1, 0, NULL, NULL, '', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 0),
(24, 'http://fullbrook-floor.vm/25/', '29:0fb8d80c6fe75bf0e7dd84c4f800dd67', 25, 'post', 'nav_menu_item', 1, 14, NULL, NULL, '', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 1),
(25, 'http://fullbrook-floor.vm/26/', '29:7da0e60b61cfee18426e75c1e55bde7e', 26, 'post', 'nav_menu_item', 1, 0, NULL, NULL, 'Buy with us', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 0),
(26, 'http://fullbrook-floor.vm/27/', '29:995ce8455d5789cc4276d6ba2e11e5d6', 27, 'post', 'nav_menu_item', 1, 0, NULL, NULL, '', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 0),
(27, 'http://fullbrook-floor.vm/28/', '29:8eb81b8d26ab8d0462a9e5930889a6a5', 28, 'post', 'nav_menu_item', 1, 0, NULL, NULL, '', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 0),
(28, 'http://fullbrook-floor.vm/29/', '29:05bffa3cbe17c352055994b5ce955b4e', 29, 'post', 'nav_menu_item', 1, 0, NULL, NULL, 'Sell with us', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 0),
(29, 'http://fullbrook-floor.vm/30/', '29:8270f18685ff9afc8b62ea229b3f7ed6', 30, 'post', 'nav_menu_item', 1, 10, NULL, NULL, '', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 18:10:25', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 1),
(31, 'http://fullbrook-floor.vm/resource-categories/buying-a-home/', '60:6453267776c867cb6f8a36a055b3435e', 3, 'term', 'resource-categories', NULL, NULL, NULL, NULL, 'Buying a home', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:22:31', '2020-07-02 21:22:31', 1, NULL, NULL, NULL, NULL, 0),
(32, 'http://fullbrook-floor.vm/resource-categories/selling-your-home/', '64:13d094da6dbabf12e7d8bdfd3dee0294', 4, 'term', 'resource-categories', NULL, NULL, NULL, NULL, 'Selling your home', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:22:38', '2020-07-02 21:22:38', 1, NULL, NULL, NULL, NULL, 0),
(33, 'http://fullbrook-floor.vm/resource-categories/mortgages/', '56:0823a1d5b8361bde616f3d26c36cc496', 5, 'term', 'resource-categories', NULL, NULL, NULL, NULL, 'Mortgages', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:22:42', '2020-07-02 21:22:42', 1, NULL, NULL, NULL, NULL, 0),
(34, 'http://fullbrook-floor.vm/resource-categories/lettings/', '55:c55c12c3eea78e52c3570eecc1fd2db9', 6, 'term', 'resource-categories', NULL, NULL, NULL, NULL, 'Lettings', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:22:46', '2020-07-02 21:22:46', 1, NULL, NULL, NULL, NULL, 0),
(35, 'http://fullbrook-floor.vm/resource-categories/landlords/', '56:537526c3f365071c2175abff0850a29a', 7, 'term', 'resource-categories', NULL, NULL, NULL, NULL, 'Landlords', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:22:52', '2020-07-02 21:22:52', 1, NULL, NULL, NULL, NULL, 0),
(36, 'http://fullbrook-floor.vm/resource-categories/finance/', '54:26d4d2383deb087ce6bc1edebba0c6ea', 8, 'term', 'resource-categories', NULL, NULL, NULL, NULL, 'Finance', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:22:55', '2020-07-02 21:22:55', 1, NULL, NULL, NULL, NULL, 0),
(37, 'http://fullbrook-floor.vm/resource-categories/housing-market/', '61:3621f1e049a921f46a0f2614bae3bc8e', 9, 'term', 'resource-categories', NULL, NULL, NULL, NULL, 'Housing market', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:23:02', '2020-07-02 21:23:02', 1, NULL, NULL, NULL, NULL, 0),
(38, 'http://fullbrook-floor.vm/?p=32', '31:2b2e85c4fe36a52e664394316bf3c66d', 32, 'post', 'nav_menu_item', 1, 0, NULL, NULL, 'Help &#038; Advice', 'draft', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:23:59', '2020-07-02 21:23:59', 1, NULL, NULL, NULL, NULL, 0),
(39, 'http://fullbrook-floor.vm/help-advice/', '38:b7e9d55deb6c275739b12e7f3b8344ea', 33, 'post', 'nav_menu_item', 1, 0, NULL, NULL, 'Help &#038; Advice', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:26:20', '2020-07-15 17:06:41', 1, NULL, NULL, NULL, NULL, 0),
(40, 'http://fullbrook-floor.vm/help-advice/', '38:b7e9d55deb6c275739b12e7f3b8344ea', NULL, 'post-type-archive', 'help-advice', NULL, NULL, '%%pt_plural%% Archive %%page%% %%sep%% %%sitename%%', '', 'Help & Advice', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:26:36', '2020-07-02 21:26:36', 1, NULL, NULL, NULL, NULL, 0),
(41, 'http://fullbrook-floor.vm/help-advice-categories/buying-a-home/', '63:4ef649f0725b6e73e192b8e365473aa8', 10, 'term', 'help-advice-categories', NULL, NULL, NULL, NULL, 'Buying a home', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:27:31', '2020-07-02 21:27:31', 1, NULL, NULL, NULL, NULL, 0),
(42, 'http://fullbrook-floor.vm/help-advice-categories/selling-your-home/', '67:57ff79addfcd9963d970f5baf74e09ff', 11, 'term', 'help-advice-categories', NULL, NULL, NULL, NULL, 'Selling your home', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:27:37', '2020-07-02 21:27:37', 1, NULL, NULL, NULL, NULL, 0),
(43, 'http://fullbrook-floor.vm/help-advice-categories/mortgages/', '59:008daa4d067a49d09da8d34828d81420', 12, 'term', 'help-advice-categories', NULL, NULL, NULL, NULL, 'Mortgages', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:27:41', '2020-07-02 21:27:41', 1, NULL, NULL, NULL, NULL, 0),
(44, 'http://fullbrook-floor.vm/help-advice-categories/lettings/', '58:c70e6bf4ba1a5e392fd0bcc91721422f', 13, 'term', 'help-advice-categories', NULL, NULL, NULL, NULL, 'Lettings', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:27:45', '2020-07-02 21:27:45', 1, NULL, NULL, NULL, NULL, 0),
(45, 'http://fullbrook-floor.vm/help-advice-categories/landlords/', '59:9ecb2f6852ee0130dfe731410b9bd8d9', 14, 'term', 'help-advice-categories', NULL, NULL, NULL, NULL, 'Landlords', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:27:49', '2020-07-02 21:27:49', 1, NULL, NULL, NULL, NULL, 0),
(46, 'http://fullbrook-floor.vm/help-advice-categories/finance/', '57:1e261f40a2cb7bf6925bdbba01fb6216', 15, 'term', 'help-advice-categories', NULL, NULL, NULL, NULL, 'Finance', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:27:54', '2020-07-02 21:27:54', 1, NULL, NULL, NULL, NULL, 0),
(47, 'http://fullbrook-floor.vm/help-advice-categories/housing-market/', '64:db416896aeb1d21e819709aa37722a89', 16, 'term', 'help-advice-categories', NULL, NULL, NULL, NULL, 'Housing market', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-02 21:28:01', '2020-07-02 21:28:01', 1, NULL, NULL, NULL, NULL, 0),
(50, 'http://fullbrook-floor.vm/help-advice/help-advice-buying-a-home/', '64:68ebfe926aae5d1763d3f2901bc30dc7', 36, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice buying a home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', NULL, '122', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', '122', 'featured-image', '{"width":1200,"height":800,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","size":"full","id":122,"alt":"","pixels":960000,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:29:08', '2020-07-10 17:26:58', 1, NULL, NULL, NULL, NULL, 0),
(51, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/placeholder-images-image_large.png', '87:df7655331d8d4e357840497630e16f22', 37, 'post', 'attachment', 1, 36, NULL, NULL, 'placeholder-images-image_large', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/placeholder-images-image_large.png', NULL, '37', 'attachment-image', NULL, NULL, NULL, '37', 'attachment-image', NULL, NULL, NULL, NULL, '2020-07-02 21:30:14', '2020-07-02 22:30:36', 1, NULL, NULL, NULL, NULL, 1),
(52, 'http://fullbrook-floor.vm/help-advice/help-advice-finance/', '58:305926ea32a925ad685583cab862f22c', 38, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice finance', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '67', 'featured-image', '{"width":1024,"height":682,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","size":"full","id":67,"alt":"","pixels":698368,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:18', '2020-07-10 17:26:49', 1, NULL, NULL, NULL, NULL, 0),
(53, 'http://fullbrook-floor.vm/help-advice/help-advice-housing-market/', '65:fb91b528cf9d40a9869c449155ba6b3c', 39, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice housing market', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', NULL, '119', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', '119', 'featured-image', '{"width":2121,"height":1414,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","size":"full","id":119,"alt":"","pixels":2999094,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:23', '2020-07-10 17:26:41', 1, NULL, NULL, NULL, NULL, 0),
(54, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-your-home/', '68:df58b45461375d9c6ee182a8ed6e9a8f', 40, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling your home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852-scaled.jpg', NULL, '120', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852-scaled.jpg', '120', 'featured-image', '{"width":2560,"height":1706,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-546201852-scaled.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-546201852-scaled.jpg","size":"full","id":120,"alt":"","pixels":4367360,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:25', '2020-07-10 17:26:01', 1, NULL, NULL, NULL, NULL, 0),
(55, 'http://fullbrook-floor.vm/help-advice/help-advice-mortgages/', '60:8a27faaf60ba91cf173e1ffcec10c66a', 41, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice mortgages', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '67', 'featured-image', '{"width":1024,"height":682,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","size":"full","id":67,"alt":"","pixels":698368,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:25', '2020-07-10 17:26:14', 1, NULL, NULL, NULL, NULL, 0),
(56, 'http://fullbrook-floor.vm/help-advice/help-advice-lettings/', '59:488d9514e6c2b3d34a761caf182d416c', 42, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice lettings', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', NULL, '122', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', '122', 'featured-image', '{"width":1200,"height":800,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","size":"full","id":122,"alt":"","pixels":960000,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:25', '2020-07-10 17:26:25', 1, NULL, NULL, NULL, NULL, 0),
(57, 'http://fullbrook-floor.vm/help-advice/help-advice-landlords/', '60:78f31bfa00ac1f2305a03f5f35bd6728', 43, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice landlords', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', NULL, '121', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', '121', 'featured-image', '{"width":724,"height":483,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","size":"full","id":121,"alt":"","pixels":349692,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:25', '2020-07-10 17:26:35', 1, NULL, NULL, NULL, NULL, 0),
(58, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-you-home-4/', '69:9a63812f45c45e197db1eda1cc53de4e', 44, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling you home 4', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', NULL, '119', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', '119', 'featured-image', '{"width":2121,"height":1414,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","size":"full","id":119,"alt":"","pixels":2999094,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:26', '2020-07-11 13:23:07', 1, NULL, NULL, NULL, NULL, 0),
(59, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-your-home-3/', '70:94d508f2c1cbbe9ec2746f885b228e2a', 45, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling your home 3', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '67', 'featured-image', '{"width":1024,"height":682,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","size":"full","id":67,"alt":"","pixels":698368,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:26', '2020-07-10 17:25:01', 1, NULL, NULL, NULL, NULL, 0),
(60, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-your-home-2/', '70:5f2d7afabdbeb5c9f2117e82575c4e1e', 46, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling your home 2', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', NULL, '121', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', '121', 'featured-image', '{"width":724,"height":483,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","size":"full","id":121,"alt":"","pixels":349692,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-02 21:31:26', '2020-07-10 17:25:54', 1, NULL, NULL, NULL, NULL, 0),
(61, 'http://fullbrook-floor.vm/?post_type=acf-field-group&p=47', '57:ea1afb579391314fb69d8ddb52350f2c', 47, 'post', 'acf-field-group', 1, 0, NULL, NULL, 'Additional Site Options', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:22:00', '2020-07-15 16:58:43', 1, NULL, NULL, NULL, NULL, 0),
(62, 'http://fullbrook-floor.vm/?post_type=acf-field&p=48', '51:a72ff8492c2c8e6d55096d6b08994e68', 48, 'post', 'acf-field', 1, 47, NULL, NULL, 'Logo Options', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:23:10', '2020-07-03 13:23:10', 1, NULL, NULL, NULL, NULL, 1),
(63, 'http://fullbrook-floor.vm/?post_type=acf-field&p=49', '51:3b5115ac5a2f51e4776b0d18ad049172', 49, 'post', 'acf-field', 1, 47, NULL, NULL, 'Logo Horizontal', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:23:10', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(64, 'http://fullbrook-floor.vm/?post_type=acf-field&p=50', '51:eff14d5d0a209a67aa10495a6c961173', 50, 'post', 'acf-field', 1, 47, NULL, NULL, 'Logo Vertical', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:23:10', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(65, 'http://fullbrook-floor.vm/?post_type=acf-field&p=51', '51:1261910ccc7fdfa535423a1efb6dd356', 51, 'post', 'acf-field', 1, 47, NULL, NULL, 'Logo Horizontal White', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:23:10', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(66, 'http://fullbrook-floor.vm/?post_type=acf-field&p=52', '51:e4710213a8417deec96f8474e0eb69e9', 52, 'post', 'acf-field', 1, 47, NULL, NULL, 'Logo Vertical White', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:23:10', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(71, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-vertical.svg', '70:f4fd886eb86cc29f5140266759884227', 57, 'post', 'attachment', 1, 10, NULL, NULL, 'logo-vertical', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:30:38', '2020-07-07 21:11:30', 1, NULL, NULL, NULL, NULL, 1),
(72, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-vertical-white.svg', '76:f789afb2882acf54afe5d79dfa1eae15', 58, 'post', 'attachment', 1, 0, NULL, NULL, 'logo-vertical-white', 'inherit', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:31:05', '2020-07-03 13:31:05', 1, NULL, NULL, NULL, NULL, 0),
(73, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-horizontal.svg', '72:f6bfa76c48a522c959063a0115fd2958', 59, 'post', 'attachment', 1, 0, NULL, NULL, 'logo-horizontal', 'inherit', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:32:31', '2020-07-03 13:32:31', 1, NULL, NULL, NULL, NULL, 0),
(74, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-horizontal-white.svg', '78:1f7d7e4d1a388c6d5937abb0bc7b6716', 60, 'post', 'attachment', 1, 0, NULL, NULL, 'logo-horizontal-white', 'inherit', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:32:32', '2020-07-03 13:32:32', 1, NULL, NULL, NULL, NULL, 0),
(75, 'http://fullbrook-floor.vm/?post_type=acf-field-group&p=61', '57:250dfb368d73019ed9cf987fbccb6b45', 61, 'post', 'acf-field-group', 1, 0, NULL, NULL, 'Homepage Options', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:56:05', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 0),
(76, 'http://fullbrook-floor.vm/?post_type=acf-field&p=62', '51:fea54753ae59971d3f1871d4192a453f', 62, 'post', 'acf-field', 1, 61, NULL, NULL, 'Hero Message', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:58:19', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 1),
(77, 'http://fullbrook-floor.vm/?post_type=acf-field&p=63', '51:cdef7b835225de434dc31db931a36bfc', 63, 'post', 'acf-field', 1, 61, NULL, NULL, 'Hero Top Line', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:58:19', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 1),
(78, 'http://fullbrook-floor.vm/?post_type=acf-field&p=64', '51:0984ad06ef51ce409f8a5ce1fb7e3514', 64, 'post', 'acf-field', 1, 61, NULL, NULL, 'Hero Main Line', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 13:58:19', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 1),
(79, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '91:cc49b0bd86548e016f2edaa303574355', 67, 'post', 'attachment', 1, 6, NULL, NULL, 'istockphoto-1225367483-1024&#215;1024', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'attachment-image', NULL, NULL, NULL, '67', 'attachment-image', NULL, NULL, NULL, NULL, '2020-07-03 14:07:48', '2020-07-03 14:07:48', 1, NULL, NULL, NULL, NULL, 1),
(80, 'http://fullbrook-floor.vm/?post_type=acf-field-group&p=68', '57:2be3db42c86f5f1f5dbf288ff90e19c9', 68, 'post', 'acf-field-group', 1, 0, NULL, NULL, 'Page Options', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:02:33', '2020-07-11 14:33:12', 1, NULL, NULL, NULL, NULL, 0),
(81, 'http://fullbrook-floor.vm/?post_type=acf-field&p=69', '51:aa024114ddf04fe8eb40ab5868a0f79d', 69, 'post', 'acf-field', 1, 68, NULL, NULL, 'Hero', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:03:30', '2020-07-03 15:03:30', 1, NULL, NULL, NULL, NULL, 1),
(82, 'http://fullbrook-floor.vm/?post_type=acf-field&p=70', '51:2f0f08ba899e6638cb931be94493590b', 70, 'post', 'acf-field', 1, 68, NULL, NULL, 'Hero Heading', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:03:30', '2020-07-03 15:03:30', 1, NULL, NULL, NULL, NULL, 1),
(83, 'http://fullbrook-floor.vm/?post_type=acf-field&p=71', '51:0b2e0f932455c298cc2b524ff4b57f2c', 71, 'post', 'acf-field', 1, 68, NULL, NULL, 'Has Search Bar', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:03:30', '2020-07-03 15:03:30', 1, NULL, NULL, NULL, NULL, 1),
(84, 'http://fullbrook-floor.vm/?post_type=acf-field-group&p=83', '57:5dd2318887c05ebe293cdf97fbd8aa8e', 83, 'post', 'acf-field-group', 1, 0, NULL, NULL, 'Meet the team options', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:47:55', '2020-07-15 17:19:01', 1, NULL, NULL, NULL, NULL, 0),
(85, 'http://fullbrook-floor.vm/?post_type=acf-field&p=84', '51:3293074eb918f32201cf7c4162eabe10', 84, 'post', 'acf-field', 1, 83, NULL, NULL, 'Team Members', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:50:07', '2020-07-03 15:50:07', 1, NULL, NULL, NULL, NULL, 1),
(86, 'http://fullbrook-floor.vm/?post_type=acf-field&p=85', '51:3b64d9ec63b3b55d912eb9467f36ef69', 85, 'post', 'acf-field', 1, 84, NULL, NULL, 'Name', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:50:07', '2020-07-15 17:19:00', 1, NULL, NULL, NULL, NULL, 1),
(87, 'http://fullbrook-floor.vm/?post_type=acf-field&p=86', '51:c9bc8f27d928957d2cf1c28b7d9a38d8', 86, 'post', 'acf-field', 1, 84, NULL, NULL, 'Job Title', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:50:07', '2020-07-15 17:19:01', 1, NULL, NULL, NULL, NULL, 1),
(88, 'http://fullbrook-floor.vm/?post_type=acf-field&p=87', '51:417da7c1b9c15bd0776e624e2b71b75e', 87, 'post', 'acf-field', 1, 84, NULL, NULL, 'Phone Number', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:50:07', '2020-07-15 17:18:28', 1, NULL, NULL, NULL, NULL, 1),
(89, 'http://fullbrook-floor.vm/?post_type=acf-field&p=88', '51:8803c1fcc5079a29d79c22e574a08ec9', 88, 'post', 'acf-field', 1, 84, NULL, NULL, 'Email address', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:50:07', '2020-07-15 17:18:28', 1, NULL, NULL, NULL, NULL, 1),
(90, 'http://fullbrook-floor.vm/?post_type=acf-field&p=89', '51:29e6e94fd327b46ccca6026cdd467fcc', 89, 'post', 'acf-field', 1, 84, NULL, NULL, 'Profile Photo', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:50:07', '2020-07-15 17:18:28', 1, NULL, NULL, NULL, NULL, 1),
(91, 'http://fullbrook-floor.vm/?post_type=acf-field&p=90', '51:b2aa7e97534897bd09852005b27e59e7', 90, 'post', 'acf-field', 1, 84, NULL, NULL, 'Biography', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 15:50:08', '2020-07-15 17:18:29', 1, NULL, NULL, NULL, NULL, 1),
(92, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/profile-image.png', '70:462ccf38b33fd6384a0ce77d601aea70', 91, 'post', 'attachment', 1, 16, NULL, NULL, 'profile-image', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/profile-image.png', NULL, '91', 'attachment-image', NULL, NULL, NULL, '91', 'attachment-image', NULL, NULL, NULL, NULL, '2020-07-03 15:51:12', '2020-07-03 15:51:12', 1, NULL, NULL, NULL, NULL, 1),
(93, 'http://fullbrook-floor.vm/?post_type=acf-field&p=93', '51:ddc4af6db6df4909015d835f990e1fbc', 93, 'post', 'acf-field', 1, 47, NULL, NULL, 'Buckets', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 17:46:47', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(94, 'http://fullbrook-floor.vm/?post_type=acf-field&p=94', '51:a744bffd314d35f5b1dde84779b90e6b', 94, 'post', 'acf-field', 1, 47, NULL, NULL, 'Buckets', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 17:46:47', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(95, 'http://fullbrook-floor.vm/?post_type=acf-field&p=95', '51:ce4fd65b4d709fe6fa8af5bd9837cdc9', 95, 'post', 'acf-field', 1, 94, NULL, NULL, 'Title', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 17:46:47', '2020-07-03 17:46:47', 1, NULL, NULL, NULL, NULL, 1),
(96, 'http://fullbrook-floor.vm/?post_type=acf-field&p=96', '51:46d1c52a79e3388edf0fa2f88a2f1848', 96, 'post', 'acf-field', 1, 94, NULL, NULL, 'Intro', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 17:46:47', '2020-07-07 18:11:22', 1, NULL, NULL, NULL, NULL, 1),
(97, 'http://fullbrook-floor.vm/?post_type=acf-field&p=97', '51:0c7dc4ed0db0e99350d0106ef82a2716', 97, 'post', 'acf-field', 1, 94, NULL, NULL, 'Background Image', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 17:46:47', '2020-07-03 17:46:47', 1, NULL, NULL, NULL, NULL, 1),
(98, 'http://fullbrook-floor.vm/?post_type=acf-field&p=98', '51:456e0c990c2db9464bb8abd8c05c430e', 98, 'post', 'acf-field', 1, 94, NULL, NULL, 'Button Text', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 17:46:47', '2020-07-03 17:46:47', 1, NULL, NULL, NULL, NULL, 1),
(99, 'http://fullbrook-floor.vm/?post_type=acf-field&p=99', '51:4cf68854ec1fa9139b8e445e4f2f3f40', 99, 'post', 'acf-field', 1, 94, NULL, NULL, 'Button Link', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-03 17:46:47', '2020-07-03 17:46:47', 1, NULL, NULL, NULL, NULL, 1),
(100, 'http://fullbrook-floor.vm/?post_type=acf-field&p=101', '52:849753e8da6849aac117f1d37eaf5284', 101, 'post', 'acf-field', 1, 61, NULL, NULL, 'H1', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 16:52:17', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 1),
(101, 'http://fullbrook-floor.vm/?post_type=acf-field&p=102', '52:6a5d20feca46db1d8d09646f7b1bde2e', 102, 'post', 'acf-field', 1, 61, NULL, NULL, 'H1', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 16:52:17', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 1),
(102, 'http://fullbrook-floor.vm/?post_type=acf-field&p=109', '52:b9f952fef668969cc127fe4d54a37327', 109, 'post', 'acf-field', 1, 68, NULL, NULL, 'H1', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 20:57:27', '2020-07-07 20:57:27', 1, NULL, NULL, NULL, NULL, 1),
(103, 'http://fullbrook-floor.vm/?post_type=acf-field&p=110', '52:2eb01e57bcc439cbbad00ee31f26120c', 110, 'post', 'acf-field', 1, 68, NULL, NULL, 'H1', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 20:57:27', '2020-07-07 20:57:27', 1, NULL, NULL, NULL, NULL, 1),
(104, 'http://fullbrook-floor.vm/?post_type=acf-field-group&p=113', '58:5b592fc4b1a4b326732cc7b2b627f1a1', 113, 'post', 'acf-field-group', 1, 0, NULL, NULL, 'Why Choose Us', 'trash', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:08:48', '2020-07-07 21:48:06', 1, NULL, NULL, NULL, NULL, 0),
(105, 'http://fullbrook-floor.vm/?post_type=acf-field&p=114', '52:41bc7bc6effdf3feb9847e29bf26b8cb', 114, 'post', 'acf-field', 1, 47, NULL, NULL, 'Why choose us', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:09:59', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(106, 'http://fullbrook-floor.vm/?post_type=acf-field&p=115', '52:665281925d5240a00407a3d26e5b1823', 115, 'post', 'acf-field', 1, 114, NULL, NULL, 'Image', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:09:59', '2020-07-07 21:09:59', 1, NULL, NULL, NULL, NULL, 1),
(107, 'http://fullbrook-floor.vm/?post_type=acf-field&p=116', '52:e751e07d8f7578f329b4e2e544f94831', 116, 'post', 'acf-field', 1, 114, NULL, NULL, 'TItle', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:10:00', '2020-07-07 21:10:00', 1, NULL, NULL, NULL, NULL, 1),
(108, 'http://fullbrook-floor.vm/?post_type=acf-field&p=117', '52:8273d975c82bc98b447d7dc91e841a3d', 117, 'post', 'acf-field', 1, 114, NULL, NULL, 'Text', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:10:00', '2020-07-07 21:10:00', 1, NULL, NULL, NULL, NULL, 1) ;
INSERT INTO `league_yoast_indexable` ( `id`, `permalink`, `permalink_hash`, `object_id`, `object_type`, `object_sub_type`, `author_id`, `post_parent`, `title`, `description`, `breadcrumb_title`, `post_status`, `is_public`, `is_protected`, `has_public_posts`, `number_of_pages`, `canonical`, `primary_focus_keyword`, `primary_focus_keyword_score`, `readability_score`, `is_cornerstone`, `is_robots_noindex`, `is_robots_nofollow`, `is_robots_noarchive`, `is_robots_noimageindex`, `is_robots_nosnippet`, `twitter_title`, `twitter_image`, `twitter_description`, `twitter_image_id`, `twitter_image_source`, `open_graph_title`, `open_graph_description`, `open_graph_image`, `open_graph_image_id`, `open_graph_image_source`, `open_graph_image_meta`, `link_count`, `incoming_link_count`, `prominent_words_version`, `created_at`, `updated_at`, `blog_id`, `language`, `region`, `schema_page_type`, `schema_article_type`, `has_ancestors`) VALUES
(109, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', '74:69b5de0ba0416f732d46887e8573f8a6', 119, 'post', 'attachment', 1, 18, NULL, NULL, 'Friends Arriving for Social Gathering', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', NULL, '119', 'attachment-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', '119', 'attachment-image', '{"width":2121,"height":1414,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","size":"full","id":119,"alt":"","pixels":2999094,"type":"image\\/jpeg"}', NULL, NULL, NULL, '2020-07-07 21:18:42', '2020-07-10 14:58:33', 1, NULL, NULL, NULL, NULL, 1),
(110, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852-scaled.jpg', '80:adf8d7194fe96b4beeed0c1ba8babfe2', 120, 'post', 'attachment', 1, 18, NULL, NULL, 'Young Family Collecting Keys To New Home From Realtor', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852-scaled.jpg', NULL, '120', 'attachment-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852-scaled.jpg', '120', 'attachment-image', '{"width":2560,"height":1706,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-546201852-scaled.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-546201852-scaled.jpg","size":"full","id":120,"alt":"","pixels":4367360,"type":"image\\/jpeg"}', NULL, NULL, NULL, '2020-07-07 21:19:35', '2020-07-15 16:47:59', 1, NULL, NULL, NULL, NULL, 1),
(111, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', '73:cf6f614251412c6c8b60b2571d2cd740', 121, 'post', 'attachment', 1, 18, NULL, NULL, 'Couple packing together', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', NULL, '121', 'attachment-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', '121', 'attachment-image', '{"width":724,"height":483,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","size":"full","id":121,"alt":"","pixels":349692,"type":"image\\/jpeg"}', NULL, NULL, NULL, '2020-07-07 21:20:18', '2020-07-10 14:58:33', 1, NULL, NULL, NULL, NULL, 1),
(112, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', '69:2259cf8e062d54d543d6b0ebd908b2a4', 122, 'post', 'attachment', 1, 18, NULL, NULL, '3122736571_f', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', NULL, '122', 'attachment-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', '122', 'attachment-image', '{"width":1200,"height":800,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","size":"full","id":122,"alt":"","pixels":960000,"type":"image\\/jpeg"}', NULL, NULL, NULL, '2020-07-07 21:26:05', '2020-07-10 14:58:33', 1, NULL, NULL, NULL, NULL, 1),
(113, 'http://fullbrook-floor.vm/?post_type=acf-field&p=123', '52:9366df53b1345e4b789a97c5b07358a5', 123, 'post', 'acf-field', 1, 47, NULL, NULL, 'Property Placeholder', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:26:40', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(114, 'http://fullbrook-floor.vm/?post_type=acf-field&p=124', '52:5adfb7b0fc877629c5f1b3e8053d212f', 124, 'post', 'acf-field', 1, 47, NULL, NULL, 'Property Placeholder', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:26:40', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(115, 'http://fullbrook-floor.vm/?post_type=acf-field&p=125', '52:7d73bf1891408527669a63a3606fa880', 125, 'post', 'acf-field', 1, 68, NULL, NULL, 'Page Options', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:45:33', '2020-07-07 21:45:33', 1, NULL, NULL, NULL, NULL, 1),
(116, 'http://fullbrook-floor.vm/?post_type=acf-field&p=126', '52:d2e21a739f7d70a0b787634614df39b4', 126, 'post', 'acf-field', 1, 68, NULL, NULL, 'Show Why Choose Us', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:45:33', '2020-07-07 21:46:53', 1, NULL, NULL, NULL, NULL, 1),
(117, 'http://fullbrook-floor.vm/?post_type=acf-field&p=127', '52:13a214fd96f175d90308dc668134c018', 127, 'post', 'acf-field', 1, 68, NULL, NULL, 'Show Buckets', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:45:33', '2020-07-07 21:46:53', 1, NULL, NULL, NULL, NULL, 1),
(118, 'http://fullbrook-floor.vm/?post_type=acf-field&p=130', '52:6832ca69f5e606c3112ddda2fb2c87a2', 130, 'post', 'acf-field', 1, 68, NULL, NULL, 'Show Team', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:46:53', '2020-07-07 21:46:53', 1, NULL, NULL, NULL, NULL, 1),
(119, 'http://fullbrook-floor.vm/?post_type=acf-field&p=132', '52:79e0c5e9a80fe70b14fa2e7d4e0956e4', 132, 'post', 'acf-field', 1, 47, NULL, NULL, 'Why Choose Us', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-07 21:48:36', '2020-07-07 21:48:36', 1, NULL, NULL, NULL, NULL, 1),
(121, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Property-Ombudsman-Logo.png', '80:bc683855e473bad9c975639e3b113101', 137, 'post', 'attachment', 1, 0, NULL, NULL, 'Property Ombudsman Logo', 'inherit', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Property-Ombudsman-Logo.png', NULL, '137', 'attachment-image', NULL, NULL, NULL, '137', 'attachment-image', NULL, NULL, NULL, NULL, '2020-07-10 13:04:51', '2020-07-10 13:04:51', 1, NULL, NULL, NULL, NULL, 0),
(122, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/property-mark.png', '70:fc41cd52116eef6256f8c5afc68a52b7', 138, 'post', 'attachment', 1, 0, NULL, NULL, 'property-mark', 'inherit', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/property-mark.png', NULL, '138', 'attachment-image', NULL, NULL, NULL, '138', 'attachment-image', NULL, NULL, NULL, NULL, '2020-07-10 13:08:46', '2020-07-10 13:08:46', 1, NULL, NULL, NULL, NULL, 0),
(123, 'http://fullbrook-floor.vm/?post_type=acf-field-group&p=139', '58:fd1c5c430b2228440a317f600c76c566', 139, 'post', 'acf-field-group', 1, 0, NULL, NULL, 'Guide To Selling Options', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-10 14:23:32', '2020-07-10 14:43:43', 1, NULL, NULL, NULL, NULL, 0),
(124, 'http://fullbrook-floor.vm/?post_type=acf-field&p=140', '52:b38b1998205705a53832a3d6249df8be', 140, 'post', 'acf-field', 1, 139, NULL, NULL, 'Guide Steps', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-10 14:25:11', '2020-07-10 14:43:43', 1, NULL, NULL, NULL, NULL, 1),
(125, 'http://fullbrook-floor.vm/?post_type=acf-field&p=141', '52:be8ecc1c2b2d9e39c6444af30c38aa8c', 141, 'post', 'acf-field', 1, 140, NULL, NULL, 'Image', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-10 14:25:11', '2020-07-10 14:42:43', 1, NULL, NULL, NULL, NULL, 1),
(126, 'http://fullbrook-floor.vm/?post_type=acf-field&p=142', '52:ec03543e232dc87b9060790d9b0be98b', 142, 'post', 'acf-field', 1, 140, NULL, NULL, 'Title', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-10 14:25:11', '2020-07-10 14:42:44', 1, NULL, NULL, NULL, NULL, 1),
(127, 'http://fullbrook-floor.vm/?post_type=acf-field&p=143', '52:7dc4750fc70f76391461f4737bd38706', 143, 'post', 'acf-field', 1, 140, NULL, NULL, 'Content', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-10 14:25:11', '2020-07-10 14:42:44', 1, NULL, NULL, NULL, NULL, 1),
(128, 'http://fullbrook-floor.vm/?post_type=acf-field&p=144', '52:262491f4f186c618ed747d045ee7bb8e', 144, 'post', 'acf-field', 1, 140, NULL, NULL, 'Is Highlighted', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-10 14:25:11', '2020-07-10 14:42:43', 1, NULL, NULL, NULL, NULL, 1),
(129, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-you-home-4-2/', '71:8745a6de2e83ba15450b3e2eff51d98b', 156, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling you home 4', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', NULL, '119', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', '119', 'featured-image', '{"width":2121,"height":1414,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","size":"full","id":119,"alt":"","pixels":2999094,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1, NULL, NULL, NULL, NULL, 0),
(130, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-your-home-3-2/', '72:56addc395b3a35c5906704c687a5d6af', 157, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling your home 3', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '67', 'featured-image', '{"width":1024,"height":682,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","size":"full","id":67,"alt":"","pixels":698368,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:04', 1, NULL, NULL, NULL, NULL, 0),
(131, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-your-home-2-2/', '72:c5d596c0669d39cf53b15b403446ef8f', 158, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling your home 2', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', NULL, '121', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', '121', 'featured-image', '{"width":724,"height":483,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","size":"full","id":121,"alt":"","pixels":349692,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:04', 1, NULL, NULL, NULL, NULL, 0),
(132, 'http://fullbrook-floor.vm/help-advice/help-advice-selling-your-home-4/', '70:a2a9b5d5968c69ebd63278883878ac9c', 159, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice selling your home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852-scaled.jpg', NULL, '120', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-546201852-scaled.jpg', '120', 'featured-image', '{"width":2560,"height":1706,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-546201852-scaled.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-546201852-scaled.jpg","size":"full","id":120,"alt":"","pixels":4367360,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:04', 1, NULL, NULL, NULL, NULL, 0),
(133, 'http://fullbrook-floor.vm/help-advice/help-advice-mortgages-2/', '62:6be69411db171e08f4b3f2b99c576689', 160, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice mortgages', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '67', 'featured-image', '{"width":1024,"height":682,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","size":"full","id":67,"alt":"","pixels":698368,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1, NULL, NULL, NULL, NULL, 0),
(134, 'http://fullbrook-floor.vm/help-advice/help-advice-lettings-2/', '61:aadb221042c97d367a878203c78e6503', 161, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice lettings', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', NULL, '122', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', '122', 'featured-image', '{"width":1200,"height":800,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","size":"full","id":122,"alt":"","pixels":960000,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1, NULL, NULL, NULL, NULL, 0),
(135, 'http://fullbrook-floor.vm/help-advice/help-advice-landlords-2/', '62:1304a197aa5dd36d80d9ab126abde935', 162, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice landlords', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', NULL, '121', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-481304898.jpg', '121', 'featured-image', '{"width":724,"height":483,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-481304898.jpg","size":"full","id":121,"alt":"","pixels":349692,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1, NULL, NULL, NULL, NULL, 0),
(136, 'http://fullbrook-floor.vm/help-advice/help-advice-housing-market-2/', '67:70189cf32546f799377b7b93ad225ac3', 163, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice housing market', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', NULL, '119', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/iStock-1078076954.jpg', '119', 'featured-image', '{"width":2121,"height":1414,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/iStock-1078076954.jpg","size":"full","id":119,"alt":"","pixels":2999094,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1, NULL, NULL, NULL, NULL, 0),
(137, 'http://fullbrook-floor.vm/help-advice/help-advice-finance-2/', '60:9c920656952525811121ac15521d8cdc', 164, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice finance', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', NULL, '67', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/istockphoto-1225367483-1024x1024-1.jpg', '67', 'featured-image', '{"width":1024,"height":682,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/istockphoto-1225367483-1024x1024-1.jpg","size":"full","id":67,"alt":"","pixels":698368,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1, NULL, NULL, NULL, NULL, 0),
(138, 'http://fullbrook-floor.vm/help-advice/help-advice-buying-a-home-2/', '66:c9aec281f9a15d849b7e9832fb6fa2f0', 165, 'post', 'help-advice', 1, 0, NULL, NULL, 'Help &#038; advice buying a home', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 30, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', NULL, '122', 'featured-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/3122736571_f.jpg', '122', 'featured-image', '{"width":1200,"height":800,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/3122736571_f.jpg","size":"full","id":122,"alt":"","pixels":960000,"type":"image\\/jpeg"}', 0, 0, NULL, '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1, NULL, NULL, NULL, NULL, 0),
(139, 'http://fullbrook-floor.vm/?post_type=acf-field&p=166', '52:e94f7fcb45551a6242673aaf5322d23a', 166, 'post', 'acf-field', 1, 68, NULL, NULL, 'Sidebar Logos', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-11 14:33:12', '2020-07-11 14:33:12', 1, NULL, NULL, NULL, NULL, 1),
(140, 'http://fullbrook-floor.vm/?post_type=acf-field&p=167', '52:2fb05abb972308c4d341ea84f6ff031d', 167, 'post', 'acf-field', 1, 68, NULL, NULL, 'Sidebar Logos', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-11 14:33:12', '2020-07-11 14:33:12', 1, NULL, NULL, NULL, NULL, 1),
(141, 'http://fullbrook-floor.vm/?post_type=acf-field&p=168', '52:36469210ad13e4f7bfb03bc519b3943e', 168, 'post', 'acf-field', 1, 167, NULL, NULL, 'Name', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-11 14:33:12', '2020-07-11 14:33:12', 1, NULL, NULL, NULL, NULL, 1),
(142, 'http://fullbrook-floor.vm/?post_type=acf-field&p=169', '52:6838559cef441a14cffc08aadfc71f5c', 169, 'post', 'acf-field', 1, 167, NULL, NULL, 'Image', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-11 14:33:12', '2020-07-11 14:33:12', 1, NULL, NULL, NULL, NULL, 1),
(143, 'http://fullbrook-floor.vm/?post_type=acf-field&p=170', '52:d15c9222bcafe8ce94ee2cceef078f05', 170, 'post', 'acf-field', 1, 167, NULL, NULL, 'Link', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-11 14:33:12', '2020-07-11 14:33:12', 1, NULL, NULL, NULL, NULL, 1),
(148, 'http://fullbrook-floor.vm/?post_type=acf-field&p=187', '52:db050c77d99df5e0d150049635e23da9', 187, 'post', 'acf-field', 1, 61, NULL, NULL, 'Hero Carousel', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:44:33', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 1),
(149, 'http://fullbrook-floor.vm/?post_type=acf-field&p=188', '52:0f6a6d15172c0ef08abc54ab3c667407', 188, 'post', 'acf-field', 1, 61, NULL, NULL, 'Hero Carousel', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:44:33', '2020-07-15 16:44:33', 1, NULL, NULL, NULL, NULL, 1),
(150, 'http://fullbrook-floor.vm/?post_type=acf-field&p=191', '52:a905117a5ee3737dd5d3f11080538362', 191, 'post', 'acf-field', 1, 47, NULL, NULL, 'Mover Logos', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:58:42', '2020-07-15 16:58:42', 1, NULL, NULL, NULL, NULL, 1),
(151, 'http://fullbrook-floor.vm/?post_type=acf-field&p=192', '52:453040fff7a82ce86ae01ee1de8c3a64', 192, 'post', 'acf-field', 1, 47, NULL, NULL, 'Mover Logo Headline', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:58:43', '2020-07-15 16:58:43', 1, NULL, NULL, NULL, NULL, 1),
(152, 'http://fullbrook-floor.vm/?post_type=acf-field&p=193', '52:638d7ea1582ab0e352ad8eb8f1b5e2af', 193, 'post', 'acf-field', 1, 47, NULL, NULL, 'Mover Logos', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:58:43', '2020-07-15 16:58:43', 1, NULL, NULL, NULL, NULL, 1),
(153, 'http://fullbrook-floor.vm/?post_type=acf-field&p=194', '52:5a0283f628c1c843b6abe1c89d9cbafb', 194, 'post', 'acf-field', 1, 193, NULL, NULL, 'Name', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:58:43', '2020-07-15 16:58:43', 1, NULL, NULL, NULL, NULL, 1),
(154, 'http://fullbrook-floor.vm/?post_type=acf-field&p=195', '52:376b03d1af6e7b10ef496dcb0822d989', 195, 'post', 'acf-field', 1, 193, NULL, NULL, 'Logo', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:58:43', '2020-07-15 16:58:43', 1, NULL, NULL, NULL, NULL, 1),
(155, 'http://fullbrook-floor.vm/?post_type=acf-field&p=196', '52:0046c23ee5f745587295693bc589aef7', 196, 'post', 'acf-field', 1, 193, NULL, NULL, 'Link', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 16:58:43', '2020-07-15 16:58:43', 1, NULL, NULL, NULL, NULL, 1),
(156, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Rightmove_logo_DEC2016.png', '79:b90061a52abfe30df65d38f599837c5c', 197, 'post', 'attachment', 1, 14, NULL, NULL, 'Rightmove_logo_DEC2016', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Rightmove_logo_DEC2016.png', NULL, '197', 'attachment-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Rightmove_logo_DEC2016.png', '197', 'attachment-image', '{"width":798,"height":174,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/Rightmove_logo_DEC2016.png","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/Rightmove_logo_DEC2016.png","size":"full","id":197,"alt":"","pixels":138852,"type":"image\\/png"}', NULL, NULL, NULL, '2020-07-15 17:01:52', '2020-07-15 17:02:11', 1, NULL, NULL, NULL, NULL, 1),
(157, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Zoopla-logo-Purple-RGBPNG.png', '82:fe72b6f17caaaedf9e3a34c4a83a22a4', 198, 'post', 'attachment', 1, 14, NULL, NULL, 'Zoopla-logo-Purple-RGBPNG', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Zoopla-logo-Purple-RGBPNG.png', NULL, '198', 'attachment-image', NULL, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/Zoopla-logo-Purple-RGBPNG.png', '198', 'attachment-image', '{"width":842,"height":243,"url":"http:\\/\\/fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/Zoopla-logo-Purple-RGBPNG.png","path":"C:\\\\wamp64\\\\www\\\\fullbrook-floor.vm\\/wp-content\\/uploads\\/2020\\/07\\/Zoopla-logo-Purple-RGBPNG.png","size":"full","id":198,"alt":"","pixels":204606,"type":"image\\/png"}', NULL, NULL, NULL, '2020-07-15 17:01:53', '2020-07-15 17:02:11', 1, NULL, NULL, NULL, NULL, 1),
(158, 'http://fullbrook-floor.vm/?post_type=acf-field&p=200', '52:d447e170866be64687b68338d1ae4c9f', 200, 'post', 'acf-field', 1, 84, NULL, NULL, 'Casual Photo', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-15 17:18:28', '2020-07-15 17:18:28', 1, NULL, NULL, NULL, NULL, 1),
(159, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D.jpg', '208:a9e96a133c5662c9e884f5f95dd75ec2', 201, 'post', 'attachment', 1, 16, NULL, NULL, 'profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D', 'inherit', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/profile-side-photo-of-smart-positive-charismatic-guy-look-at-copyspace-enjoy-summer-holidays-spend-free-time-with-his-friends-wear-casual-style-2AFH77D.jpg', NULL, '201', 'attachment-image', NULL, NULL, NULL, '201', 'attachment-image', NULL, NULL, NULL, NULL, '2020-07-15 17:20:24', '2020-07-15 17:20:24', 1, NULL, NULL, NULL, NULL, 1),
(160, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-update-v8.svg', '84:4da7d1ed92b8b622b46e98f3fe08c08d', 203, 'post', 'attachment', 1, 0, NULL, NULL, 'logo-update-v8', 'inherit', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-22 15:58:06', '2020-07-22 15:58:06', 1, NULL, NULL, NULL, NULL, 0),
(161, 'http://fullbrook-floor.vm/wp-content/uploads/2020/07/logo-update-v9.svg', '84:651c240ac69d5c3f013314c16cea31cb', 211, 'post', 'attachment', 1, 0, NULL, NULL, 'logo-update-v9', 'inherit', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-07-22 16:22:06', '2020-07-22 16:22:06', 1, NULL, NULL, NULL, NULL, 0) ;

#
# End of data contents of table `league_yoast_indexable`
# --------------------------------------------------------



#
# Delete any existing table `league_yoast_indexable_hierarchy`
#

DROP TABLE IF EXISTS `league_yoast_indexable_hierarchy`;


#
# Table structure of table `league_yoast_indexable_hierarchy`
#

CREATE TABLE `league_yoast_indexable_hierarchy` (
  `indexable_id` int(11) unsigned NOT NULL,
  `ancestor_id` int(11) unsigned NOT NULL,
  `depth` int(11) unsigned DEFAULT NULL,
  `blog_id` bigint(20) NOT NULL DEFAULT 1,
  PRIMARY KEY (`indexable_id`,`ancestor_id`),
  KEY `indexable_id` (`indexable_id`),
  KEY `ancestor_id` (`ancestor_id`),
  KEY `depth` (`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_yoast_indexable_hierarchy`
#
INSERT INTO `league_yoast_indexable_hierarchy` ( `indexable_id`, `ancestor_id`, `depth`, `blog_id`) VALUES
(16, 15, 1, 1),
(18, 17, 1, 1),
(24, 17, 1, 1),
(29, 15, 1, 1),
(63, 61, 1, 1),
(64, 61, 1, 1),
(65, 61, 1, 1),
(66, 61, 1, 1),
(71, 15, 1, 1),
(76, 75, 1, 1),
(77, 75, 1, 1),
(78, 75, 1, 1),
(86, 84, 2, 1),
(86, 85, 1, 1),
(87, 84, 2, 1),
(87, 85, 1, 1),
(88, 84, 2, 1),
(88, 85, 1, 1),
(89, 84, 2, 1),
(89, 85, 1, 1),
(90, 84, 2, 1),
(90, 85, 1, 1),
(91, 84, 2, 1),
(91, 85, 1, 1),
(93, 61, 1, 1),
(94, 61, 1, 1),
(96, 61, 2, 1),
(96, 94, 1, 1),
(100, 75, 1, 1),
(101, 75, 1, 1),
(105, 61, 1, 1),
(109, 19, 1, 1),
(110, 19, 1, 1),
(111, 19, 1, 1),
(112, 19, 1, 1),
(113, 61, 1, 1),
(114, 61, 1, 1),
(116, 80, 1, 1),
(117, 80, 1, 1),
(124, 123, 1, 1),
(125, 123, 2, 1),
(125, 124, 1, 1),
(126, 123, 2, 1),
(126, 124, 1, 1),
(127, 123, 2, 1),
(127, 124, 1, 1),
(128, 123, 2, 1),
(128, 124, 1, 1),
(156, 17, 1, 1),
(157, 17, 1, 1) ;

#
# End of data contents of table `league_yoast_indexable_hierarchy`
# --------------------------------------------------------



#
# Delete any existing table `league_yoast_migrations`
#

DROP TABLE IF EXISTS `league_yoast_migrations`;


#
# Table structure of table `league_yoast_migrations`
#

CREATE TABLE `league_yoast_migrations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_league_yoast_migrations_version` (`version`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_yoast_migrations`
#
INSERT INTO `league_yoast_migrations` ( `id`, `version`) VALUES
(1, '20171228151840'),
(2, '20171228151841'),
(3, '20190529075038'),
(4, '20191011111109'),
(5, '20200408101900'),
(6, '20200420073606'),
(7, '20200428123747'),
(8, '20200428194858'),
(9, '20200429105310'),
(10, '20200430075614'),
(11, '20200430150130'),
(12, '20200507054848'),
(13, '20200513133401'),
(14, '20200609154515'),
(15, '20200702141921') ;

#
# End of data contents of table `league_yoast_migrations`
# --------------------------------------------------------



#
# Delete any existing table `league_yoast_primary_term`
#

DROP TABLE IF EXISTS `league_yoast_primary_term`;


#
# Table structure of table `league_yoast_primary_term`
#

CREATE TABLE `league_yoast_primary_term` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(11) unsigned NOT NULL,
  `term_id` int(11) unsigned NOT NULL,
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blog_id` bigint(20) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `post_taxonomy` (`post_id`,`taxonomy`),
  KEY `post_term` (`post_id`,`term_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_yoast_primary_term`
#
INSERT INTO `league_yoast_primary_term` ( `id`, `post_id`, `term_id`, `taxonomy`, `created_at`, `updated_at`, `blog_id`) VALUES
(1, 36, 10, 'help-advice-categories', '2020-07-02 21:30:36', '2020-07-10 17:26:58', 1),
(2, 38, 10, 'help-advice-categories', '2020-07-02 21:31:18', '2020-07-10 17:26:49', 1),
(3, 39, 10, 'help-advice-categories', '2020-07-02 21:31:23', '2020-07-10 17:26:41', 1),
(4, 40, 10, 'help-advice-categories', '2020-07-02 21:31:25', '2020-07-10 17:26:01', 1),
(5, 41, 10, 'help-advice-categories', '2020-07-02 21:31:25', '2020-07-10 17:26:14', 1),
(6, 42, 10, 'help-advice-categories', '2020-07-02 21:31:25', '2020-07-10 17:26:25', 1),
(7, 43, 10, 'help-advice-categories', '2020-07-02 21:31:25', '2020-07-10 17:26:35', 1),
(8, 44, 10, 'help-advice-categories', '2020-07-02 21:31:26', '2020-07-11 13:23:07', 1),
(9, 45, 10, 'help-advice-categories', '2020-07-02 21:31:26', '2020-07-10 17:25:01', 1),
(10, 46, 10, 'help-advice-categories', '2020-07-02 21:31:26', '2020-07-10 17:25:54', 1),
(11, 156, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1),
(12, 157, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:04', 1),
(13, 158, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:04', 1),
(14, 159, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:04', 1),
(15, 160, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1),
(16, 161, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1),
(17, 162, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1),
(18, 163, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1),
(19, 164, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1),
(20, 165, 10, 'help-advice-categories', '2020-07-11 13:58:52', '2020-07-11 13:59:05', 1) ;

#
# End of data contents of table `league_yoast_primary_term`
# --------------------------------------------------------



#
# Delete any existing table `league_yoast_seo_links`
#

DROP TABLE IF EXISTS `league_yoast_seo_links`;


#
# Table structure of table `league_yoast_seo_links`
#

CREATE TABLE `league_yoast_seo_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_id` bigint(20) unsigned NOT NULL,
  `target_post_id` bigint(20) unsigned NOT NULL,
  `type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `link_direction` (`post_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_yoast_seo_links`
#

#
# End of data contents of table `league_yoast_seo_links`
# --------------------------------------------------------



#
# Delete any existing table `league_yoast_seo_meta`
#

DROP TABLE IF EXISTS `league_yoast_seo_meta`;


#
# Table structure of table `league_yoast_seo_meta`
#

CREATE TABLE `league_yoast_seo_meta` (
  `object_id` bigint(20) unsigned NOT NULL,
  `internal_link_count` int(10) unsigned DEFAULT NULL,
  `incoming_link_count` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `object_id` (`object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#
# Data contents of table `league_yoast_seo_meta`
#
INSERT INTO `league_yoast_seo_meta` ( `object_id`, `internal_link_count`, `incoming_link_count`) VALUES
(4, 0, 0),
(6, 0, 0),
(8, 0, 0),
(10, 0, 0),
(12, 0, 0),
(14, 0, 0),
(16, 0, 0),
(18, 0, 0),
(20, 0, 0),
(31, 0, 0),
(34, 0, 0),
(35, 0, 0),
(36, 0, 0),
(38, 0, 0),
(39, 0, 0),
(40, 0, 0),
(41, 0, 0),
(42, 0, 0),
(43, 0, 0),
(44, 0, 0),
(45, 0, 0),
(46, 0, 0),
(53, 0, 0),
(54, 0, 0),
(55, 0, 0),
(56, 0, 0),
(74, 0, 0),
(75, 0, 0),
(136, 0, 0),
(156, 0, 0),
(157, 0, 0),
(158, 0, 0),
(159, 0, 0),
(160, 0, 0),
(161, 0, 0),
(162, 0, 0),
(163, 0, 0),
(164, 0, 0),
(165, 0, 0),
(171, 0, 0),
(172, 0, 0),
(173, 0, 0),
(174, 0, 0) ;

#
# End of data contents of table `league_yoast_seo_meta`
# --------------------------------------------------------

#
# Add constraints back in and apply any alter data queries.
#

