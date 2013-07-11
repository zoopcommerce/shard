<section id="freeze" title="Freeze">
  <div class="page-header">
    <h1>Freeze</h1>
  </div>

    <p class="lead">Freeze documents against updating or deleting.</p>

    <h2>Configuration</h2>
    <p>Freeze has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.freeze' => true
    ],
    ...
]);
</pre>

    <h2>Making a document freezable</h2>

    <p>To make a document freezable, a boolean field should be annotated with <code>@Shard\Freeze</code>. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Boolean
 * @Shard\Freeze
 */
protected $frozen = false;
</pre>

    <p>For convienence you can use the <code>Zoop\Shard\Freeze\DataModel\FreezableTrait</code> to add such a field to a document. Eg:</p>

<pre class="prettyprint linenums">
use Zoop\Shard\Freeze\DataModel\FreezeableTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use FreezeableTrait;

    ...
}
</pre>

    <h2>Using the Freezer service</h2>
    <p>The freezer can be used to freeze and thaw documents. When frozen they cannot be updated or deleted. Note that the frozen state is not persisted until the DocumentManager is flushed. Eg:</p>

<pre class="prettyprint linenums">
$freezer = $manifest->getServiceManager()->get('freezer'); //get the freezer service
$freezer->freeze($myDocument); //freeze a document

$freezer->thaw($anotherDocument); //thaw a document

$manifest->getServiceManager()->get('mydocumentmanager')->flush() //flush to persist changes
</pre>


    <h2>Freeze and Thaw stamps</h2>

    <h3>Timestamps</h3>
    <p>The freeze extension support automatic timestamping of freeze and thaw events. Use the <code>@Shard\Freeze\FrozenOn</code> and <code>@Shard\Freeze\ThawedOn</code> annotations. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Timestamp
 * @Shard\Freeze\FrozenOn
 */
protected $frozenOn;

/**
 * @ODM\Timestamp
 * @Shard\Freeze\ThawedOn
 */
protected $thawedOn;
</pre>

    <p>Alternately you can use traits. Eg</p>

<pre class="prettyprint linenums">
use Zoop\Shard\Freeze\DataModel\FreezeableTrait;
use Zoop\Shard\Freeze\DataModel\FreezenOnTrait;
use Zoop\Shard\Freeze\DataModel\ThawedOnTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use FreezeableTrait;
    use FrozenOnTrait;
    use ThawedOnTrait;
    ...
}
</pre>

    <p>The values of the fields can be retrieved with:</p>

<pre class="prettyprint linenums">
$myDocument->getFrozenOn();
$myDocument->getThawedOn();
</pre>

    <h3>User stamps</h3>
    <p>The freeze extension support automatic stamping with the active username on freeze and thaw events. Use the <code>@Shard\Freeze\FrozenBy</code> and <code>@Shard\Freeze\ThawedBy</code> annotations. This requires a configured <a href="./config.href#user-config">user</a>. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Freeze\FrozenBy
 */
protected $frozenBy;

/**
 * @ODM\String
 * @Shard\Freeze\ThawedBy
 */
protected $thawedBy;
</pre>

    <p>Alternately you can use traits. Eg</p>

<pre class="prettyprint linenums">
use Zoop\Shard\Freeze\DataModel\FreezeableTrait;
use Zoop\Shard\Freeze\DataModel\FreezenByTrait;
use Zoop\Shard\Freeze\DataModel\ThawedByTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class MyDocument {

    use FreezeableTrait;
    use FrozenByTrait;
    use ThawedByTrait;
    ...
}
</pre>

    <p>The values of the fields can be retrieved with:</p>

<pre class="prettyprint linenums">
$myDocument->getFrozenBy();
$myDocument->getThawedBy();
</pre>


        <h2>Access Conntrol</h2>

        <p>The Freeze extension can hook into the Access Control extension to allow or deny roles to the <code>freeze</code> and <code>thaw</code> actions. This requires the Access Control extension to be enabled, as well as the Freeze extension. Eg:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.accessControl' => true,
        'extension.freeze' => true
    ],
    ...
]);
</pre>

        <p>Permissions can then be used as normal with the added actions of <code>freeze</code> and <code>thaw</code>. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="editor", allow="freeze", deny="thaw")
 *     ...
 * })
 */
class Simple {...}
</pre>

        <h2>Freeze Filter</h2>

        <p>The freeze extension provides a filter that can be used to remove frozen documents from result sets.</p>

        <p>To filter out all frozen documents, use:</p>

<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('freeze');
</pre>

        <p>To filter so <i>only</i> frozen documents are returned use:</p>

<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('freeze');
$filter = $documentManager->getFilterCollection()->getFilter('freeze');
$filter->onlyFrozen();
</pre>


        <h2>Events</h2>

        <p>Freeze provides the following events which can be subscribed to with the Doctrine EventManager:</p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>preFreeze</td>
    <td>Fires before freeze happens.</td>
</tr>
<tr>
    <td>postFreeze</td>
    <td>Fires after freeze happens.</td>
</tr>
<tr>
    <td>preThaw</td>
    <td>Fires before thaw happens.</td>
</tr>
<tr>
    <td>postThaw</td>
    <td>Firest after thaw happens.</td>
</tr>
<tr>
    <td>freezeDenied</td>
    <td>Fires if a freeze is attempted but denied by access control.</td>
</tr>
<tr>
    <td>thawDenied</td>
    <td>Fires if a thaw is attempted by denied by access control.</td>
</tr>
<tr>
    <td>frozenUpdateDenied</td>
    <td>Fires if attempt is made to update a frozen document.</td>
</tr>
<tr>
    <td>frozenDeleteDenied</td>
    <td>Fires if attempt is made to delete a frozen document.</td>
</tr>
</tbody>
</table>

</section>
