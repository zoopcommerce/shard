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
class ModelValidatorGenerator extends AbstractDojoGenerator
{
    public function generate($name, $class, $options = null) {

        $metadata = $this->getDocumentManager()->getClassMetadata($class);
        $field = $options['field'];
        $defaultMixins = $this->getDefaultMixins();

        $templateArgs = [];

        $hasMultiFieldValidator = isset($metadata->validator) && isset($metadata->validator['document']);

        $params = ['validators' => []];

        if (isset($options['mixins'])){
            $templateArgs['dependencyMids'] = $options['mixins'];
        } else {
            $templateArgs['dependencyMids'] = $defaultMixins['validator']['model'];
        }

        $templateArgs['mixins'] = $this->namesFromMids($templateArgs['dependencyMids']);
        $templateArgs['dependencies'] = $this->namesFromMids($templateArgs['dependencyMids']);
        if ($hasMultiFieldValidator){
            $templateArgs['dependencyMids'][] = $this->serviceLocator->get('generator.dojo.multifieldvalidator')->getMid($metadata->name);
            $templateArgs['dependencies'][] = 'MultiFieldValidator';
            $params['validators'][] = new Expr('new MultiFieldValidator');
        }
        foreach($this->getSerializer()->fieldListForUnserialize($metadata) as $field){
            if(isset($metadata->validator) && isset($metadata->validator['fields'][$field])){
                $templateArgs['dependencyMids'][] = $this->serviceLocator->get('generator.dojo.validator')->getMid($metadata->name, ['field' => $field]);
                $templateArgs['dependencies'][] = ucfirst($field) . 'Validator';
                $params['validators'][] = new Expr('new ' . ucfirst($field) . 'Validator');
            }
        }

        $templateArgs['dependencyMids'] = ',' . $this->indent($this->implodeMids($templateArgs['dependencyMids']));
        $templateArgs['dependencies'] = ',' . $this->indent($this->implodeNames($templateArgs['dependencies']));
        $templateArgs['mixins'] = $this->indent($this->implodeNames($templateArgs['mixins']), 12) . $this->indent("\n", 8);
        $templateArgs['params'] = $this->implodeParams($params);
        $templateArgs['comment'] = $this->indent("// Will return a validator to validate a complete model for $metadata->name");

        $resource = $this->populateTemplate(
            file_get_contents(__DIR__ . '/Template/Module.js.template'),
            $templateArgs
        );

        $this->handleFlatFile($name, $resource);

        return $resource;
    }
}
