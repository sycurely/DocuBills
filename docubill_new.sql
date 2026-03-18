-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 17, 2026 at 07:56 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `docubill_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('docubills-cache-setting.account_holder', 's:0:\"\";', 1771284296),
('docubills-cache-setting.account_holder_name', 's:0:\"\";', 1771284296),
('docubills-cache-setting.account_name', 's:0:\"\";', 1771284296),
('docubills-cache-setting.account_number', 's:0:\"\";', 1771284296),
('docubills-cache-setting.app_logo_url', 's:0:\"\";', 1771336040),
('docubills-cache-setting.bank_account_holder', 's:0:\"\";', 1771339087),
('docubills-cache-setting.bank_account_name', 's:13:\"Habiba Hassan\";', 1771337160),
('docubills-cache-setting.bank_account_number', 's:11:\"12302564952\";', 1771337160),
('docubills-cache-setting.bank_additional_info', 's:6:\"Tesint\";', 1771337160),
('docubills-cache-setting.bank_details', 's:0:\"\";', 1771339087),
('docubills-cache-setting.bank_holder_name', 's:0:\"\";', 1771284296),
('docubills-cache-setting.bank_iban', 's:20:\"PK8540ABL12302564952\";', 1771337160),
('docubills-cache-setting.bank_name', 's:11:\"Allied Bank\";', 1771337160),
('docubills-cache-setting.bank_payment_instructions', 's:0:\"\";', 1771339087),
('docubills-cache-setting.bank_routing', 's:0:\"\";', 1771337160),
('docubills-cache-setting.bank_routing_code', 's:0:\"\";', 1771339087),
('docubills-cache-setting.bank_swift', 's:0:\"\";', 1771337160),
('docubills-cache-setting.beneficiary_name', 's:0:\"\";', 1771284296),
('docubills-cache-setting.company_address', 's:0:\"\";', 1771339087),
('docubills-cache-setting.company_email', 's:0:\"\";', 1771339087),
('docubills-cache-setting.company_logo', 's:0:\"\";', 1771339087),
('docubills-cache-setting.company_name', 's:9:\"DocuBills\";', 1771336040),
('docubills-cache-setting.company_phone', 's:0:\"\";', 1771339087),
('docubills-cache-setting.cron_secret', 's:0:\"\";', 1771265423),
('docubills-cache-setting.currency_code', 's:3:\"USD\";', 1771338996),
('docubills-cache-setting.currency_symbol', 's:0:\"\";', 1771337167),
('docubills-cache-setting.email_from_address', 's:0:\"\";', 1771265423),
('docubills-cache-setting.email_from_name', 's:9:\"DocuBills\";', 1771265423),
('docubills-cache-setting.gst_number', 's:0:\"\";', 1771339087),
('docubills-cache-setting.iban', 's:0:\"\";', 1771284296),
('docubills-cache-setting.invoice_email_reminders', 's:622:\"[{\"id\":\"before_due\",\"name\":\"Before due date\",\"enabled\":false,\"direction\":\"before\",\"days\":0,\"offset_days\":0},{\"id\":\"on_due\",\"name\":\"On due date\",\"enabled\":true,\"direction\":\"on\",\"days\":0,\"offset_days\":0},{\"id\":\"after_3\",\"name\":\"3 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":3,\"offset_days\":3},{\"id\":\"after_7\",\"name\":\"7 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":7,\"offset_days\":7},{\"id\":\"after_14\",\"name\":\"14 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":14,\"offset_days\":14},{\"id\":\"after_21\",\"name\":\"21 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":21,\"offset_days\":21}]\";', 1771339087),
('docubills-cache-setting.invoice_footer', 's:0:\"\";', 1771339087),
('docubills-cache-setting.invoice_prefix', 's:3:\"INV\";', 1771339184),
('docubills-cache-setting.payment_instructions', 's:0:\"\";', 1771284296),
('docubills-cache-setting.payment_method_details', 's:0:\"\";', 1771339087),
('docubills-cache-setting.payment_methods', 's:0:\"\";', 1771339087),
('docubills-cache-setting.reminder_after_14', 's:1:\"0\";', 1771265423),
('docubills-cache-setting.reminder_after_21', 's:1:\"0\";', 1771265423),
('docubills-cache-setting.reminder_after_3', 's:1:\"0\";', 1771265423),
('docubills-cache-setting.reminder_after_7', 's:1:\"0\";', 1771265423),
('docubills-cache-setting.reminder_before_due', 's:1:\"0\";', 1771265423),
('docubills-cache-setting.reminder_on_due', 's:1:\"0\";', 1771265423),
('docubills-cache-setting.routing_code', 's:0:\"\";', 1771339087),
('docubills-cache-setting.session_timeout', 's:3:\"120\";', 1771265423),
('docubills-cache-setting.smtp_host', 's:0:\"\";', 1771265423),
('docubills-cache-setting.smtp_password', 's:0:\"\";', 1771265423),
('docubills-cache-setting.smtp_port', 's:3:\"587\";', 1771265423),
('docubills-cache-setting.smtp_username', 's:0:\"\";', 1771265423),
('docubills-cache-setting.sort_code', 's:0:\"\";', 1771339087),
('docubills-cache-setting.stripe_publishable_key', 's:0:\"\";', 1771339184),
('docubills-cache-setting.stripe_secret_key', 's:0:\"\";', 1771339184),
('docubills-cache-setting.swift', 's:0:\"\";', 1771339087),
('docubills-cache-setting.swift_bic', 's:0:\"\";', 1771339087),
('docubills-cache-setting.test_mode', 's:1:\"0\";', 1771339184);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `representative` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `gst_hst` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `company_name`, `representative`, `phone`, `email`, `address`, `gst_hst`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Dummy Client 1', 'Rep 1', '555000001', 'dummyclient1@example.com', '123 Test Street, City 1', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(2, 'Dummy Client 2', 'Rep 2', '555000002', 'dummyclient2@example.com', '123 Test Street, City 2', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(3, 'Dummy Client 3', 'Rep 3', '555000003', 'dummyclient3@example.com', '123 Test Street, City 3', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(4, 'Dummy Client 4', 'Rep 4', '555000004', 'dummyclient4@example.com', '123 Test Street, City 4', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(5, 'Dummy Client 5', 'Rep 5', '555000005', 'dummyclient5@example.com', '123 Test Street, City 5', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(6, 'Dummy Client 6', 'Rep 6', '555000006', 'dummyclient6@example.com', '123 Test Street, City 6', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(7, 'Dummy Client 7', 'Rep 7', '555000007', 'dummyclient7@example.com', '123 Test Street, City 7', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(8, 'Dummy Client 8', 'Rep 8', '555000008', 'dummyclient8@example.com', '123 Test Street, City 8', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(9, 'Dummy Client 9', 'Rep 9', '555000009', 'dummyclient9@example.com', '123 Test Street, City 9', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(10, 'Dummy Client 10', 'Rep 10', '555000010', 'dummyclient10@example.com', '123 Test Street, City 10', NULL, 'seed:dashboard', 2, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(11, 'Test1 Company', 'Habiba Hassan', '4456421489', 'mrshabibahassan@gmail.com', 'abc hose 729 NY, US', NULL, NULL, 1, '2026-02-16 17:26:22', '2026-02-16 17:26:22', NULL),
(12, 'TestCompany 2', 'Nora US', '4456321459', 'mrshabibahassan@gmail.com', 'house 829 NY street, US', NULL, 'Testing', 1, '2026-02-16 17:35:04', '2026-02-16 17:35:04', NULL),
(13, 'Habiba Hassan', 'IT Solutions', '4566625471', 'mrshabibahassan@gmail.com', '58 NY Street 1 US', '54613129', 'Testing Clients', 1, '2026-02-16 22:23:50', '2026-02-16 22:23:50', NULL),
(14, 'Amilia James', 'Web Development Labs', '44597862130', 'mrshabibahassan@gmail.com', '985 NY street 2 US', NULL, NULL, 1, '2026-02-17 13:39:44', '2026-02-17 13:39:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `html_content` longtext DEFAULT NULL,
  `design_json` longtext DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `assigned_notification_type` varchar(120) DEFAULT NULL,
  `cc_emails` text DEFAULT NULL,
  `bcc_emails` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_name`, `subject`, `body`, `html_content`, `design_json`, `category`, `assigned_notification_type`, `cc_emails`, `bcc_emails`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Test1 Company', 'Testing', '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional //EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">\r\n<head>\r\n<!--[if gte mso 9]>\r\n<xml>\r\n  <o:OfficeDocumentSettings>\r\n    <o:AllowPNG/>\r\n    <o:PixelsPerInch>96</o:PixelsPerInch>\r\n  </o:OfficeDocumentSettings>\r\n</xml>\r\n<![endif]-->\r\n  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\r\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n  <meta name=\"x-apple-disable-message-reformatting\">\r\n  <!--[if !mso]><!--><meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"><!--<![endif]-->\r\n  \r\n  \r\n    <style type=\"text/css\">\r\n      \r\n      @media only screen and (min-width: 520px) {\r\n        .u-row {\r\n          width: 500px !important;\r\n        }\r\n\r\n        .u-row .u-col {\r\n          vertical-align: top;\r\n        }\r\n\r\n        \r\n            .u-row .u-col-100 {\r\n              width: 500px !important;\r\n            }\r\n          \r\n      }\r\n\r\n      @media only screen and (max-width: 520px) {\r\n        .u-row-container {\r\n          max-width: 100% !important;\r\n          padding-left: 0px !important;\r\n          padding-right: 0px !important;\r\n        }\r\n\r\n        .u-row {\r\n          width: 100% !important;\r\n        }\r\n\r\n        .u-row .u-col {\r\n          display: block !important;\r\n          width: 100% !important;\r\n          min-width: 320px !important;\r\n          max-width: 100% !important;\r\n        }\r\n\r\n        .u-row .u-col > div {\r\n          margin: 0 auto;\r\n        }\r\n\r\n}\r\n    \r\nbody{margin:0;padding:0}table,td,tr{border-collapse:collapse;vertical-align:top}.ie-container table,.mso-container table{table-layout:fixed}*{line-height:inherit}a[x-apple-data-detectors=true]{color:inherit!important;text-decoration:none!important}\r\n\r\n\r\ntable, td { color: #000000; } #u_body a { color: #0000ee; text-decoration: underline; }\r\n    </style>\r\n  \r\n  \r\n\r\n</head>\r\n\r\n<body class=\"clean-body u_body\" style=\"margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #F7F8F9;color: #000000\">\r\n  <!--[if IE]><div class=\"ie-container\"><![endif]-->\r\n  <!--[if mso]><div class=\"mso-container\"><![endif]-->\r\n  <table role=\"presentation\" id=\"u_body\" style=\"border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #F7F8F9;width:100%\" cellpadding=\"0\" cellspacing=\"0\">\r\n  <tbody>\r\n  <tr style=\"vertical-align: top\">\r\n    <td style=\"word-break: break-word;border-collapse: collapse !important;vertical-align: top\">\r\n    <!--[if (mso)|(IE)]><table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"center\" style=\"background-color: #F7F8F9;\"><![endif]-->\r\n    \r\n  \r\n  \r\n<div class=\"u-row-container\" style=\"padding: 0px;background-color: transparent\">\r\n  <div class=\"u-row\" style=\"margin: 0 auto;min-width: 320px;max-width: 500px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;\">\r\n    <div style=\"border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;\">\r\n      <!--[if (mso)|(IE)]><table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding: 0px;background-color: transparent;\" align=\"center\"><table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:500px;\"><tr style=\"background-color: transparent;\"><![endif]-->\r\n      \r\n<!--[if (mso)|(IE)]><td align=\"center\" width=\"500\" style=\"width: 500px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;\" valign=\"top\"><![endif]-->\r\n<div class=\"u-col u-col-100\" style=\"max-width: 320px;min-width: 500px;display: table-cell;vertical-align: top;\">\r\n  <div style=\"height: 100%;width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;\">\r\n  <!--[if (!mso)&(!IE)]><!--><div style=\"box-sizing: border-box; height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;\"><!--<![endif]-->\r\n  \r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n  <tr>\r\n    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">\r\n      \r\n      <img align=\"center\" border=\"0\" src=\"https://assets.unlayer.com/projects/0/1771262054904-WhatsApp%20Image%202026-02-04%20at%207.37.34%20PM.jpeg?w=393.6px\" alt=\"\" title=\"\" style=\"outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 41%;max-width: 196.8px;\" width=\"196.8\"/>\r\n      \r\n    </td>\r\n  </tr>\r\n</table>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n  <!--[if mso]><table role=\"presentation\" width=\"100%\"><tr><td><![endif]-->\r\n    <h2 style=\"margin: 0px; line-height: 140%; text-align: center; word-wrap: break-word; font-size: 20px; font-weight: 400;\"><span><span>Invoice Status</span></span></h2>\r\n  <!--[if mso]></td></tr></table><![endif]-->\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n  <div>\r\n    <strong>Hello, world!</strong>\r\n  </div>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n  <!--[if mso]><style>.v-button {background: transparent !important;}</style><![endif]-->\r\n<div align=\"center\">\r\n  <!--[if mso]><v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com:office:word\" href=\"\" style=\"height:37px; v-text-anchor:middle; width:96px;\" arcsize=\"11%\"  stroke=\"f\" fillcolor=\"#0834a1\"><w:anchorlock/><center style=\"color:#FFFFFF;\"><![endif]-->\r\n    <a href=\"\" target=\"_blank\" class=\"v-button\" style=\"box-sizing: border-box;display: inline-block;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #FFFFFF; background-color: #0834a1; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;font-size: 14px;\">\r\n      <span style=\"display:block;padding:10px 20px;line-height:120%;\"><span><span>Pay Now</span></span></span>\r\n    </a>\r\n    <!--[if mso]></center></v:roundrect><![endif]-->\r\n</div>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n<div align=\"center\" style=\"direction: ltr;\" aria-label=\"social\">\r\n  <div style=\"display: table; max-width:-5px;\">\r\n  <!--[if (mso)|(IE)]><table role=\"presentation\" width=\"-5\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"border-collapse:collapse;\" align=\"center\"><table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:collapse; mso-table-lspace: 0pt;mso-table-rspace: 0pt; width:-5px;\"><tr><![endif]-->\r\n  \r\n    \r\n    \r\n    <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->\r\n  </div>\r\n</div>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->\r\n  </div>\r\n</div>\r\n<!--[if (mso)|(IE)]></td><![endif]-->\r\n      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->\r\n    </div>\r\n  </div>\r\n  </div>\r\n  \r\n\r\n\r\n    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->\r\n    </td>\r\n  </tr>\r\n  </tbody>\r\n  </table>\r\n  <!--[if mso]></div><![endif]-->\r\n  <!--[if IE]></div><![endif]-->\r\n</body>\r\n\r\n</html>', '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional //EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">\r\n<head>\r\n<!--[if gte mso 9]>\r\n<xml>\r\n  <o:OfficeDocumentSettings>\r\n    <o:AllowPNG/>\r\n    <o:PixelsPerInch>96</o:PixelsPerInch>\r\n  </o:OfficeDocumentSettings>\r\n</xml>\r\n<![endif]-->\r\n  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\r\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n  <meta name=\"x-apple-disable-message-reformatting\">\r\n  <!--[if !mso]><!--><meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"><!--<![endif]-->\r\n  \r\n  \r\n    <style type=\"text/css\">\r\n      \r\n      @media only screen and (min-width: 520px) {\r\n        .u-row {\r\n          width: 500px !important;\r\n        }\r\n\r\n        .u-row .u-col {\r\n          vertical-align: top;\r\n        }\r\n\r\n        \r\n            .u-row .u-col-100 {\r\n              width: 500px !important;\r\n            }\r\n          \r\n      }\r\n\r\n      @media only screen and (max-width: 520px) {\r\n        .u-row-container {\r\n          max-width: 100% !important;\r\n          padding-left: 0px !important;\r\n          padding-right: 0px !important;\r\n        }\r\n\r\n        .u-row {\r\n          width: 100% !important;\r\n        }\r\n\r\n        .u-row .u-col {\r\n          display: block !important;\r\n          width: 100% !important;\r\n          min-width: 320px !important;\r\n          max-width: 100% !important;\r\n        }\r\n\r\n        .u-row .u-col > div {\r\n          margin: 0 auto;\r\n        }\r\n\r\n}\r\n    \r\nbody{margin:0;padding:0}table,td,tr{border-collapse:collapse;vertical-align:top}.ie-container table,.mso-container table{table-layout:fixed}*{line-height:inherit}a[x-apple-data-detectors=true]{color:inherit!important;text-decoration:none!important}\r\n\r\n\r\ntable, td { color: #000000; } #u_body a { color: #0000ee; text-decoration: underline; }\r\n    </style>\r\n  \r\n  \r\n\r\n</head>\r\n\r\n<body class=\"clean-body u_body\" style=\"margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #F7F8F9;color: #000000\">\r\n  <!--[if IE]><div class=\"ie-container\"><![endif]-->\r\n  <!--[if mso]><div class=\"mso-container\"><![endif]-->\r\n  <table role=\"presentation\" id=\"u_body\" style=\"border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #F7F8F9;width:100%\" cellpadding=\"0\" cellspacing=\"0\">\r\n  <tbody>\r\n  <tr style=\"vertical-align: top\">\r\n    <td style=\"word-break: break-word;border-collapse: collapse !important;vertical-align: top\">\r\n    <!--[if (mso)|(IE)]><table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"center\" style=\"background-color: #F7F8F9;\"><![endif]-->\r\n    \r\n  \r\n  \r\n<div class=\"u-row-container\" style=\"padding: 0px;background-color: transparent\">\r\n  <div class=\"u-row\" style=\"margin: 0 auto;min-width: 320px;max-width: 500px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;\">\r\n    <div style=\"border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;\">\r\n      <!--[if (mso)|(IE)]><table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding: 0px;background-color: transparent;\" align=\"center\"><table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:500px;\"><tr style=\"background-color: transparent;\"><![endif]-->\r\n      \r\n<!--[if (mso)|(IE)]><td align=\"center\" width=\"500\" style=\"width: 500px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;\" valign=\"top\"><![endif]-->\r\n<div class=\"u-col u-col-100\" style=\"max-width: 320px;min-width: 500px;display: table-cell;vertical-align: top;\">\r\n  <div style=\"height: 100%;width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;\">\r\n  <!--[if (!mso)&(!IE)]><!--><div style=\"box-sizing: border-box; height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;\"><!--<![endif]-->\r\n  \r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n  <tr>\r\n    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">\r\n      \r\n      <img align=\"center\" border=\"0\" src=\"https://assets.unlayer.com/projects/0/1771262054904-WhatsApp%20Image%202026-02-04%20at%207.37.34%20PM.jpeg?w=393.6px\" alt=\"\" title=\"\" style=\"outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 41%;max-width: 196.8px;\" width=\"196.8\"/>\r\n      \r\n    </td>\r\n  </tr>\r\n</table>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n  <!--[if mso]><table role=\"presentation\" width=\"100%\"><tr><td><![endif]-->\r\n    <h2 style=\"margin: 0px; line-height: 140%; text-align: center; word-wrap: break-word; font-size: 20px; font-weight: 400;\"><span><span>Invoice Status</span></span></h2>\r\n  <!--[if mso]></td></tr></table><![endif]-->\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n  <div>\r\n    <strong>Hello, world!</strong>\r\n  </div>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n  <!--[if mso]><style>.v-button {background: transparent !important;}</style><![endif]-->\r\n<div align=\"center\">\r\n  <!--[if mso]><v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com:office:word\" href=\"\" style=\"height:37px; v-text-anchor:middle; width:96px;\" arcsize=\"11%\"  stroke=\"f\" fillcolor=\"#0834a1\"><w:anchorlock/><center style=\"color:#FFFFFF;\"><![endif]-->\r\n    <a href=\"\" target=\"_blank\" class=\"v-button\" style=\"box-sizing: border-box;display: inline-block;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #FFFFFF; background-color: #0834a1; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;font-size: 14px;\">\r\n      <span style=\"display:block;padding:10px 20px;line-height:120%;\"><span><span>Pay Now</span></span></span>\r\n    </a>\r\n    <!--[if mso]></center></v:roundrect><![endif]-->\r\n</div>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<table style=\"font-family:arial,helvetica,sans-serif;\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\">\r\n  <tbody>\r\n    <tr>\r\n      <td style=\"overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;\" align=\"left\">\r\n        \r\n<div align=\"center\" style=\"direction: ltr;\" aria-label=\"social\">\r\n  <div style=\"display: table; max-width:-5px;\">\r\n  <!--[if (mso)|(IE)]><table role=\"presentation\" width=\"-5\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"border-collapse:collapse;\" align=\"center\"><table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:collapse; mso-table-lspace: 0pt;mso-table-rspace: 0pt; width:-5px;\"><tr><![endif]-->\r\n  \r\n    \r\n    \r\n    <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->\r\n  </div>\r\n</div>\r\n\r\n      </td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->\r\n  </div>\r\n</div>\r\n<!--[if (mso)|(IE)]></td><![endif]-->\r\n      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->\r\n    </div>\r\n  </div>\r\n  </div>\r\n  \r\n\r\n\r\n    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->\r\n    </td>\r\n  </tr>\r\n  </tbody>\r\n  </table>\r\n  <!--[if mso]></div><![endif]-->\r\n  <!--[if IE]></div><![endif]-->\r\n</body>\r\n\r\n</html>', '{\"counters\":{\"u_column\":1,\"u_row\":1,\"u_content_image\":2,\"u_content_heading\":1,\"u_content_html\":1,\"u_content_menu\":1,\"u_content_button\":1,\"u_content_social\":1},\"body\":{\"id\":\"iChuF12NB1\",\"rows\":[{\"id\":\"ifX0Y9DidO\",\"cells\":[1],\"columns\":[{\"id\":\"8m5sZA0lrV\",\"contents\":[{\"id\":\"FPvJjWdvDF\",\"type\":\"image\",\"values\":{\"containerPadding\":\"10px\",\"anchor\":\"\",\"src\":{\"url\":\"https://assets.unlayer.com/projects/0/1771262054904-WhatsApp%20Image%202026-02-04%20at%207.37.34%20PM.jpeg\",\"width\":640,\"height\":640,\"filename\":\"WhatsApp Image 2026-02-04 at 7.37.34 PM.jpeg\",\"contentType\":\"image/jpeg\",\"size\":21964,\"dynamic\":true,\"autoWidth\":false,\"maxWidth\":\"41%\"},\"textAlign\":\"center\",\"altText\":\"\",\"action\":{\"name\":\"web\",\"values\":{\"href\":\"\",\"target\":\"_blank\"}},\"displayCondition\":null,\"_styleGuide\":null,\"_meta\":{\"htmlID\":\"u_content_image_1\",\"htmlClassNames\":\"u_content_image\"},\"selectable\":true,\"draggable\":true,\"duplicatable\":true,\"deletable\":true,\"hideable\":true,\"locked\":false,\"pending\":false}},{\"id\":\"eCI8jOx-4A\",\"type\":\"heading\",\"values\":{\"textJson\":\"{\\\"root\\\":{\\\"children\\\":[{\\\"children\\\":[{\\\"detail\\\":0,\\\"format\\\":0,\\\"mode\\\":\\\"normal\\\",\\\"style\\\":\\\"\\\",\\\"text\\\":\\\"Invoice Status\\\",\\\"type\\\":\\\"extended-text\\\",\\\"version\\\":1}],\\\"format\\\":\\\"\\\",\\\"indent\\\":0,\\\"type\\\":\\\"heading-paragraph\\\",\\\"version\\\":1,\\\"textFormat\\\":0,\\\"textStyle\\\":\\\"\\\"}],\\\"format\\\":\\\"\\\",\\\"indent\\\":0,\\\"type\\\":\\\"root\\\",\\\"version\\\":1}}\",\"containerPadding\":\"10px\",\"anchor\":\"\",\"headingType\":\"h2\",\"fontSize\":\"20px\",\"textAlign\":\"center\",\"lineHeight\":\"140%\",\"linkStyle\":{\"inherit\":true,\"linkColor\":\"#0000ee\",\"linkHoverColor\":\"#0000ee\",\"linkUnderline\":true,\"linkHoverUnderline\":true},\"displayCondition\":null,\"_styleGuide\":null,\"_meta\":{\"htmlID\":\"u_content_heading_1\",\"htmlClassNames\":\"u_content_heading\"},\"selectable\":true,\"draggable\":true,\"duplicatable\":true,\"deletable\":true,\"hideable\":true,\"locked\":false,\"_languages\":{}}},{\"id\":\"9rEPW9bYtJ\",\"type\":\"html\",\"values\":{\"html\":\"<strong>Hello, world!</strong>\",\"displayCondition\":null,\"_styleGuide\":null,\"containerPadding\":\"10px\",\"anchor\":\"\",\"_meta\":{\"htmlID\":\"u_content_html_1\",\"htmlClassNames\":\"u_content_html\"},\"selectable\":true,\"draggable\":true,\"duplicatable\":true,\"deletable\":true,\"hideable\":true,\"locked\":false}},{\"id\":\"H0-uwfpAgL\",\"type\":\"button\",\"values\":{\"textJson\":\"{\\\"root\\\":{\\\"children\\\":[{\\\"children\\\":[{\\\"detail\\\":0,\\\"format\\\":0,\\\"mode\\\":\\\"normal\\\",\\\"style\\\":\\\"\\\",\\\"text\\\":\\\"Pay Now\\\",\\\"type\\\":\\\"extended-text\\\",\\\"version\\\":1}],\\\"direction\\\":null,\\\"format\\\":\\\"\\\",\\\"indent\\\":0,\\\"type\\\":\\\"extended-paragraph\\\",\\\"version\\\":1,\\\"textFormat\\\":0,\\\"textStyle\\\":\\\"\\\",\\\"isInlineTool\\\":true}],\\\"format\\\":\\\"\\\",\\\"indent\\\":0,\\\"type\\\":\\\"root\\\",\\\"version\\\":1}}\",\"href\":{\"name\":\"web\",\"values\":{\"href\":\"\",\"target\":\"_blank\"}},\"buttonColors\":{\"color\":\"#FFFFFF\",\"backgroundColor\":\"#0834a1\",\"hoverColor\":\"#FFFFFF\",\"hoverBackgroundColor\":\"#0879A1\"},\"size\":{\"autoWidth\":true,\"width\":\"100%\"},\"fontSize\":\"14px\",\"lineHeight\":\"120%\",\"textAlign\":\"center\",\"padding\":\"10px 20px\",\"border\":{},\"borderRadius\":\"4px\",\"displayCondition\":null,\"_styleGuide\":null,\"containerPadding\":\"10px\",\"anchor\":\"\",\"_meta\":{\"htmlID\":\"u_content_button_1\",\"htmlClassNames\":\"u_content_button\"},\"selectable\":true,\"draggable\":true,\"duplicatable\":true,\"deletable\":true,\"hideable\":true,\"locked\":false,\"_languages\":{},\"calculatedWidth\":96,\"calculatedHeight\":37}},{\"id\":\"2waG-c81Ui\",\"type\":\"social\",\"values\":{\"containerPadding\":\"10px\",\"anchor\":\"\",\"icons\":{\"iconType\":\"circle\",\"icons\":[]},\"align\":\"center\",\"iconSize\":32,\"spacing\":5,\"displayCondition\":null,\"_styleGuide\":null,\"_meta\":{\"htmlID\":\"u_content_social_1\",\"htmlClassNames\":\"u_content_social\"},\"selectable\":true,\"draggable\":true,\"duplicatable\":true,\"deletable\":true,\"hideable\":true,\"locked\":false}}],\"values\":{\"backgroundColor\":\"\",\"padding\":\"0px\",\"border\":{},\"borderRadius\":\"0px\",\"_meta\":{\"htmlID\":\"u_column_1\",\"htmlClassNames\":\"u_column\"},\"deletable\":true,\"locked\":false}}],\"values\":{\"displayCondition\":null,\"columns\":false,\"_styleGuide\":null,\"backgroundColor\":\"\",\"columnsBackgroundColor\":\"\",\"backgroundImage\":{\"url\":\"\",\"fullWidth\":true,\"repeat\":\"no-repeat\",\"size\":\"custom\",\"position\":\"center\",\"customPosition\":[\"50%\",\"50%\"]},\"padding\":\"0px\",\"anchor\":\"\",\"hideDesktop\":false,\"_meta\":{\"htmlID\":\"u_row_1\",\"htmlClassNames\":\"u_row\"},\"selectable\":true,\"draggable\":true,\"duplicatable\":true,\"deletable\":true,\"hideable\":true,\"locked\":false}}],\"headers\":[],\"footers\":[],\"values\":{\"_styleGuide\":null,\"popupPosition\":\"center\",\"popupDisplayDelay\":0,\"popupWidth\":\"600px\",\"popupHeight\":\"auto\",\"borderRadius\":\"10px\",\"contentAlign\":\"center\",\"contentVerticalAlign\":\"center\",\"contentWidth\":\"500px\",\"fontFamily\":{\"label\":\"Arial\",\"value\":\"arial,helvetica,sans-serif\"},\"textColor\":\"#000000\",\"popupBackgroundColor\":\"#FFFFFF\",\"popupBackgroundImage\":{\"url\":\"\",\"fullWidth\":true,\"repeat\":\"no-repeat\",\"size\":\"cover\",\"position\":\"center\",\"customPosition\":[\"50%\",\"50%\"]},\"popupOverlay_backgroundColor\":\"rgba(0, 0, 0, 0.1)\",\"popupCloseButton_position\":\"top-right\",\"popupCloseButton_backgroundColor\":\"#DDDDDD\",\"popupCloseButton_iconColor\":\"#000000\",\"popupCloseButton_borderRadius\":\"0px\",\"popupCloseButton_margin\":\"0px\",\"popupCloseButton_action\":{\"name\":\"close_popup\",\"attrs\":{\"onClick\":\"document.querySelector(\'.u-popup-container\').style.display = \'none\';\"}},\"language\":{},\"backgroundColor\":\"#F7F8F9\",\"preheaderText\":\"\",\"linkStyle\":{\"body\":true,\"linkColor\":\"#0000ee\",\"linkHoverColor\":\"#0000ee\",\"linkUnderline\":true,\"linkHoverUnderline\":true},\"backgroundImage\":{\"url\":\"\",\"fullWidth\":true,\"repeat\":\"no-repeat\",\"size\":\"custom\",\"position\":\"center\",\"customPosition\":[\"50%\",\"50%\"]},\"accessibilityTitle\":\"\",\"_meta\":{\"htmlID\":\"u_body\",\"htmlClassNames\":\"u_body\"}}},\"schemaVersion\":23}', NULL, NULL, 'mrshabibahassan@gmail.com', 'mrshabibahassan@gmail.com', 1, '2026-02-16 17:12:42', '2026-02-16 17:20:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expense_date` date NOT NULL,
  `vendor` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `payment_method` varchar(255) DEFAULT NULL,
  `email_cc` varchar(255) DEFAULT NULL,
  `email_bcc` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bill_to_name` varchar(255) DEFAULT NULL,
  `bill_to_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bill_to_json`)),
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoice_date` datetime NOT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `html` longtext DEFAULT NULL,
  `payment_link` text DEFAULT NULL,
  `payment_provider` varchar(255) NOT NULL DEFAULT 'Manual',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `show_bank_details` tinyint(1) NOT NULL DEFAULT 1,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `recurrence_type` varchar(255) DEFAULT NULL,
  `next_run_date` date DEFAULT NULL,
  `currency_code` varchar(10) NOT NULL DEFAULT 'USD',
  `currency_display` varchar(10) DEFAULT NULL,
  `invoice_title_bg` varchar(7) NOT NULL DEFAULT '#FFDC00',
  `invoice_title_text` varchar(7) NOT NULL DEFAULT '#0033D9',
  `invoice_tax_summary` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `bill_to_name`, `bill_to_json`, `total_amount`, `invoice_date`, `due_date`, `status`, `html`, `payment_link`, `payment_provider`, `created_by`, `show_bank_details`, `is_recurring`, `recurrence_type`, `next_run_date`, `currency_code`, `currency_display`, `invoice_title_bg`, `invoice_title_text`, `invoice_tax_summary`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'DUMMY-0001', 1, 'Dummy Client 1', '{\"company_name\": \"Dummy Client 1\"}', 149.00, '2026-02-04 18:07:47', '2026-02-11 18:07:47', 'Unpaid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(2, 'DUMMY-0002', 2, 'Dummy Client 2', '{\"company_name\": \"Dummy Client 2\"}', 249.00, '2026-02-05 18:07:47', '2026-02-12 18:07:47', 'Paid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(3, 'DUMMY-0003', 3, 'Dummy Client 3', '{\"company_name\": \"Dummy Client 3\"}', 349.00, '2026-02-06 18:07:47', '2026-02-13 18:07:47', 'Unpaid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(4, 'DUMMY-0004', 4, 'Dummy Client 4', '{\"company_name\": \"Dummy Client 4\"}', 449.00, '2026-02-07 18:07:47', '2026-02-15 18:07:47', 'Paid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(5, 'DUMMY-0005', 5, 'Dummy Client 5', '{\"company_name\": \"Dummy Client 5\"}', 549.00, '2026-02-08 18:07:47', '2026-02-16 18:07:47', 'Unpaid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(6, 'DUMMY-0006', 6, 'Dummy Client 6', '{\"company_name\": \"Dummy Client 6\"}', 649.00, '2026-02-09 18:07:47', '2026-02-17 18:07:47', 'Paid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(7, 'DUMMY-0007', 7, 'Dummy Client 7', '{\"company_name\": \"Dummy Client 7\"}', 749.00, '2026-02-10 18:07:47', '2026-02-18 18:07:47', 'Unpaid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(8, 'DUMMY-0008', 8, 'Dummy Client 8', '{\"company_name\": \"Dummy Client 8\"}', 849.00, '2026-02-11 18:07:47', '2026-02-19 18:07:47', 'Paid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(9, 'DUMMY-0009', 9, 'Dummy Client 9', '{\"company_name\": \"Dummy Client 9\"}', 949.00, '2026-02-12 18:07:47', '2026-02-20 18:07:47', 'Unpaid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(10, 'DUMMY-0010', 10, 'Dummy Client 10', '{\"company_name\": \"Dummy Client 10\"}', 1049.00, '2026-02-13 18:07:47', '2026-02-21 18:07:47', 'Paid', NULL, NULL, 'Manual', 2, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', NULL, '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL),
(11, 'INV-TC-01', 11, 'Test1 Company', '{\"Company Name\":\"Test1 Company\",\"Contact Name\":\"Habiba Hassan\",\"Address\":\"abc hose 729 NY, US\",\"Phone\":\"4456421489\",\"Email\":\"mrshabibahassan@gmail.com\",\"Bank Account Holder\":\"Habiba Hassan\",\"Bank Name\":\"Allied Bank\",\"Bank Account Number\":\"123456789\",\"Bank IBAN\":\"PK5820IBAN123456789\",\"Bank SWIFT\":\"\",\"Bank Routing Code\":\"\",\"Payment Instructions\":\"\"}', 526783.25, '2026-02-01 00:00:00', '2026-03-01 00:00:00', 'Paid', '<!doctype html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Invoice INV-TC-01</title>\n  <style>\n    @page {\n      size: A4 portrait;\n      margin: 11mm;\n    }\n    body {\n      font-family: DejaVu Sans, Arial, sans-serif;\n      color: #10233c;\n      font-size: 10px;\n      margin: 0;\n      line-height: 1.25;\n      background: #ffffff;\n    }\n    @media print {\n      body {\n        -webkit-print-color-adjust: exact;\n        print-color-adjust: exact;\n      }\n    }\n    .invoice-shell {\n      width: 100%;\n    }\n    .invoice-header {\n      border-bottom: 1px solid #dbe2ea;\n      padding-bottom: 8px;\n      margin-bottom: 8px;\n    }\n    .header-grid {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .header-grid td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .company-col {\n      width: 56%;\n      padding-right: 12px;\n    }\n    .billto-col {\n      width: 44%;\n      text-align: right;\n    }\n    .logo-wrap {\n      margin-bottom: 6px;\n      min-height: 24px;\n    }\n    .logo-wrap img {\n      max-width: 120px;\n      max-height: 44px;\n    }\n    .logo-fallback {\n      display: inline-block;\n      font-size: 15px;\n      font-weight: 700;\n      color: #0b4bd8;\n    }\n    .company-name {\n      font-size: 15px;\n      font-weight: 700;\n      margin: 0 0 2px;\n      color: #0d274f;\n    }\n    .muted {\n      color: #4e6078;\n    }\n    .invoice-band {\n      margin-top: 6px;\n      display: inline-block;\n      padding: 4px 8px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.8px;\n      text-transform: uppercase;\n      background: #FFDC00;\n      color: #0033D9;\n    }\n    .billto-title {\n      font-weight: 700;\n      margin-bottom: 6px;\n      color: #10233c;\n    }\n    .invoice-meta {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 8px;\n    }\n    .invoice-meta td {\n      border: none;\n      padding: 0;\n      vertical-align: top;\n    }\n    .meta-right {\n      text-align: right;\n    }\n    .meta-label {\n      font-size: 9px;\n      color: #4e6078;\n      text-transform: uppercase;\n      letter-spacing: 0.4px;\n    }\n    .meta-value {\n      font-size: 10px;\n      font-weight: 600;\n      color: #10233c;\n      margin-bottom: 2px;\n    }\n    table {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .line-table {\n      margin-bottom: 8px;\n    }\n    .line-table th,\n    .line-table td {\n      border: 1px solid #dbe2ea;\n      padding: 4px 5px;\n      vertical-align: top;\n    }\n    .line-table th {\n      background: #f3f6fb;\n      color: #153157;\n      font-size: 9px;\n      text-transform: uppercase;\n      letter-spacing: 0.3px;\n    }\n    .line-table td {\n      font-size: 10px;\n    }\n    .amount {\n      text-align: right;\n      white-space: nowrap;\n    }\n    .line-meta {\n      margin-top: 2px;\n      font-size: 8px;\n      color: #5a6e89;\n    }\n    .line-meta span {\n      white-space: nowrap;\n    }\n    .totals-wrap {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 6px;\n    }\n    .totals-wrap td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .totals-box {\n      width: 46%;\n      margin-left: auto;\n    }\n    .totals-box table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 10px;\n    }\n    .totals-box .label {\n      color: #3d4f67;\n    }\n    .totals-box .value {\n      text-align: right;\n      font-weight: 600;\n      color: #10233c;\n      white-space: nowrap;\n    }\n    .totals-box .grand .label,\n    .totals-box .grand .value {\n      font-size: 11px;\n      font-weight: 700;\n      color: #10233c;\n      padding-top: 3px;\n      border-top: 1px solid #dbe2ea;\n    }\n    .tax-summary {\n      margin-top: 7px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n    }\n    .pay-now-row {\n      margin-top: 4px;\n      margin-bottom: 7px;\n      text-align: left;\n    }\n    .pay-now-button {\n      display: inline-block;\n      background: #0d6efd;\n      color: #ffffff;\n      text-decoration: none;\n      padding: 4px 9px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.2px;\n    }\n    .pay-now-button-disabled {\n      background: #8a94a8;\n      pointer-events: none;\n    }\n    .tax-summary h4 {\n      margin: 0 0 3px;\n      font-size: 10px;\n      text-transform: uppercase;\n      letter-spacing: 0.5px;\n      color: #2a3f60;\n    }\n    .tax-summary table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 9px;\n    }\n    .tax-summary table td:last-child {\n      text-align: right;\n      white-space: nowrap;\n      font-weight: 600;\n    }\n    .invoice-footer {\n      margin-top: 8px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n      font-size: 8px;\n      color: #526784;\n      text-align: center;\n    }\n  </style>\n</head>\n<body>\n  \n  <div class=\"invoice-shell\">\n    <div class=\"invoice-header\">\n      <table class=\"header-grid\">\n        <tr>\n          <td class=\"company-col\">\n            <div class=\"logo-wrap\">\n                              <span class=\"logo-fallback\">DocuBills</span>\n                          </div>\n            <div class=\"company-name\">DocuBills</div>\n                                                            <div class=\"invoice-band\">Invoice</div>\n          </td>\n          <td class=\"billto-col\">\n            <div class=\"billto-title\">Bill To</div>\n            <div>Test1 Company</div>\n            <div>Habiba Hassan</div>            <div>abc hose 729 NY, US</div>            <div>4456421489</div>            <div>mrshabibahassan@gmail.com</div>                          <div style=\"margin-top:8px; font-size:11px;\">\n                <strong>Banking Details</strong><br>\n                 Account Holder: Habiba Hassan<br>                 Bank: Allied Bank<br>                 Account No: 123456789<br>                 IBAN: PK5820IBAN123456789<br>                                                              </div>\n                      </td>\n        </tr>\n      </table>\n    </div>\n\n    <table class=\"invoice-meta\">\n      <tr>\n        <td>\n          <div class=\"meta-label\">Invoice Number</div>\n          <div class=\"meta-value\">INV-TC-01</div>\n        </td>\n        <td class=\"meta-right\">\n          <div class=\"meta-label\">Currency</div>\n          <div class=\"meta-value\">USD $</div>\n        </td>\n      </tr>\n    </table>\n\n    <table class=\"line-table\">\n      <thead>\n        <tr>\n          <th style=\"width:8%;\">Item #</th>\n          <th style=\"width:34%;\">Description</th>\n          <th style=\"width:12%;\">Qty</th>\n          <th style=\"width:15%;\">Rate</th>\n          <th style=\"width:15%;\">Tax</th>\n          <th style=\"width:16%;\">Line Total</th>\n        </tr>\n      </thead>\n      <tbody>\n                            <tr>\n            <td>1</td>\n            <td>\n              15 &quot; Enterprise Laptop Pro – Intel i7, 32GB RAM, 1TB NVMe, Win 11 Ent\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: LAPTOP-15PRO-INTEL-I7-32-1TB</span> |                                       <span>Material Content: Aluminum chassis, PCB, Lithium battery, Glass display, Plastics</span> |                                       <span>Unit Value (USD: 1899.95</span>                                  </div>\n                          </td>\n            <td class=\"amount\">150.00</td>\n            <td class=\"amount\">$ 1,899.95</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 284,992.50</td>\n          </tr>\n                            <tr>\n            <td>2</td>\n            <td>\n              USB‑C Universal Dock – 10-port with Dual 4K Display Support\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: DOCK-USB-C-ULTRA-10P</span> |                                       <span>Material Content: Aluminum housing, Copper wiring, PCB, ABS plastic</span> |                                       <span>Unit Value (USD: 249.5</span>                                  </div>\n                          </td>\n            <td class=\"amount\">150.00</td>\n            <td class=\"amount\">$ 249.50</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 37,425.00</td>\n          </tr>\n                            <tr>\n            <td>3</td>\n            <td>\n              Executive Laptop Backpack – Water-Resistant, TSA-Ready\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: BAG-BACKPACK-EXEC-BLACK</span> |                                       <span>Material Content: Polyester 70%, Nylon 20%, PU coating 10%</span> |                                       <span>Unit Value (USD: 89.9</span>                                  </div>\n                          </td>\n            <td class=\"amount\">150.00</td>\n            <td class=\"amount\">$ 89.90</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 13,485.00</td>\n          </tr>\n                            <tr>\n            <td>4</td>\n            <td>\n              27&quot; 4K UHD Monitor – HDR10, USB‑C Power Delivery\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-002</span> |                                       <span>SKU: MONITOR-27-UHD-HDR</span> |                                       <span>Material Content: Aluminum stand, Plastic housing, Glass panel, PCB</span> |                                       <span>Unit Value (USD: 379.75</span>                                  </div>\n                          </td>\n            <td class=\"amount\">220.00</td>\n            <td class=\"amount\">$ 379.75</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 83,545.00</td>\n          </tr>\n                            <tr>\n            <td>5</td>\n            <td>\n              Dual Gas-Spring Monitor Arm – Desk Mount\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-002</span> |                                       <span>SKU: ARM-DUAL-MONITOR-GAS</span> |                                       <span>Material Content: Steel 60%, Aluminum 25%, Plastics 15%</span> |                                       <span>Unit Value (USD: 129.4</span>                                  </div>\n                          </td>\n            <td class=\"amount\">220.00</td>\n            <td class=\"amount\">$ 129.40</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 28,468.00</td>\n          </tr>\n                            <tr>\n            <td>6</td>\n            <td>\n              UC Certified Wireless Headset with Active Noise Cancelling + Dongle\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-003</span> |                                       <span>SKU: HEADSET-UC-WIRELESS-ANC</span> |                                       <span>Material Content: Plastics, Synthetic leather, Lithium battery, Copper wiring</span> |                                       <span>Unit Value (USD: 219.99</span>                                  </div>\n                          </td>\n            <td class=\"amount\">350.00</td>\n            <td class=\"amount\">$ 219.99</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 76,996.50</td>\n          </tr>\n              </tbody>\n    </table>\n\n    <table class=\"totals-wrap\">\n      <tr>\n        <td></td>\n        <td class=\"totals-box\">\n          <table>\n            <tr><td class=\"label\">Net Total</td><td class=\"value\">$ 524,912.00</td></tr>\n            <tr><td class=\"label\">Line-Level Taxes</td><td class=\"value\">$ 1,871.25</td></tr>\n            <tr><td class=\"label\">Subtotal</td><td class=\"value\">$ 526,783.25</td></tr>\n                                                                                      <tr><td class=\"label\">Total Taxes</td><td class=\"value\">$ 1,871.25</td></tr>\n            <tr class=\"grand\"><td class=\"label\">Grand Total</td><td class=\"value\">$ 526,783.25</td></tr>\n          </table>\n        </td>\n      </tr>\n    </table>\n\n          \n    \n    <div class=\"tax-summary\">\n      <h4>Tax Summary</h4>\n      <table>\n        <tr><td>Net Total</td><td>$ 524,912.00</td></tr>\n        <tr><td>Total Line-Level Taxes</td><td>$ 1,871.25</td></tr>\n                                                  <tr><td>Total Taxes</td><td>$ 1,871.25</td></tr>\n      </table>\n    </div>\n\n    \n    \n<div class=\"invoice-footer\">\n              This invoice is generated according to your company settings and tax configuration.\n          </div>\n  </div>\n</body>\n</html>\n', NULL, 'Manual', 1, 1, 0, NULL, NULL, 'USD', '$', '#FFDC00', '#0033D9', '{\"taxable_on\":true,\"net_total\":524912,\"line_taxes\":[{\"id\":1,\"label\":\"A (5.00%)\",\"amount\":1871.25}],\"line_tax_total\":1871.25,\"subtotal\":526783.25,\"invoice_subtotal_taxes\":[],\"invoice_adjusted_taxes\":[],\"invoice_tax_total\":0,\"invoice_taxes\":[],\"invoice_subtotal_tax_total\":0,\"adjusted_subtotal\":526783.25,\"total_taxes\":1871.25,\"grand_total\":526783.25}', '2026-02-16 17:26:22', '2026-02-16 17:33:07', NULL),
(12, 'INV-T2-01', 12, 'TestCompany 2', '{\"Company Name\":\"TestCompany 2\",\"Contact Name\":\"Nora US\",\"Address\":\"house 829 NY street, US\",\"Phone\":\"4456321459\",\"Email\":\"mrshabibahassan@gmail.com\",\"Bank Account Holder\":\"\",\"Bank Name\":\"\",\"Bank Account Number\":\"\",\"Bank IBAN\":\"\",\"Bank SWIFT\":\"\",\"Bank Routing Code\":\"\",\"Payment Instructions\":\"\"}', 310.00, '2026-02-16 00:00:00', '2026-03-02 00:00:00', 'Unpaid', '<!doctype html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Invoice INV-T2-01</title>\n  <style>\n    @page {\n      size: A4 portrait;\n      margin: 11mm;\n    }\n    body {\n      font-family: DejaVu Sans, Arial, sans-serif;\n      color: #10233c;\n      font-size: 10px;\n      margin: 0;\n      line-height: 1.25;\n      background: #ffffff;\n    }\n    @media print {\n      body {\n        -webkit-print-color-adjust: exact;\n        print-color-adjust: exact;\n      }\n    }\n    .invoice-shell {\n      width: 100%;\n    }\n    .invoice-header {\n      border-bottom: 1px solid #dbe2ea;\n      padding-bottom: 8px;\n      margin-bottom: 8px;\n    }\n    .header-grid {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .header-grid td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .company-col {\n      width: 56%;\n      padding-right: 12px;\n    }\n    .billto-col {\n      width: 44%;\n      text-align: right;\n    }\n    .logo-wrap {\n      margin-bottom: 6px;\n      min-height: 24px;\n    }\n    .logo-wrap img {\n      max-width: 120px;\n      max-height: 44px;\n    }\n    .logo-fallback {\n      display: inline-block;\n      font-size: 15px;\n      font-weight: 700;\n      color: #0b4bd8;\n    }\n    .company-name {\n      font-size: 15px;\n      font-weight: 700;\n      margin: 0 0 2px;\n      color: #0d274f;\n    }\n    .muted {\n      color: #4e6078;\n    }\n    .invoice-band {\n      margin-top: 6px;\n      display: inline-block;\n      padding: 4px 8px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.8px;\n      text-transform: uppercase;\n      background: #FFDC00;\n      color: #0033D9;\n    }\n    .billto-title {\n      font-weight: 700;\n      margin-bottom: 6px;\n      color: #10233c;\n    }\n    .invoice-meta {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 8px;\n    }\n    .invoice-meta td {\n      border: none;\n      padding: 0;\n      vertical-align: top;\n    }\n    .meta-right {\n      text-align: right;\n    }\n    .meta-label {\n      font-size: 9px;\n      color: #4e6078;\n      text-transform: uppercase;\n      letter-spacing: 0.4px;\n    }\n    .meta-value {\n      font-size: 10px;\n      font-weight: 600;\n      color: #10233c;\n      margin-bottom: 2px;\n    }\n    table {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .line-table {\n      margin-bottom: 8px;\n    }\n    .line-table th,\n    .line-table td {\n      border: 1px solid #dbe2ea;\n      padding: 4px 5px;\n      vertical-align: top;\n    }\n    .line-table th {\n      background: #f3f6fb;\n      color: #153157;\n      font-size: 9px;\n      text-transform: uppercase;\n      letter-spacing: 0.3px;\n    }\n    .line-table td {\n      font-size: 10px;\n    }\n    .amount {\n      text-align: right;\n      white-space: nowrap;\n    }\n    .line-meta {\n      margin-top: 2px;\n      font-size: 8px;\n      color: #5a6e89;\n    }\n    .line-meta span {\n      white-space: nowrap;\n    }\n    .totals-wrap {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 6px;\n    }\n    .totals-wrap td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .totals-box {\n      width: 46%;\n      margin-left: auto;\n    }\n    .totals-box table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 10px;\n    }\n    .totals-box .label {\n      color: #3d4f67;\n    }\n    .totals-box .value {\n      text-align: right;\n      font-weight: 600;\n      color: #10233c;\n      white-space: nowrap;\n    }\n    .totals-box .grand .label,\n    .totals-box .grand .value {\n      font-size: 11px;\n      font-weight: 700;\n      color: #10233c;\n      padding-top: 3px;\n      border-top: 1px solid #dbe2ea;\n    }\n    .tax-summary {\n      margin-top: 7px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n    }\n    .pay-now-row {\n      margin-top: 4px;\n      margin-bottom: 7px;\n      text-align: left;\n    }\n    .pay-now-button {\n      display: inline-block;\n      background: #0d6efd;\n      color: #ffffff;\n      text-decoration: none;\n      padding: 4px 9px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.2px;\n    }\n    .pay-now-button-disabled {\n      background: #8a94a8;\n      pointer-events: none;\n    }\n    .tax-summary h4 {\n      margin: 0 0 3px;\n      font-size: 10px;\n      text-transform: uppercase;\n      letter-spacing: 0.5px;\n      color: #2a3f60;\n    }\n    .tax-summary table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 9px;\n    }\n    .tax-summary table td:last-child {\n      text-align: right;\n      white-space: nowrap;\n      font-weight: 600;\n    }\n    .invoice-footer {\n      margin-top: 8px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n      font-size: 8px;\n      color: #526784;\n      text-align: center;\n    }\n  </style>\n</head>\n<body>\n  \n  <div class=\"invoice-shell\">\n    <div class=\"invoice-header\">\n      <table class=\"header-grid\">\n        <tr>\n          <td class=\"company-col\">\n            <div class=\"logo-wrap\">\n                              <span class=\"logo-fallback\">DocuBills</span>\n                          </div>\n            <div class=\"company-name\">DocuBills</div>\n                                                            <div class=\"invoice-band\">Invoice</div>\n          </td>\n          <td class=\"billto-col\">\n            <div class=\"billto-title\">Bill To</div>\n            <div>TestCompany 2</div>\n            <div>Nora US</div>            <div>house 829 NY street, US</div>            <div>4456321459</div>            <div>mrshabibahassan@gmail.com</div>                      </td>\n        </tr>\n      </table>\n    </div>\n\n    <table class=\"invoice-meta\">\n      <tr>\n        <td>\n          <div class=\"meta-label\">Invoice Number</div>\n          <div class=\"meta-value\">INV-T2-01</div>\n        </td>\n        <td class=\"meta-right\">\n          <div class=\"meta-label\">Currency</div>\n          <div class=\"meta-value\">USD $</div>\n        </td>\n      </tr>\n    </table>\n\n    <table class=\"line-table\">\n      <thead>\n        <tr>\n          <th style=\"width:8%;\">Item #</th>\n          <th style=\"width:34%;\">Description</th>\n          <th style=\"width:12%;\">Qty</th>\n          <th style=\"width:15%;\">Rate</th>\n          <th style=\"width:15%;\">Tax</th>\n          <th style=\"width:16%;\">Line Total</th>\n        </tr>\n      </thead>\n      <tbody>\n                            <tr>\n            <td>1</td>\n            <td>\n              15 &quot; Enterprise Laptop Pro – Intel i7, 32GB RAM, 1TB NVMe, Win 11 Ent\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: LAPTOP-15PRO-INTEL-I7-32-1TB</span> |                                       <span>Unit Value (USD: 30</span>                                  </div>\n                          </td>\n            <td class=\"amount\">1.00</td>\n            <td class=\"amount\">$ 50.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 50.00</td>\n          </tr>\n                            <tr>\n            <td>2</td>\n            <td>\n              USB‑C Universal Dock – 10-port with Dual 4K Display Support\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: DOCK-USB-C-ULTRA-10P</span> |                                       <span>Unit Value (USD: 30</span>                                  </div>\n                          </td>\n            <td class=\"amount\">1.00</td>\n            <td class=\"amount\">$ 60.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 60.00</td>\n          </tr>\n                            <tr>\n            <td>3</td>\n            <td>\n              Executive Laptop Backpack – Water-Resistant, TSA-Ready\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: BAG-BACKPACK-EXEC-BLACK</span> |                                       <span>Unit Value (USD: 50</span>                                  </div>\n                          </td>\n            <td class=\"amount\">1.00</td>\n            <td class=\"amount\">$ 80.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 80.00</td>\n          </tr>\n                            <tr>\n            <td>4</td>\n            <td>\n              27&quot; 4K UHD Monitor – HDR10, USB‑C Power Delivery\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-002</span> |                                       <span>SKU: MONITOR-27-UHD-HDR</span> |                                       <span>Unit Value (USD: 10</span>                                  </div>\n                          </td>\n            <td class=\"amount\">1.00</td>\n            <td class=\"amount\">$ 50.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 50.00</td>\n          </tr>\n                            <tr>\n            <td>5</td>\n            <td>\n              Dual Gas-Spring Monitor Arm – Desk Mount\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-002</span> |                                       <span>SKU: ARM-DUAL-MONITOR-GAS</span> |                                       <span>Unit Value (USD: 30</span>                                  </div>\n                          </td>\n            <td class=\"amount\">1.00</td>\n            <td class=\"amount\">$ 50.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 50.00</td>\n          </tr>\n                            <tr>\n            <td>6</td>\n            <td>\n              UC Certified Wireless Headset with Active Noise Cancelling + Dongle\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-003</span> |                                       <span>SKU: HEADSET-UC-WIRELESS-ANC</span> |                                       <span>Unit Value (USD: 100</span>                                  </div>\n                          </td>\n            <td class=\"amount\">1.00</td>\n            <td class=\"amount\">$ 20.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 20.00</td>\n          </tr>\n              </tbody>\n    </table>\n\n    <table class=\"totals-wrap\">\n      <tr>\n        <td></td>\n        <td class=\"totals-box\">\n          <table>\n            <tr><td class=\"label\">Net Total</td><td class=\"value\">$ 310.00</td></tr>\n            <tr><td class=\"label\">Line-Level Taxes</td><td class=\"value\">$ 0.00</td></tr>\n            <tr><td class=\"label\">Subtotal</td><td class=\"value\">$ 310.00</td></tr>\n                                                                                      <tr><td class=\"label\">Total Taxes</td><td class=\"value\">$ 0.00</td></tr>\n            <tr class=\"grand\"><td class=\"label\">Grand Total</td><td class=\"value\">$ 310.00</td></tr>\n          </table>\n        </td>\n      </tr>\n    </table>\n\n          <!-- PAY_NOW_BLOCK_START -->\n      <div class=\"pay-now-row\">\n        <a href=\"#\" class=\"pay-now-button pay-now-button-disabled\" target=\"_blank\">Pay Now</a>\n      </div>\n      <!-- PAY_NOW_BLOCK_END -->\n    \n    <div class=\"tax-summary\">\n      <h4>Tax Summary</h4>\n      <table>\n        <tr><td>Net Total</td><td>$ 310.00</td></tr>\n        <tr><td>Total Line-Level Taxes</td><td>$ 0.00</td></tr>\n                                                  <tr><td>Total Taxes</td><td>$ 0.00</td></tr>\n      </table>\n    </div>\n\n    <div class=\"invoice-footer\">\n              This invoice is generated according to your company settings and tax configuration.\n          </div>\n  </div>\n</body>\n</html>\n', NULL, 'Manual', 1, 0, 1, 'monthly', '2026-03-16', 'USD', '$', '#FFDC00', '#0033D9', '{\"taxable_on\":true,\"net_total\":310,\"line_taxes\":[],\"line_tax_total\":0,\"subtotal\":310,\"invoice_subtotal_taxes\":[],\"invoice_adjusted_taxes\":[],\"invoice_tax_total\":0,\"invoice_taxes\":[],\"invoice_subtotal_tax_total\":0,\"adjusted_subtotal\":310,\"total_taxes\":0,\"grand_total\":310}', '2026-02-16 17:41:13', '2026-02-16 17:41:13', NULL),
(13, 'INV-HH-01', 13, 'Habiba Hassan', '{\"Company Name\":\"Habiba Hassan\",\"Contact Name\":\"IT Solutions\",\"Address\":\"58 NY Street 1 US\",\"Phone\":\"4566625471\",\"Email\":\"mrshabibahassan@gmail.com\",\"Bank Account Holder\":\"\",\"Bank Name\":\"\",\"Bank Account Number\":\"\",\"Bank IBAN\":\"\",\"Bank SWIFT\":\"\",\"Bank Routing Code\":\"\",\"Payment Instructions\":\"\"}', 1505461.00, '2026-02-16 00:00:00', '2026-03-02 00:00:00', 'Unpaid', '<!doctype html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Invoice INV-HH-01</title>\n  <style>\n    @page {\n      size: A4 portrait;\n      margin: 11mm;\n    }\n    body {\n      font-family: DejaVu Sans, Arial, sans-serif;\n      color: #10233c;\n      font-size: 10px;\n      margin: 0;\n      line-height: 1.25;\n      background: #ffffff;\n    }\n    @media print {\n      body {\n        -webkit-print-color-adjust: exact;\n        print-color-adjust: exact;\n      }\n    }\n    .invoice-shell {\n      width: 100%;\n    }\n    .invoice-header {\n      border-bottom: 1px solid #dbe2ea;\n      padding-bottom: 8px;\n      margin-bottom: 8px;\n    }\n    .header-grid {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .header-grid td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .company-col {\n      width: 56%;\n      padding-right: 12px;\n    }\n    .billto-col {\n      width: 44%;\n      text-align: right;\n    }\n    .logo-wrap {\n      margin-bottom: 6px;\n      min-height: 24px;\n    }\n    .logo-wrap img {\n      max-width: 120px;\n      max-height: 44px;\n    }\n    .logo-fallback {\n      display: inline-block;\n      font-size: 15px;\n      font-weight: 700;\n      color: #0b4bd8;\n    }\n    .company-name {\n      font-size: 15px;\n      font-weight: 700;\n      margin: 0 0 2px;\n      color: #0d274f;\n    }\n    .muted {\n      color: #4e6078;\n    }\n    .invoice-band {\n      margin-top: 6px;\n      display: inline-block;\n      padding: 4px 8px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.8px;\n      text-transform: uppercase;\n      background: #FFDC00;\n      color: #0033D9;\n    }\n    .billto-title {\n      font-weight: 700;\n      margin-bottom: 6px;\n      color: #10233c;\n    }\n    .invoice-meta {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 8px;\n    }\n    .invoice-meta td {\n      border: none;\n      padding: 0;\n      vertical-align: top;\n    }\n    .meta-right {\n      text-align: right;\n    }\n    .meta-label {\n      font-size: 9px;\n      color: #4e6078;\n      text-transform: uppercase;\n      letter-spacing: 0.4px;\n    }\n    .meta-value {\n      font-size: 10px;\n      font-weight: 600;\n      color: #10233c;\n      margin-bottom: 2px;\n    }\n    table {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .line-table {\n      margin-bottom: 8px;\n    }\n    .line-table th,\n    .line-table td {\n      border: 1px solid #dbe2ea;\n      padding: 4px 5px;\n      vertical-align: top;\n    }\n    .line-table th {\n      background: #f3f6fb;\n      color: #153157;\n      font-size: 9px;\n      text-transform: uppercase;\n      letter-spacing: 0.3px;\n    }\n    .line-table td {\n      font-size: 10px;\n    }\n    .amount {\n      text-align: right;\n      white-space: nowrap;\n    }\n    .line-meta {\n      margin-top: 2px;\n      font-size: 8px;\n      color: #5a6e89;\n    }\n    .line-meta span {\n      white-space: nowrap;\n    }\n    .totals-wrap {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 6px;\n    }\n    .totals-wrap td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .totals-box {\n      width: 46%;\n      margin-left: auto;\n    }\n    .totals-box table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 10px;\n    }\n    .totals-box .label {\n      color: #3d4f67;\n    }\n    .totals-box .value {\n      text-align: right;\n      font-weight: 600;\n      color: #10233c;\n      white-space: nowrap;\n    }\n    .totals-box .grand .label,\n    .totals-box .grand .value {\n      font-size: 11px;\n      font-weight: 700;\n      color: #10233c;\n      padding-top: 3px;\n      border-top: 1px solid #dbe2ea;\n    }\n    .tax-summary {\n      margin-top: 7px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n    }\n    .pay-now-row {\n      margin-top: 4px;\n      margin-bottom: 7px;\n      text-align: left;\n    }\n    .pay-now-button {\n      display: inline-block;\n      background: #0d6efd;\n      color: #ffffff;\n      text-decoration: none;\n      padding: 4px 9px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.2px;\n    }\n    .pay-now-button-disabled {\n      background: #8a94a8;\n      pointer-events: none;\n    }\n    .tax-summary h4 {\n      margin: 0 0 3px;\n      font-size: 10px;\n      text-transform: uppercase;\n      letter-spacing: 0.5px;\n      color: #2a3f60;\n    }\n    .tax-summary table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 9px;\n    }\n    .tax-summary table td:last-child {\n      text-align: right;\n      white-space: nowrap;\n      font-weight: 600;\n    }\n    .invoice-footer {\n      margin-top: 8px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n      font-size: 8px;\n      color: #526784;\n      text-align: center;\n    }\n  </style>\n</head>\n<body>\n  \n  <div class=\"invoice-shell\">\n    <div class=\"invoice-header\">\n      <table class=\"header-grid\">\n        <tr>\n          <td class=\"company-col\">\n            <div class=\"logo-wrap\">\n                              <span class=\"logo-fallback\">DocuBills</span>\n                          </div>\n            <div class=\"company-name\">DocuBills</div>\n                                                            <div class=\"invoice-band\">Invoice</div>\n          </td>\n          <td class=\"billto-col\">\n            <div class=\"billto-title\">Bill To</div>\n            <div>Habiba Hassan</div>\n            <div>IT Solutions</div>            <div>58 NY Street 1 US</div>            <div>4566625471</div>            <div>mrshabibahassan@gmail.com</div>                      </td>\n        </tr>\n      </table>\n    </div>\n\n    <table class=\"invoice-meta\">\n      <tr>\n        <td>\n          <div class=\"meta-label\">Invoice Number</div>\n          <div class=\"meta-value\">INV-HH-01</div>\n        </td>\n        <td class=\"meta-right\">\n          <div class=\"meta-label\">Currency</div>\n          <div class=\"meta-value\">AUD A$</div>\n        </td>\n      </tr>\n    </table>\n\n    <table class=\"line-table\">\n      <thead>\n        <tr>\n          <th style=\"width:8%;\">Item #</th>\n          <th style=\"width:34%;\">Description</th>\n          <th style=\"width:12%;\">Qty</th>\n          <th style=\"width:15%;\">Rate</th>\n          <th style=\"width:15%;\">Tax</th>\n          <th style=\"width:16%;\">Line Total</th>\n        </tr>\n      </thead>\n      <tbody>\n                            <tr>\n            <td>1</td>\n            <td>\n              High-Performance GPU Servers (Model XZ-900)\n                              <div class=\"line-meta\">\n                                      <span>Country of Origin: USA</span> |                                       <span>No. of Pkgs: 12</span> |                                       <span>Type of Pkging: Pallet</span> |                                       <span>HS Code: 847150</span> |                                       <span>Unit of Measure: Units</span> |                                       <span>Weight: 580.40</span>                                  </div>\n                          </td>\n            <td class=\"amount\">12.00</td>\n            <td class=\"amount\">A$ 12,999.99</td>\n            <td>N/A</td>\n            <td class=\"amount\">A$ 155,999.88</td>\n          </tr>\n                            <tr>\n            <td>2</td>\n            <td>\n              Autometed Robotic Arms- Industrial Grade (RA-4500 Series)\n                              <div class=\"line-meta\">\n                                      <span>Country of Origin: Germany</span> |                                       <span>No. of Pkgs: 45</span> |                                       <span>Type of Pkging: Crate</span> |                                       <span>HS Code: 842890</span> |                                       <span>Unit of Measure: Units</span> |                                       <span>Weight: 2765.00</span>                                  </div>\n                          </td>\n            <td class=\"amount\">45.00</td>\n            <td class=\"amount\">A$ 18,450.50</td>\n            <td>N/A</td>\n            <td class=\"amount\">A$ 830,272.50</td>\n          </tr>\n                            <tr>\n            <td>3</td>\n            <td>\n              5G Newtwork Infrastructure Modules + Edge Computing Nodes\n                              <div class=\"line-meta\">\n                                      <span>Country of Origin: Japan</span> |                                       <span>No. of Pkgs: 200</span> |                                       <span>Type of Pkging: Box</span> |                                       <span>HS Code: 851762</span> |                                       <span>Unit of Measure: Units</span> |                                       <span>Weight: 1500.80</span>                                  </div>\n                          </td>\n            <td class=\"amount\">200.00</td>\n            <td class=\"amount\">A$ 545.75</td>\n            <td>N/A</td>\n            <td class=\"amount\">A$ 109,150.00</td>\n          </tr>\n                            <tr>\n            <td>4</td>\n            <td>\n              Io Smart Sensor- Multi-Protoco; (LoRaWAN/NB-IoT/WiFi)\n                              <div class=\"line-meta\">\n                                      <span>Country of Origin: China</span> |                                       <span>No. of Pkgs: 1500</span> |                                       <span>Type of Pkging: Container</span> |                                       <span>HS Code: 902610</span> |                                       <span>Unit of Measure: Units</span> |                                       <span>Weight: 1100.20</span>                                  </div>\n                          </td>\n            <td class=\"amount\">1,500.00</td>\n            <td class=\"amount\">A$ 19.90</td>\n            <td>N/A</td>\n            <td class=\"amount\">A$ 29,850.00</td>\n          </tr>\n                            <tr>\n            <td>5</td>\n            <td>\n              OLED Flexible Displays - Grade A Enterprise Batch\n                              <div class=\"line-meta\">\n                                      <span>Country of Origin: South Korea</span> |                                       <span>No. of Pkgs: 75</span> |                                       <span>Type of Pkging: Box</span> |                                       <span>HS Code: 901380</span> |                                       <span>Unit of Measure: Units</span> |                                       <span>Weight: 430.00</span>                                  </div>\n                          </td>\n            <td class=\"amount\">75.00</td>\n            <td class=\"amount\">A$ 780.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">A$ 58,500.00</td>\n          </tr>\n                            <tr>\n            <td>6</td>\n            <td>\n              AI-Powered Supply Chain Optimization Software Licenses ( Enterprise Tier)\n                              <div class=\"line-meta\">\n                                      <span>Country of Origin: France</span> |                                       <span>No. of Pkgs: 10</span> |                                       <span>Type of Pkging: Pallet</span> |                                       <span>HS Code: 852380</span> |                                       <span>Unit of Measure: Licenses</span> |                                       <span>Weight: 0.00</span>                                  </div>\n                          </td>\n            <td class=\"amount\">10.00</td>\n            <td class=\"amount\">A$ 25,000.00</td>\n            <td>N/A</td>\n            <td class=\"amount\">A$ 250,000.00</td>\n          </tr>\n              </tbody>\n    </table>\n\n    <table class=\"totals-wrap\">\n      <tr>\n        <td></td>\n        <td class=\"totals-box\">\n          <table>\n            <tr><td class=\"label\">Net Total</td><td class=\"value\">A$ 1,433,772.38</td></tr>\n            <tr><td class=\"label\">Line-Level Taxes</td><td class=\"value\">A$ 0.00</td></tr>\n            <tr><td class=\"label\">Subtotal</td><td class=\"value\">A$ 1,433,772.38</td></tr>\n                                      <tr><td class=\"label\">Adjusted Subtotal</td><td class=\"value\">A$ 1,433,772.38</td></tr>\n                                      <tr><td class=\"label\">B (5.00% on Adjusted Subtotal)</td><td class=\"value\">A$ 71,688.62</td></tr>\n                                    <tr><td class=\"label\">Total Taxes</td><td class=\"value\">A$ 71,688.62</td></tr>\n            <tr class=\"grand\"><td class=\"label\">Grand Total</td><td class=\"value\">A$ 1,505,461.00</td></tr>\n          </table>\n        </td>\n      </tr>\n    </table>\n\n          <!-- PAY_NOW_BLOCK_START -->\n      <div class=\"pay-now-row\">\n        <a href=\"#\" class=\"pay-now-button pay-now-button-disabled\" target=\"_blank\">Pay Now</a>\n      </div>\n      <!-- PAY_NOW_BLOCK_END -->\n    \n    <div class=\"tax-summary\">\n      <h4>Tax Summary</h4>\n      <table>\n        <tr><td>Net Total</td><td>A$ 1,433,772.38</td></tr>\n        <tr><td>Total Line-Level Taxes</td><td>A$ 0.00</td></tr>\n                          <tr><td>B (5.00% on Adjusted Subtotal)</td><td>A$ 71,688.62</td></tr>\n                        <tr><td>Total Taxes</td><td>A$ 71,688.62</td></tr>\n      </table>\n    </div>\n\n    <div class=\"invoice-footer\">\n              This invoice is generated according to your company settings and tax configuration.\n          </div>\n  </div>\n</body>\n</html>\n', NULL, 'Manual', 1, 1, 1, 'monthly', '2026-03-16', 'AUD', 'A$', '#FFDC00', '#0033D9', '{\"taxable_on\":true,\"net_total\":1433772.379999999888241291046142578125,\"line_taxes\":[],\"line_tax_total\":0,\"subtotal\":1433772.379999999888241291046142578125,\"invoice_subtotal_taxes\":[],\"invoice_adjusted_taxes\":[{\"id\":2,\"label\":\"B (5.00% on Adjusted Subtotal)\",\"amount\":71688.619999999995343387126922607421875}],\"invoice_tax_total\":71688.619999999995343387126922607421875,\"invoice_taxes\":[{\"id\":2,\"label\":\"B (5.00% on Adjusted Subtotal)\",\"amount\":71688.619999999995343387126922607421875}],\"invoice_subtotal_tax_total\":0,\"adjusted_subtotal\":1433772.379999999888241291046142578125,\"total_taxes\":71688.619999999995343387126922607421875,\"grand_total\":1505461}', '2026-02-16 22:27:33', '2026-02-16 22:27:33', NULL);
INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `bill_to_name`, `bill_to_json`, `total_amount`, `invoice_date`, `due_date`, `status`, `html`, `payment_link`, `payment_provider`, `created_by`, `show_bank_details`, `is_recurring`, `recurrence_type`, `next_run_date`, `currency_code`, `currency_display`, `invoice_title_bg`, `invoice_title_text`, `invoice_tax_summary`, `created_at`, `updated_at`, `deleted_at`) VALUES
(14, 'INV-AJ-01', 14, 'Amilia James', '{\"Company Name\":\"Amilia James\",\"Contact Name\":\"Web Development Labs\",\"Address\":\"985 NY street 2 US\",\"Phone\":\"44597862130\",\"Email\":\"mrshabibahassan@gmail.com\",\"Bank Account Holder\":\"Habiba Hassan\",\"Bank Name\":\"Allied Bank\",\"Bank Account Number\":\"12302564952\",\"Bank IBAN\":\"PK8540ABL12302564952\",\"Bank SWIFT\":\"\",\"Bank Routing Code\":\"\",\"Payment Instructions\":\"Test\"}', 555199.92, '2026-01-01 00:00:00', '2026-02-17 00:00:00', 'Unpaid', '<!doctype html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Invoice INV-AJ-01</title>\n  <style>\n    @page {\n      size: A4 portrait;\n      margin: 11mm;\n    }\n    body {\n      font-family: DejaVu Sans, Arial, sans-serif;\n      color: #10233c;\n      font-size: 10px;\n      margin: 0;\n      line-height: 1.25;\n      background: #ffffff;\n    }\n    @media print {\n      body {\n        -webkit-print-color-adjust: exact;\n        print-color-adjust: exact;\n      }\n    }\n    .invoice-shell {\n      width: 100%;\n    }\n    .invoice-header {\n      border-bottom: 1px solid #dbe2ea;\n      padding-bottom: 8px;\n      margin-bottom: 8px;\n    }\n    .header-grid {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .header-grid td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .company-col {\n      width: 56%;\n      padding-right: 12px;\n    }\n    .billto-col {\n      width: 44%;\n      text-align: right;\n    }\n    .logo-wrap {\n      margin-bottom: 6px;\n      min-height: 24px;\n    }\n    .logo-wrap img {\n      max-width: 120px;\n      max-height: 44px;\n    }\n    .logo-fallback {\n      display: inline-block;\n      font-size: 15px;\n      font-weight: 700;\n      color: #0b4bd8;\n    }\n    .company-name {\n      font-size: 15px;\n      font-weight: 700;\n      margin: 0 0 2px;\n      color: #0d274f;\n    }\n    .muted {\n      color: #4e6078;\n    }\n    .invoice-band {\n      margin-top: 6px;\n      display: inline-block;\n      padding: 4px 8px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.8px;\n      text-transform: uppercase;\n      background: #FFDC00;\n      color: #0033D9;\n    }\n    .billto-title {\n      font-weight: 700;\n      margin-bottom: 6px;\n      color: #10233c;\n    }\n    .invoice-meta {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 8px;\n    }\n    .invoice-meta td {\n      border: none;\n      padding: 0;\n      vertical-align: top;\n    }\n    .meta-right {\n      text-align: right;\n    }\n    .meta-label {\n      font-size: 9px;\n      color: #4e6078;\n      text-transform: uppercase;\n      letter-spacing: 0.4px;\n    }\n    .meta-value {\n      font-size: 10px;\n      font-weight: 600;\n      color: #10233c;\n      margin-bottom: 2px;\n    }\n    table {\n      width: 100%;\n      border-collapse: collapse;\n    }\n    .line-table {\n      margin-bottom: 8px;\n    }\n    .line-table th,\n    .line-table td {\n      border: 1px solid #dbe2ea;\n      padding: 4px 5px;\n      vertical-align: top;\n    }\n    .line-table th {\n      background: #f3f6fb;\n      color: #153157;\n      font-size: 9px;\n      text-transform: uppercase;\n      letter-spacing: 0.3px;\n    }\n    .line-table td {\n      font-size: 10px;\n    }\n    .amount {\n      text-align: right;\n      white-space: nowrap;\n    }\n    .line-meta {\n      margin-top: 2px;\n      font-size: 8px;\n      color: #5a6e89;\n    }\n    .line-meta span {\n      white-space: nowrap;\n    }\n    .totals-wrap {\n      width: 100%;\n      border-collapse: collapse;\n      margin-bottom: 6px;\n    }\n    .totals-wrap td {\n      border: none;\n      vertical-align: top;\n      padding: 0;\n    }\n    .totals-box {\n      width: 46%;\n      margin-left: auto;\n    }\n    .totals-box table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 10px;\n    }\n    .totals-box .label {\n      color: #3d4f67;\n    }\n    .totals-box .value {\n      text-align: right;\n      font-weight: 600;\n      color: #10233c;\n      white-space: nowrap;\n    }\n    .totals-box .grand .label,\n    .totals-box .grand .value {\n      font-size: 11px;\n      font-weight: 700;\n      color: #10233c;\n      padding-top: 3px;\n      border-top: 1px solid #dbe2ea;\n    }\n    .tax-summary {\n      margin-top: 7px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n    }\n    .pay-now-row {\n      margin-top: 4px;\n      margin-bottom: 7px;\n      text-align: left;\n    }\n    .pay-now-button {\n      display: inline-block;\n      background: #0d6efd;\n      color: #ffffff;\n      text-decoration: none;\n      padding: 4px 9px;\n      border-radius: 4px;\n      font-size: 10px;\n      font-weight: 700;\n      letter-spacing: 0.2px;\n    }\n    .pay-now-button-disabled {\n      background: #8a94a8;\n      pointer-events: none;\n    }\n    .tax-summary h4 {\n      margin: 0 0 3px;\n      font-size: 10px;\n      text-transform: uppercase;\n      letter-spacing: 0.5px;\n      color: #2a3f60;\n    }\n    .tax-summary table td {\n      border: none;\n      padding: 1px 0;\n      font-size: 9px;\n    }\n    .tax-summary table td:last-child {\n      text-align: right;\n      white-space: nowrap;\n      font-weight: 600;\n    }\n    .invoice-footer {\n      margin-top: 8px;\n      border-top: 1px solid #dbe2ea;\n      padding-top: 6px;\n      font-size: 8px;\n      color: #526784;\n      text-align: center;\n    }\n  </style>\n</head>\n<body>\n  \n  <div class=\"invoice-shell\">\n    <div class=\"invoice-header\">\n      <table class=\"header-grid\">\n        <tr>\n          <td class=\"company-col\">\n            <div class=\"logo-wrap\">\n                              <span class=\"logo-fallback\">DocuBills</span>\n                          </div>\n            <div class=\"company-name\">DocuBills</div>\n                                                            <div class=\"invoice-band\">Invoice</div>\n          </td>\n          <td class=\"billto-col\">\n            <div class=\"billto-title\">Bill To</div>\n            <div>Amilia James</div>\n            <div>Web Development Labs</div>            <div>985 NY street 2 US</div>            <div>44597862130</div>            <div>mrshabibahassan@gmail.com</div>                          <div style=\"margin-top:8px; font-size:11px;\">\n                <strong>Banking Details</strong><br>\n                 Account Holder: Habiba Hassan<br>                 Bank: Allied Bank<br>                 Account No: 12302564952<br>                 IBAN: PK8540ABL12302564952<br>                                                 Instructions: Test              </div>\n                      </td>\n        </tr>\n      </table>\n    </div>\n\n    <table class=\"invoice-meta\">\n      <tr>\n        <td>\n          <div class=\"meta-label\">Invoice Number</div>\n          <div class=\"meta-value\">INV-AJ-01</div>\n        </td>\n        <td class=\"meta-right\">\n          <div class=\"meta-label\">Currency</div>\n          <div class=\"meta-value\">USD $</div>\n        </td>\n      </tr>\n    </table>\n\n    <table class=\"line-table\">\n      <thead>\n        <tr>\n          <th style=\"width:8%;\">Item #</th>\n          <th style=\"width:34%;\">Description</th>\n          <th style=\"width:12%;\">Qty</th>\n          <th style=\"width:15%;\">Rate</th>\n          <th style=\"width:15%;\">Tax</th>\n          <th style=\"width:16%;\">Line Total</th>\n        </tr>\n      </thead>\n      <tbody>\n                            <tr>\n            <td>1</td>\n            <td>\n              15 &quot; Enterprise Laptop Pro – Intel i7, 32GB RAM, 1TB NVMe, Win 11 Ent\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: LAPTOP-15PRO-INTEL-I7-32-1TB</span> |                                       <span>Material Content: Aluminum chassis, PCB, Lithium battery, Glass display, Plastics</span>                                  </div>\n                          </td>\n            <td class=\"amount\">150.00</td>\n            <td class=\"amount\">$ 1,899.95</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 284,992.50</td>\n          </tr>\n                            <tr>\n            <td>2</td>\n            <td>\n              USB‑C Universal Dock – 10-port with Dual 4K Display Support\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: DOCK-USB-C-ULTRA-10P</span> |                                       <span>Material Content: Aluminum housing, Copper wiring, PCB, ABS plastic</span>                                  </div>\n                          </td>\n            <td class=\"amount\">150.00</td>\n            <td class=\"amount\">$ 249.50</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 37,425.00</td>\n          </tr>\n                            <tr>\n            <td>3</td>\n            <td>\n              Executive Laptop Backpack – Water-Resistant, TSA-Ready\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-001</span> |                                       <span>SKU: BAG-BACKPACK-EXEC-BLACK</span> |                                       <span>Material Content: Polyester 70%, Nylon 20%, PU coating 10%</span>                                  </div>\n                          </td>\n            <td class=\"amount\">150.00</td>\n            <td class=\"amount\">$ 89.90</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 13,485.00</td>\n          </tr>\n                            <tr>\n            <td>4</td>\n            <td>\n              27&quot; 4K UHD Monitor – HDR10, USB‑C Power Delivery\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-002</span> |                                       <span>SKU: MONITOR-27-UHD-HDR</span> |                                       <span>Material Content: Aluminum stand, Plastic housing, Glass panel, PCB</span>                                  </div>\n                          </td>\n            <td class=\"amount\">220.00</td>\n            <td class=\"amount\">$ 379.75</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 83,545.00</td>\n          </tr>\n                            <tr>\n            <td>5</td>\n            <td>\n              Dual Gas-Spring Monitor Arm – Desk Mount\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-002</span> |                                       <span>SKU: ARM-DUAL-MONITOR-GAS</span> |                                       <span>Material Content: Steel 60%, Aluminum 25%, Plastics 15%</span>                                  </div>\n                          </td>\n            <td class=\"amount\">220.00</td>\n            <td class=\"amount\">$ 129.40</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 28,468.00</td>\n          </tr>\n                            <tr>\n            <td>6</td>\n            <td>\n              UC Certified Wireless Headset with Active Noise Cancelling + Dongle\n                              <div class=\"line-meta\">\n                                      <span>PO #: PO-2025-003</span> |                                       <span>SKU: HEADSET-UC-WIRELESS-ANC</span> |                                       <span>Material Content: Plastics, Synthetic leather, Lithium battery, Copper wiring</span>                                  </div>\n                          </td>\n            <td class=\"amount\">350.00</td>\n            <td class=\"amount\">$ 219.99</td>\n            <td>N/A</td>\n            <td class=\"amount\">$ 76,996.50</td>\n          </tr>\n              </tbody>\n    </table>\n\n    <table class=\"totals-wrap\">\n      <tr>\n        <td></td>\n        <td class=\"totals-box\">\n          <table>\n            <tr><td class=\"label\">Net Total</td><td class=\"value\">$ 524,912.00</td></tr>\n            <tr><td class=\"label\">Line-Level Taxes</td><td class=\"value\">$ 3,849.83</td></tr>\n            <tr><td class=\"label\">Subtotal</td><td class=\"value\">$ 528,761.83</td></tr>\n                                      <tr><td class=\"label\">Adjusted Subtotal</td><td class=\"value\">$ 528,761.83</td></tr>\n                                      <tr><td class=\"label\">B (5.00% on Adjusted Subtotal)</td><td class=\"value\">$ 26,438.09</td></tr>\n                                    <tr><td class=\"label\">Total Taxes</td><td class=\"value\">$ 30,287.92</td></tr>\n            <tr class=\"grand\"><td class=\"label\">Grand Total</td><td class=\"value\">$ 555,199.92</td></tr>\n          </table>\n        </td>\n      </tr>\n    </table>\n\n          \n    \n    <div class=\"tax-summary\">\n      <h4>Tax Summary</h4>\n      <table>\n        <tr><td>Net Total</td><td>$ 524,912.00</td></tr>\n        <tr><td>Total Line-Level Taxes</td><td>$ 3,849.83</td></tr>\n                          <tr><td>B (5.00% on Adjusted Subtotal)</td><td>$ 26,438.09</td></tr>\n                        <tr><td>Total Taxes</td><td>$ 30,287.92</td></tr>\n      </table>\n    </div>\n\n    \n    <!-- PAY_NOW_BLOCK_START -->\n    <div class=\"pay-now-row\">\n      <a href=\"#\" class=\"pay-now-button pay-now-button-disabled\" target=\"_blank\">Pay Now</a>\n    </div>\n    <!-- PAY_NOW_BLOCK_END -->\n<div class=\"invoice-footer\">\n              This invoice is generated according to your company settings and tax configuration.\n          </div>\n  </div>\n</body>\n</html>\n', NULL, 'Manual', 1, 1, 1, 'monthly', '2026-02-01', 'USD', '$', '#FFDC00', '#0033D9', '{\"taxable_on\":true,\"net_total\":524912,\"line_taxes\":[{\"id\":1,\"label\":\"A (5.00%)\",\"amount\":3849.829999999999927240423858165740966796875}],\"line_tax_total\":3849.829999999999927240423858165740966796875,\"subtotal\":528761.829999999958090484142303466796875,\"invoice_subtotal_taxes\":[],\"invoice_adjusted_taxes\":[{\"id\":2,\"label\":\"B (5.00% on Adjusted Subtotal)\",\"amount\":26438.09000000000014551915228366851806640625}],\"invoice_tax_total\":26438.09000000000014551915228366851806640625,\"invoice_taxes\":[{\"id\":2,\"label\":\"B (5.00% on Adjusted Subtotal)\",\"amount\":26438.09000000000014551915228366851806640625}],\"invoice_subtotal_tax_total\":0,\"adjusted_subtotal\":528761.829999999958090484142303466796875,\"total_taxes\":30287.919999999998253770172595977783203125,\"grand_total\":555199.920000000041909515857696533203125}', '2026-02-17 13:39:44', '2026-02-17 13:39:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_email_configurations`
--

CREATE TABLE `invoice_email_configurations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_confirmation_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_email_configurations`
--

INSERT INTO `invoice_email_configurations` (`id`, `invoice_id`, `delivery_template_id`, `payment_confirmation_template_id`, `created_at`, `updated_at`) VALUES
(1, 11, 1, 1, '2026-02-16 17:26:22', '2026-02-16 17:26:22'),
(2, 12, 1, 1, '2026-02-16 17:41:13', '2026-02-16 17:41:13'),
(3, 13, 1, 1, '2026-02-16 22:27:33', '2026-02-16 22:27:33'),
(4, 14, 1, 1, '2026-02-17 13:39:44', '2026-02-17 13:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_reminder_logs`
--

CREATE TABLE `invoice_reminder_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `sent_at` timestamp NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'sent',
  `reminder_type` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rule_id` varchar(64) DEFAULT NULL,
  `template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_sent_scope` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_reminder_template_bindings`
--

CREATE TABLE `invoice_reminder_template_bindings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `rule_id` varchar(64) NOT NULL,
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_reminder_template_bindings`
--

INSERT INTO `invoice_reminder_template_bindings` (`id`, `invoice_id`, `rule_id`, `template_id`, `created_at`, `updated_at`) VALUES
(1, 11, 'on_due', 1, '2026-02-16 17:26:22', '2026-02-16 17:26:22'),
(2, 12, 'on_due', 1, '2026-02-16 17:41:13', '2026-02-16 17:41:13'),
(3, 13, 'on_due', 1, '2026-02-16 22:27:33', '2026-02-16 22:27:33'),
(4, 14, 'on_due', 1, '2026-02-17 13:39:44', '2026-02-17 13:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_templates`
--

CREATE TABLE `invoice_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `html` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"78a00228-02ef-4699-9171-1241a791be8c\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"30,120,300\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"command\":\"O:36:\\\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\\\":2:{s:9:\\\"invoiceId\\\";i:11;s:7:\\\"pdfPath\\\";s:22:\\\"invoices\\/INV-TC-01.pdf\\\";}\"},\"createdAt\":1771262782,\"delay\":null}', 0, NULL, 1771262782, 1771262782),
(2, 'default', '{\"uuid\":\"107de87e-4c60-456f-b966-1c527f11c222\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentConfirmationEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"30,120,300\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentConfirmationEmailJob\",\"command\":\"O:40:\\\"App\\\\Jobs\\\\SendPaymentConfirmationEmailJob\\\":1:{s:9:\\\"invoiceId\\\";i:11;}\"},\"createdAt\":1771263187,\"delay\":null}', 0, NULL, 1771263187, 1771263187),
(3, 'default', '{\"uuid\":\"04c28494-6bd4-462f-bbce-758f68a5f41d\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"30,120,300\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"command\":\"O:36:\\\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\\\":2:{s:9:\\\"invoiceId\\\";i:12;s:7:\\\"pdfPath\\\";s:22:\\\"invoices\\/INV-T2-01.pdf\\\";}\"},\"createdAt\":1771263674,\"delay\":null}', 0, NULL, 1771263674, 1771263674),
(4, 'default', '{\"uuid\":\"4e5c62d2-664e-428b-99c6-6fe851715935\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"30,120,300\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"command\":\"O:36:\\\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\\\":2:{s:9:\\\"invoiceId\\\";i:13;s:7:\\\"pdfPath\\\";s:22:\\\"invoices\\/INV-HH-01.pdf\\\";}\"},\"createdAt\":1771280854,\"delay\":null}', 0, NULL, 1771280854, 1771280854),
(5, 'default', '{\"uuid\":\"408d45e4-9c50-44d9-aa5f-757555131156\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"30,120,300\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\",\"command\":\"O:36:\\\"App\\\\Jobs\\\\SendInvoiceDeliveryEmailJob\\\":2:{s:9:\\\"invoiceId\\\";i:14;s:7:\\\"pdfPath\\\";s:22:\\\"invoices\\/INV-AJ-01.pdf\\\";}\"},\"createdAt\":1771335585,\"delay\":null}', 0, NULL, 1771335585, 1771335585);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `username`, `ip_address`, `user_agent`, `status`, `created_at`) VALUES
(1, NULL, 'noraparker', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-14 17:43:21'),
(2, 1, 'noraparker', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success', '2026-02-14 17:48:38'),
(3, 1, 'noraparker', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success', '2026-02-14 17:54:58'),
(4, 1, 'noraparker', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success', '2026-02-14 18:04:13'),
(5, 1, 'noraparker', '124.29.253.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 16:12:32'),
(6, NULL, 'noraparkar', '124.29.253.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 16:12:49'),
(7, 1, 'noraparker', '124.29.253.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success', '2026-02-16 16:52:55'),
(8, NULL, 'marto9ine', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 18:20:48'),
(9, NULL, 'admin', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 18:20:58'),
(10, NULL, 'womenfirstinc', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 18:21:14'),
(11, NULL, 'marto9ine', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 18:22:53'),
(12, NULL, 'marto9ine', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 18:22:59'),
(13, NULL, 'marto9ine', '72.255.7.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'failure', '2026-02-16 18:23:07'),
(14, 1, 'noraparker', '124.29.253.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success', '2026-02-16 22:20:37'),
(15, 1, 'noraparker', '124.29.253.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success', '2026-02-17 12:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_25_191655_create_roles_table', 1),
(5, '2026_01_25_191700_create_permissions_table', 1),
(6, '2026_01_25_191704_create_role_permissions_table', 1),
(7, '2026_01_25_191709_create_user_sessions_table', 1),
(8, '2026_01_25_191713_create_login_logs_table', 1),
(9, '2026_01_25_191718_update_users_table_for_auth', 1),
(10, '2026_01_25_192114_create_settings_table', 1),
(11, '2026_01_25_192436_create_taxes_table', 1),
(12, '2026_01_25_204400_create_clients_table', 1),
(13, '2026_01_25_210222_create_invoices_table', 1),
(14, '2026_01_25_210712_create_expenses_table', 1),
(15, '2026_01_25_211459_create_email_templates_table', 1),
(16, '2026_01_25_211507_create_invoice_reminder_logs_table', 1),
(17, '2026_02_02_164310_add_created_at_to_login_logs_table', 1),
(18, '2026_02_02_170337_add_timestamps_to_permissions_table', 1),
(19, '2026_02_02_172224_add_timestamps_to_users_table_if_missing', 1),
(20, '2026_02_02_200000_fix_roles_name_for_super_admin', 1),
(21, '2026_02_02_230000_drop_users_role_column', 1),
(22, '2026_02_03_120000_create_invoice_templates_table', 1),
(23, '2026_02_11_210000_add_invoice_tax_summary_to_invoices_table', 1),
(24, '2026_02_13_010000_create_notification_types_table', 1),
(25, '2026_02_13_010100_add_notification_fields_to_email_and_reminder_tables', 1),
(26, '2026_02_13_010200_add_unique_index_to_invoice_reminder_logs', 1),
(27, '2026_02_13_010300_backfill_notification_and_reminder_v2_settings', 1),
(28, '2026_02_13_020000_add_category_to_email_templates_if_missing', 1),
(29, '2026_02_13_220000_add_builder_fields_to_email_templates_table', 1),
(30, '2026_02_14_000000_create_invoice_email_configuration_tables', 1),
(31, '2026_02_14_000100_add_template_id_to_invoice_reminder_logs', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(120) NOT NULL,
  `label` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'view_dashboard', 'View dashboard', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(2, 'access_reports', 'Access reports', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(3, 'access_history', 'Access history', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(4, 'create_invoice', 'Create invoice', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(5, 'delete_invoice', 'Delete invoice', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(6, 'edit_invoice', 'Edit invoice', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(7, 'save_invoice', 'Save invoice', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(8, 'view_invoices', 'View invoices', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(9, 'mark_invoice_paid', 'Mark invoice as paid', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(10, 'download_invoice_pdf', 'Download invoice PDF', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(11, 'email_invoice', 'Email invoice', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(12, 'restore_invoices', 'Restore deleted invoices', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(13, 'view_invoice_payment_info', 'View invoice payment info', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(14, 'delete_forever', 'Delete invoice forever', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(15, 'view_invoice_history', 'View invoice history', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(16, 'view_invoice_logs', 'View invoice logs', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(17, 'add_invoice_field', 'Add invoice field', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(18, 'show_due_date', 'Show due date', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(19, 'show_due_time', 'Show due time', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(20, 'show_invoice_date', 'Show invoice date', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(21, 'show_invoice_time', 'Show invoice time', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(22, 'show_invoice_checkboxes', 'Show invoice checkboxes', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(23, 'toggle_bank_details', 'Toggle bank details', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(24, 'manage_recurring_invoices', 'Manage recurring invoices', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(25, 'access_clients_tab', 'Access clients tab', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(26, 'view_clients', 'View own clients', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(27, 'view_all_clients', 'View all clients', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(28, 'add_client', 'Add client', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(29, 'edit_client', 'Edit client', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(30, 'delete_client', 'Delete client', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(31, 'restore_clients', 'Restore deleted clients', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(32, 'undo_recent_client', 'Undo recent client deletion', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(33, 'undo_all_clients', 'Undo all client deletions', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(34, 'export_clients', 'Export clients', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(35, 'search_clients', 'Search clients', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(36, 'access_expenses_tab', 'Access expenses tab', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(37, 'view_expenses', 'View own expenses', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(38, 'view_all_expenses', 'View all expenses', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(39, 'add_expense', 'Add expense', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(40, 'edit_expense', 'Edit expense', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(41, 'delete_expense', 'Delete expense', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(42, 'undo_recent_expense', 'Undo recent expense deletion', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(43, 'undo_all_expenses', 'Undo all expense deletions', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(44, 'change_expense_status', 'Change expense status', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(45, 'view_expense_details', 'View expense details', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(46, 'search_expenses', 'Search expenses', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(47, 'export_expenses', 'Export expenses', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(48, 'view_expenses_trashbin', 'View own expenses trash bin', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(49, 'view_all_expenses_trashbin', 'View all expenses trash bin', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(50, 'delete_expense_forever', 'Delete expense forever', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(51, 'update_basic_settings', 'Update basic settings', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(52, 'access_basic_settings', 'Access basic settings', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(53, 'assign_roles', 'Assign roles', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(54, 'manage_users', 'Manage users', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(55, 'manage_users_page', 'Manage users page', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(56, 'add_user', 'Add new user', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(57, 'edit_user', 'Edit user', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(58, 'delete_user', 'Delete user', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(59, 'suspend_users', 'Suspend users', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(60, 'manage_permissions', 'Manage permissions', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(61, 'manage_payment_methods', 'Manage payment methods', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(62, 'manage_card_payments', 'Manage card payments', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(63, 'manage_bank_details', 'Manage bank details', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(64, 'manage_reminder_settings', 'Manage reminder settings', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(65, 'access_email_templates_page', 'Access email templates page', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(66, 'add_email_template', 'Add email template', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(67, 'edit_email_template', 'Edit email template', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(68, 'delete_email_template', 'Delete email template', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(69, 'manage_notification_categories', 'Manage notification categories', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(70, 'manage_role_viewable', 'Manage role viewable', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(71, 'access_trashbin', 'Access trash bin', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(72, 'view_all_trash', 'View all trash', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(73, 'restore_deleted_items', 'Restore deleted items', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(74, 'access_support', 'Access support', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(75, 'view_login_logs', 'View login logs', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(76, 'terminate_sessions', 'Terminate sessions', '2026-02-14 18:03:04', '2026-02-14 18:03:04'),
(77, 'terminate_own_session', 'Terminate own session', '2026-02-14 18:03:04', '2026-02-14 18:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', '2026-02-14 18:02:55', '2026-02-14 18:02:55'),
(2, 'admin', '2026-02-14 18:02:55', '2026-02-14 18:02:55'),
(3, 'manager', '2026-02-14 18:02:55', '2026-02-14 18:02:55'),
(4, 'assistant', '2026-02-14 18:02:55', '2026-02-14 18:02:55'),
(5, 'viewer', '2026-02-14 18:02:55', '2026-02-14 18:02:55');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 52, NULL, NULL),
(2, 1, 25, NULL, NULL),
(3, 1, 65, NULL, NULL),
(4, 1, 36, NULL, NULL),
(5, 1, 3, NULL, NULL),
(6, 1, 2, NULL, NULL),
(7, 1, 74, NULL, NULL),
(8, 1, 71, NULL, NULL),
(9, 1, 28, NULL, NULL),
(10, 1, 66, NULL, NULL),
(11, 1, 39, NULL, NULL),
(12, 1, 17, NULL, NULL),
(13, 1, 56, NULL, NULL),
(14, 1, 53, NULL, NULL),
(15, 1, 44, NULL, NULL),
(16, 1, 4, NULL, NULL),
(17, 1, 30, NULL, NULL),
(18, 1, 68, NULL, NULL),
(19, 1, 41, NULL, NULL),
(20, 1, 50, NULL, NULL),
(21, 1, 14, NULL, NULL),
(22, 1, 5, NULL, NULL),
(23, 1, 58, NULL, NULL),
(24, 1, 10, NULL, NULL),
(25, 1, 29, NULL, NULL),
(26, 1, 67, NULL, NULL),
(27, 1, 40, NULL, NULL),
(28, 1, 6, NULL, NULL),
(29, 1, 57, NULL, NULL),
(30, 1, 11, NULL, NULL),
(31, 1, 34, NULL, NULL),
(32, 1, 47, NULL, NULL),
(33, 1, 63, NULL, NULL),
(34, 1, 62, NULL, NULL),
(35, 1, 69, NULL, NULL),
(36, 1, 61, NULL, NULL),
(37, 1, 60, NULL, NULL),
(38, 1, 24, NULL, NULL),
(39, 1, 64, NULL, NULL),
(40, 1, 70, NULL, NULL),
(41, 1, 54, NULL, NULL),
(42, 1, 55, NULL, NULL),
(43, 1, 9, NULL, NULL),
(44, 1, 31, NULL, NULL),
(45, 1, 73, NULL, NULL),
(46, 1, 12, NULL, NULL),
(47, 1, 7, NULL, NULL),
(48, 1, 35, NULL, NULL),
(49, 1, 46, NULL, NULL),
(50, 1, 18, NULL, NULL),
(51, 1, 19, NULL, NULL),
(52, 1, 22, NULL, NULL),
(53, 1, 20, NULL, NULL),
(54, 1, 21, NULL, NULL),
(55, 1, 59, NULL, NULL),
(56, 1, 77, NULL, NULL),
(57, 1, 76, NULL, NULL),
(58, 1, 23, NULL, NULL),
(59, 1, 33, NULL, NULL),
(60, 1, 43, NULL, NULL),
(61, 1, 32, NULL, NULL),
(62, 1, 42, NULL, NULL),
(63, 1, 51, NULL, NULL),
(64, 1, 27, NULL, NULL),
(65, 1, 38, NULL, NULL),
(66, 1, 49, NULL, NULL),
(67, 1, 72, NULL, NULL),
(68, 1, 26, NULL, NULL),
(69, 1, 1, NULL, NULL),
(70, 1, 45, NULL, NULL),
(71, 1, 37, NULL, NULL),
(72, 1, 48, NULL, NULL),
(73, 1, 15, NULL, NULL),
(74, 1, 16, NULL, NULL),
(75, 1, 13, NULL, NULL),
(76, 1, 8, NULL, NULL),
(77, 1, 75, NULL, NULL),
(78, 2, 52, NULL, NULL),
(79, 2, 25, NULL, NULL),
(80, 2, 65, NULL, NULL),
(81, 2, 36, NULL, NULL),
(82, 2, 3, NULL, NULL),
(83, 2, 2, NULL, NULL),
(84, 2, 74, NULL, NULL),
(85, 2, 71, NULL, NULL),
(86, 2, 28, NULL, NULL),
(87, 2, 66, NULL, NULL),
(88, 2, 39, NULL, NULL),
(89, 2, 17, NULL, NULL),
(90, 2, 56, NULL, NULL),
(91, 2, 53, NULL, NULL),
(92, 2, 44, NULL, NULL),
(93, 2, 4, NULL, NULL),
(94, 2, 30, NULL, NULL),
(95, 2, 68, NULL, NULL),
(96, 2, 41, NULL, NULL),
(97, 2, 50, NULL, NULL),
(98, 2, 14, NULL, NULL),
(99, 2, 5, NULL, NULL),
(100, 2, 58, NULL, NULL),
(101, 2, 10, NULL, NULL),
(102, 2, 29, NULL, NULL),
(103, 2, 67, NULL, NULL),
(104, 2, 40, NULL, NULL),
(105, 2, 6, NULL, NULL),
(106, 2, 57, NULL, NULL),
(107, 2, 11, NULL, NULL),
(108, 2, 34, NULL, NULL),
(109, 2, 47, NULL, NULL),
(110, 2, 63, NULL, NULL),
(111, 2, 62, NULL, NULL),
(112, 2, 69, NULL, NULL),
(113, 2, 61, NULL, NULL),
(114, 2, 60, NULL, NULL),
(115, 2, 24, NULL, NULL),
(116, 2, 64, NULL, NULL),
(117, 2, 70, NULL, NULL),
(118, 2, 54, NULL, NULL),
(119, 2, 55, NULL, NULL),
(120, 2, 9, NULL, NULL),
(121, 2, 31, NULL, NULL),
(122, 2, 73, NULL, NULL),
(123, 2, 12, NULL, NULL),
(124, 2, 7, NULL, NULL),
(125, 2, 35, NULL, NULL),
(126, 2, 46, NULL, NULL),
(127, 2, 18, NULL, NULL),
(128, 2, 19, NULL, NULL),
(129, 2, 22, NULL, NULL),
(130, 2, 20, NULL, NULL),
(131, 2, 21, NULL, NULL),
(132, 2, 59, NULL, NULL),
(133, 2, 77, NULL, NULL),
(134, 2, 76, NULL, NULL),
(135, 2, 23, NULL, NULL),
(136, 2, 33, NULL, NULL),
(137, 2, 43, NULL, NULL),
(138, 2, 32, NULL, NULL),
(139, 2, 42, NULL, NULL),
(140, 2, 51, NULL, NULL),
(141, 2, 27, NULL, NULL),
(142, 2, 38, NULL, NULL),
(143, 2, 49, NULL, NULL),
(144, 2, 72, NULL, NULL),
(145, 2, 26, NULL, NULL),
(146, 2, 1, NULL, NULL),
(147, 2, 45, NULL, NULL),
(148, 2, 37, NULL, NULL),
(149, 2, 48, NULL, NULL),
(150, 2, 15, NULL, NULL),
(151, 2, 16, NULL, NULL),
(152, 2, 13, NULL, NULL),
(153, 2, 8, NULL, NULL),
(154, 2, 75, NULL, NULL),
(155, 3, 3, NULL, NULL),
(156, 3, 2, NULL, NULL),
(157, 3, 28, NULL, NULL),
(158, 3, 4, NULL, NULL),
(159, 3, 29, NULL, NULL),
(160, 3, 26, NULL, NULL),
(161, 3, 1, NULL, NULL),
(162, 3, 37, NULL, NULL),
(163, 3, 48, NULL, NULL),
(164, 3, 15, NULL, NULL),
(165, 3, 13, NULL, NULL),
(166, 3, 8, NULL, NULL),
(167, 4, 28, NULL, NULL),
(168, 4, 29, NULL, NULL),
(169, 4, 26, NULL, NULL),
(170, 4, 1, NULL, NULL),
(171, 4, 37, NULL, NULL),
(172, 4, 15, NULL, NULL),
(173, 4, 8, NULL, NULL),
(174, 5, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key_name` varchar(50) NOT NULL,
  `key_value` text NOT NULL,
  `admin_email` varchar(255) DEFAULT NULL,
  `invoice_prefix` varchar(10) NOT NULL DEFAULT 'FIN',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `key_value`, `admin_email`, `invoice_prefix`, `updated_at`, `created_at`) VALUES
(1, 'invoice_email_reminders', '[{\"id\":\"before_due\",\"name\":\"Before due date\",\"enabled\":false,\"direction\":\"before\",\"days\":0,\"offset_days\":0},{\"id\":\"on_due\",\"name\":\"On due date\",\"enabled\":true,\"direction\":\"on\",\"days\":0,\"offset_days\":0},{\"id\":\"after_3\",\"name\":\"3 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":3,\"offset_days\":3},{\"id\":\"after_7\",\"name\":\"7 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":7,\"offset_days\":7},{\"id\":\"after_14\",\"name\":\"14 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":14,\"offset_days\":14},{\"id\":\"after_21\",\"name\":\"21 days after due\",\"enabled\":true,\"direction\":\"after\",\"days\":21,\"offset_days\":21}]', NULL, 'FIN', '2026-02-14 17:39:35', '2026-02-14 17:39:35'),
(2, 'invoice_email_reminder_templates', '{}', NULL, 'FIN', '2026-02-14 17:39:35', '2026-02-14 17:39:35'),
(3, 'reminders_v2_enabled', '1', NULL, 'FIN', '2026-02-14 17:39:35', '2026-02-14 17:39:35'),
(4, 'bank_account_name', 'Habiba Hassan', NULL, 'FIN', NULL, NULL),
(5, 'bank_name', 'Allied Bank', NULL, 'FIN', NULL, NULL),
(6, 'bank_account_number', '12302564952', NULL, 'FIN', NULL, NULL),
(7, 'bank_iban', 'PK8540ABL12302564952', NULL, 'FIN', NULL, NULL),
(8, 'bank_swift', '', NULL, 'FIN', NULL, NULL),
(9, 'bank_routing', '', NULL, 'FIN', NULL, NULL),
(10, 'bank_additional_info', 'Tesint', NULL, 'FIN', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_type` varchar(20) NOT NULL DEFAULT 'line',
  `calc_order` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`id`, `name`, `percentage`, `tax_type`, `calc_order`, `created_at`, `updated_at`) VALUES
(1, 'A', 5.00, 'line', 1, '2026-02-16 17:05:21', '2026-02-16 17:05:21'),
(2, 'B', 5.00, 'invoice', 3, '2026-02-16 17:05:51', '2026-02-16 17:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `full_name`, `password`, `created_at`, `updated_at`, `deleted_at`, `role_id`, `is_suspended`, `avatar`) VALUES
(1, 'noraparker', 'Nora Parker', 'nora@docubills.com', NULL, '$2y$12$8/aKVABgpz8MLkc6fOmIMuGrX8pU.E0mZ4wO8clXekcVpKTTwXMAq', '2026-02-14 17:48:04', '2026-02-14 17:48:04', NULL, 2, 0, NULL),
(2, 'dummydashboard', 'Dummy Dashboard', 'dummy.dashboard@docubills.com', 'Dummy Dashboard User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-02-14 18:07:47', '2026-02-14 18:07:47', NULL, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `terminated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `ip_address`, `last_activity`, `terminated_at`) VALUES
(1, 1, 'LX4C8kHkwBIQHECZF1zXd7a070rRY6HvaR93rUJp', '72.255.7.85', '2026-02-14 17:53:37', '2026-02-14 17:53:37'),
(2, 1, 'a9uTsgu2cfdT4wGHiQayejPzNcbmp6OwrfpDA33K', '72.255.7.85', '2026-02-14 18:04:11', '2026-02-14 18:04:11'),
(3, 1, 'h0PpR0t5m5op2ktUcVjNOoUpGXTZvHpLJFLNMUa8', '72.255.7.85', '2026-02-14 18:08:10', '2026-02-14 18:08:10'),
(4, 1, 'Da8ynTjVovzoKpa0onTmiUxn1osOz0NdWDbKEI2T', '124.29.253.123', '2026-02-16 18:38:47', NULL),
(5, 1, 'e5hSkUNDIqOOXSm4oDeJjQFCqgeBxOEz3tZE8Eey', '124.29.253.123', '2026-02-16 22:28:31', NULL),
(6, 1, '6sFQfb7LmYJlcq3EVnb9QIyPN7ZPNaxQ4W8ZdEQi', '124.29.253.123', '2026-02-17 13:39:48', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_created_by_foreign` (`created_by`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_templates_created_by_foreign` (`created_by`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_client_id_foreign` (`client_id`),
  ADD KEY `expenses_created_by_foreign` (`created_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_client_id_foreign` (`client_id`),
  ADD KEY `invoices_created_by_foreign` (`created_by`);

--
-- Indexes for table `invoice_email_configurations`
--
ALTER TABLE `invoice_email_configurations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_email_configurations_invoice_id_unique` (`invoice_id`),
  ADD KEY `idx_invoice_email_cfg_delivery_tpl` (`delivery_template_id`),
  ADD KEY `idx_invoice_email_cfg_payment_tpl` (`payment_confirmation_template_id`);

--
-- Indexes for table `invoice_reminder_logs`
--
ALTER TABLE `invoice_reminder_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_invoice_reminder_template_cycle` (`invoice_id`,`reminder_type`,`rule_id`,`template_id`,`status_sent_scope`),
  ADD KEY `idx_invoice_reminder_logs_template_id` (`template_id`),
  ADD KEY `idx_irl_invoice_id_fix` (`invoice_id`);

--
-- Indexes for table `invoice_reminder_template_bindings`
--
ALTER TABLE `invoice_reminder_template_bindings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_inv_rule_tpl` (`invoice_id`,`rule_id`,`template_id`),
  ADD KEY `idx_inv_rule` (`invoice_id`,`rule_id`),
  ADD KEY `idx_inv_tpl` (`invoice_id`,`template_id`);

--
-- Indexes for table `invoice_templates`
--
ALTER TABLE `invoice_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_templates_created_by_foreign` (`created_by`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_types`
--
ALTER TABLE `notification_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_types_slug_unique` (`slug`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_name_unique` (`key_name`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_sessions_session_id_unique` (`session_id`),
  ADD KEY `user_sessions_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `invoice_email_configurations`
--
ALTER TABLE `invoice_email_configurations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoice_reminder_logs`
--
ALTER TABLE `invoice_reminder_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_reminder_template_bindings`
--
ALTER TABLE `invoice_reminder_template_bindings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoice_templates`
--
ALTER TABLE `invoice_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notification_types`
--
ALTER TABLE `notification_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD CONSTRAINT `email_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_reminder_logs`
--
ALTER TABLE `invoice_reminder_logs`
  ADD CONSTRAINT `invoice_reminder_logs_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_templates`
--
ALTER TABLE `invoice_templates`
  ADD CONSTRAINT `invoice_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
