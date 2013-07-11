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
class ValidatorGenerator extends AbstractDojoGenerator
{

    protected $generatorName = 'generator.dojo.validator';

    protected $resourceSuffix = 'Validator';

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

        if ( ! isset($metadata->validator['fields'][$field])){
            return;
        }

        if (count($metadata->validator['fields'][$field]) > 1){
            $templateArgs = [
                'dependencyMids' => $defaultMixins['validator']['chain'],
                'dependencies' => $this->namesFromMids($defaultMixins['validator']['chain']),
                'mixins' => $this->namesFromMids($defaultMixins['validator']['chain']),
                'params' => ['field' => "$field"]
            ];

            if (isset($metadata->validator['fields'][$field])){
                $validator = $metadata->validator['fields'][$field];
                $validatorMid = $this->midFromClass($validator['class']);
                $validatorName = $this->nameFromMid($validatorMid);

                $templateArgs['dependencyMids'][] = $validatorMid;
                $templateArgs['dependencies'][] = $validatorName;

                if(isset($validator['options']) && count($validator['options']) > 0 ){
                    $params = json_encode($validator['options']);
                    $templateArgs['params']['validators'][] = new Expr("new $validatorName($params)");
                } else {
                    $templateArgs['params']['validators'][] = new Expr("new $validatorName");
                }
            }

        } else {
            $validator = $metadata->validator['fields'][$field][0];

            $mid = $this->midFromClass($validator['class']);
            $templateArgs = [
                'dependencyMids' => [$mid],
                'dependencies' => [$this->nameFromMid($mid)],
                'mixins' => [$this->nameFromMid($mid)],
            ];

            if (isset($validator['options'])){
                $templateArgs['params'] = array_merge(['field' => "$field"], $validator['options']);
            } else {
                $templateArgs['params'] = ['field' => "$field"];
            }
        }

        $templateArgs['dependencyMids'] = ',' . $this->indent($this->implodeMids($templateArgs['dependencyMids']));
        $templateArgs['dependencies'] = ',' . $this->indent($this->implodeNames($templateArgs['dependencies']));
        $templateArgs['mixins'] = $this->indent($this->implodeNames($templateArgs['mixins']), 12) . $this->indent("\n", 8);
        $templateArgs['params'] = $this->implodeParams($templateArgs['params']);
        $templateArgs['comment'] = $this->indent("// Will return a validator that can be used to check\n// the $field field");

        $resource = $this->populateTemplate(
            file_get_contents(__DIR__ . '/Template/Module.js.template'),
            $templateArgs
        );

        $this->handleFlatFile($name, $resource);

        return $resource;
    }
}
