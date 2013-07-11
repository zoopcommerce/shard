<section id="examples" title="Examples">
  <div class="page-header">
    <h1>Examples</h1>
  </div>

    <p class="lead">Most Shard extensions use annotations to add behaviors to documents.</p>

    <h2>Update Timestamp</h2>
    <p>Add the following annotation to a field. Whenever the document is updated, the field will be updated with a timestamp. Eg:</p>
<pre class="prettyprint linenums">
/**
 * @ODM\Timestamp
 * @Shard\Stamp\UpdatedOn
 */
protected $updatedOn;
</pre>

    <h2>Soft Delete</h2>
    <p>To make a document soft deletable, add the following annotation to a field:</p>
<pre class="prettyprint linenums">
/**
 * @ODM\Boolean
 * @Shard\SoftDelete
 * )
 */
protected $softDeleted = false;
</pre>

    <p>Then to Soft delete the document, use the SoftDeleter service:</p>
<pre class="prettyprint linenums">
$softDeleter = $manifest->getServiceManager()->get('softDeleter');
$softDeleter->softDelete($myDocument);
</pre>

    <p>The SoftDeleter service can also be used to restore a document:</p>
<pre class="prettyprint linenums">
$softDeleter->restore($myDocument);
</pre>

    <h2>Data validation</h2>
    <p>Use annotations to add a validator to a field. Whenever the document is created or updated, the validators will be checked before the data is persisted.</p>
<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Validator\Email
 * )
 */
protected $email;
</pre>

    <p>All the validators in the <a href="http://zoopcommerce.github.io/mystique">Mystique</a> validator library are supported, including validator chains. Eg:</p>
<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Validator\Chain({
 *     @Shard\Validator\Required,
 *     @Shard\Validator\Email
 * })
 * )
 */
protected $email;
</pre>

    <h2>Access Control</h2>
    <p>Shard supports adding fine grained role based access control to documents. Eg, give all guest users read access, and admins complete control:</p>
<pre class="prettyprint linenums">
/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="guest", allow="read")
 *     @Shard\Permission\Basic(roles="admin", allow="*")
 * })
 */
class Simple {...}
</pre>
  
</section>
