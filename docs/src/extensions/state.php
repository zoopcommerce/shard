<section id="state" title="State">
  <div class="page-header">
    <h1>State</h1>
  </div>

    <p class="lead">Add state to documents and build document workflows.</p>
    <p>A document has a state, such as 'draft' and can be transitioned to another state, such as 'published'.</p>

    <h2>Configuration</h2>
    <p>State has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.state' => true
    ],
    ...
]);
</pre>

    <h2>Adding state to a Document</h2>

    <p>To add state, add <code>@Shard\State</code> to a field. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\State
 */
protected $state;
</pre>

    <p>For convienence you can use the <code>Zoop\Shard\State\DataModel\StateTrait</code> to add such a field to a document.</p>

    <h2>State Access Control</h2>

    <p>Access Control permissions can be tied to document state. Use <code>@Shard\Permission\State</code>.</p>

    <p>Eg. These permissions allow all roles to read only when a document is in a published state. A writer role is allowed to create, read and update documents in a draft state.</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\State     (roles="*",      state="published", allow="read"                      ),
 *     @Shard\Permission\State     (roles="writer", state="draft",     allow={"create", "update", "read"}),
 * })
 */
class AccessControlled {
    ...
}
</pre>

    <h2>Transition Access Control</h2>

    <p>Access Control permission can use used to control who can make changes to document state. Use <code>@Shard\Permission\Transition</code></p>

    <p>Eg. These permissions allow a writer to move a document from draft to reivew. A reviewer may move a document from review to draft or review to published. An admin can make any transition.</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Transition(roles="writer",   allow="draft->review"                       ),
 *     @Shard\Permission\Transition(roles="reviewer", allow={"review->draft", "review->published"}),
 *     @Shard\Permission\Basic     (roles="admin",    allow="*"                                   )
 * })
 */
class AccessControlled {
    ...
}
</pre>

        <h2>State Filter</h2>

        <p>The state extension provides a filter that can be used to filter result sets based on document state.</p>

        <p>The state filter takes a list of states, and if those states should be included or excluded.</p>

        <p>Eg, exclude some states:</p>
<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('state');
$filter = $documentManager->getFilterCollection()->getFilter('state');
$filter->setStates(['inactive']);
$filter->excludeStateList();
</pre>

        <p>Eg, include some states:</p>
<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('state');
$filter = $documentManager->getFilterCollection()->getFilter('state');
$filter->setStates(['published', 'draft']);
$filter->includeStateList();
</pre>

        <h2>Events</h2>

        <p>State provides the following events which can be subscribed to with the Doctrine EventManager:</p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>preTransition</td>
    <td>Fires before state transition happens.</td>
</tr>
<tr>
    <td>onTransition</td>
    <td>Fires when a transition is happening. That is, after access control checks but before a new document change set is prepared for document persistance.</td>
</tr>
<tr>
    <td>postTransition</td>
    <td>Fires after a transition has happened.</td>
</tr>
<tr>
    <td>transitionDenied</td>
    <td>Fires if Access Control has denied permission to make a requested transition.</td>
</tr>
</tbody>
</table>
</section>
