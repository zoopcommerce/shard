<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Dojo;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ModelGenerator extends AbstractDojoGenerator
{

    protected $generatorName = 'generator.dojo.model';

    protected $resourceSuffix = 'Model';

    public function generate($name, $class, $options = null)
    {

        $metadata = $this->getDocumentManager()->getClassMetadata($class);
        $defaultMixins = $this->getDefaultMixins();

        $templateArgs = [];

        $params = [];

        if (isset($options['mixins'])){
            $templateArgs['dependencyMids'] = $options['mixins'];
        } else {
            $templateArgs['dependencyMids'] = $defaultMixins['model'];
        }

        $templateArgs['mixins'] = $this->namesFromMids($templateArgs['dependencyMids']);
        $templateArgs['dependencies'] = $this->namesFromMids($templateArgs['dependencyMids']);

        $params['_fields'] = [];
        foreach($this->getSerializer()->fieldListForUnserialize($metadata) as $field){
            $params['_fields'][] = $field;
        }

        $params['_fields'][] = '_className';
        $params['_className'] = str_replace('\\', '\\\\', $metadata->name);

        $templateArgs['dependencyMids'] = ',' . $this->indent($this->implodeMids($templateArgs['dependencyMids']));
        $templateArgs['dependencies'] = ',' . $this->indent($this->implodeNames($templateArgs['dependencies']));
        $templateArgs['mixins'] = $this->indent($this->implodeNames($templateArgs['mixins']), 12) . $this->indent("\n", 8);
        $templateArgs['params'] = $this->implodeParams($params);
        $templateArgs['comment'] = $this->indent("// Will return a model for $metadata->name");

        $resource = $this->populateTemplate(
            file_get_contents(__DIR__ . '/Template/Module.js.template'),
            $templateArgs
        );

        $this->handleFlatFile($name, $resource);

        return $resource;
    }
}
