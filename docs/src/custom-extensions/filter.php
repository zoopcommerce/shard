<section id="filter" title="Filter">
  <div class="page-header">
    <h1>Filter</h1>
  </div>

    <p>Add a filter that can filter by color:</p>

<pre class="prettyprint linenums">
namespace My\Color;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;

class Filter extends BsonFilter
{
    protected $color;

    public function setColor($color){
        $this->color = $color;
    }

    public function addFilterCriteria(ClassMetadata $metadata)
    {
        if (isset($metadata->color)) {
            return array($metadata->color => $this->color);
        }
        return array();
    }
}
</pre>

</section>
