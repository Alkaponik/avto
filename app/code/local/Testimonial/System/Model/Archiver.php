<?php

class Testimonial_System_Model_Archiver extends Mage_Core_Model_Abstract
{
    const TAPE_ARCHIVER      = 'tar';

    protected $_formats = array(
        'zip'        => 'Zip',
        'rar'        => 'Rar',
        'tar'        => 'Tar',
        'gz'         => 'Gz',
        'gzip'       => 'Gz',
        'tgz'        => 'Tar.Gz',
        'tgzip'      => 'Tar.Gz',
        'bz'         => 'Bz2',
        'bzip'       => 'Bz2',
        'bzip2'      => 'Bz2',
        'bz2'        => 'Bz2',
        'tbz'        => 'Tar.Bz2',
        'tbzip'      => 'Tar.Bz2',
        'tbz2'       => 'Tar.Bz2',
        'tbzip2'     => 'Tar.Bz2');

    /**
     * @param $extension
     * @return bool|Zend_Filter_Decompress
     */

    protected function _getArchiver($extension)
    {
        if(array_key_exists(strtolower($extension), $this->_formats)) {
            $format = $this->_formats[strtolower($extension)];
        } else {
            return false;
        }
        $this->_archiver = new Zend_Filter_Decompress('Zend_Filter_Compress_'.$format);
        return $this->_archiver;
    }

    /**
     * Split current format to list of archivers.
     *
     * @param string $source
     * @return array
     */
    protected function _getArchivers($source)
    {
        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        if(!isset($this->_formats[$ext])) {
            return array();
        }
        $format = $this->_formats[$ext];
        if ($format) {
            $archivers = explode('.', $format);
            return $archivers;
        }
        return array();
    }

    public function unpack($source, $destination = null, $tillTar=false, $clearInterm = true)
    {
        $archivers = $this->_getArchivers($source);
        $interimSource = '';
        $result = false;
        for ($i=count($archivers)-1; $i>=0; $i--) {
            if ($tillTar && $archivers[$i] == self::TAPE_ARCHIVER) {
                break;
            }
            if ($i == 0) {
                $packed = rtrim($destination, DS) . DS;
            } else {
                $packed = rtrim($destination, DS) . DS . '~tmp-'. microtime(true) . $archivers[$i-1] . '.' . $archivers[$i-1];
            }
            if ($archiver = $this->_getArchiver($archivers[$i])){
                if (!is_null($destination)){
                    $archiver->setTarget($destination);
                }
                $result = $archiver->filter($source);

                if ($clearInterm && $interimSource && $i >= 0) {
                    unlink($interimSource);
                }
                $interimSource = $source;
            }
        }
        return $result;
    }
}
