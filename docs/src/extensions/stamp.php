<section id="stamp" title="Stamp">
  <div class="page-header">
    <h1>Stamp</h1>
  </div>

    <p class="lead">Stamp documents with creation and update timestamp and user.</p>

    <h2>Configuration</h2>
    <p>Stamp has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.stamp' => true
    ],
    ...
]);
</pre>

    <h2>Create and Update stamps</h2>

    <h3>Timestamps</h3>
    <p>The stamp extension supports automatic timestamping of create and update events on documents. Use the <code>@Shard\Stamp\CreatedOn</code> and <code>@Shard\Stamp\UpdatedOn</code> annotations. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Timestamp
 * @Shard\Stamp\CreatedOn
 */
protected $createdOn;

/**
 * @ODM\Timestamp
 * @Shard\Stamp\UpdatedOn
 */
protected $updatedOn;
</pre>

    <p>Alternately you can use traits. Eg</p>

<pre class="prettyprint linenums">
use Zoop\Shard\Stamp\DataModel\CreatedOnTrait;
use Zoop\Shard\Stamp\DataModel\CreatedOnTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use CreatedOnTrait;
    use UpdatedOnTrait;
    ...
}
</pre>

    <p>The values of the fields can be retrieved with:</p>

<pre class="prettyprint linenums">
$myDocument->getCreatedOn();
$myDocument->getUpdatedOn();
</pre>

    <h3>User stamps</h3>
    <p>The stamp extension supports automatic stamping with the active username on document create and update. Use the <code>@Shard\Stamp\CreatedBy</code> and <code>@Shard\Stamp\UpdatedBy</code> annotations. This requires a configured <a href="./config.href#user-config">user</a>. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Stamp\CreatedBy
 */
protected $createdBy;

/**
 * @ODM\String
 * @Shard\Stamp\UpdatedBy
 */
protected $updatedBy;
</pre>

    <p>Alternately you can use traits. Eg</p>

<pre class="prettyprint linenums">
use Zoop\Shard\State\DataModel\CreatedByTrait;
use Zoop\Shard\State\DataModel\UpdatedByTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use CreatedByTrait;
    use UpdatedByTrait;
    ...
}
</pre>

    <p>The values of the fields can be retrieved with:</p>

<pre class="prettyprint linenums">
$myDocument->getCreatedBy();
$myDocument->getUpdatedBy();
</pre>


        <h2>Access Conntrol</h2>

        <p>The stamp extension can hook into the Access Control extension to provide the extra roles of <code>creator</code> and <code>updater</code>. Permissions can be allowed or denied if the current active user is the creator or updater. Eg:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.accessControl' => true,
        'extension.stamp' => true
    ],
    ...
]);
</pre>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="creator", allow="update::*")
 *     ...
 * })
 */
class Simple {...}
</pre>

</section>
