<section id="extension-factory" title="Extension Factory">
  <div class="page-header">
    <h1>Extension Factory</h1>
  </div>

    <p>Create a small factory class to return an instance of your extension. Eg:</p>

<pre class="prettyprint linenums">
namespace My\Color;

use Zoop\Shard\AbstractExtensionFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExtensionFactory extends AbstractExtensionFactory
{
    protected $extensionServiceName = 'my.extension.color';

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Extension($this->getConfig($serviceLocator));
    }
}
</pre>

</section>
