<section id="manual-config" title="Manual Config">
  <div class="page-header">
    <h1>Manual Config</h1>
  </div>

    <p class="lead">Hook Shard into the Doctrine configuration process.</p>

    <h2>Create a Manifest</h2>
    <p>The first thing to do is create a Manifest. The Manifest constructor takes a configuration array. Configuration keys are:</p>

    <h3>models</h3>
    <p>Specifies any document namespaces you want the DocumentManager to use</p>

<pre class="prettyprint linenums">
'models' => [
    'My\Document\Namespace' => 'my/document/directory/'
]
</pre>

    <h3>extension_configs</h3>
    <p>Specifies the extensions you want to enable. The keys are extension names. The values are extension config arrays. If you don't want to pass any config, just set the value to true.</p>
    <p>You must always include <code>extension.odmcore</code></p>
    <p>Eg:</p>

<pre class="prettyprint linenums">
'extension_configs' => [
    'extension.odmcore',
    'extension.accesscontrol' => true,
]
</pre>

    <h3>service_manager_config</h3>
    <p>Shard makes extensive use of <code>Zend/ServiceManager/ServiceManager</code>. You don't need to know all the ins and outs of this class, it's just a container for holding services. The config tells the container where to get those services from, and then you can get them by calling <code>$serviceManager->get('serviceName')</code></p>

<pre class="prettyprint linenums">
'service_manager_config' => [
    'invokables' => [...],
    'factories' => [...],
    'abstract_factories => [...]
]
</pre>

    <h3>Putting it all together</h3>
    <p>You can create a Manifest like this:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    'models' => [
        'My\Document\Namespace' => 'my/document/directory/' //Specify the location of any documents you want to use.
    ],
    'extension_configs' => [
        'extension.accesscontrol' => true //List any extensions you want to enable
    ],
]);
</pre>

    <h3>Manifest resources</h3>

    <p>Once created, the manifest can be used to retrieve all the resources used by all the extensions for the configuration of a DocumentManager. Eg:</p>

<pre class="prettyprint linenums">
$manifest->getModels(); //Array of all the document namespaces required

$manifest->getSubscribers(); //Array of all the event subscribers required
</pre>

    <h2>DocumentManager Factory</h2>

    <p>By default, Shard configures a document manager for development use. It has default useful whilst creating your app, but is not tuned for production performance.</p>

    <p>To configure your own document manager to your taste, override the <code>modelmanager</code> service with your own factory.</p>

<pre class="prettyprint linenums">
'service_manager_config' => [
    'factories' => [
        'modelmanager' => 'My\DocumentManager\Factory'
    ]
]
</pre>

    <p>Take a look at <code>Zoop\Shard\ODMCore\DevDocumentManagerFactory</code> for inspiration on creating your own factory.</p>

    <h2>Using it</h2>

    <p>With a configured Manifest and DocumentManager factory, you can get the DocumentManger with:</p>

<pre class="prettyprint linenums">
$manifest = new Manifest([...]);
$documentManager = $manifest->getModelManager();
</pre>

    <p>Then just use the DocumentManager, and the extensions will do all their work for you.</p>

    <p>Extensions may also provide other services which are available through the ServiceManager. For example, the Serializer extension provides a Serializer:</p>

<pre class="prettyprint linenums">
$serializer = $manifest->getServiceManager()->get('serializer');
</pre>

</section>

