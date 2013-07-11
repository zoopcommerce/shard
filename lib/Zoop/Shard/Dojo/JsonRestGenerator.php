<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Dojo;

use Zend\Json\Expr;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class JsonRestGenerator extends AbstractDojoGenerator
{

    protected $generatorName = 'generator.dojo.jsonrest';

    protected $resourceSuffix = 'JsonRest';

    /**
     *
     * @param \Zoop\Shard\Generator\GenerateEventArgs $eventArgs
     */
    public function generate($name, $class, $options = null) {

        $metadata = $this->getDocumentManager()->getClassMetadata($class);
        $defaultMixins = $this->getDefaultMixins();

        $templateArgs = [];

        $params = [];

        if (isset($options['mixins'])){
            $templateArgs['dependencyMids'] = $options['mixins'];
        } else {
            $templateArgs['dependencyMids'] = $defaultMixins['store']['jsonRest'];
        }
        $templateArgs['mixins'] = $this->namesFromMids($templateArgs['dependencyMids']);
        $templateArgs['dependencies'] = $this->namesFromMids($templateArgs['dependencyMids']);

        $modelMid = $this->serviceLocator->get('generator.dojo.model')->getMid($metadata->name);
        $pieces = explode('/', $modelMid);
        $model = $pieces[count($pieces) - 1];

        $templateArgs['dependencyMids'][] = $modelMid;
        $templateArgs['dependencies'][] = $model;

        $params['name'] = $metadata->collection;
        $params['idField'] = $metadata->identifier;

        if (isset($metadata->rest)){
            $params['target'] = $metadata->rest['endpoint'];
        } else {
            $params['target'] = $metadata->collection;
        }
        $params['model'] = new Expr($model);

        $templateArgs['dependencyMids'] = ',' . $this->indent($this->implodeMids($templateArgs['dependencyMids']));
        $templateArgs['dependencies'] = ',' . $this->indent($this->implodeNames($templateArgs['dependencies']));
        $templateArgs['mixins'] = $this->indent($this->implodeNames($templateArgs['mixins']), 12) . $this->indent("\n", 8);
        $templateArgs['params'] = $this->implodeParams($params);
        $templateArgs['comment'] = $this->indent("// Will return create a dojo JsonRest store for $metadata->name");

        $resource = $this->populateTemplate(
            file_get_contents(__DIR__ . '/Template/Module.js.template'),
            $templateArgs
        );

        $this->handleFlatFile($name, $resource);

        return $resource;

    }
}
