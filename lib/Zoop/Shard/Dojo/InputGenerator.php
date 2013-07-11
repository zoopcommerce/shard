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
class InputGenerator extends AbstractDojoGenerator
{

    protected $generatorName = 'generator.dojo.input';

    protected $resourceSuffix = 'Input';

    public function getMid($class, $options = null){

        $field = $options['field'];
        $resourceMap = $this->serviceLocator->get('resourcemap');
        foreach ($resourceMap->getMap() as $name => $config){
            if ($config['generator'] == $this->generatorName &&
                $config['class'] == $class &&
                isset($config['options']) &&
                $config['options']['field'] == $field
            ) {
                return substr($name, 0, strrpos($name, '.'));
            }
        }

        //no configured resource found, so create one.
        $mid = str_replace('\\', '/', $class) . '/' . ucfirst($field) . '/' . $this->resourceSuffix;
        $resourceMap->setResourceConfig($mid . '.js', [
            'class' => $class,
            'generator' => $this->generatorName,
            'options' => [
                'field' => $field
            ]
        ]);

        return $mid;
    }

    public function generate($name, $class, $options = null)
    {

        $metadata = $this->getDocumentManager()->getClassMetadata($class);
        $field = $options['field'];
        $defaultMixins = $this->getDefaultMixins();

        $templateArgs = [];

        $hasValidator = isset($metadata->validator) && isset($metadata->validator['fields'][$field]);

        $params = [];

        if ($hasValidator){
            switch ($metadata->fieldMappings[$field]['type']){
                case 'int':
                    $defaultMids = $defaultMixins['input']['intWithValidator'];
                    break;
                case 'float':
                    $defaultMids = $defaultMixins['input']['floatWithValidator'];
                    break;
                case 'custom_id':
                    $moduleParams['type'] = "hidden";
                case 'string':
                default:
                    $defaultMids = $defaultMixins['input']['stringWithValidator'];
                    break;
            }
            if (isset($options['mixins'])){
                $templateArgs['dependencyMids'] = $options['mixins'];
            } else {
                $templateArgs['dependencyMids'] = $defaultMids;
            }
            $templateArgs['dependencies'] = $this->namesFromMids($templateArgs['dependencyMids']);
            $templateArgs['mixins'] = $this->namesFromMids($templateArgs['dependencyMids']);

            $templateArgs['dependencyMids'][] = $this->serviceLocator->get('generator.dojo.validator')->getMid($metadata->name, ['field' => $field]);
            $templateArgs['dependencies'][] = ucfirst($field) . 'Validator';
            $params['validator'] = new Expr('new ' . ucfirst($field) . 'Validator');
        } else {
            switch ($metadata->fieldMappings[$field]['type']){
                case 'int':
                    $defaultMids = $defaultMixins['input']['int'];
                    break;
                case 'float':
                    $defaultMids = $defaultMixins['input']['float'];
                    break;
                case 'custom_id':
                    $params['type'] = 'hidden';
                case 'string':
                default:
                    $defaultMids = $defaultMixins['input']['string'];
                    break;
            }

            if (isset($options['mixins'])){
                $templateArgs['dependencyMids'] = $options['mixins'];
            } else {
                $templateArgs['dependencyMids'] = $defaultMids;
            }

            $templateArgs['dependencies'] = $this->namesFromMids($templateArgs['dependencyMids']);
            $templateArgs['mixins'] = $this->namesFromMids($templateArgs['dependencyMids']);
        }

        $params['name'] = $field;

        //Camel case splitting regex
        $regex = '/(?<=[a-z])(?=[A-Z])/x';

        $params['label'] = ucfirst(implode(' ', preg_split($regex, $field)));

        if (isset($options['params'])){
            foreach ($options['params'] as $key => $value){
                $params[$key] = $value;
            }
        }

        $templateArgs['dependencyMids'] = ',' . $this->indent($this->implodeMids($templateArgs['dependencyMids']));
        $templateArgs['dependencies'] = ',' . $this->indent($this->implodeNames($templateArgs['dependencies']));
        $templateArgs['mixins'] = $this->indent($this->implodeNames($templateArgs['mixins']), 12) . $this->indent("\n", 8);
        $templateArgs['params'] = $this->implodeParams($params);
        $templateArgs['comment'] = $this->indent("// Will return an input for the $field field");

        $resource = $this->populateTemplate(
            file_get_contents(__DIR__ . '/Template/Module.js.template'),
            $templateArgs
        );

        $this->handleFlatFile($name, $resource);

        return $resource;
    }
}
