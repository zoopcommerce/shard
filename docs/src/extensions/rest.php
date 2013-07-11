<section id="rest" title="Rest">
  <div class="page-header">
    <h1>Rest</h1>
  </div>

    <p class="lead">Map documents to Rest web services</p>

    <p>The Rest extension allows you to specify the mapping of document classes to Rest endpoints.</p>

    <p>Note: This extension does not create a web service. It only provides the mapping. If you require a full web service, one is provided by <a href="http://zoopcommerce.github.io/shard-module">shard-module</a>.</p>

    <h2>Configuration</h2>

    <p>Configuration is very important for the Rest extension. An <code>endpoint_map</code> must be defined. An <code>endpoint_map</code> defines how endpoint are mapped to document classes. The array keys are the endpoint names.</p>

<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.rest' => [
            'user' => [
                /* rest config goes here */
            ]
        ]
    ],
    ...
]);
</pre>

    <p>The configuration array for an endpoint may have three keys:</p>

<table class="table table-bordered table-striped">
  <thead>
   <tr>
     <th style="width: 100px;">Name</th>
     <th style="width: 50px;">type</th>
     <th style="width: 50px;">required</th>
     <th>description</th>
   </tr>
  </thead>
  <tbody>
<tr>
    <td>class</td>
    <td>string</td>
    <td>true</td>
    <td>The class name of a document class that will be used to generate the resource.</td>
</tr>
<tr>
    <td>property</td>
    <td>string</td>
    <td>true</td>
    <td>The name of the document property to be used as the rest Id. This should normally be the same as the document Id, but it does not have to be. If it is not the same, care must be take so that it is always unique.</td>
</tr>
<tr>
    <td>cache</td>
    <td>array</td>
    <td>false</td>
    <td>An optional array of cache directives that can be used to set cache headers.</td>
</tr>
<tr>
    <td>embedded_lists</td>
    <td>array</td>
    <td>false</td>
    <td>An optional array of embedded documents that you want exposed through your rest api.</td>
</tr>
</tbody>
</table>

    <p>A complete config might look like:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'extension_configs' => [
        'extension.rest' => [
            'user' => [
                'class' => 'My\Documents\User',
                'property' => 'username',
                'cache_control' => [
                    'public'  => true,
                    'max_age' => 10
                ]
            ]
        ]
    ],
    ...
]);
</pre>

    <h2>The Endpoint Map</h2>

    <p>The endpoint map is a service provided by the rest extension. To get it use:</p>

<pre class="prettyprint linenums">
$endpointMap = $manifest->getServiceManager()->get('endpointMap');
</pre>

    <p>To check if an endpoint exists:</p>

<pre class="prettyprint linenums">
if ($endpointMap->hasEndpoint('user')){
    //endpoint does exist
} else {
    //endpoint doesn't exist
};
</pre>

    <p>To get an endpoint, and access it's properties:</p>
<pre class="prettyprint linenums">
$endpoint = $endpointMap->getEndpoint('user');

$endpoint->getClass();
$endpoint->getProperty();
$endpoint->getCacheControl();
$endpoint->getEmbeddedLists();
</pre>

    <p>The endpointMap can also do a reverse lookup from the document class. Note: this will return an array.</p>
<pre class="prettyprint linenums">
$endpoints = $endpointMap->getEndpointsFromClass('My\Documents\User');
</pre>

</section>
