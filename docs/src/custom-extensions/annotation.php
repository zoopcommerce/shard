<section id="annoation" title="Annotation">
  <div class="page-header">
    <h1>Annotation</h1>
  </div>

    <p>The color extension is using an annotation, so we need to define that annotation:</p>

<pre class="prettyprint linenums">
namespace My\Color;

use Doctrine\Common\Annotations\Annotation;

final class Color extends Annotation
{
    const event = 'annotationColor';
}
</pre>

</section>
