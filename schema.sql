SET NAMES 'utf8';

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `messages`
--

-- --------------------------------------------------------

--
-- Table structure for table `direct-message`
--

CREATE TABLE IF NOT EXISTS `direct-message` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` int(11) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `Remote_name` mediumtext NOT NULL,
  `Remote_profile` mediumtext NOT NULL,
  `Remote_avatar` mediumtext NOT NULL,
  `Remote_message` mediumtext NOT NULL,
  `Remote_time` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE IF NOT EXISTS `followers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` int(11) NOT NULL,
  `Remote_URL` mediumtext NOT NULL,
  `Remote_name` varchar(255) NOT NULL,
  `Remote_profile` mediumtext NOT NULL,
  `Remote_avatar` mediumtext NOT NULL,
  `Remote_pub_key` mediumtext NOT NULL,
  `Relation_pingback` mediumtext NOT NULL,
  `Message_pingback` mediumtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Table structure for table `following`
--

CREATE TABLE IF NOT EXISTS `following` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` int(11) NOT NULL,
  `Remote_URL` mediumtext NOT NULL,
  `Remote_name` varchar(255) NOT NULL,
  `Remote_profile` mediumtext NOT NULL,
  `Remote_avatar` mediumtext NOT NULL,
  `Relation_pingback` mediumtext NOT NULL,
  `Message_pingback` mediumtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `message-cache`
--

CREATE TABLE IF NOT EXISTS `message-cache` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Remote_URL` mediumtext NOT NULL,
  `Remote_profile` mediumtext NOT NULL,
  `Remote_avatar` mediumtext NOT NULL,
  `Remote_name` varchar(255) NOT NULL,
  `Remote_time` int(11) NOT NULL,
  `Remote_message` mediumtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=388 ;

-- --------------------------------------------------------

--
-- Table structure for table `message-cache-users`
--

CREATE TABLE IF NOT EXISTS `message-cache-users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Remote_URL` mediumtext NOT NULL,
  `Update_cache` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` int(11) NOT NULL,
  `Time` int(11) NOT NULL,
  `Message` mediumtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=69 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_name` mediumtext NOT NULL,
  `E-mail` mediumtext NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Salt` varchar(255) NOT NULL,
  `Priv_key` mediumtext NOT NULL,
  `Pub_key` mediumtext NOT NULL,
  `Full_name` mediumtext NOT NULL,
  `Location` mediumtext NOT NULL,
  `Web` mediumtext NOT NULL,
  `Bio` mediumtext NOT NULL,
  `Avatar` mediumtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;
