<?php
/**
 * $Id$
 * --------------------------------------------------------------
 * Common Utilities
 * Author: Eduardo Cortes
 * Email: i.bitcero@gmail.com
 * License: GPL 2.0
 * URI: http://www.ecortes.mx
 */

/**
 * Esta clase contiene métodos útiles para dar formato a diversos datos
 */
class RMFormat
{
    /**
     * Da formato a un número telefónico basado en su longitud
     * Ejemplo:
     * <code>RMFormat::phone( "9991999999")</code>
     * Devuelve: 999-199-9999
     *
     * @param $phone <p>Número teléfonico a formatear
     * @return string
     */
    public static function phone($phone)
    {
        $matches = [];
        $found   = false;

        $patterns = [
            '/^(\d{3})[^\d]*(\d{4})$/', // Número local
            '/^(\d{3})[^\d]*(\d{3})[^\d]*(\d{4})$/', // Celular o con clave lada (sin 044)
            '/^(\d{3})(\d{1})[^\d]*(\d{2})[^\d]*(\d{4})$/', // Celular o con clave lada (sin 044)
            '/^(0\d{2})[^\d]*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4})$/', // Celular con 044 al principio,
            '/^(\d{2})[^\d]*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4})$/', // Con código de país
        ];

        $replaces = [
            '$1&middot;$2',
            '($1) $2&middot;$3',
            '($1) $2$3&middot;$4',
            '$1 ($2) $3&middot;$4',
            '+$1 ($2) $3&middot;$4',
        ];

        $formatted = preg_replace($patterns, $replaces, $phone);
        /*foreach ( $patterns as $search ){

            if( preg_match( $search, $phone, $matches ) ){
                $found = true;
                break;
            }

        }

        if (!$found)
            return $phone;
        */

        return $formatted;
        //$matches = array_slice( $matches, 1);
        //return implode("&middot;", $matches);
    }

    /**
     * Da formato a fechas MySQL
     * @param string $date
     * @param string $format
     * @param bool   $local Utilizar formato de localización
     * @return string
     */
    public static function date($date, $format = '', $local = false)
    {
        if ('' == $date) {
            return null;
        }

        $time = strtotime($date);

        if ($time <= 0) {
            return '<code>?</code>';
        }

        if ($local) {
            $tf = new RMTimeFormatter($time, $format);

            return $tf->format();
        }

        return date('' != $format ? $format : 'd/m/Y H:i:s', $time);
    }

    /**
     * Get the icon for a specific social network
     * The icon is formatted according to FontAwesome icons
     *
     * @param string $type <p>Identifier type of social network</p>
     * @return string
     */
    public static function social_icon($type)
    {
        $networks = [
            'twitter'    => 'fa-twitter-square',
            'linkedin'   => 'fa-linkedin-square',
            'github'     => 'fa-github-alt',
            'pinterest'  => 'fa-pinterest-square',
            'google+'    => 'fa-google-plus-square',
            'youtube'    => 'fa-youtube-square',
            'rss'        => 'fa-rss-square',
            'xing'       => 'fa-xing-square',
            'dropbox'    => 'fa-dropbox',
            'instagram'  => 'fa-instagram',
            'flickr'     => 'fa-flickr',
            'tumblr'     => 'fa-tumblr-square',
            'dribbble'   => 'fa-dribbble',
            'skype'      => 'fa-skype',
            'foursquare' => 'fa-foursquare',
            'vimeo'      => 'fa-vimeo-square',
            'vimeo'      => 'fa-vimeo-square',
        ];

        if (isset($networks[$type])) {
            return $networks[$type];
        }

        return 'fa-chain';
    }

