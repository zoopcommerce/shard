<section id="using-it" title="Using It">
  <div class="page-header">
    <h1>Using It</h1>
  </div>

    <p>That's it!</p>

    <h2>Config</h2>
    <p>Now register and enable you extension when you create a manifest:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'my.extension.color' => true
    ],
    'service_manager_config' => [
        'factories' => [
            'my.extension.color' => 'My\Color\ExtensionFactory'
        ],
    ]
]);
</pre>

    <h2>@Color</h2>
    <p>Use your <code>@Color</code> annotation in a document:</p>

<pre class="prettyprint linenums">
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use My\Color\Color;

/**
 * @ODM\Document
 */
class DocWithColor {

    /**
     * @ODM\String
     * @Color
     */
    protected $color;
    ...
}
</pre>

    <h2>Filter</h2>
    <p>Use your filter to filter documents by color:</p>

<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('color');
$filter = $documentManager->getFilterCollection()->getFilter('color');
$filter->setColor('blue');
</pre>
     
</section>
