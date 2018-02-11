-- Author Andrey Izman <izmanw@gmail.com>
-- License LGPL2


-- OAuth2 token table (required by OAuth2 library)
CREATE TABLE `oauth2_access_token` (
  `access_token` char(40) NOT NULL COMMENT 'Then access token',
  `user_id` int(10) DEFAULT NULL COMMENT 'The user id',
  `client_id` varchar(128) DEFAULT NULL COMMENT 'Client identifier',
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Expiration timestamp',
  `scope` varchar(128) DEFAULT NULL COMMENT 'Scope',
  PRIMARY KEY (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OAuth2 refresh tokens table (required by OAuth2 library)
CREATE TABLE `oauth2_refresh_token` (
  `refresh_token` char(40) NOT NULL COMMENT 'The refresh token',
  `client_id` varchar(128) DEFAULT NULL COMMENT 'Client identifier',
  `user_id` int(10) DEFAULT NULL COMMENT 'The user id',
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Expiration timestamp',
  `scope` varchar(128) DEFAULT NULL COMMENT 'Scope',
  PRIMARY KEY (`refresh_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OAuth2 clients table (required by OAuth2 library)
CREATE TABLE `oauth2_client` (
  `client_id` varchar(128) NOT NULL COMMENT 'Client identifier',
  `client_secret` varchar(255) DEFAULT NULL COMMENT 'Client secret string',
  `redirect_uri` varchar(255) DEFAULT NULL COMMENT 'URL to redirect to for authorization',
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OAuth2 clients table (required by OAuth2 library)
CREATE TABLE `oauth2_scope` (
  `scope` varchar(128) NOT NULL COMMENT 'Scope name',
  `is_default` tinyint(1) DEFAULT NULL COMMENT 'Flag marks default scope',
  PRIMARY KEY (`scope`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User table
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User identifier',
  `email` varchar(128) NOT NULL COMMENT 'User email',
  `name` varchar(48) NOT NULL COMMENT 'User name',
  `firstname` varchar(45) DEFAULT NULL COMMENT 'User first name',
  `lastname` varchar(45) DEFAULT NULL COMMENT 'User last name',
  `pass` varchar(255) NOT NULL COMMENT 'User hashed password',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4;

-- Create oauth2 client
INSERT INTO `oauth2_client` SET `client_id` = "test";
