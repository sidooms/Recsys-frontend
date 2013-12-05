-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 02 dec 2013 om 13:49
-- Serverversie: 5.5.31
-- PHP-Versie: 5.3.10-1ubuntu3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hybrido`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `algorithmweights`
--

CREATE TABLE IF NOT EXISTS `algorithmweights` (
  `algorithm` text NOT NULL,
  `weight` double NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `h_recommendations`
--

CREATE TABLE IF NOT EXISTS `h_recommendations` (
  `recommendationid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `movieid` int(11) NOT NULL,
  `value` double NOT NULL,
  `explanation` text NOT NULL,
  PRIMARY KEY (`recommendationid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=755239 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `movies`
--

CREATE TABLE IF NOT EXISTS `movies` (
  `movieid` int(11) NOT NULL,
  `title` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `year` int(11) NOT NULL,
  PRIMARY KEY (`movieid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ratings`
--

CREATE TABLE IF NOT EXISTS `ratings` (
  `ratingid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `movieid` int(11) NOT NULL,
  `rating` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `synced` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ratingid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `recommendations`
--

CREATE TABLE IF NOT EXISTS `recommendations` (
  `recommendationid` int(11) NOT NULL AUTO_INCREMENT,
  `algorithm` text NOT NULL,
  `userid` int(11) NOT NULL,
  `movieid` int(11) NOT NULL,
  `value` double NOT NULL,
  PRIMARY KEY (`recommendationid`),
  KEY `index_user` (`userid`),
  KEY `index_item` (`movieid`),
  KEY `index_algo` (`algorithm`(10))
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1584585 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1000000 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

INSERT INTO `users` (`userid`) VALUES
(999999);