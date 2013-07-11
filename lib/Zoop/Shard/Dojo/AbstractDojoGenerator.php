<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Dojo;

use Zoop\Shard\DocumentManagerAwareInterface;
use Zoop\Shard\DocumentManagerAwareTrait;
use Zoop\Shard\Generator\GeneratorInterface;
use Zend\Json\Expr;
use Zend\Json\Json;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
abstract class AbstractDojoGenerator implements GeneratorInterface, ServiceLocatorAwareInterface, DocumentManagerAwareInterface
{

    use ServiceLocatorAwareTrait;
    use DocumentManagerAwareTrait;

    protected $generatorName;

    protected $resourceSuffix;

    protected $extension;

    protected $serializer;

    protected $filePaths;

    protected $defaultMixins;

    protected $flatFileStrategy;

    public function getFilePaths(){
        if (!isset($this->filePaths)){
            $this->filePaths = $this->getExtension()->getFilePaths();
        }
        return $this->filePaths;
    }

    public function getDefaultMixins(){
        if (!isset($this->defaultMixins)){
            $this->defaultMixins = $this->getExtension()->getDefaultMixins();
        }
        return $this->defaultMixins;
    }

    public function getFlatFileStrategy(){
        if (!isset($this->flatFileStrategy)){
            $this->flatFileStrategy = $this->getExtension()->getflatFileStrategy();
        }
        return $this->flatFileStrategy;
    }

    public function getExtension(){
        if (!isset($this->extension)){
            $this->extension = $this->serviceLocator->get('extension.dojo');
        }
        return $this->extension;
    }

    protected function getSerializer(){
        if (!isset($this->serializer)){
            $this->serializer = $this->serviceLocator->get('serializer');
        }
        return $this->serializer;
    }

    public function getFilePath($name){
        foreach ($this->getFilePaths() as $filePath){
            if ($filePath['filter'] == '' || strpos($name, $filePath['filter']) !== false) {
                return $filePath['path'] . '/' . $name;
                break;
            }
        }
    }

    protected function populateTemplate($template, array $args) {

        $populated = $template;
        foreach ($args as $key => $value) {
            $populated = str_replace('<'.$key.'>', $value, $populated);
        }
        return $populated;
    }

    protected function indent($string, $indent = 4){
        $indent = str_repeat(' ', $indent);
        return $indent . str_replace("\n", "\n" . $indent, $string);
    }

    protected function implodeMids(array $mids){
        return "\n'" . implode("',\n'", $mids) . "'";
    }

    protected function implodeParams(array $params){
        $tmp = [];
        foreach ($params as $key => $value){
            switch (true){
                case is_array($value):
                    $param = "$key: " . Json::prettyPrint(Json::encode(
                        $value,
                        false,
                        ['enableJsonExprFinder' => true]
                    ));
                    break;
                case ($value instanceof Expr):
                    $param = "$key: " . $value;
                    break;
                default:
                    $param = "$key: '$value'";
            }
            $tmp[] = $param;
        }
        return $this->indent(implode(",\n\n", $tmp), 12);
    }

    protected function implodeNames(array $names){
        return "\n" . implode(",\n", $names);
    }

    protected function namesFromMids(array $mids){
        $result = [];
        foreach($mids as $mid){
            $result[] = $this->nameFromMid($mid);
        }
        return $result;
    }

    protected function nameFromMid($mid){
        $pieces = explode('/', $mid);
        return $pieces[count($pieces) - 1];
    }

    protected function midFromClass($class){
        return str_replace('\\', '/', $class);
    }

    protected function handleFlatFile($name, $content){

        $strategy = $this->getFlatFileStrategy();
        if ($strategy == 'ignore'){
            return;
        }

        $filePath = $this->getFilePath($name);
        if ($strategy == 'save'){
            $this->saveFlatFile($filePath, $content);
        };

        if ($strategy == 'delete'){
            $this->deleteFlatFile($filePath, $content);
        };
    }

    protected function saveFlatFile($filePath, $content){

        if ($filePath){

            $dir = dirname($filePath);

            if ( ! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($filePath, $content);
        }
    }

    protected function deleteFlatFile($filePath){
        if ($filePath && file_exists($filePath)){
            unlink($filePath);
        }
    }

    public function getMid($class, $options = null){

        $resourceMap = $this->serviceLocator->get('resourcemap');
        foreach ($resourceMap->getMap() as $name => $config){
            if ($config['generator'] == $this->generatorName &&
                $config['class'] == $class
            ) {
                return substr($name, 0, strrpos($name, '.'));
            }
        }

        //no configured resource found, so create one.
        $mid = str_replace('\\', '/', $class) . '/' . $this->resourceSuffix;
        $resourceMap->setResourceConfig($mid . '.js', [
            'class' => $class,
            'generator' => $this->generatorName
        ]);

        return $mid;
    }

}
