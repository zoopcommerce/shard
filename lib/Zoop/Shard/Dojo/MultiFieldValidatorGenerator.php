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
class MultiFieldValidatorGenerator extends AbstractDojoGenerator
{

    protected $generatorName = 'generator.dojo.multifieldvalidator';

    protected $resourceSuffix = 'MultiFieldValidator';

    public function generate($name, $class, $options = null)
    {

        $metadata = $this->getDocumentManager()->getClassMetadata($class);
        $defaultMixins = $this->getDefaultMixins();

        if ( ! isset($metadata->validator['document'])){
            return;
        }

        if (count($metadata->validator['document']) > 1){
            $templateArgs = [
                'dependencyMids' => $defaultMixins['validator']['chain'],
                'dependencies' => $this->namesFromMids($defaultMixins['validator']['chain']),
                'mixins' => $this->namesFromMids($defaultMixins['validator']['chain'])
            ];

            if (isset($metadata->validator['document'])){
                $validator = $metadata->validator['document'];
                $validatorMid = $this->midFromClass($validator['class']);
                $validatorName = $this->nameFromMid($validatorMid);

                $templateArgs['dependencyMids'][] = $validatorMid;
                $templateArgs['dependencies'][] = $validatorName;

                if(isset($validator['options']) && count($validator['options']) > 0 ){
                    $params = json_encode($validator['options'], JSON_PRETTY_PRINT);
                    $templateArgs['params']['validators'][] = new Expr("new $validatorName($params)");
                } else {
                    $templateArgs['params']['validators'][] = new Expr("new $validatorName");
                }
            }

        } else {
            $validator = $metadata->validator['document'][0];

            $mid = $this->midFromClass($validator['class']);
            $templateArgs = [
                'dependencyMids' => [$mid],
                'dependencies' => [$this->nameFromMid($mid)],
                'mixins' => [$this->nameFromMid($mid)],
            ];

            if (isset($validator['options'])){
                $templateArgs['params'] = array_merge(['field' => "'$field'"], $validator['options']);
            } else {
                $templateArgs['params'] = [];
            }
        }

        $templateArgs['dependencyMids'] = ',' . $this->indent($this->implodeMids($templateArgs['dependencyMids']));
        $templateArgs['dependencies'] = ',' . $this->indent($this->implodeNames($templateArgs['dependencies']));
        $templateArgs['mixins'] = $this->indent($this->implodeNames($templateArgs['mixins']), 12) . $this->indent("\n", 8);
        $templateArgs['params'] = $this->implodeParams($templateArgs['params']);
        $templateArgs['comment'] = $this->indent("// Will return a multi field validator");

        $resource = $this->populateTemplate(
            file_get_contents(__DIR__ . '/Template/Module.js.template'),
            $templateArgs
        );

        $this->handleFlatFile($name, $resource);

        return $resource;
    }
}
