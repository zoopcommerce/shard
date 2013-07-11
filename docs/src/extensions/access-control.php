<section id="access-control" title="Access Control">
  <div class="page-header">
    <h1>Access Control</h1>
  </div>

    <p class="lead">Add role based permissions to your documents.</p>

    <p>The Access Control extension allows you to set user access permissions on a document with simple annotations.</p>

    <h2>Configuration</h2>
    <p>Access Control has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.accessControl' => true
    ],
    ...
]);
</pre>

    <p>However, Access Control requires a configured <code>user</code> service which is an instance of <code>Zoop\Common\User\RoleAwareUserInterface</code>. See <a href="./config.html#user-config">User Config</a></p>

    <h2>Annotations</h2>
    <h3>@Shard\AccessControl</h3>

    <p>The <code>@Shard\AccessControl</code> annotation is a document annotation that can contain a list of permission annotations which define who can access the document and what level of access they have. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     ...
 * })
 */
class MyDoc {...}

</pre>

    <h3>@Shard\Permission\Basic</h3>
    <p>The Access Control extension provides the Basic permission. (Other extensions may provide other kinds of permission.) The Basic permission has three arguments: </p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th style="width: 50px;">type</th>
     <th style="width: 50px;">default</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>allow</td>
    <td>string | array</td>
    <td>null</td>
    <td>A role, or array of roles that are allowed.</td>
</tr>
<tr>
    <td>roles</td>
    <td>string | array</td>
    <td>null</td>
    <td>A role, or array of roles that the permission applies to.</td>
</tr>
<tr>
    <td>allow</td>
    <td>string | array</td>
    <td>null</td>
    <td>An action, or array of actions that are allowed.</td>
</tr>
<tr>
    <td>deny</td>
    <td>string | array</td>
    <td>null</td>
    <td>An action, or array of actions that are denied.</td>
</tr>
</tbody>
</table>

    <p>Access Control defines four actions (other extensions may define further actions):</p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>create</td>
    <td>Persist a new instance of the document to the database.</td>
</tr>
<tr>
    <td>read</td>
    <td>Read documents of this type from the database.</td>
</tr>
<tr>
    <td>update::$field</td>
    <td>Change fields on an instance of this document which has already been persisted.</td>
</tr>
<tr>
    <td>delete</td>
    <td>Perminently delete this type of document from the database.</td>
</tr>
</tbody>
</table>

    <p>So, for example, the following annotations would allow users with the <code>guest</code> permission to <code>create</code>, and <code>read</code>.</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="guest", allow={"create", "read"})
 * })
 */
class Simple {...}
</pre>

        <h2>Events</h2>

        <p>Most access control checks happen during a <code>$documentManager->flush()</code>. Therefore, when an access control check fails, an exception is not raised, as that would prevent a flush from completing correctly. Rather, an event is raised. The following events may be listened to:</p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>createDenied</td>
    <td>Fires if create is attempted and denied.</td>
</tr>
<tr>
    <td>updateDenied</td>
    <td>Fires if update is attempted and denied.</td>
</tr>
<tr>
    <td>deleteDenied</td>
    <td>Fires if delete is attempted and denied.</td>
</tr>
</tbody>
</table>

        <p>Note: there is no event for a rejected <code>read</code>. This is because read access control is achieved through query filters, meaning both Doctrine and Shard are unaware if, or how many documents may have been filtered out by read access control.</p>
        
    <h3>Default permission</h3>

    <p>If a permission is not allowed, then it is always denied.</p>
    <p>So, in this example there isn't any user who can <code>update</code> or <code>delete</code>.</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="guest", allow={"create", "read"})
 * })
 */
class Simple {...}
</pre>

    <h3>The * wildcard</h3>

    <p>The <code>*</code> can be used to glob role or action names.</p>

    <p>In this example all users are allowed to read, editors are also allowed to create, and admins can do everything.</p>
<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="read"),
 *     @Shard\Permission\Basic(roles="editor", allow="create"),
 *     @Shard\Permission\Basic(roles="admin", allow="*")
 * })
 */
class Simple {...}
</pre>

    <p>Specific actions take precidence over wild cards in the same Permission. Eg, editors are allowed to all actions except <code>delete</code></p>
<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="editor", allow="*", deny="delete")
 * })
 */
class Simple {...}
</pre>

    <h3>Order of permissions</h3>

    <p>Permissions are read in the order they are listed, so permissions lower on the list can override permissions higher on the list. Eg, if a user with the role <code>editor</code> tries to <code>create</code> they will be allowed, because the later permission overrides the earlier permission.</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles={"guest", "editor"}, allow="read"),
 *     @Shard\Permission\Basic(roles="editor", allow="create")
 * })
 */
class Simple {...}
</pre>

    <h3>Users with multiple roles</h3>

    <p>Users may have more than one role. Eg, if a user has only the editor role, they they will be allowed to create, but not be allowed to read. If a user has both the guest and editor role, they will be allowed to read and create.</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="guest", allow="read"),
 *     @Shard\Permission\Basic(roles="editor", allow="create")
 * })
 */
class Simple {...}
</pre>

    <h3>Update actions</h3>

    <p>Update actions are related to individual fields, not whole documents. To allow all fields to be updated, use the <code>*</code> wildcard. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="editor", allow="update::*")
 * })
 */
class Simple {...}
</pre>

    <p>To allow update on a specific field, use the field name. Eg:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="editor", allow="update::description")
 * })
 */
class Simple {...}
</pre>

    <p>To allow update on all fields, except some:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="editor", allow="update::*", deny={"update::lockedField1", "update::lockedField2"})
 * })
 */
class Simple {...}
</pre>

    <h2>Access Controller Service</h2>
    <p>Use the Access Controller service's <code>areAllowed</code> method to check if the configured user has permission to do actions on a document.</p>

    <h4>AccessController::areAllowed arguments</h4>
<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th style="width: 50px;">type</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>actions</td>
    <td>array</td>
    <td>An array of action names to check.</td>
</tr>
<tr>
    <td>metadata</td>
    <td>ClassMetadata</td>
    <td>The metadata for the document type being checked. This argument doesn't have to be passed, but is required if checking the <code>create</code> action, because no document instance exists before it is created.</td>
</tr>
<tr>
    <td>document</td>
    <td>Object</td>
    <td>A document instance to check permissions against. Not required when checking <code>create</code> action.</td>
</tr>
</tbody>
</table>

    <p>This method will return an AllowedResult object.</p>

    <p>For example:</p>

<pre class="prettyprint linenums">
$accessController = $manifest->getServiceManager->get('accessController');

if ( ! $accessController->areAllowed('update::name', null, $mydocument)->getAllowed()){
    //configured user is not allowed to update the name field of $mydocument;
}
</pre>

</section>
