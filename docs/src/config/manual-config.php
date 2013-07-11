<section id="manual-config" title="Manual Config">
  <div class="page-header">
    <h1>Manual Config</h1>
  </div>

    <p class="lead">Hook Shard into the Doctrine configuration process.</p>

    <p>The DocumentManager is at the core of Doctrine Mongo ODM. Surprisingly enough, it manages all the fetching and persistence of documents. Configuring a DocumentManager can be quite involved, because it needs to know about a lot of stuff. Adding Shard into the mix doesn't make it more complex, but it is helpful to know what is going on. Config requires these elements:</p>

    <ul>
        <li>Standard DocumentManager config, including locations of document classes, proxies, hydrators, filters, and database connection details.</li>
        <li>A Shard Manifest specifying which Shard extensions to use</li>
        <li>A DocumentManager and a Shard Manifest need to be linked together. (note: Shard supports multiple document managers, each with their own unique set of extensions. However, a DocumentManager and a Manifest must be linked in a one to one relationship. That is, a single manifest can't service multiple DocumentManagers, and a single DocumentManager shouldn't be linked to multiple Manifests.)</li>
    </ul>

    <h2>Create a Manifest</h2>
    <p>The first thing to do is create a Manifest. The Manifest constructor takes a configuration array. Configuration keys are:</p>

    <h3>documents</h3>
    <p>Specifies any document namespaces you want the DocumentManager to use</p>

<pre class="prettyprint linenums">
'documents' => [
    'My\Document\Namespace' => 'my/document/directory/'
]
</pre>

    <h3>extension_configs</h3>
    <p>Specifies the extensions you want to enable. The keys are extension names. The values are extension config arrays. If you don't want to pass any config, just set the value to true. Eg:</p>

<pre class="prettyprint linenums">
'extension_configs' => [
    'extension.accessControl' => true,
    'extension.rest' => ['endpointMap' => [...]]
]
</pre>

    <h3>document_manager</h3>
    <p>Specifies the service name of the DocumentManager to use. See below for an explaination of service names and services.</p>

<pre class="prettyprint linenums">
'document_manager' => 'mydocumentmanager'
</pre>

    <h3>service_manager_config</h3>
    <p>Shard makes extensive use of <code>Zend/ServiceManager/ServiceManager</code>. You don't need to know all the ins and outs of this class, it's just a container for holding services. The config tells the container where to get those services from, and then you can get them by calling <code>$serviceManager->get('serviceName')</code></p>

    <p>You must configure the service used for the <code>document_manager</code> config key above. It's recommended that you use a factory, and there is an example factory below.</p>

<pre class="prettyprint linenums">
'service_manager_config' => [
    'invokables' => [...],
    'factories' => [
        'mydocumentmanager' => 'My\DocumentManager\Factory' //A factory that can create the DocumentManager
    ],
    'abstract_factories => [...]
]
</pre>

    <h3>Putting it all together</h3>
    <p>You can create a Manifest like this:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    'documents' => [
        'My\Document\Namespace' => 'my/document/directory/' //Specify the location of any documents you want to use.
    ],
    'extension_configs' => [
        'extension.accessControl' => true //List any extensions you want to enable
    ],
    'document_manager' => 'mydocumentmanager', //The service name of the DocumentManager
    'service_manager_config' => [
        'factories' => [
            'mydocumentmanager' => 'My\DocumentManager\Factory' //A factory that can create the DocumentManager
        ]
    ]
]);
</pre>

    <h3>Manifest resources</h3>

    <p>Once created, the manifest can be used to retrieve all the resources used by all the extensions for the configuration of a DocumentManager. Eg:</p>

<pre class="prettyprint linenums">
$manifest->getDocuments(); //Array of all the document namespaces required

$manifest->getFilters(); //Array of all the filters required

$manifest->getSubscribers(); //Array of all the event subscribers required
</pre>

    <h2>DocumentManager Factory</h2>

    <p>The resources in the Manifest can be used in a factory class to create a DocumentManger. Eg:</p>

<pre class="prettyprint linenums">
namespace My\DocumentManager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Factory implements FactoryInterface
{

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return DocumentManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //Get the Shard Manifest
        $manifest = $serviceLocator->get('manifest');

        $config = new Configuration();

        //Set the proxy directory
        $config->setProxyDir('my/proxy/directory');
        $config->setProxyNamespace('Proxies');

        //Set the hydrator directory
        $config->setHydratorDir('my/hydrator/directory');
        $config->setHydratorNamespace('Hydrators');

        //Set the db name
        $config->setDefaultDB('my_db_name');

        //Set the metadata cache type
        $config->setMetadataCacheImpl(new ArrayCache);

        //create driver chain
        $chain  = new MappingDriverChain;

        //add all the document folders in the manifest
        foreach ($manifest->getDocuments() as $namespace => $path){
            $driver = new AnnotationDriver(new AnnotationReader, $path);
            $chain->addDriver($driver, $namespace);
        }
        $config->setMetadataDriverImpl($chain);

        //register manifest filters
        foreach ($manifest->getFilters() as $name => $class){
            $config->addFilter($name, $class);
        }

        //create event manager
        $eventManager = new EventManager();

        //register manifest event subscribers
        foreach($manifest->getSubscribers() as $subscriber){
            $eventManager->addEventSubscriber($serviceLocator->get($subscriber));
        }

        //register annotations
        AnnotationRegistry::registerLoader(function($className) {
            return class_exists($className);
        });

        //create the connection
        $conn = new Connection(null, array(), $config);

        //create and return the DocumentManager yay!
        return DocumentManager::create($conn, $config, $eventManager);
    }
}
</pre>

    <h2>Using it</h2>

    <p>With a configured Manifest and DocumentManager factory, you can get the DocumentManger with:</p>

<pre class="prettyprint linenums">
$manifest = new Manifest([...]);
$documentManager = $manifest->getServiceManager()->get('mydocumentmanager');
</pre>

    <p>Then just use the DocumentManager, and the extensions will do all their work for you.</p>

    <p>Extensions may also provide other services which are available through the ServiceManager. For example, the Serializer extension provides a Serializer:</p>

<pre class="prettyprint linenums">
$serializer = $manifest->getServiceManager()->get('serializer');
</pre>

</section>

