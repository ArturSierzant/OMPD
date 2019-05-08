# -*- coding: utf-8 -*-
#
# Copyright (C) 2014 Thomas Amland
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.


from __future__ import unicode_literals

IMG_URL = "http://images.osl.wimpmusic.com/im/im?w={width}&h={height}&{id_type}={id}"


class Model(object):
    id = None
    name = None

    def __init__(self, **kwargs):
        self.__dict__.update(kwargs)


class Album(Model):
    artist = None
    num_tracks = -1
    duration = -1
    release_date = None

    @property
    def image(self, width=512, height=512):
        return IMG_URL.format(width=width, height=height, id=self.id, id_type='albumid')


class Artist(Model):

    @property
    def image(self, width=512, height=512):
        return IMG_URL.format(width=width, height=height, id=self.id, id_type='artistid')


class Playlist(Model):
    description = None
    creator = None
    type = None
    is_public = None
    created = None
    last_updated = None
    num_tracks = -1
    duration = -1

    @property
    def image(self, width=512, height=512):
        return IMG_URL.format(width=width, height=height, id=self.id, id_type='uuid')


class Track(Model):
    duration = -1
    track_num = -1
    disc_num = 1
    popularity = -1
    artist = None
    album = None
    available = True


class SearchResult(Model):
    artists = []
    albums = []
    tracks = []
    playlists = []


class Category(Model):
    image = None
