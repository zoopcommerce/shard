<?php

namespace Zoop\Shard\ODMCore;

use Doctrine\Common\Cache\CacheProvider;
use Zoop\Juggernaut\Adapter\AbstractAdapter;

/**
 * Caches objects as php files (which can be optimized by an optcode cache)
 * similar to PhpFileCache. However, objects are serialized, so don't need to
 * support var_export and __set_state, as required by PhpFileCache.
 *
 * @author Tim Roediger <superdweebie@gmail.com>
 */
class JuggernautCache extends CacheProvider
{

    protected $juggernautInstance;

    public function getJuggernautInstance()
    {
        return $this->juggernautInstance;
    }

    public function setJuggernautInstance(AbstractAdapter $juggernautInstance)
    {
        $this->juggernautInstance = $juggernautInstance;
    }

    public function __construct(AbstractAdapter $juggernautInstance)
    {
        $this->setJuggernautInstance($juggernautInstance);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doDelete($id)
    {
        //juggernaut doesn't support delete yet
    }

    protected function doFetch($id)
    {
        $item = $this->juggernautInstance->getItem($id, $success);
        if ($success) {
            return $item;
        }

        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doContains($id)
    {
        $this->juggernautInstance->getItem($id, $success);

        return $success;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        //ttl ignored, because juggernaught doesn't support individual ttl
        $this->juggernautInstance->setItem($id, $data);
    }

    protected function doFlush()
    {
        //juggernauth doesn't support flush yet.
    }

    protected function doGetStats()
    {
    }
}
