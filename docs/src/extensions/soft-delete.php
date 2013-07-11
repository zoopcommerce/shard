<section id="soft-delete" title="Soft Delete">
  <div class="page-header">
    <h1>Soft Delete</h1>
  </div>

    <p class="lead">Mark documents as deleted, without actually deleting them.</p>
    <p>Soft Deleted documents are simply marked as soft deleted, so they can be filtered out from result sets. Soft Deleted documents cannot be updated. However, note that Soft Deleted documents can still be fully deleted. If you need to control delete access, then use the Access Control extension.</p>

    <h2>Configuration</h2>
    <p>Soft Delete has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.softDelete' => true
    ],
    ...
]);
</pre>

    <h2>Making a document soft deletable</h2>

    <p>To make a document soft deleteable, a boolean field should be annotated with <code>@Shard\SoftDelete</code>. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Boolean
 * @Shard\SoftDelete
 */
protected $softDeleted = false;
</pre>

    <p>For convienence you can use the <code>Zoop\Shard\SoftDelete\DataModel\SoftDeletableTrait</code> to add such a field to a document. Eg:</p>

<pre class="prettyprint linenums">
use Zoop\Shard\SoftDelete\DataModel\SoftDeletableTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use SoftDeletableTrait;

    ...
}
</pre>

    <h2>Using the SoftDeleter service</h2>
    <p>The SoftDeleter can be used to soft delete and restore documents. Note that the soft delete state is not persisted until the DocumentManager is flushed. Eg:</p>

<pre class="prettyprint linenums">
$softDeleter = $manifest->getServiceManager()->get('softDeleter'); //get the softDeleter service
$softDeleter->softDelete($myDocument); //soft delete a document

$softDeleter->restore($anotherDocument); //restore a document

$manifest->getServiceManager()->get('mydocumentmanager')->flush() //flush to persist changes
</pre>


    <h2>Soft Delete and Restore stamps</h2>

    <h3>Timestamps</h3>
    <p>The soft delete extension supports automatic timestamping of soft delete and restore events. Use the <code>@Shard\SoftDelete\SoftDeletedOn</code> and <code>@Shard\SoftDelete\RestoredOn</code> annotations. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Timestamp
 * @Shard\SoftDelete\SoftDeletedOn
 */
protected $softDeletedOn;

/**
 * @ODM\Timestamp
 * @Shard\SoftDelete\RestoredOn
 */
protected $restoredOn;
</pre>

    <p>Alternately you can use traits. Eg</p>

<pre class="prettyprint linenums">
use Zoop\Shard\SoftDelete\DataModel\SoftDeletableTrait;
use Zoop\Shard\SoftDelete\DataModel\SoftDeletedOnTrait;
use Zoop\Shard\SoftDelete\DataModel\RestoredOnTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use SoftDeletableTrait;
    use SoftDeletedOnTrait;
    use RestoredOnTrait;
    ...
}
</pre>

    <p>The values of the fields can be retrieved with:</p>

<pre class="prettyprint linenums">
$myDocument->getSoftDeletedOn();
$myDocument->getRestoredOn();
</pre>

    <h3>User stamps</h3>
    <p>The soft delete extension supports automatic stamping with the active username on soft delete and restore events. Use the <code>@Shard\SoftDelete\SoftDeletedBy</code> and <code>@Shard\SoftDelete\RestoredBy</code> annotations. This requires a configured <a href="./config.href#user-config">user</a>. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\SoftDelete\SoftDeletedBy
 */
protected $softDeletedBy;

/**
 * @ODM\String
 * @Shard\SoftDelete\RestoredBy
 */
protected $restoredBy;
</pre>

    <p>Alternately you can use traits. Eg</p>

<pre class="prettyprint linenums">
use Zoop\Shard\SoftDelete\DataModel\SoftDeleteableTrait;
use Zoop\Shard\SoftDelete\DataModel\SoftDeletedByTrait;
use Zoop\Shard\SoftDelete\DataModel\RestoredByTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use SoftDeleteableTrait;
    use SoftDeletedByTrait;
    use RestoredByTrait;
    ...
}
</pre>

    <p>The values of the fields can be retrieved with:</p>

<pre class="prettyprint linenums">
$myDocument->getSoftDeletedBy();
$myDocument->getRestoredBy();
</pre>


        <h2>Access Conntrol</h2>

        <p>The soft deleted extension can hook into the Access Control extension to allow or deny roles to the <code>softDelete</code> and <code>restore</code> actions. This requires the Access Control extension to be enabled, as well as the Soft Delete extension. Eg:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.accessControl' => true,
        'extension.softDelete' => true
    ],
    ...
]);
</pre>

        <p>Permissions can then be used as normal with the added actions of <code>softDelete</code> and <code>restore</code>. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="editor", allow="softDelete", deny="restore")
 *     ...
 * })
 */
class Simple {...}
</pre>

        <h2>Soft Delete Filter</h2>

        <p>The soft delete extension provides a filter that can be used to remove soft deleted documents from result sets.</p>

        <p>To filter out all soft deleted documents, use:</p>

<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('softDelete');
</pre>

        <p>To filter so <i>only</i> soft deleted documents are returned use:</p>

<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('softDelete');
$filter = $documentManager->getFilterCollection()->getFilter('softDelete');
$filter->onlySoftDeleted();
</pre>

        <h2>Events</h2>

        <p>Soft Delete provides the following events which can be subscribed to with the Doctrine EventManager:</p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>preSoftDelete</td>
    <td>Fires before soft delete happens.</td>
</tr>
<tr>
    <td>postSoftDelete</td>
    <td>Fires after soft delete happens.</td>
</tr>
<tr>
    <td>preRestore</td>
    <td>Fires before restore happens.</td>
</tr>
<tr>
    <td>postRestore</td>
    <td>Firest after restore happens.</td>
</tr>
<tr>
    <td>softDeleteDenied</td>
    <td>Fires if a soft delete is attempted but denied by access control.</td>
</tr>
<tr>
    <td>restoreDenied</td>
    <td>Fires if a restore is attempted by denied by access control.</td>
</tr>
<tr>
    <td>softDeleteUpdateDenied</td>
    <td>Fires if attempt is made to update a soft deleted document.</td>
</tr>
</tbody>
</table>
</section>
