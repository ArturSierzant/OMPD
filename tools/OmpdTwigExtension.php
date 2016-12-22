<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
//  |                                                                        |
//  | netjukebox, Copyright © 2001-2012 Willem Bartels                       |
//  |                                                                        |
//  | http://www.netjukebox.nl                                               |
//  | http://forum.netjukebox.nl                                             |
//  |                                                                        |
//  | This program is free software: you can redistribute it and/or modify   |
//  | it under the terms of the GNU General Public License as published by   |
//  | the Free Software Foundation, either version 3 of the License, or      |
//  | (at your option) any later version.                                    |
//  |                                                                        |
//  | This program is distributed in the hope that it will be useful,        |
//  | but WITHOUT ANY WARRANTY; without even the implied warranty of         |
//  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          |
//  | GNU General Public License for more details.                           |
//  |                                                                        |
//  | You should have received a copy of the GNU General Public License      |
//  | along with this program.  If not, see <http://www.gnu.org/licenses/>.  |
//  +------------------------------------------------------------------------+


class OmpdTwigExtension extends \Twig_Extension {

    /**
     * Name of this extension.
     *
     * @return string
     */
    public function getName() {
        return 'Ompd';
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('formattedTime', function ($miliseconds) {
                return formattedTime($miliseconds);
            }),

            new \Twig_SimpleFilter('getType', function ($var) {
                return gettype($var);
            }),

            new \Twig_SimpleFilter("dumpImagestream", function ($input, $mimeType) {
                $imageinfo = array();
                if ($imagechunkcheck = \getid3_lib::GetDataImageSize($input, $imageinfo)) {
                    $streamString = "data:" .$mimeType.";base64,".base64_encode($input);
                    $width = $imagechunkcheck[0];
                    $height = $imagechunkcheck[1];
                    $linkTitle = "Open image (".$width."x".$height.") in new tab";
                    return '
                        <a href="'.$streamString.'" width="100" title="'.$linkTitle.'" target="_blank">
                            <img src="'.$streamString.'" width="200" />
                        </a>';
                }
                return "<i>invalid image data</i></td></tr>";
            })
        );
    }


    public function getTests() {
        return array(
            new \Twig_SimpleTest('typeString', function ($value) {
                return is_string($value);
            }),

            new \Twig_SimpleTest('typeArray', function ($value) {
                return is_array($value);
            }),

            new \Twig_SimpleTest('typeBoolean', function ($value) {
                return is_bool($value);
            }),

            new \Twig_SimpleTest('typeDouble', function ($value) {
                return is_double($value);
            }),

            new \Twig_SimpleTest('typeFloat', function ($value) {
                return is_float($value);
            }),

            new \Twig_SimpleTest('typeInteger', function ($value) {
                return is_int($value);
            }),

            new \Twig_SimpleTest('typeNull', function ($value) {
                return is_null($value);
            }),

            new \Twig_SimpleTest('typeObject', function ($value) {
                return is_object($value);
            })
        );
    }
}