    /**
     * Format a given array with version information for a module.
     *
     * @param array $version Array with version values
     * @param bool  $name    Include module name in return string
     * @return string
     */
    public static function version($version, $name = false)
    {
        $rtn = '';

        if ($name) {
            $rtn .= (defined($version['name']) ? constant($version['name']) : $version['name']) . ' ';
        }

        // New versioning
        if (isset($version['major'])) {
            $rtn .= $version['major'];
            $rtn .= '.' . $version['minor'];
            $rtn .= '.' . ($version['revision'] / 10);
            switch ($version['stage']) {
                case -3:
                    $rtn .= ' alfa';
                    break;
                case -2:
                    $rtn .= ' beta';
                    break;
                case -1:
                    $rtn .= ' RC';
                    break;
                default:
                    $rtn .= '';
                    break;
            }

            return $rtn;
        }

        // Format version of a module with previous versioning system
        $rtn .= $version['number'];

        if ($version['revision'] > 0) {
            $rtn .= '.' . ($version['revision'] / 100);
        } else {
            $rtn .= '.0';
        }

        switch ($version['status']) {
            case '-3':
                $rtn .= ' alfa';
                break;
            case '-2':
                $rtn .= ' beta';
                break;
            case '-1':
                $rtn .= ' final';
                break;
            case '0':
                break;
        }

        return $rtn;
    }

    /**
     * Format bytes to MB, GB, KB, etc
     * @param int    $size   value to format
     * @param string $origin type of input. Can be b, kb, mb, gb, tb, or ''
     * @param string $target type of output. Can be b, kb, mb, gb, kb or ''
     * @param bool   $abr    True enable abbreviations (e.g. kb, mb, gb, etc)
     * @return string
     */
    public static function bytes_format($size, $origin = '', $target = '', $abr = true)
    {
        $kb = 1000;
        $mb = $kb * 1000;
        $gb = $mb * 1000;
        $tb = $gb * 1000;

        $units = [
            'b'  => 1,
            'kb' => $kb,
            'mb' => $mb,
            'gb' => $gb,
            'tb' => $tb,
        ];

        $string = [
            'b'  => $abr ? __('%s b', 'rmcommon') : __('%s Bytes', 'rmcommon'),
            'kb' => $abr ? __('%s KB', 'rmcommon') : __('%s Kilobytes', 'rmcommon'),
            'mb' => $abr ? __('%s MB', 'rmcommon') : __('%s Megabytes', 'rmcommon'),
            'gb' => $abr ? __('%s GB', 'rmcommon') : __('%s Gigabytes', 'rmcommon'),
            'tb' => $abr ? __('%s TB', 'rmcommon') : __('%s Terabytes', 'rmcommon'),
        ];

        $origin = '' == $origin || !isset($units[$origin]) ? 'b' : $origin;
        $target = !isset($units[$target]) ? '' : $target;

        if ('' != $target && $units[$target] == $units[$origin]) {
            return sprintf($string[$origin], $size);
        }

        // Convert size to bytes
        $size = $size * $units[$origin];
        // Get bytes in target format only if $target has been provided
        if ('' != $target) {
            $result = number_format($bytes / $units[$target], 2);
        } else {
            switch ($size) {
                case $size < $kb:
                    $result = $size;
                    $target = 'b';
                    break;
                case $size < $mb:
                    $result = number_format($size / $kb, 2);
                    $target = 'kb';
                    break;
                case $size < $gb:
                    $result = number_format($size / $mb, 2);
                    $target = 'mb';
                    break;
                case $size < $tb:
                    $result = number_format($size / $gb, 2);
                    $target = 'gb';
                    break;
                default:
                    $result = number_format($size / $tb, 2);
                    $target = 'tb';
                    break;
            }
        }

        return sprintf($string[$target], $result);
    }

    /**
     * Formats a given quantity to short it:
     * Example:
     * 1000 => 1K
     * 150000 => 150K
     * @param $number
     * @return string
     */
    public static function quantity($number)
    {
        $value  = 0;
        $suffix = '';

        if ($number < 1000) {
            $value  = $number;
            $suffix = '';
        } elseif ($number >= 1000 && $number < 1000000) {
            $value  = number_format($number / 1000, 1);
            $suffix = 'K';
        } elseif ($number >= 1000000) {
            $value  = number_format($number / 1000000, 1);
            $suffix = 'M';
        }

        if ($value > (int)$value) {
            $formatted = $value . $suffix;
        } else {
            $formatted = (int)$value . $suffix;
        }

        return $formatted;
    }
}
