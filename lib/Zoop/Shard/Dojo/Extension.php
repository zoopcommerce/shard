<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Dojo;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension {

    protected $serviceManagerConfig = [
        'invokables' => [
            'cli.command.dojo.files.saveall' => 'Zoop\Shard\Dojo\Console\Command\FilesSaveAllCommand',
            'cli.command.dojo.files.deleteall' => 'Zoop\Shard\Dojo\Console\Command\FilesDeleteAllCommand',
            'generator.dojo.form' => 'Zoop\Shard\Dojo\FormGenerator',
            'generator.dojo.input' => 'Zoop\Shard\Dojo\InputGenerator',
            'generator.dojo.multifieldvalidator' => 'Zoop\Shard\Dojo\MultiFieldValidatorGenerator',
            'generator.dojo.validator' => 'Zoop\Shard\Dojo\ValidatorGenerator',
            'generator.dojo.model' => 'Zoop\Shard\Dojo\ModelGenerator',
            'generator.dojo.modelvalidator' => 'Zoop\Shard\Dojo\ModelValidatorGenerator',
            'generator.dojo.jsonrest' => 'Zoop\Shard\Dojo\JsonRestGenerator'
        ],
        'factories' => [
            'cli.helper.dojo.servicelocator' => 'Zoop\Shard\Dojo\Console\Helper\ServiceLocatorHelperFactory',
        ]
    ];

    protected $cliCommands = [
        'cli.command.dojo.files.saveall',
        'cli.command.dojo.files.deleteall'
    ];

    protected $cliHelpers = [
        'servicelocator' => 'cli.helper.dojo.servicelocator',
    ];

    /**
     *
     * @var array
     */
    protected $dependencies = array(
        'extension.generator' => true,
        'extension.rest' => true,
        'extension.serializer' => true,
        'extension.validator' => true,
    );

    protected $filePaths = [];

    protected $defaultMixins = [
        'model'                    => ['havok/mvc/BaseModel'],
        'form' => [
            'simple'               => ['havok/form/Form'],
            'withValidator'        => ['havok/form/ValidationControlGroup'],
        ],
        'input' => [
            'string'               => ['havok/form/TextBox'],
            'stringWithValidator'  => ['havok/form/ValidationTextBox'],
            'float'                => ['havok/form/TextBox'],
            'floatWithValidator'   => ['havok/form/ValidationTextBox'],
            'int'                  => ['havok/form/TextBox'],
            'intWithValidator'     => ['havok/form/ValidationTextBox'],
            'boolean'              => ['havok/form/Checkbox'],
        ],
        'validator' => [
            'model'                => ['havok/validator/Model'],
            'chain'                => ['mystique/chain']
        ],
        'store' => [
            'jsonRest'             => ['havok/mvc/JsonRest']
        ]
    ];

    /**
     * Values can be save | delete | ignore
     *
     * @var string
     */
    protected $flatFileStrategy = 'ignore';

    /**
     *
     * @return string
     */
    public function getFilePaths() {
        return $this->filePaths;
    }

    /**
     *
     * @param array $filePaths
     */
    public function setFilePaths(array $filePaths) {
        $this->filePaths = $filePaths;
    }

    public function getDefaultMixins() {
        return $this->defaultMixins;
    }

    public function setDefaultMixins(array $defaultMixins) {
        $this->defaultMixins = $defaultMixins;
    }

    public function getFlatFileStrategy() {
        return $this->flatFileStrategy;
    }

    public function setFlatFileStrategy($flatFileStrategy) {
        $this->flatFileStrategy = (string) $flatFileStrategy;
    }
}