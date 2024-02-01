# Radio Browser

A library to fetch data from the [radio-browser.info](https://www.radio-browser.info/#/) catalog of Internet radio stations by using the [project's api](https://de1.api.radio-browser.info/).

```php
use \AdinanCenci\RadioBrowser\RadioBrowser;

$browser          = new RadioBrowser();
$tag              = 'metal';
$orderBy          = 'name';
$descendingOrder  = true;

$stations         = $browser->getStationsByTag($tag, $orderBy, $descendingOrder);

print_r($stations);
```

<br><br>

## Examples

See examples of how to use in the "examples" directory.

- [Get tags](examples/a-get-tags.php)
- [Get states](examples/b-get-states.php)
- [Get stations by tag](examples/c-get-stations-by-tag.php)
- [Get stations by country](examples/d-get-stations-by-country.php)
- [Get stations by clicks](examples/e-get-stations-by-clicks.php)
- [Get stations by votes](examples/f-get-stations-by-votes.php)
- [Get stations in XML format](examples/g-get-stations-in-xml-format.php)
- [Get servers IPs](examples/y-get-servers-ips.php)

<br><br>

## Classes and instantiating

### Associative arrays / Objects

The methods of `\AdinanCenci\RadioBrowser\RadioBrowser` will return either associative arrays or stdObjects, depending on the `$associative` parameter informed to the constructor.

| Parameter    | Type         | Default                             | Description                                                                                                                                                                            |
| ------------ | ------------ | ----------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| $server      | string\|bool | https://de1.api.radio-browser.info/ | A Radio Browser server, it defaults to "'https://de1.api.radio-browser.info/", if false is informed the object will pick a random server, see the server section for more information. |
| $associative | bool         | true                                | If true, the methods will return associative arrays, stdObjects otherwise.                                                                                                             |

<br><br>

### Xml, json, csv etc...

If you need the data represented in a specific format, then use `\AdinanCenci\RadioBrowser\RarioBrowserApi`. The different methods will return data formatted as: json, xml, csv, m3u, pls, xspf and ttl.

| Parameter | Type         | Default                             | Description                                                                                                                                                                             |
| --------- | ------------ | ----------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| $server   | string\|bool | https://de1.api.radio-browser.info/ | A Radio Browser server, it defaults to "'https://de1.api.radio-browser.info/", if false is informed, the object will pick a random server, see the server section for more information. |
| $format   | string       | json                                | The format the data must be served, possible values: json, xml, csv, m3u, pls, xspf, ttl.                                                                                               |

<br><br>

## Radio Stations

### Get stations by UUID

The `::getStationsByUuid($uuids)` search stations with the specified ids.

| Parameter | Type          | Description                                                     |
| --------- | ------------- | --------------------------------------------------------------- |
| $uuids    | string\|array | A list of uuids, either an array, or an comma separated string. |

<br><br>

### Get stations by URL

The `::getStationsByUrl($url)`method search stations by their web page.

<br><br>

### Get stations by clicks

The `::getStationsByClicks($offset, $limit, $hideBroken)` method returns the most sintonized stations.

<br><br>

### Get stations by votes

The `::getStationsByVotes($offset, $limit, $hideBroken)` method returns the most voted stations.

<br><br>

### Get stations by recent clicks

The `::getStationsByRecentClicks($offset, $limit, $hideBroken)` method returns the currently most popular stations.

<br><br>

### Get stations last changed

The `::getStationsByLastChange($offset, $limit, $hideBroken)` method returns the stations last updated.

<br><br>

### Get older version of stations

The `::getStationOlderVersions($lastChangeUuid, $limit)` method returns old versions of stations from the last 30 days.

<br><br>

### Get broken stations

The `::getBrokenStations($offset, $limit)` method returns stations that did not pass the connection test.

<br><br>

## Get stations by ...

All the methods in this section share the following parameters:

| Parameter   | Type   | Default | Description                                                                                                                                                                            |
| ----------- | ------ | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| $order      | string | 'name'  | Possible values:<br />name, url, homepage, favicon, tags, country, state, language, votes, codec, bitrate, lastcheckok, lastchecktime, clicktimestamp, clickcount, clicktrend, random. |
| $reverse    | bool   | false   | false = Ascending order.<br />true = Descending order.                                                                                                                                 |
| $hideBroken | bool   | false   | Do not list stations that failed the connection test.                                                                                                                                  |
| $offset     | int    | 0       |                                                                                                                                                                                        |
| $limit      | int    | 100000  |                                                                                                                                                                                        |

<br><br>

### Just get all stations

The `::getStations($order, $reverse, $hideBroken, $offset, $limit)` method will return all stations.

<br><br>

### Get stations by name

The `::getStationsByName($name, $order, $reverse, $hideBroken, $offset, $limit)` method returns stations described with `$name`.

<br><br>

### Get stations by exact name

The `::getStationsByExactName($name, $order, $rev...)` method returns stations described with an exact match of `$name`.

<br><br>

### Get stations by codec

The `::getStationsByCodec($codec, $order, $rev...)` method returns stations described with `$codec`.

<br><br>

### Get stations by exact codec

The `::getStationsByExactCodec($codec, $order, $rev...)` method returns stations described with an exact match of `$codec`.

<br><br>

### Get stations by country

`::getStationsByCountry($country, $order, $rev...)`

<br><br>

### Get station by exact country

`::getStationsByExactCountry($country, $order, $rev...)`

<br><br>

### Get stations by state

`::getStationsByState($state, $order, $rev...)`

<br><br>

### Get stations by exact state

`::getStationsByExactState($state, $order, $rev...)`

<br><br>

### Get stations by language

`::getStationsByLanguage($language, $order, $rev...)` 

<br><br>

### Get stations by exact language

`::getStationsByExactLanguage($language, $order, $rev...)`.

<br><br>

### Get stations by tag

`::getStationsByTag($tag, $order, $rev...)`

<br><br>

### Get stations by exact tag

`::getStationsByExactTag($tag, $order, $rev...)`

<br><br>

## Search station

The `::searchStation($searchTerms)` method allow us to fine grain our search.
It receives a single associative array with the following keys available, all of which are optional:

| Key           | Type          | Default | Description                                                                                                                                                                            |
| ------------- | ------------- | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| name          | string        | null    |                                                                                                                                                                                        |
| nameExact     | bool          | false   |                                                                                                                                                                                        |
| country       | string        | null    |                                                                                                                                                                                        |
| countryExact  | bool          | false   |                                                                                                                                                                                        |
| countrycode   | string        | null    |                                                                                                                                                                                        |
| state         | string        | null    |                                                                                                                                                                                        |
| stateExact    | bool          | false   |                                                                                                                                                                                        |
| language      | string        | null    |                                                                                                                                                                                        |
| languageExact | bool          | false   |                                                                                                                                                                                        |
| tag           | string        | null    |                                                                                                                                                                                        |
| tagExact      | bool          | false   |                                                                                                                                                                                        |
| tagList       | string\|array | null    | A list of tags, either an array or a comma separated string.                                                                                                                           |
| codec         | string        | null    |                                                                                                                                                                                        |
| bitrateMin    | int           | 0       |                                                                                                                                                                                        |
| bitrateMax    | int           | 1000000 |                                                                                                                                                                                        |
| order         | string        | name    | Possible values:<br />name, url, homepage, favicon, tags, country, state, language, votes, codec, bitrate, lastcheckok, lastchecktime, clicktimestamp, clickcount, clicktrend, random. |
| reverse       | bool          | false   | false = Ascending order.<br />true = Descending order.                                                                                                                                 |
| offset        | int           | 0       |                                                                                                                                                                                        |
| limit         | int           | 100000  |                                                                                                                                                                                        |

<br><br>

## Get station checks

The `::getStationCheckResults($stationUuid, $lastCheckUuid, $seconds, $limit)` method returns a list of station check results. If a station UUID is provided, the whole history will be returned, otherwise a list of all last checks of all stations will be sent (without older check results).

| Parameter      | Type     | Default                                                        | Description                                                                   |
| -------------- | -------- | -------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| $stationUiid   | 'string' |                                                                | Optional. If set, only list check result of the matching station.             |
| $lastCheckUuid | 'string' | If set, only list checks after the check with the given check. |                                                                               |
| $seconds       | integer  | 0                                                              | if > 0, it will only return history entries from the last `$seconds` seconds. |
| $limit         | integer  | 999999                                                         |                                                                               |

<br><br>

## Get station clicks

The `::getStationClicks($stationUuid, $lastCheckUuid, $seconds)` method returns a list of station clicks. If a station UUID is provided, only clicks of the station will be returned, otherwise a list of all clicks of all stations will be sent (chunksize 10000).

| Parameter      | Type     | Default                                                        | Description                                                                   |
| -------------- | -------- | -------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| $stationUiid   | 'string' |                                                                | Optional. If set, only list check result of the matching station.             |
| $lastCheckUuid | 'string' | If set, only list checks after the check with the given check. |                                                                               |
| $seconds       | integer  | 0                                                              | if > 0, it will only return history entries from the last `$seconds` seconds. |

<br><br>

## Add station

The `::addStation($name, $url, $homePage, $favIcon, $countryCode, $state, $language, $tags, $geoLat, $geoLong)` method allow 
us to insert new stations.

It receives a single associative array with the following keys available, all of which are optional:

| Key          | Type   | Default | Description                                                                 |
| ------------ | ------ | ------- | --------------------------------------------------------------------------- |
| $name        | string |         | MANDATORY, the name of the radio station. Max 400 chars.                    |
| $rl          | string |         | MANDATORY, the URL of the station.                                          |
| $homePage    | string | null    | the homepage URL of the station.                                            |
| $favIcon     | string | null    | the URL of an image file (jpg or png).                                      |
| $countryCode | string | null    | The 2 letter countrycode of the country where the radio station is located. |
| $state       | string | null    | The name of the part of the country where the station is located.           |
| $language    | string | null    | The main language used in spoken text parts of the radio station.           |
| $tags        | string | null    | A list of tags separated by commas to describe the station.                 |
| $geoLat      | string | null    | The latitude of the stream location.                                        |
| $geoLong     | string | null    | The longitude of the stream location.                                       |

<br><br>

## Ranking

### Listeners

The `::clickStation($stationUuid)` method must be invoked every time a user starts playing a stream, this helps Radio Browser sort how popular each station is. **IMPORTANT**: Every call from the same IP address and for the same station only gets counted once per day.

### Voting

The `::voteStation($stationUuid)` method increases the vote count by one. **IMPORTANT**: it can only be called once every 10 minutes for the same radio stations, from the same IP.

<br><br>

## General Information

The methods bellow share the following parameters:

| Parameter   | Type   | Default | Description                                            |
| ----------- | ------ | ------- | ------------------------------------------------------ |
| $filter     | string |         | A string to be matched against.                        |
| $order      | string | name    | Possible values: name, stationcount.                   |
| $reverse    | bool   | false   | false = Ascending order.<br />true = Descending order. |
| $hideBroken | bool   | false   | Do not count stations that failed the connection test. |

### Get codecs

The `::getCodecs($filter, $order, $reverse, $hideBroken)` method returns a list of codecs and a count of stations using them.

<br><br>

### Get languages

The `::getLanguages($filter, $ord...)` method returns a list of languages and a count of stations in this language.

<br><br>

### Get tags

The `::getTags($filter, $ord...)` method returns a list of tags and a count of stations described with them.

<br><br>

### Get country codes

The `::getCountryCodes($filter, $ord...)` method returns a list of country codes and a count of stations described with them.

<br><br>

### Get countries

The `::getCountries($filter, $ord...)` method returns a list of countries and a count of stations described with them.

<br><br>

### Get states

The `::getStates($filter, $country, $order, $reverse, $hideBroken)` return a list of states and a count of stations described with them.

| Parameter   | Type   | Default | Description                                            |
| ----------- | ------ | ------- | ------------------------------------------------------ |
| $filter     | string |         | A string to be matched against.                        |
| $country    | string | null    | The country that the state belongs to.                 |
| $order      | string | name    | Possible values: name, stationcount.                   |
| $reverse    | bool   | false   | false = Ascending order.<br />true = Descending order. |
| $hideBroken | bool   | false   | Do not count stations that failed the connection test. |

<br><br>

## Servers

### Get the server's stats

`::getServerStats()`

<br><br>

### Get the server's mirrors

`::getServerMirrors()`

<br><br>

### Get the server's configurations

`::getServerConfig()`

<br><br>

### Get the server's metrics

`::getServerMetrics()`

<br><br>

### Get DNS records

The `::getDnsRecords()` static method returns DNS information on available servers.

<br><br>

### Get server IPs

The `::getServerIps()` static method returns an array of IPs of available servers.

<br><br>

### Get servers

The `::getServers()` static method returns an array of URLs of available servers.

<br><br>

### Pick a server

The `::pickAServer()` static method returns a random server's URL.

<br><br>

## Installation

Use composer.

```cmd
composer require adinan-cenci/radio-browser
```

<br><br>

## License

MIT