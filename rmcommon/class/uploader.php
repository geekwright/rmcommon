<?php
// $Id: uploader.php 999 2012-07-02 03:53:17Z i.bitcero $
// --------------------------------------------------------------
// Red México Common Utilities
// A framework for Red México Modules
// Author: Eduardo Cortés <i.bitcero@gmail.com>
// Email: i.bitcero@gmail.com
// License: GPL 2.0
// --------------------------------------------------------------

require_once XOOPS_ROOT_PATH . '/class/uploader.php';

class RMFileUploader extends XoopsMediaUploader
{
    /**
     * Generate the uploader object
     *
     * @param mixed $uploadDir
     * @param mixed $maxFileSize
     * @param mixed  $allowed_exts
     * @return string RMFileUploader
     */
    public function __construct($uploadDir, $maxFileSize, $allowed_exts = [])
    {
        //$this->XoopsMediaUploader($dir, $allowedtypes, $maxsize);
        $this->extensionToMime = include $GLOBALS['xoops']->path('include/mimetypes.inc.php');
        $ev                    = RMEvents::get();
        $this->extensionToMime = $ev->run_event('rmcommon.get.mime.types', $this->extensionToMime);
        if (!is_array($this->extensionToMime)) {
            $this->extensionToMime = [];

            return false;
        }
        if (is_array($allowed_exts)) {
            foreach ($allowed_exts as $ext) {
                $this->allowedMimeTypes[] = $this->extensionToMime[$ext];
            }
        }

        $this->uploadDir   = $uploadDir;
        $this->maxFileSize = (int)$maxFileSize;
        if (isset($maxWidth)) {
            $this->maxWidth = (int)$maxWidth;
        }
        if (isset($maxHeight)) {
            $this->maxHeight = (int)$maxHeight;
        }

        if (!@require_once $GLOBALS['xoops']->path('language/' . $GLOBALS['xoopsConfig']['language'] . '/uploader.php')) {
            require_once $GLOBALS['xoops']->path('language/english/uploader.php');
        }

        return null;
    }

    public function _copyFile($chmod)
    {
        $matched = [];
        if (!preg_match("/\.([a-zA-Z0-9]+)$/", $this->mediaName, $matched)) {
            $this->setErrors(_ER_UP_INVALIDFILENAME);

            return false;
        }
        if (isset($this->targetFileName)) {
            $this->savedFileName = $this->targetFileName;
        } elseif (isset($this->prefix)) {
            $this->savedFileName = uniqid($this->prefix) . '.' . mb_strtolower($matched[1]);
        } else {
            $this->savedFileName = mb_strtolower($this->mediaName);
        }

        $fdata               = pathinfo($this->savedFileName);
        $this->savedFileName = TextCleaner::sweetstring($fdata['filename']) . ('' != $fdata['extension'] ? '.' . $fdata['extension'] : '');
        $fdata               = pathinfo($this->savedFileName);

        if (file_exists($this->uploadDir . '/' . $this->savedFileName)) {
            $num = 1;
            while (file_exists($this->uploadDir . '/' . $this->savedFileName)) {
                $this->savedFileName = $fdata['filename'] . '-' . $num . ('' != $fdata['extension'] ? '.' . $fdata['extension'] : '');
                $num++;
            }
        }

        $this->savedDestination = $this->uploadDir . '/' . $this->savedFileName;
        if (!move_uploaded_file($this->mediaTmpName, $this->savedDestination)) {
            $this->setErrors(sprintf(_ER_UP_FAILEDSAVEFILE, $this->savedDestination));

            return false;
        }
        // Check IE XSS before returning success
        $ext = mb_strtolower(mb_substr(mb_strrchr($this->savedDestination, '.'), 1));
        if (in_array($ext, $this->imageExtensions, true)) {
            $info = @getimagesize($this->savedDestination);
            if (false === $info || $this->imageExtensions[(int)$info[2]] != $ext) {
                $this->setErrors(_ER_UP_SUSPICIOUSREFUSED);
                @unlink($this->savedDestination);

                return false;
            }
        }
        @chmod($this->savedDestination, $chmod);

        return true;
    }

    public function getMIME($extension)
    {
        print_r($this->extensionToMime);
        die();
    }
}
