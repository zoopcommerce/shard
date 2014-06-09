<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class DigitalSku
{
    /**
     *
     * @ODM\String
     */
    protected $file;

    /**
     *
     * @ODM\Int
     */
    protected $numberOfDownloads = 0;


    /**
     *
     * @return integer
     */
    public function getNumberOfDownloads()
    {
        return $this->numberOfDownloads;
    }

    /**
     *
     * @param integer $numberOfDownloads
     */
    public function setNumberOfDownloads($numberOfDownloads)
    {
        $this->numberOfDownloads = (int) $numberOfDownloads;
    }

    /**
     * Increases the download count
     */
    public function incrementNumberOfDownloads()
    {
        $this->numberOfDownloads++;
    }

    /**
     * Decreases the download count
     */
    public function decrementNumberOfDownloads()
    {
        if ($this->numberOfDownloads > 0) {
            $this->numberOfDownloads--;
        }
    }

    /**
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}
