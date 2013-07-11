<section id="extension-class" title="Extension Class">
  <div class="page-header">
    <h1>Extension Class</h1>
  </div>

    <p>At bare minimum, an extension must have an instance of <code>Zoop\Shard\AbstractExtension</code>. This class provides all the hooks and configuration required to create your extension. It has these properties, and you can add your own custom configuration properties:</p>

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
    <td>documents</td>
    <td>array</td>
    <td>An array of document namespaces and directories to register.</td>
</tr>
<tr>
    <td>subscribers</td>
    <td>array</td>
    <td>An array of subscribers, or subscriber service names to register.</td>
</tr>
<tr>
    <td>cliCommands</td>
    <td>array</td>
    <td>An array of cli command service names to register with the doctrine cli.</td>
</tr>
<tr>
    <td>cliHelpers</td>
    <td>array</td>
    <td>An array of cli helper service names to register with the doctrine cli.</td>
</tr>
<tr>
    <td>serviceManagerConfig</td>
    <td>array</td>
    <td>Any service manager configuration.</td>
</tr>
<tr>
    <td>dependencies</td>
    <td>array</td>
    <td>An array of service names of other extensions that must be loaded for this extension to work.</td>
</tr>
</tbody>
</table>

    <p>For example, the Color extension class would look like this:</p>

<pre class="prettyprint linenums">
namespace My\Color;

use Zoop\Shard\AbstractExtension;

class Extension extends AbstractExtension
{

    protected $subscribers = [
        'subscriber.color.annotationsubscriber' //annotation subscriber to listen to @Color annotation events
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.color.annotationsubscriber' => 'My\Color\AnnotationSubscriber' //register the annotation subscriber service
        ]
    ];

    protected $filters = [
        'color' => 'My\Color\Filter' //register filter
    ];

    protected $dependencies = [
        'extension.annotation' => true //require the annotation extension to make annotation events work
    ];
}
</pre>

</section>
