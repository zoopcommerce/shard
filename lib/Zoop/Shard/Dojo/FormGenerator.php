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
class FormGenerator extends AbstractDojoGenerator
{

    protected $generatorName = 'generator.dojo.form';

    protected $resourceSuffix = 'Form';

    public function generate($name, $class, $options = null)
    {

        $metadata = $this->getDocumentManager()->getClassMetadata($class);
        $defaultMixins = $this->getDefaultMixins();

        $templateArgs = [];

        $hasMultiFieldValidator = isset($metadata->validator) && isset($metadata->validator['document']);

        $params = [];

        if ($hasMultiFieldValidator){
            $defaultMids = $defaultMixins['form']['withValidator'];
        } else {
            $defaultMids = $defaultMixins['form']['simple'];
        }
        if (isset($options['mixins'])){
            $templateArgs['dependencyMids'] = $options['mixins'];
        } else {
            $templateArgs['dependencyMids'] = $defaultMids;
        }
        $templateArgs['mixins'] = $this->namesFromMids($templateArgs['dependencyMids']);
        $templateArgs['dependencies'] = $this->namesFromMids($templateArgs['dependencyMids']);
        if ($hasMultiFieldValidator){
            $templateArgs['dependencyMids'][] = $this->serviceLocator->get('generator.dojo.multifieldvalidator')->getMid($metadata->name);
            $templateArgs['dependencies'][] = 'MultiFieldValidator';
            $params['validator'] = new Expr('new MultiFieldValidator');
        }
        $params['inputs'] = [];
        $inputGenerator = $this->serviceLocator->get('generator.dojo.input');
        foreach($this->getSerializer()->fieldListForUnserialize($metadata) as $field){
            $templateArgs['dependencyMids'][] = $inputGenerator->getMid($metadata->name, ['field' => $field]);
            $templateArgs['dependencies'][] = ucfirst($field) . 'Input';
            $params['inputs'][] = new Expr('new ' . ucfirst($field) . 'Input');
        }

        $templateArgs['dependencyMids'] = ',' . $this->indent($this->implodeMids($templateArgs['dependencyMids']));
        $templateArgs['dependencies'] = ',' . $this->indent($this->implodeNames($templateArgs['dependencies']));
        $templateArgs['mixins'] = $this->indent($this->implodeNames($templateArgs['mixins']), 12) . $this->indent("\n", 8);
        $templateArgs['params'] = $this->implodeParams($params);
        $templateArgs['comment'] = $this->indent("// Will return a form for $metadata->name");

        $resource = $this->populateTemplate(
            file_get_contents(__DIR__ . '/Template/Module.js.template'),
            $templateArgs
        );

        $this->handleFlatFile($name, $resource);

        return $resource;
    }
}
