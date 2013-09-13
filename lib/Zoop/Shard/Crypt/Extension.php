<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension
{
    protected $subscribers = [
        'subscriber.crypt.mainsubscriber',
        'subscriber.crypt.annotationsubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.crypt.mainsubscriber' => 'Zoop\Shard\Crypt\MainSubscriber',
            'subscriber.crypt.annotationsubscriber' => 'Zoop\Shard\Crypt\AnnotationSubscriber',
            'crypt.hashhelper' => 'Zoop\Shard\Crypt\HashHelper',
            'crypt.blockcipherhelper' => 'Zoop\Shard\Crypt\BlockCipherHelper',
            'crypt.hash.basichashservice' => 'Zoop\Shard\Crypt\Hash\BasicHashService',
            'crypt.blockcipher.zendservice' => 'Zoop\Shard\Crypt\BlockCipher\ZendBlockCipherService',
            'Zoop\Shard\Crypt\CryptValidator' => 'Zoop\Shard\Crypt\CryptValidator'
        ]
    ];

    /**
     *
     * @var array
     */
    protected $dependencies = [
        'extension.annotation' => true,
    ];
}
