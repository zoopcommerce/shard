<section id="annoation-subscriber" title="Annotation Subscriber">
  <div class="page-header">
    <h1>Annotation Subscriber</h1>
  </div>

    <p>Add an annotation subscriber to listen to the <code>annotationColor</code> event and augment the metadata:</p>

<pre class="prettyprint linenums">
namespace My\Color;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Annotation\AnnotationEventArgs;

class AnnotationSubscriber implements EventSubscriber
{

    public function getSubscribedEvents(){
        return [Color::event];
    }

    public function annotationColorZones(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->color = $eventArgs->getReflection()->getName();
    }
}
</pre>

</section>
