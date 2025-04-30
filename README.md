## Welcome!

O!MPD is free, opensource [MPD](http://www.musicpd.org/) client based on PHP and MySQL.

MPD is a brilliant application that plays music from many sources, in many formats, but it has no user interface. O!MPD is what MPD needs: user interface which can control MPD and lets you browse your music library in the way you surf the internet.

O!MPD is a fork of [netjukebox](http://www.netjukebox.nl/) (5.37).

While netjukebox also supports VideoLAN and Winamp/httpQ, O!MPD supports O!nly MPD.

## Main features

* responsive design – works in modern web browsers, on various devices, various screen resolutions
* user-defined Quick search
* browse your library by user-defined tags or artists, genres, years, adding time
* control all of MPDs in your network
* smart search for another versions of currently playing song
* search for info about artist and album (Google, Wiki, AllMusic…)
* search the library for specific phrase (album/track artist, album/track title)
* search albums and tracks of multiple/single artists
* search for lyrics
* Favorites (aka. playlists) and Blacklist
* suggestions of albums to listen
* statistics for played music and whole collection
* skins
* support for TIDAL and HighResAudio
* Radio Browser (based on [radio-browser.info](https://radio-browser.info))

## O!MPD requirements

- PHP 5.2.0 or later with extension: GD2, ICONV, MBSTRING, MYSQLi, JSON, CTYPE and CURL 
- MySQL 4.1.0 or later
- Music Player Daemon (MPD)

## Installation

The installation instruction can be found on https://ompd.pl

## Configuration

Start configuration from settings in:
`include/config.inc.php`

You can also copy this file into `include/config.local.inc.php` and there make all nessesary changes - those changes will override default values from `include/config.inc.php`

Next (in O!MPD GUI) go to _Configuration -> Settings_ and complete setup.

## Very large files

O!MPD should support files larger than 2GB (64-bit PHP installations only) - but it was not tested.

