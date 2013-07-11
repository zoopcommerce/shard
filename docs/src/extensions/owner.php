<section id="owner" title="Owner">
  <div class="page-header">
    <h1>Owner</h1>
  </div>

    <p class="lead">Add an owner field to documents, and enable the owner based access control.</p>

    <h2>Configuration</h2>
    <p>Owner has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.owner' => true
    ],
    ...
]);
</pre>

    <p>However, the Owner extension requires a configured <code>user</code> service which is an instance of <code>Zoop\Common\User\UserInterface</code>. See <a href="./config.html#user-config">User Config</a></p>

    <h2>Add an owner field</h2>

    <p>Place the <code>@Shard\Owner</code> annotation on a field.</p>
<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Owner
 */
protected $owner;
</pre>

    <p>Alternately you can use traits. Eg</p>

<pre class="prettyprint linenums">
use Zoop\Shard\Owner\DataModel\OwnerTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use OwnerTrait;
    ...
}
</pre>

    <p>The values of the field can be set and retrieved with:</p>

<pre class="prettyprint linenums">
$myDocument->setOwner();
$myDocument->getOwner();
</pre>

    <p>Note: the value of the owner field is not automatically assigned to the active use when a document is created. It must be manually set.</p>

    <h2>Owner based access control</h2>

    <p>If the Access Control extension is enabled along with the Owner extension, the <code>owner</code> role can be used to allow or deny actions if the current user is equal to the owner field.</p>

    <p>For example, the following access control annotations, only the owning user may read the document.</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="owner", allow="read")
 * })
 */
</pre>

    <h2>Access control owner field update</h2>

    <p>It is normally required to access control the ability to change who owns a document. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",     allow={"create", "read"}                     ),
 *     @Shard\Permission\Basic(roles="owner", allow="update::*",       deny="update::owner"),
 *     @Shard\Permission\Basic(roles="admin", allow="update::owner"                        )
 * })
 */
</pre>

    <p>In this example, all roles can read. Only the own can update a document. And only an admin can update the document owner.</p>

</section>
