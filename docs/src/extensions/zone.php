<section id="zone" title="Zone">
  <div class="page-header">
    <h1>Zone</h1>
  </div>

    <p class="lead">Add zones to documents.</p>
    <p>A zone is a region of relevance. For example, it may be a company department, a geographical area.</p>

    <p>A document may be assigned multiple zones.</p>

    <h2>Configuration</h2>
    <p>Zone has no configuration options. Just use:</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.zone' => true
    ],
    ...
]);
</pre>

    <h2>Adding zones to a Document</h2>

    <p>To add zones, add <code>@Shard\Zones</code> to a field. Eg:</p>

<pre class="prettyprint linenums">
/**
 * @ODM\Collection
 * @Shard\Zones
 */
protected $zones;
</pre>

    <p>For convienence you can use the <code>Zoop\Shard\Zone\DataModel\ZoneTrait</code> to add such a field to a document.</p>


        <h2>Zone Filter</h2>

        <p>The zone extension provides a filter that can be used to filter result sets based on document zones.</p>

        <p>The zone filter takes a list of zones, and if those zones should be included or excluded.</p>

        <p>Eg, exclude some zones:</p>
<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('zone');
$filter = $documentManager->getFilterCollection()->getFilter('zone');
$filter->setStates(['business-support']);
$filter->excludeZoneList();
</pre>

        <p>Eg, include some states:</p>
<pre class="prettyprint linenums">
$documentManager->getFilterCollection()->enable('zone');
$filter = $documentManager->getFilterCollection()->getFilter('zone');
$filter->setStates(['accounts', 'hr']);
$filter->includeZoneList();
</pre>

</section>
