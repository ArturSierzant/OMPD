#!/usr/bin/env bash

#  +------------------------------------------------------------------------+
#  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
#  | http://www.ompd.pl                                                     |
#  |                                                                        |
#  |                                                                        |
#  | netjukebox, Copyright © 2001-2012 Willem Bartels                       |
#  |                                                                        |
#  | http://www.netjukebox.nl                                               |
#  | http://forum.netjukebox.nl                                             |
#  |                                                                        |
#  | This program is free software: you can redistribute it and/or modify   |
#  | it under the terms of the GNU General Public License as published by   |
#  | the Free Software Foundation, either version 3 of the License, or      |
#  | (at your option) any later version.                                    |
#  |                                                                        |
#  | This program is distributed in the hope that it will be useful,        |
#  | but WITHOUT ANY WARRANTY; without even the implied warranty of         |
#  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          |
#  | GNU General Public License for more details.                           |
#  |                                                                        |
#  | You should have received a copy of the GNU General Public License      |
#  | along with this program.  If not, see <http://www.gnu.org/licenses/>.  |
#  +------------------------------------------------------------------------+


# This script copies directories and files from the gitignored "vendor" dirctory
# to "vendor-dist" but skips a lot of stuff like demos and non-minified scripts.
# All files and directories that are relevant for sliMpd are listed in "deploy-vendor.txt"
# Some missing licenses of "vendor" packages gets copied as well (from "licenses" directory).
# Limiting to only used vendor files decreases O!MPD's repo size


# TODO: log all invalid paths end echo it after copying

SCRIPT_PATH=$(cd $(dirname "${BASH_SOURCE[0]}") && pwd)

DIR_SOURCE="vendor"
DIR_DEST="vendor-dist"

# delete old dist files
rm -Rf "$SCRIPT_PATH/../$DIR_DEST/"*
cd "$SCRIPT_PATH/../$DIR_SOURCE"

# copy files and directories of deploy-vendor.txt
while IFS='' read -r line || [[ -n "$line" ]]; do
	if [[ -d "$line" ]]
	then
		echo "found dir $line"
		cp --parents --recursive "$line" "$SCRIPT_PATH/../$DIR_DEST/"
	fi
	if [[ -f "$line" ]]
	then
		echo "found file $line"
		cp --parents "$line" "$SCRIPT_PATH/../$DIR_DEST/"
	fi
	#echo "Text read from file: $line"
done < "$SCRIPT_PATH/deploy-vendor.txt"


# add missing license files
echo ""
echo "adding licenses..."
DIR_SOURCE="licenses"
cd "$SCRIPT_PATH/$DIR_SOURCE"
find . -type f| while read filepath; do
	if [[ -f "$filepath" ]]
	then
		echo "found file $filepath"
		cp --parents "$filepath" "$SCRIPT_PATH/../$DIR_DEST/"
	fi
	#echo "Text read from file: $line"
done
