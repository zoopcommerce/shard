<section id="serializer" title="Serializer">
  <div class="page-header">
    <h1>Serializer</h1>
  </div>

    <p class="lead">Serialize document instances to array or json, and unserialize back to document instances.</p>
    <p>The shard Serializer offers fine grained control over how documents are serialized and unserialized, with particluar mind to ajax and web clients.</p>

    <h2>Configuration</h2>
    <p>The serializer does not require any specific configuration.</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.serializer' => true
    ],
    ...
]);
</pre>

    <p>However, some configuration options are available:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.serializer' => [
            'type_serializers' => [...]
            'max_nesting_depth' => [...]
        ]
    ],
    ...
]);
</pre>

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
    <td>type_serializer</td>
    <td>array</td>
    <td>['date' => 'serializer.type.dateToISO8601']</td>
    <td>An array of type serializers. For more information see below.</td>
</tr>
<tr>
    <td>max_nesting_depth</td>
    <td>integer</td>
    <td>1</td>
    <td>How deeply should the tree of references inside documents be followed when serializing.</td>
</tr>
</tbody>
</table>

    <h2>Serializing Documents</h2>

    <p>First get the Serializer service, and then it can be used to serialize documents:</p>
<pre class="prettyprint linenums">
$serializer = $manifest->getServiceManager()->get('serializer');

$array = $serializer->toArray($myDocument); //serialize to array
$json = $serializer->toJson($myDocument);   //serialize to json
</pre>

    <h2>@Shard\Serializer\Ignore and @Shard\Unserializer\Ignore</h2>
    <p>Place on a document field to control if the field is serialized</p>

    <p>Always ignore the field, eg:</p>
<pre class="prettyprint linenums">
/**
 * @ODM\String
 */
protected MyProperty;

/* or */

/**
 * @ODM\String
 * @Shard\Serializer\Ignore
 * @Shard\Unserializer\Ignore
 */
protected MyProperty;
</pre>

    <p>Ignore a field only when serializing (not when unserializing):</p>
<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Serializer\Ignore
 */
protected MyProperty;
</pre>

    <p>Ignore a field only when unserializing (not when serializing):</p>
<pre class="prettyprint linenums">
/**
 * @ODM\String
 * @Shard\Unserializer\Ignore
 */
protected MyProperty;
</pre>

    <h2>Reference Serializers</h2>

    <p>Document references can be serialized in several differnet ways.</p>

    <h3>RefLazy</h3>

    <p>By default references will be serialized to an array like this:</p>
<pre class="prettyprint linenums">
[$ref: 'CollectionName/DocumentId']
</pre>

    <p>The <code>$ref</code> style of referencing is what Mongo uses internally.</p>

    <p>The default behaviour uses the RefLazy serializer. However this can be overridden by defineing an alternative ReferenceSerializer as a property annotation.</p>

    <p>Two alternate ReferenceSerializers are already included with Shard.</p>

    <h3>SimpleLazy</h3>

    <p>SimpleLazy will serialize a reference as the mongo id. It can be used like this:</p>
<pre class="prettyprint linenums">
/**
 * @ODM\ReferenceMany(targetDocument="MyTargetDocument")
 * @Sds\Serializer\SimpleLazy
 */
protected $myDocumentProperty;
</pre>

    <h3>Eager</h3>
    <p>Eager will serialize references as if they were embedded documents. It can be used like this:</p>
<pre class="prettyprint linenums">
/**
 * @ODM\ReferenceMany(targetDocument="MyTargetDocument")
 * @Sds\Serializer\Eager
 */
protected $myDocumentProperty;
</pre>

    <p>When using the Eager serializer, the maxNestingDepth configuration option will control how deep the Eager serializer will go into a tree of references.</p>

    <h3>Custom Reference Serializer</h3>

    <p>You can create your own reference serializer to render references however you like. To do so, implement the <code>Shard\Serializer\Reference\ReferenceSerializerInterface</code>.</p>

    <p>Then register your serializer with the service manager in the manifest config:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.serializer' => [
            ...
        ]
    ],
    'service_manager_config' => [
        'invokables' => [
            'my.reference.serializer' => 'My\ReferenceSerializer\Class'
        ],
        ...
    ]
]);
</pre>

    <p>To use your reference serializer, use the annotation:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\ReferenceMany(targetDocument="MyTargetDocument")
 * @Sds\Serializer\ReferenceSerializer("my.reference.serializer")
 */
protected $myDocumentProperty;
</pre>

    <h2>Date Serializer</h2>

    <p>Fields of type <code>date</code> are serialized by default with <code>Shard\Serializer\Type\DateToISO8601</code>.</p>

    <p>To override this format, see Custom Type Serializers below</p>

    <h2>Custom Type Serializers</h2>

    <p>Each document field has an associated type, such as string or date. Serialization may be customized by type.</p>

    <p>First create a class which implements the <code>Shard\Serializer\Type\TypeSerializerInterface</code>. You will need to define serialize and unserialize methods.</p>

    <p>For example, this class will uppercase the first letter of every string when serializing, and lower case the first letter when unserializing:</p>

<pre class="prettyprint linenums">
use Shard\Serializer\Type\TypeSerializerInterface;

class MyStringSerializer implements TypeSerializerInterface {

    public static function serialize($value) {
        return ucfirst($value);
    }

    public static function unserialize($value) {
        return lcfirst($value);
    }
}
</pre>

    <p>Then the class needs to be registered in the extension config and service manager:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.serializer' => [
            'type_serializers' => [
                'string' => 'my.string.serializer'
            ]
        ]
    ],
    'service_manager_config' => [
        'invokables' => [
            'my.string.serializer' => 'MyStringSerializer'
        ],
        ...
    ]
]);
</pre>

    <p>The default Date serializer is an example of a Type Serializer which is regisered by default. To over ride it, simply register your own in the extension config.</p>

    <h2>Unserializing Documents</h2>

    <p>Get the unserializer from the service manager:</p>

<pre class="prettyprint linenums">
$unserializer = $manifest->getServiceManager()->get('unserializer');
</pre>

    <p>Both <code>fromArray</code> and <code>fromJson</code> will unserialize an array or json into a document. They take up to four arguments:</p>

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
    <td>data</td>
    <td>array | string</td>
    <td></td>
    <td>The data to be unserialized. An array for <code>fromArray</code>, or a json string for <code>fromJson</code>.</td>
</tr>
<tr>
    <td>className</td>
    <td>string</td>
    <td>null</td>
    <td>The class name of the document instance to create.</td>
</tr>
<tr>
    <td>document</td>
    <td>object</td>
    <td>null</td>
    <td>If supplied, the unserializer won't attempt to load any document from the db, but use this one instead.</td>
</tr>
<tr>
    <td>mode</td>
    <td>string</td>
    <td>unserialize_patch</td>
    <td>Must be either <code>unserialize_update</code> or <code>unserialize_patch</code>. If <code>unserialize_update</code> is used, the unserialized document will replace any existing document in the db with the same id. If <code>unserialize_patch</code> is used the unserialized data will be merged with any existing document in the db.</td>
</tr>
</tbody>
</table>

</section>
