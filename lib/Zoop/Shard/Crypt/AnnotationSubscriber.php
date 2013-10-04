<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;

/**
 * Listener hashes fields marked with CryptHash annotation
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AnnotationSubscriber implements EventSubscriber
{

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Shard\Crypt\Hash::EVENT,
            Shard\Crypt\BlockCipher::EVENT
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationCryptHash(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $metadata = $eventArgs->getMetadata();
        $cryptMetadata = $this->getCryptMetadata($metadata);
        $cryptMetadata['hash'][$eventArgs->getReflection()->getName()] = [
            'service' => $annotation->service,
            'salt' => $annotation->salt
        ];
        $metadata->setCrypt($cryptMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationCryptBlockCipher(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $metadata = $eventArgs->getMetadata();
        $cryptMetadata = $this->getCryptMetadata($metadata);
        $cryptMetadata['blockCipher'][$eventArgs->getReflection()->getName()] = [
            'service' => $annotation->service,
            'key' => $annotation->key,
            'salt' => $annotation->salt
        ];
        $metadata->setCrypt($cryptMetadata);
    }

    protected function getCryptMetadata($metadata)
    {
        if (!$metadata->hasProperty('crypt')) {
            $metadata->addProperty('crypt', true);
            $metadata->setCrypt([]);
        }

        return $metadata->getCrypt();
    }
}
