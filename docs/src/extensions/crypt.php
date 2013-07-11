<section id="crypt" title="Crypt">
  <div class="page-header">
    <h1>Crypt</h1>
  </div>

    <p class="lead">Hash or encrypt document fields.</p>

    <h2>Configuration</h2>
    <p>Access Control has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.crypt' => true
    ],
    ...
]);
</pre>

    <h2>Hash</h2>
    <p>A hash is a one way encryption method. That is, once the text is encrypted, you can't get the plain text back. It is especially useful for user passwords.</p>

    <h3>@Shard\Crypt\Hash</h3>
    <p>To hash a field, just add the <code>@Shard\Crypt\Hash</code> annotation to that field. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument {
    /**
     * @ODM\String
     * @Shard\Crypt\Hash
     */
    protected $password;

    ...
}
</pre>

    <h3>Hash Salt</h3>

    <p>It is wise to use a salt when hashing to make cracking the encryption harder.</p>

    <h4>Salt stored in Document</h4>
    <p>By default, the crypt extension will check if your document implements <code>Zoop\Common\Crypt\SaltInterface</code>. If so, that will be used to retireve a salt. Eg:</p>

<pre class="prettyprint linenums">
use Zoop\Common\Crypt\SaltInterface;
use Zoop\Shard\Crypt\SaltGenerator;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument implements SaltInterface {

    /**
     * @ODM\String
     * @Shard\Crypt\Hash
     */
    protected $password;

    /**
     * @ODM\String
     */
    protected $salt;

    public function getSalt(){
        if (!isset($this->salt)){
            $this->salt = SaltGenerator::generateSalt();
        }
        return $this->salt;
    }

    ...
}
</pre>

    <p>Note: if there are several hashed fields in the one document, this method will use the same salt for all.</p>

    <h4>Alternate salt</h4>

    <p>If you want to use a different salt, set the <code>salt</code> property of the annotation to a service name that will return an instance of <code>Zoop\Common\Crypt\SaltInterface</code>. Eg</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument {
    /**
     * @ODM\String
     * @Shard\Crypt\Hash(salt='mysaltservice')
     */
    protected $password;

    ...
}
</pre>

    <p>And configure your salt service in the Manifest:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    'service_manager_config' => [
        'invokables' => [
            'mysaltservice' => 'My\Salt' //A class that implements SaltInterface
        ]
    ]
]);
</pre>

    <h4>Alternate Hash Algorithim</h4>

    <p>The default hash algorithim is in <code>Zoop\Shard\Crypt\Hash\BasicHashService</code>. If you would like to use an alternate hashing algorithim, set the <code>service</code> property of the annotation to a service name that will return an instance of <code>Zoop\Shard\Crypt\Hash\HashServiceInterface</code>. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument {
    /**
     * @ODM\String
     * @Shard\Crypt\Hash(service='myhashservice')
     */
    protected $password;

    ...
}
</pre>

    <p>And configure your hash service in the Manifest:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    'service_manager_config' => [
        'invokables' => [
            'myhashservice' => 'My\Hash\Service' //A class that implements HashServiceInterface
        ]
    ]
]);
</pre>

    <h2>Block Cipher</h2>

    <p>A block cipher is a two way encryption method. That is, once the text is encrypted, you can get the plain text back.</p>

    <h3>@Shard\Crypt\BlockCipher</h3>
    <p>To encrypt a field, just add the <code>@Shard\Crypt\BlockCipher</code> annotation to that field. You must also set the name of a key service. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument {
    /**
     * @ODM\String
     * @Shard\Crypt\BlockCipher(key="mykey")
     */
    protected $password;

    ...
}
</pre>

    <h3>Key Service</h3>
    <p>The key is used to encrypt and decrypt the field. <strong>Keep your keys safe! If someone steals your keys, then they can unlock your data.</strong>. The key is set with a key service which must return an instance of <code>Zoop\Common\Crypt\KeyInterface</code>. Eg:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    'service_manager_config' => [
        'invokables' => [
            'mykey' => 'My\Key' //A class that implements KeyInterface
        ]
    ]
]);
</pre>

<pre class="prettyprint linenums">
use Zoop\Common\Crypt\KeyInterface;

class Key implements KeyInterface {

    public function getKey() {
        return 'my very secret key phrase';
    }
}
</pre>


    <h3>Block Cipher Salt</h3>

    <p>If you want to use a salt with the Block Cipher, you can.</p>

    <h4>Salt stored in Document</h4>
    <p>By default, the crypt extension will check if your document implements <code>Zoop\Common\Crypt\SaltInterface</code>. If so, that will be used to retireve a salt. Eg:</p>

<pre class="prettyprint linenums">
use Zoop\Common\Crypt\SaltInterface;
use Zoop\Shard\Crypt\SaltGenerator;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument implements SaltInterface {

    /**
     * @ODM\String
     * @Shard\Crypt\Hash
     */
    protected $password;

    /**
     * @ODM\String
     */
    protected $salt;

    public function getSalt(){
        if (!isset($this->salt)){
            $this->salt = SaltGenerator::generateSalt();
        }
        return $this->salt;
    }

    ...
}
</pre>

    <p>Note: if there are several encrypted fields in the one document, this method will use the same salt for all.</p>

    <h4>Alternate salt</h4>

    <p>If you want to use a different salt, set the <code>salt</code> property of the annotation to a service name that will return an instance of <code>Zoop\Common\Crypt\SaltInterface</code>. Eg</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument {
    /**
     * @ODM\String
     * @Shard\Crypt\BlockCypter(key='mykey', salt='mysaltservice')
     */
    protected $password;

    ...
}
</pre>

    <p>And configure your salt service in the Manifest:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    'service_manager_config' => [
        'invokables' => [
            'mykey' => 'My\Key',
            'mysaltservice' => 'My\Salt' //A class that implements SaltInterface
        ]
    ]
]);
</pre>

    <h4>Alternate Encryption Algorithim</h4>

    <p>The default block cypher algorithim is in <code>Zoop\Shard\Crypt\BlockCypher\ZendBlockCypherService</code>. If you would like to use an alternate algorithim, set the <code>service</code> property of the annotation to a service name that will return an instance of <code>Zoop\Shard\Crypt\BlockCypher\BlockCypherServiceInterface</code>. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class MyDocument {
    /**
     * @ODM\String
     * @Shard\Crypt\BlockCypher(key="mykey", service='mycipherservice')
     */
    protected $password;

    ...
}
</pre>

    <p>And configure your service in the Manifest:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    'service_manager_config' => [
        'invokables' => [
            'mykey' => 'My\Key',
            'mycipherservice' => 'My\Cipher\Service' //A class that implements BlockCipherServiceInterface
        ]
    ]
]);
</pre>

</section>
