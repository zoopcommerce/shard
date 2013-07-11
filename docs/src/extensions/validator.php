<section id="validator" title="Validator">
  <div class="page-header">
    <h1>Validator</h1>
  </div>

    <p class="lead">Add validation to documents.</p>
    <p>Validate fields, and whole documents so only quality data makes it into your database.</p>
    <p>The Validator extensions uses the <a href="http://zoopcommerce.github.io/mystique">Mystique</a> validator library.</p>

    <h2>Configuration</h2>
    <p>State has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.validator' => true
    ],
    ...
]);
</pre>

    <h2>Adding validation to a Field</h2>

    <p>To add a validator use annotations in the <code>@Shard\Validator</code> namespace. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Validator\Length("min" = 5, "max" = 10)
 */
protected $myproperty;
</pre>

    <p>To add a multiple validators in a chain, use <code>@Shard\Validator\Chain</code>. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Validator\Chain({
 *     @Shard\Validator\Alpha,
 *     @Shard\Validator\Length("min" = 5, "max" = 10)
 * })
 */
protected $myproperty;
</pre>

    <p>All the validators in the Mystique library are supported with their own annotation.</p>

    <h2>Custom Validators</h2>

    <p>To write your own validators, inherit from <code>Zoop\Mystique\Base</code> and use the <code>@Shard\Validator</code> annotation.</p>

<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Validator("class" = "My\Validator\Class", "options" = {"constuctor options array"})
 */
protected $myproperty;
</pre>

    <h2>Document validators</h2>

    <p>Sometimes you want a validation rule that interrogates more than one field. To do so, just use the <code>@Shard\Validator</code> annotation on a document, rather than a field. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Document
 * @Shard\Validator(class = "Zoop\Shard\Test\Validator\TestAsset\ClassValidator")
 */
class Simple {
    ...
}
</pre>

    <p>The <code>isValid</code> method of the validator will be passed the complete document instance to validate.</p>

    <h2>Document Validator Service</h2>

    <p>To validate a document before flush, use the documentValidator service. This will validate all fields and any document validators. Eg:</p>
<pre class="prettyprint linenums">
$documentValidator = $manifest->getServiceManager()->get('documentValidator');
$result = $documentValidator->isValid($myDocument);
</pre>


        <h2>Events</h2>

        <p>Validator provides the following events which can be subscribed to with the Doctrine EventManager:</p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>invalidUpdate</td>
    <td>Fires during flush if and updated document is invalid.</td>
</tr>
<tr>
    <td>invalidCreate</td>
    <td>Fires during flush if and updated document is invalid.</td>
</tr>
</tbody>
</table>
</section>
